<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $URL  = "http://localhost:11434/api/generate";
    $MODELO = "tinyllama";

    $prompt = $_POST['prompt'];

    if (strlen($prompt) <= 0) {
        throw new Error("Bad Request");
    }

    $data = [
        "model" => $MODELO,
        "prompt" => $prompt,
        "stream" => false
    ];

    try {

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Error('Erro na requisição: ' . curl_error($ch));
        }

        echo json_encode($response);
        http_response_code(200);
    } catch (Exception $e) {
        $data = [
            "message" => "Erro ao buscar dados.",
            "detalhes" => $e,
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
