<?php
include_once "./../../ad-min/services/crud.php";
global $mysqli;

$response = array();



if (isset($_POST['id'])) {
    $tid = $_POST['id'];
    $gatdef = $_POST['gateway_default'];

    if ($gatdef === 'digitopay') {
        global $data_digitopay, $tokendigitopay;


        if (verifyStatus($tid)) {
            if (!verifyPaid($tid)) {
                $result = att_paymentpix($tid);

                if ($result == 1) {
                    $response['status'] = 'success';
                    $response['message'] = 'Pagamento atualizado com sucesso';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Erro ao atualizar pagamento';
                }
            }
        }
        echo json_encode($response);
        exit;
    }
    // Chama a função att_paymentpix com o $tid
    /* $result = att_paymentpix($tid);

    if ($result == 1) {
        $response['status'] = 'success';
        $response['message'] = 'Pagamento atualizado com sucesso';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Erro ao atualizar pagamento';
    } */
} else {
    $response['status'] = 'error';
    $response['message'] = 'ID de transação não fornecido';
}

function getTokenDigitopay()
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
    //var_dump($dados);
    return $dados['accessToken'];
}


function verifyStatus($tid)
{
    global  $data_digitopay;

    $token = getTokenDigitopay();
    $url = $data_digitopay['url'] . "status/" . $tid;
    $header = array(
        'Content-Type:application/json',
        'Authorization:Bearer ' . $token
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resultado = curl_exec($ch);
    $status = json_decode($resultado, true);
    if ($status === "REALIZADO") {
        return true;
    } else {
        return false;
    }
}

function verifyPaid($id)
{
    global $mysqli;

    $qry = "SELECT * FROM transacoes WHERE transacao_id='" . $id . "'";
    $resp = mysqli_query($mysqli, $qry);
    if (mysqli_num_rows($resp) > 0) {
        $datares = mysqli_fetch_assoc($resp);
        if ($datares['status'] === 'pago') {
            return true;
        } else {
            return false;
        }
    }
}

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

// Retorna a resposta como JSON
echo json_encode($response);
