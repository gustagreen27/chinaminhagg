<?php include 'partials/html.php' ?>

<?php
#======================================#
ini_set('display_errors', 0);
error_reporting(E_ALL);
#======================================#
session_start();
include_once "services/database.php";
include_once 'logs/registrar_logs.php';
include_once "services/funcao.php";
include_once "services/crud.php";
include_once "services/crud-adm.php";
include_once 'services/checa_login_adm.php';
include_once "services/CSRF_Protect.php";
$csrf = new CSRF_Protect();
#======================================#
#expulsa user
checa_login_adm();
#======================================#
//inicio do script expulsa usuario bloqueado
if ($_SESSION['data_adm']['status'] != '1') {
    echo "<script>setTimeout(function() { window.location.href = 'bloqueado.php'; }, 0);</script>";
    exit();
}

# Função para buscar os dados atuais da tabela afiliados_config
function get_afiliados_config()
{
    global $mysqli;
    $qry = "SELECT * FROM afiliados_config WHERE id=1";
    $result = mysqli_query($mysqli, $qry);
    return mysqli_fetch_assoc($result);
}

# Função para atualizar os dados da tabela afiliados_config
function update_afiliados_config($data)
{
    global $mysqli;
    $qry = $mysqli->prepare("UPDATE afiliados_config SET 
        cpaLvl1 = ?, 
        cpaLvl2 = ?, 
        cpaLvl3 = ?, 
        chanceCpa = ?, 
        revShareFalso = ?, 
        revShareLvl1 = ?, 
        revShareLvl2 = ?, 
        revShareLvl3 = ?, 
        minDepForCpa = ?, 
        minResgate = ? 
        WHERE id = 1");

    $qry->bind_param(
        "dddddddddd",
        $data['cpaLvl1'],
        $data['cpaLvl2'],
        $data['cpaLvl3'],
        $data['chanceCpa'],
        $data['revShareFalso'],
        $data['revShareLvl1'],
        $data['revShareLvl2'],
        $data['revShareLvl3'],
        $data['minDepForCpa'],
        $data['minResgate']
    );
    return $qry->execute();
}

# Se o formulário for enviado, atualizar os dados
$toastType = null; // Variável para definir o tipo de Toast
$toastMessage = ''; // Variável para definir a mensagem do Toast

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'cpaLvl1' => floatval($_POST['cpaLvl1']),
        'cpaLvl2' => floatval($_POST['cpaLvl2']),
        'cpaLvl3' => floatval($_POST['cpaLvl3']),
        'chanceCpa' => floatval($_POST['chanceCpa']),
        'revShareFalso' => floatval($_POST['revShareFalso']),
        'revShareLvl1' => floatval($_POST['revShareLvl1']),
        'revShareLvl2' => floatval($_POST['revShareLvl2']),
        'revShareLvl3' => floatval($_POST['revShareLvl3']),
        'minDepForCpa' => floatval($_POST['minDepForCpa']),
        'minResgate' => floatval($_POST['minResgate']),
    ];

    if (update_afiliados_config($data)) {
        $toastType = 'success';
        $toastMessage = 'Configurações de afiliados atualizadas com sucesso!';
    } else {
        $toastType = 'error';
        $toastMessage = 'Erro ao atualizar as configurações. Tente novamente.';
    }
}

# Buscar os dados atuais
$config = get_afiliados_config();
?>

<head>
    <?php $title = "Configurações de Afiliados";
    include 'partials/title-meta.php' ?>

    <link rel="stylesheet" href="assets/libs/jsvectormap/jsvectormap.min.css">
    <?php include 'partials/head-css.php' ?>
</head>

<body>

    <!-- Top Bar Start -->
    <?php include 'partials/topbar.php' ?>
    <!-- Top Bar End -->
    <!-- leftbar-tab-menu -->
    <?php include 'partials/startbar.php' ?>
    <!-- end leftbar-tab-menu-->

    <div class="page-wrapper">
        <div class="page-content">
            <div class="container-xxl">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Gerenciamento de Configurações de Afiliados</h4>
                            </div>

                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <!-- CPA Nível 1 -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-user"></i> CPA Nível 1</h5>
                                                    <input type="text" name="cpaLvl1" class="form-control"
                                                        value="<?= $config['cpaLvl1'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- CPA Nível 2 -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-group"></i> CPA Nível 2
                                                    </h5>
                                                    <input type="text" name="cpaLvl2" class="form-control"
                                                        value="<?= $config['cpaLvl2'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- CPA Nível 3 -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-community"></i> CPA Nível 3
                                                    </h5>
                                                    <input type="text" name="cpaLvl3" class="form-control"
                                                        value="<?= $config['cpaLvl3'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Chance de CPA -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-percentage-circle"></i>
                                                        Chance de CPA (%)</h5>
                                                    <input type="text" name="chanceCpa" class="form-control"
                                                        value="<?= $config['chanceCpa'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- RevShare Falso -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-percentage-circle"></i>
                                                        RevShare Falso (%)</h5>
                                                    <input type="text" name="revShareFalso" class="form-control"
                                                        value="<?= $config['revShareFalso'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- RevShare Nível 1 -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-percent-rotate-out"></i>
                                                        RevShare Nível 1 (%)</h5>
                                                    <input type="text" name="revShareLvl1" class="form-control"
                                                        value="<?= $config['revShareLvl1'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- RevShare Nível 2 -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-percent-rotate-out"></i>
                                                        RevShare Nível 2 (%)</h5>
                                                    <input type="text" name="revShareLvl2" class="form-control"
                                                        value="<?= $config['revShareLvl2'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- RevShare Nível 3 -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-percent-rotate-out"></i>
                                                        RevShare Nível 3 (%)</h5>
                                                    <input type="text" name="revShareLvl3" class="form-control"
                                                        value="<?= $config['revShareLvl3'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Depósito Mínimo para CPA -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-send-dollars"></i> Depósito
                                                        Mínimo para CPA</h5>
                                                    <input type="text" name="minDepForCpa" class="form-control"
                                                        value="<?= $config['minDepForCpa'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Valor Mínimo para Resgate -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-send-dollars"></i> Valor
                                                        Mínimo para Resgate</h5>
                                                    <input type="text" name="minResgate" class="form-control"
                                                        value="<?= $config['minResgate'] ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success">Salvar Configurações</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div><!-- end row -->
            </div><!-- container -->
        </div><!-- page content -->
    </div><!-- page-wrapper -->

    <!-- Toast container -->
    <div id="toastPlacement" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <!-- Javascript -->
    <?php include 'partials/vendorjs.php' ?>
    <script src="assets/js/app.js"></script>

    <!-- Função de Toast -->
    <script>
        function showToast(type, message) {
            var toastPlacement = document.getElementById('toastPlacement');
            var toast = document.createElement('div');
            toast.className = `toast align-items-center bg-light border-0 fade show`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.innerHTML = `
                <div class="toast-header">
                    <img src="assets/images/logo-sm.png" alt="" height="20" class="me-1">
                    <h5 class="me-auto my-0">[BlackScript]</h5>
                    <small>Agora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">${message}</div>
            `;
            toastPlacement.appendChild(toast);

            var bootstrapToast = new bootstrap.Toast(toast);
            bootstrapToast.show();

            setTimeout(function() {
                bootstrapToast.hide();
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }
    </script>

    <!-- Exibir o Toast baseado nas ações do formulário -->
    <?php if ($toastType && $toastMessage): ?>
        <script>
            showToast('<?= $toastType ?>', '<?= $toastMessage ?>');
        </script>
    <?php endif; ?>

</body>

</html>