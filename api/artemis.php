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

    // Limite de caracteres (ajuste conforme necessário)
    $MAX_LENGTH = 1100;
    $prompt = $data_decode['prompt'];
    if (strlen($prompt) > $MAX_LENGTH) {
        // Divida o texto em pedaços menores se exceder o limite
        $chunks = str_split($prompt, $MAX_LENGTH);

        $responses = [];
        foreach ($chunks as $chunk) {
            $data = [
                "model" => $MODELO,
                "prompt" => $chunk,
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

                if (curl_errno($ch)) {
                    throw new Exception('Erro na requisição: ' . curl_error($ch));
                }

                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($http_code !== 200) {
                    throw new Exception('HTTP code: ' . $http_code . '. Response: ' . $response);
                }

                curl_close($ch);
                
                // Decodifica a resposta JSON da API
                $response_decode = json_decode($response, true);

                if (isset($response_decode['response'])) {
                    $responses[] = $response_decode['response'];
                } else {
                    throw new Exception("Chave 'response' não encontrada na resposta.");
                }

            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode([
                    "message" => "Erro ao buscar dados.",
                    "details" => $e->getMessage()
                ]);
                exit;
            }
        }
        // Retorna todas as respostas concatenadas
        echo implode(" ", $responses);
        http_response_code(200);
    } else {
        // Caso o tamanho não ultrapasse o limite, executa normalmente
        $data = [
            "model" => $MODELO,
            "prompt" => $prompt,
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

            if (curl_errno($ch)) {
                throw new Exception('Erro na requisição: ' . curl_error($ch));
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code !== 200) {
                throw new Exception('HTTP code: ' . $http_code . '. Response: ' . $response);
            }

            curl_close($ch);
            
            // Decodifica a resposta JSON da API
            $response_decode = json_decode($response, true);

            if (isset($response_decode['response'])) {
                echo $response_decode['response'];
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
    }

} else {
    http_response_code(405);
    echo json_encode([
        "message" => "Método não permitido."
    ]);
}
