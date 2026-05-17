<?php include 'partials/html.php' ?>

<?php
#======================================#
ini_set('display_errors', 1);
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
/* if ($_SESSION['data_adm']['value'] != '1') {
    echo "<script>setTimeout(function() { window.location.href = 'bloqueado.php'; }, 0);</script>";
    exit();
} */

# Função para buscar os tarefas
function get_tarefas()
{
    global $mysqli;
    $qry = "SELECT * FROM tarefas";
    $result = mysqli_query($mysqli, $qry);
    $tarefas = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $tarefas[] = $row;
        }
    }
    return $tarefas;
}

# Função para atualizar o tarefa
function update_tarefa($id, $name, $value = null, $active = 1)
{
    global $mysqli;

    $qry = $mysqli->prepare("UPDATE tarefas SET name = ?, value = ?, active = ? WHERE id = ?");
    $qry->bind_param("sdii", $name, $value, $active, $id);

    return $qry->execute();
}

# Se o formulário for enviado para atualização ou inserção
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $name = $_POST['name'];
    $value = floatval($_POST['value']);
    $active = (int) $_POST['active'];

    # Se houver ID, atualiza a mensagem
    if ($id) {
        if (update_tarefa($id, $name, $value, $active)) {
            $toastType = 'success';
            $toastMessage = 'Tarefa atualizada com sucesso!';
        } else {
            $toastType = 'error';
            $toastMessage = 'Erro ao atualizar a tarefa. Tente novamente.';
        }
    }
}

# Buscar os tarefas atuais
$tarefas = get_tarefas();
?>

<head>
    <?php $title = "Gerenciamento de Tarefas";
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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Gerenciamento de Tarefas</h4>
                            </div>

                            <div class="card-body">
                                <table class="table table-centered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Título</th>
                                            <th>Valor</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tarefas as $tarefa): ?>
                                            <tr>
                                                <td><?= $tarefa['id']; ?></td>
                                                <td><?= $tarefa['name']; ?></td>
                                                <td><?= 'R$ ' . number_format($tarefa['value'], 2, ',', '.') ?></td>
                                                <?php if ($tarefa['active'] === 1 || $tarefa['active'] === '1') { ?>
                                                    <td>
                                                        <h5><span class="badge text-bg-primary text-black">Ativo</span></h5>
                                                    </td>
                                                <?php } else { ?>
                                                    <td>
                                                        <h5><span class="badge text-bg-warning text-black">Inativo</span></h5>
                                                    </td>
                                                <?php } ?>
                                                <td>
                                                    <button class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#editBannerModal<?= $tarefa['id']; ?>">Editar</button>
                                                </td>
                                            </tr>

                                            <!-- Modal de Edição -->
                                            <div class="modal fade" id="editBannerModal<?= $tarefa['id']; ?>" tabindex="-1"
                                                aria-labelledby="editBannerLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editBannerLabel">Editar Anuncio</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="POST" enctype="multipart/form-data">
                                                                <input type="hidden" name="id"
                                                                    value="<?= $tarefa['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label for="name" class="form-label">Título</label>
                                                                    <input type="text" class="form-control" name="name"
                                                                        value="<?= $tarefa['name']; ?>" required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="value" class="form-label">Valor <span style="font-size:14px;color:red;">* (Separe os centavos por .)</label>
                                                                    <input type="decimal" class="form-control" name="value"
                                                                        value="<?= $tarefa['value']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="value" class="form-label">Ativo <span style="font-size:14px;color:red;">*</label>
                                                                    <select name="active" class="form-select" required>
                                                                        <option value="1" <?= $tarefa['active'] == 1 ? 'selected' : '' ?>>Ativo</option>
                                                                        <option value="0" <?= $tarefa['active'] == 0 ? 'selected' : '' ?>>Inativo</option>
                                                                    </select>
                                                                </div>
                                                                <div class="text-center">
                                                                    <button type="submit" class="btn btn-success">Salvar
                                                                        Alterações</button>
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