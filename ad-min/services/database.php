<?php

$host = 'localhost';
$db = 'u835092454_fonsaonifa';
$user = "u835092454_fonsaonifa";
$pass = "Y!r2eYiSqtO";
$charset = "UTF8";
//  -- Setar o timezone padrão do sistema  --------------------------------------------------------//
date_default_timezone_set("America/Sao_Paulo");
define('PRODUCAO', true);
if (PRODUCAO) {
    $bd = array(
        'local' => $host, // local/ip
        'usuario' => $user, // user bd
        'senha' => $pass, // senha bd
        'banco' => $db // nome bd
    );
} else {
    $bd = array(
        'local' => $host, // local/ip
        'usuario' => $user, // user bd
        'senha' => $pass, // senha bd
        'banco' => $db // nome bd
    );
}
#----------------------------------------------------------------------------------------------------------#
//-- conexao procedural --------------------------------------------------------------------------//
$mysqli = new mysqli($bd['local'], $bd['usuario'], $bd['senha'], $bd['banco']);
if ($mysqli->connect_errno) {
    echo "Erro ao Conectar o BD: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit;
}
$mysqli->set_charset("utf8");


$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Criar uma nova instância PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Lidar com erros de conexão
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
