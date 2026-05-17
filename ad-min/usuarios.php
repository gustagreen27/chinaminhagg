<?php

include 'partials/html.php';
include_once('./services/database.php');

function deletarUsuario($id)
{
    global $pdo;
    $sql = "DELETE FROM usuarios WHERE id = :id";

    // Prepara a declaração
    $stmt = $pdo->prepare($sql);

    // Associa o parâmetro ID
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Executa a query
    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "Usuário deletado com sucesso."]);
    } else {
        echo json_encode(["status" => false, "message" => "Erro ao deletar o usuário."]);
    }
}

// Verifica se uma requisição AJAX foi feita
if (isset($_GET['deletarUsuario'])) {
    echo deletarUsuario($_GET['id']);
    exit; // Para garantir que não execute o resto do código HTML
}
?>

<head>
    <?php $title = "[BlackScript]"; ?>
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

    <?php
    // Capturar os parâmetros de busca e filtro
    $search_query = '';
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_query = mysqli_real_escape_string($mysqli, $_GET['search']);
    }

    $status_filter = '';
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $status_filter = (int) $_GET['status'];
    }

    // Configuração da paginação
    $limit = 50; // Limite de 50 usuários por página
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Consulta para contar o total de usuários
    $query_total_usuarios = "SELECT COUNT(*) AS total_usuarios FROM usuarios WHERE 1=1";
    if (!empty($search_query)) {
        $query_total_usuarios .= " AND (id LIKE '%$search_query%' OR mobile LIKE '%$search_query%')";
    }
    if ($status_filter !== '') {
        $query_total_usuarios .= " AND statusaff = $status_filter";
    }
    $result_total_usuarios = mysqli_query($mysqli, $query_total_usuarios);
    $total_usuarios = mysqli_fetch_assoc($result_total_usuarios)['total_usuarios'];

    // Cálculo do total de páginas
    $total_pages = ceil($total_usuarios / $limit);

    // Consulta para exibir os usuários com paginação e filtro
    $query_usuarios = "SELECT * FROM usuarios WHERE 1=1"; // Sempre verdadeiro para facilitar a adição de condições

    // Filtrar por busca (ID ou nome de usuário)
    if (!empty($search_query)) {
        $query_usuarios .= " AND (id LIKE '%$search_query%' OR mobile LIKE '%$search_query%')";
    }

    // Filtrar por status: Banido, Afiliado ou Usuário
    if ($status_filter !== '') {
        if ($status_filter == 2) {
            // Usuários banidos
            $query_usuarios .= " AND banido = 1";
        } else {
            // Outros status (Afiliado ou Usuário)
            $query_usuarios .= " AND statusaff = $status_filter";
        }
    }

    // Ordenar e paginar
    $query_usuarios .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $result_usuarios = mysqli_query($mysqli, $query_usuarios);

    ?>

    <div class="page-wrapper">
        <!-- Page Content-->
        <div class="page-content">
            <div class="container-xxl">
                <div class="row justify-content-center">
                    <div class="col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="card-title">Todos Usuários (<?= $total_usuarios; ?> no total)</h4>
                                    </div>
                                    <div class="col text-end">
                                        <a href="export/exportar_usuarios.php" class="btn btn-primary">Exportar Dados</a>
                                    </div>
                                </div>
                            </div><!--end card-header-->

                            <!-- Filtros e Busca -->
                            <div class="card-body pt-0">
                                <form method="GET" action="">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <input type="text" name="search" class="form-control"
                                                placeholder="Buscar por ID ou Nome do Usuário"
                                                value="<?= htmlspecialchars($search_query) ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <select name="status" class="form-select">
                                                <option value="">Filtrar por Status</option>
                                                <option value="2" <?= (isset($_GET['status']) && $_GET['status'] == '2') ? 'selected' : ''; ?>>Banido</option>
                                                <option value="1" <?= (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : ''; ?>>Afiliado</option>
                                                <option value="0" <?= (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : ''; ?>>Usuário</option>
                                            </select>

                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button type="submit" class="btn btn-success">Filtrar</button>
                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table class="table mb-0 table-centered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Id</th>
                                                <th>Usuário</th>
                                                <th>Saldo</th>
                                                <th>Depositado</th>
                                                <th>Sacado</th>
                                                <th>Cargo</th>
                                                <th class="text-end">Detalhes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($result_usuarios && mysqli_num_rows($result_usuarios) > 0) {
                                                while ($usuario = mysqli_fetch_assoc($result_usuarios)) {
                                                    // Definir o cargo com base nos dados da tabela
                                                    $cargo_badge = ($usuario['statusaff'] == '1') ? "<span class='badge bg-danger'>Afiliado</span>" : "<span class='badge bg-secondary'>Usuário</span>";

                                                    // Consultar total sacado e depositado
                                                    $query_sacado = "SELECT SUM(valor) AS total_sacado FROM solicitacao_saques WHERE id_user = {$usuario['id']} AND status = 1";
                                                    $result_sacado = mysqli_query($mysqli, $query_sacado);
                                                    $sacado = ($result_sacado && mysqli_num_rows($result_sacado) > 0) ? mysqli_fetch_assoc($result_sacado)['total_sacado'] : 0;

                                                    $query_depositado = "SELECT SUM(valor) AS total_depositado FROM transacoes WHERE usuario = {$usuario['id']} AND status = 'pago'";
                                                    $result_depositado = mysqli_query($mysqli, $query_depositado);
                                                    $depositado = ($result_depositado && mysqli_num_rows($result_depositado) > 0) ? mysqli_fetch_assoc($result_depositado)['total_depositado'] : 0;
                                            ?>
                                                    <tr>
                                                        <td><?= $usuario['id']; ?></td>
                                                        <td><?= $usuario['mobile']; ?></td>
                                                        <td>R$ <?= number_format($usuario['saldo'], 2, ',', '.'); ?></td>
                                                        <td>R$ <?= number_format($depositado, 2, ',', '.'); ?></td>
                                                        <td>R$ <?= number_format($sacado, 2, ',', '.'); ?></td>
                                                        <td><?= $cargo_badge; ?></td>
                                                        <td class="text-end">
                                                            <div class="dropdown d-inline-block">
                                                                <a class="dropdown-toggle arrow-none" id="dLabel11"
                                                                    data-bs-toggle="dropdown" href="#" role="button"
                                                                    aria-haspopup="false" aria-expanded="false">
                                                                    <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <a class="dropdown-item text-success"
                                                                        href="<?= $painel_adm_ver_usuarios . encodeAll($usuario['id']); ?>">
                                                                        <i class="las la-info-circle"></i> Detalhes
                                                                    </a>
                                                                    <Button class="<?= $usuario['id']; ?> dropdown-item text-danger" onclick="delUser('<?= $usuario['id'] ?>');">
                                                                        <i class="las la-ban"></i> Deletar Usuário
                                                                        </a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'>Sem dados disponíveis!</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table><!--end /table-->
                                </div><!--end /tableresponsive-->

                                <!-- Paginação -->
                                <?php if ($total_pages > 1): ?>
                                    <nav>
                                        <ul class="pagination justify-content-center">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Anterior">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Próximo">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                            </div><!--end card-body-->
                        </div>

                        <!-- Resumo Financeiro -->
                        <div class="row mt-4">
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Depositado</h5>
                                        <p class="text-muted mb-0">R$
                                            <?= number_format(total_dep_pagos_usuarios(), 2, ',', '.'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Sacado</h5>
                                        <p class="text-muted mb-0">R$
                                            <?= number_format(total_saques_usuarios(), 2, ',', '.'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Saldo Médio</h5>
                                        <p class="text-muted mb-0">R$
                                            <?= number_format(media_saldo_usuarios(), 2, ',', '.'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!--end col-->
                </div><!--end row-->
            </div><!-- container -->

            <!--Start Rightbar-->
            <?php include 'partials/endbar.php' ?>
            <!--end Rightbar-->
            <!--Start Footer-->
            <?php include 'partials/footer.php' ?>
            <!--end footer-->
        </div><!-- end page content -->
    </div><!-- end page-wrapper -->

    <div id="toastPlacement" class="toast-container position-fixed bottom-0 end-0 p-3"></div>
    <!-- Javascript  -->
    <?php include 'partials/vendorjs.php' ?>
    <script src="assets/js/app.js"></script>
</body>

</html>

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

        // Inicializar o Toast do Bootstrap
        var bootstrapToast = new bootstrap.Toast(toast);
        bootstrapToast.show();

        setTimeout(function() {
            bootstrapToast.hide(); // Esconder o toast após 3 segundos
            setTimeout(() => toast.remove(), 500); // Remove o toast após ele sumir
        }, 3000);
    }

    function delUser(id) {
        // Chama o próprio arquivo PHP, passando um parâmetro na URL para identificar que a função deve ser executada
        fetch(`?deletarUsuario=true&id=${id}`)
            .then(response => response.text()) // Obter a resposta como texto
            .then(data => {
                console.log(data);
                // Exibir o resultado da função PHP
                showToast('success', "Usuário excluído com sucesso!");
                setTimeout(function() {
                    window.location.reload();
                }, 4000);
            })
            .catch(error => console.error('Erro:', error));
    };
</script>

<?php
// Funções de totalização e média para o resumo financeiro
function total_dep_pagos_usuarios()
{
    global $mysqli;
    $qry = "SELECT SUM(valor) as total_soma FROM transacoes WHERE status = 'pago' AND tipo = 'deposito'";
    $result = mysqli_query($mysqli, $qry);
    return mysqli_fetch_assoc($result)['total_soma'] ?? 0;
}

function total_saques_usuarios()
{
    global $mysqli;
    $qry = "SELECT SUM(valor) as total_soma FROM solicitacao_saques WHERE status = 1";
    $result = mysqli_query($mysqli, $qry);
    return mysqli_fetch_assoc($result)['total_soma'] ?? 0;
}

function media_saldo_usuarios()
{
    global $mysqli;
    $qry = "SELECT AVG(saldo) as media_saldo FROM usuarios";
    $result = mysqli_query($mysqli, $qry);
    return mysqli_fetch_assoc($result)['media_saldo'] ?? 0;
}
?>