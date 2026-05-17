<!DOCTYPE html>
<?php
include_once "./../../ad-min/services/crud.php";
global $mysqli;

$query = "SELECT * FROM config WHERE id = 1";
$configs = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
$config = mysqli_fetch_assoc($configs);

$paymentCode = isset($_GET['paymentCode']) ? $_GET['paymentCode'] : '';
$safeCode = mysqli_real_escape_string($mysqli, $paymentCode);

$querypc = "SELECT * FROM transacoes WHERE code = '" . $safeCode . "'";
$trs = mysqli_query($mysqli, $querypc) or die(mysqli_error($mysqli));
$tr = mysqli_fetch_assoc($trs);

$tid = $tr ? $tr['transacao_id'] : '';
$status = $tr ? $tr['status'] : '';

$primaryColor = htmlspecialchars($config['cor_padrao'] ?? '#07c160');
?>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <title>PIX QR</title>
  <link rel="icon" href="Pix%20QR_arquivos/logo.png">
  <style>
    html {
      font-size: 62.5%;
    }

    body {
      margin: 0;
      background: #f4f5f6;
      font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", Helvetica, Segoe UI, Arial, Roboto, sans-serif;
    }

    .container {
      margin: 0 auto;
      position: relative;
      max-width: 41.4rem;
      background: #fff;
      min-height: 100vh;
    }

    .payment-view {
      padding: 1.5rem;
    }

    .payment-main {
      margin-top: 2rem;
    }

    .trans-no {
      text-align: right;
      color: #999;
      font-size: 1.2rem;
      word-break: break-all;
      margin-bottom: 0.8rem;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .info-wrap {
      border: 1px solid #dde7f0;
      border-radius: 0.8rem;
      padding: 1rem 1.5rem;
    }

    .info-box {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .info-label {
      font-size: 1.6rem;
      color: #323233;
    }

    .val {
      font-size: 2.8rem;
      font-weight: bold;
      color: #323233;
    }

    .van-divider--hairline {
      border: none;
      border-top: 1px solid #ebedf0;
      margin: 0.8rem 0;
    }

    .qrcode-wrap {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      border: 1px solid #dde7f0;
      border-radius: 0.8rem;
      padding: 1.5rem;
      margin-top: 1rem;
      position: relative;
    }

    .tips {
      text-align: center;
      font-size: 1.4rem;
      font-weight: 600;
      color: #333;
      margin: 0;
      line-height: 1.6;
    }

    .qrcode-box {
      margin: 1.5rem auto 0;
      width: 25.4rem;
      height: 25.4rem;
      position: relative;
    }

    .qrcode-box img {
      width: 100%;
      height: 100%;
      display: block;
    }

    .qrcode-box canvas {
      width: 100% !important;
      height: 100% !important;
    }

    .paid-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 8rem;
      background: rgba(255, 255, 255, 0.88);
      border-radius: 0.4rem;
      z-index: 10;
    }

    .copy-btn {
      width: 85%;
      height: 5.5rem;
      font-size: 1.6rem;
      color: #fff;
      background:
        <?php echo $primaryColor; ?>
      ;
      border: none;
      border-radius: 1rem;
      margin-top: 1.5rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 1rem;
      font-weight: 600;
      letter-spacing: 0.04em;
    }

    .copy-btn:hover {
      opacity: 0.9;
    }

    .copy-btn:active {
      opacity: 0.8;
    }

    .back-btn {
      width: 85%;
      height: 5rem;
      font-size: 1.5rem;
      color:
        <?php echo $primaryColor; ?>
      ;
      background: transparent;
      border: 2px solid
        <?php echo $primaryColor; ?>
      ;
      border-radius: 1rem;
      margin-top: 1rem;
      cursor: pointer;
      font-weight: 600;
    }

    .back-btn:hover {
      background: rgba(0, 0, 0, 0.04);
    }

    .digitopay-warning {
      color: red;
      font-size: 1.3rem;
      font-weight: bold;
      text-align: center;
      margin-top: 1rem;
      line-height: 1.5;
    }

    .pix-icon {
      margin-top: 1.5rem;
      width: 14rem;
      height: 5rem;
      object-fit: contain;
    }

    .toast-wrap {
      width: 100vw;
      height: 100vh;
      position: fixed;
      z-index: 9999;
      top: 0;
      left: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      visibility: hidden;
      pointer-events: none;
    }

    .toast {
      font-size: 1.4rem;
      color: #fff;
      padding: 1.2rem 2.4rem;
      background: rgba(0, 0, 0, 0.75);
      border-radius: 0.6rem;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="payment-view">
      <div class="payment-main">

        <div id="sid" class="trans-no"><?php echo htmlspecialchars($paymentCode); ?></div>

        <div class="info-wrap">
          <div class="info-box">
            <span class="info-label">Valor</span>
            <span class="val">R$ <span id="pix-value">--</span></span>
          </div>
          <div class="van-divider--hairline"></div>
        </div>

        <div class="qrcode-wrap">
          <p class="tips">
            Abra o app com sua chave PIX cadastrada, escolha Pagar com Pix e escaneie o QR Code
            ou copie e cole o código.<br><br>
            Este código só pode ser pago uma vez. Se precisar pagar novamente, solicite um novo.
          </p>

          <div id="qrcode" class="qrcode-box"></div>

          <?php if ($config['gateway_default'] === 'digitopay') { ?>
            <p class="digitopay-warning">
              IMPORTANTE: Após efetuar o pagamento aguarde a confirmação!<br>
              Não saia da tela até que a confirmação apareça.
            </p>
          <?php } ?>

          <button type="button" class="copy-btn" onclick="CopyCid()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="white">
              <path
                d="M16 1H4C3 1 2 2 2 3v14h2V3h12V1zm3 4H8C7 5 6 6 6 7v14c0 1 1 2 2 2h11c1 0 2-1 2-2V7c0-1-1-2-2-2zm0 16H8V7h11v14z" />
            </svg>
            PIX CÓPIA E COLA
          </button>

          <?php if ($config['gateway_default'] === 'digitopay') { ?>
            <button id="btn-voltar" type="button" class="back-btn" onclick="history.back()" style="display:none;">
              Pagamento realizado. Clique aqui para voltar
            </button>
          <?php } else { ?>
            <button id="btn-voltar" type="button" class="back-btn" onclick="history.back()">
              Já fiz o pagamento
            </button>
          <?php } ?>

          <img class="pix-icon" src="https://logodownload.org/wp-content/uploads/2021/01/pix-bc-logo.png" alt="PIX"
            onerror="this.style.display='none'">
        </div>

      </div>
    </div>

    <div class="toast-wrap" id="toast-wrap">
      <div class="toast">Código PIX copiado!</div>
    </div>
  </div>

  <script src="Pix%20QR_arquivos/jquery.min.js"></script>
  <script src="Pix%20QR_arquivos/qrcode.min.js"></script>
  <script>
    function getUrlParameter(name) {
      name = name.replace(/[\[\]]/g, '\\$&');
      var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
      var r = regex.exec(window.location.href);
      if (!r) return null;
      if (!r[2]) return '';
      return decodeURIComponent(r[2].replace(/\+/g, ' '));
    }

    var paymentCode = getUrlParameter('paymentCode') || '';
    var paymentCodeBase64 = getUrlParameter('paymentCodeBase64') || '';
    var amount = getUrlParameter('valorPix') || '';

    if (amount) {
      document.getElementById('pix-value').textContent = amount;
    }

    // Render QR code
    var qrcodeBox = document.getElementById('qrcode');
    if (paymentCodeBase64) {
      var img = document.createElement('img');
      img.src = 'data:image/png;base64,' + paymentCodeBase64.replace(/\s+/g, '');
      img.alt = 'QR Code PIX';
      qrcodeBox.appendChild(img);
    } else if (paymentCode) {
      var qr = new QRCode(qrcodeBox, { correctLevel: 3, width: 254, height: 254 });
      qr.makeCode(paymentCode);
    }

    // Copy PIX code to clipboard
    function CopyCid() {
      if (!paymentCode) return;
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(paymentCode).then(showToast).catch(function () { legacyCopy(paymentCode); });
      } else {
        legacyCopy(paymentCode);
      }
    }

    function legacyCopy(text) {
      var el = document.createElement('input');
      el.value = text;
      el.style.cssText = 'position:fixed;opacity:0;';
      document.body.appendChild(el);
      el.select();
      document.execCommand('copy');
      document.body.removeChild(el);
      showToast();
    }

    function showToast() {
      var tw = document.getElementById('toast-wrap');
      tw.style.visibility = 'visible';
      setTimeout(function () { tw.style.visibility = 'hidden'; }, 1500);
    }

    // Show paid checkmark overlay on QR box
    function showPaidOverlay() {
      if (qrcodeBox.querySelector('.paid-overlay')) return;
      var overlay = document.createElement('div');
      overlay.className = 'paid-overlay';
      overlay.textContent = '✅';
      qrcodeBox.appendChild(overlay);
    }

    // Payment verification polling
    var formData = new FormData();
    formData.append('id', <?= json_encode($tid) ?>);
    formData.append('gateway_default', <?= json_encode($config['gateway_default'] ?? '') ?>);

    var intervalId = setInterval(function () {
      fetch('atualizar_pagamento.php', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.status === 'success') {
            showPaidOverlay();
            clearInterval(intervalId);
            <?php if ($config['gateway_default'] === 'digitopay') { ?>
              document.getElementById('btn-voltar').style.display = 'block';
            <?php } else { ?>
              setTimeout(function () { history.back(); }, 3000);
            <?php } ?>
          }
        })
        .catch(function () { });
    }, 5000);

    // If already paid on page load
    <?php if ($status === 'pago') { ?>
      clearInterval(intervalId);
      showPaidOverlay();
      <?php if ($config['gateway_default'] === 'digitopay') { ?>
        document.getElementById('btn-voltar').style.display = 'block';
      <?php } else { ?>
        setTimeout(function () { history.back(); }, 3000);
      <?php } ?>
    <?php } ?>
  </script>
</body>

</html>