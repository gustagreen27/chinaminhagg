<?php
/**
 * Configuração da API Royal Bank
 */

// Configurações da API
define('ROYALBANK_API_KEY', 'SUA_API_KEY_AQUI'); // Substitua pela sua API key
define('ROYALBANK_BASE_URL', 'https://api.royalbanking.com.br');
define('ROYALBANK_CASHIN_ENDPOINT', '/v1/gateway/');
define('ROYALBANK_CASHOUT_ENDPOINT', '/c1/cashout/');

// Configurações do webhook
define('ROYALBANK_WEBHOOK_URL', 'https://seudominio.com/gateway/webhook.php');

// Configurações de retry
define('ROYALBANK_MAX_RETRIES', 3);
define('ROYALBANK_RETRY_DELAY', 5); // segundos

// Status de transação
define('ROYALBANK_STATUS_PAID', 'paid');
define('ROYALBANK_STATUS_SAQUE_PAGO', 'SaquePago');
define('ROYALBANK_STATUS_SAQUE_FALHOU', 'SaqueFalhou');

// Tipos de chave Pix
define('ROYALBANK_PIX_TYPE_CPF', 'CPF');
define('ROYALBANK_PIX_TYPE_EMAIL', 'E-MAIL');
define('ROYALBANK_PIX_TYPE_TELEFONE', 'TELEFONE');
define('ROYALBANK_PIX_TYPE_ALEATORIA', 'ALEATORIA');

// Função para obter configuração do banco
function getRoyalBankConfig() {
    global $mysqli;
    
    $sql = "SELECT url, api_key, client_secret, webhook_url FROM royalbenk WHERE id = 1";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return [
        'url' => ROYALBANK_BASE_URL,
        'api_key' => ROYALBANK_API_KEY,
        'client_secret' => '',
        'webhook_url' => ROYALBANK_WEBHOOK_URL
    ];
}

// Função para validar dados do cliente
function validateClientData($client) {
    $errors = [];
    
    if (empty($client['name'])) {
        $errors[] = 'Nome do cliente é obrigatório';
    }
    
    if (empty($client['document']) || strlen($client['document']) !== 11) {
        $errors[] = 'CPF deve ter 11 dígitos';
    }
    
    if (empty($client['telefone']) || strlen($client['telefone']) < 10) {
        $errors[] = 'Telefone deve ter pelo menos 10 dígitos';
    }
    
    if (empty($client['email']) || !filter_var($client['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    
    return $errors;
}

// Função para validar dados do saque
function validateCashOutData($amount, $keypix, $pixType, $name, $cpf) {
    $errors = [];
    
    if ($amount <= 0) {
        $errors[] = 'Valor deve ser maior que zero';
    }
    
    if (empty($keypix)) {
        $errors[] = 'Chave Pix é obrigatória';
    }
    
    if (!in_array($pixType, [ROYALBANK_PIX_TYPE_CPF, ROYALBANK_PIX_TYPE_EMAIL, ROYALBANK_PIX_TYPE_TELEFONE, ROYALBANK_PIX_TYPE_ALEATORIA])) {
        $errors[] = 'Tipo de chave Pix inválido';
    }
    
    if (empty($name)) {
        $errors[] = 'Nome do beneficiário é obrigatório';
    }
    
    if (empty($cpf) || strlen($cpf) !== 11) {
        $errors[] = 'CPF deve ter 11 dígitos';
    }
    
    return $errors;
}

// Função para formatar valor
function formatAmount($amount) {
    return number_format($amount, 2, '.', '');
}

// Função para limpar CPF
function cleanCPF($cpf) {
    return preg_replace('/[^0-9]/', '', $cpf);
}

// Função para limpar telefone
function cleanPhone($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
}

// Função para gerar ID único de transação
function generateTransactionId() {
    return uniqid('royalbank_', true);
}

// Função para log de transações
function logRoyalBankTransaction($transactionId, $action, $data, $response = null) {
    global $mysqli;
    
    $logData = [
        'transaction_id' => $transactionId,
        'action' => $action,
        'request_data' => json_encode($data),
        'response_data' => $response ? json_encode($response) : null,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $sql = "INSERT INTO royalbank_logs (transaction_id, action, request_data, response_data, created_at) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssss", 
        $logData['transaction_id'],
        $logData['action'],
        $logData['request_data'],
        $logData['response_data'],
        $logData['created_at']
    );
    
    return $stmt->execute();
}
?>
