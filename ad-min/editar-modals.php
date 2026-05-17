<?php include 'partials/html.php' ?>

<?php
#======================================#
ini_set('display_errors', 0);
error_reporting(E_ALL);
#======================================#
session_start();
include_once "services/database.php";
//include_once 'logs/registrar_logs.php';
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
/* if ($_SESSION['data_adm']['link'] != '1') {
    echo "<script>setTimeout(function() { window.location.href = 'bloqueado.php'; }, 0);</script>";
    exit();
} */

# Função para buscar os modals
function get_modals()
{
    global $mysqli;
    $qry = "SELECT * FROM modal_images";
    $result = mysqli_query($mysqli, $qry);
    $modals = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $modals[] = $row;
        }
    }
    return $modals;
}

# Função para atualizar o modal
function update_modal($id, $name, $link, $img = null)
{
    var_dump($img);
    global $mysqli;

    if ($img) {
        $qry = $mysqli->prepare("UPDATE modal_images SET name = ?, img = ?, link = ? WHERE id = ?");
        $qry->bind_param("sssi", $name, $img, $link, $id);
    } else {
        $qry = $mysqli->prepare("UPDATE modal_images SET name = ?, link = ? WHERE id = ?");
        $qry->bind_param("ssi", $name, $link, $id);
    }

    return $qry->execute();
}

# Se o formulário for enviado, atualizar os dados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $link = $_POST['link'];

    # Buscar a imagem atual no banco de dados
    $query = "SELECT img FROM modal_images WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $modal = $result->fetch_assoc();
    $img = $modal['img']; // Manter o nome da imagem atual

    # Verificar se uma nova imagem foi enviada
    if (!empty($_FILES['img']['name'])) {
        $file_ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
        $uuid = uniqid(); // Gerar UUID único
        $upload_dir = "../uploads/"; // A pasta uploads está um nível acima
        $img_path = $upload_dir . $uuid.'.'.$file_ext; // Usar o mesmo nome da imagem que já está no banco

        # Mover a nova imagem para o caminho correto, substituindo a imagem existente
        if (move_uploaded_file($_FILES["img"]["tmp_name"], $img_path)) {
           $img =$uuid.'.'.$file_ext;
            // A imagem foi movida com sucesso
        } else {
            $toastType = 'error';
            $toastMessage = 'Erro ao enviar a imagem. Tente novamente.';
        }
    }

    # Atualizar o modal no banco de dados
    if (update_modal($id, $name, $link, $img)) {
        $toastType = 'success';
        $toastMessage = 'modal atualizado com sucesso!';
    } else {
        $toastType = 'error';
        $toastMessage = 'Erro ao atualizar o modal. Tente novamente.';
    }
}




# Buscar os modals atuais
$modals = get_modals();
?>

<head>
    <?php $title = "Gerenciamento da Modal";
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
                                <h4 class="card-title">Gerenciamento da Modal</h4>
                            </div>

                            <div class="card-body">
                                <table class="table table-centered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Imagem</th>
                                            <th>link</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($modals as $modal): ?>
                                            <tr>
                                                <td><?= $modal['id']; ?></td>
                                                <td><?= $modal['name']; ?></td>
                                                <td><img src="/uploads/<?= $modal['img']; ?>" alt="modal" width="100"></td>
                                                <td><?= $modal['link']  ?></td>
                                                <td>
                                                    <button class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#editmodalModal<?= $modal['id']; ?>">Editar</button>
                                                </td>
                                            </tr>

                                            <!-- Modal de Edição -->
                                            <div class="modal fade" id="editmodalModal<?= $modal['id']; ?>" tabindex="-1" aria-labelledby="editmodalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editmodalLabel">Editar Item</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="POST" enctype="multipart/form-data">
                                                                <input type="hidden" name="id" value="<?= $modal['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label for="name" class="form-label">Título</label>
                                                                    <input type="text" class="form-control" name="name" value="<?= $modal['name']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="img" class="form-label">Imagem</label>
                                                                    <input type="file" class="form-control" name="img">
                                                                    <small class="text-muted">Deixe em branco se não quiser alterar a imagem.</small>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="link" class="form-label">Link</label>
                                                                    <input type="text" class="form-control" name="link" value="<?= $modal['link']; ?>" required>
                                                                </div>

                                                                <div class="text-center">
                                                                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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