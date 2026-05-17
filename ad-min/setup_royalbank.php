<?php
/**
 * Script para configurar a tabela royalbenk
 */

include_once "services/database.php";

// SQL para criar a tabela
$sql = "
CREATE TABLE IF NOT EXISTS `royalbenk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT 'https://api.royalbanking.com.br',
  `client_secret` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Executar criação da tabela
if ($mysqli->query($sql)) {
    echo "✅ Tabela royalbenk criada com sucesso!\n";
} else {
    echo "❌ Erro ao criar tabela: " . $mysqli->error . "\n";
}

// Verificar se já existe registro
$check = "SELECT COUNT(*) as count FROM royalbenk WHERE id = 1";
$result = $mysqli->query($check);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Inserir registro padrão
    $insert = "INSERT INTO royalbenk (id, url, client_secret, api_key, webhook_url) 
               VALUES (1, 'https://api.royalbanking.com.br', '', '', '')";
    
    if ($mysqli->query($insert)) {
        echo "✅ Registro padrão inserido com sucesso!\n";
    } else {
        echo "❌ Erro ao inserir registro: " . $mysqli->error . "\n";
    }
} else {
    echo "✅ Registro já existe na tabela!\n";
}

echo "\n🎉 Configuração concluída! Acesse o painel admin para configurar suas credenciais.\n";
?>
