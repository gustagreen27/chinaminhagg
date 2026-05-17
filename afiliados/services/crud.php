<?php
date_default_timezone_set('America/Sao_Paulo');
include_once($_SERVER['DOCUMENT_ROOT'] . '/ad-min/services/database.php');

$pasta_url = '/';
//=======================================#

// Função para detectar se a conexão é HTTPS
function pega_http_https()
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
}

// Função para gerar a URL base do sistema
function url_sistema()
{
    global $pasta_url;
    $protocol = pega_http_https();
    $system_url = $protocol . "://" . filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL) . $pasta_url;
    return rtrim($system_url, '/');
}

$urlsistema = url_sistema();

#=====================================================#
# DATA CONFIG
function data_config()
{
    global $mysqli;
    $qry = "SELECT * FROM config WHERE id=1";
    $res = mysqli_query($mysqli, $qry);
    $data = mysqli_fetch_assoc($res);
    return $data;
}
$dataconfig = data_config();

#=====================================================#
# DATA AFILIADOS CONFIG
function data_afiliados_config()
{
    global $mysqli;
    $qry = "SELECT * FROM afiliados_config WHERE id=1";
    $res = mysqli_query($mysqli, $qry);
    $data = mysqli_fetch_assoc($res);
    return $data;
}
$afiliadosconfig = data_afiliados_config();

#=====================================================#
# DATA USER
function data_user()
{
    global $pdo;

    session_start();
    $token = $_SESSION['auth_token'];
    global $mysqli;
    $query = "SELECT id, mobile, invite_code, saldo_afiliados FROM usuarios WHERE token = ? AND banido = 0";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();


    $querycnf = "SELECT minResgate FROM afiliados_config WHERE id = :id";
    $stmtcnf = $pdo->prepare($querycnf);
    $stmtcnf->bindValue(':id', 1); // Use bindValue instead of bindParam
    $stmtcnf->execute();
    $config = $stmtcnf->fetch(PDO::FETCH_ASSOC);

    $restResgate = $config['minResgate'] - $data['saldo_afiliados'];
    $disableResgate = $restResgate > 0 ? 1 : 0;

    $data['min_resgate'] = $config['minResgate'];
    $data['rest_resgate'] = $restResgate;
    $data['disabled_resgate'] = $disableResgate;

    return $data;
}
$datauser = data_user();

#=====================================================#
# DATA USER
function data_subordinados()
{
    global $datauser, $mysqli, $pdo;
    session_start();

    $invite_code = $datauser['invite_code'];
    $query = "SELECT COUNT(*) as total FROM usuarios WHERE invitation_code = ?";

    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $invite_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        $totalSaldo = 0;

        $queryu = "SELECT * FROM usuarios WHERE invitation_code = ?";

        $stmtu = $mysqli->prepare($queryu);
        $stmtu->bind_param("s", $invite_code);
        $stmtu->execute();
        $resultu = $stmtu->get_result();

        // Initialize an array to hold all users
        $usuarios = [];

        // Loop through all results
        while ($row = $resultu->fetch_assoc()) {
            // Add each row (user) to the $usuarios array
            $usuarios[] = $row;
        }

        foreach ($usuarios as $usuario) {

            $qr = "SELECT saldo FROM usuarios WHERE id = ?";
            $stmts = $mysqli->prepare($qr);
            $stmts->bind_param("i", $usuario['id']);  // "i" indicates the parameter is an integer
            $stmts->execute();
            $result = $stmts->get_result();

            // Fetch the result
            $datau = $result->fetch_assoc();

            // Access the saldo value
            $saldo = $datau['saldo'];
            $totalSaldo += (int) $saldo;
        }



        // Retorna o número de registros encontrados
        return [
            'cadastros' => $data ? (int)$data['total'] : 0,
            'saldo' => $totalSaldo,
        ];
    } else {
        // Handle query preparation error
        return 0;
    }
}
$subordinados = data_subordinados();

