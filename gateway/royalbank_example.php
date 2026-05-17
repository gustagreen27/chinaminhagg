<?php
/**
 * Exemplo de uso da API Royal Bank
 */

include_once('../ad-min/services/database.php');
include_once('../ad-min/services/funcao.php');
include_once('../ad-min/services/crud.php');
include_once('royalbank.php');

// Configuração da API
$apiKey = 'SUA_API_KEY_AQUI'; // Substitua pela sua API key
$royalBank = new RoyalBankAPI($apiKey);

// Exemplo 1: Cash In (Depósito)
function exemploCashIn() {
    global $royalBank;
    
    $amount = 100.00; // R$ 100,00
    $client = [
        'name' => 'João Silva',
        'document' => '12345678911',
        'telefone' => '11999999999',
        'email' => 'joao.silva@email.com'
    ];
    $callbackUrl = 'https://seudominio.com/gateway/webhook.php';
    
    // Opcional: Split de pagamento
    $split = [
        'email' => '@teste123',
        'percentage' => '10' // 10%
    ];
    
    $result = $royalBank->cashIn($amount, $client, $callbackUrl, $split);
    
    if ($result['status_code'] === 200) {
        $response = $result['response'];
        if ($response['status'] === 'success') {
            echo "Pix gerado com sucesso!\n";
            echo "ID da transação: " . $response['idTransaction'] . "\n";
            echo "Código Pix: " . $response['paymentCode'] . "\n";
            
            // Salvar no banco de dados
            salvarTransacao($response['idTransaction'], $amount, $client['email']);
        } else {
            echo "Erro: " . $response['message'] . "\n";
        }
    } else {
        echo "Erro HTTP: " . $result['status_code'] . "\n";
    }
}

// Exemplo 2: Cash Out (Saque)
function exemploCashOut() {
    global $royalBank;
    
    $amount = 50.00; // R$ 50,00
    $keypix = '11999999999'; // Chave Pix (telefone)
    $pixType = 'TELEFONE';
    $name = 'João Silva';
    $cpf = '12345678911';
    $postbackUrl = 'https://seudominio.com/gateway/webhook.php';
    
    $result = $royalBank->cashOut($amount, $keypix, $pixType, $name, $cpf, $postbackUrl);
    
    if ($result['status_code'] === 200) {
        $response = $result['response'];
        echo "Saque solicitado com sucesso!\n";
        echo "ID da transação: " . $response['idTransaction'] . "\n";
    } else {
        echo "Erro HTTP: " . $result['status_code'] . "\n";
    }
}

// Função para salvar transação no banco
function salvarTransacao($transactionId, $amount, $userEmail) {
    global $mysqli;
    
    // Busca ID do usuário pelo email
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        
        // Insere transação
        $insertSql = "INSERT INTO transacoes (transacao_id, usuario, valor, status, gateway, data_criacao) 
                      VALUES (?, ?, ?, 'pendente', 'royalbank', NOW())";
        $insertStmt = $mysqli->prepare($insertSql);
        $insertStmt->bind_param("sid", $transactionId, $userId, $amount);
        
        if ($insertStmt->execute()) {
            echo "Transação salva no banco de dados\n";
        } else {
            echo "Erro ao salvar transação\n";
        }
    }
}

// Exemplo de uso
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'cashin':
            exemploCashIn();
            break;
        case 'cashout':
            exemploCashOut();
            break;
        default:
            echo "Ação inválida\n";
    }
} else {
    echo "Uso: ?action=cashin ou ?action=cashout\n";
}
?>
