<?php

// Configurações de erros
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

include_once('../ad-min/services/funcao.php');
include_once('../ad-min/services/crud.php');
$logFilePath = __DIR__ . '/log.json';

function webhook()
{
    global $pdo;
    $logFilePath = __DIR__ . '/log.json';
    $logFileUserPath = __DIR__ . '/user.json';

    // Verifica se a requisição é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['msg' => 'error404']);
        return;
    }

    // Captura e registra o conteúdo JSON recebido
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    file_put_contents($logFilePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Usando switch para lidar com diferentes métodos
    switch ($data['method']) {
        case 'user_balance':
            $stmtUsr = $pdo->prepare("SELECT id, saldo FROM usuarios WHERE id = :id");
            $stmtUsr->execute(['id' => $data['user_code']]);
            $usuario = $stmtUsr->fetch();

            $balance = $usuario['saldo'] ?? 0;
            echo json_encode(['status' => 1, 'user_balance' => $balance]);
            break;

case 'transaction':
case 'game_callback':
    $stmtUsr = $pdo->prepare("SELECT id, saldo FROM usuarios WHERE id = :id");
    $stmtUsr->execute(['id' => $data['user_code']]);
    $usuario = $stmtUsr->fetch();

    if ($usuario) {
        $gameData = $data[$data['game_type']] ?? [];
        $tipoTransacao = $gameData['txn_type'] ?? 'debit_credit';
        $saldoAtual = floatval($usuario['saldo']);

        // Pega os valores de aposta e ganho
        $bet = floatval($gameData['bet_money'] ?? 0);
        $win = floatval($gameData['win_money'] ?? 0);

        // Calcula o novo saldo de acordo com o tipo
        if ($tipoTransacao === 'debit') {
            $novoSaldo = $saldoAtual - $bet;
        } elseif ($tipoTransacao === 'credit') {
            $novoSaldo = $saldoAtual + $win;
        } else { // debit_credit
            $novoSaldo = $saldoAtual - $bet + $win;
        }

        // Salva o histórico
        $stmtInsert = $pdo->prepare("INSERT INTO historico_play (id_user, nome_game, bet_money, win_money, txn_id, created_at, status_play) VALUES (:id_user, :nome_game, :bet_money, :win_money, :txn_id, :created_at, :status_play)");

        $dataPost = [
            'id_user' => $usuario['id'],
            'nome_game' => $gameData['game_code'] ?? null,
            'bet_money' => $bet,
            'win_money' => $win,
            'txn_id' => $gameData['txn_id'] ?? null,
            'created_at' => formatDate($gameData['created_at']) ?? date('Y-m-d H:i:s'),
            'status_play' => 1,
        ];
        $stmtInsert->execute($dataPost);

        // Atualiza o saldo
        $stmtUpdate = $pdo->prepare("UPDATE usuarios SET saldo = :saldo WHERE id = :id");
        $stmtUpdate->execute(['saldo' => $novoSaldo, 'id' => $usuario['id']]);

        echo json_encode([
            'status' => 1,
            'user_balance' => $novoSaldo
        ]);
    } else {
        echo json_encode(['status' => 0, 'msg' => 'INSUFFICIENT_USER_FUNDS', 'user_balance' => 0]);
    }
    break;
    }
}

// Chama a função webhook
webhook();

function createUser($id, $code, $token)
{
    global $pdo;

    $user = [
        "method" => "user_create",
        "agent_code" => $code,
        "agent_token" => $token,
        "user_code" => $id,
    ];

    // Exemplo de inserção no banco de dados (não especificado no escopo original)
    // Aqui precisaria ajustar de acordo com a tabela e dados reais
    $insert = $pdo->prepare("INSERT INTO usuarios (id, agent_code, agent_token) VALUES (:user_code, :agent_code, :agent_token)");

    $insert->execute($user);
}

function formatDate($date)
{
    $data = new DateTime($date);

    // Define o fuso horário UTC (opcional, mas garante que o "Z" seja tratado corretamente)
    $data->setTimezone(new DateTimeZone('UTC'));

    // Formata a data para o formato desejado
    $dataFormatada = $data->format('Y-m-d H:i:s');

    return $dataFormatada;
}
