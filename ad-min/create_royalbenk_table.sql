-- Criar tabela royalbenk se não existir
CREATE TABLE IF NOT EXISTS `royalbenk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `client_secret` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir registro padrão se não existir
INSERT IGNORE INTO `royalbenk` (`id`, `url`, `client_secret`, `api_key`, `webhook_url`) 
VALUES (1, 'https://api.royalbanking.com.br', '', '', 'https://seudominio.com/gateway/webhook.php');
