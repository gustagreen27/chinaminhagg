<?php include 'partials/html.php' ?>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include_once 'services/database.php';
include_once 'services/funcao.php';
include_once "services/crud.php";
include_once "services/crud-adm.php";
include_once "services/CSRF_Protect.php";
$csrf = new CSRF_Protect();
?>

<head>
    <?php $title = "[BlackScript]";
    include 'partials/title-meta.php' ?>

    <?php include 'partials/head-css.php' ?>

    <?php include 'partials/vendorjs.php' ?>
</head>

<style>
    #particles-js {
        position: fixed;
        width: 100%;
        height: 100%;
        z-index: -1;
        top: 0;
        left: 0;
        opacity: 0.2;
    }

    #response {
        display: none;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<body>
    <div class="container-xxl">
        <div class="row vh-100 d-flex justify-content-center">
            <div id="particles-js"></div>
            <div class="col-12 align-self-center">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mx-auto">
                            <div id="response"></div>
                            <br>
                            <div class="card">
                                <div class="card-body p-0 bg-black auth-header-box rounded-top">
                                    <div class="text-center p-3">
                                        <a href="index.php" class="logo logo-admin">
                                            <img src="../uploads/<?= $dataconfig['logo'] ?>?v=<?= rand(1000,9999) ?>" height="50" alt="logo"
                                                class="auth-logo">
                                        </a>
                                        <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">🚀Acessar Painel De Controle
                                        </h4>
                                        <p class="text-muted fw-medium mb-0">Faça login para continuar.</p>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <form method="POST" id="form-acessar" class="my-4">
                                        <div class="form-group mb-2">
                                            <label class="form-label" for="username">Usuario/Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Insira O Acesso">
                                        </div><!--end form-group-->

                                        <div class="form-group">
                                            <label class="form-label" for="userpassword">Senha</label>
                                            <input type="password" class="form-control" name="senha" id="senha"
                                                placeholder="Insira A Senha">
                                        </div><!--end form-group-->

                                        <div class="form-group row mt-3">
                                            <div class="col-sm-6">
                                                <div class="form-check form-switch form-switch-success">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="customSwitchSuccess">
                                                    <label class="form-check-label" for="customSwitchSuccess">Manter
                                                        Conectado</label>
                                                </div>
                                            </div><!--end col-->

                                        </div><!--end form-group-->

                                        <div class="form-group mb-0 row">
                                            <div class="col-12">
                                                <div class="d-grid mt-3">
                                                    <?php $csrf->echoInputField(); ?>
                                                    <button class="btn btn-primary" type="submit">Acessar <i
                                                            class="fas fa-sign-in-alt ms-1"></i></button>
                                                </div>
                                            </div><!--end col-->
                                        </div> <!--end form-group-->
                                    </form><!--end form-->

                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end card-body-->
            </div><!--end col-->
        </div><!--end row-->
    </div><!-- container -->
</body>
<!--end body-->
<script>
    $(document).ready(function() {
        $('#form-acessar').submit(function(event) {
            event.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                url: 'ajax/form-acessar.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#response').html(response).show();
                    setTimeout(function() {
                        $('#response').hide();
                    }, 9000);
                },
            });
        });
    });
</script>

</html>