<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $URL = "http://localhost:11434/api/generate";
    $MODELO = "tinyllama";

    // Obtém os dados do corpo da requisição
    $json_data = file_get_contents('php://input');
    $data_decode = json_decode($json_data, true);

    if (empty($data_decode['prompt'])) {
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

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        // Verifica se ocorreu algum erro durante a execução da requisição
        if (curl_errno($ch)) {
            throw new Exception('Erro na requisição: ' . curl_error($ch));
        }

        // Verifica o código de status HTTP da resposta
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (!$success || $http_code !== 200) {
            throw new Exception('HTTP code: ' . $http_code);
        }

        curl_close($ch);
        
        // Decodifica a resposta JSON da API
        $response_decode = json_decode($response, true);

        // Verifica se a chave 'response' existe na resposta e a exibe
        if (isset($response_decode['response'])) {
            echo json_encode($response_decode['response']);
            http_response_code(200);
        } else {
            throw new Exception("Chave 'response' não encontrada na resposta.");
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "message" => "Erro ao buscar dados.",
            "details" => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "message" => "Método não permitido."
    ]);
}