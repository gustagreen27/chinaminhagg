<?php
/**
 * Royal Bank API Integration
 * Cash In (Depósitos) e Cash Out (Saques)
 */

include_once('royalbank_config.php');

class RoyalBankAPI {
    private $apiKey;
    private $baseUrl;
    private $cashInEndpoint = '/v1/gateway/';
    private $cashOutEndpoint = '/c1/cashout/';
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $config = getRoyalBankConfig();
        $this->baseUrl = $config['url'] ?: 'https://api.royalbanking.com.br';
    }
    
    /**
     * Cash In - Solicitar depósito via Pix
     */
    public function cashIn($amount, $client, $callbackUrl, $split = null) {
        // Validação dos dados
        $errors = validateClientData($client);
        if (!empty($errors)) {
            return [
                'status_code' => 400,
                'response' => ['status' => 'error', 'message' => implode(', ', $errors)],
                'raw_response' => null
            ];
        }
        
        // Limpa e formata dados
        $client['document'] = cleanCPF($client['document']);
        $client['telefone'] = cleanPhone($client['telefone']);
        $amount = formatAmount($amount);
        
        $data = [
            'api-key' => $this->apiKey,
            'amount' => $amount,
            'client' => [
                'name' => $client['name'],
                'document' => $client['document'],
                'telefone' => $client['telefone'],
                'email' => $client['email']
            ],
            'callbackUrl' => $callbackUrl
        ];
        
        if ($split) {
            $data['split'] = [
                'email' => $split['email'],
                'percentage' => $split['percentage']
            ];
        }
        
        $result = $this->makeRequest($this->cashInEndpoint, $data);
        
        // Log da transação
        $transactionId = $result['response']['idTransaction'] ?? generateTransactionId();
        logRoyalBankTransaction($transactionId, 'cash_in', $data, $result['response']);
        
        return $result;
    }
    
    /**
     * Cash Out - Solicitar saque via Pix
     */
    public function cashOut($amount, $keypix, $pixType, $name, $cpf, $postbackUrl) {
        // Validação dos dados
        $errors = validateCashOutData($amount, $keypix, $pixType, $name, $cpf);
        if (!empty($errors)) {
            return [
                'status_code' => 400,
                'response' => ['status' => 'error', 'message' => implode(', ', $errors)],
                'raw_response' => null
            ];
        }
        
        // Limpa e formata dados
        $cpf = cleanCPF($cpf);
        $amount = formatAmount($amount);
        
        $data = [
            'api-key' => $this->apiKey,
            'amount' => $amount,
            'keypix' => $keypix,
            'pixType' => $pixType,
            'name' => $name,
            'cpf' => $cpf,
            'postbackUrl' => $postbackUrl
        ];
        
        $result = $this->makeRequest($this->cashOutEndpoint, $data, 'basic');
        
        // Log da transação
        $transactionId = $result['response']['idTransaction'] ?? generateTransactionId();
        logRoyalBankTransaction($transactionId, 'cash_out', $data, $result['response']);
        
        return $result;
    }
    
    /**
     * Faz requisição HTTP para a API
     */
    private function makeRequest($endpoint, $data, $authType = 'header') {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        // Autenticação
        if ($authType === 'basic') {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status_code' => $httpCode,
            'response' => json_decode($response, true),
            'raw_response' => $response
        ];
    }
    
    /**
     * Processa webhook da Royal Bank
     */
    public static function processWebhook($data) {
        $response = ['status' => 'error', 'message' => 'Invalid data'];
        
        // Cash In webhook
        if (isset($data['idtransaction']) && isset($data['status'])) {
            if ($data['status'] === 'paid') {
                $response = self::handleCashInPaid($data['idtransaction']);
            }
        }
        
        // Cash Out webhook
        if (isset($data['idTransaction']) && isset($data['status'])) {
            if ($data['status'] === 'SaquePago') {
                $response = self::handleCashOutPaid($data['idTransaction']);
            } elseif ($data['status'] === 'SaqueFalhou') {
                $response = self::handleCashOutFailed($data['idTransaction']);
            }
        }
        
        return $response;
    }
    
    /**
     * Processa Cash In pago
     */
    private static function handleCashInPaid($transactionId) {
        global $mysqli;
        
        // Verifica se a transação existe e não foi processada
        $sql = "SELECT status FROM transacoes WHERE transacao_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $transactionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
            
            if ($transaction['status'] !== 'pago' && $transaction['status'] !== 'expirado') {
                // Atualiza status para pago
                $updateSql = "UPDATE transacoes SET status = 'pago' WHERE transacao_id = ?";
                $updateStmt = $mysqli->prepare($updateSql);
                $updateStmt->bind_param("s", $transactionId);
                
                if ($updateStmt->execute()) {
                    // Processa crédito do saldo
                    self::creditUserBalance($transactionId);
                    return ['status' => 'success', 'message' => 'Transaction processed successfully'];
                }
            } else {
                return ['status' => 'error', 'message' => 'Transaction already processed'];
            }
        }
        
        return ['status' => 'error', 'message' => 'Transaction not found'];
    }
    
    /**
     * Processa Cash Out pago
     */
    private static function handleCashOutPaid($transactionId) {
        // Implementar lógica para saque confirmado
        return ['status' => 'success', 'message' => 'Cash out processed successfully'];
    }
    
    /**
     * Processa Cash Out falhou
     */
    private static function handleCashOutFailed($transactionId) {
        // Implementar lógica para saque falhou
        return ['status' => 'error', 'message' => 'Cash out failed'];
    }
    
    /**
     * Credita saldo do usuário
     */
    private static function creditUserBalance($transactionId) {
        global $mysqli;
        
        // Busca dados da transação
        $sql = "SELECT usuario, valor FROM transacoes WHERE transacao_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $transactionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
            
            // Busca dados do usuário
            $userSql = "SELECT mobile FROM usuarios WHERE id = ?";
            $userStmt = $mysqli->prepare($userSql);
            $userStmt->bind_param("i", $transaction['usuario']);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            
            if ($userResult->num_rows > 0) {
                $user = $userResult->fetch_assoc();
                
                // Chama função para creditar saldo
                if (function_exists('enviarSaldo')) {
                    enviarSaldo($user['mobile'], $transaction['valor']);
                }
            }
        }
    }
}

// Função helper para criar instância da API
function createRoyalBankAPI($apiKey = null) {
    if (!$apiKey) {
        // Busca API key das configurações
        $config = getRoyalBankConfig();
        $apiKey = $config['api_key'];
    }
    
    return new RoyalBankAPI($apiKey);
}
?>
