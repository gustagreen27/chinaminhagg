<?php
// Melhor configuração de erros - Para produção, mantenha display_errors desativado
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Inclua o arquivo de conexão ao banco de dados
include_once('../services/database.php');
include_once('../services/funcao.php'); // Função PHP_SEGURO() para sanitização
include_once('../services/crud.php'); // Função PHP_SEGURO() para sanitização

// Função para executar uma query preparada
function executePreparedQuery($mysqli, $sql, $types = '', $params = [])
{
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }
    return false;
}

// Função para lidar com cURL
function executeCurl($url, $data, $headers = [])
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $headers)
    ]);
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    return $error ? ['error' => $error] : json_decode($response, true);
}

// Verifica se o parâmetro 'id' foi passado
if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "Parâmetro 'id' não fornecido."]);
    exit;
}

$id = PHP_SEGURO($_GET['id']);

// Busca as informações do saque
$stmt = executePreparedQuery($mysqli, "SELECT valor, pix, transacao_id FROM solicitacao_saques WHERE transacao_id = ?", 's', [$id]);
$stmt->bind_result($valor, $chavepix, $transacaoId);
$stmt->fetch();
$stmt->close();

if (!$valor || !$chavepix) {
    echo json_encode(["success" => false, "message" => "Saque não encontrado ou parâmetros inválidos."]);
    exit;
}

// Formata o valor e busca o gateway padrão
$valor = number_format($valor, 2, '.', '');
$stmt = executePreparedQuery($mysqli, "SELECT gateway_default FROM config WHERE id = 1");
$stmt->bind_result($gateway_default);
$stmt->fetch();
$stmt->close();

// Verifica as credenciais do gateway e executa o pagamento
$gatewayQueries = [
    'suitpay' => "SELECT client_id, client_secret FROM suitpay WHERE id = 1",
    'royalbenk' => "SELECT url, client_secret FROM royalbenk WHERE id = 1",
    'pixup' => "SELECT url, client_id, client_secret FROM pixup WHERE id = 1",
    'digitopay' => "SELECT url, client_id, client_secret FROM digitopay WHERE id = 1",
    'paguepix' => "SELECT url, client_id, client_secret FROM paguepix WHERE id = 1",
];

$stmt = executePreparedQuery($mysqli, $gatewayQueries[$gateway_default]);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Erro ao buscar credenciais do gateway."]);
    exit;
}


