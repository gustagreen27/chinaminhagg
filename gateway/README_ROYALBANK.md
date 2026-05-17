# Integração API Royal Bank

Esta integração permite realizar operações de Cash In (depósitos) e Cash Out (saques) via Pix usando a API da Royal Bank.

## Arquivos da Integração

- `royalbank.php` - Classe principal da API
- `royalbank_config.php` - Configurações e funções auxiliares
- `royalbank_example.php` - Exemplos de uso
- `webhook.php` - Processamento de webhooks (atualizado)

## Configuração

1. **Configure sua API Key** no arquivo `royalbank_config.php`:
```php
define('ROYALBANK_API_KEY', 'SUA_API_KEY_AQUI');
```

2. **Configure a URL do webhook**:
```php
define('ROYALBANK_WEBHOOK_URL', 'https://seudominio.com/gateway/webhook.php');
```

3. **Adicione as colunas necessárias** na tabela `config`:
```sql
ALTER TABLE config ADD COLUMN royalbank_api_key VARCHAR(255);
ALTER TABLE config ADD COLUMN royalbank_webhook_url VARCHAR(255);
```

4. **Crie a tabela de logs** (opcional):
```sql
CREATE TABLE royalbank_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(255),
    action VARCHAR(50),
    request_data TEXT,
    response_data TEXT,
    created_at DATETIME
);
```

## Uso da API

### Cash In (Depósito)

```php
include_once('royalbank.php');

$apiKey = 'SUA_API_KEY';
$royalBank = new RoyalBankAPI($apiKey);

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

if ($result['status_code'] === 200 && $result['response']['status'] === 'success') {
    echo "Pix gerado: " . $result['response']['paymentCode'];
    echo "ID da transação: " . $result['response']['idTransaction'];
}
```

### Cash Out (Saque)

```php
$amount = 50.00;
$keypix = '11999999999'; // Chave Pix
$pixType = 'TELEFONE'; // CPF, E-MAIL, TELEFONE, ALEATORIA
$name = 'João Silva';
$cpf = '12345678911';
$postbackUrl = 'https://seudominio.com/gateway/webhook.php';

$result = $royalBank->cashOut($amount, $keypix, $pixType, $name, $cpf, $postbackUrl);

if ($result['status_code'] === 200) {
    echo "Saque solicitado: " . $result['response']['idTransaction'];
}
```

## Webhooks

A integração processa automaticamente os webhooks da Royal Bank:

### Cash In Pago
```json
{
  "idtransaction": "81bb141a-1746-49a8-bb4a-c3b8aa0d2259",
  "status": "paid"
}
```

### Cash Out Pago
```json
{
  "idTransaction": "exemplo34243243",
  "status": "SaquePago"
}
```

### Cash Out Falhou
```json
{
  "idTransaction": "exemplo34243243",
  "status": "SaqueFalhou"
}
```

## Validações

A integração inclui validações automáticas:

- **CPF**: Deve ter 11 dígitos
- **Telefone**: Mínimo 10 dígitos
- **Email**: Formato válido
- **Valor**: Maior que zero
- **Chave Pix**: Obrigatória
- **Tipo de chave**: CPF, E-MAIL, TELEFONE, ALEATORIA

## Tratamento de Erros

### Códigos de Status HTTP
- `200`: Sucesso
- `400`: Dados inválidos
- `401`: Não autorizado (API key ou IP)
- `500`: Erro interno

### Exemplo de Tratamento
```php
$result = $royalBank->cashIn($amount, $client, $callbackUrl);

if ($result['status_code'] !== 200) {
    echo "Erro: " . $result['response']['message'];
} elseif ($result['response']['status'] !== 'success') {
    echo "Erro da API: " . $result['response']['message'];
} else {
    // Sucesso
    $pixCode = $result['response']['paymentCode'];
    $transactionId = $result['response']['idTransaction'];
}
```

## Logs

A integração registra automaticamente todas as transações na tabela `royalbank_logs` (se existir):

- ID da transação
- Ação realizada (cash_in, cash_out)
- Dados da requisição
- Resposta da API
- Data/hora

## Exemplos Práticos

Execute `royalbank_example.php` para testar:

```bash
# Testar Cash In
php royalbank_example.php?action=cashin

# Testar Cash Out
php royalbank_example.php?action=cashout
```

## Segurança

- Validação de IPs autorizados (configurado na Royal Bank)
- Criptografia de dados sensíveis
- Validação de assinatura de webhooks
- Logs de auditoria

## Suporte

Para dúvidas ou problemas:
1. Verifique os logs em `royalbank_logs`
2. Teste com `royalbank_example.php`
3. Consulte a documentação da Royal Bank
4. Entre em contato com o suporte da Royal Bank
