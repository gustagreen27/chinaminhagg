<?php
include '../services/database.php'; // Certifique-se de incluir o arquivo que contém a conexão com o banco de dados

// Defina o cabeçalho para que o navegador entenda que estamos exportando um arquivo CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=usuarios_exportados.csv');

// Crie um recurso de arquivo para escrever os dados CSV
$output = fopen('php://output', 'w');

// Escreva a primeira linha do CSV (os cabeçalhos)
fputcsv($output, array('ID', 'Usuário', 'Saldo', 'Total Depositado', 'Total Sacado', 'Status'));

// Consulta SQL para pegar os dados dos usuários
$query = "SELECT u.id, u.mobile, u.saldo, 
                 (SELECT SUM(t.valor) FROM transacoes t WHERE t.usuario = u.id AND t.tipo = 'deposito' AND t.status = 'pago') AS total_depositado,
                 (SELECT SUM(s.valor) FROM solicitacao_saques s WHERE s.id_user = u.id AND s.status = 1) AS total_sacado,
                 CASE 
                    WHEN u.banido = 1 THEN 'Banido'
                    WHEN u.statusaff = 1 THEN 'Afiliado'
                    ELSE 'Usuário'
                 END AS status
          FROM usuarios u";
$result = mysqli_query($mysqli, $query);

// Verifique se há resultados na consulta
if ($result && mysqli_num_rows($result) > 0) {
    // Escreva os dados dos usuários no arquivo CSV
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, array(
            $row['id'],
            $row['mobile'],
            number_format($row['saldo'], 2, ',', '.'),
            number_format($row['total_depositado'], 2, ',', '.'),
            number_format($row['total_sacado'], 2, ',', '.'),
            $row['status']
        ));
    }
} else {
    // Caso não haja usuários, escreva uma linha de aviso
    fputcsv($output, array('Nenhum usuário encontrado.'));
}

// Feche o recurso de arquivo
fclose($output);
exit();