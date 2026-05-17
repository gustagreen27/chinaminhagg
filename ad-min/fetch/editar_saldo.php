<?php
// Incluir a conexão com o banco de dados
include '../services/database.php';
include '../services/crud.php';

// Verificar se os dados foram recebidos via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter os dados enviados
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['user_id'])) {
        $user_id = intval($data['user_id']);
        $adicionar = floatval($data['adicionar']) ?? 0;
        $remover = floatval($data['remover']) ?? 0;
        
        $query = "SELECT * FROM usuarios WHERE id = '".$user_id."' ";
        $resposta = mysqli_query($mysqli, $query);
        $data = mysqli_fetch_assoc($resposta);
        
        

        if ($adicionar >= 1) {
            $ft = enviarSaldo($data['mobile'], $adicionar);
            
            // Chamar função de log de adicão de saldo caso precise.
        
            if ($ft === 1){
                echo json_encode(['success' => true, 'message' => 'Saldo adicionado com sucesso.']);
            } else {
                echo json_encode(['success' => true, 'message' => 'Erro ao adicionar saldo.']);
            }
        }
        
        if ($remover >= 1) {
            $ft = withdrawSaldo($data['mobile'], $remover);
            
            // Chamar função de log de adicão de saldo caso precise.
        
            if ($ft === 1){
                echo json_encode(['success' => true, 'message' => 'Saldo removido com sucesso.']);
            } else {
                echo json_encode(['success' => true, 'message' => 'Erro ao remover saldo.']);
            }
        }
        
    

    } else {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos fornecidos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido de requisição.']);
}

// Fechar a conexão com o banco de dados
$mysqli->close();
?>
