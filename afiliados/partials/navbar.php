 <!-- Navbar -->
 <nav class="navbar navbar-fixed navbar-main navbar-expand-lg  px-0 mx-4 shadow-none border-radius-xl z-index-sticky " id="navbarBlur" data-scroll="false">
     <div class="container-fluid py-1 px-3">

         <div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none ">
             <a href="javascript:;" class="nav-link p-0">
                 <div class="sidenav-toggler-inner">
                     <i class="sidenav-toggler-line bg-white"></i>
                     <i class="sidenav-toggler-line bg-white"></i>
                     <i class="sidenav-toggler-line bg-white"></i>
                 </div>
             </a>
         </div>
         <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
             <div class="ms-md-auto pe-md-3 d-flex align-items-center text-white">
                 Bem vindo, <?= $datauser['mobile']; ?>
             </div>
             <ul class="navbar-nav  justify-content-end">
                 <li class="nav-item d-flex align-items-center">
                     <a href="/afiliados/logout" class="nav-link text-white font-weight-bold px-0" target="_blank">
                         <i class="fa-solid fa-arrow-right-from-bracket"></i>
                         <span class="d-sm-inline d-none">Sair</span>
                     </a>
                 </li>


             </ul>
         </div>
     </div>
 </nav>