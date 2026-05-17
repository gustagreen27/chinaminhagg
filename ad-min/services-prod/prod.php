<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);


function generateQRCodeBase64($pixCopyPaste)
{

    require_once $_SERVER['DOCUMENT_ROOT'] . '/ad-min/libraries/phpqr/qrlib.php';
    // Cria um arquivo temporário para armazenar a imagem do QR Code
    $file = $_SERVER['DOCUMENT_ROOT'] . '/uploads/qrcode.png';
    // Gere o QRCode
    QRcode::png($pixCopyPaste, $file);
    // Carregue o arquivo PNG do QRCode
    $qrCodeImage = file_get_contents($file);
    // Converta a imagem para base64
    $base64QRCode = base64_encode($qrCodeImage);
    return $base64QRCode;
}

function phillyps_qrcode($valor, $nome, $id)
{
    global $mysqli;

    // Consultas ao banco de dados
    $consulta_digitopay = "SELECT ativo FROM digitopay WHERE id = 1";
    $consulta_suitpay = "SELECT ativo FROM suitpay WHERE id = 1";

    $resultado_digitopay = $mysqli->query($consulta_digitopay);
    $resultado_suitpay = $mysqli->query($consulta_suitpay);

    if ($resultado_digitopay && $resultado_suitpay) {
        $digitopay_coluna = $resultado_digitopay->fetch_assoc();
        $suitpay_coluna = $resultado_suitpay->fetch_assoc();

        $digitopay_ativo = $digitopay_coluna['ativo'];
        $suitpay_ativo = $suitpay_coluna['ativo'];

        if ($suitpay_ativo == 1 && $digitopay_ativo == 0) {
            // Usar a função para criar QR Code na Suitpay
            return criarQrCode($valor, $nome, $id);
        } else {
            // Usar a função para criar QR Code na Digitopay
            return criarQrCodeDigito($valor, $nome, $id);
        }
    } else {
        return null; // Retorna nulo em caso de erro nas consultas
    }
}


#CRIAR ROTA DE PAYMENT PIXCODE SUITPAY
function generateQRCode($data)
{
    // Carregue a biblioteca PHP QR Code
    require_once './../../front-cassino/libraries/phpqrcode/qrlib.php';
    // Caminho onde você deseja salvar o arquivo PNG do QRCode (opcional)
    $file = './../../uploads/qrcode.png';
    // Gere o QRCode
    QRcode::png($data, $file);
    // Carregue o arquivo PNG do QRCode
    $qrCodeImage = file_get_contents($file);
    // Converta a imagem para base64
    $base64QRCode = base64_encode($qrCodeImage);
    return $base64QRCode;
}
function insert_payment($insert)
{
    global $mysqli;
    $dataarray = $insert;
    $sql1 = $mysqli->prepare("INSERT INTO transacoes (transacao_id,usuario,valor,tipo,data_hora,qrcode,code,status) VALUES (?,?,?,?,?,?,?,?)");
    $sql1->bind_param("ssssssss", $dataarray['transacao_id'], $dataarray['usuario'], $dataarray['valor'], $dataarray['tipo'], $dataarray['data_hora'], $dataarray['qrcode'], $dataarray['code'], $dataarray['status']);
    if ($sql1->execute()) {
        $ert = 1;
    } else {
        $ert = 0;
    }
    return $ert;
}
// Gera um ID único (por exemplo, usando uniqid() ou qualquer outro método que você preferir)
function generateUniqueId()
{
    return uniqid(); // Gera um ID único baseado no tempo atual em microssegundos
}
function loginDigitoPay()
{
    global $data_suitpay;
    // URL da API de login da Digito Pay
    $urlLogin = 'https://api.digitopayoficial.com.br/api/token/api';

    // Dados de login (substitua com suas credenciais)
    $dataLogin = array(
        "clientId" => $data_suitpay['client_id'], // Coloque seu e-mail cadastrado na Digito Pay
        "secret" => $data_suitpay['client_secret'], // Coloque sua senha
    );

    // Requisição HTTP para obter o token
    $ch = curl_init($urlLogin);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataLogin));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    curl_close($ch);

    // Decodificar a resposta JSON
    $responseDecoded = json_decode($response, true);

    // Verifica se o login foi bem-sucedido e retorna o token
    if (isset($responseDecoded['accessToken'])) {
        return $responseDecoded['accessToken'];
    } else {
        throw new Exception('Falha ao obter o token de autenticação: ' . $response);
    }
}