if ($gateway_default === 'suitpay') {
    $stmt->bind_result($ci, $cs);
    $stmt->fetch();
    $stmt->close();

    if (!$ci || !$cs) {
        echo json_encode(["success" => false, "message" => "Credenciais Suitpay não encontradas."]);
        exit;
    }

    $responsejson = executeCurl('https://ws.suitpay.app/api/v1/gateway/pix-payment', [
        "value" => $valor,
        "key" => $chavepix,
        "typeKey" => "document"
    ], ['Ci: ' . $ci, 'Cs: ' . $cs]);
} else if ($gateway_default === 'royalbenk') {
    $stmt->bind_result($url, $client_secret);
    $stmt->fetch();
    $stmt->close();

    if (!$url || !$client_secret) {
        echo json_encode(["success" => false, "message" => "Credenciais Royalbenk não encontradas."]);
        exit;
    }

    // Busca informações do PIX para Royalbenk
    $stmt = executePreparedQuery($mysqli, "SELECT realname, pix_id, flag FROM metodos_pagamentos WHERE id = ?", 'i', [$chavepix]);
    $stmt->bind_result($pix_realname, $pix_account, $pix_flag);
    $stmt->fetch();
    $stmt->close();

    $urlcashout = str_replace('/v1/', '/', $url) . 'c1/cashout/';
    $responsejson = executeCurl($urlcashout, [
        "api-key" => $client_secret,
        "amount" => $valor,
        "keypix" => $pix_account,
        "cpf" => $pix_account,
        "name" => "Jogador"
    ]);
} else if ($gateway_default === 'pixup') {
    $stmt->bind_result($url, $client_id, $client_secret);
    $stmt->fetch();
    $stmt->close();

    if (!$url || !$client_id || !$client_secret) {
        echo json_encode(["success" => false, "message" => "Credenciais PixUp não encontradas."]);
        exit;
    }
} else if ($gateway_default === 'paguepix') {
    $stmt->bind_result($url, $client_id, $client_secret);
    $stmt->fetch();
    $stmt->close();

    if (!$url || !$client_id || !$client_secret) {
        echo json_encode(["success" => false, "message" => "Credenciais PaguePix não encontradas."]);
        exit;
    }    

    // Busca informações do PIX para Royalbenk
    $stmt = $pdo->prepare("SELECT realname, pix_account, flag, pix_id FROM metodos_pagamentos WHERE id = ?");
    $stmt->execute([$chavepix]);
    $stmt->bindColumn(1, $pix_realname);
    $stmt->bindColumn(2, $pix_account);
    $stmt->bindColumn(3, $pix_flag);
    $stmt->bindColumn(4, $pix_id);
    $stmt->fetch(PDO::FETCH_BOUND);
    $stmt->closeCursor();

    $credentials = base64_encode($client_id . ':' . $client_secret);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url . "oauth/token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Basic $credentials"
        ],
    ]);

    $responseT = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    // Decodificar a resposta JSON
    $responseToken = json_decode($responseT, true);
    $token = $responseToken['access_token'];

    if (isset($responseToken['statusCode'])) {
        echo json_encode(["success" => false, "message" => $responseToken['message']]);
        exit;
    }

    $doc = 'CPF';

    switch ($pix_flag) {
        case 1:
            $doc = "EMAIL";
            break;
        case 2:
            $doc = "TELEFONE";
            break;
        case 3:
            $doc = "CPF";
            break;
        case 4:
            $doc = "CNPJ";
            break;
    }


    $payload = [
        "amount" => (int) $valor,
        "description" => "Pagamento Jogador",
        "external_id" => $id,
        "creditParty" => [
            "name" => "Jogador",
            "keyType" => 'document',
            "key" => $pix_id,
            "taxId" => $pix_flag === 3 || $pix_flag === 4 ? $pix_account : $pix_id,
        ]
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url . "pix/payment",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token",
            "accept: application/json",
            "content-type: application/json"
        ],
    ]);

    $responsejson = curl_exec($curl);
    $responsejson = json_decode($responsejson, true);

    // Verifica a resposta do gateway
    if (isset($responsejson['statusCode'])) {
        echo json_encode(["success" => false, "message" => $responsejson['message']]);
        exit;
    } else {
        $date = new DateTime();
        $date = $date->format('Y-m-d H:i:s');
        $stmt = executePreparedQuery($mysqli, "UPDATE solicitacao_saques SET status = 1, data_att = ?  WHERE transacao_id = ?", 'ss', [$date, $id]);

        $urlpayconfirm =  $url . 'webhook/';
        executeCurl($urlpayconfirm, [
            "idTransaction" => $id,
        ]);


        echo json_encode(["success" => true, "message" => "Saque aprovado com sucesso."]);

        exit;
    }
} else if ($gateway_default === 'digitopay') {
    $stmt->close();

    global $data_digitopay;
    $url = $data_digitopay['url'];
    $client_id = $data_digitopay['client_id'];
    $client_secret = $data_digitopay['client_secret'];

    if (!$url || !$client_secret) {
        echo json_encode(["success" => false, "message" => "Credenciais Digitopay não encontradas."]);
        exit;
    }

    // Busca informações do PIX para Royalbenk
    $stmt = executePreparedQuery($mysqli, "SELECT realname, pix_id, flag FROM metodos_pagamentos WHERE id = ?", 'i', [$chavepix]);
    $stmt->bind_result($pix_realname, $pix_account, $pix_flag);
    $stmt->fetch();
    $stmt->close();

    $urlcashout = $url . 'withdraw';
    $payload = [
        "paymentOptions" => ["PIX"],
        "person" => [
            "pixKeyTypes" => FormatPixType($pix_flag),
            "pixKey" => $pix_account,
            "name" => "Jogador"
        ],
        "value" => 1 //(float) $valor
    ];


    // var_dump($urlcashout);
    function getToken()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        global $data_digitopay;

        $url = $data_digitopay['url'] . 'token/api';
        //var_dump($url);
        //exit;
        $header = array(
            'Content-Type: application/json'
        );

        $payload = array(
            "clientId" => $data_digitopay['client_id'],
            "secret" => $data_digitopay['client_secret']
        );

        $response = enviarRequest_PAYMENT($url, $header, $payload);
        $dados = json_decode($response, true);
        //var_dump($dados);
        return $dados['accessToken'];
    }

    $token = getToken();
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer " . $token,
        "Accept: application/json"
    ];

    // Inicializa o cURL
    $ch = curl_init();

    // Configurações do cURL
    curl_setopt($ch, CURLOPT_URL, $urlcashout); // Define a URL
    curl_setopt($ch, CURLOPT_IPRESOLVE,  CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_POST, true); // Define como POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload)); // Converte o payload para JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Adiciona os headers
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna a resposta em vez de imprimir
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignora verificação SSL (se necessário)
    curl_setopt($ch, CURLOPT_VERBOSE, true); // Ativa o modo detalhado
    curl_setopt($ch, CURLOPT_HEADER, true);  // Inclui os headers na resposta

    // Executa a requisição
    $response = curl_exec($ch);

    // Verifica erros
    if (curl_errno($ch)) {
        echo json_encode(["status" => "error",  "message" => 'Erro no cURL: ' . curl_error($ch)], true);
        exit;
    } else {
        $dados = json_decode($response, true);
        if (isset($dados['errors'])) {
            $res = [
                "status" => 'error',
                "data" => $dados['erros'][0]['errorMessage']
            ];

            echo json_encode($res, true);
            exit;
        }

        $res = [
            "status" => "success",
            "data" => "Pagamento realizado com sucesso!"
        ];

        echo json_encode($res, true);
        exit;
    }
}

function FormatPixType($type)
{
    switch ($type) {
        case 1:
            return 'EMAIL';
        case 2:
            return 'PHONE';
        case 3:
            return 'CPF';
        case 4:
            return 'CNPJ';
    }
}


echo json_encode(["success" => false, "message" => "Erro do gateway: não definido"]);
exit;
