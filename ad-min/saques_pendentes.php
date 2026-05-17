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

function marcarComoPago($id)
{
    global $pdo;

    $stmt = $pdo->prepare("UPDATE solicitacao_saques SET status = 1 WHERE id = :id AND tipo_saque = 0");
    // Bind the parameter to the query
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    return json_encode(["success" => true, "message" => "Saque marcado como pago com sucesso."]);
}

function marcarComoPagoImg($id, $file)
{

    global $pdo;

    $stmt = $pdo->prepare("UPDATE solicitacao_saques SET status = 1, comprovante = :comprovante WHERE id = :id");
    // Bind the parameters to the query
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':comprovante', $file, PDO::PARAM_STR);

    // Execute the query
    $stmt->execute();

    return json_encode(["success" => true, "message" => "Saque marcado como pago com sucesso."]);
}

// Verifica se uma requisição AJAX foi feita
if (isset($_GET['marcarComoPago'])) {
    if (isset($_FILES['comprovante'])) {
        $file = $_FILES['comprovante'];
        $saqueId = $_GET['id'];

        // Diretório de upload
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . 'uploads/comprovantes/';

        // Verificar se o diretório existe, caso contrário, criá-lo
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Pegar a extensão do arquivo original
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Gerar um UUID para o nome do arquivo
        $uuid = uniqid(); // Gera um ID único

        // Montar o caminho completo do arquivo com a nova UUID e a extensão original
        $uploadFile = $uploadDir . $uuid . '.' . $fileExtension;

        // Mover o arquivo para o diretório de uploads
        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            // Chamar a função que marca o saque como pago e salva o caminho do comprovante
            echo marcarComoPagoImg($saqueId, '/uploads/comprovantes/' . $uuid . '.' . $fileExtension);
            exit; // Para garantir que não execute o resto do código HTML
        } else {
            echo json_encode(["success" => false, "message" => "Erro ao mover o arquivo."]);
            exit;
        }
    } else {
        // Se não houver comprovante, apenas marcar como pago
        echo marcarComoPago($_GET['id']);
        exit; // Para garantir que não execute o resto do código HTML
    }
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
                                        <h4 class="card-title">Saques Pendentes</h4>
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
                                                <th>Chave Pix</th>
                                                <th>Status</th>
                                                <th>Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            global $mysqli;
                                            // Consultar os dados da tabela usuarios
                                            $query_usuarios = "SELECT * FROM solicitacao_saques WHERE status = '0' ORDER BY id DESC";
                                            $result_usuarios = mysqli_query($mysqli, $query_usuarios);

                                            if ($result_usuarios && mysqli_num_rows($result_usuarios) > 0) {
                                                while ($usuario = mysqli_fetch_assoc($result_usuarios)) {
                                                    $cargo_badge = ($usuario['status'] == '0') ? "<span class='badge bg-danger'>Em Analise</span>" : "<span class='badge bg-secondary'>Usuário</span>";

                                                    $chaveatt = localizarchavepix($usuario['tipo']);
                                            ?>
                                                    <tr>
                                                        <td><?= $usuario['id']; ?></td>
                                                        <td><?php echo getUserById($usuario['id_user']); ?></td>
                                                        <td><?= $usuario['transacao_id']; ?></td>
                                                        <td>R$ <?= number_format($usuario['valor'], 2, ',', '.'); ?></td>
                                                        <td><?= $usuario['data_cad']; ?> : <?= $usuario['data_hora']; ?></td>
                                                        <td>
                                                            <span><?= $chaveatt ?></span>
                                                            <button type="button" class="btn btn-primary btn-clipboard ms-2"
                                                                data-clipboard-text="<?= $chaveatt; ?>">
                                                                <i class="far fa-copy"></i>
                                                            </button>
                                                        </td>
                                                        <td><?= $cargo_badge; ?></td>
                                                        <td>
                                                            <button class="btn btn-warning btn-edit-saque"
                                                                data-id="<?= $usuario['id']; ?>"
                                                                data-transacao="<?= $usuario['transacao_id']; ?>"
                                                                data-valor="<?= number_format($usuario['valor'], 2, ',', '.'); ?>"
                                                                data-chave="<?= $chaveatt; ?>"
                                                                data-status="<?= $usuario['status']; ?>">Editar</button>
                                                        </td>
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
                        </div>
                    </div><!-- container -->
                </div>
            </div>
        </div>

        <!-- Modal Editar Saque -->
        <div class="modal fade" id="modalEditarSaque" tabindex="-1" aria-labelledby="modalEditarSaqueLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarSaqueLabel">Editar Saque</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditarSaque">
                            <input type="hidden" id="saqueId" name="saqueId">
                            <input type="hidden" id="_csrf" name="_csrf" value="<?= md5(uniqid()) ?>">
                            <!-- CSRF token -->
                            <div class="mb-3">
                                <label for="transacaoId" class="form-label">Transação ID</label>
                                <input type="text" class="form-control" id="transacaoId" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="valorSaque" class="form-label">Valor</label>
                                <input type="text" class="form-control" id="valorSaque" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="chavePix" class="form-label">Chave Pix</label>
                                <input type="text" class="form-control" id="chavePix" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="comprovante" class="form-label">Comprovante</label>
                                <input type="file" class="form-control" id="comprovante" readonly>
                                <span class="py-1" style="font-size: 10px;"><span style="color: red;">*</span> Enviar comprovante apenas se for "Marcar como pago"</span>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" id="btnMarcarPago">Marcar como pago</button>
                        <button type="button" class="btn btn-success" id="btnAprovarSaque">Aprovar</button>
                        <button type="button" class="btn btn-danger" id="btnRecusarSaque">Recusar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <?php include 'partials/vendorjs.php' ?>
        <script src="assets/js/app.js"></script>
        <script src="assets/libs/clipboard/clipboard.min.js"></script>
        <script src="assets/js/pages/clipboard.init.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Evento para abrir o modal e preencher as informações do saque
                const btnsEdit = document.querySelectorAll('.btn-edit-saque');
                btnsEdit.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const saqueId = this.getAttribute('data-id');
                        const transacaoId = this.getAttribute('data-transacao');
                        const valor = this.getAttribute('data-valor');
                        const chavePix = this.getAttribute('data-chave');

                        // Preenche os campos do modal
                        document.getElementById('saqueId').value = saqueId;
                        document.getElementById('transacaoId').value = transacaoId;
                        document.getElementById('valorSaque').value = valor;
                        document.getElementById('chavePix').value = chavePix;

                        // Exibe o modal
                        const modal = new bootstrap.Modal(document.getElementById('modalEditarSaque'));
                        modal.show();
                    });
                });

                // Evento para aprovar saque
                document.getElementById('btnAprovarSaque').addEventListener('click', function() {
                    const transacaoId = document.getElementById('transacaoId').value;

                    // Enviar os dados via POST para payment_manual.php
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", `services-gateway/payment_manual.php?id=${transacaoId}`, true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            console.log(xhr.responseText)
                            const response = JSON.parse(xhr.responseText);

                            // Verifica o status da resposta e exibe a mensagem apropriada
                            if (response.success) {
                                console.log("SUCESSO")
                                showToast('success', response.message || 'Saque aprovado com sucesso!');
                                // Fechar o modal após a resposta
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarSaque'));
                                modal.hide();
                                // Recarregar a página após o toast
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                showToast('danger', response.message || 'Erro ao aprovar o saque.');
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarSaque'));
                                modal.hide();
                                // Recarregar a página após o toast
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            }
                        } else if (xhr.readyState === 4) {
                            showToast('danger', 'Erro ao aprovar o saque.');
                        }
                    };
                    xhr.send(); // Não precisamos enviar parâmetros via body, já que o transacao_id vai na URL.
                });

                // Evento para recusar saque
                document.getElementById('btnRecusarSaque').addEventListener('click', function() {
                    const saqueId = document.getElementById('saqueId').value;
                    const _csrf = document.getElementById('_csrf').value;
                    const email_reprovado = document.getElementById('chavePix').value;
                    const valor_reprovado = document.getElementById('valorSaque').value;

                    // Enviar os dados via AJAX para o servidor
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "ajax/recusar_saque.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                showToast('success', response.message);
                                // Fechar o modal após a resposta
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarSaque'));
                                modal.hide();
                                // Recarregar a página após o toast
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000); // Delay para o toast ser exibido antes de recarregar
                            } else {
                                showToast('danger', response.message);
                            }
                        }
                    };
                    xhr.send(`att-pay=1&_csrf=${_csrf}&id_pay=${saqueId}&email_reprovado=${email_reprovado}&valor_reprovado=${valor_reprovado}`);
                });

                // Função para exibir toast com o estilo fornecido
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
            });

            document.getElementById('btnMarcarPago').addEventListener('click', function() {

                document.getElementById('btnMarcarPago').setAttribute('disabled', true);

                const saqueId = document.getElementById('saqueId').value;
                var comprovante = document.getElementById('comprovante');

                if (comprovante.files.length === 0) {
                    showToast('error', "Por favor, selecione o comprovante antes de continuar.");
                    document.getElementById('btnMarcarPago').removeAttribute('disabled');
                    return;
                }


                // Criar um objeto FormData
                var formData = new FormData();
                formData.append('comprovante', comprovante.files[0]);


                // Pega o primeiro arquivo selecionado

                // Fazer a requisição com fetch, usando o FormData
                fetch(`?marcarComoPago=true&id=${saqueId}`, {
                        method: 'POST', // Envio do arquivo requer método POST
                        body: formData
                    })
                    .then(response => response.text()) // Obter a resposta como texto
                    .then(data => {
                        console.log(data);
                        // Exibir o resultado da função PHP
                        showToast('success', "Saque marcado como pago com sucesso!");
                        setTimeout(() => {
                            window.location.reload()
                        }, 3000)
                    })
                    .catch(error => console.error('Erro:', error));


                // Função para exibir toast com o estilo fornecido
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

                // Chama o próprio arquivo PHP, passando um parâmetro na URL para identificar que a função deve ser executada
                /* fetch(`?marcarComoPago=true&id=${saqueId}`)
                    .then(response => response.text()) // Obter a resposta como texto
                    .then(data => {
                        console.log(data);
                        // Exibir o resultado da função PHP
                        showToast('success', "Saque marcado como pago com sucesso!");
                        window.location.reload();
                    })
                    .catch(error => console.error('Erro:', error)); */
            });
        </script>

        <!-- Adicione um elemento de posicionamento para o Toast -->
        <div id="toastPlacement" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11"></div>


</body>
<!--end body-->

</html>