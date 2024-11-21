<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $URL = "http://localhost:11434/api/generate";
    $MODELO = "tinyllama";

    $json_data = file_get_contents('php://input');
    $data_decode = json_decode($json_data, true);

    if (empty($data_decode['prompt'])) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Prompt não pode estar vazio."]);
        exit;
    }

    $data = [
        "model" => $MODELO,
        "prompt" => $data_decode['prompt'],
        "stream" => false,
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
        if ($http_code !== 200) {
            throw new Exception('HTTP code: ' . $http_code . '. Response: ' . $response);
        }

        curl_close($ch);
        
        // Decodifica a resposta JSON da API
        $response_decode = json_decode($response, true);

        // Verifica se a chave 'response' existe na resposta
        if (isset($response_decode['response'])) {
            $responseText = $response_decode['response'];

            // Limita o tamanho da resposta a 1000 caracteres
            if (strlen($responseText) > 1000) {
                $responseText = substr($responseText, 0, 1000);
            }

            echo $responseText;
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
