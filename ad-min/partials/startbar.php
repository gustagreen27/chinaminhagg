<div class="startbar d-print-none">
    <!--start brand-->
    <div class="brand">
        <a href="index.php" class="logo">
            <span>
                <img src="../uploads/<?= $dataconfig['logo'] ?>?v=<?= rand(1000,9999) ?>" alt="logo-small" class="logo-sm">
            </span>
            <span class="">
                <!-- <img src="assets/images/logo-light.png" alt="logo-large" class="logo-lg logo-light">
                <h4 class="mb-0 fw-bold text-truncate logo-lg logo-dark">TESTE</h4> -->
            </span>
        </a>
    </div>
    <!--end brand-->
    <!--start startbar-menu-->
    <div class="startbar-menu">
        <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
            <div class="d-flex align-items-start flex-column w-100">
                <!-- Navigation -->
                <ul class="navbar-nav mb-auto w-100">
                    <li class="menu-label pt-0 mt-0">
                        <!-- <small class="label-border">
                            <div class="border_left hidden-xs"></div>
                            <div class="border_right"></div>
                        </small> -->
                        <span>RELATÓRIOS</span>
                    </li>
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarDashboards">
                            <i class="iconoir-home-simple menu-icon"></i>
                            <span>Dashboard</span>
                        </a>
                        <div class="collapse " id="sidebarDashboards">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="index.php">Relatórios </a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarDashboards-->
                    </li><!--end nav-item-->
                    <div class="border-dashed-bottom pb-2"></div>
                    <li class="menu-label mt-2">
                        <small class="label-border">
                            <div class="border_left hidden-xs"></div>
                            <div class="border_right"></div>
                        </small>
                        <span>FINANCEIRO</span>
                    </li>
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#sidebarElements" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarElements">
                            <i class="iconoir-receive-dollars menu-icon"></i>
                            <span>Depósitos</span>
                        </a>
                        <div class="collapse " id="sidebarElements">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="depositos_pagos">Depósitos Aprovados</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="depositos_pendentes">Depósitos Pendentes</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="depositos_expirados">Depósitos Expirados</a>
                                </li><!--end nav-item-->

                            </ul><!--end nav-->
                        </div><!--end startbarElements-->
                    </li><!--end nav-item-->
                    <?php
                    // Consultar a quantidade de saques pendentes
                    $query_saques_pendentes = "SELECT COUNT(*) as total_pendentes FROM solicitacao_saques WHERE status = '0'";
                    $result_saques_pendentes = mysqli_query($mysqli, $query_saques_pendentes);
                    $row_saques_pendentes = mysqli_fetch_assoc($result_saques_pendentes);
                    $total_saques_pendentes = $row_saques_pendentes['total_pendentes'];
                    ?>

                    <?php
                    // Consultar a quantidade de saques pendentes
                    $query_saques_afiliados_pendentes = "SELECT COUNT(*) as total_pendentes FROM solicitacao_saques WHERE status = '0' AND tipo_saque = '1'";
                    $result_saques_afiliados_pendentes = mysqli_query($mysqli, $query_saques_pendentes);
                    $row_saques_afiliados_pendentes = mysqli_fetch_assoc($result_saques_pendentes);
                    $total_saques_afiliados_pendentes = $row_saques_pendentes['total_pendentes'];
                    ?>

                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#sidebarAdvancedUI" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarAdvancedUI">
                            <i class="iconoir-send-dollars menu-icon"></i>
                            <span>Saques</span>
                            <span
                                class="badge rounded text-warning bg-warning-subtle ms-1"><?= $total_saques_pendentes; ?></span>
                            <!-- Exibir quantidade de saques pendentes -->
                        </a>
                        <div class="collapse" id="sidebarAdvancedUI">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="saques_aprovados">Saques Aprovados</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="saques_pendentes">Saques Pendentes
                                        <span
                                            class="badge rounded text-warning bg-warning-subtle ms-1"><?= $total_saques_pendentes; ?></span>
                                        <!-- Exibir quantidade de saques pendentes -->
                                    </a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="saques_recusados">Saques Recusados</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarAdvancedUI-->
                    </li><!--end nav-item-->
                    <div class="border-dashed-bottom pb-2"></div>
                    <li class="menu-label mt-2">
                        <small class="label-border">
                            <div class="border_left hidden-xs"></div>
                            <div class="border_right"></div>
                        </small>
                        <span>USUARIOS</span>
                    </li>
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#sidebarForms" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarForms">
                            <i class="iconoir-community menu-icon"></i>
                            <span>Usuários</span>
                        </a>
                        <div class="collapse " id="sidebarForms">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="usuarios">Todos Usuários</a>
                                </li><!--end nav-item-->

                            </ul><!--end nav-->
                        </div><!--end startbarForms-->
                    </li><!--end nav-item-->
                    <div class="border-dashed-bottom pb-2"></div>
                    <li class="menu-label mt-2">
                        <small class="label-border">
                            <div class="border_left hidden-xs"></div>
                            <div class="border_right"></div>
                        </small>
                        <span>AFILIADOS</span>
                    </li>
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#sidebarCharts" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarCharts">
                            <i class="iconoir-settings menu-icon"></i>
                            <span>Configurações</span>
                        </a>
                        <div class="collapse " id="sidebarCharts">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="gerenciamento-afiliados.php">Gerenciar Valores</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarCharts-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#sidebarTables" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarTables">
                            <i class="iconoir-user-star menu-icon"></i>
                            <span>Afiliados</span>
                        </a>
                        <div class="collapse " id="sidebarTables">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="afiliados">Todos Afiliados</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarTables-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#sidebarIcons" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarIcons">
                            <i class="iconoir-send-dollars menu-icon"></i>
                            <span>Saques</span><span class="badge rounded text-warning bg-warning-subtle ms-1"><?= $total_saques_afiliados_pendentes; ?></span>
                        </a>
                        <div class="collapse " id="sidebarIcons">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="saques_afiliados_aprovados">Saques Aprovados</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="saques_afiliados_pendentes">Saques Pendentes
                                        <span
                                            class="badge rounded text-warning bg-warning-subtle ms-1"><?= $total_saques_afiliados_pendentes; ?></span>
                                        <!-- Exibir quantidade de saques pendentes -->
                                    </a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="saques_afiliados_recusados">Saques Recusados</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarIcons-->
                    </li><!--end nav-item-->
                    <div class="border-dashed-bottom pb-2"></div>
                    <li class="menu-label mt-2">
                        <small class="label-border">
                            <div class="border_left hidden-xs"></div>
                            <div class="border_right"></div>
                        </small>
                        <span>CONFIGURAÇÕES GERAIS</span>
                    </li>
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#sidebarMaps" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarMaps">
                            <i class="iconoir-html5 menu-icon"></i>
                            <span>Plataforma</span>
                        </a>
                        <div class="collapse " id="sidebarMaps">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="ge-gateway-default.php">Definir Gateway Padrão</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="marquee.php">Definir Mensagem Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="configuracoes">Gerenciar Valores</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="gerenciamento-nomes">Gerenciar Nomes</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="pixel">Gerenciar Pixels</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="identidade-visual">Gerenciar Imagens</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="gerenciar-tarefas">Gerenciar Tarefas</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarMaps-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#baus" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="baus">
                            <i class="iconoir-html5 menu-icon"></i>
                            <span>Baús</span>
                        </a>
                        <div class="collapse " id="baus">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="baus">Gerenciar Baús</a>
                                </li><!--end nav-item-->

                            </ul><!--end nav-->
                        </div><!--end startbarMaps-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#banners" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="banners">
                            <i class="iconoir-media-image-folder menu-icon"></i>
                            <span>Banners</span>
                        </a>
                        <div class="collapse " id="banners">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="banners">Gerenciar Imagens</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarEmailTemplates-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#popups" data-bs-toggle="collapse" role="button" aria-expanded="false"
                            aria-controls="popups">
                            <i class="iconoir-message-alert menu-icon"></i>
                            <span>Popups</span>
                        </a>
                        <div class="collapse " id="popups">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="popups">Gerenciar Popups</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="baixarpop">Gerenciar App</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarEmailTemplates-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#mensagens" data-bs-toggle="collapse" role="button" aria-expanded="false"
                            aria-controls="mensagens">
                            <i class="iconoir-message-alert menu-icon"></i>
                            <span>Mensagens</span>
                        </a>
                        <div class="collapse " id="mensagens">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="mensagens">Gerenciar Anuncios</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarEmailTemplates-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#modals" data-bs-toggle="collapse" role="button" aria-expanded="false"
                            aria-controls="modals">
                            <i class="iconoir-message-alert menu-icon"></i>
                            <span>Modals</span>
                        </a>
                        <div class="collapse " id="modals">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="gerenciar-modal">Gerenciar Modal</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarEmailTemplates-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#gateway" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="gateway">
                            <i class="iconoir-fingerprint-lock-circle menu-icon"></i>
                            <span>Gateway</span><span class="badge rounded text-success bg-success-subtle ms-1">5</span>
                        </a>
                        <div class="collapse " id="gateway">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="digitopay">Chaves DigitoPay</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="suitpay">Chaves SuitPay</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="pixup">Chaves BsPay / PixUp</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="royalbenk">Chaves RoyalBenk</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="paguepix">Chaves PaguePix</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="gateway-config">Credenciais Gateway</a>
                                </li>                                
                                <!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarEmailTemplates-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#temas" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="temas">
                            <i class="iconoir-design-pencil menu-icon"></i>
                            <span>Personalização</span><span class="badge rounded text-success bg-success-subtle ms-1">35</span>
                        </a>
                        <div class="collapse " id="temas">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="temas">Temas E Cores
                                        <span class="badge rounded text-success bg-success-subtle ms-1">23</span>
                                    </a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="iconesfloat">Icones Float
                                        <span class="badge rounded text-success bg-success-subtle ms-1">3</span>
                                    </a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="jackpot">Jackpot
                                        <span class="badge rounded text-success bg-success-subtle ms-1">4</span>
                                    </a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="numeros-jackpot">Números Jackpot
                                        <span class="badge rounded text-success bg-success-subtle ms-1">5</span>
                                    </a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarEmailTemplates-->
                    </li><!--end nav-item-->
                    <div class="border-dashed-bottom pb-2"></div>
                    <li class="menu-label mt-2">
                        <small class="label-border">
                            <div class="border_left hidden-xs"></div>
                            <div class="border_right"></div>
                        </small>
                        <span>API</span>
                    </li>

                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#jogosapi" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="jogosapi">
                            <i class="iconoir-spades menu-icon"></i>
                            <span>Gerenciar Jogos</span>
                        </a>
                        <div class="collapse " id="jogosapi">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="jogos">Gerenciar E Listar Jogos</a>
                                </li><!--end nav-item-->

                            </ul><!--end nav-->
                        </div><!--end startbarPages-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#provedoresapi" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="provedoresapi">
                            <i class="iconoir-server menu-icon"></i>
                            <span>Gerenciar Provedores</span>
                        </a>
                        <div class="collapse " id="provedoresapi">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="provedores">Listar E Gerenciar Provedores</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarAuthentication-->
                    </li><!--end nav-item-->
                    <li class="nav-item" style="background-color: rgba(255, 255, 255, 0.04);border-radius: 8px;margin:2px;">
                        <a class="nav-link" href="#chavesapi" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="chavesapi">
                            <i class="iconoir-key-plus menu-icon"></i>
                            <span>Gerenciar Chaves</span><span class="badge rounded text-success bg-success-subtle ms-1">5</span>
                        </a>
                        <div class="collapse " id="chavesapi">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="api-pg12">Api PG 12 Jogos</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="api-pg16">Api PG 16 Jogos</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="api-pp38">Api PP 38 Jogos</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="api-playfiver">Api Playfiver</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="api-fiverscan">Api Fiverscan</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div><!--end startbarAuthentication-->
                    </li><!--end nav-item-->
                    <div class="border-dashed-bottom pb-2"></div>
                </ul><!--end navbar-nav--->
                <div class="update-msg text-center">
                    <div
                        class="d-flex justify-content-center align-items-center thumb-lg update-icon-box  rounded-circle mx-auto">
                        <i class="iconoir-peace-hand h3 align-self-center mb-0 text-primary"></i>
                    </div>
                    <h5 class="mt-3">Precisa de ajuda?</h5>
                    <p class="mb-3 text-muted">Contate o suporte para esclarecer suas duvidas!</p>
                    <a href="https://api.whatsapp.com/send/?phone=5515996536389" class="btn text-primary shadow-sm rounded-pill">Suporte</a>
                </div>
            </div>
        </div><!--end startbar-collapse-->
    </div><!--end startbar-menu-->
</div><!--end startbar-->
<div class="startbar-overlay d-print-none"></div>