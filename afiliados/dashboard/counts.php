<div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-4 col-md-6 col-12">
                <div class="card  mb-4">
                    <div class="card-body p-3 position-relative">
                        <div class="row">
                            <div class="col-8 ">
                                <div class="numbers ">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold ">Saldo disponível</p>
                                    <h5 class="font-weight-bolder">
                                        R$ <?= number_format($datauser['saldo_afiliados'], 2, ',', '.'); ?>
                                    </h5>
                                    <p class="mb-0">
                                        Saldo dispinível para saque
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-xs " style="width: 150px;position: absolute;right:5px;bottom:-10px;display: <?php echo (int)$datauser['disabled_resgate'] === 1 ? 'none' : '' ?>;" data-bs-toggle="modal" data-bs-target="#modalResgate">Solicitar saque</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <div class="card  mb-4">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Saldo dos indicados</p>
                                    <h5 class="font-weight-bolder">
                                        R$ <?= number_format($subordinados['saldo'], 2, ',', '.'); ?>
                                    </h5>
                                    <p class="mb-0">
                                        Saldo geral dos indicados
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <div class="card  mb-4">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Cadastros</p>
                                    <h5 class="font-weight-bolder">
                                        <?= $subordinados['cadastros']; ?>
                                    </h5>
                                    <p class="mb-0">
                                        Usuários cadastrados com seu link
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalResgate" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #111c44;">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Resgatar</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeModal"></button>
            </div>
            <div class="modal-body">
                <form id="formResgatar" method="post">
                    <div class="mb-3">
                        <label for="exampleFormControlInput1" class="form-label">Valor</label>
                        <input type="number" class="form-control" id="valor" name="valor" placeholder="0" style="color:white;background-color:#051139;font-weight:bold;">
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary w-100">Solicitar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const myModal = document.getElementById('modalResgate')

    document.getElementById('formResgatar').addEventListener('submit', async (event) => {
        event.preventDefault();
        let valor = document.getElementById('valor').value;
        const formData = new FormData();
        formData.append('valor', valor);
        const response = await fetch('/afiliados/api/resgate', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        console.log("result ->", result);
        sessionStorage.setItem('auth_token', result.data)
        showToast(result.status === 'success' ? 'success' : 'danger', result.message);

        if (result.status === 'success') {
            // Redirecionar em caso de sucesso
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            document.getElementById('closeModal').click();
            document.getElementById('valor').value = 0;
        }
    });

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
<div id="toastPlacement" class="toast-container position-fixed bottom-0 end-0 p-3"></div>