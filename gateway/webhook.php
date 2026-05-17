<?php

session_start();
include_once('../ad-min/services/database.php');
include_once('../ad-min/services/funcao.php');
include_once('../ad-min/services/crud.php');
include_once('suitpay.php');
include_once('royalbank.php');


function webhook()
{

    global $pdo;

    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;  // Para garantir que o script seja encerrado aqui
    }

    $settingS = "SELECT gateway_default FROM config WHERE id = 1";
    $stmt = $pdo->prepare($settingS);  // Prepare the statement
    $stmt->execute();  // Execute the statement
    $stts = $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch the result as an associative array

    switch ($stts['gateway_default']) {
        case 'pixup':
            if (!isset($data['requestBody'])) {
                echo json_encode(['status' => 'error', 'message' => 'Incomplete data']);
                exit;
            }

            // Processa os dados conforme necessário
            $idTransaction = PHP_SEGURO($data['requestBody']['transactionId']);
            $typeTransaction = PHP_SEGURO($data['requestBody']['paymentType']);             // id da transação
            // tipo de transação

            // Verifica qual status está presente
            if (isset($data['requestBody']['status'])) {
                $statusTransaction = PHP_SEGURO($data['requestBody']['status']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Status not provided']);
                exit;
            }

            if (isset($data['requestBody']['paymentType'])) {
                $typeTransaction = PHP_SEGURO($data['requestBody']['paymentType']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'type not provided']);
                exit;
            }

            // Verifica e processa o status da transação
            if ($data['requestBody']['status'] === 'PAID') {
                // Função externa para atualizar transação


                $consulta = "SELECT status FROM transacoes WHERE transacao_id = :idTransaction";
                $stmt = $pdo->prepare($consulta);  // Prepare the statement
                $stmt->bindParam(':idTransaction', $idTransaction, PDO::PARAM_INT);  // Bind the parameter
                $stmt->execute();  // Execute the statement
                $trans = $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch the result as an associative array

                if ($trans === false) {
                    echo json_encode(['status' => 'error', 'message' => 'Transaction does not exist']);
                    exit;
                } else {
                    if ($trans['status'] === 'pago' || $trans['status'] === 'expirado') {
                        echo json_encode(['status' => 'error', 'message' => 'It`s already paid']);
                        exit;
                    } else {
                        att_paymentpix($idTransaction);
                        echo json_encode(['status' => 'success']);
                    }
                }


                // Caso a transação seja paga, enviar saldo
                // $retorna_insert_saldo_suit_pay = enviarSaldo($retornaUSER['email'], $data['valor']);
            }


            break;
        case 'paguepix':
            if (!isset($data['requestBody'])) {
                echo json_encode(['status' => 'error', 'message' => 'Incomplete data']);
                exit;
            }

            // Processa os dados conforme necessário
            $idTransaction = PHP_SEGURO($data['requestBody']['transactionId']);
            $typeTransaction = PHP_SEGURO($data['requestBody']['paymentType']);             // id da transação
            // tipo de transação

            // Verifica qual status está presente
            if (isset($data['requestBody']['status'])) {
                $statusTransaction = PHP_SEGURO($data['requestBody']['status']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Status not provided']);
                exit;
            }

            if (isset($data['requestBody']['paymentType'])) {
                $typeTransaction = PHP_SEGURO($data['requestBody']['paymentType']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'type not provided']);
                exit;
            }

            // Verifica e processa o status da transação
            if ($data['requestBody']['status'] === 'PAID') {
                // Função externa para atualizar transação


                $consulta = "SELECT status FROM transacoes WHERE transacao_id = :idTransaction";
                $stmt = $pdo->prepare($consulta);  // Prepare the statement
                $stmt->bindParam(':idTransaction', $idTransaction, PDO::PARAM_INT);  // Bind the parameter
                $stmt->execute();  // Execute the statement
                $trans = $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch the result as an associative array

                if ($trans === false) {
                    echo json_encode(['status' => 'error', 'message' => 'Transaction does not exist']);
                    exit;
                } else {
                    if ($trans['status'] === 'pago' || $trans['status'] === 'expirado') {
                        echo json_encode(['status' => 'error', 'message' => 'It`s already paid']);
                        exit;
                    } else {
                        att_paymentpix($idTransaction);
                        echo json_encode(['status' => 'success']);
                    }
                }


                // Caso a transação seja paga, enviar saldo
                // $retorna_insert_saldo_suit_pay = enviarSaldo($retornaUSER['email'], $data['valor']);
            }


            break;            
        case 'royalbenk':
            // Processa webhook da Royal Bank
            $result = RoyalBankAPI::processWebhook($data);
            echo json_encode($result);
            break;
            
        case 'suitpay':
            if (!isset($data['idTransaction'])) {
                echo json_encode(['status' => 'error', 'message' => 'Incomplete data']);
                return;
            }

            // Processa os dados conforme necessário
            $idTransaction = PHP_SEGURO($data['idTransaction']);              // id da transação
            // tipo de transação

            // Verifica qual status está presente
            if (isset($data['statusTransaction'])) {
                $statusTransaction = PHP_SEGURO($data['statusTransaction']);
            } elseif (isset($data['status'])) {
                $statusTransaction = PHP_SEGURO($data['status']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Status not provided']);
                return;
            }

            // Verifica e processa o status da transação
            if ($statusTransaction === 'PAID_OUT' || $statusTransaction === 'paid') {
                // Função externa para atualizar transação


                $consulta = "SELECT status FROM transacoes WHERE transacao_id = :idTransaction";
                $stmt = $pdo->prepare($consulta);  // Prepare the statement
                $stmt->bindParam(':idTransaction', $idTransaction, PDO::PARAM_INT);  // Bind the parameter
                $stmt->execute();  // Execute the statement
                $trans = $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch the result as an associative array

                if ($trans['status'] === 'pago' || $trans['status'] === 'expirado') {
                    echo json_encode(['status' => 'error', 'message' => 'It`s already paid']);
                    exit;
                } else {
                    $att_transacao = att_paymentpix($idTransaction);
                }

                // Caso a transação seja paga, enviar saldo
                // $retorna_insert_saldo_suit_pay = enviarSaldo($retornaUSER['email'], $data['valor']);
            }

            echo json_encode(['status' => 'success']);
            break;
    }
}
webhook();
