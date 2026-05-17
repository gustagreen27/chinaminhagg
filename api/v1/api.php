<?php
/*  ========================================================================================
#     SSSSS   OOOOO   FFFFF   TTTTT   BBBBB    EEEEE   TTTTT        JJJ    BBBBB          M   M   CCCCC   BBBBB  
#    S       O     O  F         T     B    B   E         T            J    B    B         MM MM  C        B    B 
#     SSSSS  O     O  FFFFF     T     BBBBB    EEEEE     T            J    BBBBB  oooooo  M M M  C        BBBBB  
#         S  O     O  F         T     B    B   E         T        J   J    B    B         M   M  C        B    B 
#     SSSSS   OOOOO   F         T     BBBBB    EEEEE     T         JJJJ    BBBBB          M   M   CCCCC   BBBBB  
#
#===============================================================================================
#                                 S O F T B E T   A N D   J B - M C B
#                                                                
 */
/*-----------------------------------------------------------------------------------------------*/
/* Main Settings REST API china V3 */

// Debugar Erros No Código / 1 = ON, 0 = OFF
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Obter Dados Enviados Via Req
parse_str(file_get_contents("php://input"), $data);

// Verificar se o JSON foi decodificado com sucesso
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Erro na decodificação do JSON.']);
    exit;
}

// Definir Tipo De Conteúdo Da Resposta
header('Content-Type: application/json');

/*-----------------------------------------------------------------------------------------------*/
/* Main Functions REST API china V3 */

// Função para lidar com a resposta de erro
function sendError($code, $message)
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

// Verificação de rotas
$rotaEncontrada = false;

// Método Da Requisição
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Url De Origem Da Req
$requestURI = $_SERVER['REQUEST_URI'];

/*-----------------------------------------------------------------------------------------------*/

/* Dependencias Da Api */

include_once "./../../ad-min/services-prod/prod.php";
include_once "./../../ad-min/services/database.php";
include_once "./../../ad-min/services/funcao.php";
include_once "./../../ad-min/services/crud.php";

/*-----------------------------------------------------------------------------------------------*/

/* Function Baú */

function getBoxList($mysqli, $token)
{
    // Verificar o token
    $qry = "SELECT * FROM usuarios WHERE token = '$token'";
    $resp = mysqli_query($mysqli, $qry);

    if (mysqli_num_rows($resp) > 0) {
        $user = mysqli_fetch_assoc($resp);

        // Verifica se o tipo de pagamento é 2, para definir o total_mem_count como 0
        $total_mem_count = ($user['tipo_pagamento'] == 2) ? 0 : $user['pessoas_convidadas'];

        // Obter o número de pessoas convidadas diretamente da coluna pessoas_convidadas na tabela usuarios
        $invite_count = $user['pessoas_convidadas'];

        // Buscar o valor atual de 'num' na tabela 'bau' para o usuário específico
        $qry = "SELECT num FROM bau WHERE token = '$token'";
        $resp = mysqli_query($mysqli, $qry);
        $row = mysqli_fetch_assoc($resp);
        $nums = $row['num'];

        // Converter a string de números em um array
        $numsArray = !empty($nums) ? explode(',', $nums) : [];

        // Obter os valores dos baús da tabela config
        $config_qry = "SELECT niveisbau, qntsbaus, nvlbau, pessoasbau FROM config";
        $config_resp = mysqli_query($mysqli, $config_qry);
        $config = mysqli_fetch_assoc($config_resp);

        // Converter a string de níveis em um array
        $niveis_bau = explode(',', $config['niveisbau']);
        $quantidade_baus = $config['qntsbaus'];
        $pessoas_bau = $config['pessoasbau'];

        // Calcular a quantidade de baús por nível
        $baus_por_nivel = ceil($quantidade_baus / count($niveis_bau));

        // Criar a lista de baús com valores do banco de dados
        $baus = [];
        for ($i = 1; $i <= $quantidade_baus; $i++) {
            // Determinar o nível do baú com base na posição
            $nivel_index = floor(($i - 1) / $baus_por_nivel);
            $money = isset($niveis_bau[$nivel_index]) ? (float) $niveis_bau[$nivel_index] : (float) end($niveis_bau);

            // Calcular a condição necessária para cada baú
            $condition = $i * $pessoas_bau; // $i (1) multiplicado por $pessoas_bau
            $is_get = 1;

            if (in_array($condition, $numsArray)) {
                $is_get = 3; // Baú já resgatado
            } elseif ($total_mem_count >= $condition) {
                $is_get = 2; // Baú disponível para resgate
            }

            $baus[] = [
                "mem_count" => $condition,
                "bonus_amount" => $money,
                "sort" => $i,
                "state" => $is_get,
            ];
        }

        return [
            "status" => true, // indica sucesso
            "data" => [
                "list" => $baus, // Aninha $baus dentro da chave "list"
                "total_mem_count" => $total_mem_count, // Aqui está a modificação
                "deposit_limit" => 20,
                "valid_bet_amount" => 100,
                "title" => "Recomende amigos e ganhe bônus",
                "promo_content_json" => [
                    [
                        "title" => "222",
                        "content" => "33333",
                    ],
                ],
                "promo_rule_json" => [
                    [
                        "content" => "Somente o subordinado recem-registrado,os subordinados atendem aos requisitos de atividade e concluir Configure o metodo de retirada.",
                    ],
                    [
                        "content" => "Recomende amigos e ganhe bônus。Convidar diferentes números de amigos pode gerar bônus correspondentes. O número máximo de amigos convidados é 50.000. Quanto mais você convidar, maior será uma recompensa.",
                    ],
                    [
                        "content" => "Esta atividade é um presente extra da plataforma, você pode desfrutar de outras recompensas e comissões de agentes ao mesmo tempo e desfrutar de múltiplas alegrias.",
                    ],
                    [
                        "content" => "As recompensas incluem coleta manual em IOS, Android, H5 e PC e serão reabastecidas automaticamente durante a transição.",
                    ],
                    [
                        "content" => "O bónus atribuído neste evento (excluindo o prémio principal) requer 5 apostas válidas antes de poder ser levantado.As apostas estão limitadas a: slot machines (todos os jogos), pesca (todos os jogos) e cartas (todos os jogos).",
                    ],
                    [
                        "content" => "Esta atividade está limitada às operações normais dos correntistas. É proibido o leasing, a utilização de plug-ins, as apostas com contas diferentes, a escovagem mútua, a exploração de lacunas e outros meios técnicos. Caso contrário, as recompensas serão canceladas ou deduzidas, a conta será congelada ou mesmo colocada na lista negra.",
                    ],
                    [
                        "content" => "Para evitar diferenças na compreensão do texto, a plataforma reserva-se o direito de interpretação final deste evento.",
                    ],
                ],
            ],
        ];
    } else {
        return [
            "code" => 0, // indica falha
            "msg" => "Usuário sem efetuar login",
            "time" => time(),
            "data" => null,
        ];
    }
}

/*-----------------------------------------------------------------------------------------------*/

