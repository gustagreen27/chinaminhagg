<!DOCTYPE html>
<html lang="pt-BR data-bs-theme=" dark">
<?php include "./services/configs.php" ?>
<?php
session_start();

if (isset($_SESSION['auth_token'])) {
    header('Location: /afiliados/dashboard');
} else {
    unset($_SESSION['auth_token']);
    session_destroy();
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body style="width: 100vw;height:100vh;color: white;background: #051139;display:flex;justify-content:center;align-items:center;">
    <div style="width: 400px;background-color:#111c44;" class="px-4 py-5 border border-primary-subtle rounded rounded-md">
        <div class="text-center pb-3">
                            <img class="text-center" src="/uploads/<?php echo $dataconfig['logo'] ?>?v=<?= rand(1000,9999) ?>" alt="Logo Painel Afiliado" width="auto" height="55px" />
        </div>
        <h5 class="text-center" style="border-bottom: 0.5px solid #36404a;padding-bottom:10px">Painel de afiliados</h5>
        <form id="loginForm">
            <div class="mb-3">
                <label for="mobile" class="form-label">Login</label>
                <input type="mobile" class="form-control" id="mobile" name="mobile" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Acessar</button>
        </form>



        <!-- Bootstrap 5.3 JS and dependencies -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Script de requisição AJAX com JavaScript -->
        <script>
            document.getElementById('loginForm').addEventListener('submit', async function(event) {
                event.preventDefault();

                const formData = new FormData(this);
                const response = await fetch('/afiliados/api/login', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                console.log("result ->", result);
                if (result.data) {
                    sessionStorage.setItem('auth_token', result.data)
                }
                showToast(result.status === 'success' ? 'success' : 'danger', result.message);

                if (result.status === 'success') {
                    // Redirecionar em caso de sucesso
                    setTimeout(() => {
                        window.location.href = '/afiliados/dashboard';
                    }, 1000);
                }
            });

            function showToast(type, message) {
                var toastPlacement = document.getElementById('toastPlacement');
                var toast = document.createElement('div');
                toast.className = `toast align-items-center bg-dark border-0 fade show`;
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                toast.innerHTML = `
<div class="toast-header bg-dark text-white">
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

        <div id="toastPlacement" class="toast-container position-fixed bottom-0 end-0 p-3"></div>
</body>

</html>