<?php
session_start();
include_once('../logs/registrar_logs.php');
include_once('../services/database.php');
include_once('../services/funcao.php');
#------------------------------------------------------------#
#capta dados do form
if (isset($_POST['email']) && isset($_POST['senha']) && isset($_POST['_csrf'])) {
	$email =  PHP_SEGURO($_POST['email']);
	$senha =   PHP_SEGURO($_POST['senha']);
	$CRSF =   PHP_SEGURO($_POST['_csrf']);
	#------------------------------------------------------------#
	if(empty($CRSF)) {
		echo '<div class="alert alert-danger alert-dismissible fade show  border-start border-2 border-danger mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-skull-crossbones align-self-center fs-30 text-danger "></i>
                    <div class="flex-grow-1 ms-2 text-truncate">
                        <h5 class="mb-1 fw-bold mt-0">Erro</h5>
                        <p class="mb-0">Oops! Houve um erro ao obter dados. Atualize sua página.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>';
	}
	if(empty($email)) {
		echo '<div class="alert alert-danger alert-dismissible fade show  border-start border-2 border-danger mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-skull-crossbones align-self-center fs-30 text-danger "></i>
                    <div class="flex-grow-1 ms-2 text-truncate">
                        <h5 class="mb-1 fw-bold mt-0">Erro</h5>
                        <p class="mb-0">Oops! Insira um e-mail no formulário.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>';
	}
	if(filter_var($email, FILTER_VALIDATE_EMAIL) AND !empty($email)){}else{
		echo '<div class="alert alert-danger alert-dismissible fade show  border-start border-2 border-danger mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-skull-crossbones align-self-center fs-30 text-danger "></i>
                    <div class="flex-grow-1 ms-2 text-truncate">
                        <h5 class="mb-1 fw-bold mt-0">Erro</h5>
                        <p class="mb-0">Oops! Insira um e-mail válido no formulário.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>';
    }
	if(empty($senha)) {
		echo '<div class="alert alert-danger alert-dismissible fade show  border-start border-2 border-danger mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-skull-crossbones align-self-center fs-30 text-danger "></i>
                    <div class="flex-grow-1 ms-2 text-truncate">
                        <h5 class="mb-1 fw-bold mt-0">Erro</h5>
                        <p class="mb-0">Oops! Insira sua senha no formulário.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>';
	}	
	#------------------------------------------------------------#	
	$query = "SELECT * FROM admin_users WHERE email = '$email' AND status=1";
	$result = mysqli_query($mysqli , $query) or die (mysqli_error($mysqli));
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_array($result)) {
			$user_idx = $row['id'];
			$emailbd = $row['email'];
			$pass = $row['senha'];
			if (password_verify($senha, $pass)) {
				$data = date('Y-m-d H:i:s');
				$datadsd = date('Y-m-d');
				$_token_easy = md5(sha1(mt_rand()).$data.$emailbd.$user_idx);
				$_token = CRIPT_AES('encrypt', $_token_easy);
				// Proteção XSS, pois podemos imprimir esse valor
				$users_id = preg_replace("/[^0-9]+/", "", $user_idx);
				// ==========  CRIANDO ROTA DE SISTEMA =====================================#
				$_SESSION['token_adm_encrypted'] = CRIPT_AES('encrypt', $users_id);
				$_SESSION['crsf_token_adm'] = $_token;
				$_SESSION['anti_crsf_token_adm'] = $CRSF;
				if(isset($_SESSION['token_adm_encrypted']) && isset($_SESSION['crsf_token_adm']) && isset($_SESSION['anti_crsf_token_adm'])){
					// Registrar log de login
                    registrarLog($mysqli, $email, "<span class='status-badge green'style='display: inline-block;'><i class='fa fa-sign-out'></i></span></i> Logou no painel admin");
					echo "<div class='alert alert-success alert-dismissible fade show  border-start border-2 border-success mb-0' role='alert'>
                            <div class='d-flex align-items-center gap-2'>
                                <i class='fas fa-check-circle align-self-center fs-30 text-success'></i>
                                <div class='flex-grow-1 ms-2 text-truncate'>
                                    <h5 class='mb-1 fw-bold mt-0'>Sucesso</h5>
                                    <p class='mb-0'>Acessando Conta, aguarde....</p>
                                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                </div>
                            </div>
                          </div><script> setTimeout('window.location.href=\"".$painel_adm."\";', 3000); </script>";
					
				}
			}else{
				echo '<div class="alert alert-danger alert-dismissible fade show  border-start border-2 border-danger mb-0" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-skull-crossbones align-self-center fs-30 text-danger"></i>
                            <div class="flex-grow-1 ms-2 text-truncate">
                                <h5 class="mb-1 fw-bold mt-0">Erro</h5>
                                <p class="mb-0">Revise os dados inseridos.</p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>';
			}
		}
	}else{
		echo '<div class="alert alert-danger alert-dismissible fade show  border-start border-2 border-danger mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-skull-crossbones align-self-center fs-30 text-danger"></i>
                    <div class="flex-grow-1 ms-2 text-truncate">
                        <h5 class="mb-1 fw-bold mt-0">Erro</h5>
                        <p class="mb-0">Seus dados não foram encontrados</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>';
	}
}