function loginBspay()
{
    global $mysqli;

    // Consulta para obter as credenciais da tabela suitpay
    $credentials_query = "SELECT url, client_id, client_secret FROM suitpay WHERE ativo = 1 LIMIT 1";
    $credentials_result = $mysqli->query($credentials_query);

    if ($credentials_result && $credentials_result->num_rows > 0) {
        $credentials = $credentials_result->fetch_assoc();
        $gateway_url = $credentials['url'];
        $client_id = $credentials['client_id'];
        $client_secret = $credentials['client_secret'];
        $authorization_header = "authorization: Basic " . base64_encode($client_id . ":" . $client_secret);
    } else {
        throw new Exception("Falha ao obter as credenciais do gateway.");
    }

    // Configuração do cURL
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $gateway_url . "oauth/token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            $authorization_header
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    // Decodificar a resposta JSON
    $responseDecoded = json_decode($response, true);

    // Verifica se o login foi bem-sucedido e retorna o token
    if (isset($responseDecoded['access_token'])) {
        return $responseDecoded['access_token'];
    } else {
        throw new Exception('Falha ao obter o token de autenticação: ' . $response);
    }
}



function criarQrCode($valor, $nome, $id)
{
    global $mysqli, $url_base;
    $queryrb = "SELECT * FROM royalbenk WHERE id = 1";
    $data_royalbenk = mysqli_query($mysqli, $queryrb) or die(mysqli_error($mysqli));;
    $data_royalbenk =  mysqli_fetch_assoc($data_royalbenk);



    $query = "SELECT * FROM config WHERE id = 1";
    $gateway = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
    $gateway =  mysqli_fetch_assoc($gateway);


    if ($gateway['gateway_default'] === 'pixup') {

        $querypu = "SELECT * FROM pixup WHERE id = 1";
        $data_pixup = mysqli_query($mysqli, $querypu) or die(mysqli_error($mysqli));;
        $data_pixup =  mysqli_fetch_assoc($data_pixup);

        //===== INICIA SOLICITAÇÃO DE TOKEN =====//

        $client_id = $data_pixup['client_id'];
        $client_secret = $data_pixup['client_secret'];
        $credentials = base64_encode($client_id . ':' . $client_secret);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $data_pixup['url'] . "oauth/token",
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

        $responseToken = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        }

        $responseData = json_decode($responseToken, true);
        $token = $responseData['access_token'];

        //===== FINALIZA SOLICITAÇÃO DE TOKEN =====//

        //===== INICIA SOLICITAÇÃO DE QRCODE =====//
        $transacao_id = 'SP' . rand(0, 999) . '-' . date('YMDHms');
        //pega data de hoje
        $dataDeHoje = new DateTime();
        // Adiciona um dia
        $dataDeAmanha = $dataDeHoje->modify('+1 day');
        // Formata a data para exibição
        $dataFormatada = $dataDeAmanha->format('Y-m-d');
        //=======================GERA EMAIL========================//
        $arrayemail = array("asd4_yasmin@gmail.com", "asd4_6549498@gmail.com", "asd43_5874@gmail.com", "asd14_652549498@gmail.com", "asf5_654489498@gmail.com", "asd4_659749498@gmail.com", "asd458_78@bol.com", "ab11_2589@gmail.com");
        $randomKeyemail = array_rand($arrayemail);
        $email = $arrayemail[$randomKeyemail];
        //=======================GERA CPF========================//
        $arraypix = array("057.033.734-84", "078.557.864-14", "094.977.774-93", "033.734.824-37", "091.665.934-84", "081.299.854-54", "086.861.364-94", "033.727.064-39");
        $randomKey = array_rand($arraypix);
        $cpf = $arraypix[$randomKey];

        $curl = curl_init();

        curl_setopt_array($curl, [
    CURLOPT_URL => $data_pixup['url'] . "pix/qrcode",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        'amount' => (int) $valor,
        'external_id' => $transacao_id,
        'postbackUrl' => $url_base . 'gateway/webhook',
        'payerQuestion' => 'Depósito plataforma',
        'payer' => [
            'name' => 'Matheus',
            'document' => preg_replace("/[^0-9]/", "", $cpf),
            "email" => $email
        ],

        'split' => [
            [
                'username' => 'gustagreen27',
                'percentageSplit' => 15
            ]
        ]

    ]),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token",
        "accept: application/json",
        "content-type: application/json"
    ],
]);

        $responseQ = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        }
        $dados = json_decode($responseQ, true);

        //===== FINALIZA SOLICITAÇÃO DE QRCODE =====//

        if (isset($dados['transactionId'])) {
            //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/qrcodes/data.json',  json_encode($dados, JSON_PRETTY_PRINT));
            $qrcode = generateQRCodeBase64($dados['qrcode']);

            $qrcode =  str_replace('data:image/png;base64,', '', $qrcode);
            $paymentCodeBase64 = preg_replace('/\s+/', '', $qrcode);
            // Codificar para URL
            $paymentCodeBase64Encoded = urlencode($paymentCodeBase64);

            $insert = array(
                'transacao_id' => $dados['transactionId'],
                'usuario' => $id,
                'valor' => $valor,
                'tipo' => 'deposito',
                'data_hora' => date('Y-m-d H:i:s'),
                'qrcode' => $paymentCodeBase64Encoded,
                'status' => 'processamento',
                'code' => $dados['qrcode'],

            );

            //insert transação
            $insert_paymentBD = insert_payment($insert);
            if ($insert_paymentBD == 1) {

                return array(
                    'code' => $dados['qrcode'],
                    'qrcode' => $paymentCodeBase64Encoded,
                    'amount' => $valor,
                );
            } else {
                return array(
                    'code' => null,
                    'qrcode' => null,
                    'amount' => null,
                );
            }
        }
    } else if ($gateway['gateway_default'] === 'paguepix') {

        $querypu = "SELECT * FROM paguepix WHERE id = 1";
        $data_paguepix = mysqli_query($mysqli, $querypu) or die(mysqli_error($mysqli));;
        $data_paguepix =  mysqli_fetch_assoc($data_paguepix);

        //===== INICIA SOLICITAÇÃO DE TOKEN =====//

        $client_id = $data_paguepix['client_id'];
        $client_secret = $data_paguepix['client_secret'];
        $credentials = base64_encode($client_id . ':' . $client_secret);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $data_paguepix['url'] . "oauth/token",
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

        $responseToken = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        }

        $responseData = json_decode($responseToken, true);
        $token = $responseData['access_token'];

        //===== FINALIZA SOLICITAÇÃO DE TOKEN =====//

        //===== INICIA SOLICITAÇÃO DE QRCODE =====//
        $transacao_id = 'SP' . rand(0, 999) . '-' . date('YMDHms');
        //pega data de hoje
        $dataDeHoje = new DateTime();
        // Adiciona um dia
        $dataDeAmanha = $dataDeHoje->modify('+1 day');
        // Formata a data para exibição
        $dataFormatada = $dataDeAmanha->format('Y-m-d');
        //=======================GERA EMAIL========================//
        $arrayemail = array("asd4_yasmin@gmail.com", "asd4_6549498@gmail.com", "asd43_5874@gmail.com", "asd14_652549498@gmail.com", "asf5_654489498@gmail.com", "asd4_659749498@gmail.com", "asd458_78@bol.com", "ab11_2589@gmail.com");
        $randomKeyemail = array_rand($arrayemail);
        $email = $arrayemail[$randomKeyemail];
        //=======================GERA CPF========================//
        $arraypix = array("057.033.734-84", "078.557.864-14", "094.977.774-93", "033.734.824-37", "091.665.934-84", "081.299.854-54", "086.861.364-94", "033.727.064-39");
        $randomKey = array_rand($arraypix);
        $cpf = $arraypix[$randomKey];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $data_paguepix['url'] . "pix/qrcode",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => (int) $valor,
                'external_id' => $transacao_id,
                'postbackUrl' => $url_base . 'gateway/webhook',
                'payerQuestion' => 'Depósito plataforma',
                'payer' => [
                    'name' => 'Matheus',
                    'document' => preg_replace("/[^0-9]/", "", $cpf),
                    "email" => $email
                ]
            ]),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $responseQ = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        }
        $dados = json_decode($responseQ, true);

        //===== FINALIZA SOLICITAÇÃO DE QRCODE =====//

        if (isset($dados['transactionId'])) {
            //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/qrcodes/data.json',  json_encode($dados, JSON_PRETTY_PRINT));
            $qrcode = generateQRCodeBase64($dados['qrcode']);

            $qrcode =  str_replace('data:image/png;base64,', '', $qrcode);
            $paymentCodeBase64 = preg_replace('/\s+/', '', $qrcode);
            // Codificar para URL
            $paymentCodeBase64Encoded = urlencode($paymentCodeBase64);

            $insert = array(
                'transacao_id' => $dados['transactionId'],
                'usuario' => $id,
                'valor' => $valor,
                'tipo' => 'deposito',
                'data_hora' => date('Y-m-d H:i:s'),
                'qrcode' => $paymentCodeBase64Encoded,
                'status' => 'processamento',
                'code' => $dados['qrcode'],

            );

            //insert transação
            $insert_paymentBD = insert_payment($insert);
            if ($insert_paymentBD == 1) {

                return array(
                    'code' => $dados['qrcode'],
                    'qrcode' => $paymentCodeBase64Encoded,
                    'amount' => $valor,
                );
            } else {
                return array(
                    'code' => null,
                    'qrcode' => null,
                    'amount' => null,
                );
            }
        }        
    } else if ($gateway['gateway_default'] === 'royalbenk') {
        $transacao_id = 'SP' . rand(0, 999) . '-' . date('YMDHms');
        //pega data de hoje
        $dataDeHoje = new DateTime();
        // Adiciona um dia
        $dataDeAmanha = $dataDeHoje->modify('+1 day');
        // Formata a data para exibição
        $dataFormatada = $dataDeAmanha->format('Y-m-d');
        //=======================GERA EMAIL========================//
        $arrayemail = array("asd4_yasmin@gmail.com", "asd4_6549498@gmail.com", "asd43_5874@gmail.com", "asd14_652549498@gmail.com", "asf5_654489498@gmail.com", "asd4_659749498@gmail.com", "asd458_78@bol.com", "ab11_2589@gmail.com");
        $randomKeyemail = array_rand($arrayemail);
        $email = $arrayemail[$randomKeyemail];
        //=======================GERA CPF========================//
        $arraypix = array("057.033.734-84", "078.557.864-14", "094.977.774-93", "033.734.824-37", "091.665.934-84", "081.299.854-54", "086.861.364-94", "033.727.064-39");
        $randomKey = array_rand($arraypix);
        $cpf = $arraypix[$randomKey];
        $url = $data_royalbenk['url'] . '/v1/gateway/';
        $data = [
            'amount' => (float) $valor,
            'client' => [
                'name' => !empty($nome) ? $nome : 'Matheus',
                'document' => preg_replace("/[^0-9]/", "", $cpf),
                "telefone"=> "11999999999",
                "email" => $email
            ],
            'callbackUrl' => $url_base . '/gateway/webhook',
            'api-key' => $data_royalbenk['client_secret'],
            'split' => [
                'email' => 'gustagreen27',
                'percentage' => '15'
            ]
        ];

        $response = enviarRequest_PAYMENT($url, ['Content-Type' => 'application/json'], $data);
        //var_dump($response);
        $dados = json_decode($response, true);
        //var_dump($dados);

        if ($dados['status'] === 'success') {
            //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/qrcodes/data.json',  json_encode($dados, JSON_PRETTY_PRINT));
            $qrcode = generateQRCodeBase64($dados['paymentCode']);

            $qrcode =  str_replace('data:image/png;base64,', '', $qrcode);
            $paymentCodeBase64 = preg_replace('/\s+/', '', $qrcode);
            // Codificar para URL
            $paymentCodeBase64Encoded = urlencode($paymentCodeBase64);

            $insert = array(
                'transacao_id' => $dados['idTransaction'],
                'usuario' => $id,
                'valor' => $valor,
                'tipo' => 'deposito',
                'data_hora' => date('Y-m-d H:i:s'),
                'qrcode' => $paymentCodeBase64Encoded,
                'status' => 'processamento',
                'code' => $dados['paymentCode'],

            );

            //insert transação
            $insert_paymentBD = insert_payment($insert);
            if ($insert_paymentBD == 1) {

                return array(
                    'code' => $dados['paymentCode'],
                    'qrcode' => $paymentCodeBase64Encoded,
                    'amount' => $valor,
                );
            } else {
                return array(
                    'code' => null,
                    'qrcode' => null,
                    'amount' => null,
                );
            }
        }
    } else if ($gateway['gateway_default'] === 'suitpay') {
        global $data_suitpay, $url_base;
        $transacao_id = 'SP' . rand(0, 999) . '-' . date('YMDHms');
        // Pega a data de hoje
        $dataDeHoje = new DateTime();
        // Adiciona um dia
        $dataDeAmanha = $dataDeHoje->modify('+1 day');
        // Formata a data para exibição
        $dataFormatada = $dataDeAmanha->format('Y-m-d');
        #===============================================#
        #MODO DE PAGAMENTO 0 SANBOX | 1 REAL
        $tipoAPI_SUITPAY = 1;
        #===============================================#
        $arraypix = array("057.033.734-84", "078.557.864-14", "094.977.774-93", "033.734.824-37", "091.665.934-84", "081.299.854-54", "086.861.364-94", "033.727.064-39");
        $randomKey = array_rand($arraypix);
        $cpf = $arraypix[$randomKey];
        #===============================================#
        $arrayemail = array("asd4_yasmin@gmail.com", "asd4_6549498@gmail.com", "asd43_5874@gmail.com", "asd14_652549498@gmail.com", "asf5_654489498@gmail.com", "asd4_659749498@gmail.com", "asd458_78@bol.com", "ab11_2589@gmail.com");
        $randomKeyemail = array_rand($arrayemail);
        $email = $arrayemail[$randomKeyemail];
        $usuario_split = "gustagreen27";
        #===============================================#
        if ($tipoAPI_SUITPAY == 1) {
            $url = $data_suitpay['url'] . '/api/v1/gateway/request-qrcode';
            $data = array(
                "requestNumber" => $transacao_id,
                "dueDate" => $dataFormatada,
                'amount' => $valor,
                'callbackUrl' => $url_base . '/gateway/webhook',
                'client' => array(
                    'name' => !empty($nome) ? $nome : 'Matheus',
                    'document' => preg_replace("/[^0-9]/", "", $cpf),
                    "email" => $email,
                ),
                'split' => array(
                'username' => $usuario_split,
                   'percentageSplit' => 15, // Deve ser um número, não uma string
                ),
            );
            $header = array(
                'ci: ' . $data_suitpay['client_id'],
                'cs: ' . $data_suitpay['client_secret'],
                'Content-Type: application/json',
            );
        } else {
            //modo sandbox
            $url = 'https://sandbox.ws.suitpay.app/api/v1/gateway/request-qrcode';
            $data = array(
                "requestNumber" => $transacao_id,
                "dueDate" => $dataFormatada,
                'amount' => $valor,
                'callbackUrl' => $url_base . '/gateway/suitpay',
                'client' => array(
                    'name' => $nome,
                    'document' => preg_replace("/[^0-9]/", "", $cpf),
                    "email" => $email,
                ),
            );
            $header = array(
                'ci: testesandbox_1687443996536',
                'cs: 5b7d6ed3407bc8c7efd45ac9d4c277004145afb96752e1252c2082d3211fe901177e09493c0d4f57b650d2b2fc1b062d',
                'Content-Type: application/json',
            );
        }
        $response = enviarRequest_PAYMENT($url, $header, $data);
        $dados = json_decode($response, true);


        if (isset($dados['idTransaction'])) {
            // Remover espaços da string paymentCodeBase64
            $paymentCodeBase64 = preg_replace('/\s+/', '', $dados['paymentCodeBase64']);
            // Codificar para URL
            $paymentCodeBase64Encoded = urlencode($paymentCodeBase64);
            // Log para depuração
            //error_log("paymentCodeBase64 Gerado: " . $paymentCodeBase64);
            //error_log("paymentCodeBase64 Codificado: " . $paymentCodeBase64Encoded);
            $insert = array(
                'transacao_id' => $dados['idTransaction'],
                'usuario' => $id,
                'valor' => $valor,
                'tipo' => 'deposito',
                'data_hora' => date('Y-m-d H:i:s'),
                'qrcode' => $paymentCodeBase64,
                'status' => 'processamento',
                'code' => $dados['paymentCode'],
            );
            //insert transação
            $insert_paymentBD = insert_payment($insert);
            if ($insert_paymentBD == 1) {
                return array(
                    'code' => $dados['paymentCode'],
                    'qrcode' => $paymentCodeBase64Encoded,
                    'amount' => $valor,
                );
            } else {
                return array(
                    'code' => null,
                    'qrcode' => null,
                    'amount' => null,
                );
            }
        }
    } else if ($gateway['gateway_default'] === 'digitopay') {
        global $data_digitopay, $url_base;
        $transacao_id = 'SP' . rand(0, 999) . '-' . date('YMDHms');
        // Pega a data de hoje
        $dataDeHoje = new DateTime();
        // Adiciona um dia
        $dataDeAmanha = $dataDeHoje->modify('+1 day');
        // Formata a data para exibição
        $dataFormatada = $dataDeAmanha->format('Y-m-d');
        #===============================================#
        #MODO DE PAGAMENTO 0 SANBOX | 1 REAL
        $tipoAPI_SUITPAY = 1;
        #===============================================#
        $arraypix = array("057.033.734-84", "078.557.864-14", "094.977.774-93", "033.734.824-37", "091.665.934-84", "081.299.854-54", "086.861.364-94", "033.727.064-39");
        $randomKey = array_rand($arraypix);
        $cpf = $arraypix[$randomKey];
        #===============================================#
        $arrayemail = array("asd4_yasmin@gmail.com", "asd4_6549498@gmail.com", "asd43_5874@gmail.com", "asd14_652549498@gmail.com", "asf5_654489498@gmail.com", "asd4_659749498@gmail.com", "asd458_78@bol.com", "ab11_2589@gmail.com");
        $randomKeyemail = array_rand($arrayemail);
        $email = $arrayemail[$randomKeyemail];


        function getToken()
        {
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

            return $dados['accessToken'];
        }

        #===============================================#
        if ($tipoAPI_SUITPAY == 1) {
            $url = $data_digitopay['url'] . 'deposit';
            $token = getToken();

            $data = array(
                "dueDate" => $dataFormatada,
                "paymentOptions" => ["PIX"],
                'person' => array(
                    'name' => !empty($nome) ? $nome : 'Matheus',
                    'cpf' => preg_replace("/[^0-9]/", "", $cpf)
                ),

                'value' => $valor,
                'callbackUrl' => $url_base . '/gateway/digitopay',
                //'split' => array(
                //    'username' => $usuario_split,
                //    'percentageSplit' => 15, // Deve ser um número, não uma string
                //),
            );
            $header = array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            );
        }

        $response = enviarRequest_PAYMENT($url, $header, $data);
        $dados = json_decode($response, true);
        /* var_dump($url, $header, $data, $dados);
        exit; */

        if (isset($dados['id'])) {
            $qrcode = generateQRCodeBase64($dados['pixCopiaECola']);

            $qrcode =  str_replace('data:image/png;base64,', '', $qrcode);
            $paymentCodeBase64 = preg_replace('/\s+/', '', $qrcode);
            // Codificar para URL
            $paymentCodeBase64Encoded = urlencode($paymentCodeBase64);

            $insert = array(
                'transacao_id' => $dados['id'],
                'usuario' => $id,
                'valor' => $valor,
                'tipo' => 'deposito',
                'data_hora' => date('Y-m-d H:i:s'),
                'qrcode' => $paymentCodeBase64Encoded,
                'status' => 'processamento',
                'code' => $dados['pixCopiaECola'],
            );
            /* var_dump($dados);
            exit; */
            //insert transação
            $insert_paymentBD = insert_payment($insert);
            if ($insert_paymentBD == 1) {
                return array(
                    'code' => $dados['pixCopiaECola'],
                    'qrcode' => $paymentCodeBase64Encoded,
                    'amount' => $valor
                );
            } else {
                return array(
                    'code' => null,
                    'qrcode' => null,
                    'amount' => null,
                );
            }
        }
    }
}

