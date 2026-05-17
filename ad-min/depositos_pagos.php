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
                                        <h4 class="card-title">Depósitos Pagos</h4>
                                    </div><!--end col-->
                                </div> <!--end row-->
                            </div><!--end card-header-->
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table  mb-0 table-centered">
                                        <thead class="table-light">
                                            <tr>
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
                                            $query_usuarios = "SELECT * FROM transacoes WHERE status = 'pago' ORDER BY id DESC";
                                            $result_usuarios = mysqli_query($mysqli, $query_usuarios);

                                            if ($result_usuarios && mysqli_num_rows($result_usuarios) > 0) {
                                                while ($usuario = mysqli_fetch_assoc($result_usuarios)) {
                                                    // Definir o cargo com base nos dados da tabela (exemplo para afiliado)
                                                    $cargo_badge = ($usuario['status'] == 'pago') ? "<span class='badge bg-success'>Pago</span>" : "<span class='badge bg-secondary'>Usuário</span>";
                                            ?>
                                                    <tr>
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
                                </div><!--end /tableresponsive-->
                            </div><!--end card-body-->

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
                <script src="assets/js/app.js"></script>
</body>
<!--end body-->

</html>