switch ($requestMethod) {
    case 'POST':
        /* Rotas POST */
        // Rota De Cadastro
        if ($requestURI === '/api/member/reg') {

            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            $rotaEncontrada = true;

            function filterUrl($url)
            {
                preg_match('/id=([^&#]*)/', $url, $matches);
                if (isset($matches[1])) {
                    $id = $matches[1];
                    $parts = parse_url($url);
                    $baseUrl = $parts['scheme'] . '://' . $parts['host'] . ($parts['path'] ?? '');
                    return $baseUrl . "?id=" . $id . "#/index";
                }
                return $url;
            }

            header("Content-Type: application/json");

            // Verificar se $data está definido
            if (!isset($data) || !is_array($data)) {
                echo json_encode(['status' => false, 'data' => 'Dados não fornecidos.']);
                exit;
            }

            // Validar entrada JSON
            if (empty($data['password']) || empty($data['username'])) {
                echo json_encode(['code' => 0, 'msg' => 'Dados insuficientes.']);
                exit;
            }

            // Sanitizar dados
            $password = htmlspecialchars($data['password']);
            $nome_user = htmlspecialchars($data['username']);
            $real_name = $nome_user;
            $url = $url_base ?? '';
            $afiliado = !empty($data['link_id']) ? htmlspecialchars($data['link_id']) : null;

            // Verificar duplicação de conta
            $query = "SELECT * FROM usuarios WHERE mobile = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $nome_user);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo json_encode(['status' => false, 'data' => 'Usuário já cadastrado.']);
                exit;
            }

            // Criar novo usuário
            $datadia = date('Y-m-d H:i:s');
            $token = md5($real_name . sha1(mt_rand()) . $datadia);
            $afinveted = 'AF' . substr(md5($real_name . sha1(mt_rand()) . $datadia), 0, 5);
            $senha = password_hash($password, PASSWORD_DEFAULT, ["cost" => 10]);

            $sql1 = $mysqli->prepare(
                "INSERT INTO usuarios (mobile, password, real_name, spassword, url, token, invite_code, invitation_code, data_cad) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $sql1->bind_param("sssssssss", $nome_user, $senha, $real_name, $senha, $url, $token, $afinveted, $afiliado, $datadia);

            if ($sql1->execute()) {
                if ($afiliado) {
                    $queryAfiliado = "SELECT pessoas_convidadas FROM usuarios WHERE invite_code = ?";
                    $stmtAfiliado = $mysqli->prepare($queryAfiliado);
                    $stmtAfiliado->bind_param("s", $afiliado);
                    $stmtAfiliado->execute();
                    $resultAfiliado = $stmtAfiliado->get_result();

                    if ($resultAfiliado->num_rows > 0) {
                        $afiliadoData = $resultAfiliado->fetch_assoc();
                        $pessoasConvidadas = $afiliadoData['pessoas_convidadas'] + 1;

                        $sqlUpdateAfiliado = $mysqli->prepare("UPDATE usuarios SET pessoas_convidadas = ? WHERE invite_code = ?");
                        $sqlUpdateAfiliado->bind_param("is", $pessoasConvidadas, $afiliado);
                        $sqlUpdateAfiliado->execute();
                    }
                }

                $qryusr = "SELECT id FROM usuarios WHERE token = ?";
                $stmtusr = $mysqli->prepare($qryusr);
                $stmtusr->bind_param("s", $token);
                $stmtusr->execute();
                $resusr = $stmtusr->get_result();
                $datres = $resusr->fetch_assoc();

                $user_id = $datres['id'];

                $config_qry = "SELECT niveisbau, qntsbaus, nvlbau, pessoasbau, valoresbau FROM config";
                $config_resp = $mysqli->query($config_qry);
                $config = $config_resp->fetch_assoc();

                $niveis_bau = explode(',', $config['niveisbau']);
                $valores_bau = explode(',', $config['valoresbau']);
                $pessoas_bau = $config['pessoasbau'];

                foreach ($valores_bau as $i => $valor) {
                    $mem_count = $i + 1;
                    $sqlibau = $mysqli->prepare("INSERT INTO afiliado_baus (id_user, valor, afiliados, mem_count) VALUES (?, ?, ?, ?)");
                    $sqlibau->bind_param("idii", $user_id, $valor, $pessoas_bau, $mem_count);
                    $sqlibau->execute();
                }
                header("id: f51:" . $token);
                setcookie('token_user', $token, time() + (86400 * 30), "/"); // Definir cookie por 30 dias
                $response = [
                    'status' => true,
                    'data' => '1000',
                ];
                echo json_encode($response);
                exit;
            } else {
                echo json_encode(['status' => false, 'data' => 'Não foi possível criar sua conta.']);
                exit;
            }
        }

        // Rota De Login
        if ($requestURI === '/api/member/login') {
            $rotaEncontrada = true; // Rota encontrada
            $jsonDataModificado = $data;
            $data_user = PHP_SEGURO($data['username']);
            $password = PHP_SEGURO($data['password']);
            $query = "SELECT * FROM usuarios WHERE mobile = '$data_user'";
            $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_array($result);
                $pass = $row['password'];
                $token = $row['token'];
                if (password_verify($password, $pass)) {
                    // Gera um ID único para o cabeçalho
                    $uniqueId = generateUniqueId();

                    // Define o cabeçalho 'id' com o token
                    header("id: f51:" . $token);
                    setcookie('token_user', $token, time() + (86400 * 30), "/"); // Definir cookie por 30 dias
                    $response = [
                        'status' => true, // Sucesso
                        'msg' => null,
                        'data' => '1000',
                    ];
                    echo json_encode($response);
                    exit;
                } else {
                    $response = [
                        "code" => 0, // Indica falha
                        "data" => '1007', // Mensagem de erro
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0, // Indica falha
                    "data" => '1006', // Mensagem de erro
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Platform/list
        if ($requestURI === '/api/member/platform/list') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    [
                        "id" => "26595015200105",
                        "game_type" => 1,
                        "name" => "EVO Cassino",
                    ],
                    [
                        "id" => "26595015200115",
                        "game_type" => 1,
                        "name" => "DB Cassino",
                    ],
                    [
                        "id" => "26595015200201",
                        "game_type" => 2,
                        "name" => "JL Pescaria",
                    ],
                    [
                        "id" => "26595015200203",
                        "game_type" => 2,
                        "name" => "JDB Pescaria",
                    ],
                    [
                        "id" => "26595015200206",
                        "game_type" => 2,
                        "name" => "SG Pescaria",
                    ],
                    [
                        "id" => "26595015200210",
                        "game_type" => 2,
                        "name" => "JDB Pescaria",
                    ],
                    [
                        "id" => "26595015200304",
                        "game_type" => 3,
                        "name" => "JDB Slots",
                    ],
                    [
                        "id" => "26595015200305",
                        "game_type" => 3,
                        "name" => "PG Slots",
                    ],
                    [
                        "id" => "26595015200306",
                        "game_type" => 3,
                        "name" => "JL Slots",
                    ],
                    [
                        "id" => "26595015200309",
                        "game_type" => 3,
                        "name" => "SG Slots",
                    ],
                    [
                        "id" => "26595015200310",
                        "game_type" => 3,
                        "name" => "PP Slots",
                    ],
                    [
                        "id" => "26595015200313",
                        "game_type" => 3,
                        "name" => "PG Slots",
                    ],
                    [
                        "id" => "26595015200314",
                        "game_type" => 3,
                        "name" => "ACEWIN Slots",
                    ],
                    [
                        "id" => "26595015200315",
                        "game_type" => 3,
                        "name" => "CG Slots",
                    ],
                    [
                        "id" => "26595015200316",
                        "game_type" => 3,
                        "name" => "CQ9 Slots",
                    ],
                    [
                        "id" => "26595015200317",
                        "game_type" => 3,
                        "name" => "FC Slots",
                    ],
                    [
                        "id" => "26595015200321",
                        "game_type" => 3,
                        "name" => "JDB Slots",
                    ],
                    [
                        "id" => "26595015200329",
                        "game_type" => 3,
                        "name" => "WG Slots",
                    ],
                    [
                        "id" => "26595015200407",
                        "game_type" => 4,
                        "name" => "DB Sport",
                    ],
                    [
                        "id" => "26595015200503",
                        "game_type" => 5,
                        "name" => "JL Cartas",
                    ],
                    [
                        "id" => "26595015200505",
                        "game_type" => 5,
                        "name" => "JDB Cartas",
                    ],
                    [
                        "id" => "26595015200511",
                        "game_type" => 5,
                        "name" => "JDB Cartas",
                    ],
                    [
                        "id" => "26595015200604",
                        "game_type" => 6,
                        "name" => "DB Esport",
                    ],
                    [
                        "id" => "26595015200702",
                        "game_type" => 7,
                        "name" => "DB Loteria",
                    ],
                    [
                        "id" => "26595015200902",
                        "game_type" => 9,
                        "name" => "JDB Blockchain",
                    ],
                    [
                        "id" => "26595015200905",
                        "game_type" => 9,
                        "name" => "JDB Blockchain",
                    ],
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Attbalance
        if ($requestURI === '/api/atualizar/saldo/china') {
            if (isset($_COOKIE['token_user']) and !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
                $resp = mysqli_query($mysqli, $qry);
                if (mysqli_num_rows($resp) > 0) {
                    $datares = mysqli_fetch_assoc($resp);
                    $obt_saldo = pegarSaldo($datares['mobile'], $datares['id']);
                    $response = array(
                        "code" => 1,
                        "msg" => "ok",
                        "saldo" => $obt_saldo,
                        "time" => time(),
                    );
                }
            } else {
                $response = array(
                    "code" => 0,
                    "msg" => "Usuário não logado",
                    "time" => time(),
                );
            }
            echo json_encode($response);
            exit;
        }
        // Rota Recall/balance
        if ($requestURI === '/api/member/recall/balance') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => "1000",
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota /api/member/update
        if ($requestURI === '/api/member/update') {
            //$jsonDataModificado = $data;
            //var_dump($data);
            if (isset($_COOKIE['token_user']) and !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
                $resp = mysqli_query($mysqli, $qry);
                if (mysqli_num_rows($resp) > 0) {
                    $datares = mysqli_fetch_assoc($resp);

                    //var_dump($data['id']);
                    if (isset($data['id'])) {
                        $sql = $mysqli->prepare("UPDATE usuarios SET avatar = ?,  bonus_status_define_avatar = '502' WHERE id = ?");
                        $sql->bind_param("si", $data['id'], $datares['id']);
                        $sql->execute();

                        $response = [
                            "status" => true,
                            "data" => "1000",
                            "msg" => null,
                        ];
                        echo json_encode($response);
                        exit;
                    }

                    $sql = $mysqli->prepare("UPDATE usuarios SET birth = ?, whatsapp = ?, facebook = ?, telegram = ?, twitter = ? WHERE id = ?");
                    $sql->bind_param("sssssi", $data['birth'], $data['whatsapp'], $data['facebook'], $data['telegram'], $data['twitter'], $datares['id']);
                    if ($sql->execute()) {
                        if (isset($data['birth'])) {
                            $sql = $mysqli->prepare("UPDATE usuarios SET bonus_status_birth = '502' WHERE id = ?");
                            $sql->bind_param("i", $datares['id']);
                            $sql->execute();
                        }
                        $response = [
                            "status" => true,
                            "data" => "1000",
                            "msg" => null,
                        ];
                        echo json_encode($response);
                        exit;
                    } else {
                        $response = [
                            "code" => 0,
                            "msg" => "Erro ao atualizar os dados.",
                        ];
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response = [
                        "code" => 0, // Indica falha
                        "msg" => "Usuário sem efetuar login", // Mensagem de erro
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Usuario ou senha incorretos",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }

        // Rota Member/Password/update
        if ($requestURI === '/api/member/password/update') {
            //$jsonDataModificado = $data;
            if (isset($_COOKIE['token_user']) and !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
                $resp = mysqli_query($mysqli, $qry);
                if (mysqli_num_rows($resp) > 0) {
                    $datares = mysqli_fetch_assoc($resp);
                    $sql = $mysqli->prepare("UPDATE usuarios SET senhaparasacar = ?,senha_saque = 1, bonus_status_pass_withdraw = '502' WHERE id = ?");
                    $sql->bind_param("si", $data['password'], $datares['id']);
                    if ($sql->execute()) {
                        $response = [
                            "status" => true,
                            "data" => '1000',
                            "msg" => null,
                        ];
                        echo json_encode($response);
                        exit;
                    } else {
                        $response = [
                            "code" => 0,
                            "msg" => "Erro ao realizar saque.",
                        ];
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response = [
                        "code" => 0, // Indica falha
                        "msg" => "Usuário sem efetuar login", // Mensagem de erro
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Usuario ou senha incorretos",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }


        // Rota Member/wpw/check
        if ($requestURI === '/api/member/wpw/check') {

            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $qry = "SELECT * FROM usuarios WHERE token=?";
                $stmt = $mysqli->prepare($qry);
                $stmt->bind_param("s", $_COOKIE['token_user']);
                $stmt->execute();
                $resp = $stmt->get_result();

                if ($resp->num_rows > 0) {
                    $datares = $resp->fetch_assoc();

                    // Verificação da senha de pagamento
                    if (isset($data['password']) && !empty($data['password'])) {
                        $senha_enviada = $data['password'];
                        $senha_armazenada = $datares['senhaparasacar'];

                        // Verificação direta de senha em texto simples
                        $senha_correta = ($senha_enviada === $senha_armazenada);

                        if ($senha_correta) {
                            $response = [
                                "status" => true,
                                "data" => "1000",
                                "msg" => null,
                            ];
                            echo json_encode($response);
                            exit;
                        } else {
                            $response = [
                                "status" => false,
                                "data" => "1251",
                                "msg" => null,
                            ];
                            echo json_encode($response);
                            exit;
                        }
                    } else {
                        // Se o campo password estiver vazio ou ausente, retorne o JSON solicitado
                        $response = [
                            "status" => true,
                            "data" => "1249",
                            "msg" => null,
                        ];
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response = [
                        "code" => 0, // Falha
                        "msg" => "Usuário sem efetuar login",
                        "time" => time(),
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Usuário ou senha incorretos",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Member/bankcard/insert
        if ($requestURI === '/api/member/bankcard/insert') {
            if (isset($_COOKIE['token_user']) and !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
                $resp = mysqli_query($mysqli, $qry);
                if (mysqli_num_rows($resp) > 0) {
                    $datares = mysqli_fetch_assoc($resp);

                    // Insere os dados na tabela 'payment_methods'
                    $sql = $mysqli->prepare("INSERT INTO metodos_pagamentos (user_id, realname, pix_id, flag, pix_account) VALUES (?, ?, ?, ?, ?)");
                    $sql->bind_param("issss", $datares['id'], $data['realname'], $data['bank_card'], $data['ty'], $data['content']);

                    if ($sql->execute()) {
                        $qry = "SELECT * FROM metodos_pagamentos WHERE user_id='" . $datares['id'] . "'";
                        $resp = mysqli_query($mysqli, $qry);
                        if (mysqli_num_rows($resp) === 1) {
                            $sql = $mysqli->prepare("UPDATE usuarios SET bonus_status_acc_withdraw = '502' WHERE id = ?");
                            $sql->bind_param("i", $datares['id']);
                            $sql->execute();
                        }

                        $response = [
                            "status" => true,
                            "data" => '1000',
                            "msg" => null,
                        ];
                        echo json_encode($response);
                        exit;
                    } else {
                        $response = [
                            "code" => 0,
                            "msg" => "Erro ao inserir conta de pagamento.",
                        ];
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response = [
                        "code" => 0,
                        "msg" => "Usuário sem efetuar login",
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Usuario ou senha incorretos",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Finance/Withdraw
        if ($requestURI === '/api/finance/withdraw') {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            if (isset($_COOKIE['token_user']) and !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $qry = "SELECT * FROM usuarios WHERE token=?";
                $stmt = $mysqli->prepare($qry);
                $stmt->bind_param("s", $_COOKIE['token_user']);
                $stmt->execute();
                $resp = $stmt->get_result();

                if ($resp->num_rows > 0) {
                    $datares = $resp->fetch_assoc();

                    // Verificação da senha de pagamento
                    if (isset($data['password']) && !empty($data['password'])) {
                        $senha_enviada = $data['password'];
                        $senha_armazenada = $datares['senhaparasacar'];

                        // Supondo que a senha esteja armazenada como um hash
                        // Se a senha estiver armazenada em texto simples, remova ou comente a linha abaixo
                        //$senha_correta = password_verify($senha_enviada, $senha_armazenada);

                        // Verificação direta de senha em texto simples
                        $senha_correta = ($senha_enviada === $senha_armazenada);

                        if ($senha_correta) {
                            $data = date('Y-m-d'); // Data no formato Y-m-d

                            if ($datares['saldo'] <= 0) {
                                $response = [
                                    "code" => 0,
                                    "data" => "Saldo insuficiente.",
                                    "time" => time(),
                                ];
                                echo json_encode($response);
                                exit;
                            }
                            // Verificação do limite diário de saque
                            $qry = "SELECT COUNT(*) as saques_hoje, SUM(valor) as total_saque_hoje FROM solicitacao_saques WHERE id_user = ? AND DATE(data_cad) = ?";
                            $stmt = $mysqli->prepare($qry);
                            $stmt->bind_param("is", $datares['id'], $data);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            $row = $res->fetch_assoc();

                            $saques_hoje = $row['saques_hoje'];
                            $total_saque_hoje = $row['total_saque_hoje'] ? $row['total_saque_hoje'] : 0;

                            parse_str(file_get_contents("php://input"), $data);
                            $valor_saque = isset($data['amount']) ? $data['amount'] : 0;

                            if ($saques_hoje >= $dataconfig['limite_saque'] || ($saques_hoje == 1 && $total_saque_hoje + $valor_saque > 500)) {
                                $response = [
                                    "code" => 0,
                                    "msg" => "Limite de saques diários atingido, tente amanhã novamente.",
                                    "time" => time(),
                                ];
                                echo json_encode($response);
                                exit;
                                // Verificação do limite diário de saque
                                //$qry = "SELECT COUNT(*) as saques_hoje, SUM(valor) as total_saque_hoje FROM solicitacao_saques WHERE id_user = ? AND DATE(data_cad) = ?";
                                //$stmt = $mysqli->prepare($qry);
                                //$stmt->bind_param("is", $datares['id'], $data);
                                //$stmt->execute();
                                //$res = $stmt->get_result();
                                //$row = $res->fetch_assoc();

                                //$saques_hoje = $row['saques_hoje'];
                                //$total_saque_hoje = $row['total_saque_hoje'] ? (float) $row['total_saque_hoje'] : 0;
                                //var_dump($total_saque_hoje);
                                //parse_str(file_get_contents("php://input"), $data);
                                //$valor_saque = isset($data['amount']) ? $data['amount'] : 0;

                                //if ($valor_saque < $datares['saldo']) {
                                //$response = [
                                //"code" => 0,
                                //"data" => "Saldo indisponível.",
                                //"time" => time(),
                                //  ];
                                //echo json_encode($response);
                                //echo json_encode($response);
//
                                // exit;
                                //}

                                // if ($saques_hoje >= $dataconfig['limite_saque'] || ($saques_hoje == 1 && $total_saque_hoje + $valor_saque > 500)) {
                                // $response = [
                                //"code" => 0,
                                //"data" => "Limite de saques diários atingido, tente amanhã novamente.",
                                // "time" => time(),
                                // ];
                                //echo json_encode($response);
                                //exit;
                            } else {
                                // Calculando o total de depósitos
                                $qry = "SELECT SUM(valor) as total_depositos FROM transacoes WHERE usuario=? AND tipo='deposito' AND status='pago'";
                                $stmt = $mysqli->prepare($qry);
                                $stmt->bind_param("i", $datares['id']);
                                $stmt->execute();
                                $resultado = $stmt->get_result();
                                $row = $resultado->fetch_assoc();
                                $total_depositos = ($row['total_depositos'] > 0) ? $row['total_depositos'] : 0;

                                if ($total_depositos <= 0) {
                                    $response = [
                                        "code" => 0,
                                        "data" => "Faça o primeiro depósito, jogue e volte aqui para sacar.",
                                        "time" => time(),
                                    ];
                                    echo json_encode($response);
                                    exit;
                                }


                                // Calculando o total de depósitos
                                $qryap = "SELECT SUM(bet_money) as total_apostas FROM historico_play WHERE id_user=?";
                                $stmtap = $mysqli->prepare($qryap);
                                $stmtap->bind_param("i", $datares['id']);
                                $stmtap->execute();
                                $resultadoap = $stmtap->get_result();
                                $rowap = $resultadoap->fetch_assoc();
                                // Conversão e formatação do total de apostas
                                $total_apostas = (float) $rowap['total_apostas'];
                                $total_apostas_formatado = 'R$ ' . number_format($total_apostas, 2, ',', '.');

                                $rollover_tarefas = rollover_tarefas($datares['mobile']);
                                // Cálculo do valor que falta apostar
                                $rollover_necessario = ($total_depositos + $rollover_tarefas) * $dataconfig['rollover'];
                                $falta_apostar = ($rollover_necessario + $rollover_tarefas) - $total_apostas;
                                $falta_apostar_formatado = 'R$ ' . number_format($falta_apostar, 2, ',', '.');
                                //var_dump($valor_saque < $rollover_necessario, $valor_saque, $rollover_necessario);
                                //exit;
                                // Verificando se o valor do saque é permitido
                                if ($valor_saque < $rollover_necessario) {
                                    $response = [
                                        "code" => 0,
                                        "data" => "Você apostou $total_apostas_formatado. Necessário apostar mais $falta_apostar_formatado para realizar o saque.",
                                        "time" => time(),
                                    ];
                                    echo json_encode($response);
                                    exit;
                                } else {
                                    //var_dump($valor_saque, $datares['saldo'], $dataconfig['minsaque']);

                                    if ($valor_saque <= $datares['saldo'] && $valor_saque >= $dataconfig['minsaque']) {
                                        $datadia = date('Y-m-d H:i:s');
                                        $dataX = date('Y-m-d');
                                        $data_hora = date('H:i:s');
                                        $tokenSaque = md5($datares['mobile'] . sha1(mt_rand()) . $datadia);

                                        $RANDOMSAQUE = md5($tokenSaque);
                                        $sql12 = $mysqli->prepare("INSERT INTO solicitacao_saques (id_user, valor, tipo, pix, telefone, data_cad, data_hora, transacao_id) VALUES (?,?,?,?,?,?,?,?)");
                                        $sql12->bind_param("ssssssss", $datares['id'], $valor_saque, $data['bank_id'], $data['bank_id'], $data['flag'], $dataX, $data_hora, $RANDOMSAQUE);

                                        $novosaldo = $datares['saldo'] - $valor_saque;
                                        if ($novosaldo < 0) {
                                            $novosaldo = 0;
                                        }
                                        $sql = $mysqli->prepare("UPDATE usuarios SET saldo = ? WHERE id = ?");
                                        $sql->bind_param("si", $novosaldo, $datares['id']);

                                        if ($sql->execute() and $sql12->execute()) {
                                            if ($valor_saque <= $dataconfig['saque_automatico']) {
                                                $api_url = $url_base . "ad-min/services-gateway/payment_auto.php";

                                                // $chavepix2 = localizarchavepix($data['bank_id']);

                                                $api_data = array(
                                                    'chavepix' => $data['bank_id'],
                                                    'valor' => $valor_saque,
                                                    'id' => md5(token_id_transacao()),
                                                );

                                                $curl = curl_init($api_url);
                                                curl_setopt($curl, CURLOPT_POST, true);
                                                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($api_data));
                                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                                                $api_response = curl_exec($curl);

                                                curl_close($curl);

                                                if ($api_response === "Pagamento realizado com sucesso") {
                                                    $qry = "UPDATE solicitacao_saques SET status = '1' WHERE transacao_id = ?";
                                                    $stmt = $mysqli->prepare($qry);
                                                    $stmt->bind_param("s", $RANDOMSAQUE);
                                                    $stmt->execute();
                                                }

                                                //var_dump($api_url);
                                            }

                                            $response = [
                                                "status" => true,
                                                "data" => '10000',
                                                "time" => time(),
                                            ];
                                            echo json_encode($response);
                                            exit;
                                        } else {
                                            $response = [
                                                "code" => 0, // Falha
                                                "data" => "Erro ao realizar saque.",
                                                "time" => time(),
                                            ];
                                            echo json_encode($response);
                                            exit;
                                        }
                                    } else {
                                        $response = [
                                            "code" => 0,
                                            "data" => "Valor do saque fora dos limites permitidos.",
                                            "time" => time(),
                                        ];
                                        echo json_encode($response);
                                        exit;
                                    }
                                }
                            }
                        } else {
                            $response = [
                                "status" => false,
                                "data" => "1026",
                                "msg" => null,
                            ];
                            echo json_encode($response);
                            exit;
                        }
                    } else {
                        $response = [
                            "status" => false,
                            "data" => "1251",
                            "msg" => null,
                        ];
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response = [
                        "code" => 0, // Falha
                        "data" => "Usuário sem efetuar login",
                        "time" => time(),
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "data" => "Usuario ou senha incorretos",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Finance/Third/Deposit
        if ($requestURI === '/api/finance/third/deposit') {
            if (isset($_COOKIE['token_user']) and !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
                $resp = mysqli_query($mysqli, $qry);
                if (mysqli_num_rows($resp) > 0) {
                    $datares = mysqli_fetch_assoc($resp);

                    //se gerado qr code retorna o pixcode
                    $return_data_pix = criarQrCode($data['amount'], $datares['real_name'] ?? $datares['username'], $datares['id']);

                    if (!empty($return_data_pix) and $return_data_pix != null) {
                        $response = [
                            "status" => true,
                            "data" => [
                                "url" => $url_api_gatewayPix . '?paymentCodeBase64=' . $return_data_pix['qrcode'] . '&paymentCode=' . $return_data_pix['code'] . '&valorPix=' . $return_data_pix['amount'],
                            ],
                        ];
                    } else {
                        $response = [
                            "code" => 0,
                            "msg" => "Erro PixApi",
                            "time" => time(),
                        ];
                    }
                } else {
                    $response = [
                        "code" => 0,
                        "msg" => "Usuário não logado",
                        "time" => time(),
                    ];
                }
            } else {
                $response = [
                    "code" => 0,
                    "msg" => "Usuário não logado",
                    "time" => time(),
                ];
            }

            echo json_encode($response);
            exit;
        }
        // Rota Member/Agent/Sub/Member
        if ($requestURI === '/api/member/agent/sub/member') {
            global $pdo;
            // Verifica se o cookie 'token_user' está definido e não está vazio
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $token = mysqli_real_escape_string($mysqli, $_COOKIE['token_user']);

                // Busca o usuário principal com base no token
                $qry = "SELECT * FROM usuarios WHERE token='$token'";
                $resp = mysqli_query($mysqli, $qry);

                if (mysqli_num_rows($resp) > 0) {
                    $datres = mysqli_fetch_assoc($resp);
                    $inviteCode = $datres['invite_code'];

                    // Busca os usuários convidados pelo usuário principal
                    $invitedUsersQuery = "SELECT id FROM usuarios WHERE invitation_code = '$inviteCode'";
                    $invitedUsersResult = mysqli_query($mysqli, $invitedUsersQuery);

                    $invitedUserIds = [];
                    while ($row = mysqli_fetch_assoc($invitedUsersResult)) {
                        $invitedUserIds[] = $row['id'];
                    }

                    $tr = "SELECT id FROM usuarios WHERE invitation_code = '$inviteCode'";
                    $invitedUsersResult = mysqli_query($mysqli, $invitedUsersQuery);

                    $invitedUserIds = [];
                    while ($row = mysqli_fetch_assoc($invitedUsersResult)) {
                        $invitedUserIds[] = $row['id'];
                    }

                    // Consulta para obter os usuários que entraram pelo invite_code do usuário autenticado
                    $qry_subs = "SELECT * FROM usuarios WHERE invitation_code='" . $inviteCode . "'";
                    $resp_subs = mysqli_query($mysqli, $qry_subs);

                    $sub_users = [];
                    while ($sub = mysqli_fetch_assoc($resp_subs)) {
                        $subId = $sub['id'];

                        // INICIO DA SOMA BET DO SUBAFILIADO
                        $queryhistorico = "SELECT SUM(COALESCE(bet_money, 0)) AS total_bet FROM historico_play WHERE id_user = :id_user";
                        $stmthistorico = $pdo->prepare($queryhistorico);
                        $stmthistorico->bindParam(':id_user', $subId, PDO::PARAM_INT);
                        $stmthistorico->execute();
                        $historico = $stmthistorico->fetch(PDO::FETCH_ASSOC);

                        $total_bet = $historico['total_bet'] ?? 0; // Default to 0 if NULL
                        // FIM DA SOMA BET DO SUBAFILIADO


                        // INICIO DA BUSCA DO PRIMEIRO DEPOSITO
                        $queryprimeirodeposito = "SELECT valor 
                          FROM transacoes 
                          WHERE usuario = :usuario AND tipo = 'deposito' AND status = 'pago' 
                          ORDER BY id ASC 
                          LIMIT 1";
                        $stmtprimeirodeposito = $pdo->prepare($queryprimeirodeposito);
                        $stmtprimeirodeposito->bindParam(':usuario', $subId, PDO::PARAM_INT);
                        $stmtprimeirodeposito->execute();
                        $primeirodeposito = $stmtprimeirodeposito->fetch(PDO::FETCH_ASSOC);
                        $valor_primeirodeposito = $primeirodeposito['valor'] ?? 0;
                        // FIM DA BUSCA DO PRIMEIRO DEPOSITO

                        $sub_users[] = [
                            'username' => $sub['id'],
                            'level' => 0,
                            'money' => $sub['saldo'],
                            'bet_amount' => $total_bet,
                            'deposit_amount' => $valor_primeirodeposito,
                            'is_recharge' => 0,
                            'is_good' => 0,
                            'created_at' => $sub['data_cad'],
                        ];
                    }

                    if (count($invitedUserIds) > 0) {
                        $invitedUserIdsStr = implode(',', $invitedUserIds);

                        // Exemplo: Quantidade de primeiros depósitos pagos (um por cada afiliado)
                        $firstDepositQuery = "
                            SELECT COUNT(DISTINCT usuario) as firstDepositCount
                            FROM transacoes
                            WHERE usuario IN ($invitedUserIdsStr)
                            AND status = 'pago'
                        ";
                        $firstDepositResult = mysqli_query($mysqli, $firstDepositQuery);
                        $firstDepositCount = mysqli_fetch_assoc($firstDepositResult)['firstDepositCount'];

                        // Exemplo: Total de depósitos pagos e soma dos valores
                        $depositQuery = "
                            SELECT COUNT(*) as depositCount, SUM(valor) as totalDeposits
                            FROM transacoes
                            WHERE usuario IN ($invitedUserIdsStr)
                            AND status = 'pago'
                        ";
                        $depositResult = mysqli_query($mysqli, $depositQuery);
                        $depositData = mysqli_fetch_assoc($depositResult);

                        // Verificar se os dados foram retornados corretamente
                        $depositCount = isset($depositData['depositCount']) ? $depositData['depositCount'] : 0;
                        $totalDeposits = isset($depositData['totalDeposits']) ? $depositData['totalDeposits'] : 0;
                    } else {
                        // Se não houver usuários convidados, os valores serão zero
                        $firstDepositCount = 0;
                        $depositCount = 0;
                        $totalDeposits = 0.00;
                    }

                    // Construindo a resposta com os valores zeros como solicitado
                    $userData = array(
                        "status" => true,
                        "data" => [
                            "t" => 0,
                            "d" => $sub_users,
                            "extra" => [
                                "total_deposit_amount" => $totalDeposits,
                                "total_first_deposit_num" => $firstDepositCount,
                                "other_deposit_amount" => 0,
                                "other_first_deposit_num" => 0,
                                "direct_deposit_amount" => 0,
                                "direct_first_deposit_num" => 0,
                            ],
                        ],
                        "msg" => null,
                    );

                    // Converte o array associativo para JSON
                    echo json_encode($userData);
                } else {
                    $response = [
                        "status" => false,
                        "data" => null,
                        "msg" => "Usuário sem efetuar login",
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "status" => false,
                    "data" => null,
                    "msg" => "Token não encontrado ou inválido",
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Member/Agency/Alldata
        if ($requestURI === '/api/member/agency/alldata') {
            // Verifica se o cookie 'token_user' está definido e não está vazio
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $token = mysqli_real_escape_string($mysqli, $_COOKIE['token_user']);

                // Busca o usuário principal com base no token
                $qry = "SELECT * FROM usuarios WHERE token='$token'";
                $resp = mysqli_query($mysqli, $qry);

                if (mysqli_num_rows($resp) > 0) {
                    $datres = mysqli_fetch_assoc($resp);
                    $inviteCode = $datres['invite_code'];

                    // Busca os usuários convidados pelo usuário principal
                    $invitedUsersQuery = "SELECT id FROM usuarios WHERE invitation_code = '$inviteCode'";
                    $invitedUsersResult = mysqli_query($mysqli, $invitedUsersQuery);

                    $invitedUserIds = [];
                    while ($row = mysqli_fetch_assoc($invitedUsersResult)) {
                        $invitedUserIds[] = $row['id'];
                    }

                    if (count($invitedUserIds) > 0) {
                        $invitedUserIdsStr = implode(',', $invitedUserIds);

                        // Exemplo: Quantidade de primeiros depósitos pagos (um por cada afiliado)
                        $firstDepositQuery = "
                            SELECT COUNT(DISTINCT usuario) as firstDepositCount
                            FROM transacoes
                            WHERE usuario IN ($invitedUserIdsStr)
                            AND status = 'pago'
                        ";
                        $firstDepositResult = mysqli_query($mysqli, $firstDepositQuery);
                        $firstDepositCount = mysqli_fetch_assoc($firstDepositResult)['firstDepositCount'];

                        // Exemplo: Total de depósitos pagos e soma dos valores
                        $depositQuery = "
                            SELECT COUNT(*) as depositCount, SUM(valor) as totalDeposits
                            FROM transacoes
                            WHERE usuario IN ($invitedUserIdsStr)
                            AND status = 'pago'
                        ";
                        $depositResult = mysqli_query($mysqli, $depositQuery);
                        $depositData = mysqli_fetch_assoc($depositResult);

                        // Verificar se os dados foram retornados corretamente
                        $depositCount = isset($depositData['depositCount']) ? $depositData['depositCount'] : 0;
                        $totalDeposits = isset($depositData['totalDeposits']) ? $depositData['totalDeposits'] : 0;

                        // Aqui é onde você deve ajustar os valores conforme o seu cálculo necessário
                        $child_lvl1_num = $firstDepositCount; // Por exemplo
                        $child_lvl1_validbet = $totalDeposits; // Por exemplo

                    } else {
                        // Se não houver usuários convidados, os valores serão zero
                        $firstDepositCount = 0;
                        $depositCount = 0;
                        $totalDeposits = 0.00;

                        // Valores baseados na ausência de convidados
                        $child_lvl1_num = 0;
                        $child_lvl1_validbet = 0;
                    }

                    $qryaflcnf = "SELECT * FROM afiliados_config WHERE id = 1";
                    $respaflcnf = mysqli_query($mysqli, $qryaflcnf);
                    $afiliados_config = mysqli_fetch_assoc($respaflcnf);
                    $comissao = ((int) $afiliados_config['cpaLvl1'] * $totalDeposits) / 100;

                    // Construindo a resposta com os valores calculados
                    $userData = array(
                        "status" => true,
                        "data" => array(
                            "child_num" => count($invitedUserIds), // Número de usuários convidados
                            "child_lvl1_num" => count($invitedUserIds), // Número de afiliados de primeiro nível
                            "child_other_num" => 0, // Outros níveis, ajuste se necessário
                            "child_validbet" => $totalDeposits, // Soma dos valores de depósito
                            "child_lvl1_validbet" => $child_lvl1_validbet, // Total de depósitos do nível 1
                            "child_other_validbet" => 0, // Ajuste conforme necessário para outros níveis
                            "rebate_all" => $comissao, // Ajuste conforme necessário para rebate total
                            "rebate_lvl1" => $comissao, // Ajuste conforme necessário para rebate do nível 1
                            "rebate_other" => 0, // Ajuste conforme necessário para rebate de outros níveis
                        ),
                        "msg" => null,
                    );

                    // Converte o array associativo para JSON
                    echo json_encode($userData);
                } else {
                    $response = [
                        "status" => false,
                        "data" => null,
                        "msg" => "Usuário sem efetuar login",
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "status" => false,
                    "data" => null,
                    "msg" => "Token não encontrado ou inválido",
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Member/Agency/Mydata
        if ($requestURI === '/api/member/agency/mydata') {
            // Verifica se o cookie 'token_user' está definido e não está vazio
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $token = mysqli_real_escape_string($mysqli, $_COOKIE['token_user']);

                // Busca o usuário principal com base no token
                $qry = "SELECT * FROM usuarios WHERE token='$token'";
                $resp = mysqli_query($mysqli, $qry);

                if (mysqli_num_rows($resp) > 0) {
                    $datres = mysqli_fetch_assoc($resp);
                    $inviteCode = $datres['invite_code'];

                    // Busca os usuários convidados pelo usuário principal
                    $invitedUsersQuery = "SELECT id FROM usuarios WHERE invitation_code = '$inviteCode'";
                    $invitedUsersResult = mysqli_query($mysqli, $invitedUsersQuery);

                    $invitedUserIds = [];
                    while ($row = mysqli_fetch_assoc($invitedUsersResult)) {
                        $invitedUserIds[] = $row['id'];
                    }

                    if (count($invitedUserIds) > 0) {
                        $invitedUserIdsStr = implode(',', $invitedUserIds);

                        // Exemplo: Quantidade de primeiros depósitos pagos (um por cada afiliado)
                        $firstDepositQuery = "
                            SELECT COUNT(DISTINCT usuario) as firstDepositCount
                            FROM transacoes
                            WHERE usuario IN ($invitedUserIdsStr)
                            AND status = 'pago'
                        ";
                        $firstDepositResult = mysqli_query($mysqli, $firstDepositQuery);
                        $firstDepositCount = mysqli_fetch_assoc($firstDepositResult)['firstDepositCount'];

                        // Exemplo: Total de depósitos pagos e soma dos valores
                        $depositQuery = "
                            SELECT COUNT(*) as depositCount, SUM(valor) as totalDeposits
                            FROM transacoes
                            WHERE usuario IN ($invitedUserIdsStr)
                            AND status = 'pago'
                        ";
                        $depositResult = mysqli_query($mysqli, $depositQuery);
                        $depositData = mysqli_fetch_assoc($depositResult);

                        // Verificar se os dados foram retornados corretamente
                        $depositCount = isset($depositData['depositCount']) ? $depositData['depositCount'] : 0;
                        $totalDeposits = isset($depositData['totalDeposits']) ? $depositData['totalDeposits'] : 0;
                    } else {
                        // Se não houver usuários convidados, os valores serão zero
                        $firstDepositCount = 0;
                        $depositCount = 0;
                        $totalDeposits = 0.00;
                    }

                    $qryaflcnf = "SELECT * FROM afiliados_config WHERE id = 1";
                    $respaflcnf = mysqli_query($mysqli, $qryaflcnf);
                    $afiliados_config = mysqli_fetch_assoc($respaflcnf);
                    $comissao = ((int) $afiliados_config['cpaLvl1'] * $totalDeposits) / 100;


                    // Construindo a resposta com os valores zeros como solicitado
                    $userData = array(
                        "status" => true,
                        "data" => array(
                            "add_lvl1_num" => count($invitedUserIds),
                            "first_deposit_count" => $firstDepositCount,
                            "deposit_mem_count" => $depositCount,
                            "deposit_amount" => $totalDeposits,
                            "valid_bet_amount" => 0,
                            "cg_rebate" => $comissao,
                        ),
                        "msg" => null,
                    );

                    // Converte o array associativo para JSON
                    echo json_encode($userData);
                } else {
                    $response = [
                        "status" => false,
                        "data" => null,
                        "msg" => "Usuário sem efetuar login",
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "status" => false,
                    "data" => null,
                    "msg" => "Token não encontrado ou inválido",
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Member/Rebate/Agency/Brief
        if ($requestURI === '/api/member/rebate/agency/brief') {
            // Verifica se o cookie 'token_user' está definido e não está vazio
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $token = mysqli_real_escape_string($mysqli, $_COOKIE['token_user']);

                // Busca o usuário principal com base no token
                $qry = "SELECT * FROM usuarios WHERE token='$token'";
                $resp = mysqli_query($mysqli, $qry);

                if (mysqli_num_rows($resp) > 0) {
                    $datres = mysqli_fetch_assoc($resp);
                    $inviteCode = $datres['invite_code'];

                    // Busca os usuários convidados pelo usuário principal
                    $invitedUsersQuery = "SELECT id FROM usuarios WHERE invitation_code = '$inviteCode'";
                    $invitedUsersResult = mysqli_query($mysqli, $invitedUsersQuery);

                    $invitedUserIds = [];
                    while ($row = mysqli_fetch_assoc($invitedUsersResult)) {
                        $invitedUserIds[] = $row['id'];
                    }

                    if (count($invitedUserIds) > 0) {
                        $invitedUserIdsStr = implode(',', $invitedUserIds);

                        // Exemplo: Quantidade de primeiros depósitos pagos (um por cada afiliado)
                        $firstDepositQuery = "
                            SELECT COUNT(DISTINCT usuario) as firstDepositCount
                            FROM transacoes
                            WHERE usuario IN ($invitedUserIdsStr)
                            AND status = 'pago'
                        ";
                        $firstDepositResult = mysqli_query($mysqli, $firstDepositQuery);
                        $firstDepositCount = mysqli_fetch_assoc($firstDepositResult)['firstDepositCount'];

                        // Exemplo: Total de depósitos pagos e soma dos valores
                        $depositQuery = "
                            SELECT COUNT(*) as depositCount, SUM(valor) as totalDeposits
                            FROM transacoes
                            WHERE usuario IN ($invitedUserIdsStr)
                            AND status = 'pago'
                        ";
                        $depositResult = mysqli_query($mysqli, $depositQuery);
                        $depositData = mysqli_fetch_assoc($depositResult);

                        // Verificar se os dados foram retornados corretamente
                        $depositCount = isset($depositData['depositCount']) ? $depositData['depositCount'] : 0;
                        $totalDeposits = isset($depositData['totalDeposits']) ? $depositData['totalDeposits'] : 0;
                    } else {
                        // Se não houver usuários convidados, os valores serão zero
                        $firstDepositCount = 0;
                        $depositCount = 0;
                        $totalDeposits = 0.00;
                    }

                    // Construindo a resposta com os valores zeros como solicitado
                    $userData = array(
                        "status" => true,
                        "data" => [
                            "parent_uid" => 0,
                            "paid_amount" => 0,
                            "total_bet_amount" => 0,
                            "total_amount" => 0,
                            "last_paid_amount" => 0,
                            "last_total_amount" => 0,
                            "total_num" => 0,
                            "child1_total_num" => isset($datres['pessoas_convidadas']) ? (int) $datres['pessoas_convidadas'] : 0,
                            "child1_total_amount" => 0,
                            "other_total_num" => 0,
                            "other_total_amount" => 0,
                            "net_amount" => 0,
                            "valid_bet_amount" => 0,
                            "bet_num" => 0,
                        ],
                        "msg" => null,
                    );

                    // Converte o array associativo para JSON
                    echo json_encode($userData);
                } else {
                    $response = [
                        "status" => false,
                        "data" => null,
                        "msg" => "Usuário sem efetuar login",
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "status" => false,
                    "data" => null,
                    "msg" => "Token não encontrado ou inválido",
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Member/History/Save
        if ($requestURI === '/api/member/history/save') {
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
                $resp = mysqli_query($mysqli, $qry);

                if (mysqli_num_rows($resp) > 0) {
                    $datares = mysqli_fetch_assoc($resp);
                    $userId = $datares['id'];

                    // Consultar o histórico atual do usuário
                    $sql = "SELECT historico FROM usuarios WHERE id = ?";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        $historicoAtual = $result->fetch_assoc()['historico'];

                        // Verifica se já existe histórico
                        if ($historicoAtual) {
                            // Se já existe, adiciona o novo código com vírgula
                            $novoHistorico = $historicoAtual . ',' . $data['code'];
                        } else {
                            // Se não existe, apenas usa o novo código
                            $novoHistorico = $data['code'];
                        }
                    } else {
                        // Se não houver registro no histórico, apenas adiciona o novo código
                        $novoHistorico = $data['code'];
                    }

                    // Atualizar o histórico do usuário
                    $sqlUpdate = $mysqli->prepare("UPDATE usuarios SET historico = ? WHERE id = ?");
                    $sqlUpdate->bind_param("si", $novoHistorico, $userId);
                    if ($sqlUpdate->execute()) {
                        $response = [
                            "status" => true,
                            "data" => '1000',
                            "msg" => null,
                        ];
                        echo json_encode($response);
                        exit;
                    } else {
                        $response = [
                            "code" => 0,
                            "msg" => "Erro ao atualizar histórico.",
                        ];
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response = [
                        "code" => 0, // Indica falha
                        "msg" => "Usuário sem efetuar login", // Mensagem de erro
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Usuário ou senha incorretos",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Member/Favorites/Save
        if ($requestURI === '/api/member/favorites/save') {
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
                $resp = mysqli_query($mysqli, $qry);

                if (mysqli_num_rows($resp) > 0) {
                    $datares = mysqli_fetch_assoc($resp);
                    $userId = $datares['id'];

                    // Consultar o histórico atual do usuário
                    $sql = "SELECT favoritos FROM usuarios WHERE id = ?";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        $historicoAtual = $result->fetch_assoc()['favoritos'];

                        // Verifica se já existe histórico
                        if ($historicoAtual) {
                            // Se já existe, adiciona o novo código com vírgula
                            $novoHistorico = $historicoAtual . ',' . $data['code'];
                        } else {
                            // Se não existe, apenas usa o novo código
                            $novoHistorico = $data['code'];
                        }
                    } else {
                        // Se não houver registro no histórico, apenas adiciona o novo código
                        $novoHistorico = $data['code'];
                    }

                    // Atualizar o histórico do usuário
                    $sqlUpdate = $mysqli->prepare("UPDATE usuarios SET favoritos = ? WHERE id = ?");
                    $sqlUpdate->bind_param("si", $novoHistorico, $userId);
                    if ($sqlUpdate->execute()) {
                        $response = [
                            "status" => true,
                            "data" => '1000',
                            "msg" => null,
                        ];
                        echo json_encode($response);
                        exit;
                    } else {
                        $response = [
                            "code" => 0,
                            "msg" => "Erro ao atualizar histórico.",
                        ];
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response = [
                        "code" => 0, // Indica falha
                        "msg" => "Usuário sem efetuar login", // Mensagem de erro
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Usuário ou senha incorretos",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Promo/invite/list
        if ($requestURI === '/api/promo/invite/list') {

            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $token = $_COOKIE['token_user'];

                // Verificar usuário
                $qryusr = "SELECT id, pessoas_convidadas FROM usuarios WHERE token = ?";
                $stmtusr = $mysqli->prepare($qryusr);
                $stmtusr->bind_param("s", $token);
                $stmtusr->execute();
                $datausr = $stmtusr->get_result();
                $user = $datausr->fetch_assoc();

                if (!$user) {
                    echo json_encode(["status" => false, "message" => "Usuário não encontrado."]);
                    exit;
                }

                liberarBau($user['id']);

                // Consultar baús
                $qryfbau = "SELECT * FROM afiliado_baus WHERE id_user = ? ORDER BY id ASC";
                $stmtfbau = $mysqli->prepare($qryfbau);
                $stmtfbau->bind_param("i", $user['id']);
                $stmtfbau->execute();
                $datafbau = $stmtfbau->get_result();

                if ($datafbau->num_rows === 0) {
                    echo json_encode(["status" => false, "message" => "Nenhum baú encontrado."]);
                    exit;
                }

                $status = [
                    "gerado" => 1,
                    "disponivel" => 2,
                    "aberto" => 3
                ];

                $baus = [];
                foreach ($datafbau->fetch_all(MYSQLI_ASSOC) as $key => $bau) {
                    $baus[] = [
                        "mem_count" => $key + 1,
                        "bonus_amount" => $bau['valor'],
                        "sort" => $key,
                        "state" => $status[$bau['status']] ?? null,
                    ];
                }


                $qrycnf = "SELECT * FROM config WHERE id = 1";
                $confd = mysqli_query($mysqli, $qrycnf);
                $config = mysqli_fetch_assoc($confd);

                $response = [
                    "status" => true,
                    "data" => [
                        "list" => $baus,
                        "total_mem_count" => $user['pessoas_convidadas'] ?? 0,
                        "deposit_limit" => $config['depositosbau'],
                        "valid_bet_amount" => $config['apostasbau'],
                        "title" => "Recomende amigos e ganhe bônus",
                        "promo_content_json" => [
                            [
                                "title" => "222",
                                "content" => "33333",
                            ]
                        ],
                        "promo_rule_json" => [
                            [
                                "content" => "Somente o subordinado recem-registrado,os subordinados atendem aos requisitos de atividade e concluir Configure o metodo de retirada.",
                            ],
                            [
                                "content" => "Recomende amigos e ganhe bônus。Convidar diferentes números de amigos pode gerar bônus correspondentes. O número máximo de amigos convidados é 50.000. Quanto mais você convidar, maior será uma recompensa.",
                            ],
                            [
                                "content" => "Esta atividade é um presente extra da plataforma, você pode desfrutar de outras recompensas e comissões de agentes ao mesmo tempo e desfrutar de múltiplas alegrias.",
                            ],
                            [
                                "content" => "As recompensas incluem coleta manual em IOS, Android, H5 e PC e serão reabastecidas automaticamente durante a transição.",
                            ],
                            [
                                "content" => "O bónus atribuído neste evento (excluindo o prémio principal) requer 5 apostas válidas antes de poder ser levantado.As apostas estão limitadas a: slot machines (todos os jogos), pesca (todos os jogos) e cartas (todos os jogos).",
                            ],
                            [
                                "content" => "Esta atividade está limitada às operações normais dos correntistas. É proibido o leasing, a utilização de plug-ins, as apostas com contas diferentes, a escovagem mútua, a exploração de lacunas e outros meios técnicos. Caso contrário, as recompensas serão canceladas ou deduzidas, a conta será congelada ou mesmo colocada na lista negra.",
                            ],
                            [
                                "content" => "Para evitar diferenças na compreensão do texto, a plataforma reserva-se o direito de interpretação final deste evento.",
                            ],
                        ],
                    ],
                ];

                echo json_encode($response);
                exit;
            } else {
                $response = [
                    "status" => true, // indica falha
                    "msg" => "",
                    "time" => time(),
                    "data" => [],
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Member/slot/search
        if ($requestURI === '/api/member/slot/search') {
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
                $resp = mysqli_query($mysqli, $qry);
                $provedor = $data['pid'];

                if (mysqli_num_rows($resp) > 0) {
                    $datares = mysqli_fetch_assoc($resp);
                    $userId = $datares['id'];

                    // Consultar o histórico atual do usuário
                    $sql = "SELECT favoritos FROM usuarios WHERE id = ?";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $historicoAtual = [];
                    if ($result && $result->num_rows > 0) {
                        $historicoRow = $result->fetch_assoc();
                        $historicoAtual = !empty($historicoRow['favoritos']) ? explode(',', $historicoRow['favoritos']) : [];
                    }


                    // SQL para obter os dados dos jogos
                    if ($provedor == 0) {
                        // Se pid for igual a 0, consulta sem o WHERE
                        $sql = "SELECT id, game_code, game_name, provider, banner FROM games WHERE status=1 ORDER BY popular DESC;";
                    } else {
                        // Se pid não for 0, consulta com WHERE
                        $sql = "SELECT id, game_code, game_name, provider, banner FROM games WHERE status=1 AND provider = '" . $provedor . "' ORDER BY popular DESC;";
                    }
                    //var_dump($sql);
                    $result = $mysqli->query($sql);
                    $games_data = [];

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Verifica se o jogo está no histórico
                            $isFavorite = in_array($row['id'], $historicoAtual) ? 1 : 0;

                            $games_data[] = [
                                "id" => $row['id'],
                                "platform_id" => $row['provider'], // Valor fixo, você pode ajustar conforme necessário
                                "en_name" => $row['game_name'],
                                "client_type" => '',
                                "game_type" => '3',
                                "game_id" => $row['id'],
                                "img" => $row['banner'],
                                "is_hot" => 1,
                                "is_new" => 1,
                                "name" => $row['game_name'],
                                "sorting" => 99, // Você pode ajustar o valor conforme a necessidade
                                "vn_alias" => "Hổ May Mắn", // Exemplo, ajuste conforme necessário
                                "prefix" => "f51",
                                "game_code" => "",
                                "updated_at" => 0,
                                "updated_name" => "",
                                "currency" => "BRL",
                                "is_recommend" => 1,
                                "maintained" => 1,
                                "min_admission" => 1,
                                "is_lobby" => 0,
                                "hot_sort" => 99,
                                "is_favorites" => $isFavorite // Define is_favorites com base no histórico
                            ];
                        }

                        // Envolvendo os dados em "d" e adicionando o timestamp "t"
                        $response = [
                            "status" => true,
                            "data" => [
                                "d" => $games_data,
                                "t" => count($games_data), // Adicionando o timestamp atual
                            ],
                        ];
                    } else {
                        // Caso não haja resultados
                        $response = [
                            "status" => true,
                            "data" => [
                                "d" => [],
                                "t" => 0, // Adicionando o timestamp atual
                            ],
                        ];
                    }

                    // Fechando a conexão com o banco de dados
                    $mysqli->close();

                    // Retornando o JSON no formato correto
                    echo json_encode($response, JSON_UNESCAPED_SLASHES);
                } else {
                    $response = [
                        "status" => true,
                        "data" => [
                            "d" => [],
                            "t" => 0, // Adicionando o timestamp atual
                        ],
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Usuário ou senha incorretos",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }

        break;

    case 'GET':
        /* Rotas GET */


        // Rota Member/balance
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/balance') {
            $rotaEncontrada = true; // Rota encontrada
            // Verifica se o cookie 'token_user' está definido e não está vazio
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $token = mysqli_real_escape_string($mysqli, $_COOKIE['token_user']);

                // Usando prepared statement para segurança
                $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $resp = $stmt->get_result();

                if ($resp->num_rows > 0) {
                    $datres = $resp->fetch_assoc();
                    $userData = array(
                        "status" => true,
                        "data" => [
                            "uid" => $datres['id'],
                            "balance" => $datres['saldo'],
                            "lock_amount" => "0.00",
                        ],
                    );

                    // Converte o array associativo para JSON e retorna com código 200
                    http_response_code(200);
                    echo json_encode($userData);
                } else {
                    $response = [
                        "code" => 0, // Indica falha
                        "msg" => "Usuário sem efetuar login", // Mensagem de erro
                    ];
                    http_response_code(401); // Unauthorized
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Token não encontrado ou inválido",
                    "time" => time(),
                ];
                http_response_code(400); // Bad Request
                echo json_encode($response);
                exit;
            }
        }
        // Rota Withdraw/fee
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/finance/withdraw/fee?') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    [
                        "id" => "",
                        "tag_id" => "",
                        "fmin" => 0,
                        "fmax" => 20,
                        "amount" => 0,
                        "flags" => 1,
                        "updated_name" => "",
                        "updated_at" => 0,
                    ],
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Webset/list
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/webset/list') {

            // var_dump($_GET);

            global $pdo;

            $stmt = $pdo->prepare("SELECT * FROM modal_images");
            $stmt->execute();
            $datres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $modal = $datres;


            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    "pool_money_style" => $dataconfig['numero_jackpot'],
                    "img_shape" => "1",
                    "lang_switch" => "1",
                    "banner_hidden_proxy" => "",
                    "authLogRegType" => "none",
                    "authLogReg" => "0",
                    "footerJson" => '{"styleDisplay":"1","quickNavigateToggle":"1","cassinoAry":"返水,VIP,邀请/代理","gameAry":"捕鱼,电子,棋牌","suporteAry":"在线客服","快速跳转地址":"活动","shareSettingsToggle":"1","officialChannelToggle":"1","partnerInfoToggle":"1","licenseToggle":"0","hzValue":"","pzValue":"","partnerInfoData":[{"image":"/image/1724220117041..webp","name":"JDB","operationTime":"2024-08-21 03:02:02","operator":"mango01","id":"_1gsbr2rwm"},{"image":"/image/1724220126421..webp","name":"JILI","operationTime":"2024-08-21 03:02:09","operator":"mango01","id":"_2fgwmvp70"},{"image":"/image/1724220135199..webp","name":"PG","operationTime":"2024-08-21 03:02:18","operator":"mango01","id":"_4drxwjogn"},{"image":"/image/1724220142647..webp","name":"GC","operationTime":"2024-08-21 03:02:26","operator":"mango01","id":"_gqmphdwhj"},{"image":"/image/1724220149222..webp","name":"PIX","operationTime":"2024-08-21 03:02:32","operator":"mango01","id":"_o1mtg5mic"}],"licenseInfo":[{"image":"/image/1724220587697..webp","name":"MGA","operationTime":"2024-08-21 03:09:52","operator":"mango01","id":"_f26kelw6d"},{"image":"/image/1724220597985..webp","name":"GLI","operationTime":"2024-08-21 03:10:35","operator":"mango01","id":"_x3pqd4zva"},{"image":"/image/1724220640166..webp","name":"GC","operationTime":"2024-08-21 03:10:43","operator":"mango01","id":"_3fjlt8bhm"},{"image":"/image/1724220646502..webp","name":"PAGCOR","operationTime":"2024-08-21 03:10:53","operator":"mango01","id":"_ibe2y2dng"}],"copyrightInfo":["CNPJ: 57.281.970/0001-20","@Todos os direitos reservados 2025"],"companyInfoHtml":"<p>O Grupo MCB é líder mundial em cassinos online, oferecendo entretenimento emocionante com dealers ao vivo, jogos de mesa, caça-níqueis, pesca, loteria e esportes. Regulamentado pelo Governo de Curaçao sob a licença 8048/JAZ, o grupo cumpre todas as auditorias legais, garantindo operações confiáveis e de alta qualidade.</p>"}',
                    "googleQuickLogin" => "0",
                    "web_title" => "MCB Grup",
                    "pop" => [
                        [
                            "id" => "31042230947155971",
                            "ty" => "",
                            "name" => $modal[0]['name'],
                            "portal" => [
                                "pc",
                                "h5",
                                "app",
                            ],
                            "img" => "uploads/" . $modal[0]['img'],
                            "link" => $modal[0]['link'],
                            "oper" => "",
                            "sway" => 1,
                            "sort" => 2,
                            "state" => 1,
                            "op_at" => 1720066373,
                            "login_bf" => 2,
                            "login_af" => 2,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                        [
                            "id" => "168545903842044476",
                            "ty" => "",
                            "name" => $modal[1]['name'],
                            "portal" => [
                                "pc",
                                "h5",
                                "app",
                            ],
                            "img" => "uploads/" . $modal[1]['img'],
                            "link" => $modal[1]['link'],
                            "oper" => "",
                            "sway" => 1,
                            "sort" => 3,
                            "state" => 1,
                            "op_at" => 1720066319,
                            "login_bf" => 2,
                            "login_af" => 2,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                        [
                            "id" => "19253642284340635",
                            "ty" => "",
                            "name" => $modal[2]['name'],
                            "portal" => [
                                "pc",
                                "h5",
                                "app",
                            ],
                            "img" => "uploads/" . $modal[2]['img'],
                            "link" => $modal[2]['link'],
                            "oper" => "",
                            "sway" => 1,
                            "sort" => 10,
                            "state" => 1,
                            "op_at" => 1720066302,
                            "login_bf" => 2,
                            "login_af" => 2,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                    ],
                    "register_need_name_switch" => "1",
                    "guide_title" => "MCP",
                    "t_fees" => '[{"id":"1","tag_id":"","fmin":0,"fmax":20,"amount":0,"flags":1,"updated_name":"superadmin","updated_at":1721995535}]',
                    "decimalPlaces" => "2",
                    "pool_forward_flag" => "/",
                    "pool_forward" => "/",
                    "float" => [],
                    "banner_bottom_switch" => "0",
                    "Redirect_Url" => "/",
                    "deposit_img_h5" => "/image/1708935846379.webp",
                    "deposit_to" => "/activity-detail/17395548563954431/deposit",
                    "pool_forward_name" => "/",
                    "player_switch" => "0",
                    "prefix" => "f51",
                    "t_limits" => '[{"id":"538923381501373445","tag_id":"0","fmin":10,"fmax":50000,"updated_name":"superadmin","updated_at":1721995535}]',
                    "banner_switch" => "0",
                    "marqueeType" => "2", // 1 = GANHOS ALEATORIOS NO BROADCAST - 2 = BROADCAST DE TEXTO NORMAL
                    "pool_forward_jump_type" => "0",
                    "googleH5AppID" => "",
                    "deposit_img_pc" => "/image/1708935841207.webp",
                    "pool_style" => $dataconfig['jackpot'], // ESTILO DO JACKPOT
                    "s_wdraw_fst_deptamount" => "0",
                    "share" => [
                        [
                            "id" => "507356404759062703",
                            "ty" => "",
                            "name" => "line",
                            "portal" => [
                                "",
                            ],
                            "img" => "/image/1710154471108..webp",
                            "link" => "https://line.me/R/ti/p/",
                            "oper" => "",
                            "sway" => 0,
                            "sort" => 8,
                            "state" => 1,
                            "op_at" => 1717235005,
                            "login_bf" => 0,
                            "login_af" => 0,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                        [
                            "id" => "14797924984634028",
                            "ty" => "",
                            "name" => "ins",
                            "portal" => [
                                "",
                            ],
                            "img" => "/image/1713094582376..webp",
                            "link" => "./missao/",
                            "oper" => "",
                            "sway" => 0,
                            "sort" => 1,
                            "state" => 1,
                            "op_at" => 1720066398,
                            "login_bf" => 0,
                            "login_af" => 0,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                        [
                            "id" => "507339199868328987",
                            "ty" => "",
                            "name" => "facebook",
                            "portal" => [
                                "",
                            ],
                            "img" => "/image/1710154419122..webp",
                            "link" => "https://www.facebook.com/sharer/sharer.php?u=xxxxx",
                            "oper" => "",
                            "sway" => 0,
                            "sort" => 2,
                            "state" => 1,
                            "op_at" => 1712855731,
                            "login_bf" => 0,
                            "login_af" => 0,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                        [
                            "id" => "507346558646967565",
                            "ty" => "",
                            "name" => "telegram",
                            "portal" => [
                                "",
                            ],
                            "img" => "/image/1710154436860..webp",
                            "link" => "https://t.me/telegram",
                            "oper" => "",
                            "sway" => 0,
                            "sort" => 2,
                            "state" => 1,
                            "op_at" => 1710154439,
                            "login_bf" => 0,
                            "login_af" => 0,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                        [
                            "id" => "507347921991100188",
                            "ty" => "",
                            "name" => "youtube",
                            "portal" => [
                                "",
                            ],
                            "img" => "/image/1710154410968..webp",
                            "link" => "https://www.youtube.com/",
                            "oper" => "",
                            "sway" => 0,
                            "sort" => 4,
                            "state" => 1,
                            "op_at" => 1710154412,
                            "login_bf" => 0,
                            "login_af" => 0,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                        [
                            "id" => "507350188537890161",
                            "ty" => "",
                            "name" => "whatsapp",
                            "portal" => [
                                "",
                            ],
                            "img" => "/image/1710154463790..webp",
                            "link" => "https://wa.me/?text=xxxxx",
                            "oper" => "",
                            "sway" => 0,
                            "sort" => 1,
                            "state" => 1,
                            "op_at" => 1712855692,
                            "login_bf" => 0,
                            "login_af" => 0,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                        [
                            "id" => "507352469121437887",
                            "ty" => "",
                            "name" => "twitter",
                            "portal" => [
                                "",
                            ],
                            "img" => "/image/1712855638183..webp",
                            "link" => "https://twitter.com/intent/tweet",
                            "oper" => "",
                            "sway" => 0,
                            "sort" => 6,
                            "state" => 1,
                            "op_at" => 1712855644,
                            "login_bf" => 0,
                            "login_af" => 0,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                        [
                            "id" => "507353360508835083",
                            "ty" => "",
                            "name" => "tiktok",
                            "portal" => [
                                "",
                            ],
                            "img" => "/image/1710154454428..webp",
                            "link" => "https://www.tiktok.com/",
                            "oper" => "",
                            "sway" => 0,
                            "sort" => 7,
                            "state" => 1,
                            "op_at" => 1710154456,
                            "login_bf" => 0,
                            "login_af" => 0,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                        [
                            "id" => "507356404759062703",
                            "ty" => "",
                            "name" => "line",
                            "portal" => [
                                "",
                            ],
                            "img" => "/image/1710154471108..webp",
                            "link" => "https://line.me/R/ti/p/",
                            "oper" => "",
                            "sway" => 0,
                            "sort" => 8,
                            "state" => 1,
                            "op_at" => 1717235005,
                            "login_bf" => 0,
                            "login_af" => 0,
                            "close_today" => 0,
                            "recipient_type" => 0,
                            "recipient" => "",
                        ],
                    ],
                    "netsignal_switch" => "0", // ATIVAR OU DESATIVAR TROCAR DE SERVIDORES
                    "banner_text" => $dataconfig['mensagem_app'], // TEXTO EXIBIDO DENTRO DO POPUP DE DOWNLOAD
                    "group_name" => "china dev",
                    "realNameRequired" => "1",
                    "googleH5Secret" => "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",
                    "logo_img" => "/uploads/logo.webp",
                    "marqueeTxt" => $dataconfig['marquee'],
                    "pool_switch" => "1",
                    "favicon_img" => "/uploads/favicon.webp",
                    "banner_img" => "/image/1720066244137..webp",
                    "player_autoplay" => "1",
                    "reg_need_phone" => "1",
                    "pool_custom_style" => "/uploads/jackpot_custom.png",
                    "phoneRequired" => "1",
                    "game_recommend" => "1",
                    "pool_forward_id" => "/",
                ],
                "msg" => null,
            ];
            // Use JSON_UNESCAPED_UNICODE to avoid escaping Unicode characters
            $response_json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo $response_json;
        }
        // Rota Member/nav
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/nav') {
            $rotaEncontrada = true; // Rota encontrada


            $response = [
                "status" => true,
                "data" => [
                    [
                        "id" => 0,
                        "code" => 0,
                        "name" => "Hot",
                        "title" => "热门",
                        "icon" => "",
                        "url" => "",
                        "currency" => "",
                        "state" => 1,
                        "sort" => 0,
                        "show_by" => "{\"sh\": 3,\"ss\": 3,\"fh\": 3,\"fs\": 3}",
                        "open_by" => "",
                        "created_at" => 1721440897,
                        "updated_at" => 1722501993,
                        "operator_id" => 4662433674467505,
                        "operator_name" => "mango01",
                        "prefix" => "f51",
                        "img" => "",
                        "l" => []
                    ],
                    [

                        "id" => 5,
                        "code" => 5,
                        "name" => "Arcade",
                        "title" => "街机",
                        "icon" => "",
                        "url" => "https://pixvip.bet", // URL de redirecionamento ao clicar na imagem
                        "currency" => "",
                        "state" => 1,
                        "sort" => 3,
                        "show_by" => "{\"sh\": 1,\"ss\": 1,\"fh\": 1,\"fs\": 1}",
                        "open_by" => "",
                        "created_at" => 1721440897,
                        "updated_at" => 1722502399,
                        "operator_id" => 4662433674467505,
                        "operator_name" => "mango01",
                        "prefix" => "f51",
                        "img" => "/bximages/h5/game/logo/Online.webp", // Imagem clicável
                        "l" => []
                    ],
                    [

                        "id" => 3,
                        "code" => 3,
                        "name" => "Slots",
                        "title" => "电子",
                        "icon" => "./image-prod/bximages/h5/game/logo/PG.webp",
                        "url" => "",
                        "currency" => "",
                        "state" => 1,
                        "sort" => 2,
                        "show_by" => "{\"sh\": 2,\"ss\": 2,\"fh\": 2,\"fs\": 2}",
                        "open_by" => "",
                        "created_at" => 1721440897,
                        "updated_at" => 1722502399,
                        "operator_id" => 4662433674467505,
                        "operator_name" => "mango01",
                        "prefix" => "f51",
                        "img" => "",
                        "l" => [
                            [
                                "id" => "26595015200313",
                                "name" => "",
                                "wallet_id" => "26595015200313",
                                "wallet_name" => "PG",
                                "sub" => [
                                    "26595015200313",
                                    "26595015200305"
                                ],
                                "game_type" => 3,
                                "maintained" => 1,
                                "maintained_start" => 0,
                                "maintained_end" => 0,
                                "flags" => 3,
                                "state" => 1,
                                "seq" => 99,
                                "share_wallet" => 0,
                                "platform_is_hot" => 1,
                                "min_admission" => 0,
                                "jump_type" => 0,
                                "currency" => "BRL",
                                "promo_image" => "",
                                "popular_image" => "",
                                "games_count" => 92,
                                "code" => "",
                                "pid" => "c",
                                "img" => "/image-prod/bximages/h5/logolsot/pgsoft1.png",
                                "automatic" => 1
                            ],
                            [
                                "id" => "26595015200310",
                                "name" => "",
                                "wallet_id" => "26595015200309",
                                "wallet_name" => "SG/JL/JDB/PG/PP",
                                "sub" => [
                                    "26595015200310"
                                ],
                                "game_type" => 3,
                                "maintained" => 1,
                                "maintained_start" => 0,
                                "maintained_end" => 0,
                                "flags" => 3,
                                "state" => 1,
                                "seq" => 8,
                                "share_wallet" => 0,
                                "platform_is_hot" => 0,
                                "min_admission" => 0,
                                "jump_type" => 0,
                                "currency" => "BRL",
                                "promo_image" => "",
                                "popular_image" => "",
                                "games_count" => 347,
                                "code" => "",
                                "pid" => "26595015200310",
                                "img" => "/image-prod/bximages/h5/logolsot/progmatic1.png",
                                "automatic" => 1
                            ],
                            [
                                "id" => "36595015200906",
                                "name" => "",
                                "wallet_id" => "26595015200309",
                                "wallet_name" => "SG/JL/JDB/PG/PP",
                                "sub" => [
                                    "36595015200906"
                                ],
                                "game_type" => 3,
                                "maintained" => 1,
                                "maintained_start" => 0,
                                "maintained_end" => 0,
                                "flags" => 3,
                                "state" => 1,
                                "seq" => 15,
                                "share_wallet" => 0,
                                "platform_is_hot" => 0,
                                "min_admission" => 0,
                                "jump_type" => 0,
                                "currency" => "BRL",
                                "promo_image" => "",
                                "popular_image" => "",
                                "games_count" => 80,
                                "code" => "",
                                "pid" => "36595015200906",
                                "img" => "./image-prod/bximages/h5/logolsot/spribe1.png",
                                "automatic" => 1
                            ],
                            [
                                "id" => "26595015200105",
                                "name" => "",
                                "wallet_id" => "26595015200105",
                                "wallet_name" => "",
                                "sub" => [
                                    "26595015200105"
                                ],
                                "game_type" => 1,
                                "maintained" => 1,
                                "maintained_start" => 0,
                                "maintained_end" => 0,
                                "flags" => 3,
                                "state" => 1,
                                "seq" => 20,
                                "share_wallet" => 0,
                                "platform_is_hot" => 1,
                                "min_admission" => 0,
                                "jump_type" => 0,
                                "currency" => "",
                                "promo_image" => "",
                                "popular_image" => "",
                                "games_count" => 1,
                                "code" => "",
                                "pid" => "26595015200105",
                                "img" => "./image-prod/bximages/h5/logolsot/aovivo1.png",
                                "automatic" => 1
                            ],
                            [
                                "id" => "26595015200201",
                                "name" => "",
                                "wallet_id" => "26595015200309",
                                "wallet_name" => "SG/JL/JDB/PG/PP",
                                "sub" => [
                                    "26595015200201",
                                ],
                                "game_type" => 2,
                                "maintained" => 1,
                                "maintained_start" => 0,
                                "maintained_end" => 0,
                                "flags" => 3,
                                "state" => 1,
                                "seq" => 96,
                                "share_wallet" => 0,
                                "platform_is_hot" => 1,
                                "min_admission" => 0,
                                "jump_type" => 0,
                                "currency" => "BRL",
                                "promo_image" => "",
                                "popular_image" => "",
                                "games_count" => 11,
                                "code" => "",
                                "pid" => "26595015200201",
                                "img" => "./image-prod/bximages/h5/logolsot/pescaria1.png",
                                "automatic" => 1
                            ],
                            [
                                "id" => "26595015200401",
                                "name" => "",
                                "wallet_id" => "26595015200401",
                                "wallet_name" => "Arcade",
                                "sub" => [
                                    "26595015200401"
                                ],
                                "game_type" => 4,
                                "maintained" => 1,
                                "maintained_start" => 0,
                                "maintained_end" => 0,
                                "flags" => 3,
                                "state" => 1,
                                "seq" => 1,
                                "share_wallet" => 0,
                                "platform_is_hot" => 1,
                                "min_admission" => 0,
                                "jump_type" => 0,
                                "currency" => "BRL",
                                "promo_image" => "",
                                "popular_image" => "",
                                "games_count" => 25,
                                "code" => "",
                                "pid" => "26595015200401",
                                "img" => "./image-prod/bximages/h5/logolsot/arcade1.png",
                                "automatic" => 1

                            ]
                        ]
                    ],
                    /* [
                        "id" =>  2,
                        "code" =>  2,
                        "name" =>  "Fishing",
                        "title" =>  "捕鱼",
                        "icon" =>  "",
                        "url" =>  "",
                        "currency" =>  "",
                        "state" =>  1,
                        "sort" =>  3,
                        "show_by" =>  "{\"sh\": 2,\"ss\": 2,\"fh\": 2,\"fs\": 2}",
                        "open_by" =>  "",
                        "created_at" =>  1721440897,
                        "updated_at" =>  1722502396,
                        "operator_id" =>  4662433674467505,
                        "operator_name" =>  "mango01",
                        "prefix" =>  "f51",
                        "img" =>  "",
                        "l" =>  [
                            [
                                "id" =>  "26595015200201",
                                "name" =>  "JL Pescaria",
                                "wallet_id" =>  "26595015200309",
                                "wallet_name" =>  "SG/JL/JDB/PG/PP",
                                "sub" =>  [
                                    "26595015200201"
                                ],
                                "game_type" =>  2,
                                "maintained" =>  1,
                                "maintained_start" =>  0,
                                "maintained_end" =>  0,
                                "flags" =>  3,
                                "state" =>  1,
                                "seq" =>  5,
                                "share_wallet" =>  0,
                                "platform_is_hot" =>  1,
                                "min_admission" =>  0,
                                "jump_type" =>  0,
                                "currency" =>  "BRL",
                                "promo_image" =>  "",
                                "popular_image" =>  "",
                                "games_count" =>  11,
                                "code" =>  "",
                                "pid" =>  "26595015200201",
                                "img" =>  "/images-br/plat/Pescaria-JILI.png.webp",
                                "automatic" =>  1
                            ]
                        ]
                    ], */
                    /* [
                        "id" => 1,
                        "code" => 1,
                        "name" => "Live",
                        "title" => "真人",
                        "icon" => "",
                        "url" => "",
                        "currency" => "",
                        "state" => 1,
                        "sort" => 3,
                        "show_by" => "{\"sh\":2,\"ss\":2,\"fh\":2,\"fs\":2}",
                        "open_by" => 0,
                        "created_at" => 1721440897,
                        "updated_at" => 1722501988,
                        "operator_id" => 4662433674467505,
                        "operator_name" => "mango01",
                        "prefix" => "bs8",
                        "img" => "",
                        "l" => [
                            [
                                "id" => "26595015200105",
                                "name" => "EVO Casino ",
                                "wallet_id" => "26595015200105",
                                "wallet_name" => "EVO",
                                "sub" => [
                                    "26595015200105"
                                ],
                                "game_type" => 1,
                                "maintained" => 1,
                                "maintained_start" => 0,
                                "maintained_end" => 0,
                                "flags" => 3,
                                "state" => 1,
                                "seq" => 20,
                                "share_wallet" => 0,
                                "platform_is_hot" => 1,
                                "min_admission" => 0,
                                "jump_type" => 0,
                                "currency" => "",
                                "promo_image" => "",
                                "popular_image" => "",
                                "games_count" => 1,
                                "code" => "",
                                "pid" => "26595015200105",
                                "img" => "/images-br/plat/Live-EVO.png.webp",
                                "automatic" => 1
                            ]
                        ]
                    ] */
                ]
            ];

            // Retornando o JSON no formato correto
            echo json_encode($response, JSON_UNESCAPED_SLASHES);
        }
        // Rota Promo/welfare/getconf
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/promo/welfare/getconf') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    "entrance" => "2,1",
                    "limited" => '""',
                    "pick" => "1",
                    "login_before" => "",
                    "login_after" => "",
                    "flow_multiple" => "1",
                    "is_audit" => 1,
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Promo/welfare/config
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/promo/welfare/config') {
            $rotaEncontrada = true; // Rota encontrada

            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $token = mysqli_real_escape_string($mysqli, $_COOKIE['token_user']);


                // Usando prepared statement para segurança
                $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $resp = $stmt->get_result();

                if ($resp->num_rows > 0) {
                    $datres = $resp->fetch_assoc();

                    $stmt_mp = $mysqli->prepare("SELECT * FROM metodos_pagamentos WHERE user_id = ?");
                    $stmt_mp->bind_param("i", $datares['id']);
                    $stmt_mp->execute();
                    $resp_mp = $stmt_mp->get_result();

                    $stmt_tks = $mysqli->prepare("SELECT * FROM tarefas");
                    $stmt_tks->execute();
                    $resp_tks = $stmt_tks->get_result();
                    $tarefas = $resp_tks->fetch_all(MYSQLI_ASSOC);

                    $data = [];

                    if ($tarefas[1]['active'] === 1) {
                        $item = [
                            "id" => $tarefas[1]['id'],
                            "promo_id" => "85100667711113",
                            "welfare_id" => "4",
                            "prefix" => "bs8",
                            "title" => $tarefas[1]['name'],
                            "uid" => "861141037",
                            "username" => $datres['mobile'],
                            "flag" => 1,
                            "state" => (int) $datres['bonus_status_birth'],
                            "limited_at" => 0,
                            "expired_at" => 0,
                            "receipt_at" => 1731933464,
                            "remark" => "",
                            "device" => "0",
                            "device_ty" => "",
                            "ip" => "",
                            "amount" => $tarefas[1]['value'],
                            "flow_multiple" => 1,
                            "created_at" => 1731933464,
                            "check_deposit" => 0,
                            "first_deposit_done" => 0,
                            "temp_add_record" => 0
                        ];
                        array_push($data, $item);
                    }

                    if ($tarefas[2]['active'] === 1) {
                        $item = [
                            "id" => $tarefas[2]['id'],
                            "promo_id" => "85100667711113",
                            "welfare_id" => "5",
                            "prefix" => "bs8",
                            "title" => $tarefas[2]['name'],
                            "uid" => "861141037",
                            "username" => $datres['mobile'],
                            "flag" => 1,
                            "state" => (int) $datres['bonus_status_pass_withdraw'],
                            "limited_at" => 0,
                            "expired_at" => 0,
                            "receipt_at" => 1731933464,
                            "remark" => "",
                            "device" => "0",
                            "device_ty" => "",
                            "ip" => "",
                            "amount" => $tarefas[2]['value'],
                            "flow_multiple" => 1,
                            "created_at" => 1731933464,
                            "check_deposit" => 0,
                            "first_deposit_done" => 0,
                            "temp_add_record" => 0
                        ];
                        array_push($data, $item);
                    }

                    if ($tarefas[3]['active'] === 1) {
                        $item = [
                            "id" => $tarefas[3]['id'],
                            "promo_id" => "85100667711113",
                            "welfare_id" => "6",
                            "prefix" => "bs8",
                            "title" => $tarefas[3]['name'],
                            "uid" => "861141037",
                            "username" => $datres['mobile'],
                            "flag" => 1,
                            "state" => (int) $datres['bonus_status_acc_withdraw'],
                            "limited_at" => 0,
                            "expired_at" => 0,
                            "receipt_at" => 1731933464,
                            "remark" => "",
                            "device" => "0",
                            "device_ty" => "",
                            "ip" => "",
                            "amount" => $tarefas[3]['value'],
                            "flow_multiple" => 1,
                            "created_at" => 1731933464,
                            "check_deposit" => 0,
                            "first_deposit_done" => 0,
                            "temp_add_record" => 0
                        ];
                        array_push($data, $item);
                    }

                    if ($tarefas[4]['active'] === 1) {
                        $item = [
                            "id" => $tarefas[4]['id'],
                            "promo_id" => "85100667711113",
                            "welfare_id" => "7",
                            "prefix" => "bs8",
                            "title" => $tarefas[4]['name'],
                            "uid" => "861141037",
                            "username" => $datres['mobile'],
                            "flag" => 1,
                            "state" => (int) $datres['bonus_status_define_avatar'],
                            "limited_at" => 0,
                            "expired_at" => 0,
                            "receipt_at" => 1731933464,
                            "remark" => "",
                            "device" => "0",
                            "device_ty" => "",
                            "ip" => "",
                            "amount" => $tarefas[4]['value'],
                            "flow_multiple" => 1,
                            "created_at" => 1731933464,
                            "check_deposit" => 0,
                            "first_deposit_done" => 0,
                            "temp_add_record" => 0
                        ];
                        array_push($data, $item);
                    }

                    if ($tarefas[5]['active'] === 1) {
                        $item = [
                            "id" => $tarefas[5]['id'],
                            "promo_id" => "85100667711113",
                            "welfare_id" => "8",
                            "prefix" => "bs8",
                            "title" => $tarefas[5]['name'],
                            "uid" => "861141037",
                            "username" => $datres['mobile'],
                            "flag" => 1,
                            "state" => (int) $datres['bonus_status_define_email'],
                            "limited_at" => 0,
                            "expired_at" => 0,
                            "receipt_at" => 1731933464,
                            "remark" => "",
                            "device" => "0",
                            "device_ty" => "",
                            "ip" => "",
                            "amount" => $tarefas[5]['value'],
                            "flow_multiple" => 1,
                            "created_at" => 1731933464,
                            "check_deposit" => 0,
                            "first_deposit_done" => 0,
                            "temp_add_record" => 0
                        ];
                        array_push($data, $item);
                    }

                    if ($tarefas[0]['active'] === 1) {
                        $item = [
                            "id" => $tarefas[0]['id'],
                            "promo_id" => "85100667711113",
                            "welfare_id" => "3",
                            "prefix" => "bs8",
                            "title" => $tarefas[0]['name'],
                            "uid" => "861141037",
                            "username" => $datres['mobile'],
                            "flag" => 1,
                            "state" => (int) $datres['bonus_status_primary_withdraw'],
                            "limited_at" => 0,
                            "expired_at" => 0,
                            "receipt_at" => 1731933464,
                            "remark" => "",
                            "device" => "0",
                            "device_ty" => "",
                            "ip" => "",
                            "amount" => $tarefas[0]['value'],
                            "flow_multiple" => 1,
                            "created_at" => 1731933464,
                            "check_deposit" => 0,
                            "first_deposit_done" => 0,
                            "temp_add_record" => 0
                        ];
                        array_push($data, $item);
                    }

                    $response = [
                        "status" => true,
                        "data" => $data,
                        "msg" => null
                    ];

                    /* [
                             [
                                "id" => "274289871900719704",
                                "promo_id" => "85100667711113",
                                "welfare_id" => "1",
                                "prefix" => "bs8",
                                "title" => "Faça o download do APP, instale e faça login no aplicativo pela primeira vez",
                                "uid" => "861141037",
                                "username" => $datres['mobile'],
                                "flag" => 1,
                                "state" => $datres['download_app'] ? 502 : 501,
                                "limited_at" => 0,
                                "expired_at" => 0,
                                "receipt_at" => 1731933464,
                                "remark" => "",
                                "device" => "0",
                                "device_ty" => "",
                                "ip" => "",
                                "amount" => 1.99,
                                "flow_multiple" => 1,
                                "created_at" => 1731933464,
                                "check_deposit" => 0,
                                "first_deposit_done" => 0,
                                "temp_add_record" => 0
                            ],
                            [
                                "id" => "274289872445166186",
                                "promo_id" => "85100667711113",
                                "welfare_id" => "2",
                                "prefix" => "bs8",
                                "title" => "Salve atalho de mesa",
                                "uid" => "861141037",
                                "username" => $datres['mobile'],
                                "flag" => 1,
                                "state" => $datres['save_atalho'] ? 502 : 501,
                                "limited_at" => 0,
                                "expired_at" => 0,
                                "receipt_at" => 1731933464,
                                "remark" => "",
                                "device" => "0",
                                "device_ty" => "",
                                "ip" => "",
                                "amount" => 0.99,
                                "flow_multiple" => 1,
                                "created_at" => 1731933464,
                                "check_deposit" => 0,
                                "first_deposit_done" => 0,
                                "temp_add_record" => 0
                            ],

                            [
                                "id" =>  $tarefas[1]['id'],
                                "promo_id" => "85100667711113",
                                "welfare_id" => "4",
                                "prefix" => "bs8",
                                "title" => $tarefas[1]['name'],
                                "uid" => "861141037",
                                "username" => $datres['mobile'],
                                "flag" => 1,
                                "state" => (int) $datres['bonus_status_birth'],
                                "limited_at" => 0,
                                "expired_at" => 0,
                                "receipt_at" => 1731933464,
                                "remark" => "",
                                "device" => "0",
                                "device_ty" => "",
                                "ip" => "",
                                "amount" => $tarefas[1]['value'],
                                "flow_multiple" => 1,
                                "created_at" => 1731933464,
                                "check_deposit" => 0,
                                "first_deposit_done" => 0,
                                "temp_add_record" => 0
                            ],
                            [
                                "id" => $tarefas[2]['id'],
                                "promo_id" => "85100667711113",
                                "welfare_id" => "5",
                                "prefix" => "bs8",
                                "title" => $tarefas[2]['name'],
                                "uid" => "861141037",
                                "username" => $datres['mobile'],
                                "flag" => 1,
                                "state" => (int) $datres['bonus_status_pass_withdraw'],
                                "limited_at" => 0,
                                "expired_at" => 0,
                                "receipt_at" => 1731933464,
                                "remark" => "",
                                "device" => "0",
                                "device_ty" => "",
                                "ip" => "",
                                "amount" => $tarefas[2]['value'],
                                "flow_multiple" => 1,
                                "created_at" => 1731933464,
                                "check_deposit" => 0,
                                "first_deposit_done" => 0,
                                "temp_add_record" => 0
                            ],
                            [
                                "id" => $tarefas[3]['id'],
                                "promo_id" => "85100667711113",
                                "welfare_id" => "6",
                                "prefix" => "bs8",
                                "title" => $tarefas[3]['name'],
                                "uid" => "861141037",
                                "username" => $datres['mobile'],
                                "flag" => 1,
                                "state" => (int) $datres['bonus_status_acc_withdraw'],
                                "limited_at" => 0,
                                "expired_at" => 0,
                                "receipt_at" => 1731933464,
                                "remark" => "",
                                "device" => "0",
                                "device_ty" => "",
                                "ip" => "",
                                "amount" => $tarefas[3]['value'],
                                "flow_multiple" => 1,
                                "created_at" => 1731933464,
                                "check_deposit" => 0,
                                "first_deposit_done" => 0,
                                "temp_add_record" => 0
                            ],
                            [
                                "id" => $tarefas[4]['id'],
                                "promo_id" => "85100667711113",
                                "welfare_id" => "7",
                                "prefix" => "bs8",
                                "title" => $tarefas[4]['name'],
                                "uid" => "861141037",
                                "username" => $datres['mobile'],
                                "flag" => 1,
                                "state" => (int) $datres['bonus_status_define_avatar'],
                                "limited_at" => 0,
                                "expired_at" => 0,
                                "receipt_at" => 1731933464,
                                "remark" => "",
                                "device" => "0",
                                "device_ty" => "",
                                "ip" => "",
                                "amount" => $tarefas[4]['value'],
                                "flow_multiple" => 1,
                                "created_at" => 1731933464,
                                "check_deposit" => 0,
                                "first_deposit_done" => 0,
                                "temp_add_record" => 0
                            ],
                            [
                                "id" => $tarefas[5]['id'],
                                "promo_id" => "85100667711113",
                                "welfare_id" => "8",
                                "prefix" => "bs8",
                                "title" => $tarefas[5]['name'],
                                "uid" => "861141037",
                                "username" => $datres['mobile'],
                                "flag" => 1,
                                "state" => (int) $datres['bonus_status_define_email'],
                                "limited_at" => 0,
                                "expired_at" => 0,
                                "receipt_at" => 1731933464,
                                "remark" => "",
                                "device" => "0",
                                "device_ty" => "",
                                "ip" => "",
                                "amount" => $tarefas[5]['value'],
                                "flow_multiple" => 1,
                                "created_at" => 1731933464,
                                "check_deposit" => 0,
                                "first_deposit_done" => 0,
                                "temp_add_record" => 0
                            ],
                            [
                                "id" => $tarefas[0]['id'],
                                "promo_id" => "85100667711113",
                                "welfare_id" => "3",
                                "prefix" => "bs8",
                                "title" => $tarefas[0]['name'],
                                "uid" => "861141037",
                                "username" => $datres['mobile'],
                                "flag" => 1,
                                "state" => (int) $datres['bonus_status_primary_withdraw'],
                                "limited_at" => 0,
                                "expired_at" => 0,
                                "receipt_at" => 1731933464,
                                "remark" => "",
                                "device" => "0",
                                "device_ty" => "",
                                "ip" => "",
                                "amount" => $tarefas[0]['value'],
                                "flow_multiple" => 1,
                                "created_at" => 1731933464,
                                "check_deposit" => 0,
                                "first_deposit_done" => 0,
                                "temp_add_record" => 0
                            ],/* ,
                            [
                                "id" => "274290264851409662",
                                "promo_id" => "85100667711113",
                                "welfare_id" => "9",
                                "prefix" => "bs8",
                                "title" => "Cadastre-se e ganhe 1.99",
                                "uid" => "861141037",
                                "username" => "testando",
                                "flag" => 1,
                                "state" => 502,
                                "limited_at" => 0,
                                "expired_at" => 0,
                                "receipt_at" => 1731933464,
                                "remark" => "",
                                "device" => "0",
                                "device_ty" => "",
                                "ip" => "",
                                "amount" => 1.99,
                                "flow_multiple" => 1,
                                "created_at" => 1731933464,
                                "check_deposit" => 1,
                                "first_deposit_done" => 0,
                                "temp_add_record" => 0
                            ]
                        ] */

                    // Use JSON_UNESCAPED_UNICODE to avoid escaping Unicode characters
                    $response_json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    echo $response_json;
                }
            } else {
                $stmt_tks = $mysqli->prepare("SELECT * FROM tarefas");
                $stmt_tks->execute();
                $resp_tks = $stmt_tks->get_result();
                $tarefas = $resp_tks->fetch_all(MYSQLI_ASSOC);

                $data = [];

                if ($tarefas[1]['active'] === 1) {
                    $item = [
                        "id" => $tarefas[1]['id'],
                        "promo_id" => "85100667711113",
                        "welfare_id" => "4",
                        "prefix" => "bs8",
                        "title" => $tarefas[1]['name'],
                        "uid" => "861141037",
                        "username" => "softbet",
                        "flag" => 1,
                        "state" => 502,
                        "limited_at" => 0,
                        "expired_at" => 0,
                        "receipt_at" => 1731933464,
                        "remark" => "",
                        "device" => "0",
                        "device_ty" => "",
                        "ip" => "",
                        "amount" => $tarefas[1]['value'],
                        "flow_multiple" => 1,
                        "created_at" => 1731933464,
                        "check_deposit" => 0,
                        "first_deposit_done" => 0,
                        "temp_add_record" => 0
                    ];
                    array_push($data, $item);
                }

                if ($tarefas[2]['active'] === 1) {
                    $item = [
                        "id" => $tarefas[2]['id'],
                        "promo_id" => "85100667711113",
                        "welfare_id" => "5",
                        "prefix" => "bs8",
                        "title" => $tarefas[2]['name'],
                        "uid" => "861141037",
                        "username" => "softbet",
                        "flag" => 1,
                        "state" => 502,
                        "limited_at" => 0,
                        "expired_at" => 0,
                        "receipt_at" => 1731933464,
                        "remark" => "",
                        "device" => "0",
                        "device_ty" => "",
                        "ip" => "",
                        "amount" => $tarefas[2]['value'],
                        "flow_multiple" => 1,
                        "created_at" => 1731933464,
                        "check_deposit" => 0,
                        "first_deposit_done" => 0,
                        "temp_add_record" => 0
                    ];
                    array_push($data, $item);
                }

                if ($tarefas[3]['active'] === 1) {
                    $item = [
                        "id" => $tarefas[3]['id'],
                        "promo_id" => "85100667711113",
                        "welfare_id" => "6",
                        "prefix" => "bs8",
                        "title" => $tarefas[3]['name'],
                        "uid" => "861141037",
                        "username" => "softbet",
                        "flag" => 1,
                        "state" => 502,
                        "limited_at" => 0,
                        "expired_at" => 0,
                        "receipt_at" => 1731933464,
                        "remark" => "",
                        "device" => "0",
                        "device_ty" => "",
                        "ip" => "",
                        "amount" => $tarefas[3]['value'],
                        "flow_multiple" => 1,
                        "created_at" => 1731933464,
                        "check_deposit" => 0,
                        "first_deposit_done" => 0,
                        "temp_add_record" => 0
                    ];
                    array_push($data, $item);
                }

                if ($tarefas[4]['active'] === 1) {
                    $item = [
                        "id" => $tarefas[4]['id'],
                        "promo_id" => "85100667711113",
                        "welfare_id" => "7",
                        "prefix" => "bs8",
                        "title" => $tarefas[4]['name'],
                        "uid" => "861141037",
                        "username" => "softbet",
                        "flag" => 1,
                        "state" => 502,
                        "limited_at" => 0,
                        "expired_at" => 0,
                        "receipt_at" => 1731933464,
                        "remark" => "",
                        "device" => "0",
                        "device_ty" => "",
                        "ip" => "",
                        "amount" => $tarefas[4]['value'],
                        "flow_multiple" => 1,
                        "created_at" => 1731933464,
                        "check_deposit" => 0,
                        "first_deposit_done" => 0,
                        "temp_add_record" => 0
                    ];
                    array_push($data, $item);
                }

                if ($tarefas[5]['active'] === 1) {
                    $item = [
                        "id" => $tarefas[5]['id'],
                        "promo_id" => "85100667711113",
                        "welfare_id" => "8",
                        "prefix" => "bs8",
                        "title" => $tarefas[5]['name'],
                        "uid" => "861141037",
                        "username" => "softbet",
                        "flag" => 1,
                        "state" => 502,
                        "limited_at" => 0,
                        "expired_at" => 0,
                        "receipt_at" => 1731933464,
                        "remark" => "",
                        "device" => "0",
                        "device_ty" => "",
                        "ip" => "",
                        "amount" => $tarefas[5]['value'],
                        "flow_multiple" => 1,
                        "created_at" => 1731933464,
                        "check_deposit" => 0,
                        "first_deposit_done" => 0,
                        "temp_add_record" => 0
                    ];
                    array_push($data, $item);
                }

                if ($tarefas[0]['active'] === 1) {
                    $item = [
                        "id" => $tarefas[0]['id'],
                        "promo_id" => "85100667711113",
                        "welfare_id" => "3",
                        "prefix" => "bs8",
                        "title" => $tarefas[0]['name'],
                        "uid" => "861141037",
                        "username" => "softbet",
                        "flag" => 1,
                        "state" => 502,
                        "limited_at" => 0,
                        "expired_at" => 0,
                        "receipt_at" => 1731933464,
                        "remark" => "",
                        "device" => "0",
                        "device_ty" => "",
                        "ip" => "",
                        "amount" => $tarefas[0]['value'],
                        "flow_multiple" => 1,
                        "created_at" => 1731933464,
                        "check_deposit" => 0,
                        "first_deposit_done" => 0,
                        "temp_add_record" => 0
                    ];
                    array_push($data, $item);
                }

                $response = [
                    "status" => true,
                    "data" => $data,
                    "msg" => null
                ];

                $response_json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo $response_json;
            }
        }

        // Rota /api/promo/welfare/receive
        if (strpos($_SERVER['REQUEST_URI'], '/api/promo/welfare/receive') !== false) {
            $rotaEncontrada = true; // Rota encontrada

            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $token = mysqli_real_escape_string($mysqli, $_COOKIE['token_user']);

                // Usando prepared statement para segurança
                $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $resp = $stmt->get_result();

                if ($resp->num_rows > 0) {
                    $datres = $resp->fetch_assoc();
                    $saldoatual = $datres['saldo'];
                    $id = isset($_GET['id']) ? $_GET['id'] : NULL;

                    $stmttask = $mysqli->prepare("SELECT * FROM tarefas WHERE id = ?");
                    $stmttask->bind_param("s", $id);
                    $stmttask->execute();
                    $task = $stmttask->get_result();
                    $tarefa = $task->fetch_assoc();
                    $novosaldo = $tarefa['value'];
                    $status = "503";

                    /* var_dump($novosaldo);
                    exit; */
                    $column = "bonus_status_birth";
                    switch ($id) {
                        case "274289873698243860":
                            $column = "bonus_status_birth";
                            break;
                        case "274289874240437166":
                            $column = "bonus_status_pass_withdraw";
                            break;
                        case "274289874806688461":
                            $column = "bonus_status_acc_withdraw";
                            break;
                        case "274289875414269563":
                            $column = "bonus_status_define_avatar";
                            break;
                        case "274289875952033183":
                            $column = "bonus_status_define_email";
                            break;
                        case "274289873169027780":
                            $column = "bonus_status_primary_withdraw";
                            break;
                    }

                    $sqlUpdate = $mysqli->prepare("UPDATE usuarios SET saldo = saldo + ?, $column = ? WHERE id = ?");
                    $sqlUpdate->bind_param("ssi", $novosaldo, $status, $datres['id']);

                    if ($sqlUpdate->execute()) {
                        $response = [
                            "status" => true,
                            "data" => '1000',
                            "msg" => null,
                        ];
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response = [
                        'status' => true,
                        "data" => []
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    'status' => true,
                    "data" => []
                ];
                echo json_encode($response);
                exit;
            }
        }



        /* [
                "status" => true,
                "data" => [
                    [
                        "id" => "122609400672191826",
                        "promo_id" => "85100667711113",
                        "welfare_id" => "1",
                        "prefix" => "f51",
                        "title" => 'Faça o download do APP, instale e faça login no aplicativo pela primeira vez',
                        "uid" => "138820231",
                        "username" => "china",
                        "flag" => 1,
                        "state" => 501,
                        "limited_at" => 0,
                        "expired_at" => 0,
                        "receipt_at" => 1725613454,
                        "remark" => "",
                        "device" => "0",
                        "device_ty" => "",
                        "ip" => "",
                        "amount" => 0.99,
                        "flow_multiple" => 1,
                        "created_at" => 1725613454,
                        "check_deposit" => 1,
                        "first_deposit_done" => 0,
                    ],
                    [
                        "id" => "122609401349143740",
                        "promo_id" => "85100667711113",
                        "welfare_id" => "2",
                        "prefix" => "f51",
                        "title" => "Salve atalho de mesa",
                        "uid" => "138820231",
                        "username" => "china",
                        "flag" => 1,
                        "state" => 501,
                        "limited_at" => 0,
                        "expired_at" => 0,
                        "receipt_at" => 1725613454,
                        "remark" => "",
                        "device" => "0",
                        "device_ty" => "",
                        "ip" => "",
                        "amount" => 0.99,
                        "flow_multiple" => 1,
                        "created_at" => 1725613454,
                        "check_deposit" => 0,
                        "first_deposit_done" => 0,
                    ],
                ],
                "msg" => null,
            ]; */



        // Rota Member/award
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/award') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    "amount" => 11575274645,
                    "num" => 0,
                    "prefix" => "f51",
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Banner
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/banner') {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            $rotaEncontrada = true; // Rota encontrada
            $qry = "SELECT * FROM banner WHERE 1";
            $dat = mysqli_query($mysqli, $qry);
            $banners = $dat->fetch_all(MYSQLI_ASSOC);
            //var_dump($banners);
            $data = [];
            foreach ($banners as $key => $banner) {
                if ($key === 0 && isset($banner['status']) && $banner['status'] == 1) {
                    $data[] = [
                        "id" => "12135081375969104",
                        "title" => $banner['titulo'],
                        "content" => "",
                        "url" => "/activity/recommend-friends",
                        "sort" => "1",
                        "images" => "/uploads/" . $banner['img'],
                        "flags" => "1",
                        "kf_type" => 0,
                    ];
                } else if ($key === 1 && isset($banner['status']) && $banner['status'] == 1) {
                    $data[] = [
                        "id" => "7621850545701900",
                        "title" => $banner['titulo'],
                        "content" => "",
                        "url" => "/activity-detail/17395548563954431/deposit",
                        "sort" => "2",
                        "images" => "/uploads/" . $banner['img'],
                        "flags" => "1",
                        "kf_type" => 0,
                    ];
                } else if ($key === 2 && isset($banner['status']) && $banner['status'] == 1) {
                    $data[] = [
                        "id" => "12141996033465223",
                        "title" => $banner['titulo'],
                        "content" => "",
                        "url" => "/activity-detail/12052671887318748/static",
                        "sort" => "3",
                        "images" => "/uploads/" . $banner['img'],
                        "flags" => "1",
                        "kf_type" => 0,
                    ];
                }
            }

            $response = [
                "status" => true,
                "data" => $data,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Slot/Hotgame
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/slot/hotgame') {
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            $limit = 9; // Número de registros por página
            $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;
            $de = ($page - 1) * $limit; // Deslocamento baseado na página

            $sqlt = "SELECT COUNT(*) as total FROM games WHERE popular = 1";
            $stmtt = $mysqli->prepare($sqlt);
            if ($stmtt) {
                $stmtt->execute();
                $resultt = $stmtt->get_result()->fetch_assoc();
                $totalRecords = $resultt['total']; // Total de registros populares
            } else {
                $response = [
                    "status" => false,
                    "msg" => "Erro ao obter o total de registros.",
                ];
                echo json_encode($response, JSON_UNESCAPED_SLASHES);
                exit;
            }

            $response = [];

            // SQL para obter os dados dos jogos
            $sql = "SELECT id, game_code, game_name, provider, banner 
            FROM games
            WHERE popular = 1 
            ORDER BY ordem DESC
            LIMIT ?, ?"; // Usando LIMIT com deslocamento e quantidade

            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                // Vincular parâmetros e executar a consulta
                $stmt->bind_param("ii", $de, $limit); // $de como deslocamento e $limit como número de registros
                $stmt->execute();

                // Obter os resultados
                $result = $stmt->get_result();
                // Verifica se a consulta falhou
                if (!$result) {
                    error_log("Erro na consulta SQL: " . mysqli_error($mysqli));
                    $response = [
                        "status" => false,
                        "msg" => "Erro ao acessar os jogos.",
                    ];
                    echo json_encode($response, JSON_UNESCAPED_SLASHES);
                    exit;
                }

                $games_data = [];

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $isFavorite = 0;

                        // Verifica se o usuário está logado
                        if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                            $qry = "SELECT id, favoritos FROM usuarios WHERE token = ?";
                            $stmt = $mysqli->prepare($qry);
                            $stmt->bind_param("s", $_COOKIE['token_user']);
                            $stmt->execute();
                            $resp = $stmt->get_result();

                            if ($resp && $resp->num_rows > 0) {
                                $datares = $resp->fetch_assoc();
                                $userId = $datares['id'];
                                $historicoAtual = !empty($datares['favoritos']) ? explode(',', $datares['favoritos']) : [];

                                // Verifica se o jogo está no histórico
                                $isFavorite = in_array($row['id'], $historicoAtual) ? 1 : 0;
                            }
                        }

                        // Adiciona os dados do jogo ao array
                        $games_data[] = [
                            "id" => $row['id'],
                            "platform_id" => $row['provider'],
                            "en_name" => $row['game_name'],
                            "client_type" => '',
                            "game_type" => '3',
                            "game_id" => $row['id'],
                            "img" => $row['banner'],
                            "is_hot" => 1,
                            "is_new" => 1,
                            "name" => $row['game_name'],
                            "sorting" => 99,
                            "vn_alias" => "Hổ May Mắn",
                            "prefix" => "f51",
                            "game_code" => "",
                            "updated_at" => 0,
                            "updated_name" => "",
                            "currency" => "BRL",
                            "is_recommend" => 1,
                            "maintained" => 0,
                            "min_admission" => 1,
                            "is_lobby" => 0,
                            "hot_sort" => 99,
                            "is_favorites" => $isFavorite
                        ];
                    }

                    // Debug: Verifica quantos jogos foram retornados
                    error_log("Número de jogos retornados: " . count($games_data));

                    //$games_data_new = [];
                    //if ($page !== 1) {
                    //    // Remove o primeiro item do array e o adiciona ao array $games_data_new
                    //    $games_data_new[] = array_shift($games_data);
                    //} else {
                    //    // Caso contrário, apenas mescla todo o array $games_data
                    //    $games_data_new = array_merge($games_data_new, $games_data);
                    //}

                    $response = [
                        "status" => true,
                        "data" => [
                            "d" => $games_data,  // Atualizando para o games_data correto
                            "t" => $totalRecords, // Total de registros encontrados
                        ],
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "msg" => "Nenhum jogo encontrado",
                    ];
                }
            } else {
                $response = [
                    "status" => false,
                    "msg" => "Erro na consulta SQL para obter os jogos.",
                ];
            }

            $mysqli->close();

            echo json_encode($response, JSON_UNESCAPED_SLASHES);
            exit;
        }
        // Rota Promo/List
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/promo/list') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    [
                        "static" => [
                            "list_web" => "/image/1720065549566..webp",
                            "list_h5" => "/image/banner1.png",
                            "title_web" => "/image/1705398003881.webp",
                            "title_h5" => "/image/1705398015397.webp",
                            "share_h5" => "/",
                        ],
                        "id" => "17395548563955151",
                        "title" => "Recomende amigos e ganhe bônus",
                        "state" => 2,
                        "flag" => "invite",
                        "grade" => "1001,1002,1003,1004,1005,1006,1007,1008,35,34,47",
                        "login_af" => 0,
                        "login_bf" => 0,
                    ],
                    [
                        "static" => [
                            "list_web" => "/image/1720065785599..webp",
                            "list_h5" => "/image/banner2.png",
                            "title_web" => "/image/1712836894182..webp",
                            "title_h5" => "/image/1712836902055..webp",
                            "share_h5" => "",
                            "display_mode" => 2,
                            "link_url" => "/missao",
                        ],
                        "id" => "12117535491806041",
                        "title" => "telegram.VIP",
                        "state" => 2,
                        "flag" => "static",
                        "grade" => "47,35,34,1002,1003,1004,1005,1006,1007,1008,1001,45,2025",
                        "login_af" => 0,
                        "login_bf" => 0,
                    ],
                    [
                        "static" => [
                            "list_web" => "/image/1720065571216..webp",
                            "list_h5" => "/image/banner3.png",
                            "title_web" => "/image/1720065575374..webp",
                            "title_h5" => "/image/1720065588603..webp",
                            "share_h5" => "/",
                            "display_mode" => 2,
                            "link_url" => "/activity/recommend-friends",
                        ],
                        "id" => "17395548563954431",
                        "title" => "Primeiro bônus de recarga para novos membros",
                        "state" => 2,
                        "flag" => "deposit",
                        "grade" => "1001,1002,1003,1004,1005,1006,1007,1008,35,34,47,2024,2025",
                        "login_af" => 0,
                        "login_bf" => 0,
                    ],
                    /* [
                        "static" => [
                            "list_web" => "/image/1720065659395..webp",
                            "list_h5" => "/image/1720065667276..webp",
                            "title_web" => "/image/1720065663588..webp",
                            "title_h5" => "/image/1720065672031..webp",
                            "share_h5" => "",
                            "display_mode" => 1,
                        ],
                        "id" => "12052671887318748",
                        "title" => "Anúncio VIP",
                        "state" => 2,
                        "flag" => "static",
                        "grade" => ",47,34,35,1001,1002,1003,1004,1005,1007,1008,1006,2025,45",
                        "login_af" => 0,
                        "login_bf" => 0,
                    ], */
                    [
                        "static" => [
                            "list_web" => "/image/1720065706287..webp",
                            "list_h5" => "/image/banner4.png",
                            "title_web" => "/image/1712836040998..webp",
                            "title_h5" => "/image/1712836046062..webp",
                            "share_h5" => "",
                            "display_mode" => 2,
                            "link_url" => "/activity/recommend-friends",
                        ],
                        "id" => "12093654993918560",
                        "title" => "especialista em telégrafo",
                        "state" => 2,
                        "flag" => "static",
                        "grade" => ",35,47,1001,1002,1003,1004,1005,1006,1007,1008,45,2025,34",
                        "login_af" => 0,
                        "login_bf" => 0,
                    ],/* 
                [
                    "static" => [
                        "list_web" => "/image/1720065724874..webp",
                        "list_h5" => "/image/1720065732475..webp",
                        "title_web" => "/image/1720065728622..webp",
                        "title_h5" => "/image/1720065736240..webp",
                        "share_h5" => "",
                        "display_mode" => 1,
                    ],
                    "id" => "7589294108015554",
                    "title" => "Bônus misterioso",
                    "state" => 2,
                    "flag" => "static",
                    "grade" => "35,34,1001,1002,1003,1004,1005,1006,1007,1008,47,45,2025",
                    "login_af" => 0,
                    "login_bf" => 0,
                ], */
                    [
                        "static" => [
                            "list_web" => "/image/1720065785599..webp",
                            "list_h5" => "/image/banner5.png",
                            "title_web" => "/image/1712836894182..webp",
                            "title_h5" => "/image/1712836902055..webp",
                            "share_h5" => "",
                            "display_mode" => 2,
                            "link_url" => "/missao",
                        ],
                        "id" => "12117535491806041",
                        "title" => "telegram.VIP",
                        "state" => 2,
                        "flag" => "static",
                        "grade" => "47,35,34,1002,1003,1004,1005,1006,1007,1008,1001,45,2025",
                        "login_af" => 0,
                        "login_bf" => 0,
                    ],
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Notices
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/notices') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [],
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Player/list
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/player/list') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    "d" => [
                        [
                            "id" => "1", // ADICIONE SEMPRE O PROXIMO NUMERO. EX: ID ANTERIOR 266, NESSE ID COLOCA 267
                            "music_name" => "You Spin Me Round", // NOME DA MUSICA
                            "size" => "163000", // TAMANHO DA MUSICA, COLOCA UM TAMANHO APROXIMADO EM BYTES. 367000 BYTES DA UNS 3,67MB
                            "src" => "/br-music/siteadmin_upload_music_You+Spin+Me+Round.mp3", // CAMINHO DE ONDE ESTA O ARQUIVO DA MUSICA
                            "sort" => 0,
                            "status" => 0,
                            "update_name" => "",
                            "update_at" => 0,
                            "create_at" => 0,
                            // RESTO É SÓ PRA ENCHER LINGUIÇA
                        ],
                        [
                            "id" => "2",
                            "music_name" => "Se Mordendo De Raiva",
                            "size" => "167000",
                            "src" => "/br-music/se mordendo de raiva.mp3", // CAMINHO DE ONDE ESTA O ARQUIVO DA MUSICA
                            "sort" => 0,
                            "status" => 0,
                            "update_name" => "",
                            "update_at" => 0,
                            "create_at" => 0,
                        ],
                        [
                            "id" => "3", // ID 2
                            "music_name" => "Yesterday-The Beatles",
                            "size" => "367000",
                            "src" => "/br-music/yesterday.mp3",
                            "sort" => 0,
                            "status" => 0,
                            "update_name" => "",
                            "update_at" => 0,
                            "create_at" => 0,
                        ],
                        [
                            "id" => "4", // ID 2
                            "music_name" => "See You Again-Wiz+Khalifa",
                            "size" => "362000",
                            "src" => "/br-music/seeyou.mp3",
                            "sort" => 0,
                            "status" => 0,
                            "update_name" => "",
                            "update_at" => 0,
                            "create_at" => 0,
                        ],
                        [
                            "id" => "5", // ID 2
                            "music_name" => "Without You-Mariah Carey",
                            "size" => "92000",
                            "src" => "/br-music/mariah.mp3",
                            "sort" => 0,
                            "status" => 0,
                            "update_name" => "",
                            "update_at" => 0,
                            "create_at" => 0,
                        ],
                        [
                            "id" => "6", // ID 2
                            "music_name" => "Live It Up",
                            "size" => "317000",
                            "src" => "/br-music/live.mp3",
                            "sort" => 0,
                            "status" => 0,
                            "update_name" => "",
                            "update_at" => 0,
                            "create_at" => 0,
                        ],
                        [
                            "id" => "7", // ID 2
                            "music_name" => "Waiting for Love",
                            "size" => "351000",
                            "src" => "/br-music/love.mp3",
                            "sort" => 0,
                            "status" => 0,
                            "update_name" => "",
                            "update_at" => 0,
                            "create_at" => 0,
                        ],
                        [
                            "id" => "8", // ID 2
                            "music_name" => "Wait Wait Wait",
                            "size" => "321000",
                            "src" => "/br-music/wait.mp3",
                            "sort" => 0,
                            "status" => 0,
                            "update_name" => "",
                            "update_at" => 0,
                            "create_at" => 0,
                        ],
                        [
                            "id" => "9", // ID 2
                            "music_name" => "Victory-anonymous",
                            "size" => "495000",
                            "src" => "/br-music/victory.mp3",
                            "sort" => 0,
                            "status" => 0,
                            "update_name" => "",
                            "update_at" => 0,
                            "create_at" => 0,
                        ],
                        [
                            "id" => "10", // ID 2
                            "music_name" => "The Nights(Remix)",
                            "size" => "295000",
                            "src" => "/br-music/remix.mp3",
                            "sort" => 0,
                            "status" => 0,
                            "update_name" => "",
                            "update_at" => 0,
                            "create_at" => 0,
                        ],
                    ],
                    "t" => 8,
                    "config" => [
                        "player_switch" => 1,
                        "player_autoplay" => 0,
                    ],
                ],
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Marquee
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/marquee') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota App/Upgrade
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/app/upgrade') {
            // Captura a query string
            $queryString = parse_url($requestURI, PHP_URL_QUERY);
            parse_str($queryString, $queryParams);

            // Verifica se a query string contém a chave 'dv'
            if (isset($queryParams['dv']) && $queryParams['dv'] == 35) {
                $rotaEncontrada = true; // Rota encontrada

                // Aqui você pode definir a resposta como antes
                $response = [
                    "status" => true,
                    "data" => [
                        "id" => "",
                        "platform" => "android",
                        "version" => $dataconfig['versao_app_android'],
                        "is_force" => 0,
                        "content" => "
                 1 Otimização de campanha
                 ",
                        "url" => $dataconfig['link_app_android'],
                        "updated_at" => 0,
                        "updated_uid" => "",
                        "updated_name" => "",
                        "prefix" => "",
                        "model_type" => 0,
                    ],
                ];

                // Use JSON_UNESCAPED_UNICODE to avoid escaping Unicode characters
                $response_json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo $response_json;
            }
            // Verifica se a query string contém a chave 'dv'
            if (isset($queryParams['dv']) && $queryParams['dv'] == 36) {
                $rotaEncontrada = true; // Rota encontrada

                // Aqui você pode definir a resposta como antes
                $response = [
                    "status" => true,
                    "data" => [
                        "id" => "",
                        "platform" => "ios",
                        "version" => $dataconfig['versao_app_ios'],
                        "is_force" => 0,
                        "content" => "
                    1 Otimização de campanha
                    ",
                        "url" => $dataconfig['link_app_ios'],
                        "updated_at" => 0,
                        "updated_uid" => "",
                        "updated_name" => "",
                        "prefix" => "",
                        "model_type" => 0,
                    ],
                ];

                // Use JSON_UNESCAPED_UNICODE to avoid escaping Unicode characters
                $response_json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo $response_json;
            }
        }
        // Rota Member/Customer/List
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/customer/list') {
            // Captura a query string
            $queryString = parse_url($requestURI, PHP_URL_QUERY);
            parse_str($queryString, $queryParams);

            // Verifica se a query string contém a chave 'dv'
            if (isset($queryParams['flag']) && $queryParams['flag'] == 2) {
                $rotaEncontrada = true; // Rota encontrada

                // Aqui você pode definir a resposta como antes
                $response = [
                    "status" => true,
                    "data" => [
                        [
                            "id" => "764031310417011112",
                            "imId" => "20",
                            "im" => "/image/1f19575d-85e2-43ef-a6e1-84839311c8c2.png",
                            "name" => "telegram",
                            "link" => "https://t.me/Gestor_Jing",
                            "remark" => "",
                            "flag" => 2,
                            "sort" => 1,
                            "status" => 2,
                            "method" => 0,
                            "createdAt" => 1712838327,
                            "updatedAt" => 1720066187,
                        ],
                    ],
                    "msg" => null,
                ];

                // Use JSON_UNESCAPED_UNICODE to avoid escaping Unicode characters
                $response_json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo $response_json;
            }

            // Verifica se a query string contém a chave 'dv'
            if (isset($queryParams['flag']) && $queryParams['flag'] == 1) {
                $rotaEncontrada = true; // Rota encontrada

                // Aqui você pode definir a resposta como antes
                $response = [
                    "status" => true,
                    "data" => [
                        [
                            "id" => "4",
                            "title" => "Line  Suporte",
                            "im" => "/image/1708679618427.webp",
                            "flag" => 1,
                            "sort" => 3,
                            "createdAt" => 0,
                            "updatedAt" => 0,
                            "items" => [
                                [
                                    "id" => "481783378761001989",
                                    "imId" => "4",
                                    "im" => "/image/1720066136856..webp",
                                    "name" => "Apoio online 24/7",
                                    "link" => "https://t.me/Gestor_Jing",
                                    "remark" => "chat",
                                    "flag" => 1,
                                    "sort" => 0,
                                    "status" => 2,
                                    "method" => 0,
                                    "createdAt" => 0,
                                    "updatedAt" => 1721718218,
                                ],
                            ],
                        ],
                    ],
                    "msg" => null,
                ];

                // Use JSON_UNESCAPED_UNICODE to avoid escaping Unicode characters
                $response_json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo $response_json;
            }
            // Verifica se a query string contém a chave 'dv'
            if (isset($queryParams['flag']) && $queryParams['flag'] == 3) {
                $rotaEncontrada = true; // Rota encontrada

                // Aqui você pode definir a resposta como antes
                $response = [
                    "status" => true,
                    "data" => [
                        [
                            "id" => "11",
                            "title" => "Telegram Suporte",
                            "im" => "/image/1708679594041.webp",
                            "flag" => 3,
                            "sort" => 2,
                            "createdAt" => 0,
                            "updatedAt" => 0,
                            "items" => [
                                [
                                    "id" => "168772440975260479",
                                    "imId" => "11",
                                    "im" => "/image/1708679594041.webp",
                                    "name" => "telegram",
                                    "link" => "https://t.me/Gestor_Jing",
                                    "remark" => "telegram",
                                    "flag" => 3,
                                    "sort" => 1,
                                    "status" => 2,
                                    "method" => 0,
                                    "createdAt" => 1710255728,
                                    "updatedAt" => 1720066193,
                                ],
                            ],
                        ],
                    ],
                    "msg" => null,
                ];

                // Use JSON_UNESCAPED_UNICODE to avoid escaping Unicode characters
                $response_json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo $response_json;
            }
        }
        // Rota Member/Point/Statistics
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/point/statistics') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    "facebook" => "",
                    "kwai" => "",
                    "tiktok" => "",
                    "google" => "",
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Rebate/Config
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/rebate/config') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    [
                        "id" => 3117973535451604,
                        "game_type" => 3,
                        "bet_amount" => 0,
                        "rebate_amount" => 0.1,
                        "ty" => 1,
                    ],
                    [
                        "id" => 3118183146219625,
                        "game_type" => 3,
                        "bet_amount" => 1,
                        "rebate_amount" => 0.15,
                        "ty" => 1,
                    ],
                    [
                        "id" => 3118402711650122,
                        "game_type" => 3,
                        "bet_amount" => 5,
                        "rebate_amount" => 0.18,
                        "ty" => 1,
                    ],
                    [
                        "id" => 3118603221577640,
                        "game_type" => 3,
                        "bet_amount" => 10,
                        "rebate_amount" => 0.2,
                        "ty" => 1,
                    ],
                    [
                        "id" => 3118811288439380,
                        "game_type" => 3,
                        "bet_amount" => 50,
                        "rebate_amount" => 0.3,
                        "ty" => 1,
                    ],
                    [
                        "id" => 3119007217308780,
                        "game_type" => 3,
                        "bet_amount" => 100,
                        "rebate_amount" => 0.4,
                        "ty" => 1,
                    ],
                    [
                        "id" => 3119159847745440,
                        "game_type" => 3,
                        "bet_amount" => 200,
                        "rebate_amount" => 0.6,
                        "ty" => 1,
                    ],
                    [
                        "id" => 3119305445801466,
                        "game_type" => 3,
                        "bet_amount" => 500,
                        "rebate_amount" => 1,
                        "ty" => 1,
                    ],
                    [
                        "id" => 3119507707122527,
                        "game_type" => 3,
                        "bet_amount" => 1000,
                        "rebate_amount" => 1.5,
                        "ty" => 1,
                    ],
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Info & Member/Short/Info
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/info' || parse_url($requestURI, PHP_URL_PATH) === '/api/member/short/info') {
            $rotaEncontrada = true; // Rota encontrada
            // Verifica se o cookie 'token_user' está definido e não está vazio
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $token = mysqli_real_escape_string($mysqli, $_COOKIE['token_user']);
                $qry = "SELECT * FROM usuarios WHERE token='$token'";
                $resp = mysqli_query($mysqli, $qry);





                if (mysqli_num_rows($resp) > 0) {
                    $datres = mysqli_fetch_assoc($resp);

                    $qryhp = "SELECT SUM(bet_money) AS apostas FROM historico_play WHERE id_user = ?";
                    $stmthp = $mysqli->prepare($qryhp);

                    if ($stmthp) {
                        $stmthp->bind_param("i", $datres['id']);
                        $stmthp->execute();
                        $resphp = $stmthp->get_result();

                        $apostas = $resphp->fetch_assoc()['apostas'] ?? 0;

                        $stmthp->close();
                    } else {
                        $apostas = 0; // Define um valor padrão caso a preparação falhe
                    }

                    $level = 0;
                    $next = 10000;
                    $ammounts = [10000, 30000, 100000, 300000, 600000, 1000000, 3000000, 5000000, 7000000, 10000000, 13000000, 16000000, 20000000, 100000000];

                    foreach ($ammounts as $key => $amount) {
                        if ($next <= $apostas) {
                            $level = $key;
                            $next = $amount;
                        } else {
                            break; // Como o array está em ordem crescente, podemos parar quando encontrar o primeiro maior
                        }
                    }

                    $qryinvit = "SELECT * FROM usuarios WHERE invite_code=?";
                    $stmtinvit = $mysqli->prepare($qryinvit);
                    $stmtinvit->bind_param("s", $datres['invitation_code']);
                    $stmtinvit->execute();
                    $parent = $stmtinvit->get_result();
                    $parent = $parent->fetch_assoc();

                    $qrytdep = "SELECT SUM(valor) as totaldep FROM transacoes WHERE tipo = 'deposito' AND status = 'pago' AND usuario = ?";
                    $stmttdep = $mysqli->prepare($qrytdep);
                    $stmttdep->bind_param("i", $datres['id']);
                    $stmttdep->execute();
                    $resultdep = $stmttdep->get_result();
                    $totaldep = $resultdep->fetch_assoc();
                    $totaldep = $totaldep['totaldep'] ?? 0; // Garante que $totaldep seja 0 caso a consulta não retorne resultados

                    $qrytwith = "SELECT SUM(valor) as totalwith FROM solicitacao_saques WHERE status = 1 AND id_user = ?";
                    $stmttwith = $mysqli->prepare($qrytwith);
                    $stmttwith->bind_param("i", $datres['id']);
                    $stmttwith->execute();
                    $resultwith = $stmttwith->get_result();
                    $totalwith = $resultwith->fetch_assoc();
                    $totalwith = $totalwith['totalwith'] ?? 0; // Garante que $totalwith seja 0 caso a consulta não retorne resultados

                    $userData = array(
                        "status" => true,
                        "data" => [
                            "uid" => $datres['id'],
                            "username" => $datres['mobile'],
                            "password" => "0",
                            "birth" => strtotime($datres['birth']),
                            "realname" => "",
                            "email" => $datres['email'],
                            "phone" => "*******",
                            "zalo" => "",
                            "prefix" => "f51",
                            "tester" => "1",
                            "withdraw_pwd" => 0,
                            "regip" => "2804:15fc:1013:7601:d9b5:26b6:ded1:f82a",
                            "reg_device" => "xbwrlskpkz4b67ygeadbpj08hkssujif",
                            "reg_url" => "/?id=205158614",
                            "created_at" => 1725613453,
                            "last_login_ip" => "2804:15fc:1013:7601:d9b5:26b6:ded1:f82a",
                            "last_login_at" => 1725618194,
                            "source_id" => 1,
                            "first_deposit_at" => 0,
                            "first_deposit_amount" => "0.000",
                            "first_bet_at" => 0,
                            "first_bet_amount" => "0.000",
                            "second_deposit_at" => 0,
                            "second_deposit_amount" => "0.000",
                            "top_uid" => "174474690",
                            "top_name" => "softbet",
                            "parent_uid" => isset($parent['id']) ? $parent['id'] : "1234567890",
                            "parent_name" => isset($parent['mobile']) ? $parent['mobile'] : "softbet",
                            "bankcard_total" => 0,
                            "last_login_device" => "y4670ybncamy9l2mf59zvzlgme50cwaj",
                            "last_login_source" => 24,
                            "remarks" => "",
                            "state" => 1,
                            "level" => $level,
                            "balance" => $datres['saldo'],
                            "lock_amount" => "0.0000",
                            "commission" => "0.0000",
                            "group_name" => "softbet",
                            "agency_type" => 391,
                            "address" => "",
                            "avatar" => $datres['avatar'],
                            "last_withdraw_at" => "0",
                            "automatic" => 1,
                            "facebook" => $datres['facebook'],
                            "whatsapp" => $datres['whatsapp'],
                            "telegram" => $datres['telegram'],
                            "twitter" => $datres['twitter'],
                            "referer" => "",
                            "link_id" => "",
                            "device" => 0,
                            "fphone" => "",
                            "total_dept_amount" => $totaldep,
                            "total_wdraw_amount" => $totalwith,
                            "link_black_list" => 0,
                            "next" => $next,
                            "now" => $apostas,
                            "rate" => "0.00000",
                            "next_level" => $level + 1,
                            "rebate_amount" => "",
                            "agency_amount" => "",
                            "token" => $datres['token'],
                        ],
                        "msg" => null,
                    );

                    // Converte o array associativo para JSON
                    echo json_encode($userData);
                } else {
                    $response = [
                        "code" => 0, // Indica falha
                        "msg" => "Usuário sem efetuar login", // Mensagem de erro
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Token não encontrado ou inválido",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Member/Message/num
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/message/num') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => 0,
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Password/Check
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/password/check') {
            $rotaEncontrada = true; // Rota encontrada
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {

                $qry = "SELECT * FROM usuarios WHERE token=?";
                $stmt = $mysqli->prepare($qry);
                $stmt->bind_param("s", $_COOKIE['token_user']);
                $stmt->execute();
                $resp = $stmt->get_result();

                if ($resp->num_rows > 0) {
                    $datares = $resp->fetch_assoc();

                    // Verificação do campo senha_saque
                    if (isset($datares['senha_saque'])) {
                        if ($datares['senha_saque'] == 1) {
                            $response = [
                                "status" => true,
                                "data" => "1000",
                                "msg" => null,
                            ];
                        } else {
                            $response = [
                                "status" => true,
                                "data" => "1249",
                                "msg" => null,
                            ];
                        }
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response = [
                        "code" => 0, // Falha
                        "msg" => "Usuário sem efetuar login",
                        "time" => time(),
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Usuário ou senha incorretos",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Member/Link/list
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/link/list') {
            $rotaEncontrada = true; // Rota encontrada
            // Verifica se o cookie 'token_user' está definido e não está vazio
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $token = mysqli_real_escape_string($mysqli, $_COOKIE['token_user']);
                $qry = "SELECT * FROM usuarios WHERE token='$token'";
                $resp = mysqli_query($mysqli, $qry);

                if (mysqli_num_rows($resp) > 0) {
                    $datres = mysqli_fetch_assoc($resp);
                    $userData = [
                        "status" => true,
                        "data" => [
                            [
                                "id" => $datres["id"],
                                "uid" => $datres["id"],
                                "username" => $datres["mobile"],
                                "short_url" => "id=" . $datres["invite_code"],
                                "code" => $datres["invite_code"],
                                "prefix" => "",
                                "created_at" => "",
                                "tester" => "",
                            ],
                        ],
                        "msg" => null,
                    ];

                    // Converte o array associativo para JSON
                    echo json_encode($userData);
                } else {
                    $response = [
                        "code" => 0, // Indica falha
                        "msg" => "Usuário sem efetuar login", // Mensagem de erro
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = [
                    "code" => 0,
                    "data" => null,
                    "msg" => "Token não encontrado ou inválido",
                    "time" => time(),
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Rota Promo/Promo/Wait/Pick
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/promo/promo/wait/pick') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    "d" => null,
                    "agg" => "0",
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Point/Statistics/Deposit
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/point/statistics/deposit') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Finance/Channel/type
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/finance/channel/type') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    [
                        "id" => "8",
                        "name" => "PIX",
                        "alias" => "PIX",
                        "state" => 1,
                        "sort" => 100,
                        "flow_multiple" => 1,
                    ],
                ],
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Finance/Channel/list
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/finance/channel/list') {
            $rotaEncontrada = true; // Rota encontrada
            // Obter a configuração do banco de dados (substitua pela consulta real para buscar dataconfig['mindep'])
            $dataconfig_query = "SELECT mindep FROM config WHERE id = 1"; // Exemplo de consulta
            $dataconfig_result = mysqli_query($mysqli, $dataconfig_query);
            $dataconfig = mysqli_fetch_assoc($dataconfig_result);

            // Verificar se existe a configuração e separá-la por vírgula
            $mindep = isset($dataconfig['mindep']) ? explode(',', $dataconfig['mindep']) : [];
            $fmin = explode(',', $dataconfig['mindep']);
            $fmin = $fmin[0];
            // Construir a lista de bônus com base nos valores de mindep
            $bonus_list = [];
            foreach ($mindep as $deposit_amount) {
                $depositValue = (int) $deposit_amount;
                $bonusAmount = $depositValue > 20 ? $depositValue : (int) ($depositValue * 0.1);

                $bonus_list[] = [
                    "deposit_amount" => $depositValue,
                    "bonus_amount" => $bonusAmount,
                    "rate" => 0,
                ];
            }

            // Resposta com os dados, substituindo a lista de bônus por aquela baseada em 'mindep'
            $response = [
                "status" => true,
                "data" => [
                    [
                        "id" => "1600",
                        "factory_id" => "30",
                        "channel_type_id" => "8",
                        "show_name" => "PIX7",
                        "fmin" => $fmin,
                        "fmax" => 50000,
                        "amount_list" => implode(',', $mindep), // Exemplo de uso de mindep para amount_list
                        "state" => "1",
                        "sort" => 1,
                        "comment" => "owen代收",
                        "vip_list" => "1,2,3,4,5,6,7,8,9,10",
                        "discount" => "0.00",
                        "created_at" => 1,
                        "updated_at" => 1725668816,
                        "is_zone" => 1,
                        "is_fast" => 1,
                        "is_rang" => 1,
                        "web_img" => "1",
                        "h5_img" => "1",
                        "app_img" => "1",
                        "daily_max_amount" => 9999999,
                        "daily_finish_amount" => 0,
                        "flag" => 1,
                        "third_code" => "BRL002",
                        "factory_name" => "",
                        "bonus_list" => $bonus_list, // Substitui pelo bônus baseado em mindep
                        "balance" => "",
                        "grade_list" => "2025,1009,48,47,46,44,43,42,41,40,39,38,37,36,35,34,1001,1002,1003,1004,1005,1006,1007,1008",
                        "list" => '[{"max": 0, "min": 0, "flag": 1, "rate": "", "level": "2025,1009,47,46,48,44,43,42,41,40,39,38,37,36,35,34,1003,1004,2026,1001,1005,1006,1007,1008,1002", "tagsArr": ["2025", "1009", "47", "46", "48", "44", "43", "42", "41", "40", "39", "38", "37", "36", "35", "34", "1003", "1004", "2026", "1001", "1005", "1006", "1007", "1008", "1002"]}]',
                        "crowd" => 0,
                        "deposit" => 0,
                    ],
                    [
                        "id" => "1502",
                        "factory_id" => "600",
                        "channel_type_id" => "8",
                        "show_name" => "PIX1",
                        "fmin" => $fmin * 2,
                        "fmax" => 50000,
                        "amount_list" => "10,30,50,100,500,1000,3000,5000,10000,20000,50000",
                        "state" => "1",
                        "sort" => 2,
                        "comment" => "pagopay代收",
                        "vip_list" => "1,2,3,4,5,6,7,8,9,10",
                        "discount" => "0.00",
                        "created_at" => 1,
                        "updated_at" => 1725668832,
                        "is_zone" => 1,
                        "is_fast" => 1,
                        "is_rang" => 1,
                        "web_img" => "1",
                        "h5_img" => "",
                        "app_img" => "",
                        "daily_max_amount" => 9999999,
                        "daily_finish_amount" => 0,
                        "flag" => 1,
                        "third_code" => "",
                        "factory_name" => "",
                        "bonus_list" => $bonus_list, // Substitui pelo bônus baseado em mindep
                        "balance" => "",
                        "grade_list" => "2025,1009,48,47,46,44,43,42,41,40,39,38,37,36,35,34,1001,1002,1003,1004,1005,1006,1007,1008",
                        "list" => '[{"max": 0, "min": 0, "flag": 1, "rate": "", "level": "2025,1009,48,47,46,44,43,42,41,40,39,38,37,36,35,34,1001,2026,1003,1004,1005,1007,1006,1008,1002", "tagsArr": ["2025", "1009", "48", "47", "46", "44", "43", "42", "41", "40", "39", "38", "37", "36", "35", "34", "1001", "2026", "1003", "1004", "1005", "1007", "1006", "1008", "1002"]}]',
                        "crowd" => 0,
                        "deposit" => 0,
                    ],
                ],
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Record/Trade
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/record/trade') {
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                // Supondo que a variável $mysqli é a conexão com o banco de dados já existente

                // Obter o token do usuário
                $token_user = $_COOKIE['token_user'];

                // Captura a query string
                $queryString = parse_url($requestURI, PHP_URL_QUERY);
                parse_str($queryString, $queryParams);

                // Verifica se a query string contém a chave 'dv'
                if (isset($queryParams['flag']) && $queryParams['flag'] == 272) {
                    // Consultar o banco de dados para obter o ID do usuário associado ao token
                    $qry = "SELECT * FROM usuarios WHERE token=?";
                    $stmt = $mysqli->prepare($qry);
                    $stmt->bind_param("s", $token_user);
                    $stmt->execute();
                    $result_user = $stmt->get_result();

                    if ($result_user->num_rows > 0) {
                        $user = $result_user->fetch_assoc();
                        $user_id = $user['id'];

                        // Consultar os saques do usuário específico
                        $sql = "SELECT * FROM solicitacao_saques WHERE id_user = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result) {
                            if ($result->num_rows > 0) {
                                // Armazenar os dados dos saques em um array
                                $saques = [];
                                //var_dump($result->fetch_assoc());
                                while ($row = $result->fetch_assoc()) {
                                    // Determinar o valor de "state" com base no "status"
                                    $state = 0;

                                    if ((int) $row['status'] === 0) {
                                        $state = 371;
                                    } elseif ((int) $row['status'] === 1) {
                                        $state = 374;
                                    }

                                    $chaveph = localizarchavepix($row['pix']);

                                    // Filtrar os dados retornados para incluir apenas os campos desejados
                                    $saques[] = [
                                        "flag" => 271,
                                        "id" => $row['id'], // Substitua com o campo correspondente
                                        "ty" => 1,
                                        // Exibir apenas os primeiros 12 caracteres do transacao_id
                                        "bill_no" => substr($row['transacao_id'], 0, 12),
                                        "platform_id" => "",
                                        "transfer_type" => 1,
                                        "amount" => $row['valor'], // Substitua com o campo correspondente
                                        "created_at" => $row['data_cad'] . " " . $row['data_hora'], // Substitua com o campo correspondente
                                        "state" => $state, // Estado com base no status
                                        "remark" => $chaveph, // Substitua com o campo correspondente
                                        "ptitle" => "",
                                        "username" => $user['mobile'], // Substitua com o campo correspondente
                                        "parent_name" => $user['invitation_code'] || 'mcbsoftbet', // Substitua com o campo correspondente
                                        "balance" => "",
                                        "channel_id" => "8",
                                        "channel_name" => "PIX",
                                        "pay_name" => "PIX7",
                                        "real_name" => $user['mobile'], // Substitua com o campo correspondente
                                        "account" => "",
                                        "updated_at" => $row['data_att'] ?? 0,
                                        "ramount" => "",
                                        "discount" => "",
                                        "bank_ty" => 0,
                                        "channel_type_name" => "PIX",
                                    ];
                                }

                                // Preparar a resposta com os dados coletados e o número total de linhas
                                $response = [
                                    "status" => true,
                                    "data" => [
                                        "t" => count($saques), //$result->num_rows, // Número total de rows
                                        "d" => $saques, //$saques,
                                        "s" => 0,
                                        "agg" => null,
                                    ],
                                    "msg" => null,
                                ];
                            } else {
                                $response = [
                                    "status" => true,
                                    "data" => [
                                        "t" => 0, // Sem transações
                                        "d" => null,
                                        "s" => 0,
                                        "agg" => null,
                                    ],
                                    "msg" => null,
                                ];
                            }
                        } else {
                            // Erro na execução da consulta
                            $response = [
                                "status" => false,
                                "data" => [
                                    "t" => 0, // Sem transações
                                    "d" => null,
                                    "s" => 0,
                                    "agg" => null,
                                ],
                                "msg" => "Erro ao consultar a tabela de transações.",
                            ];
                        }
                    } else {
                        // Usuário não encontrado para o token fornecido
                        $response = [
                            "status" => false,
                            "message" => "Usuário não encontrado para o token fornecido.",
                        ];
                    }

                    $stmt->close();
                    // Exibir a resposta em formato JSON
                    $response_json = json_encode($response, JSON_PRETTY_PRINT);
                    echo $response_json;
                    exit;
                }
                if (isset($queryParams['ty']) && $queryParams['ty'] == 0) {
                    // Consultar o banco de dados para obter o ID do usuário associado ao token
                    $qry = "SELECT * FROM usuarios WHERE token=?";
                    $stmt = $mysqli->prepare($qry);
                    $stmt->bind_param("s", $token_user);
                    $stmt->execute();
                    $result_user = $stmt->get_result();

                    if ($result_user->num_rows > 0) {
                        $user = $result_user->fetch_assoc();
                        $user_id = $user['id'];

                        // Consultar os saques do usuário específico
                        $sql = "SELECT * FROM transacoes WHERE usuario = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result) {
                            if ($result->num_rows > 0) {
                                // Armazenar os dados dos saques em um array
                                $saques = [];
                                while ($row = $result->fetch_assoc()) {
                                    // Determinar o valor de "state" com base no "status"
                                    $state = 0;
                                    if ($row['status'] === 'processamento') {
                                        $state = 361;
                                    } elseif ($row['status'] === 'pago') {
                                        $state = 362;
                                    }

                                    // Filtrar os dados retornados para incluir apenas os campos desejados
                                    $saques[] = [
                                        "flag" => 271,
                                        "id" => $row['id'], // Substitua com o campo correspondente
                                        "ty" => 1,
                                        // Exibir apenas os primeiros 12 caracteres do transacao_id
                                        "bill_no" => substr($row['transacao_id'], 0, 12),
                                        "platform_id" => "",
                                        "transfer_type" => 1,
                                        "amount" => $row['valor'], // Substitua com o campo correspondente
                                        "created_at" => $row['data_hora'], // Substitua com o campo correspondente
                                        "state" => $state, // Estado com base no status
                                        "remark" => $row['tipo'], // Substitua com o campo correspondente
                                        "ptitle" => "",
                                        "username" => $user['mobile'], // Substitua com o campo correspondente
                                        "parent_name" => $user['invitation_code'] || 'mcbsoftbet', // Substitua com o campo correspondente
                                        "balance" => "",
                                        "channel_id" => "8",
                                        "channel_name" => "PIX",
                                        "pay_name" => "PIX7",
                                        "real_name" => $user['mobile'], // Substitua com o campo correspondente
                                        "account" => "",
                                        "updated_at" => 0,
                                        "ramount" => "",
                                        "discount" => "",
                                        "bank_ty" => 0,
                                        "channel_type_name" => "PIX",
                                    ];
                                }

                                // Preparar a resposta com os dados coletados e o número total de linhas
                                $response = [
                                    "status" => true,
                                    "data" => [
                                        "t" => count($saques), //$result->num_rows, // Número total de rows
                                        "d" => $saques, //$saques,
                                        "s" => 0,
                                        "agg" => null,
                                    ],
                                    "msg" => null,
                                ];
                            } else {
                                $response = [
                                    "status" => true,
                                    "data" => [
                                        "t" => 0, // Sem transações
                                        "d" => [],
                                        "s" => 0,
                                        "agg" => null,
                                    ],
                                    "msg" => null,
                                ];
                            }
                        } else {
                            // Erro na execução da consulta
                            $response = [
                                "status" => false,
                                "msg" => "Erro ao consultar a tabela de transações.",
                            ];
                        }
                    } else {
                        // Usuário não encontrado para o token fornecido
                        $response = [
                            "status" => false,
                            "msg" => "Usuário não encontrado para o token fornecido.",
                        ];
                    }

                    $stmt->close();
                    // Exibir a resposta em formato JSON
                    $response_json = json_encode($response, JSON_PRETTY_PRINT);
                    echo $response_json;
                    exit;
                }
            } else {
                // Token do usuário não está presente ou é vazio
                $response = [
                    "status" => false,
                    "message" => "Token de usuário inválido ou ausente.",
                ];
                $response_json = json_encode($response, JSON_PRETTY_PRINT);
                echo $response_json;
            }
        }
        // Rota Member/Record/Trade/Detail
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/record/trade/detail') {
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                // Supondo que a variável $mysqli é a conexão com o banco de dados já existente

                // Obter o token do usuário
                $token_user = $_COOKIE['token_user'];

                // Consultar o banco de dados para obter o ID do usuário associado ao token
                $qry = "SELECT * FROM usuarios WHERE token=?";
                $stmt = $mysqli->prepare($qry);
                $stmt->bind_param("s", $token_user);
                $stmt->execute();
                $result_user = $stmt->get_result();

                if ($result_user->num_rows > 0) {
                    $user = $result_user->fetch_assoc();
                    $user_id = $user['id'];
                    $saqueId = $_GET['id'];
                    // Consultar os saques do usuário específico
                    $sql = "SELECT * FROM solicitacao_saques WHERE id = ? AND id_user = ?";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param("ii", $saqueId, $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result) {
                        if ($result->num_rows > 0) {
                            // Armazenar os dados dos saques em um array
                            $saques = [];
                            while ($row = $result->fetch_assoc()) {
                                // Determinar o valor de "state" com base no "status"
                                $state = 0;

                                if ($row['status'] === 0) {
                                    $state = 371;
                                } elseif ($row['status'] === 1) {
                                    $state = 374;
                                }

                                $chaveph = localizarchavepix($row['pix']);

                                // Filtrar os dados retornados para incluir apenas os campos desejados
                                $saques[] = [
                                    "flag" => $state,
                                    "id" => $row['id'], // Substitua com o campo correspondente
                                    "ty" => 1,
                                    // Exibir apenas os primeiros 12 caracteres do transacao_id
                                    "bill_no" => substr($row['transacao_id'], 0, 12),
                                    "platform_id" => "",
                                    "transfer_type" => 1,
                                    "amount" => $row['valor'], // Substitua com o campo correspondente
                                    "created_at" => $row['data_cad'], // Substitua com o campo correspondente
                                    "state" => $state, // Estado com base no status
                                    "remark" => $chaveph, // Substitua com o campo correspondente
                                    "ptitle" => "",
                                    "username" => $user['mobile'], // Substitua com o campo correspondente
                                    "parent_name" => $user['invitation_code'] || 'mcbsoftbet', // Substitua com o campo correspondente
                                    "balance" => "",
                                    "channel_id" => "8",
                                    "channel_name" => "PIX",
                                    "pay_name" => "PIX7",
                                    "real_name" => $user['mobile'], // Substitua com o campo correspondente
                                    "account" => "",
                                    "updated_at" => 0,
                                    "ramount" => "",
                                    "discount" => "",
                                    "bank_ty" => 0,
                                    "channel_type_name" => "PIX",
                                ];
                            }

                            // Preparar a resposta com os dados coletados e o número total de linhas
                            $response = [
                                "status" => true,
                                "data" => [
                                    "t" => $result->num_rows, // Número total de rows
                                    "d" => $saques,
                                ],
                                "s" => 0,
                                "agg" => null,
                                "msg" => null,
                            ];
                        } else {
                            $response = [
                                "status" => true,
                                "data" => [
                                    "t" => 0, // Sem transações
                                    "d" => [],
                                ],
                                "s" => 0,
                                "agg" => null,
                                "msg" => null,
                            ];
                        }
                    } else {
                        // Erro na execução da consulta
                        $response = [
                            "status" => false,
                            "message" => "Erro ao consultar a tabela de transações.",
                        ];
                    }
                } else {
                    // Usuário não encontrado para o token fornecido
                    $response = [
                        "status" => false,
                        "message" => "Usuário não encontrado para o token fornecido.",
                    ];
                }

                $stmt->close();
                // Exibir a resposta em formato JSON
                $response_json = json_encode($response, JSON_PRETTY_PRINT);
                echo $response_json;
            } else {
                // Token do usuário não está presente ou é vazio
                $response = [
                    "status" => false,
                    "message" => "Token de usuário inválido ou ausente.",
                ];
                $response_json = json_encode($response, JSON_PRETTY_PRINT);
                echo $response_json;
            }
        }
        // Rota Member/Bankcard/Pixtypelist
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/bankcard/pixtypelist') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    [
                        "displayName" => "CPF",
                        "ty" => 3,
                        "enable" => true,
                        "num" => 1,
                    ],
                    [
                        "displayName" => "PNONE",
                        "ty" => 2,
                        "enable" => true,
                        "num" => 1,
                    ],
                    [
                        "displayName" => "EMAIL",
                        "ty" => 1,
                        "enable" => true,
                        "num" => 1,
                    ],
                    [
                        "displayName" => "CNPJ",
                        "ty" => 4,
                        "enable" => true,
                        "num" => 1,
                    ],
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Finance/Withdraw/Processing
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/finance/withdraw/processing') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    "id" => "",
                    "bid" => "",
                    "amount" => "",
                    "ramount" => "",
                    "state" => "",
                    "created_at" => "",
                    "min_amount" => $dataconfig['minsaque'],
                    "max_amount" => $dataconfig['maxsaque'],
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/bankcard/list
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/bankcard/list') {
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                // Consulta para obter informações do usuário com base no token
                $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
                $resp = mysqli_query($mysqli, $qry);

                if (mysqli_num_rows($resp) > 0) {
                    $datares = mysqli_fetch_assoc($resp);

                    // Consulta as contas de pagamento do usuário na tabela 'metodos_pagamentos'
                    $paymentQuery = "SELECT * FROM metodos_pagamentos WHERE user_id='" . $datares['id'] . "'";
                    $paymentResult = mysqli_query($mysqli, $paymentQuery);

                    if (mysqli_num_rows($paymentResult) > 0) {
                        $paymentMethods = [];
                        while ($row = mysqli_fetch_assoc($paymentResult)) {
                            $paymentMethods[] = [
                                "id" => $row['id'],
                                "uid" => $datares['id'], // Use o id do usuário para 'uid'
                                "username" => $datares['mobile'],
                                "bank_card" => $row['pix_id'],
                                "created_at" => (int) $row['created_at'], // Certifique-se de que 'created_at' seja retornado como inteiro
                                "state" => (int) $row['state'], // 'state' também como inteiro
                                "updated_at" => (int) $row['created_at'], // 'updated_at' usa o mesmo valor de 'created_at'
                                "realname" => $row['realname'],
                                "content" => $row['pix_account'],
                                "ty" => (int) $row['flag'], // Valor fixo 'ty' como 3
                            ];
                        }

                        // Prepara a resposta no formato JSON solicitado
                        $response = [
                            "status" => true,
                            "data" => $paymentMethods, // Removido array extra para retornar corretamente a lista de métodos de pagamento
                            "msg" => null,
                        ];
                    } else {
                        // Se não houver métodos de pagamento encontrados
                        $response = [
                            "status" => true,
                            "data" => [],
                            "msg" => null,
                        ];
                    }
                } else {
                    // Caso o usuário não seja encontrado no banco de dados
                    $response = [
                        "status" => false,
                        "msg" => "Usuário não encontrado",
                    ];
                }
            } else {
                // Caso o token do usuário não esteja presente ou esteja vazio
                $response = [
                    "status" => false,
                    "msg" => "Usuário ou senha incorretos",
                    "time" => time(),
                ];
            }

            // Envia a resposta em JSON
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Finance/Withdraw/Fee
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/finance/withdraw/fee') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    [
                        "id" => "",
                        "tag_id" => "",
                        "fmin" => 0,
                        "fmax" => 20,
                        "amount" => 0,
                        "flags" => 1,
                        "updated_name" => "",
                        "updated_at" => 0,
                    ],
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/Record/Game
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/record/game') {
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                // Consulta para obter informações do usuário com base no token
                $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
                $resp = mysqli_query($mysqli, $qry);

                if (mysqli_num_rows($resp) > 0) {
                    $datares = mysqli_fetch_assoc($resp);

                    // Consulta as contas de pagamento do usuário na tabela 'metodos_pagamentos'
                    $paymentQuery = "SELECT * FROM historico_play WHERE id_user='" . $datares['id'] . "'";
                    $paymentResult = mysqli_query($mysqli, $paymentQuery);

                    if (mysqli_num_rows($paymentResult) > 0) {
                        $paymentMethods = [];
                        while ($row = mysqli_fetch_assoc($paymentResult)) {
                            $paymentMethods[] = [
                                "bill_no" => $row['txn_id'],
                                "api_type" => $row['txn_id'], // Use o id do usuário para 'uid'
                                "api_types" => $row['txn_id'],
                                "player_name" => $datares['mobile'],
                                "name" => $datares['mobile'], // Certifique-se de que 'created_at' seja retornado como inteiro
                                "net_amount" => (int) $row['bet_money'], // 'state' também como inteiro
                                "bet_time" => $row['created_at'], // 'updated_at' usa o mesmo valor de 'created_at'
                                "game_type" => "3",
                                "bet_amount" => $row['bet_money'],
                                "valid_bet_amount" => (int) $row['bet_money'], // Valor fixo 'ty' como 3
                                "flag" => 1, // Valor fixo 'ty' como 3
                                "play_type" => $row['nome_game'], // Valor fixo 'ty' como 3
                                "prefix" => "f51", // Valor fixo 'ty' como 3
                                "result" => 'gameName:' . $row['nome_game'] . '|Hand number:126', // Valor fixo 'ty' como 3
                                "api_name" => 'Slots', // Valor fixo 'ty' como 3
                                "api_bill_no" => $row['txn_id'], // Valor fixo 'ty' como 3
                                "game_name" => $row['nome_game'], // Valor fixo 'ty' como 3
                            ];
                        }

                        // Prepara a resposta no formato JSON solicitado
                        $response = [
                            "status" => true,
                            "data" => [
                                "t" => count($paymentMethods),
                                "d" => $paymentMethods,
                            ], // Removido array extra para retornar corretamente a lista de métodos de pagamento
                            "msg" => null,
                        ];
                    } else {
                        // Se não houver métodos de pagamento encontrados
                        $response = [
                            "status" => true,
                            "data" => [],
                            "msg" => null,
                        ];
                    }
                } else {
                    // Caso o usuário não seja encontrado no banco de dados
                    $response = [
                        "status" => false,
                        "msg" => "Usuário não encontrado",
                    ];
                }
            } else {
                // Caso o token do usuário não esteja presente ou esteja vazio
                $response = [
                    "status" => false,
                    "msg" => "Usuário ou senha incorretos",
                    "time" => time(),
                ];
            }

            // Envia a resposta em JSON
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota [BlackScript]/launch (Iniciar Jogo)
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/china/launch/') {
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                // Capturar os parâmetros 'id' e 'code' usando $_REQUEST para maior flexibilidade
                $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : 'null';
                $game_type = isset($_REQUEST['id']) ? $_REQUEST['id'] : 'null';

                // Verifica se ambos os parâmetros estão presentes e não estão vazios
                if (!empty($code) && !empty($game_type)) {
                    // Continua com a lógica de buscar o usuário pelo token
                    $qry = "SELECT * FROM usuarios WHERE token='" . mysqli_real_escape_string($mysqli, $_COOKIE['token_user']) . "'";
                    $resp = mysqli_query($mysqli, $qry);

                    if (mysqli_num_rows($resp) > 0) {
                        $datares = mysqli_fetch_assoc($resp);

                        // Chama a função gameprovider com base no code
                        //$provedor = gameprovider($code);


                        // var_dump($game_code, $datares['mobile'], $datares['saldo']);
                        $gameretur = pegarLinkJogo('PROVEDOR', $code, $datares['mobile'], $datares['saldo']);

                        // Monta a resposta
                        $response = array(
                            "status" => true,
                            "data" => $gameretur['gameURL'],
                        );
                    } else {
                        $response = array(
                            "status" => 0,
                            "msg" => "Usuário não logado [2]",
                        );
                    }
                } else {
                    $response = array(
                        "status" => 0,
                        "msg" => "Parâmetro 'code' ou 'id' não encontrado",
                    );
                }
            } else {
                $response = array(
                    "status" => 0,
                    "msg" => "Usuário não logado [1]",
                );
            }
            echo json_encode($response);
            exit;
        }
        // Rota Member/history/detail
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/history/detail') {
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                // Supondo que a variável $mysqli é a conexão com o banco de dados já existente

                // Obter o token do usuário
                $token_user = $_COOKIE['token_user'];

                // Consultar o banco de dados para obter o ID do usuário associado ao token
                $qry = "SELECT * FROM usuarios WHERE token=?";
                $stmt = $mysqli->prepare($qry);
                $stmt->bind_param("s", $token_user);
                $stmt->execute();
                $result_user = $stmt->get_result();

                if ($result_user->num_rows > 0) {
                    $user = $result_user->fetch_assoc();
                    $user_id = $user['id'];

                    // Consultar o histórico do usuário específico
                    $sql = "SELECT historico FROM usuarios WHERE id = ?";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        // Armazenar os dados do histórico em um array
                        $historico = $result->fetch_assoc()['historico'];
                        // Verifica se o histórico não é nulo
                        if ($historico) {
                            // Transforma a string de IDs em um array
                            $historico_ids = explode(',', $historico);
                            // Remove espaços em branco ao redor dos IDs
                            $historico_ids = array_map('trim', $historico_ids);

                            $gamesData = []; // Array para armazenar os dados dos jogos

                            // Iterar sobre o histórico para buscar informações dos jogos
                            foreach ($historico_ids as $gameCode) { // Supondo que $historico_array contém os códigos dos jogos
                                $gameQuery = "SELECT * FROM games WHERE id = ? AND status =1"; // Substitua 'code' pelo nome da coluna que armazena o código do jogo
                                $stmt = $mysqli->prepare($gameQuery);
                                $stmt->bind_param("s", $gameCode);
                                $stmt->execute();
                                $gameResult = $stmt->get_result();

                                if ($gameResult && $gameResult->num_rows > 0) {
                                    while ($gameRow = $gameResult->fetch_assoc()) {
                                        // Adicione os dados do jogo ao array
                                        $gamesData[] = [
                                            "id" => $gameRow['id'],
                                            "platform_id" => $gameRow['id'], // Ajuste conforme os nomes das colunas
                                            //"en_name" => $gameRow['game_name'],
                                            "client_type" => 0,
                                            "game_type" => 3,
                                            "game_id" => $gameRow['id'],
                                            "img" => $gameRow['banner'],
                                            "is_hot" => $gameRow['popular'],
                                            "is_new" => $gameRow['popular'],
                                            //"name" => $gameRow['game_name'],
                                            "sorting" => $gameRow['id'],
                                            "vn_alias" => $gameRow['game_code'],
                                        ];
                                    }
                                }
                            }
                        }

                        // Preparar a resposta
                        $response = [
                            "status" => true,
                            "data" => [
                                "t" => $result->num_rows, // Número total de rows
                                "d" => $gamesData,
                            ],
                        ];
                        echo json_encode($response);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Histórico não encontrado']);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Usuário não encontrado']);
                }
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Token não encontrado ou inválido']);
            }
        }
        // Rota Member/favorites/detail
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/favorites/detail') {
            if (isset($_COOKIE['token_user']) && !empty($_COOKIE['token_user'])) {
                $rotaEncontrada = true; // Rota encontrada
                // Supondo que a variável $mysqli é a conexão com o banco de dados já existente

                // Obter o token do usuário
                $token_user = $_COOKIE['token_user'];

                // Consultar o banco de dados para obter o ID do usuário associado ao token
                $qry = "SELECT * FROM usuarios WHERE token=?";
                $stmt = $mysqli->prepare($qry);
                $stmt->bind_param("s", $token_user);
                $stmt->execute();
                $result_user = $stmt->get_result();

                if ($result_user->num_rows > 0) {
                    $user = $result_user->fetch_assoc();
                    $user_id = $user['id'];

                    // Consultar o histórico do usuário específico
                    $sql = "SELECT favoritos FROM usuarios WHERE id = ?";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        // Armazenar os dados do histórico em um array
                        $historico = $result->fetch_assoc()['favoritos'];
                        // Verifica se o histórico não é nulo
                        if ($historico) {
                            // Transforma a string de IDs em um array
                            $historico_ids = explode(',', $historico);
                            // Remove espaços em branco ao redor dos IDs
                            $historico_ids = array_map('trim', $historico_ids);

                            $gamesData = []; // Array para armazenar os dados dos jogos

                            // Iterar sobre o histórico para buscar informações dos jogos
                            foreach ($historico_ids as $gameCode) { // Supondo que $historico_array contém os códigos dos jogos
                                $gameQuery = "SELECT * FROM games WHERE id = ? AND status = 1"; // Substitua 'code' pelo nome da coluna que armazena o código do jogo
                                $stmt = $mysqli->prepare($gameQuery);
                                $stmt->bind_param("s", $gameCode);
                                $stmt->execute();
                                $gameResult = $stmt->get_result();

                                if ($gameResult && $gameResult->num_rows > 0) {
                                    while ($gameRow = $gameResult->fetch_assoc()) {
                                        // Adicione os dados do jogo ao array
                                        $gamesData[] = [
                                            "id" => $gameRow['id'],
                                            "platform_id" => $gameRow['id'], // Ajuste conforme os nomes das colunas
                                            "en_name" => $gameRow['game_name'],
                                            "client_type" => 0,
                                            "game_type" => 3,
                                            "game_id" => $gameRow['id'],
                                            "img" => $gameRow['banner'],
                                            "is_hot" => $gameRow['popular'],
                                            "is_new" => $gameRow['popular'],
                                            "name" => $gameRow['game_name'],
                                            "sorting" => $gameRow['id'],
                                            "vn_alias" => $gameRow['game_code'],
                                        ];
                                    }
                                }
                            }
                        }

                        // Preparar a resposta
                        $response = [
                            "status" => true,
                            "data" => [
                                "t" => $result->num_rows, // Número total de rows
                                "d" => $gamesData,
                            ],
                        ];
                        echo json_encode($response);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Histórico não encontrado']);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Usuário não encontrado']);
                }
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Token não encontrado ou inválido']);
            }
        }
        // Rota Finance/Channel/type
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/promo/list/sort') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    [
                        [
                            "static" => [
                                "list_web" => "/image/1720065549566..webp",
                                "list_h5" => "/image/1720065553516..webp",
                                "title_web" => "/image/1705398003881.webp",
                                "title_h5" => "/image/1705398015397.webp",
                                "share_h5" => "/",
                            ],
                            "id" => "17395548563955151",
                            "title" => "Recomende amigos e ganhe bônus",
                            "state" => 2,
                            "flag" => "invite",
                            "grade" => "1001,1002,1003,1004,1005,1006,1007,1008,35,34,47",
                            "login_af" => 0,
                            "login_bf" => 0,
                        ],
                        /* [
                            "static" => [
                                "list_web" => "/image/1720065571216..webp",
                                "list_h5" => "/image/1720065579987..webp",
                                "title_web" => "/image/1720065575374..webp",
                                "title_h5" => "/image/1720065588603..webp",
                                "share_h5" => "",
                            ],
                            "id" => "17395548563954431",
                            "title" => "Primeiro bônus de recarga para novos membros",
                            "state" => 2,
                            "flag" => "deposit",
                            "grade" => "1001,1002,1003,1004,1005,1006,1007,1008,35,34,47,2024,2025",
                            "login_af" => 0,
                            "login_bf" => 0,
                        ], */
                        /* [
                            "static" => [
                                "list_web" => "/image/1720065623672..webp",
                                "list_h5" => "/image/1720065637681..webp",
                                "title_web" => "/image/1720065632933..webp",
                                "title_h5" => "/image/1720065641152..webp",
                                "share_h5" => "/undefined",
                            ],
                            "id" => "17405392470691661",
                            "title" => "fundo de ajuda",
                            "state" => 2,
                            "flag" => "rescue",
                            "grade" => "2025,35,34,1001,1002,47,1003,1004,1006,1005,1007,1008",
                            "login_af" => 0,
                            "login_bf" => 0,
                        ], */
                        /*  [
                            "static" => [
                                "list_web" => "/image/1720065659395..webp",
                                "list_h5" => "/image/1720065667276..webp",
                                "title_web" => "/image/1720065663588..webp",
                                "title_h5" => "/image/1720065672031..webp",
                                "share_h5" => "",
                                "display_mode" => 1,
                            ],
                            "id" => "12052671887318748",
                            "title" => "Anúncio VIP",
                            "state" => 2,
                            "flag" => "static",
                            "grade" => ",47,34,35,1001,1002,1003,1004,1005,1007,1008,1006,2025,45",
                            "login_af" => 0,
                            "login_bf" => 0,
                        ], */
                        [
                            "static" => [
                                "list_web" => "/image/1720065706287..webp",
                                "list_h5" => "/image/1720065710539..webp",
                                "title_web" => "/image/1712836040998..webp",
                                "title_h5" => "/image/1712836046062..webp",
                                "share_h5" => "",
                                "display_mode" => 2,
                                "link_url" => "https://t.me/alfplayers",
                            ],
                            "id" => "12093654993918560",
                            "title" => "especialista em telégrafo",
                            "state" => 2,
                            "flag" => "static",
                            "grade" => ",35,47,1001,1002,1003,1004,1005,1006,1007,1008,45,2025,34",
                            "login_af" => 0,
                            "login_bf" => 0,
                        ],
                        /* [
                            "static" => [
                                "list_web" => "/image/1720065724874..webp",
                                "list_h5" => "/image/1720065732475..webp",
                                "title_web" => "/image/1720065728622..webp",
                                "title_h5" => "/image/1720065736240..webp",
                                "share_h5" => "",
                                "display_mode" => 1,
                            ],
                            "id" => "7589294108015554",
                            "title" => "Bônus misterioso",
                            "state" => 2,
                            "flag" => "static",
                            "grade" => "35,34,1001,1002,1003,1004,1005,1006,1007,1008,47,45,2025",
                            "login_af" => 0,
                            "login_bf" => 0,
                        ], */
                        [
                            "static" => [
                                "list_web" => "/image/1720065785599..webp",
                                "list_h5" => "/image/1720065789796..webp",
                                "title_web" => "/image/1712836894182..webp",
                                "title_h5" => "/image/1712836902055..webp",
                                "share_h5" => "",
                                "display_mode" => 2,
                                "link_url" => "https://telegram.me/alfplayers",
                            ],
                            "id" => "12117535491806041",
                            "title" => "telegram.VIP",
                            "state" => 2,
                            "flag" => "static",
                            "grade" => "47,35,34,1002,1003,1004,1005,1006,1007,1008,1001,45,2025",
                            "login_af" => 0,
                            "login_bf" => 0,
                        ],
                    ],
                ],
                "msg" => null,
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        // Rota Member/message/list
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/message/list') {
            $rotaEncontrada = true; // Rota encontrada
            // SQL para obter os dados dos jogos
            $sql = "SELECT * FROM mensagens WHERE status = 1";
            $result = $mysqli->query($sql);

            $games_data = [
                "d" => [],
                "t" => 0, // Ajuste conforme necessário
                "s" => 0, // Ajuste conforme necessário
            ];

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $games_data["d"][] = [
                        "id" => $row['id'],
                        "title" => $row['titulo'],
                        "is_read" => 1,
                        "send_at" => strtotime($row['criado_em']),
                    ];
                }

                // Ajuste os valores de 't' e 's' conforme necessário
                $games_data["t"] = count($games_data); // Exemplo: número de resultados
                $games_data["s"] = count($games_data); // Exemplo: número total de resultados
            } else {
                // No games data, manter valores padrão ou ajustar conforme necessário
                $games_data["t"] = 0;
                $games_data["s"] = 0;
            }

            $mysqli->close();

            $response = [
                "status" => true,
                "data" => $games_data,
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);
        }
        // Rota member/vip/config
        if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/vip/config') {
            $rotaEncontrada = true; // Rota encontrada
            $response = [
                "status" => true,
                "data" => [
                    [
                        "id" => "0",
                        "level" => 0,
                        "level_name" => "VIP0",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 0,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 0,
                        "birth_gift" => 0,
                        "withdraw_count" => 30,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1729530852,
                        "user_count" => 56930,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "1",
                        "level" => 1,
                        "level_name" => "VIP1",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 10000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 5,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1721318677,
                        "user_count" => 36,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "2",
                        "level" => 2,
                        "level_name" => "VIP2",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 30000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 10,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717205468,
                        "user_count" => 6,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "3",
                        "level" => 3,
                        "level_name" => "VIP3",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 100000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 20,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717205472,
                        "user_count" => 1,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "4",
                        "level" => 4,
                        "level_name" => "VIP4",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 300000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 50,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717205477,
                        "user_count" => 0,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "5",
                        "level" => 5,
                        "level_name" => "VIP5",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 600000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 100,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717205481,
                        "user_count" => 0,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "6",
                        "level" => 6,
                        "level_name" => "VIP6",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 1000000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 200,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717615192,
                        "user_count" => 0,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "7",
                        "level" => 7,
                        "level_name" => "VIP7",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 3000000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 400,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717619261,
                        "user_count" => 0,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "8",
                        "level" => 8,
                        "level_name" => "VIP8",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 5000000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 600,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717619266,
                        "user_count" => 1,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "9",
                        "level" => 9,
                        "level_name" => "VIP9",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 7000000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 800,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717832417,
                        "user_count" => 0,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "10",
                        "level" => 10,
                        "level_name" => "VIP10",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 10000000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 950,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717832425,
                        "user_count" => 0,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "11",
                        "level" => 11,
                        "level_name" => "VIP11",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 13000000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 1100,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717832430,
                        "user_count" => 0,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "12",
                        "level" => 12,
                        "level_name" => "VIP12",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 16000000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 1250,
                        "birth_gift" => 0,
                        "withdraw_count" => 10,
                        "withdraw_max" => 1000000,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717832435,
                        "user_count" => 0,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "13",
                        "level" => 13,
                        "level_name" => "VIP13",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 20000000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 1500,
                        "birth_gift" => 0,
                        "withdraw_count" => 0,
                        "withdraw_max" => 0,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717832442,
                        "user_count" => 0,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "14",
                        "level" => 14,
                        "level_name" => "VIP14",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 100000000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 3000,
                        "birth_gift" => 0,
                        "withdraw_count" => 0,
                        "withdraw_max" => 0,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1717832518,
                        "user_count" => 0,
                        "remark" => "",
                        "flow_multiple" => 1
                    ],
                    [
                        "id" => "15",
                        "level" => 15,
                        "level_name" => "VIP15",
                        "recharge_num" => 0,
                        "upgrade_deposit" => 0,
                        "upgrade_record" => 1000000000,
                        "relegation_flowing" => 0,
                        "upgrade_gift" => 6515,
                        "birth_gift" => 0,
                        "withdraw_count" => 0,
                        "withdraw_max" => 0,
                        "early_month_packet" => 0,
                        "late_month_packet" => 0,
                        "created_at" => 0,
                        "updated_at" => 1714668744,
                        "user_count" => 14000,
                        "remark" => "",
                        "flow_multiple" => 1
                    ]
                ]
            ];
            $response_json = json_encode($response, JSON_PRETTY_PRINT);
            echo $response_json;
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
}

// Rota Member/Agency/Mybet
if ($requestURI === '/api/member/agency/mybet') {

    $response = [
        "status" => true,
        "data" => [
            "d" => [],
            "t" => 0,
            "s" => 0,
            "agg" => [
                "child_validbet" => 0,
                "child_lvl1_validbet" => 0,
                "child_other_validbet" => 0,
                "report_time" => 0
            ]
        ],
        "msg" => null
    ];
    echo json_encode($response);
    exit;
}

// Rota Member/rebate/agency/record
if (parse_url($requestURI, PHP_URL_PATH) === '/api/member/rebate/agency/record') {

    $response = [
        "status" => true,
        "data" => [
            "d" => null,
            "t" => 0,
            "agg" => [
                "total_paid_amount" => 0,
                "paid_amount" => 0,
                "other_paid_amount" => 0
            ],
            "s" => 15
        ],
        "msg" => null
    ];
    echo json_encode($response);
    exit;
}


// Rota Member/direct/agency/state
if ($requestURI === '/api/member/direct/agency/state') {

    $response = [
        "status" => true,
        "data" => [
            "d" => null,
            "t" => 0,
            "s" => 15,
            "agg" => [
                "reg_num" => 0,
                "deposit_num" => 0,
                "first_deposit_num" => 0,
                "first_deposit_amount" => 0,
                "total_dept_amount" => 0,
                "valid_bet_amount" => 0
            ]
        ],
        "msg" => null
    ];
    echo json_encode($response);
    exit;
}

// Rota Member/direct/agency/bet
if ($requestURI === '/api/member/direct/agency/bet') {

    $response = [
        "status" => true,
        "data" => [
            "d" => null,
            "t" => 0,
            "s" => 15,
            "agg" => [
                "valid_bet_amount" => 0,
                "total_valid_bet_amount" => 0,
                "other_valid_bet_amount" => 0
            ]
        ],
        "msg" => null
    ];
    echo json_encode($response);
    exit;
}

// Rota Member/direct/agency/finance
if ($requestURI === '/api/member/direct/agency/finance') {

    $response = [
        "status" => true,
        "data" => [
            "d" => null,
            "t" => 0,
            "s" => 15,
            "agg" => [
                "total_dept_amount" => 0,
                "first_deposit_amount" => 0,
                "first_deposit_num" => 0,
                "deposit_num" => 0
            ]
        ],
        "msg" => null
    ];
    echo json_encode($response);
    exit;
}

// Rota Member/direct/agency/finance
if ($requestURI === '/api/member/agency/mypick') {

    $response = [
        "status" => true,
        "data" => [
            "d" => null,
            "t" => 0,
            "s" => 15,
            "agg" => [
                "username" => "",
                "uid" => "",
                "level" => 0,
                "sub_num" => 0,
                "water" => 0,
                "vip_upgrade" => 0,
                "total_get" => 0
            ]
        ],
        "msg" => null
    ];
    echo json_encode($response);
    exit;
}

// Rota Member/commission/config
if ($requestURI === '/api/member/commission/config?') {

    $response = [
        "status" => true,
        "data" => [
            [
                "id" => 101,
                "game_type" => 1,
                "bet_amount" => 0.01,
                "rebate_amount" => 10,
                "ty" => 2
            ],
            [
                "id" => 143,
                "game_type" => 9,
                "bet_amount" => 0.01,
                "rebate_amount" => 300,
                "ty" => 2
            ],
            [
                "id" => 137,
                "game_type" => 7,
                "bet_amount" => 0.01,
                "rebate_amount" => 10,
                "ty" => 2
            ],
            [
                "id" => 107,
                "game_type" => 2,
                "bet_amount" => 0.01,
                "rebate_amount" => 10,
                "ty" => 2
            ],
            [
                "id" => 131,
                "game_type" => 6,
                "bet_amount" => 0.01,
                "rebate_amount" => 10,
                "ty" => 2
            ],
            [
                "id" => 125,
                "game_type" => 5,
                "bet_amount" => 0.01,
                "rebate_amount" => 10,
                "ty" => 2
            ],
            [
                "id" => 119,
                "game_type" => 4,
                "bet_amount" => 0.01,
                "rebate_amount" => 10,
                "ty" => 2
            ],
            [
                "id" => 113,
                "game_type" => 3,
                "bet_amount" => 0.01,
                "rebate_amount" => 10,
                "ty" => 2
            ],
            [
                "id" => 102,
                "game_type" => 1,
                "bet_amount" => 0.02,
                "rebate_amount" => 60,
                "ty" => 2
            ],
            [
                "id" => 138,
                "game_type" => 7,
                "bet_amount" => 0.02,
                "rebate_amount" => 20,
                "ty" => 2
            ],
            [
                "id" => 132,
                "game_type" => 6,
                "bet_amount" => 0.02,
                "rebate_amount" => 90,
                "ty" => 2
            ],
            [
                "id" => 126,
                "game_type" => 5,
                "bet_amount" => 0.02,
                "rebate_amount" => 90,
                "ty" => 2
            ],
            [
                "id" => 120,
                "game_type" => 4,
                "bet_amount" => 0.02,
                "rebate_amount" => 90,
                "ty" => 2
            ],
            [
                "id" => 114,
                "game_type" => 3,
                "bet_amount" => 0.02,
                "rebate_amount" => 20,
                "ty" => 2
            ],
            [
                "id" => 108,
                "game_type" => 2,
                "bet_amount" => 0.02,
                "rebate_amount" => 90,
                "ty" => 2
            ],
            [
                "id" => 115,
                "game_type" => 3,
                "bet_amount" => 0.05,
                "rebate_amount" => 50,
                "ty" => 2
            ],
            [
                "id" => 103,
                "game_type" => 1,
                "bet_amount" => 0.05,
                "rebate_amount" => 70,
                "ty" => 2
            ],
            [
                "id" => 139,
                "game_type" => 7,
                "bet_amount" => 0.05,
                "rebate_amount" => 200,
                "ty" => 2
            ],
            [
                "id" => 133,
                "game_type" => 6,
                "bet_amount" => 0.05,
                "rebate_amount" => 100,
                "ty" => 2
            ],
            [
                "id" => 127,
                "game_type" => 5,
                "bet_amount" => 0.05,
                "rebate_amount" => 100,
                "ty" => 2
            ],
            [
                "id" => 109,
                "game_type" => 2,
                "bet_amount" => 0.05,
                "rebate_amount" => 100,
                "ty" => 2
            ],
            [
                "id" => 121,
                "game_type" => 4,
                "bet_amount" => 0.05,
                "rebate_amount" => 100,
                "ty" => 2
            ],
            [
                "id" => 110,
                "game_type" => 2,
                "bet_amount" => 0.1,
                "rebate_amount" => 130,
                "ty" => 2
            ],
            [
                "id" => 128,
                "game_type" => 5,
                "bet_amount" => 0.1,
                "rebate_amount" => 120,
                "ty" => 2
            ],
            [
                "id" => 122,
                "game_type" => 4,
                "bet_amount" => 0.1,
                "rebate_amount" => 120,
                "ty" => 2
            ],
            [
                "id" => 134,
                "game_type" => 6,
                "bet_amount" => 0.1,
                "rebate_amount" => 120,
                "ty" => 2
            ],
            [
                "id" => 104,
                "game_type" => 1,
                "bet_amount" => 0.1,
                "rebate_amount" => 80,
                "ty" => 2
            ],
            [
                "id" => 140,
                "game_type" => 7,
                "bet_amount" => 0.1,
                "rebate_amount" => 300,
                "ty" => 2
            ],
            [
                "id" => 123,
                "game_type" => 4,
                "bet_amount" => 0.2,
                "rebate_amount" => 150,
                "ty" => 2
            ],
            [
                "id" => 129,
                "game_type" => 5,
                "bet_amount" => 0.2,
                "rebate_amount" => 150,
                "ty" => 2
            ],
            [
                "id" => 135,
                "game_type" => 6,
                "bet_amount" => 0.2,
                "rebate_amount" => 150,
                "ty" => 2
            ],
            [
                "id" => 141,
                "game_type" => 7,
                "bet_amount" => 0.2,
                "rebate_amount" => 500,
                "ty" => 2
            ],
            [
                "id" => 105,
                "game_type" => 1,
                "bet_amount" => 0.2,
                "rebate_amount" => 100,
                "ty" => 2
            ],
            [
                "id" => 111,
                "game_type" => 2,
                "bet_amount" => 0.2,
                "rebate_amount" => 150,
                "ty" => 2
            ],
            [
                "id" => 124,
                "game_type" => 4,
                "bet_amount" => 0.3,
                "rebate_amount" => 200,
                "ty" => 2
            ],
            [
                "id" => 112,
                "game_type" => 2,
                "bet_amount" => 0.3,
                "rebate_amount" => 200,
                "ty" => 2
            ],
            [
                "id" => 142,
                "game_type" => 7,
                "bet_amount" => 0.3,
                "rebate_amount" => 1000,
                "ty" => 2
            ],
            [
                "id" => 136,
                "game_type" => 6,
                "bet_amount" => 0.3,
                "rebate_amount" => 180,
                "ty" => 2
            ],
            [
                "id" => 106,
                "game_type" => 1,
                "bet_amount" => 0.3,
                "rebate_amount" => 120,
                "ty" => 2
            ],
            [
                "id" => 130,
                "game_type" => 5,
                "bet_amount" => 0.3,
                "rebate_amount" => 180,
                "ty" => 2
            ],
            [
                "id" => 144,
                "game_type" => 9,
                "bet_amount" => 200,
                "rebate_amount" => 300,
                "ty" => 2
            ],
            [
                "id" => 145,
                "game_type" => 9,
                "bet_amount" => 2000,
                "rebate_amount" => 300,
                "ty" => 2
            ],
            [
                "id" => 146,
                "game_type" => 9,
                "bet_amount" => 3000,
                "rebate_amount" => 300,
                "ty" => 2
            ],
            [
                "id" => 147,
                "game_type" => 9,
                "bet_amount" => 6000,
                "rebate_amount" => 300,
                "ty" => 2
            ],
            [
                "id" => 148,
                "game_type" => 9,
                "bet_amount" => 10000,
                "rebate_amount" => 300,
                "ty" => 2
            ]
        ],
        "msg" => null
    ];
    echo json_encode($response);
    exit;
}

// Rota /api/member/bind/email
if ($requestURI === '/api/member/bind/email') {
    //$jsonDataModificado = $data;
    if (isset($_COOKIE['token_user']) and !empty($_COOKIE['token_user'])) {
        $rotaEncontrada = true; // Rota encontrada
        $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
        $resp = mysqli_query($mysqli, $qry);
        if (mysqli_num_rows($resp) > 0) {
            $datares = mysqli_fetch_assoc($resp);
            $sql = $mysqli->prepare("UPDATE usuarios SET bonus_status_define_email = '502', email = ? WHERE id = ?");
            $sql->bind_param("si", $data['email'], $datares['id']);
            if ($sql->execute()) {
                $response = [
                    "status" => true,
                    "data" => '1000',
                    "msg" => null,
                ];
                echo json_encode($response);
                exit;
            } else {
                $response = [
                    "code" => 0,
                    "msg" => "Erro ao realizar saque.",
                ];
                echo json_encode($response);
                exit;
            }
        } else {
            $response = [
                "code" => 0, // Indica falha
                "msg" => "Usuário sem efetuar login", // Mensagem de erro
            ];
            echo json_encode($response);
            exit;
        }
    } else {
        $response = [
            "code" => 0,
            "data" => null,
            "msg" => "Usuario ou senha incorretos",
            "time" => time(),
        ];
        echo json_encode($response);
        exit;
    }
}

// Rota /api/member/avatar/update
if (strpos($_SERVER['REQUEST_URI'], '/api/member/avatar/update') !== false) {
    //$jsonDataModificado = $data;
    if (isset($_COOKIE['token_user']) and !empty($_COOKIE['token_user'])) {
        $rotaEncontrada = true; // Rota encontrada
        $qry = "SELECT * FROM usuarios WHERE token='" . $_COOKIE['token_user'] . "'";
        $resp = mysqli_query($mysqli, $qry);
        if (mysqli_num_rows($resp) > 0) {
            $datares = mysqli_fetch_assoc($resp);

            $update = "UPDATE usuarios SET avatar = ? WHERE id = ?";
            if ($datares['bonus_status_define_avatar'] === '501') {
                $update = "UPDATE usuarios SET bonus_status_define_avatar = '502', avatar = ? WHERE id = ?";
            }

            $sql = $mysqli->prepare($update);
            $sql->bind_param("si", $_GET['id'], $datares['id']);
            if ($sql->execute()) {

                $response = [
                    "status" => true,
                    "data" => '1000',
                    "msg" => null,
                ];
                echo json_encode($response);
                exit;
            } else {
                $response = [
                    "code" => 0,
                    "msg" => "Erro ao realizar saque.",
                ];
                echo json_encode($response);
                exit;
            }
        } else {
            $response = [
                "code" => 0, // Indica falha
                "msg" => "Usuário sem efetuar login", // Mensagem de erro
            ];
            echo json_encode($response);
            exit;
        }
    } else {
        $response = [
            "code" => 0,
            "data" => null,
            "msg" => "Usuario ou senha incorretos",
            "time" => time(),
        ];
        echo json_encode($response);
        exit;
    }
}


// Rota /api/promo/detail
if (strpos($_SERVER['REQUEST_URI'], '/api/promo/detail') !== false) {

    $response = []/* [
"status" => true,
"data" => [
"static" => [
"display_mode" => 1,
"link_mode" => 1,
"link_url" => "web_login",
"page_style" => "img",
"list_h5" => "/image/1727866779415..webp",
"title_h5" => "/image/1727866886742..webp",
"list_web" => "/image/1710299520110..webp",
"title_web" => "/image/1710299193585..webp",
"share_h5" => ""
],
"rules" => [
"1" => [
[
"deposit_amount" => 20,
"bonus_rate" => 10,
"limit_amount" => 10000,
"bonus_amount" => 2,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 30,
"bonus_rate" => 10,
"limit_amount" => 10000,
"bonus_amount" => 2,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 50,
"bonus_rate" => 500,
"limit_amount" => 10000,
"bonus_amount" => 2,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 100,
"bonus_rate" => 10,
"limit_amount" => 10000,
"bonus_amount" => 2,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 200,
"bonus_rate" => 10,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 300,
"bonus_rate" => 10,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 500,
"bonus_rate" => 10,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 1000,
"bonus_rate" => 10,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 2000,
"bonus_rate" => 10,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 3000,
"bonus_rate" => 10,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 5000,
"bonus_rate" => 10,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 10000,
"bonus_rate" => 10,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
]
],
"2" => [
[
"deposit_amount" => 30,
"bonus_rate" => 20,
"limit_amount" => 10000,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 300,
"bonus_rate" => 20,
"limit_amount" => 10000,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 500,
"bonus_rate" => 20,
"limit_amount" => 10000,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 1000,
"bonus_rate" => 20,
"limit_amount" => 10000,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 5000,
"bonus_rate" => 20,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 10000,
"bonus_rate" => 20,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 20000,
"bonus_rate" => 20,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
],
[
"deposit_amount" => 50000,
"bonus_rate" => 20,
"limit_amount" => 0,
"bonus_amount" => 0,
"flow_multiple" => 3,
"bonus_type" => 2
]
],
"3" => [
[
"lose_amount" => 0,
"bonus_amount" => 0,
"bl_rate" => 1,
"bonus_rate" => 30,
"rate" => 1,
"flow_multiple" => 3,
"limit_amount" => 0,
"deposit_amount" => 30,
"bonus_type" => 2
],
[
"lose_amount" => 0,
"bonus_amount" => 0,
"bl_rate" => 1,
"bonus_rate" => 30,
"rate" => 1,
"flow_multiple" => 3,
"limit_amount" => 0,
"deposit_amount" => 300,
"bonus_type" => 2
],
[
"lose_amount" => 0,
"bonus_amount" => 0,
"bl_rate" => 1,
"bonus_rate" => 30,
"rate" => 1,
"flow_multiple" => 3,
"limit_amount" => 0,
"deposit_amount" => 500,
"bonus_type" => 2
],
[
"lose_amount" => 0,
"bonus_amount" => 0,
"bl_rate" => 1,
"bonus_rate" => 30,
"rate" => 1,
"flow_multiple" => 3,
"limit_amount" => 0,
"deposit_amount" => 1000,
"bonus_type" => 2
],
[
"lose_amount" => 0,
"bonus_amount" => 0,
"bl_rate" => 1,
"bonus_rate" => 30,
"rate" => 1,
"flow_multiple" => 3,
"limit_amount" => 0,
"deposit_amount" => 5000,
"bonus_type" => 2
],
[
"lose_amount" => 0,
"bonus_amount" => 0,
"bl_rate" => 1,
"bonus_rate" => 30,
"rate" => 1,
"flow_multiple" => 3,
"limit_amount" => 0,
"deposit_amount" => 10000,
"bonus_type" => 2
],
[
"lose_amount" => 0,
"bonus_amount" => 0,
"bl_rate" => 1,
"bonus_rate" => 30,
"rate" => 1,
"flow_multiple" => 3,
"limit_amount" => 0,
"deposit_amount" => 20000,
"bonus_type" => 2
],
[
"lose_amount" => 0,
"bonus_amount" => 0,
"bl_rate" => 1,
"bonus_rate" => 30,
"rate" => 1,
"flow_multiple" => 3,
"limit_amount" => 0,
"deposit_amount" => 50000,
"bonus_type" => 2
]
]
],
"config" => [
"wheelDisplayStyle" => 1,
"wheelPrizeCount" => 1,
"title" => "Os primeiros 3 bônus de recarga do novo membro",
"sort" => 1,
"h5_content" => [
"/image/1712273686251..webp",
"/image/1712273690803..webp",
"/image/1712273695075..webp"
],
"list_richOriginContent" => "",
"list_richContent" => "",
"list_content" => [
"bgColor" => "",
"texture" => ""
],
"start_time" => "2022-06-01 00:00:00",
"end_time" => "2032-12-31 23:59:59",
"period" => 2,
"show_time" => "2022-06-20 01:30:45",
"show_end_time" => "2024-07-31 23:59:59",
"is_limit_plat" => 1,
"is_audit" => 2,
"is_discount" => 1,
"rewardCycle" => 1,
"upper_limit" => "",
"is_manual" => 1,
"deposit_manual" => 2,
"pick" => 1,
"award" => [
"type" => 1,
"time" => "00:00:00"
],
"bonus_type" => 2,
"auditMethod" => 1,
"flow_multiple" => 1,
"entrance" => [
"2",
"3",
"4"
],
"limited" => [],
"rule_text" => [
"1. Por favor, preencha seus dados pessoais antes de participar do evento",
"2. Cada membro com o mesmo nome, mesmo endereço IP, mesmo código de dispositivo e mesmo número de telefone só poderá participar do evento uma vez. Se um membro registrar intencionalmente várias contas consecutivas, a empresa retirará ou revogará os direitos da agência e congelará permanentemente a conta e recuperará todos os ganhos.",
"3. Todas as atividades são preparadas para os membros, caso seja descoberto que um indivíduo ou organização cometeu fraude para lucrar com as atividades. A Empresa reserva-se o direito total de reter ou anular o valor total do prêmio desse indivíduo ou organização"
],
"content_text" => [
"1. Requisitos de apostas válidos",
"a. Ao participar do evento, o saldo da conta < valor do depósito, aposta válida = (saldo + bônus) x 2, você pode sacar dinheiro",
"b. Ao participar do evento, o saldo da conta > o valor do depósito e a aposta efetiva = (valor do depósito + bônus) x 2, você      pode sacar o dinheiro",
"2. Após o depósito ser bem-sucedido, participe da atividade antes de fazer apostas.",
"3. O primeiro depósito, o segundo depósito e o terceiro depósito só podem ser participados uma vez."
],
"activity_conditions" => 1,
"collection_method" => 1,
"recharge_method" => [
"1",
"2",
"3"
],
"recharge_num" => 1,
"login_af" => 0,
"login_bf" => 0,
"web_content" => [
"/image/1712273659951..webp",
"/image/1712273667439..webp",
"/image/1712273673232..webp"
],
"flag" => "deposit",
"hide_time" => "2032-12-31 23:59:59",
"activity_time" => "",
"platforms" => [
"26595015200313"
],
"lv" => 3
],
"deposit_manual" => 2
]
] */ ;
    echo json_encode($response);
    exit;
}

// Rota /api/member/agency/report/sub/plat
if (strpos($_SERVER['REQUEST_URI'], '/api/member/agency/report/sub/plat') !== false) {
    $response = [
        "status" => true,
        "data" => [
            "t" => 0,
            "d" => [
                [
                    "id" => "26595015100510",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "TaDa Casino",
                    "seq" => 101
                ],
                [
                    "id" => "26595015100209",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "TaDa Pescaria",
                    "seq" => 101
                ],
                [
                    "id" => "26595015200909",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "TaDa Blockchain",
                    "seq" => 101
                ],
                [
                    "id" => "36595015200325",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "TaDa Slots",
                    "seq" => 101
                ],
                [
                    "id" => "36595015200324",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "PP Slots",
                    "seq" => 3
                ],
                [
                    "id" => "26595015200105",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "EVO Casino ",
                    "seq" => 20
                ],
                [
                    "id" => "36595015200907",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "Mini Game",
                    "seq" => 15
                ],
                [
                    "id" => "36595015200906",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "SPRIBE",
                    "seq" => 15
                ],
                [
                    "id" => "36595015200314",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "Slots AceWin",
                    "seq" => 1
                ],
                [
                    "id" => "26595015200315",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "CG Slots",
                    "seq" => 13
                ],
                [
                    "id" => "36595015200322",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "PG Slots",
                    "seq" => 31
                ],
                [
                    "id" => "36595015200313",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "PG Slots",
                    "seq" => 25
                ],
                [
                    "id" => "26595015200313",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "PG Slots",
                    "seq" => 14
                ],
                [
                    "id" => "36595015200305",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "PG Slots",
                    "seq" => 10
                ],
                [
                    "id" => "26595015200503",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "JL Cartas",
                    "seq" => 10
                ],
                [
                    "id" => "26595015200505",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "JDB Cartas",
                    "seq" => 10
                ],
                [
                    "id" => "26595015200316",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "CQ9 Slots",
                    "seq" => 7
                ],
                [
                    "id" => "26595015200317",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "FC Slots",
                    "seq" => 6
                ],
                [
                    "id" => "26595015200309",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "SG Slots",
                    "seq" => 4
                ],
                [
                    "id" => "26595015200201",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "JL Pescaria",
                    "seq" => 4
                ],
                [
                    "id" => "26595015200206",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "SG Pescaria",
                    "seq" => 3
                ],
                [
                    "id" => "26595015200905",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "JDB Blockchain",
                    "seq" => 12
                ],
                [
                    "id" => "26595015200304",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "JDB Slots",
                    "seq" => 2
                ],
                [
                    "id" => "26595015200203",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "JDB Pescaria",
                    "seq" => 2
                ],
                [
                    "id" => "26595015200306",
                    "amount" => "",
                    "bet_count" => "",
                    "bet_amount" => "",
                    "valid_bet_amount" => "",
                    "name" => "JL Slots",
                    "seq" => 1
                ]
            ]
        ],
        "msg" => null
    ];
    echo json_encode($response);
    exit;
}

// Rota /api/member/agency/report/sub/plat
if (strpos($_SERVER['REQUEST_URI'], '/api/promo/invite/record/detail') !== false) {

    $response = [
        "status" => true,
        "data" => [
            "d" => [],
            "t" => 1
        ],
        "msg" => null
    ];

    echo json_encode($response);
    exit;
}

// Rota /api/promo/invite/open
if (strpos($_SERVER['REQUEST_URI'], '/api/promo/invite/open') !== false) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    //var_dump($data);
    $token = $_COOKIE['token_user'];
    $mem_count = $data['mem_count'];

    $rotaEncontrada = true; // Rota encontrada

    $qry = "SELECT * FROM usuarios WHERE token='" . $token . "'";
    $resp = mysqli_query($mysqli, $qry);
    if (mysqli_num_rows($resp) > 0) {
        $datares = mysqli_fetch_assoc($resp);

        $qrygetbau = "SELECT * FROM afiliado_baus WHERE id_user = ? AND mem_count = ?";

        $stmtgetbau = $mysqli->prepare($qrygetbau);
        $stmtgetbau->bind_param("ii", $datares['id'], $mem_count);
        $stmtgetbau->execute();
        $afiliadobau = $stmtgetbau->get_result();
        $afiliadobau = mysqli_fetch_assoc($afiliadobau);
        $stmtgetbau->close();

        $transacao_id = generate_unique_token();
        $usrId = $datares['id'];
        $valor = $afiliadobau['valor'];
        $tipo = "bau";
        $data_hora = new DateTime();
        $data_hora = $data_hora->format('Y-m-d H:i:s');
        $qrcode = 'BAU_' . $mem_count . '_' . $transacao_id;
        $code = 'BAU_' . $mem_count . '_' . $transacao_id;
        $status = "pago";

        $qryaddtrans = "INSERT into transacoes (transacao_id, usuario, valor, tipo, data_hora, qrcode, code, status) VALUES (?,?,?,?,?,?,?,?)";
        $stmtaddtrans = $mysqli->prepare($qryaddtrans);
        $stmtaddtrans->bind_param("sidsssss", $transacao_id, $usrId, $valor, $tipo, $data_hora, $qrcode, $code, $status);
        $stmtaddtrans->execute();
        $stmtaddtrans->close();

        $qryupbau = "UPDATE afiliado_baus SET status = 'aberto' WHERE id_user = ? AND mem_count = ?";

        $stmtupbau = $mysqli->prepare($qryupbau);
        $stmtupbau->bind_param("ii", $datares['id'], $mem_count);
        $stmtupbau->execute();
        $stmtupbau->close();

        $novosaldo = $datares['saldo'] + $afiliadobau['valor'];
        $qrysaldouser = "UPDATE usuarios SET saldo = ? WHERE id = ? ";

        $stmtsaldouser = $mysqli->prepare($qrysaldouser);
        $stmtsaldouser->bind_param("ds", $novosaldo, $datares['id']);
        $stmtsaldouser->execute();
        $stmtsaldouser->close();

        echo json_encode(["status" => true, "data" => "1000"]);
        exit;
    } else {
        echo json_encode(["status" => false, "data" => "Usuário não encontrado!"]);
        exit;
    }
}


// Rota /api/member/agency/report/sub/plat

function generate_unique_token()
{
    // Generate a UUID (Universally Unique Identifier) using PHP's random_bytes and bin2hex
    $data = random_bytes(16);

    // Set the version to 4 (random) and the variant to RFC 4122
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // Set version to 0100
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // Set bits 6-7 to 10

    // Convert the binary data to a UUID string
    return sprintf(
        '%08x-%04x-%04x-%04x-%12s',
        unpack('N1', substr($data, 0, 4))[1],
        unpack('n1', substr($data, 4, 2))[1],
        unpack('n1', substr($data, 6, 2))[1],
        unpack('n1', substr($data, 8, 2))[1],
        bin2hex(substr($data, 10, 6))
    );
}


if (strpos($_SERVER['REQUEST_URI'], '/softbet/teste') !== false) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    $id = $_GET['id'];
    $data = liberarBau($id);
    //var_dump($data);
    echo json_encode($data, true);
    exit;
}

// Se nenhuma rota foi encontrada, retorna 404
if (!$rotaEncontrada) {
    var_dump($_SERVER['REQUEST_URI']);
    sendError(404, 'Rota não encontrada');
}