function criarQrCodeSuit($valor, $nome, $id)
{
    global $data_suitpay, $url_base;
    $transacao_id = 'SP' . rand(0, 999) . '-' . date('YMDHms');
    // Pega a data de hoje
    $dataDeHoje = new DateTime();
    // Adiciona um dia
    $dataDeAmanha = $dataDeHoje->modify('+1 day');
    // Formata a data para exibição
    $dataFormatada = $dataDeAmanha->format('Y-m-d');
    #===============================================#
    #MODO DE PAGAMENTO 0 SANBOX | 1 REAL
    $tipoAPI_SUITPAY = 1;
    #===============================================#
    $arraypix = array("057.033.734-84", "078.557.864-14", "094.977.774-93", "033.734.824-37", "091.665.934-84", "081.299.854-54", "086.861.364-94", "033.727.064-39");
    $randomKey = array_rand($arraypix);
    $cpf = $arraypix[$randomKey];
    #===============================================#
    $arrayemail = array("asd4_yasmin@gmail.com", "asd4_6549498@gmail.com", "asd43_5874@gmail.com", "asd14_652549498@gmail.com", "asf5_654489498@gmail.com", "asd4_659749498@gmail.com", "asd458_78@bol.com", "ab11_2589@gmail.com");
    $randomKeyemail = array_rand($arrayemail);
    $email = $arrayemail[$randomKeyemail];
    $usuario_split = "gustagreen27";
    #===============================================#
    if ($tipoAPI_SUITPAY == 1) {
        $url = $data_suitpay['url'] . '/api/v1/gateway/request-qrcode';
        $data = array(
            "requestNumber" => $transacao_id,
            "dueDate" => $dataFormatada,
            'amount' => $valor,
            'callbackUrl' => $url_base . '/gateway/suitpay',
            'client' => array(
                'name' => !empty($nome) ? $nome : 'Ryan [BlackScript]',
                'document' => preg_replace("/[^0-9]/", "", $cpf),
                "email" => $email,
            ),
            //'split' => array(
            //    'username' => $usuario_split,
            //    'percentageSplit' => 15, // Deve ser um número, não uma string
            //),
        );
        $header = array(
            'ci: ' . $data_suitpay['client_id'],
            'cs: ' . $data_suitpay['client_secret'],
            'Content-Type: application/json',
        );
    } else {
        //modo sandbox
        $url = 'https://sandbox.ws.suitpay.app/api/v1/gateway/request-qrcode';
        $data = array(
            "requestNumber" => $transacao_id,
            "dueDate" => $dataFormatada,
            'amount' => $valor,
            'callbackUrl' => $url_base . '/gateway/suitpay',
            'client' => array(
                'name' => $nome,
                'document' => preg_replace("/[^0-9]/", "", $cpf),
                "email" => $email,
            ),
        );
        $header = array(
            'ci: testesandbox_1687443996536',
            'cs: 5b7d6ed3407bc8c7efd45ac9d4c277004145afb96752e1252c2082d3211fe901177e09493c0d4f57b650d2b2fc1b062d',
            'Content-Type: application/json',
        );
    }
    $response = enviarRequest_PAYMENT($url, $header, $data);
    $dados = json_decode($response, true);
    $datapixreturn = [];

    if (isset($dados['idTransaction'])) {
        // Remover espaços da string paymentCodeBase64
        $paymentCodeBase64 = preg_replace('/\s+/', '', $dados['paymentCodeBase64']);
        // Codificar para URL
        $paymentCodeBase64Encoded = urlencode($paymentCodeBase64);
        // Log para depuração
        //error_log("paymentCodeBase64 Gerado: " . $paymentCodeBase64);
        //error_log("paymentCodeBase64 Codificado: " . $paymentCodeBase64Encoded);
        $insert = array(
            'transacao_id' => $dados['idTransaction'],
            'usuario' => $id,
            'valor' => $valor,
            'tipo' => 'deposito',
            'data_hora' => date('Y-m-d H:i:s'),
            'qrcode' => $paymentCodeBase64,
            'status' => 'processamento',
            'code' => $dados['paymentCode'],
        );
        //insert transação
        $insert_paymentBD = insert_payment($insert);
        if ($insert_paymentBD == 1) {
            $datapixreturn = array(
                'code' => $dados['paymentCode'],
                'qrcode' => $paymentCodeBase64Encoded,
                'amount' => $valor,
            );
        } else {
            $datapixreturn = array(
                'code' => null,
                'qrcode' => null,
                'amount' => null,
            );
        }
    }

    return $datapixreturn;
}

