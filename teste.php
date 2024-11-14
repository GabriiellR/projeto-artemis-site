<?php

$url = "http://localhost:11434/api/generate";

$prompt = $_GET['prompt'];

// Dados da requisição em JSON
$data = [
    "model" => "tinyllama",
    "prompt" => $prompt,
    "stream" => false
];

// Inicia uma sessão cURL
$ch = curl_init($url);

// Configurações da requisição cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Executa a requisição e obtém a resposta
$response = curl_exec($ch);

// Verifica se houve erros
if (curl_errno($ch)) {
    echo 'Erro na requisição: ' . curl_error($ch);
} else {
    // Exibe a resposta
    echo 'Resposta da API: ' . $response;
}

// Fecha a sessão cURL 
curl_close($ch);
?>
