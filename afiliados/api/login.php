<?php
session_start();
header('Content-Type: application/json');
require '../../ad-min/services/database.php'; // Arquivo de conexão com banco de dados

$response = ['status' => 'error', 'message' => 'Credenciais inválidas'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];

    // Verifique o usuário no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE mobile = :mobile");
    $stmt->bindParam(':mobile', $mobile);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!isset($user)) {
        echo json_encode($response);
        exit;
    }

    if ($user && password_verify($password, $user['password'])) {
        // Gerar token de autenticação
        $token = bin2hex(random_bytes(16));

        // Salvar o token no banco
        $stmt = $pdo->prepare("UPDATE usuarios SET token = :token WHERE id = :id");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();

        // Armazenar token na sessão
        $_SESSION['auth_token'] = $token;

        // Resposta de sucesso
        $response = ['status' => 'success', 'message' => 'Login bem-sucedido', 'data' => $token];
        echo json_encode($response);
    } else {
        echo json_encode($response);
        exit;
    }
}
