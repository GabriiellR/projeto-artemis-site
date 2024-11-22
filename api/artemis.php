<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Definições básicas
    $URL = "http://localhost:11434/api/generate";
    $MODELO = "tinyllama";
    
    // Recebe os dados do corpo da requisição
    $json_data = file_get_contents('php://input');
    $data_decode = json_decode($json_data, true);

    // Validação do prompt
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
        // Inicializa o cURL
        $ch = curl_init($URL);

        // Configuração otimizada
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, // Retorna a resposta como string
            CURLOPT_POST => true,          // Define o método POST
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 120,        // Timeout de 2 minutos
        ]);

        // Executa a requisição
        $response = curl_exec($ch);

        // Verifica se ocorreu algum erro na requisição
        if ($response === false) {
            throw new Exception('Erro na requisição: ' . curl_error($ch));
        }

        // Verifica o código de status HTTP da resposta
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code !== 200) {
            throw new Exception('HTTP code: ' . $http_code . '. Response: ' . $response);
        }

        curl_close($ch);

        // Decodifica a resposta JSON
        $response_decode = json_decode($response, true);

        // Verifica se a chave 'response' está presente e a limita a 1200 caracteres
        if (isset($response_decode['response'])) {
            $response_content = $response_decode['response'];
            // Limita a resposta a 1200 caracteres
            if (strlen($response_content) > 1200) {
                $response_content = substr($response_content, 0, 1200) . '...'; // Trunca e adiciona "..."
            }

            // Retorna a resposta em formato JSON
            echo json_encode([
                "status" => "success",
                "response" => $response_content
            ]);
            http_response_code(200);
        } else {
            throw new Exception("Chave 'response' não encontrada na resposta.");
        }

    } catch (Exception $e) {
        // Caso ocorra qualquer erro, envia resposta de erro
        echo json_encode([
            "status" => "error",
            "message" => "Erro ao buscar dados.",
            "details" => $e->getMessage()
        ]);
        http_response_code(500);
    }
} else {
    // Resposta para método não permitido
    echo json_encode([
        "status" => "error",
        "message" => "Método não permitido."
    ]);
    http_response_code(405);
}
