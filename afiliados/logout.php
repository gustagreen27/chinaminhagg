<?php
session_start();

session_destroy();
unset($_SESSION['auth_token']);
header('Location: /afiliados/login');
exit();

?>
<script>
    setTimeout(() => {
        window.location.href = "/afiliados/login";
    }, 1000)
</script>