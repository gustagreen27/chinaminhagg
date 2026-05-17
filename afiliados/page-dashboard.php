<?php include_once "./services/crud.php"; ?>
<?php include "partials/header.php"; ?>
<?php
include_once "./dashboard/counts.php";
// Debugar Erros No Código / 1 = ON, 0 = OFF
ini_set('display_errors', 0);
error_reporting(E_ALL);
global $datasubs;
?>


<div class="row mt-4">
    <div class="col-12">
        <div class="card h-100 ">
            <div class="card-header">
                <h5 class="mb-0 text-capitalize">Meus subordinados</h5>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-flush" id="datatable-basic">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nome</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipo</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Depósito</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Comissão</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($datasubs as $subordinado) { ?>
                                <tr>
                                    <td class="text-sm font-weight-normal"><?= $subordinado['real_name'] ?></td>
                                    <td class="text-sm font-weight-normal"><?= $subordinado['tipo'] ?></td>
                                    <td class="text-sm font-weight-normal">R$ <?= number_format($subordinado['depositos'], 2, ',', '.') ?></td>
                                    <td class="text-sm font-weight-normal">R$ <?= number_format($subordinado['comissao'], 2, ',', '.') ?></td>
                                    <td class="text-sm font-weight-normal">
                                        <?php switch ($subordinado['status']) {
                                            case 'aguardando':
                                        ?>
                                                <span class="badge text-bg-warning">aguardando</span>
                                            <?php break;
                                            case 'pago':
                                            ?>
                                                <span class="badge text-bg-success">pago</span>
                                            <?php break;
                                            case 'expirado':
                                            ?>
                                                <span class="badge text-bg-secondary">expirado</span>
                                        <?php break;
                                        }
                                        ?>
                                    </td>
                                    <td class="text-sm font-weight-normal"><?= $subordinado['data'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
        <div class="card h-100 ">
            <div class="card-header">
                <h5 class="mb-0 text-capitalize">To do list</h5>
            </div>
            <div class="card-body pt-0">
                <ul class="list-group list-group-flush" data-toggle="checklist">
                    <li class="checklist-entry list-group-item px-0">
                        <div class="checklist-item checklist-item-success checklist-item-checked d-flex">
                            <div class="checklist-info">
                                <h6 class="checklist-title mb-0">Call with Dave</h6>
                                <small class="text-xs">09:30 AM</small>
                            </div>
                            <div class="form-check my-auto ms-auto">
                                <input class="form-check-input" type="checkbox" id="customCheck1" checked>
                            </div>
                        </div>
                    </li>
                    <li class="checklist-entry list-group-item px-0">
                        <div class="checklist-item checklist-item-warning d-flex">
                            <div class="checklist-info">
                                <h6 class="checklist-title mb-0">Brunch Meeting</h6>
                                <small class="text-xs">11:00 AM</small>
                            </div>
                            <div class="form-check my-auto ms-auto">
                                <input class="form-check-input" type="checkbox" id="customCheck1">
                            </div>
                        </div>
                    </li>
                    <li class="checklist-entry list-group-item px-0">
                        <div class="checklist-item checklist-item-info d-flex">
                            <div class="checklist-info">
                                <h6 class="checklist-title mb-0">Argon Dashboard Launch</h6>
                                <small class="text-xs">02:00 PM</small>
                            </div>
                            <div class="form-check my-auto ms-auto">
                                <input class="form-check-input" type="checkbox" id="customCheck1">
                            </div>
                        </div>
                    </li>
                    <li class="checklist-entry list-group-item px-0">
                        <div class="checklist-item checklist-item-danger checklist-item-checked d-flex">
                            <div class="checklist-info">
                                <h6 class="checklist-title mb-0">Winter Hackaton</h6>
                                <small>10:30 AM</small>
                            </div>
                            <div class="form-check my-auto ms-auto">
                                <input class="form-check-input" type="checkbox" id="customCheck2" checked>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100 ">
            <div class="card-header">
                <h5 class="mb-0 text-capitalize">Progress track</h5>
            </div>
            <div class="card-body pt-0">
                <ul class="list-group list-group-flush list">
                    <li class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <a href="javascript:;" class="avatar rounded-circle">
                                    <img alt="Image placeholder" src="./assets/img/small-logos/logo-jira.svg">
                                </a>
                            </div>
                            <div class="col">
                                <h6>React Material Dashboard</h6>
                                <div class="progress progress-xs mb-0">
                                    <div class="progress-bar bg-primary" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100" style="width: 90%;"></div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <a href="javascript:;" class="avatar rounded-circle">
                                    <img alt="Image placeholder" src="./assets/img/small-logos/logo-asana.svg">
                                </a>
                            </div>
                            <div class="col">
                                <h6>Argon Design System</h6>
                                <div class="progress progress-xs mb-0">
                                    <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;"></div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <a href="javascript:;" class="avatar rounded-circle">
                                    <img alt="Image placeholder" src="./assets/img/small-logos/logo-spotify.svg">
                                </a>
                            </div>
                            <div class="col">
                                <h6>VueJs Now UI Kit PRO</h6>
                                <div class="progress progress-xs mb-0">
                                    <div class="progress-bar bg-success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <a href="javascript:;" class="avatar rounded-circle">
                                    <img alt="Image placeholder" src="./assets/img/small-logos/bootstrap.svg">
                                </a>
                            </div>
                            <div class="col">
                                <h6>Soft UI Dashboard</h6>
                                <div class="progress progress-xs mb-0">
                                    <div class="progress-bar bg-gradient-primary" role="progressbar" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100" style="width: 72%;"></div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div> -->
    </div>
    <?php include "partials/footer.php"; ?>