#=====================================================#
# DATA SUBORDINADOS LV2
function data_sub_subordinados()
{
    global $datauser, $mysqli, $pdo;
    session_start();

    $invite_code = $datauser['invite_code'];

    // Get all direct affiliates
    $querys = "SELECT * FROM usuarios WHERE invitation_code = ?";
    $stmts = $mysqli->prepare($querys);
    $stmts->bind_param("s", $invite_code);
    $stmts->execute();
    $results = $stmts->get_result();

    $totalSaldo = 0;

    while ($afiliado = $results->fetch_assoc()) {
        // Get sub-affiliates for each affiliate
        $queryu = "SELECT * FROM usuarios WHERE invitation_code = ?";
        $stmtu = $mysqli->prepare($queryu);
        $stmtu->bind_param("s", $afiliado['invite_code']);
        $stmtu->execute();
        $subafiliados = $stmtu->get_result();

        while ($subafiliado = $subafiliados->fetch_assoc()) {
            // Get the first deposit of the sub-affiliate
            $query = "SELECT * FROM transacoes WHERE usuario = :usuario AND tipo = 'deposito' ORDER BY id ASC LIMIT 1";
            $stmt = $pdo->prepare($query);
            $usuario_id = $subafiliado['id']; // Create a variable to use with bindParam
            $stmt->bindValue(':usuario', $usuario_id); // Use bindValue instead of bindParam
            $stmt->execute();
            $primeiroDeposito = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($primeiroDeposito) {
                // Get affiliate configuration
                $querycnf = "SELECT cpaLvl2 FROM afiliados_config WHERE id = :id";
                $stmtcnf = $pdo->prepare($querycnf);
                $stmtcnf->bindValue(':id', 1); // Use bindValue instead of bindParam
                $stmtcnf->execute();
                $config = $stmtcnf->fetch(PDO::FETCH_ASSOC);

                // Calculate commission
                $comissaoCpa = $primeiroDeposito['valor'] * $config['cpaLvl2'] / 100;
                $totalSaldo += $comissaoCpa;
            }
        }
    }

    return $totalSaldo;
}
$subsubordinados = data_sub_subordinados();

function subs()
{
    global $datauser, $mysqli, $pdo;
    session_start();
    $invite_code = $datauser['invite_code'];

    // Query to fetch users by invitation code
    $queryu = "SELECT * FROM usuarios WHERE invitation_code = ?";
    $stmtu = $mysqli->prepare($queryu);
    $stmtu->bind_param("s", $invite_code);
    $stmtu->execute();
    $resultu = $stmtu->get_result();

    // Initialize an array to hold all users
    $usuarios = [];

    while ($row = $resultu->fetch_assoc()) {
        // Query to get the first deposit of each user
        $query = "SELECT * FROM transacoes WHERE usuario = :usuario AND tipo = 'deposito' ORDER BY id ASC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':usuario', $row['id']);
        $stmt->execute();
        $primeiroDeposito = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($primeiroDeposito) {
            // Query to count all deposits for the user
            $queryaf = "SELECT COUNT(*) AS total FROM transacoes WHERE usuario = :usuario AND tipo = 'deposito'";
            $stmtaf = $pdo->prepare($queryaf);
            $stmtaf->bindParam(':usuario', $primeiroDeposito['usuario']);
            $stmtaf->execute();
            $totalDepositos = $stmtaf->fetchColumn();

            // Query to get affiliate configuration
            $querycnf = "SELECT cpaLvl1 FROM afiliados_config WHERE id = 1";
            $stmtcnf = $pdo->prepare($querycnf);
            $stmtcnf->execute();
            $config = $stmtcnf->fetch(PDO::FETCH_ASSOC);

            // Calculate commission
            $comissaoCpa = $primeiroDeposito['valor'] * ($config['cpaLvl1'] / 100);
            $date = explode(' ', $primeiroDeposito['data_hora']);
            $time = $date[1];
            $date = explode('-', $date[0]);
            $datetime = (int)$date[2] - 1 . '/' . $date[1] . '/' . $date[0] . ' ' . $time;

            // Add user data to the array
            $usuarios[] = [
                'real_name' => $row['real_name'],
                'tipo' => 'CPA',
                'depositos' => $primeiroDeposito['valor'],
                'comissao' => $comissaoCpa,
                'status' => $primeiroDeposito['status'] === 'processamento' ? 'aguardando' : $primeiroDeposito['status'],
                'data' => $datetime,
            ];
        }
    }

    return $usuarios;
}
$datasubs = subs();

