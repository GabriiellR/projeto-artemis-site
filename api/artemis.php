<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $URL  = "http://localhost:11434/api/generate";
    $MODELO = "tinyllama";

    $json_data = file_get_contents('php://input');
    $data_decode = json_decode($json_data);

    if (strlen($data_decode['prompt']) <= 0) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Prompt não pode estar vazio."]);
        exit;
    }

    $data = [
        "model" => $MODELO,
        "prompt" => $data_decode['prompt'],
        "stream" => false
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

        if (curl_errno($ch)) {
            throw new Exception('Erro na requisição: ' . curl_error($ch));
        }

        curl_close($ch);

        echo $response;
        http_response_code(200);
    } catch (Exception $e) {
        $data = [
            "message" => "Erro ao buscar dados.",
            "detalhes" => $e->getMessage(),
            "data" => [],
        ];

        echo json_encode($data);
        http_response_code(500);
    }
} else {
    $data = [
        "message" => "Método não permitido.",
        "data" => []
    ];

    echo json_encode($data);
    http_response_code(405);
}
