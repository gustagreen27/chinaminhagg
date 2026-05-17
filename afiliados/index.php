
<?php
session_start();

if (isset($_SESSION['auth_token']) && $_SESSION['auth_token'] !== 'undefined') {
    header('Location: /afiliados/dashboard');
} else {
    unset($_SESSION['auth_token']);
    session_destroy();
    header('Location: /afiliados/login');
}

if (!isset($_SESSION['auth_token']) || isset($_SESSION['auth_token']) && $_SESSION['auth_token'] === 'undefined') {
    session_destroy();
    unset($_SESSION['auth_token']);
    header('Location: /afiliados/login');
}


?>