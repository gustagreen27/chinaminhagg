<?php
header("Content-Type: application/javascript");

include_once "../ad-min/services/database.php";

// Função para buscar o número do tema do banco de dados
function getThemeNumber() {
    global $mysqli;
    $qry = "SELECT tema FROM config WHERE id = 1";
    $result = mysqli_query($mysqli, $qry);
    $row = mysqli_fetch_assoc($result);
    return $row['tema'] ?? null;
}

$themeNumber = getThemeNumber();

// Lista de temas
$themeNames = [
    1 => 'black',
    2 => 'blue',
    3 => 'whiteGreen',
    4 => 'purple',
    5 => 'whiteRed',
    6 => 'oilyGreen',
    7 => 'versaceYellow',
    8 => 'lancomePeach',
    9 => 'whiteYellow',
    10 => 'whiteBlue',
    11 => 'sk2',
    12 => 'whiteOrange',
    13 => 'hermesOrange',
    14 => 'lightBrown',
    15 => 'furlaBlue',
    16 => 'whitePink',
    17 => 'bvGreen',
    18 => 'whiteBrown',
    19 => 'AnnaSuiPurple',
    20 => 'whitePurple',
    21 => 'burgundyRed',
    22 => 'whiteDarkGreen',
    23 => 'greenGold'
];

// Verificar se o tema foi encontrado e definir um tema padrão caso não seja
$theme = $themeNames[$themeNumber] ?? 'defaultTheme';

// Gerar o código JavaScript que será executado
echo "
(function updateThemeColor() {
    var theme = '{$theme}';
    document.documentElement.setAttribute('theme', theme);
})();
";
