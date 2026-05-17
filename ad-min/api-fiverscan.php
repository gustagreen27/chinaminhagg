<?php include 'partials/html.php' ?>

<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
include_once "services/database.php";
include_once 'logs/registrar_logs.php';
include_once "services/funcao.php";
include_once "services/crud.php";
include_once "services/crud-adm.php";
include_once 'services/checa_login_adm.php';
include_once "services/CSRF_Protect.php";
$csrf = new CSRF_Protect();

checa_login_adm();

if ($_SESSION['data_adm']['status'] != '1') {
    echo "<script>setTimeout(function() { window.location.href = 'bloqueado.php'; }, 0);</script>";
    exit();
}

function get_afiliados_config()
{
    global $mysqli;
    $qry = "SELECT * FROM fiverscan WHERE id=1";
    $result = mysqli_query($mysqli, $qry);
    return mysqli_fetch_assoc($result);
}

function get_afiliados_config2()
{
    global $mysqli;
    $qry = "SELECT * FROM payigaming WHERE id=1";
    $result = mysqli_query($mysqli, $qry);
    return mysqli_fetch_assoc($result);
}

function update_config($data)
{
    global $mysqli;
    $qry = $mysqli->prepare("UPDATE fiverscan SET 
        url = ?, 
        agent_code = ?, 
        agent_token = ? 
        WHERE id = 1");
    $qry->bind_param("sss", $data['url'], $data['agent_code'], $data['agent_token']);
    return $qry->execute();
}

function update_gateway_status($gateway, $status)
{
    global $mysqli;
    if ($gateway === 'fiverscan') {
        $qry = "UPDATE fiverscan SET ativo = ? WHERE id = 1";
        $stmt = $mysqli->prepare($qry);
        $stmt->bind_param("i", $status);
        $stmt->execute();
        $qry = "UPDATE payigaming SET ativo = ? WHERE id = 1";
        $status = $status ? 0 : 1; // Inverte o estado para o outro gateway
    } else {
        $qry = "UPDATE payigaming SET ativo = ? WHERE id = 1";
        $stmt = $mysqli->prepare($qry);
        $stmt->bind_param("i", $status);
        $stmt->execute();
        $qry = "UPDATE fiverscan SET ativo = ? WHERE id = 1";
        $status = $status ? 0 : 1; // Inverte o estado para o outro gateway
    }
    $stmt = $mysqli->prepare($qry);
    $stmt->bind_param("i", $status);
    return $stmt->execute();
}

$toastType = null;
$toastMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'url' => $_POST['url'],
        'agent_code' => $_POST['agent_code'], // Certifique-se de que este campo existe no seu formulário
        'agent_token' => $_POST['agent_token'], // Certifique-se de que este campo existe no seu formulário
    ];

    if (update_config($data)) {
        $toastType = 'success';
        $toastMessage = 'Credenciais atualizadas com sucesso!';
    } else {
        $toastType = 'error';
        $toastMessage = 'Erro ao atualizar as Credenciais. Tente novamente.';
    }
}

$config = get_afiliados_config();
$config2 = get_afiliados_config2();
?>


<head>
    <?php $title = "Configurações de Credenciais";
    include 'partials/title-meta.php' ?>
    <link rel="stylesheet" href="assets/libs/jsvectormap/jsvectormap.min.css">
    <?php include 'partials/head-css.php' ?>
</head>

<body>
    <?php include 'partials/topbar.php' ?>
    <?php include 'partials/startbar.php' ?>

    <div class="page-wrapper">
        <div class="page-content">
            <div class="container-xxl">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Seleção de Provedor</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-body">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="suitpaySwitch" <?= $config['ativo'] ? 'checked' : ''; ?> onclick="toggleGateway('suitpay')">
                                                    <label class="form-check-label" for="suitpaySwitch">Api 10/12/16 Jogos</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-body">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="digitopaySwitch" <?= $config2['ativo'] ? 'checked' : ''; ?> onclick="toggleGateway('digitopay')">
                                                    <label class="form-check-label" for="digitopaySwitch">PayIgaming</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end row -->
            </div>
            <div class="container-xxl">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Gerenciamento de Credenciais(Fiverscan)</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-user"></i> Agent Token (10/12/16 Jogos)</h5>
                                                    <input type="text" name="agent_code" class="form-control" value="<?= $config['agent_code'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-group"></i> Secret Key (10/12/16 Jogos)</h5>
                                                    <input type="text" name="agent_token" class="form-control" value="<?= $config['agent_token'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-community"></i> Endpoint (10/12/16 Jogos)</h5>
                                                    <input type="text" name="url" class="form-control" value="<?= $config['url'] ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success">Salvar Configurações Api</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div><!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Gerenciamento de Credenciais (PayIgaming)</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-user"></i> Client ID (PayIgaming)</h5>
                                                    <input type="text" name="client_id" class="form-control" value="<?= $config2['agent_code'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-group"></i> Client Secret (PayIgaming)</h5>
                                                    <input type="text" name="client_secret" class="form-control" value="<?= $config2['agent_token'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-community"></i> Endpoint (PayIgaming)</h5>
                                                    <input type="text" name="url" class="form-control" value="<?= $config2['url'] ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success">Salvar Configurações PayIgaming</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div><!-- end row -->
            </div><!-- container -->
        </div><!-- page content -->
    </div><!-- page-wrapper -->

    <div id="toastPlacement" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <?php include 'partials/vendorjs.php' ?>
    <script src="assets/js/app.js"></script>
    <script>
        function toggleGateway(activeGateway) {
            const suitpaySwitch = document.getElementById('suitpaySwitch');
            const digitopaySwitch = document.getElementById('digitopaySwitch');

            if (activeGateway === 'suitpay') {
                digitopaySwitch.checked = false; // Desativa Digitopay
                updateGatewayStatus('suitpay', 1);
                updateGatewayStatus('digitopay', 0);
            } else {
                suitpaySwitch.checked = false; // Desativa Suitpay
                updateGatewayStatus('digitopay', 1);
                updateGatewayStatus('suitpay', 0);
            }
        }

        function updateGatewayStatus(gateway, status) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "fetch/update_provedor.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    console.log(xhr.responseText); // Log do retorno para depuração
                } else {
                    console.error('Erro ao atualizar o status do gateway.');
                }
            };
            xhr.send("gateway=" + gateway + "&status=" + status + "&csrf_token=<?= $csrf->getToken() ?>");
        }
    </script>

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

    <?php if ($toastType && $toastMessage): ?>
        <script>
            showToast('<?= $toastType ?>', '<?= $toastMessage ?>');
        </script>
    <?php endif; ?>

</body>

</html>