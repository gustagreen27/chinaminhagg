<?php
#======================================#
ini_set('display_errors', 1);
error_reporting(E_ALL);
#======================================#
session_start();
include_once "./services/database.php"; // Certifique-se de que a conexão com PDO esteja correta.
include_once "./services/funcao.php";
include_once "./services/crud.php";
include_once "./services/crud-adm.php";
include_once './services/checa_login_adm.php';
include_once "./services/CSRF_Protect.php";
$csrf = new CSRF_Protect();
#======================================#
# Expulsa user
checa_login_adm();
#======================================#

# Função para buscar as credenciais atuais da tabela api38
function get_api38_config()
{
    global $pdo; // Usando a variável global $pdo (PDO connection)
    $qry = "SELECT * FROM api38 WHERE id = 1";
    $stmt = $pdo->prepare($qry);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

# Função para atualizar os dados da tabela api38
function update_api38_config($data)
{
    global $pdo;
    $qry = "UPDATE api38 SET 
        url = :url, 
        agent_code = :agent_code, 
        agent_secret = :agent_secret, 
        agent_token = :agent_token, 
        ativo = :ativo
        WHERE id = 1";

    $stmt = $pdo->prepare($qry);
    $stmt->bindParam(':url', $data['url']);
    $stmt->bindParam(':agent_code', $data['agent_code']);
    $stmt->bindParam(':agent_secret', $data['agent_secret']);
    $stmt->bindParam(':agent_token', $data['agent_token']);
    $stmt->bindParam(':ativo', $data['ativo'], PDO::PARAM_INT);

    // Execute e verifique se ocorreu algum erro
    if ($stmt->execute()) {
        return true;
    } else {
        // Captura os erros para debug
        $errorInfo = $stmt->errorInfo();
        error_log("Erro ao atualizar a tabela api38: " . $errorInfo[2]);
        return false;
    }
}

# Se o formulário for enviado, atualizar os dados
$toastType = null; // Variável para definir o tipo de Toast
$toastMessage = ''; // Variável para definir a mensagem do Toast

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validação básica dos dados de entrada
    if (!empty($_POST['url']) && !empty($_POST['agent_code']) && !empty($_POST['agent_secret']) && !empty($_POST['agent_token'])) {
        // Adiciona validação do comprimento
        if (strlen($_POST['agent_secret']) > 255) {
            $toastType = 'error';
            $toastMessage = 'O Agent Secret excede o tamanho máximo permitido.';
        } else {
            $data = [
                'url' => $_POST['url'],
                'agent_code' => $_POST['agent_code'],
                'agent_secret' => $_POST['agent_secret'],
                'agent_token' => $_POST['agent_token'],
                'ativo' => intval($_POST['ativo']),
            ];

            if (update_api38_config($data)) {
                $toastType = 'success';
                $toastMessage = 'Credenciais atualizadas com sucesso!';
            } else {
                $toastType = 'error';
                $toastMessage = 'Erro ao atualizar as credenciais. Tente novamente.';
            }
        }
    } else {
        $toastType = 'error';
        $toastMessage = 'Por favor, preencha todos os campos obrigatórios.';
    }
}


# Buscar os dados atuais
$api38_config = get_api38_config();
?>

<head>
    <?php $title = "Configurações do api38"; ?>
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
                                <h4 class="card-title">Gerenciamento de Credenciais (Api Pragmatic 38 Jogos)</h4>
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
                                                        value="<?= $api38_config['url'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Agent Code -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-user"></i> Agent Code</h5>
                                                    <input type="text" name="agent_code" class="form-control"
                                                        value="<?= $api38_config['agent_code'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Agent Code -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-user"></i> Agent Secret</h5>
                                                    <input type="text" name="agent_secret" class="form-control"
                                                        value="<?= $api38_config['agent_secret'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Agent Token -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-lock"></i> Agent Token</h5>
                                                    <input type="text" name="agent_token" class="form-control"
                                                        value="<?= $api38_config['agent_token'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Status Ativo -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-check-circle"></i> Ativo</h5>
                                                    <select name="ativo" class="form-select" required>
                                                        <option value="1" <?= $api38_config['ativo'] == 1 ? 'selected' : '' ?>>Sim</option>
                                                        <option value="0" <?= $api38_config['ativo'] == 0 ? 'selected' : '' ?>>Não</option>
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