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

    $cache_key = md5($data_decode['prompt']);
    $cached_response = apcu_fetch($cache_key);

    if ($cached_response) {
        echo $cached_response;
        http_response_code(200);
        exit;
    }

    $data = [
        "model" => $MODELO,
        "prompt" => $data_decode['prompt'],
        "stream" => true, // Ativa o streaming para grandes respostas
    ];

    try {
        $ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Habilita o streaming
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
            echo $data; // Envia os dados assim que chegam
            flush(); // Garante que os dados sejam enviados para o cliente imediatamente
            return strlen($data); // Continua a escrita
        });

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Erro na requisição: ' . curl_error($ch));
        }

        curl_close($ch);

        // Cache a resposta por 3600 segundos (1 hora)
        apcu_store($cache_key, $response, 3600);

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
