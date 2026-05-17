<?php include 'partials/html.php' ?>

<?php
#======================================#
ini_set('display_errors', 1);
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
//inicio do script expulsa usuario bloqueado
if ($_SESSION['data_adm']['status'] != '1') {
    echo "<script>setTimeout(function() { window.location.href = 'bloqueado.php'; }, 0);</script>";
    exit();
}

# Função para buscar os dados atuais da tabela afiliados_config
function get_afiliados_config()
{
    global $mysqli;
    $qry = "SELECT * FROM config WHERE id=1";
    $result = mysqli_query($mysqli, $qry);
    return mysqli_fetch_assoc($result);
}

# Função para atualizar os dados da tabela afiliados_config
function update_config($data)
{
    global $mysqli;
    $qry = $mysqli->prepare("UPDATE config SET 
        nome = ?, 
        nome_site = ?, 
        descricao = ?, 
        grupoplataforma = ?, 
        keyword = ?,
        marquee = ?,
        popup_download = ?
        WHERE id = 1");

    $qry->bind_param(
        "sssssss",
        $data['nome'],
        $data['nome_site'],
        $data['descricao'],
        $data['grupoplataforma'],
        $data['keyword'],
        $data['marquee'],
        $data['popup_download']
    );
    return $qry->execute();
}

# Se o formulário for enviado, atualizar os dados
$toastType = null; // Variável para definir o tipo de Toast
$toastMessage = ''; // Variável para definir a mensagem do Toast

# Se o formulário for enviado, atualizar os dados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'nome' => $_POST['nome'],  // Texto, não usar floatval
        'nome_site' => $_POST['nome_site'],  // Texto, não usar floatval
        'descricao' => $_POST['descricao'],  // Texto, não usar floatval
        'grupoplataforma' => $_POST['grupoplataforma'],  // Texto, não usar floatval
        'keyword' => $_POST['keyword'],
        'marquee' => $_POST['marquee'], // Texto, não usar floatval
        'popup_download' => $_POST['popup_download'],  // Texto, não usar floatval
    ];

    if (update_config($data)) {
        $toastType = 'success';
        $toastMessage = 'Configurações de nomes atualizadas com sucesso!';
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
                                <h4 class="card-title">Gerenciamento de nomes da plataforma</h4>
                            </div>

                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <!-- Nome -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-user"></i> Nome da plataforma</h5>
                                                    <input type="text" name="nome" class="form-control"
                                                        value="<?= $config['nome'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Nome do Site -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-group"></i> Nome do Site
                                                    </h5>
                                                    <input type="text" name="nome_site" class="form-control"
                                                        value="<?= $config['nome_site'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Descrição -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-community"></i> Descrição
                                                    </h5>
                                                    <input type="text" name="descricao" class="form-control"
                                                        value="<?= $config['descricao'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Grupo da plataforma -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-percentage-circle"></i>
                                                        Grupo da plataforma</h5>
                                                    <input type="text" name="grupoplataforma" class="form-control"
                                                        value="<?= $config['grupoplataforma'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Popup Download -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-percentage-circle"></i>
                                                        Texto Do Popup De Download</h5>
                                                    <input type="text" name="popup_download" class="form-control"
                                                        value="<?= $config['popup_download'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Keyword/SEO -->
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title"><i class="iconoir-percentage-circle"></i>
                                                        Keyword/SEO</h5>
                                                    <input type="text" name="keyword" class="form-control"
                                                        value="<?= $config['keyword'] ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Texto Marquee -->
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-body">
                                                <h5 class="card-title"><i class="iconoir-percentage-circle"></i>
                                                    Texto Slide</h5>
                                                <input type="text" name="marquee" class="form-control"
                                                    value="<?= $config['marquee'] ?>" required>
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
                    <img src="/uploads/logo.webp" alt="" height="20" class="me-1">
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