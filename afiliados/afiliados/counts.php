<style>
    .message {
        display: none;
        color: green;
        margin-top: 10px;
    }

    #valor:hover {
        cursor: pointer;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-4 col-md-6 col-12">
                <div class="card  mb-4">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Renda</p>
                                    <h5 class="font-weight-bolder">
                                        R$ <?= number_format($subsubordinados, 2, ',', '.'); ?>
                                    </h5>

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
            <div class="col-lg-6 col-md-8 col-12">
                <div class="card  mb-4">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-12 position-relative">
                                <div class="form-text position-absolute left-6" style="bottom: -10px" id="basic-addon4">Link de convite <span id="message" class="message">Link copiado com sucesso!</span></div>
                                <div class="input-group mb-3 w-100">

                                    <input type="text" class="form-control" style="background-color: black;color:white;font-weight:bold;" value="https://<?= $_SERVER['HTTP_HOST'] . '/?id=' . $datauser['invite_code'] . '&currency=BRL&type=2' ?>" placeholder="Recipient's username" aria-label="Recipient's username" aria-describedby="basic-addon2" id="valor" readonly>
                                    <span class="input-group-text" id="basic-addon2" style="background-color: black;color:white;font-weight:bold;"><i class="fa-solid fa-bolt"></i></span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const inputField = document.getElementById('valor');
    const messageDiv = document.getElementById('message');

    inputField.addEventListener('click', () => {
        // Seleciona o valor do input
        inputField.select();
        inputField.setSelectionRange(0, 99999); // Para compatibilidade com dispositivos móveis

        try {
            // Copia o valor para a área de transferência
            document.execCommand('copy');

            // Exibe a mensagem de sucesso
            messageDiv.style.display = 'block';

            // Oculta a mensagem após 2 segundos
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        } catch (err) {
            console.error('Erro ao copiar o valor:', err);
        }
    });
</script>