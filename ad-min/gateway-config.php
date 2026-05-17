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

# Função para buscar dados do Royal Bank
function get_royalbank_config()
{
    global $mysqli;
    $qry = "SELECT * FROM royalbenk WHERE id=1";
    $result = mysqli_query($mysqli, $qry);
    return mysqli_fetch_assoc($result);
}

# Função para buscar dados do PixUp
function get_pixup_config()
{
    global $mysqli;
    $qry = "SELECT * FROM pixup WHERE id=1";
    $result = mysqli_query($mysqli, $qry);
    return mysqli_fetch_assoc($result);
}

# Função para atualizar Royal Bank
function update_royalbank_config($data)
{
    global $mysqli;
    $qry = $mysqli->prepare("UPDATE royalbenk SET 
        url = ?, 
        api_key = ?,
        client_secret = ?,
        webhook_url = ?
        WHERE id = 1");

    $qry->bind_param(
        "ssss",
        $data['url'],
        $data['api_key'],
        $data['client_secret'],
        $data['webhook_url']
    );
    return $qry->execute();
}

# Função para atualizar PixUp
function update_pixup_config($data)
{
    global $mysqli;
    $qry = $mysqli->prepare("UPDATE pixup SET 
        url = ?, 
        client_id = ?,
        client_secret = ?
        WHERE id = 1");

    $qry->bind_param(
        "sss",
        $data['url'],
        $data['client_id'],
        $data['client_secret']
    );
    return $qry->execute();
}

# Se o formulário for enviado, atualizar os dados
$toastType = null;
$toastMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['gateway']) && $_POST['gateway'] === 'royalbank') {
        $data = [
            'url' => $_POST['url'],
            'api_key' => $_POST['api_key'],
            'client_secret' => $_POST['client_secret'],
            'webhook_url' => $_POST['webhook_url']
        ];

        if (update_royalbank_config($data)) {
            $toastType = 'success';
            $toastMessage = 'Configurações Royal Bank atualizadas com sucesso!';
        } else {
            $toastType = 'error';
            $toastMessage = 'Erro ao atualizar Royal Bank. Tente novamente.';
        }
    } elseif (isset($_POST['gateway']) && $_POST['gateway'] === 'pixup') {
        $data = [
            'url' => $_POST['url'],
            'client_id' => $_POST['client_id'],
            'client_secret' => $_POST['client_secret']
        ];

        if (update_pixup_config($data)) {
            $toastType = 'success';
            $toastMessage = 'Configurações PixUp atualizadas com sucesso!';
        } else {
            $toastType = 'error';
            $toastMessage = 'Erro ao atualizar PixUp. Tente novamente.';
        }
    }
}

# Buscar os dados atuais
$royalbank_config = get_royalbank_config();
$pixup_config = get_pixup_config();
?>

<head>
    <?php $title = "Configurações de Gateways";
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
                                <h4 class="card-title">Configurações de Gateways de Pagamento</h4>
                                <p class="card-subtitle">Gerencie as credenciais dos seus gateways de pagamento</p>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <!-- Royal Bank -->
                                    <div class="col-md-6">
                                        <div class="card border-success">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="card-title mb-0">
                                                    <i class="iconoir-crown"></i> Royal Bank
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="royalbankSwitch" checked disabled>
                                                    <label class="form-check-label" for="royalbankSwitch">
                                                        <strong>Gateway Ativo</strong>
                                                    </label>
                                                </div>
                                                
                                                <form method="POST" action="">
                                                    <input type="hidden" name="gateway" value="royalbank">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">API Key</label>
                                                        <input type="text" name="api_key" class="form-control"
                                                            value="<?= $royalbank_config['api_key'] ?>" 
                                                            placeholder="Sua API Key da Royal Bank" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Secret Key</label>
                                                        <input type="text" name="client_secret" class="form-control"
                                                            value="<?= $royalbank_config['client_secret'] ?>" 
                                                            placeholder="Client Secret (opcional)">
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Endpoint</label>
                                                        <input type="text" name="url" class="form-control"
                                                            value="<?= $royalbank_config['url'] ?>" 
                                                            placeholder="https://api.royalbanking.com.br" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Webhook URL</label>
                                                        <input type="text" name="webhook_url" class="form-control"
                                                            value="<?= $royalbank_config['webhook_url'] ?>" 
                                                            placeholder="https://seudominio.com/gateway/webhook.php" required>
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="iconoir-save"></i> Salvar Royal Bank
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PixUp -->
                                    <div class="col-md-6">
                                        <div class="card border-info">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="card-title mb-0">
                                                    <i class="iconoir-pix"></i> PixUp
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="pixupSwitch" checked disabled>
                                                    <label class="form-check-label" for="pixupSwitch">
                                                        <strong>Gateway Ativo</strong>
                                                    </label>
                                                </div>
                                                
                                                <form method="POST" action="">
                                                    <input type="hidden" name="gateway" value="pixup">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Client ID</label>
                                                        <input type="text" name="client_id" class="form-control"
                                                            value="<?= $pixup_config['client_id'] ?>" 
                                                            placeholder="Client ID" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Client Secret</label>
                                                        <input type="text" name="client_secret" class="form-control"
                                                            value="<?= $pixup_config['client_secret'] ?>" 
                                                            placeholder="Client Secret" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Endpoint</label>
                                                        <input type="text" name="url" class="form-control"
                                                            value="<?= $pixup_config['url'] ?>" 
                                                            placeholder="https://api.pixupbr.com/v2/" required>
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-info w-100">
                                                        <i class="iconoir-save"></i> Salvar PixUp
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
