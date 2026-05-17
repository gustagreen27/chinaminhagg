<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 ps bg-default" id="sidenav-main">
    <div class="sidenav-header text-center">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 text-center" href="/afiliados">
            <img src="/uploads/<?= $dataconfig['logo'] ?>?v=<?= rand(1000,9999) ?>" width="auto" height="48px" class="navbar-brand-img h-100" alt="main_logo">
        </a>
        <span class="ms-1 font-weight-bold">Painel do Afiliado</span>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="/afiliados/dashboard">
                    <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-chart-bar-32 text-dark text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/afiliados/sub-afiliados">
                    <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-dark text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Sub Afiliados</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/afiliados/saques-afiliados">
                    <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-money-coins text-dark text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Saques de Afiliados</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="https://<?= $_SERVER['HTTP_HOST'] . '/?id=' . $datauser['invite_code'] . '&currency=BRL&type=2' ?>" target="_blank">
                    <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni ni-curved-next text-dark text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Link de Convite</span>
                </a>
            </li>
        </ul>
    </div>
</aside>