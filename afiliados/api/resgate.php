<?php
session_start();
header('Content-Type: application/json');
require '../../ad-min/services/database.php'; // Arquivo de conexão com banco de dados
require '../services/crud.php'; // Arquivo de conexão com banco de dados

$response = ['status' => 'error', 'message' => 'Houve um erro, tente novamente!'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    global $datauser;
    $valor = $_POST['valor'];

    try {
        // Corrigir a condição de saldo insuficiente
        if ($valor > $datauser['saldo_afiliados'] && $valor < $afiliadosconfig['minResgate']) {
            $response['message'] = "Saldo insuficiente para resgate!";
            echo json_encode($response);
            exit;
        }

        $userId = $datauser['id'];

        // SQL para buscar o método de pagamento PIX
        $sqlpix = "SELECT * FROM metodos_pagamentos WHERE user_id = :user_id";
        $stmpix = $pdo->prepare($sqlpix);
        $stmpix->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmpix->execute();
        $pix = $stmpix->fetch(PDO::FETCH_ASSOC);

        $datadia = date('Y-m-d H:i:s');
        $tokenSaque = md5($datauser['mobile'] . sha1(mt_rand()) . $datadia);
        $RANDOMSAQUE = md5($tokenSaque);

        // Se não encontrar o método de pagamento, retornar erro
        if (!$pix) {
            $response['message'] = "Método de pagamento não encontrado!";
            echo json_encode($response);
            exit;
        }

        // SQL para inserir a solicitação de saque
        $sql = "INSERT INTO solicitacao_saques (id_user, tipo, pix, data_cad, data_hora, data_att, tipo_saque, transacao_id, valor) 
            VALUES (:id_user, :tipo, :pix, :data_cad, :data_hora, :data_att, :tipo_saque, :transacao_id, :valor)";

        // Prepare a instrução
        $stmt = $pdo->prepare($sql);

        // Variáveis para os valores
        $id_user = $datauser['id'];
        $tipo = $pix['id']; // Ou outro campo relevante
        $pix_id = $pix['id'];
        $data_cad = date('Y-m-d');
        $data_hora = date('H:i:s'); // Formato correto de hora
        $tipo_saque = 1;
        $transacao_id = $RANDOMSAQUE;

        // Bind os parâmetros
        $stmt->bindParam(':id_user', $id_user);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':pix', $pix_id);
        $stmt->bindParam(':data_cad', $data_cad);
        $stmt->bindParam(':data_hora', $data_hora);
        $stmt->bindParam(':data_att', $datadia);
        $stmt->bindParam(':tipo_saque', $tipo_saque);
        $stmt->bindParam(':transacao_id', $transacao_id);
        $stmt->bindParam(':valor', $valor);

        // Executa a solicitação de saque
        $solicitacao = $stmt->execute();

        if ($solicitacao) {
            // Atualiza o saldo do usuário
            $novo_saldo = $datauser['saldo_afiliados'] - $valor;
            $sqlatt = "UPDATE usuarios SET saldo_afiliados = :saldo_afiliados WHERE id = :id";
            $stmtatt = $pdo->prepare($sqlatt);
            $stmtatt->bindParam(':saldo_afiliados', $novo_saldo);
            $stmtatt->bindParam(':id', $datauser['id']);
            $stmtatt->execute();
        }

        // Resposta de sucesso
        $response = ['status' => 'success', 'message' => 'Solicitação de resgate realizada com sucesso!'];
        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        // Resposta de erro com a mensagem do PDOException
        $response = ['status' => 'error', 'message' => 'Erro ao processar a solicitação: ' . $e->getMessage()];
        echo json_encode($response);
        exit;
    }
}
