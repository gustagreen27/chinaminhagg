<?php
session_start();
include_once('../ad-min/services/database.php');
include_once('../ad-min/services/funcao.php');
include_once('../ad-min/services/crud.php');
global $mysqli;

// Receber dados da solicitação POST JSON
$data = json_decode(file_get_contents("php://input"), true);

// Verificar se o JSON foi decodificado com sucesso
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    // Erro ao decodificar o JSON
    http_response_code(400); // Bad Request
    exit;
}

$idTransaction = PHP_SEGURO($data['idTransaction']);         // id da transação
$statusTransaction = NULL;

#====================================================================#
# Function GET TOKEN
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
$token = getToken();

//===================================================================#
function url_send()
{
    global $data, $token, $data_digitopay;

    // URL de SUA API
    $url = $data_digitopay['url'] . 'status/' . $data['id'];
    $header = array(
        'Content-Type:application/json',
        'Authorization: Bearer ' . $token,
    );
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resultado = curl_exec($ch);
    curl_close($ch);

    if (isset($resultado) && $resultado === 'REALIZADO') {
        $statusTransaction = $resultado;
        return $statusTransaction;
    } else {
        url_send();
    }
}
url_send();

#====================================================================#
function busca_valor_ipn($transacao_id)
{
    global $mysqli;
    $qry = "SELECT usuario, valor FROM transacoes WHERE transacao_id='" . $transacao_id . "'";
    $res = mysqli_query($mysqli, $qry);
    if (mysqli_num_rows($res) > 0) {
        $data = mysqli_fetch_assoc($res);
        $retornaUSER = get_user_by_id($data['usuario']);
        $retorna_insert_saldo_suit_pay = enviarSaldo($retornaUSER['mobile'], $data['valor']);
        return $retorna_insert_saldo_suit_pay;
    }
    return false;
}

function get_user_by_id($user_id)
{
    global $mysqli;
    $qry = "SELECT mobile FROM usuarios WHERE id = ?";
    $stmt = $mysqli->prepare($qry);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($mobile);
    $stmt->fetch();
    $stmt->close();
    if (!isset($mobile)) {
        return false;
    }
    return ['mobile' => $mobile];
}

#====================================================================#
function att_paymentpix($transacao_id)
{
    global $mysqli;
    $sql = $mysqli->prepare("UPDATE transacoes SET status='1' WHERE transacao_id=?");
    $sql->bind_param("s", $transacao_id);
    if ($sql->execute()) {
        $buscar = busca_valor_ipn($transacao_id);
        var_dump($buscar);
        if ($buscar) {
            $rf = 1;
        } else {
            $rf = 0;
        }
    } else {
        $rf = 0;
    }
    return $rf;
}

#====================================================================#
#01
if (isset($idTransaction) && $typeTransaction == "PIX" && $statusTransaction == "REALIZADO") {
    $att_transacao = att_paymentpix($idTransaction);
}
#====================================================================#
