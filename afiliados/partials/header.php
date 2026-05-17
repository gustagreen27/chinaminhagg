<?php
// Debugar Erros No Código / 1 = ON, 0 = OFF
ini_set('display_errors', 0);
error_reporting(E_ALL);
define("APP_URL", $_SERVER['DOCUMENT_ROOT'] . '/')
?>
<?php
session_start();
if (!isset($_SESSION['auth_token']) || isset($_SESSION['auth_token']) && $_SESSION['auth_token'] === 'undefined') {
    session_destroy();
    unset($_SESSION['auth_token']);
    header('Location: /afiliados/login');
}

?>
<?php include_once '../services/verifySession.php'; ?>
<?php
include_once '../services/crud.php';
global $datauser, $urlsistema;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= $urlsistema ?>/uploads/<?php echo $dataconfig['logo'] ?>">
    <link rel="icon" type="image/png" href="<?= $urlsistema ?>/uploads/<?php echo $dataconfig['logo'] ?>">
    <link rel="icon" type="image/png" href="<?= $urlsistema ?>/uploads/<?php echo $dataconfig['logo'] ?>">
    <title>
        <?php echo $dataconfig['nome'] ?> | Painel do Afiliado
    </title>
    <!--     Fonts and icons     -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="<?= $urlsistema ?>/afiliados/assets/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show dark-version bg-gray-100">
    <div class="min-height-300 bg-dark position-absolute w-100">
        <?php include_once "sidebar.php"; ?>
        <main class="main-content position-relative border-radius-lg ">
            <?php include_once "navbar.php"; ?>
            <!-- End Navbar -->
            <div class="container-fluid py-4" style="min-height: 92vh;-height: 92vh;">