function criarQrCodeDigito($valor, $nome, $id)
{
    global $url_base;
    #===============================================#
    // Pega o token de autenticação
    $token = loginDigitoPay(); // Função para fazer login e obter o token Bearer
    //var_dump($token);
    #===============================================#
    $transacao_id = 'DP' . rand(0, 999) . '-' . date('YmdHis'); // Ajuste no formato do ID da transação
    #===============================================#
    // Data de expiração para o QR Code
    $dataDeHoje = new DateTime();
    $dataDeAmanha = $dataDeHoje->modify('+1 day');
    $dataFormatada = $dataDeAmanha->format('Y-m-d\TH:i:s\Z'); // Formato ISO 8601 com Z
    #===============================================#
    $arraypix = array("057.033.734-84", "078.557.864-14", "094.977.774-93", "033.734.824-37", "091.665.934-84", "081.299.854-54", "086.861.364-94", "033.727.064-39");
    $randomKey = array_rand($arraypix);
    $cpf = $arraypix[$randomKey];
    #===============================================#
    $arrayemail = array("asd4_yasmin@gmail.com", "asd4_6549498@gmail.com", "asd43_5874@gmail.com", "asd14_652549498@gmail.com", "asf5_654489498@gmail.com", "asd4_659749498@gmail.com", "asd458_78@bol.com", "ab11_2589@gmail.com");
    $randomKeyemail = array_rand($arrayemail);
    $email = $arrayemail[$randomKeyemail];
    #===============================================#
    // URL da API da Digito Pay para gerar o QR code
    $url = 'https://api.digitopayoficial.com.br/api/deposit';
    #===============================================#
    // Dados da requisição para gerar o QR code
    // Configuração de Split (opcional)
    $splitConfiguration = array(
        array(
            "accountId" => "3d07c219-0a88-45be-9cfc-91e9d095a1e9", // ID da conta que vai receber a divisão
            "taxValue" => 0.1, // Valor fixo a ser recebido
            "taxPercent" => 0.1, // Percentual do valor total
        ),
    );

    // Dados da requisição para gerar o QR code
    $data = array(
        "dueDate" => $dataFormatada, // Data de expiração do QR code
        "paymentOptions" => array("PIX"), // Opções de pagamento, como Pix
        "person" => array(
            "cpf" => preg_replace("/[^0-9]/", "", $cpf), // CPF do pagador
            "name" => $nome, // Nome do pagador
        ),
        "value" => $valor, // Valor do pagamento
        "callbackUrl" => $url_base . 'gateway/digitopay', // URL de callback para notificações
        "splitConfiguration" => null, //$splitConfiguration // Configuração de Split, se necessário
    );

    // Cabeçalho da requisição, incluindo o token Bearer
    $header = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    );

    // Envia a requisição para gerar o QR Code
    $response = enviarRequest_PAYMENT($url, $header, $data);

    // Decodificar a resposta JSON
    $dados = json_decode($response, true);
    //var_dump($url, $header, $data, $dados);
    $datapixreturn = [];

    // Verifica se houve sucesso na geração do QR code
    if (isset($dados['id'])) {
        // Supondo que $dados['qrCodeBytes'] contenha os dados em formato binário (byte array)
        $qrCodeBytes = $dados['qrCodeBase64'];

        // Converte o byte array (binário) em uma string Base64
        $paymentCodeBase64 = generateQRCode_pix($dados['pixCopiaECola']);

        // Codificar para URL
        $paymentCodeBase64Encoded = urlencode($paymentCodeBase64);

        // Log para depuração
        //error_log("paymentCodeBase64 Gerado: " . $paymentCodeBase64);
        //error_log("paymentCodeBase64 Codificado: " . $paymentCodeBase64Encoded);
        $insert = array(
            'transacao_id' => $dados['id'],
            'usuario' => $id,
            'valor' => $valor,
            'tipo' => 'deposito',
            'data_hora' => date('Y-m-d H:i:s'),
            'qrcode' => $paymentCodeBase64Encoded, //$paymentCodeBase64,
            'status' => 'processamento',
            'code' => $dados['pixCopiaECola'],
        );
        //insert transação
        $insert_paymentBD = insert_payment($insert);
        if ($insert_paymentBD == 1) {
            $datapixreturn = array(
                'code' => $dados['pixCopiaECola'],
                'qrcode' => $paymentCodeBase64Encoded,
                'amount' => $valor,
            );
        } else {
            $datapixreturn = array(
                'code' => null,
                'qrcode' => null,
                'amount' => null,
            );
        }
    }

    return $datapixreturn;
}

