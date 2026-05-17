<?php
date_default_timezone_set('America/Sao_Paulo');
include_once($_SERVER['DOCUMENT_ROOT'] . '/ad-min/services/database.php');


$pasta_url = '/';
//=======================================#

// Função para detectar se a conexão é HTTPS
function pega_http_https()
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
}

// Função para gerar a URL base do sistema
function url_sistema()
{
    global $pasta_url;
    $protocol = pega_http_https();
    $system_url = $protocol . "://" . filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL) . $pasta_url;
    return rtrim($system_url, '/');
}

$urlsistema = url_sistema();

#=====================================================#
# DATA CONFIG
function data_config()
{
    global $mysqli;
    $qry = "SELECT * FROM config WHERE id=1";
    $res = mysqli_query($mysqli, $qry);
    $data = mysqli_fetch_assoc($res);
    return $data;
}
$dataconfig = data_config();
