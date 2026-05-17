<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('../ad-min/services/funcao.php');
include_once('../ad-min/services/crud.php');


    global $pdo;

    // Verifica se a requisição é do tipo POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['msg' => 'error404']);
        return;
    }

    // Log para verificar o método POST
    error_log("Requisição POST recebida.");

    // Obtém o conteúdo JSON enviado na requisição
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // Verifica se o tipo de requisição é 'BALANCE'
    if ($data['type'] === 'WinBet') {
        $stmtUsr = $pdo->prepare("SELECT * FROM usuarios WHERE mobile = :mobile");
        $stmtUsr->execute(['mobile' => $data['user_code']]);
        $usuario = $stmtUsr->fetch();

        // Verifica se o usuário existe antes de preparar os dados para inserção
        if ($usuario) {

            //$stmtTr = $pdo->prepare("SELECT * FROM historico_play WHERE txn_id = :txn_id");
            //$stmtTr->execute(['txn_id' => $data[$data['game_type']]['txn_id']]);
            //$trsct = $stmtUsr->fetch();
            //if (isset($trsct)) {
            //    echo json_encode(['msg' => 'DUPLICATE_TRANSACTION']);
            //    return;
            //}

            $dataPost = [
                'id_user' => $usuario['id'], // Certifique-se de que o usuário existe aqui
                'nome_game' => $data[$data['game_type']]['game_code'] ?? null,
                'bet_money' => $data[$data['game_type']]['bet'] ?? null,
                'win_money' => $data[$data['game_type']]['win'] ?? null,
                'txn_id' => $data[$data['game_type']]['txn_id'] ?? null,
                'created_at' => $data[$data['game_type']]['created_at'] ?? null,
                'status_play' => 1,
            ];

            // Preparar a instrução SQL para inserção
            $stmtInsert = $pdo->prepare("INSERT INTO historico_play (id_user, nome_game, bet_money, win_money, txn_id, created_at, status_play) VALUES (:id_user, :nome_game, :bet_money, :win_money, :txn_id, :created_at, :status_play)");

            // Executar a instrução com os dados
            if ($stmtInsert->execute($dataPost)) {
                $ganho = $data[$data['game_type']]['win'] - $data[$data['game_type']]['bet'];
                $novosaldo = $usuario['saldo'] + $ganho;

                // Atualizar o saldo do usuário
                $stmtGanho = $pdo->prepare("UPDATE usuarios SET saldo = :saldo WHERE id = :id");
                $stmtGanho->execute(['saldo' => $novosaldo, 'id' => $usuario['id']]);

                echo json_encode(['msg' => 'SUCCESS', 'balance' => (float) $novosaldo]);
                return;
            }
        }

        // Se o usuário não existir ou a inserção falhar
        echo json_encode(['msg' => 'INVALID_USER', 'balance' => 0]);
    } else {
        $stmtUsr = $pdo->prepare("SELECT * FROM usuarios WHERE mobile = :mobile");
        $stmtUsr->execute(['mobile' => $data['user_code']]);
        $usuario = $stmtUsr->fetch();

        if (!$usuario) {
            echo json_encode(['msg' => 'INVALID_USER', 'balance' => 0]);
            return; // Retorna aqui para evitar continuar com o código
        }

        echo json_encode(['msg' => '', 'balance' => (float) $usuario['saldo']]);
    }


