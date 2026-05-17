<?php include 'partials/html.php' ?>

<?php
#======================================#
ini_set('display_errors', 0);
error_reporting(E_ALL);
#======================================#
session_start();
include_once "services/database.php";
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

# Função para buscar as credenciais atuais da tabela playfiver
function get_playfiver_config()
{
    global $mysqli;
    $qry = "SELECT * FROM playfiver WHERE id = 1";  // Aqui você pode ajustar o ID de acordo com o necessário
    $result = mysqli_query($mysqli, $qry);
    return mysqli_fetch_assoc($result);
}

# Função para atualizar os dados da tabela playfiver
# Função para atualizar os dados da tabela playfiver
function update_playfiver_config($data)
{
    global $mysqli;
    $qry = $mysqli->prepare("UPDATE playfiver SET 
        url = ?, 
        agent_code = ?, 
        agent_token = ?, 
        agent_secret = ?, 
        ativo = ?
        WHERE id = 1");

    // Verificar se a query foi preparada corretamente
    if (!$qry) {
        return "Erro na preparação da query: " . $mysqli->error;
    }

    // Corrigido o bind_param para incluir o tipo do 'ativo' (que é um inteiro)
    $qry->bind_param(
        "ssssi",
        $data['url'],
        $data['agent_code'],
        $data['agent_token'],
        $data['agent_secret'],
        $data['ativo']
    );

    // Executar e verificar a execução
    if ($qry->execute()) {
        return true;
    } else {
        return "Erro ao executar: " . $qry->error;
    }
}


# Se o formulário for enviado, atualizar os dados
$toastType = null; // Variável para definir o tipo de Toast
$toastMessage = ''; // Variável para definir a mensagem do Toast

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'url' => $_POST['url'],
        'agent_code' => $_POST['agent_code'],
        'agent_token' => $_POST['agent_token'],
        'agent_secret' => $_POST['agent_secret'],
        'ativo' => intval($_POST['ativo']),
    ];

    $response = update_playfiver_config($data);

    if ($response === true) {
        $toastType = 'success';
        $toastMessage = 'Credenciais atualizadas com sucesso!';
    } else {
        $toastType = 'error';
        $toastMessage = 'Erro ao atualizar as credenciais: ' . $response;
    }
}


# Buscar os dados atuais
$playfiver_config = get_playfiver_config();
?>

<head>
    <?php $title = "Configurações do playfiver"; ?>
    <?php include 'partials/title-meta.php' ?>
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
                                <h4 class="card-title">Gerenciamento de Credenciais (Api Playfiver)</h4>
                            </div>

                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <!-- URL -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-community"></i> URL</h5>
                                                    <input type="text" name="url" class="form-control"
                                                        value="<?= $playfiver_config['url'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Agent Code -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-user"></i> Agent Code</h5>
                                                    <input type="text" name="agent_code" class="form-control"
                                                        value="<?= $playfiver_config['agent_code'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Agent Token -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-lock"></i> Agent Token</h5>
                                                    <input type="text" name="agent_token" class="form-control"
                                                        value="<?= $playfiver_config['agent_token'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Agent Secret -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-lock"></i> Agent Secret</h5>
                                                    <input type="text" name="agent_secret" class="form-control"
                                                        value="<?= $playfiver_config['agent_secret'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Status Ativo -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-check-circle"></i> Ativo</h5>
                                                    <select name="ativo" class="form-select" required>
                                                        <option value="1" <?= $playfiver_config['ativo'] == 1 ? 'selected' : '' ?>>Sim</option>
                                                        <option value="0" <?= $playfiver_config['ativo'] == 0 ? 'selected' : '' ?>>Não</option>
                                                    </select>
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