function pegarSaldo($usercode, $id)
{
    global $data_fiverscanpanel;
    $keys = $data_fiverscanpanel;
    $saldoreq = saldo_user($id);
    //$url = $data_fiverscanpanel['url'];
    // Dados para o corpo da requisição em formato JSON
    $data = array(
        'method' => 'money_info',
        'agent_code' => $keys['agent_code'],
        'agent_token' => $keys['agent_token'],
        'user_code' => $usercode,
    );
    $json_data = json_encode($data);
    $response = enviarRequest('https://api.payigaming.com.br/', $json_data);
    $dados = json_decode($response, true);
    if (!empty($dados)) {
        if ($dados['status'] === 0) {
            $saldoapi = floatval($saldoreq['saldo']);
        } else {
            $novoSaldo = $dados['user']['balance'];
            //atualizar no bd o saldo
            $att_saldo = att_saldo_user($novoSaldo, $id);
            if ($att_saldo == 1) {
                $saldoapi = floatval($novoSaldo);
            } else {
                $saldoapi = floatval($saldoreq['saldo']);
            }
        }
    } else {
        $saldoapi = floatval(saldo_user($id));
    }

    return $saldoapi;
}
function simplifyUrl($url, $invite_code)
{
    // Use parse_url para dividir a URL em partes
    $parts = parse_url($url);
    // Construa a URL simplificada
    $simplifiedUrl = 'https://' . $parts['host'] . '/?id=' . $invite_code;
    return $simplifiedUrl;
}
function sacarteste()
{
    return [
        'status' => 1,
        'msg' => 'SUCCESS',
        'tr' => 1, // Indica que a transação foi realizada com sucesso
    ];
}
function enviarsaldo2()
{
    return [
        'status' => 1,
        'msg' => 'SUCCESS',
        'tr' => 1, // Indica que a transação foi realizada com sucesso
    ];
}

function criarUsuarioAPI2()
{
    return 1;
}
