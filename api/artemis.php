<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $URL = "http://localhost:11434/api/generate";
    $MODELO = "tinyllama";

    // Obtém os dados do corpo da requisição
    $json_data = file_get_contents('php://input');
    $data_decode = json_decode($json_data, true);

    if (empty($data_decode['prompt']) || !is_string($data_decode['prompt'])) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Prompt deve ser uma string não vazia."]);
        exit;
    }

    $data = [
        "model" => $MODELO,
        "prompt" => $data_decode['prompt'],
        "stream" => true, // Stream habilitado
    ];

    try {
        $ch = curl_init($URL);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $chunk) {
            // Envia os dados recebidos diretamente ao cliente
            echo $chunk;
            ob_flush();
            flush();
            return strlen($chunk);
        });

        // Envia os cabeçalhos iniciais para o cliente antes de iniciar o streaming
        header('Content-Type: application/json');
        header('Transfer-Encoding: chunked');
        header('Cache-Control: no-cache');

        // Executa a requisição cURL
        $success = curl_exec($ch);

        // Verifica se ocorreu algum erro durante a execução
        if (curl_errno($ch)) {
            throw new Exception('Erro na requisição: ' . curl_error($ch));
        }

        // Verifica o código de status HTTP da resposta
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (!$success || $http_code !== 200) {
            throw new Exception('HTTP code: ' . $http_code);
        }

        curl_close($ch);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "message" => "Erro ao processar o streaming.",
            "details" => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "message" => "Método não permitido."
    ]);
}