function subsubs()
{
    global $mysqli, $pdo, $datauser;
    session_start();
    $invite_code = $datauser['invite_code'];
    // Query to fetch users by invitation code
    $queryaf1 = "SELECT * FROM usuarios WHERE invitation_code = ?";
    $stmtaf1 = $mysqli->prepare($queryaf1);
    $stmtaf1->bind_param("s", $invite_code);
    $stmtaf1->execute();
    $resultaf1 = $stmtaf1->get_result();

    foreach ($resultaf1 as $afl) {

        $invite_code = $afl['invite_code'];

        // Query to fetch users by invitation code
        $queryu = "SELECT * FROM usuarios WHERE invitation_code = ?";
        $stmtu = $mysqli->prepare($queryu);
        $stmtu->bind_param("s", $invite_code);
        $stmtu->execute();
        $resultu = $stmtu->get_result();

        // Initialize an array to hold all users
        $usuarios = [];

        while ($row = $resultu->fetch_assoc()) {
            // Query to get the first deposit of each user
            $query = "SELECT * FROM transacoes WHERE usuario = :usuario AND tipo = 'deposito' ORDER BY id ASC LIMIT 1";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':usuario', $row['id']);
            $stmt->execute();
            $primeiroDeposito = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($primeiroDeposito) {
                // Query to count all deposits for the user
                $queryaf = "SELECT COUNT(*) AS total FROM transacoes WHERE usuario = :usuario AND tipo = 'deposito'";
                $stmtaf = $pdo->prepare($queryaf);
                $stmtaf->bindParam(':usuario', $primeiroDeposito['usuario']);
                $stmtaf->execute();
                $totalDepositos = $stmtaf->fetchColumn();

                // Query to get affiliate configuration
                $querycnf = "SELECT cpaLvl2 FROM afiliados_config WHERE id = 1";
                $stmtcnf = $pdo->prepare($querycnf);
                $stmtcnf->execute();
                $config = $stmtcnf->fetch(PDO::FETCH_ASSOC);

                // Calculate commission
                $comissaoCpa = $primeiroDeposito['valor'] * ($config['cpaLvl2'] / 100);
                $date = explode(' ', $primeiroDeposito['data_hora']);
                $time = $date[1];
                $date = explode('-', $date[0]);
                $datetime =  $date[2] . '/' . $date[1] . '/' . $date[0] . ' ' . $time;

                // Add user data to the array
                $usuarios[] = [
                    'real_name' => $row['real_name'],
                    'tipo' => 'CPA',
                    'depositos' => $primeiroDeposito['valor'],
                    'comissao' => $comissaoCpa,
                    'status' => $primeiroDeposito['status'] === 'processamento' ? 'aguardando' : $primeiroDeposito['status'],
                    'data' => $datetime,
                ];
            }
        }
    }

    return $usuarios;
}
$datasubafl = subsubs();


function meusSaques()
{
    global $mysqli, $pdo, $datauser;
    session_start();
    $id = $datauser['id'];

    $query = "SELECT * FROM solicitacao_saques WHERE tipo_saque = 1 AND id_user = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $saques = $stmt->get_result();

    $todos = [];

    foreach ($saques as $saque) {
        $querypix = "SELECT pix_id, pix_account, realname FROM metodos_pagamentos WHERE id = :id";
        $stmtpix = $pdo->prepare($querypix);
        $stmtpix->bindParam('id', $saque['pix']);
        $stmtpix->execute();
        $pix = $stmtpix->fetch(PDO::FETCH_ASSOC);

        $date = explode(' ',  $saque['data_att']);
        $time = $date[1];
        $date = explode('-', $date[0]);
        $datetime = (int)$date[2] - 1 . '/' . $date[1] . '/' . $date[0] . ' ' . $time;


        $todos[] = [
            'transacao_id' => $saque['transacao_id'],
            'valor' => $saque['valor'],
            'data' => $saque['data_att'] ? $datetime  : '-',
            'pix_name' => $pix['realname'],
            'pix_account' => $pix['pix_account'] ?? ofuscaCpf($pix['pix_id']),
            'status' => $saque['status']
        ];
    }

    return $todos;
}

function ofuscaCpf($numero)
{
    // Pegando os 3 primeiros e os 3 últimos dígitos
    $primeiros = substr($numero, 0, 3);
    $ultimos = substr($numero, -2);

    // Calculando a quantidade de dígitos intermediários
    $tamanhoIntermediario = strlen($numero) - 5;

    // Substituindo os dígitos intermediários por *
    $mascarados = str_repeat('*', $tamanhoIntermediario);

    // Concatenando o resultado final
    $resultado = $primeiros . '.' . $mascarados . '-' . $ultimos;

    return $resultado;
}

$meussques = meusSaques();

function logout()
{
    session_destroy();
    unset($_SESSION['auth_token']);
    header('Location: /afiliados/login');
    exit();
}
