<?php include 'partials/html.php' ?>

<head>
    <?php $title = "[BlackScript]";
    include 'partials/title-meta.php' ?>

    <link href="assets/libs/tobii/css/tobii.min.css" rel="stylesheet" type="text/css" />
    <?php include 'partials/head-css.php' ?>
</head>

<body>
    <!-- Top Bar Start -->
    <?php include 'partials/topbar.php' ?>
    <!-- Top Bar End -->
    <!-- leftbar-tab-menu -->
    <?php include 'partials/startbar.php' ?>
    <!-- end leftbar-tab-menu-->
    <!-- decodificar slug usuario -->
    <?php
    // Definir o badge com base no status do usuário
    function getStatusBadge($status)
    {
        switch ($status) {
            case 'pago':
                return "<span class='badge bg-success'>Pago</span>";
            case 'processamento':
                return "<span class='badge bg-warning'>Pendente</span>";
            case 'expirado':
                return "<span class='badge bg-danger'>Expirado</span>";
            default:
                return "<span class='badge bg-secondary'>Indefinido</span>";
        }
    }
    if (isset($_REQUEST['slug'])) {
        $id_user = decodeAll($_REQUEST['slug']);
        $qry = "SELECT * FROM usuarios WHERE id='" . intval($id_user) . "'";
        $res = mysqli_query($mysqli, $qry);
        $data = mysqli_fetch_assoc($res);
        $saldo_user = saldo_user($data['id']);
    }

    // Atualizar os dados do usuário
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
        $real_name = $_POST['real_name'];
        $token = $_POST['token'];
        $password = $_POST['password'];
        $statusaff = $_POST['statusaff'];
        $tipo_pagamento = $_POST['tipo_pagamento'];
        $invite = $_POST['invite'];
        $senha_saque = $_POST['senhaparasacar'];

        $pass =  password_hash($password, PASSWORD_DEFAULT, array("cost" => 10));

        // Atualizar no banco de dados
        $update_query = "UPDATE usuarios SET mobile='$real_name', token='$token', password='$pass', statusaff='$statusaff', invite_code='$invite', senhaparasacar='$senha_saque', tipo_pagamento = '$tipo_pagamento' WHERE id='" . intval($id_user) . "'";
        $update_res = mysqli_query($mysqli, $update_query);

        if ($update_res) {
            echo "<script>window.addEventListener('DOMContentLoaded', function() { showToast('success', 'Dados do usuário atualizados com sucesso!'); });</script>";
        } else {
            echo "<script>window.addEventListener('DOMContentLoaded', function() { showToast('danger', 'Erro ao atualizar os dados do usuário.'); });</script>";
        }
    }

    if ($data['statusaff'] == 2 || $data['statusaff'] == 1) {
        $view_status = '<span class="label label-success">Afiliado</span>';
    } else {
        $view_status = '<span class="label label-warning">Usuário</span>';
    }
    ?>


    <div class="page-wrapper">

        <!-- Page Content-->
        <div class="page-content">
            <div class="container-xxl">

                <div class="row justify-content-center">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4 align-self-center mb-3 mb-lg-0">
                                        <div class="d-flex align-items-center flex-row flex-wrap">
                                            <div class="position-relative me-3">
                                                <img src="/uploads/<?= $dataconfig['avatar'] ?>" alt="" height="120"
                                                    class="rounded-circle">
                                            </div>
                                            <div class="">
                                                <div
                                                    class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0">
                                                    <h5 class="fw-semibold fs-22 mb-1"><?= $data['real_name']; ?></h5>
                                                </div>
                                                <br>
                                                <!-- <h6 class="fw-semibold fs-15 mb-1">Nível:</h6> -->
                                                <div
                                                    class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0">
                                                    <h5 class="fw-semibold fs-20 mb-1">
                                                        <span class="badge bg-success"> <?= $view_status; ?></span>
                                                    </h5>
                                                </div>

                                            </div>
                                        </div>
                                    </div><!--end col-->

                                    <div class="col-lg-5 ms-auto align-self-center">
                                        <div class="d-flex justify-content-center">
                                            <div
                                                class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0">
                                                <h5 class="fw-semibold fs-22 mb-1">
                                                    R$<?= Reais2(total_dep_pagos_id($data['id'])); ?>
                                                </h5>
                                                <p class="text-muted mb-0 fw-medium">Dep. total</p>
                                            </div>
                                            <div
                                                class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0">
                                                <h5 class="fw-semibold fs-22 mb-1">
                                                    R$<?= Reais2(total_saques_id($data['id'])); ?>
                                                </h5>
                                                <p class="text-muted mb-0 fw-medium">Saques total</p>
                                            </div>
                                            <div
                                                class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0">
                                                <h5 class="fw-semibold fs-22 mb-1">
                                                    R$<?= Reais2($saldo_user['saldo']); ?>
                                                </h5>
                                                <p class="text-muted mb-0 fw-medium">Saldo Atual</p>
                                            </div>
                                        </div>
                                    </div><!--end col-->

                                    <div class="col-lg-3 col-md-6 col-sm-12 align-self-center mt-3 mt-lg-0">
                                        <div
                                            class="d-grid gap-2 d-md-flex justify-content-md-end justify-content-sm-center">
                                            <?php if ($data['banido'] == 1): ?>
                                                <!-- Botão de Desbanir -->
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#desbanirUsuarioModal">
                                                    Desbanir Usuário
                                                </button>
                                            <?php else: ?>
                                                <!-- Botão de Banir -->
                                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#banirUsuarioModal">
                                                    Banir Usuário
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editarSaldoModal">Editar Saldo</button>
                                        </div>
                                    </div>


                                    <!-- Modal de Confirmação -->
                                    <div class="modal fade" id="banirUsuarioModal" tabindex="-1"
                                        aria-labelledby="banirUsuarioLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="banirUsuarioLabel">Confirmar Banimento
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Você tem certeza que deseja banir este usuário?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="button" class="btn btn-danger"
                                                        id="confirmarBanimento">Sim, tenho certeza</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal de Confirmação de Desbanir -->
                                    <div class="modal fade" id="desbanirUsuarioModal" tabindex="-1"
                                        aria-labelledby="desbanirUsuarioLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="desbanirUsuarioLabel">Confirmar
                                                        Desbanimento</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Você tem certeza que deseja desbanir este usuário?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="button" class="btn btn-success"
                                                        id="confirmarDesbanimento">Sim, tenho certeza</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal de Editar Saldo -->
                                    <div class="modal fade" id="editarSaldoModal" tabindex="-1"
                                        aria-labelledby="editarSaldoLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editarSaldoLabel">Editar Saldo</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="saldoAtual" class="form-label">Saldo Atual</label>
                                                        <input type="text" class="form-control" id="saldoAtual"
                                                            value="R$<?= Reais2($saldo_user['saldo']); ?>" disabled>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="adicionarSaldo" class="form-label">Adicionar
                                                            Saldo</label>
                                                        <input type="number" class="form-control" id="adicionarSaldo"
                                                            placeholder="Insira o valor para adicionar" step="0.01">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="removerSaldo" class="form-label">Remover
                                                            Saldo</label>
                                                        <input type="number" class="form-control" id="removerSaldo"
                                                            placeholder="Insira o valor para remover" step="0.01">
                                                    </div>

                                                    <div class="mt-3">
                                                        <h6>Saldo Final Estimado: <span
                                                                id="saldoFinal">R$<?= Reais2($saldo_user['saldo']); ?></span>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="button" class="btn btn-primary"
                                                        id="confirmarEdicaoSaldo">Salvar Alterações</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                </div><!--end row-->
                            </div><!--end card-body-->
                        </div><!--end card-->
                    </div> <!--end col-->
                </div><!--end row-->

                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link fw-medium active" data-bs-toggle="tab" href="#afiliado" role="tab"
                                    aria-selected="true">Informações de afiliado</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-medium" data-bs-toggle="tab" href="#depositos" role="tab"
                                    aria-selected="false">Registros de depósitos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-medium" data-bs-toggle="tab" href="#saques" role="tab"
                                    aria-selected="false">Registros de retiradas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-medium" data-bs-toggle="tab" href="#partidas" role="tab"
                                    aria-selected="false">Registros de partidas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-medium" data-bs-toggle="tab" href="#editar" role="tab"
                                    aria-selected="false">Editar usuario</a>
                            </li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content">
                            <!-- Aba de edição do usuário -->
                            <div class="tab-pane p-3" id="editar" role="tabpanel">
                                <form method="POST" action="">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="real_name" class="form-label">Nome de usuario</label>
                                            <input type="text" class="form-control" name="real_name" id="real_name"
                                                value="<?= $data['mobile']; ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="token" class="form-label">Token</label>
                                            <input type="text" class="form-control" name="token" id="token"
                                                value="<?= $data['token']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">Senha</label>
                                            <input type="password" class="form-control" name="password" id="password"
                                                value="<?= $data['password']; ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="invite" class="form-label">Código De Convite</label>
                                            <input type="invite" class="form-control" name="invite" id="invite"
                                                value="<?= $data['invite_code']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="senhaparasacar" class="form-label">Senha De Saque</label>
                                            <input type="senhaparasacar" class="form-control" name="senhaparasacar"
                                                id="senhaparasacar" placeholder="Senha Para Sacar"
                                                value="<?= $data['senhaparasacar']; ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="statusaff" class="form-label">Status de Afiliado</label>
                                            <select class="form-select" name="statusaff" id="statusaff">
                                                <option value="1" <?= $data['statusaff'] == 1 ? 'selected' : ''; ?>>Ativo
                                                </option>
                                                <option value="0" <?= $data['statusaff'] == 0 ? 'selected' : ''; ?>>Inativo
                                                </option>
                                                <option value="2" <?= $data['statusaff'] == 2 ? 'selected' : ''; ?>>
                                                    Bloqueado</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="tipo_pagamento" class="form-label">Tipo De Pagamento Do Afiliado</label>
                                            <select class="form-select" name="tipo_pagamento" id="tipo_pagamento">
                                                <option value="1" <?= $data['tipo_pagamento'] == 1 ? 'selected' : ''; ?>>Cpa
                                                </option>
                                                <option value="0" <?= $data['tipo_pagamento'] == 0 ? 'selected' : ''; ?>>Cpa + Rev
                                                </option>
                                                <option value="2" <?= $data['tipo_pagamento'] == 2 ? 'selected' : ''; ?>>
                                                    Rev</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <button type="submit" name="update_user" class="btn btn-primary">Atualizar
                                                Usuário</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div><!-- end tab-content -->

                        <div class="tab-content">
                            <div class="tab-pane active" id="afiliado" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row d-flex justify-content-center">
                                                    <div class="col">
                                                        <p class="text-dark mb-1 fw-semibold">Referências diretas</p>
                                                        <h3 class="my-2 fs-24 fw-bold">
                                                            <?= count_refer_direto($data['invite_code']); ?>
                                                        </h3>
                                                    </div>
                                                    <div class="col-auto align-self-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center thumb-xl bg-light rounded-circle mx-auto">
                                                            <i
                                                                class="iconoir-eye fs-30 align-self-center text-muted"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--end card-body-->
                                        </div><!--end card-->
                                    </div><!--end col-->
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row d-flex justify-content-center">
                                                    <div class="col">
                                                        <p class="text-dark mb-1 fw-semibold">Total ganho: Cpa</p>
                                                        <h3 class="my-2 fs-24 fw-bold">
                                                            R$<?= Reais2(total_CPA_id($data['id'])); ?></h3>
                                                    </div>
                                                    <div class="col-auto align-self-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center thumb-xl bg-light rounded-circle mx-auto">
                                                            <i
                                                                class="iconoir-receive-dollars fs-30 align-self-center text-muted"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--end card-body-->
                                        </div><!--end card-->
                                    </div><!--end col-->
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row d-flex justify-content-center">
                                                    <div class="col">
                                                        <p class="text-dark mb-1 fw-semibold">Total ganho: Rev</p>
                                                        <h3 class="my-2 fs-24 fw-bold">
                                                            R$<?= Reais2(total_REV_id($data['id'])); ?></h3>
                                                    </div>
                                                    <div class="col-auto align-self-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center thumb-xl bg-light rounded-circle mx-auto">
                                                            <i
                                                                class="iconoir-receive-dollars fs-30 align-self-center text-muted"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--end card-body-->
                                        </div><!--end card-->
                                    </div><!--end col-->
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row d-flex justify-content-center">
                                                    <div class="col">
                                                        <p class="text-dark mb-1 fw-semibold">Depósitos dos indicados
                                                        </p>
                                                        <h3 class="my-2 fs-24 fw-bold">
                                                            R$<?= Reais2(total_dep_afiliado($data['invite_code'])); ?>
                                                        </h3>
                                                    </div>
                                                    <div class="col-auto align-self-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center thumb-xl bg-light rounded-circle mx-auto">
                                                            <i
                                                                class="iconoir-send-dollars fs-30 align-self-center text-muted"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--end card-body-->
                                        </div><!--end card-->
                                    </div><!--end col-->
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row d-flex justify-content-center">
                                                    <div class="col">
                                                        <p class="text-dark mb-1 fw-semibold">Numero De Depositantes</p>
                                                        <h3 class="my-2 fs-24 fw-bold">
                                                            <?= numero_total_dep($data['invite_code']); ?>
                                                        </h3>
                                                    </div>
                                                    <div class="col-auto align-self-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center thumb-xl bg-light rounded-circle mx-auto">
                                                            <i
                                                                class="iconoir-piggy-bank fs-30 align-self-center text-muted"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--end card-body-->
                                        </div><!--end card-->
                                    </div><!--end col-->
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row d-flex justify-content-center">
                                                    <div class="col">
                                                        <p class="text-dark mb-1 fw-semibold">Saldo de afiliado</p>
                                                        <h3 class="my-2 fs-24 fw-bold">
                                                            R$<?= Reais2($saldo_user['saldo_afiliado']); ?></h3>
                                                    </div>
                                                    <div class="col-auto align-self-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center thumb-xl bg-light rounded-circle mx-auto">
                                                            <i
                                                                class="iconoir-dollar-circle fs-30 align-self-center text-muted"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--end card-body-->
                                        </div><!--end card-->
                                    </div><!--end col-->
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row d-flex justify-content-center">
                                                    <div class="col">
                                                        <p class="text-dark mb-1 fw-semibold">Afiliação:</p>
                                                        <h3 class="my-2 fs-24 fw-bold">
                                                            <?= afiliado_de_quem($data['invitation_code']); ?>
                                                        </h3>
                                                    </div>
                                                    <div class="col-auto align-self-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center thumb-xl bg-light rounded-circle mx-auto">
                                                            <i
                                                                class="iconoir-group fs-30 align-self-center text-muted"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--end card-body-->
                                        </div><!--end card-->
                                    </div><!--end col-->
                                </div><!--end row-->
                            </div>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <!-- Registros de Depósitos -->
                                <div class="tab-pane p-3" id="depositos" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table  mb-0 table-centered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Id</th>
                                                    <th>Transação ID</th>
                                                    <th>Valor</th>
                                                    <th>Data/Hora</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query_depositos = "SELECT * FROM transacoes WHERE usuario = '" . intval($id_user) . "' ORDER BY id DESC";
                                                $result_depositos = mysqli_query($mysqli, $query_depositos);

                                                if ($result_depositos && mysqli_num_rows($result_depositos) > 0) {
                                                    while ($deposito = mysqli_fetch_assoc($result_depositos)) {
                                                        $cargo_badge = ($usuario['status'] == 'pago') ? "<span class='badge bg-success'>Pago</span>" : "<span class='badge bg-warning'>Pendente</span> ";
                                                ?>
                                                        <tr>
                                                            <td><?= $deposito['id']; ?></td>
                                                            <td><?= $deposito['transacao_id']; ?></td>
                                                            <td>R$ <?= number_format($deposito['valor'], 2, ',', '.'); ?></td>
                                                            <td><?= $deposito['data_hora']; ?></td>
                                                            <td><?= getStatusBadge($deposito['status']); ?></td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5' class='text-center'>Sem depósitos disponíveis!</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table><!--end /table-->
                                    </div><!--end /tableresponsive-->
                                </div>

                                <!-- Registros de Saques -->
                                <div class="tab-pane p-3" id="saques" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table  mb-0 table-centered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Id</th>
                                                    <th>Valor</th>
                                                    <th>Data/Hora</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query_saques = "SELECT * FROM solicitacao_saques WHERE id_user = '" . intval($id_user) . "' ORDER BY id DESC";
                                                $result_saques = mysqli_query($mysqli, $query_saques);

                                                if ($result_saques && mysqli_num_rows($result_saques) > 0) {
                                                    while ($saque = mysqli_fetch_assoc($result_saques)) {
                                                        $status_saque = $saque['status'] == 1 ? "Aprovado" : "Em Análise";
                                                ?>
                                                        <tr>
                                                            <td><?= $saque['id']; ?></td>
                                                            <td>R$ <?= number_format($saque['valor'], 2, ',', '.'); ?></td>
                                                            <td><?= $saque['data_hora']; ?></td>
                                                            <td><span
                                                                    class="badge bg-<?= $saque['status'] == 1 ? 'success' : 'warning' ?>"><?= $status_saque; ?></span>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='4' class='text-center'>Sem registros de saques disponíveis!</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table><!--end /table-->
                                    </div><!--end /tableresponsive-->
                                </div>

                                <div class="tab-pane p-3" id="partidas" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table  mb-0 table-centered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Id</th>
                                                    <th>Game</th>
                                                    <th>Aposta</th>
                                                    <th>Ganho</th>
                                                    <th>Data/Hora</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query_historico_play = "SELECT * FROM historico_play WHERE id_user = '" . intval($id_user) . "' ORDER BY id DESC";
                                                $result_historico_play = mysqli_query($mysqli, $query_historico_play);

                                                if ($result_historico_play && mysqli_num_rows($result_historico_play) > 0) {
                                                    while ($historico_play = mysqli_fetch_assoc($result_historico_play)) {
                                                        $status_historico_play = $historico_play['status'] == 1 ? "Aprovado" : "Em Análise";
                                                ?>
                                                        <tr>
                                                            <td><?= $historico_play['id']; ?></td>
                                                            <td><?= $historico_play['nome_game']; ?></td>
                                                            <td>R$ <?= number_format($historico_play['bet_money'], 2, ',', '.'); ?></td>
                                                            <td>R$ <?= number_format($historico_play['win_money'], 2, ',', '.'); ?></td>
                                                            <td><?= $historico_play['created_at']; ?></td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='4' class='text-center'>Sem registros de partidas disponíveis!</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table><!--end /table-->
                                    </div><!--end /tableresponsive-->
                                </div>
                            </div>
                        </div> <!--end col-->
                    </div><!--end row-->

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

        <!-- Toast Container -->
        <div id="toastPlacement" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

        <!-- Javascript  -->
        <?php include 'partials/vendorjs.php' ?>
        <script src="assets/js/app.js"></script>


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

                // Ocultar o toast após 3 segundos
                setTimeout(function() {
                    bootstrapToast.hide();
                    setTimeout(() => toast.remove(), 500); // Remove o toast após ele sumir
                }, 3000);

                // Recarregar a página após o toast, sem reenvio do formulário
                setTimeout(function() {
                    window.location.href = window.location.href.split("?")[0] + "?mcbsoftbet=" + encodeURIComponent(new URLSearchParams(window.location.search).get('mcbsoftbet'));
                }, 3000); // 3 segundos
            }
        </script>

        <script>
            document.getElementById('confirmarBanimento').addEventListener('click', function() {
                // Chama a função de banir o usuário
                modificarUsuario(<?= $id_user; ?>, 'banir');
            });

            document.getElementById('confirmarDesbanimento').addEventListener('click', function() {
                // Chama a função de desbanir o usuário
                modificarUsuario(<?= $id_user; ?>, 'desbanir');
            });

            function modificarUsuario(userId, action) {
                fetch('fetch/banir_usuario.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            action: action
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('success', data.message);
                            setTimeout(() => {
                                location.reload(); // Recarregar a página após o toast
                            }, 3000);
                        } else {
                            showToast('danger', 'Erro: ' + data.message);
                        }
                    })
                    .catch(error => {
                        showToast('danger', 'Erro ao processar a solicitação.');
                    });
            }
        </script>

        <script>
            document.getElementById('adicionarSaldo').addEventListener('input', atualizarSaldoFinal);
            document.getElementById('removerSaldo').addEventListener('input', atualizarSaldoFinal);

            function atualizarSaldoFinal() {
                var saldoAtual = parseFloat(<?= $saldo_user['saldo']; ?>); // Saldo atual do usuário do PHP
                var adicionarSaldo = parseFloat(document.getElementById('adicionarSaldo').value) || 0;
                var removerSaldo = parseFloat(document.getElementById('removerSaldo').value) || 0;

                var saldoFinal = saldoAtual + adicionarSaldo - removerSaldo;

                // Atualizar o texto de saldo final no modal
                document.getElementById('saldoFinal').textContent = 'R$' + saldoFinal.toFixed(2).replace('.', ',');
            }

            document.getElementById('confirmarEdicaoSaldo').addEventListener('click', function() {
                var adicionarSaldo = parseFloat(document.getElementById('adicionarSaldo').value) || 0;
                var removerSaldo = parseFloat(document.getElementById('removerSaldo').value) || 0;

                // Chama a função para salvar a edição do saldo
                editarSaldoUsuario(<?= $id_user; ?>, adicionarSaldo, removerSaldo);
            });

            function editarSaldoUsuario(userId, adicionar, remover) {
                fetch('fetch/editar_saldo.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            adicionar: adicionar,
                            remover: remover
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('success', 'Saldo atualizado com sucesso!');
                            setTimeout(() => {
                                location.reload(); // Recarregar a página após o sucesso
                            }, 3000);
                        } else {
                            showToast('danger', 'Erro: ' + data.message);
                        }
                    })
                    .catch(error => {
                        showToast('danger', 'Erro ao atualizar o saldo.');
                    });
            }
        </script>


</body>

</html>