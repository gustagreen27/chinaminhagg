<?php
session_start();
include_once("ad-min/services/database.php");
include_once("ad-min/services/funcao.php");
include_once("ad-min/services/crud.php");
include_once("ad-min/services/CSRF_Protect.php");
include_once("ad-min/services/pega-ip.php");
include_once("ad-min/services/ip-crawler.php");
$csrf = new CSRF_Protect();
#==================================================================#
if (isset($_GET['utm_ads']) && !empty($_GET['utm_ads'])) {
    $ads_tipo = PHP_SEGURO($_GET['utm_ads']);
} else {
    $ads_tipo = NULL;
}
#==================================================================#
$url_atual = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
#==================================================================#
//INSERT DE VISITAS NAS LPS
$data_hoje = date("Y-m-d");
$hora_hoje = date("H:i:s");
if (isset($_SERVER['HTTP_REFERER'])) {
    $ref =  $_SERVER['HTTP_REFERER'];
} else {
    $ref = $url_atual;
}
#==================================================================#
$data_us = ip_F($ip);
#==================================================================#
if ($browser != "Unknown Browser" and $os != "Unknown OS Platform" and $data_us['pais'] == "Brazil") {
    $id_user_ret = "1";
    $sql0 = $mysqli->prepare("SELECT ip_visita FROM visita_site WHERE data_cad=? AND ip_visita=?");
    $sql0->bind_param("ss", $data_hoje, $ip);
    $sql0->execute();
    $sql0->store_result();
    if ($sql0->num_rows) { //JÁ EXISTE CAD 
    } else {
        $sql = $mysqli->prepare("INSERT INTO visita_site (nav_os,mac_os,ip_visita,refer_visita,data_cad,hora_cad,id_user,pais,cidade,estado,ads_tipo) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $sql->bind_param("sssssssssss", $browser, $os, $ip, $ref, $data_hoje, $hora_hoje, $id_user_ret, $data_us['pais'], $data_us['cidade'], $data_us['regiao'], $ads_tipo);
        $sql->execute();
    }
}
#===============================================================================#  

?>
<!doctype html>
<html lang="pt">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0" name="viewport" />
    <title><?= $dataconfig['nome']; ?></title>
    <script src="/xxxx/prod/config.js?v=2024_8_30_15_11"></script>
    <script src="/ssss/theme.php"></script>
    <link rel="apple-touch-icon" href="/xxxx/h5/favicon.png" />
    <link rel="manifest" href="/manifest.json">
    <meta property="og:title" content="" />
    <meta property="og:description" content="" />
    <meta property="og:image" content="/xxxx/h5/share_image.jpg" />
    <meta property="og:type" content="website">
    <meta property="og:url" content="/xxxx/h5/share_image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="600" />
    <meta property="og:updated_time" content="1725001884" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta property="twitter:title" content="" />
    <meta property="twitter:description" content="" />
    <meta property="twitter:url" content="/xxxx/h5/share_image.jpg" />
    <meta property="twitter:image" content="/xxxx/h5/share_image.jpg" />
    <meta property="twitter:type" content="website">
    <meta property="twitter:image:width" content="1200" />
    <meta property="twitter:image:height" content="600" />
    <meta property="twitter:updated_time" content="1725001884" />
    <meta property="title" content="" />
    <meta property="description" content="" />
    <meta property="url" content="/xxxx/h5/share_image.jpg" />
    <meta property="image" content="/xxxx/h5/share_image.jpg" />
    <meta property="type" content="website">
    <meta property="image:width" content="1200" />
    <meta property="image:height" content="600" />
    <meta property="updated_time" content="1725001884" />
    <meta property="al:title" content="" />
    <meta property="al:description" content="" />
    <meta property="al:url" content="/xxxx/h5/share_image.jpg" />
    <meta property="al:image" content="/xxxx/h5/share_image.jpg" />
    <meta property="al:image:width" content="1200" />
    <meta property="al:image:height" content="600" />
    <meta property="al:type" content="website">
    <meta property="al:updated_time" content="1725001884" />
    <script src="https://accounts.google.com/gsi/client" async defer="defer"></script>
    <script src="https://apis.google.com/js/platform.js?onload=init" async defer="defer"></script>
    <script>
        function init() {
            gapi.load('auth2', function() {
                console.log('22222222222222222222')
                /* Ready. Make a call to gapi.auth2.init or some other API */
            });
        }
    </script>
    <script async defer="defer" crossorigin="anonymous"
        src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v20.0" nonce="s2QYaSCr"></script>
    <script
        type="text/javascript">
        (function() {
            setTimeout(function() {
                var temp = '<script type="module" crossorigin src="yq-br-prod/web1/assets/index-CKtHrVPI-2024_9_14_11_28.js">____script><link rel="stylesheet" crossorigin href="yq-br-prod/web1/assets/index-DQZyYQwA-2024_9_14_11_28.css"><script type="module">import.meta.url;import("_").catch(()=>1);(async function*(){})().next();if(location.protocol!="file:"){window.__vite_is_modern_browser=true}____script><script type="module">!function(){if(window.__vite_is_modern_browser)return;console.warn("vite: loading legacy chunks, syntax error above and the same error below should be ignored");var e=document.getElementById("vite-legacy-polyfill"),n=document.createElement("script");n.src=e.src,n.onload=function(){System.import(document.getElementById("vite-legacy-entry").getAttribute("data-src"))},document.body.appendChild(n)}();____script>';
                var div = document.createElement('div');
                div.style.width = '0px';
                div.style.height = '0px';
                div.style.display = 'none';
                document.body.appendChild(div);
                var range = document.createRange();
                range.selectNode(div);
                var doc = range.createContextualFragment(temp.replace(/____/g, '</'));
                div.appendChild(doc);
            }, 0);
        })()
    </script>
</head>

<body>
    <!--- DESENVOLVIDO POR JB-MCB SOFTBET --->
    <!--- +55 41 999917500 --->
    <div id="root"></div>

    <div id="logRegBlock"></div>
    <!--- DESENVOLVIDO POR JB-MCB SOFTBET --->
    <!--- +55 41 999917500 --->

    <script nomodule>
        ! function() {
            var e = document,
                t = e.createElement("script");
            if (!("noModule" in t) && "onbeforeload" in t) {
                var n = !1;
                e.addEventListener("beforeload", function(e) {
                        if (e.target === t) n = !0;
                        else if (!e.target.hasAttribute("nomodule") || !n) return;
                        e.preventDefault();
                    }, !0),
                    t.type = "module",
                    t.src = ".",
                    e.head.appendChild(t),
                    t.remove()
            }
        }();
    </script>

    <script nomodule crossorigin id="vite-legacy-polyfill"
        src="https://" + window.location.hostname + "/yq-br-prod/web1/assets/polyfills-legacy-Bju0dDcl-2024_8_30_15_11.js"></script>

    <script nomodule crossorigin id="vite-legacy-entry"
        data-src="https://" + window.location.hostname + "/yq-br-prod/web1/assets/index-legacy-CsCDms-9-2024_8_30_15_11.js">
        System.import(document.getElementById('vite-legacy-entry').getAttribute('data-src'))
    </script>
    <script>
        let deferredPrompt; // Variável para armazenar o evento beforeinstallprompt

        // Escutar o evento 'beforeinstallprompt'
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault(); // Impede que o navegador exiba o prompt automaticamente
            deferredPrompt = e; // Armazena o evento para ser usado mais tarde

            // Adiciona evento ao botão para disparar o prompt de instalação
            button.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt(); // Exibe o prompt de instalação
                    const choiceResult = await deferredPrompt.userChoice; // Espera a resposta do usuário
                    console.log(`Resultado do usuário: ${choiceResult.outcome}`);
                    deferredPrompt = null; // Limpa o evento armazenado
                }
            });
        });

        // Simula o evento 'beforeinstallprompt' para teste
        setTimeout(() => {
            const event = new Event('beforeinstallprompt');
            window.dispatchEvent(event);
        }, 2000); // Simula o evento após 2 segundos
    </script>
</body>

</html>