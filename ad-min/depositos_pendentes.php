<?php
include 'partials/html.php';
include_once('./services/database.php');

function getUserById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");

    // Bind the parameter to the query
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch the result as an associative array
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['real_name'];
}

?>

<head>
    <?php $title = "[BlackScript]";
    include 'partials/title-meta.php' ?>

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

        <div class="toast-container position-absolute p-3 top-0 end-0" id="toastPlacement"
            data-original-class="toast-container position-absolute p-3">
            <!-- Os toasts serão inseridos dinamicamente aqui -->
        </div>


        <!-- Page Content-->
        <div class="page-content">
            <div class="container-xxl">

                <div class="row justify-content-center">
                    <div class="col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">

                                    <div class="col" style="display: flex;align-content: center;align-items: center;">
                                        <div class="tag"></div>
                                        <h4 class="card-title">Depósitos Pendentes</h4>
                                    </div><!--end col-->
                                </div> <!--end row-->
                            </div><!--end card-header-->
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-centered">
                                        <thead class="table-light">
                                            <tr>
                                                <th><input type="checkbox" id="select-all"></th>
                                                <!-- Checkbox para selecionar todos -->
                                                <th>Id</th>
                                                <th>Nome do usuário</th>
                                                <th>Transação ID</th>
                                                <th>Valor</th>
                                                <th>Data/Hora</th>
                                                <th>Copia E Cola</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            global $mysqli;
                                            // Consultar os dados da tabela usuarios
                                            $query_usuarios = "SELECT * FROM transacoes WHERE status = 'processamento' ORDER BY id DESC";
                                            $result_usuarios = mysqli_query($mysqli, $query_usuarios);

                                            if ($result_usuarios && mysqli_num_rows($result_usuarios) > 0) {
                                                while ($usuario = mysqli_fetch_assoc($result_usuarios)) {
                                                    $cargo_badge = ($usuario['status'] == 'processamento') ? "<span class='badge bg-danger'>Pendente</span>" : "<span class='badge bg-secondary'>Usuário</span>";
                                            ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="ids[]" value="<?= $usuario['id']; ?>">
                                                            <!-- Checkbox para cada registro -->
                                                        </td>
                                                        <td><?= $usuario['id']; ?></td>
                                                        <td><?php echo getUserById($usuario['usuario']); ?></td>
                                                        <td><?= $usuario['transacao_id']; ?></td>
                                                        <td>R$ <?= number_format($usuario['valor'], 2, ',', '.'); ?></td>
                                                        <td><?= $usuario['data_hora']; ?></td>
                                                        <td>
                                                            <span><?= substr($usuario['code'], 0, 40); ?>...</span>
                                                            <button type="button" class="btn btn-primary btn-clipboard ms-2"
                                                                data-clipboard-text="<?= $usuario['code']; ?>">
                                                                <i class="far fa-copy"></i>
                                                            </button>
                                                        </td>
                                                        <td><?= $cargo_badge; ?></td>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'>Sem dados disponíveis!</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table><!--end /table-->

                                    <div class="mt-3">
                                        <button type="button" id="mark-expired" class="btn btn-warning">Marcar como
                                            Expirado</button> <!-- Botão para submeter os selecionados -->
                                    </div>
                                </div><!--end /tableresponsive-->
                            </div><!--end card-body-->
                            <!--end card-body-->

                        </div><!-- container -->
                        <!--Start Rightbar-->
                        <?php include 'partials/endbar.php' ?>
                        <!--end Rightbar-->
                        <!--Start Footer-->
                        <?php include 'partials/footer.php' ?>
                        <!--end footer-->
                    </div>
                    <!-- end page content -->
                </div>
                <!-- end page-wrapper -->

                <!-- Javascript  -->
                <!-- vendor js -->
                <?php include 'partials/vendorjs.php' ?>

                <script src="assets/js/app.js"></script>
                <script src="assets/libs/clipboard/clipboard.min.js"></script>
                <script src="assets/js/pages/clipboard.init.js"></script>
                <script src="assets/js/pages/toast.init.js"></script>
                <script>
                    document.getElementById('select-all').onclick = function() {
                        var checkboxes = document.querySelectorAll('input[name="ids[]"]');
                        for (var checkbox of checkboxes) {
                            checkbox.checked = this.checked;
                        }
                    }

                    document.getElementById('mark-expired').onclick = function() {
                        var checkboxes = document.querySelectorAll('input[name="ids[]"]:checked');
                        var ids = [];
                        for (var checkbox of checkboxes) {
                            ids.push(checkbox.value);
                        }

                        if (ids.length > 0) {
                            // Fazer a requisição Ajax
                            var xhr = new XMLHttpRequest();
                            xhr.open("POST", "ajax/att_status_depositos.php", true);
                            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4 && xhr.status === 200) {
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.success) {
                                        showToast('success', 'Status atualizado com sucesso!');
                                        setTimeout(function() {
                                            location.reload(); // Atualiza a página após a alteração
                                        }, 2000); // Delay para o toast ser exibido antes de recarregar
                                    } else {
                                        showToast('danger', 'Erro ao atualizar o status.');
                                    }
                                }
                            };

                            // Enviar IDs selecionados
                            xhr.send("ids=" + JSON.stringify(ids));
                        } else {
                            showToast('warning', 'Por favor, selecione ao menos um depósito.');
                        }
                    };

                    // Função para exibir toast com o estilo fornecido
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
                </script>



</body>
<!--end body-->

</html>