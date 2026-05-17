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

# Função para fazer o upload e renomear o arquivo como .png
function upload_and_rename_as_png($file, $target_name)
{
    $upload_dir = "../uploads/"; // Diretório de uploads
    $target_file = $upload_dir . $target_name . '.webp'; // Caminho final do arquivo como .webp

    // Verifica se o arquivo enviado é uma imagem
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return false; // Não é uma imagem válida
    }

    // Obter a extensão original do arquivo
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Mover o arquivo para o diretório de uploads com o novo nome como .webp
    if ($file_extension === 'webp') {
        // Se já for WEBP, apenas mover
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return $target_name . '.webp';
        }
    } else {
        // Caso a imagem não seja WEBP, converter para WEBP
        $image = null;
        switch ($file_extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            case 'gif':
                $image = imagecreatefromgif($file['tmp_name']);
                break;
            default:
                return false; // Formato não suportado
        }

        if ($image !== null) {
            // Salva a imagem como WEBP
            if (imagewebp($image, $target_file)) {
                imagedestroy($image); // Libera a memória
                return $target_name . '.webp';
            }
        }
    }

    return false; // Retorna falso se houver falha
}

# Função para atualizar logo e/ou favicon na tabela config
function update_config_images($logo = null, $favicon = null)
{
    global $mysqli;

    $qry_string = "UPDATE config SET ";
    $params = [];
    $types = '';

    // Adicionar logo à consulta se estiver presente
    if ($logo !== null) {
        $qry_string .= "logo = ?, ";
        $params[] = $logo;
        $types .= 's';
    }

    // Adicionar favicon à consulta se estiver presente
    if ($favicon !== null) {
        $qry_string .= "favicon = ?, ";
        $params[] = $favicon;
        $types .= 's';
    }

    // Remover a última vírgula e espaço
    $qry_string = rtrim($qry_string, ', ') . " WHERE id = 1";

    $qry = $mysqli->prepare($qry_string);
    $qry->bind_param($types, ...$params);

    return $qry->execute();
}

# Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $toastType = null;
    $toastMessage = '';

    $logo = null;
    $favicon = null;

    // Verificar se o logo foi enviado
    if (!empty($_FILES['logo']['name'])) {
        $logo = upload_and_rename_as_png($_FILES['logo'], 'logo'); // Renomear o logo para 'logo.png'
        if (!$logo) {
            $toastType = 'error';
            $toastMessage = 'Erro ao enviar o logo. Verifique a extensão do arquivo.';
        }
    }

    // Verificar se o favicon foi enviado
    if (!empty($_FILES['favicon']['name'])) {
        $favicon = upload_and_rename_as_png($_FILES['favicon'], 'favicon'); // Renomear o favicon para 'favicon.png'
        if (!$favicon) {
            $toastType = 'error';
            $toastMessage = 'Erro ao enviar o favicon. Verifique a extensão do arquivo.';
        }
    }

    // Atualizar as imagens no banco de dados
    if ($logo || $favicon) {
        if (update_config_images($logo, $favicon)) {
            $toastType = 'success';
            $toastMessage = 'Imagens atualizadas com sucesso!';
        } else {
            $toastType = 'error';
            $toastMessage = 'Erro ao atualizar as imagens.';
        }
    }
}

# Buscar o caminho atual das imagens logo e favicon
$query = "SELECT logo, favicon FROM config WHERE id = 1";
$result = mysqli_query($mysqli, $query);
$config = mysqli_fetch_assoc($result);

?>

<head>
    <?php $title = "Gerenciamento de Identidade Visual"; ?>
    <?php include 'partials/title-meta.php' ?>
    <?php include 'partials/head-css.php' ?>
</head>

<style>
    .img-container {
        width: 100%;
        height: 150px;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }

    .img-container img {
        width: auto;
        object-fit: cover;
    }
</style>


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
                                <h4 class="card-title">Gerenciamento De Identidade Visual</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row mt-12">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <label for="logo" class="form-label">Logo</label>
                                                    <?php if (!empty($config['logo'])): ?>
                                                        <div class="mb-3">
                                                            <img src="/uploads/<?= $dataconfig['logo']; ?>?v=<?= filemtime('uploads/' . $dataconfig['logo']) ?>" class="img-fluid" alt="Logo" style="max-height: 150px;">
                                                        </div>
                                                    <?php else: ?>
                                                        <p class="text-muted">Nenhuma imagem de logo enviada ainda.</p>
                                                    <?php endif; ?>
                                                    <input type="file" name="logo" id="logo" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <label for="favicon" class="form-label">Fav Icon</label>
                                                    <?php if (!empty($config['favicon'])): ?>
                                                        <div class="mb-3">
                                                            <img src="/uploads/<?= $dataconfig['favicon']; ?>" class="img-fluid" alt="Favicon" style="max-height: 150px;">
                                                        </div>
                                                    <?php else: ?>
                                                        <p class="text-muted">Nenhuma imagem de favicon enviada ainda.</p>
                                                    <?php endif; ?>
                                                    <input type="file" name="favicon" id="favicon" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success">Salvar Alterações</button>
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