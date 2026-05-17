<?php include 'partials/html.php' ?>

<?php
#======================================#
ini_set('display_errors', 0);
error_reporting(E_ALL);
#======================================#
session_start();
include_once "./services/database.php";  // Verifique se este arquivo configura corretamente $mysqli
include_once "./services/funcao.php";
include_once "./services/crud.php";
include_once "./services/crud-adm.php";
include_once './services/checa_login_adm.php';
include_once "./services/CSRF_Protect.php";
$csrf = new CSRF_Protect();

# Verificar se a conexão com o banco está funcionando
if (!$mysqli) {
    die("Erro ao conectar com o banco de dados");
} else {
    //echo "Conexão com o banco de dados bem-sucedida.";  // Adicione essa linha temporariamente
}

# Expulsar usuário bloqueado
checa_login_adm();

if ($_SESSION['data_adm']['status'] != '1') {
    echo "<script>setTimeout(function() { window.location.href = 'bloqueado.php'; }, 0);</script>";
    exit();
}

# Função para buscar os dados atuais da tabela afiliados_config
function get_afiliados_config()
{
    global $mysqli;
    $qry = "SELECT gateway_default FROM config WHERE id=1";
    $result = mysqli_query($mysqli, $qry);

    if (!$result) {
        echo "Erro ao buscar dados: " . mysqli_error($mysqli);
        return null;
    }

    return mysqli_fetch_assoc($result);
}

# Função para atualizar os dados da tabela afiliados_config
function update_config($data)
{
    global $mysqli;

    $qry = $mysqli->prepare("UPDATE config SET 
        gateway_default = ? 
        WHERE id = 1");

    if (!$qry) {
        echo "Erro na preparação da consulta: " . $mysqli->error;
        return false;
    }

    $qry->bind_param(
        "s",
        $data['gateway_default']
    );

    if (!$qry->execute()) {
        echo "Erro ao atualizar: " . $qry->error;
        return false;
    }

    return true;
}

# Buscar os dados atuais
$config = get_afiliados_config();

$toastType = null; // Variável para definir o tipo de Toast
$toastMessage = ''; // Variável para definir a mensagem do Toast

# Se o formulário for enviado, atualizar os dados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'gateway_default' => $_POST['gateway_default'],
    ];

    if (update_config($data)) {
        $toastType = 'success';
        $toastMessage = 'Configurações de nomes atualizadas com sucesso!';
    } else {
        $toastType = 'error';
        $toastMessage = 'Erro ao atualizar as configurações. Verifique os dados e tente novamente.';
    }
}
?>

<head>
    <?php $title = "Configurações de Gateway default";
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
                                <h4 class="card-title">Gerenciamento de Gateway default</h4>
                            </div>

                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <!-- Nome -->
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gateway_default" class="form-label">Gateway padrão</label>
                                                <select name="gateway_default" class="form-select" required>
                                                    <option value="digitopay" <?= $config['gateway_default'] == 'digitopay' ? 'selected' : '' ?>>Digitopay</option>
                                                    <option value="pixup" <?= $config['gateway_default'] == 'pixup' ? 'selected' : '' ?>>BsPay / PixUP</option>
                                                    <option value="royalbenk" <?= $config['gateway_default'] == 'royalbenk' ? 'selected' : '' ?>>Royalbenk</option>
                                                    <option value="suitpay" <?= $config['gateway_default'] == 'suitpay' ? 'selected' : '' ?>>Suitpay</option>
                                                    <option value="PaguePix" <?= $config['gateway_default'] == 'PaguePix' ? 'selected' : '' ?>>PaguePix</option>
                                                </select>
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