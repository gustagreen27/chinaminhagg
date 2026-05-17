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

// Função para buscar todos os administradores
// Função para buscar todos os administradores
function get_admins()
{
    global $mysqli;
    $qry = "SELECT * FROM admin_users";
    $result = mysqli_query($mysqli, $qry);
    $admins = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $admins[] = $row;
    }
    return $admins;
}

// Função para atualizar os dados de um administrador
function update_admin($data)
{
    global $mysqli;
    
    // Atualização da senha se estiver preenchida
    if (!empty($data['senha'])) {
        $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT, array("cost" => 10));
        $qry = $mysqli->prepare("UPDATE admin_users SET nome = ?, email = ?, contato = ?, nivel = ?, status = ?, avatar = ?, senha = ? WHERE id = ?");
        $qry->bind_param(
            "sssisssi",
            $data['nome'],
            $data['email'],
            $data['contato'],
            $data['nivel'],
            $data['status'],
            $data['avatar'],
            $senha_hash,
            $data['id']
        );
    } else {
        $qry = $mysqli->prepare("UPDATE admin_users SET nome = ?, email = ?, contato = ?, nivel = ?, status = ?, avatar = ? WHERE id = ?");
        $qry->bind_param(
            "sssissi",
            $data['nome'],
            $data['email'],
            $data['contato'],
            $data['nivel'],
            $data['status'],
            $data['avatar'],
            $data['id']
        );
    }
    
    return $qry->execute();
}

// Função para adicionar novo administrador
function add_admin($data)
{
    global $mysqli;
    // Criptografando a senha
    $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT, array("cost" => 10));
    $qry = $mysqli->prepare("INSERT INTO admin_users (nome, email, contato, senha, nivel, status, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $qry->bind_param(
        "sssssis",
        $data['nome'],
        $data['email'],
        $data['contato'],
        $senha_hash,
        $data['nivel'],
        $data['status'],
        $data['avatar']
    );
    return $qry->execute();
}

// Atualizar administrador existente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_admin'])) {
    $data = [
        'id' => intval($_POST['id']),
        'nome' => $_POST['nome'],
        'email' => $_POST['email'],
        'contato' => $_POST['contato'],
        'senha' => $_POST['senha'], // Pode estar vazia
        'nivel' => intval($_POST['nivel']),
        'status' => intval($_POST['status']),
        'avatar' => $_POST['avatar']
    ];

    if (update_admin($data)) {
        $toastType = 'success';
        $toastMessage = 'Administrador atualizado com sucesso!';
    } else {
        $toastType = 'error';
        $toastMessage = 'Erro ao atualizar o administrador.';
    }
}

// Adicionar novo administrador
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $data = [
        'nome' => $_POST['nome'],
        'email' => $_POST['email'],
        'contato' => $_POST['contato'],
        'senha' => $_POST['senha'],
        'nivel' => intval($_POST['nivel']),
        'status' => intval($_POST['status']),
        'avatar' => $_POST['avatar']
    ];

    if (add_admin($data)) {
        $toastType = 'success';
        $toastMessage = 'Novo administrador adicionado com sucesso!';
    } else {
        $toastType = 'error';
        $toastMessage = 'Erro ao adicionar o administrador.';
    }
}

$admins = get_admins(); // Buscar todos os administradores
?>


<head>
<?php $title = "Gerenciamento de Provedores";
    include 'partials/title-meta.php' ?>

    <link rel="stylesheet" href="assets/libs/jsvectormap/jsvectormap.min.css">
    <?php include 'partials/head-css.php' ?>
</head>
<body>
    <!-- Top Bar Start -->
    <?php include 'partials/topbar.php' ?>
    <!-- End Top Bar -->

    <!-- Left Sidebar Start -->
    <?php include 'partials/startbar.php' ?>
    <!-- End Left Sidebar -->

    <div class="page-wrapper">
        <div class="page-content">
            <div class="container-xxl">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Gerenciamento de Administradores</h4>
                                <button class="btn btn-success float-end" data-bs-toggle="modal" data-bs-target="#addAdminModal">Adicionar Novo Administrador</button>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Contato</th>
                                            <th>Nível</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($admins as $admin): ?>
                                            <tr>
                                                <td><?= $admin['nome'] ?></td>
                                                <td><?= $admin['email'] ?></td>
                                                <td><?= $admin['contato'] ?></td>
                                                <td><?= $admin['nivel'] ?></td>
                                                <td><?= $admin['status'] == 1 ? 'Ativo' : 'Inativo' ?></td>
                                                <td>
                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editAdminModal<?= $admin['id'] ?>">Editar</button>
                                                </td>
                                            </tr>

                                            <!-- Modal de Edição -->
                                            <div class="modal fade" id="editAdminModal<?= $admin['id'] ?>" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editAdminModalLabel">Editar Administrador</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="nome" class="form-label">Nome</label>
                                                                    <input type="text" name="nome" class="form-control" value="<?= $admin['nome'] ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="email" class="form-label">Email</label>
                                                                    <input type="email" name="email" class="form-control" value="<?= $admin['email'] ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="contato" class="form-label">Senha</label>
                                                                    <input type="text" name="senha" class="form-control" value="<?= $admin['senha'] ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="contato" class="form-label">Contato</label>
                                                                    <input type="text" name="contato" class="form-control" value="<?= $admin['contato'] ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="nivel" class="form-label">Nível</label>
                                                                    <input type="number" name="nivel" class="form-control" value="<?= $admin['nivel'] ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="status" class="form-label">Status</label>
                                                                    <select name="status" class="form-select">
                                                                        <option value="1" <?= $admin['status'] == 1 ? 'selected' : '' ?>>Ativo</option>
                                                                        <option value="0" <?= $admin['status'] == 0 ? 'selected' : '' ?>>Inativo</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="avatar" class="form-label">Avatar</label>
                                                                    <input type="text" name="avatar" class="form-control" value="<?= $admin['avatar'] ?>">
                                                                </div>
                                                                <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                                <button type="submit" name="edit_admin" class="btn btn-primary">Salvar alterações</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Modal de Adição -->
                        <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addAdminModalLabel">Adicionar Novo Administrador</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="nome" class="form-label">Nome</label>
                                                <input type="text" name="nome" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="contato" class="form-label">Contato</label>
                                                <input type="text" name="contato" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="senha" class="form-label">Senha</label>
                                                <input type="password" name="senha" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="nivel" class="form-label">Nível</label>
                                                <input type="number" name="nivel" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="1">Ativo</option>
                                                    <option value="0">Inativo</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="avatar" class="form-label">Avatar</label>
                                                <input type="text" name="avatar" class="form-control">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                            <button type="submit" name="add_admin" class="btn btn-success">Adicionar Administrador</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                </div><!-- end row -->
            </div><!-- container -->
        </div><!-- page content -->
    </div><!-- page-wrapper -->

    <?php include 'partials/vendorjs.php' ?>
    <script src="assets/js/app.js"></script>
</body>
</html>
