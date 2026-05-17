<?php
date_default_timezone_set('America/Sao_Paulo');
include_once('database.php');
include_once('funcao.php');
#=====================================================#
# DATA CONFIG
function data_config()
{
	global $mysqli;
	$qry = "SELECT * FROM config WHERE id=1";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);
	return $data;
}
$dataconfig = data_config();
#=====================================================#
# DATA POPUPS
function data_popups($id)
{
	global $mysqli;
	$qry = "SELECT * FROM popups WHERE id = '" . $id . "'";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);
	return $data;
}
#=====================================================#
# DATA CONFIG
function data_fiverscanPanel()
{
	global $mysqli;
	$qry = "SELECT * FROM fiverscan WHERE id=1";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);
	return $data;
}
$data_fiverscanpanel = data_fiverscanPanel();

function enviarSaldo($email, $saldo)
{
	global $mysqli;

	// Monta a query de atualização
	$qry = "UPDATE usuarios SET saldo = saldo + '" . $saldo . "' WHERE mobile = '" . $email . "'";

	// Executa a consulta
	if (mysqli_query($mysqli, $qry)) {
		return 1;  // Sucesso
	} else {
		return 0;  // Falha
	}
}

#diminuir saldo na api da fiverscan
function withdrawSaldo($email, $saldo)
{
	global $mysqli;

	// Verifica o saldo atual do usuário
	$qryCheckSaldo = "SELECT saldo FROM usuarios WHERE mobile = '" . $email . "'";
	$result = mysqli_query($mysqli, $qryCheckSaldo);

	if ($result && mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		$saldoAtual = $row['saldo'];

		// Verifica se o saldo é suficiente para o saque
		if ($saldoAtual >= $saldo) {
			// Monta a query de atualização do saldo
			$qry = "UPDATE usuarios SET saldo = saldo - '" . $saldo . "' WHERE mobile = '" . $email . "'";

			// Executa a consulta de atualização
			if (mysqli_query($mysqli, $qry)) {
				return 1;  // Sucesso
			} else {
				return 0;  // Falha na execução da query
			}
		} else {
			return -1;  // Saldo insuficiente
		}
	} else {
		return 0;  // Falha ao buscar o saldo ou usuário não encontrado
	}
}

function gameInDb($id)
{
	global $pdo;

	// Corrigido o operador de comparação
	$stmt = $pdo->prepare('SELECT * FROM games WHERE  status=1 AND id = :id');
	$stmt->execute(['id' => $id]);
	$gameData = $stmt->fetch();

	return $gameData; // Retorna o resultado
}

function pegarGameCode($gamecode)
{

	$gamename = '126';
	switch ($gamecode) {
		case '98':
			$gamename = "fortune-ox";
			break;
		case '68':
			$gamename = "fortune-mouse";
			break;
		case '69':
			$gamename = "bikini-paradise";
			break;
		case '126':
			$gamename = "fortune-tiger";
			break;
		case '1543462':
			$gamename = "fortune-rabbit";
			break;
		case '1695365':
			$gamename = "fortune-dragon";
			break;
		case '40':
			$gamename = "jungle-delight";
			break;
		case '42':
			$gamename = "ganesha-gold";
			break;
		case '48':
			$gamename = "double-fortune";
			break;
		case '63':
			$gamename = "dragon-tiger-luck";
			break;
		case '1682240':
			$gamename = "cash-mania";
			break;
		case '1451122':
			$gamename = "dragon-hatch2";
			break;
		case '1492288':
			$gamename = "pinata-wins";
			break;
		case '1738001':
			$gamename = "chicky-run";
			break;
		case '1508783':
			$gamename = "wild-ape-3258";
			break;
		case '1778752':
			$gamename = "futebol-fever";
			break;
		case '1717688':
			$gamename = "mystic-potions";
			break;
	}
	return $gamename;
}

function pegarLinkJogo($provedor, $gameId, $mobile, $saldo)
{
	global $data_fiverscanpanel, $pdo;

	$keys = $data_fiverscanpanel;
	$games = NULL;
	$gamebco = gameInDb($gameId);

	if ($gamebco && isset($gamebco['distribution'])) {
		switch ($gamebco['distribution']) {
			case 'fiverscan':

				$stmtUsr = $pdo->prepare("SELECT * FROM usuarios WHERE mobile = :mobile");
				$stmtUsr->execute(['mobile' => $mobile]);
				$usuario = $stmtUsr->fetch();

				if ($usuario) {
					$stmtPf = $pdo->query("SELECT * FROM fiverscan WHERE id=1");
					$fiverscan = $stmtPf->fetch();

					if ($fiverscan) {
						// Dados para o corpo da requisição em formato JSON
						$data = [
							"method" => "game_launch",
							'agent_code' => $fiverscan['agent_code'],
							'agent_token' => $fiverscan['agent_token'],
							'user_code' => $mobile,
							"provider_code" => $provedor,
							"game_code" => $gamebco['game_code'],
							"lang" => "pt"
						];
						$json_data = json_encode($data);
						$response = enviarRequest($fiverscan['url'] . '/', $json_data);
						$data = json_decode($response, true);
						$games = isset($data['launch_url']) ? ['gameURL' => $data['launch_url']] : null;
					} else {
						echo "Dados de fiverscan não encontrados.";
					}
				} else {
					echo "Usuário não encontrado.";
				}
				break;

			case 'playfiver':
				$stmtUsr = $pdo->prepare("SELECT * FROM usuarios WHERE mobile = :mobile");
				$stmtUsr->execute(['mobile' => $mobile]);
				$usuario = $stmtUsr->fetch();

				if ($usuario) {
					$stmtPf = $pdo->query("SELECT * FROM playfiver WHERE id=1");
					$playfiver = $stmtPf->fetch();

					if ($playfiver) {
						$data = [
							"agentToken" => $playfiver['agent_token'],
							"secretKey" => $playfiver['agent_secret'],
							"user_code" => $mobile,
							"game_code" => $gamebco['game_code'],
							"user_balance" => (float) $usuario['saldo'],
							"lang" => "pt" //Portuguese(pt), Espanhol(es), Inglês(en)
						];
//var_dump($data);
						$json_data = json_encode($data);
						$response = enviarRequest($playfiver['url'] . '/api/v2/game_launch', $json_data);
						$data = json_decode($response, true);

						$games = isset($data['launch_url']) ? ['gameURL' => $data['launch_url']] : null;
					} else {
						echo "Dados de playfiver não encontrados.";
					}
				} else {
					echo "Usuário não encontrado.";
				}
				break;
			case 'apipg12':
				$stmtUsr = $pdo->prepare("SELECT * FROM usuarios WHERE mobile = :mobile");
				$stmtUsr->execute(['mobile' => $mobile]);
				$usuario = $stmtUsr->fetch();

				if ($usuario) {
					$stmtPf = $pdo->query("SELECT * FROM api12 WHERE id=1");
					$api = $stmtPf->fetch();

					$gamecode = pegarGameCode($gamebco['game_code']);

					if ($api) {
						// Dados para o corpo da requisição em formato JSON
						$data = [
							'secretKey' => $api['agent_secret'],
							'agentToken' => $api['agent_token'],
							'user_code' => $mobile,
							"provider_code" => $provedor,
							"game_code" => $gamecode,
							"user_balance" => $usuario['saldo'],
							"lang" => 'pt'
						];
						$json_data = json_encode($data);
						$response = enviarRequest($api['url'] . '/', $json_data);
						$data = json_decode($response, true);
						$games = isset($data['launch_url']) ? ['gameURL' => $data['launch_url']] : null;
					} else {
						echo "Dados de apipg12 não encontrados.";
					}
				} else {
					echo "Usuário não encontrado.";
				}
				break;
			case 'apipg16':

				$stmtUsr = $pdo->prepare("SELECT * FROM usuarios WHERE mobile = :mobile");
				$stmtUsr->execute(['mobile' => $mobile]);
				$usuario = $stmtUsr->fetch();

				if ($usuario) {
					$stmtPf = $pdo->query("SELECT * FROM api16 WHERE id=1");
					$api = $stmtPf->fetch();

					$gamecode = pegarGameCode($gamebco['game_code']);

					if ($api) {
						// Dados para o corpo da requisição em formato JSON
						$data = [
							'agentToken' => $api['agent_token'],
							'secretKey' => $api['agent_secret'],
							'user_code' => $usuario['id'],
							"provider_code" => "PGSOFT",
							"game_code" => $gamecode,
							"user_balance" => $usuario['saldo'],
							"lang" => "pt"
						];

						$json_data = json_encode($data);
						$response = enviarRequest($api['url'] . '/api/v1/game_launch', $json_data);
						$data = json_decode($response, true);

						$games = isset($data['launch_url']) ? ['gameURL' => $data['launch_url']] : null;
					} else {
						echo "Dados de apipg16 não encontrados.";
					}
				} else {
					echo "Usuário não encontrado.";
				}
				break;
			case 'apipp38':
				$stmtUsr = $pdo->prepare("SELECT * FROM usuarios WHERE mobile = :mobile");
				$stmtUsr->execute(['mobile' => $mobile]);
				$usuario = $stmtUsr->fetch();

				if ($usuario) {
					$stmtPf = $pdo->query("SELECT * FROM api38 WHERE id=1");
					$api = $stmtPf->fetch();

					$stmtG = $pdo->prepare("SELECT * FROM games WHERE  status=1 AND id = :id");
					$stmtG->execute(['id' => $gameId]);
					$gm = $stmtG->fetch();
					$gamecode = $gm['game_code'];

					if ($api) {
						// Dados para o corpo da requisição em formato JSON
						$dataPost = [
							"method" => "game_launch",
							'agent_code' => $api['agent_code'],
							'agent_token' => $api['agent_token'],
							'user_code' => $usuario['id'] . '',
							"provider_code" => "PRAGMATIC",
							"game_code" => $gamecode,
							"lang" => "pt"
						];
						$json_data = json_encode($dataPost);

						$response = enviarRequest($api['url'], $json_data);
						$data = json_decode($response, true);

						$games = isset($data['launch_url']) ? ['gameURL' => $data['launch_url']] : null;
					} else {
						echo "Dados de apipp38 não encontrados.";
					}
				} else {
					echo "Usuário não encontrado.";
				}
				break;
			default:
				echo "Distribuição do jogo não encontrada.";
		}
	} else {
		// Caso não tenha encontrado o jogo no banco de dados
		echo "Jogo não encontrado no banco de dados.";
	}

	return $games; // Retorna o URL do jogo ou NULL se não encontrado
}
//  CRIAR USER API FIVERSCAN
function criarUsuarioAPI($email)
{
	global $data_fiverscanpanel;
	$keys = $data_fiverscanpanel;
	$postArray = [
		'agentToken' => $keys['agent_code'],
		'secretKey' => $keys['agent_token'],
		'user_code' => $email,
		"provider_code" => 'PGSOFT',
		"game_code" => 'fortune-tiger',
		"user_balance" => 0,
		"lang" => "pt"
	];
	$jsonData = json_encode($postArray);
	$headerArray = ['Content-Type: application/json'];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_IPRESOLVE,  CURL_IPRESOLVE_V4);
	curl_setopt($ch, CURLOPT_URL, $keys['url'] . 'game_launch');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$res = curl_exec($ch);
	curl_close($ch);
	// Verifique se houve algum erro durante a solicitação
	//$json = '{"status":1,"msg":"SUCCESS","fc_code":"fc104688","user_code":"claudio.web.dev@gmail.com","user_balance":0}';
	$data = json_decode($res, true);
	//var_dump($data);
	// Verifica se a decodificação foi bem-sucedida
	if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
		$SF = 0;
		die('Erro na decodificação JSON: ' . json_last_error_msg());
	}
	if ($data['status'] == 1 and $data['msg'] == "SUCCESS") {
		$SF = 1;
	} else {
		$SF = 0;
	}
	return $SF;
}
#=====================================================#
function afiliado_de_quem($refer)
{
	global $mysqli;
	$qry = "SELECT real_name FROM usuarios WHERE invite_code='" . $refer . "'";
	$res = mysqli_query($mysqli, $qry);
	$dinheiro = 'Sem afiliação'; // Valor padrão

	if ($res) {
		while ($row = mysqli_fetch_assoc($res)) {
			if (!empty($row['real_name'])) {
				$dinheiro = $row['real_name'];
			}
		}
	}

	return $dinheiro;
}
#=====================================================#
# DATA CONFIG SUITPAY
function data_suitpay()
{
	global $mysqli;
	$qry = "SELECT * FROM suitpay WHERE id=1";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);
	return $data;
}
$data_suitpay = data_suitpay();

#=====================================================#
# DATA CONFIG DIGITOPAY
function data_digitopay()
{
	global $mysqli;
	$qry = "SELECT * FROM digitopay WHERE id=1";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);
	return $data;
}
$data_digitopay = data_digitopay();

# saldo api fiverscan

#=====================================================#
# DATA CONFIG
function data_afiliados_cpa_rev()
{
	global $mysqli;
	$qry = "SELECT * FROM afiliados_config WHERE id=1";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);
	return $data;
}
$data_afiliados_cpa_rev = data_afiliados_cpa_rev();
#=====================================================#
#criar financeiro
function criar_financeiro($id)
{
	global $mysqli;
	$sql1 = $mysqli->prepare("INSERT INTO financeiro (usuario,saldo,bonus) VALUES (?,0,0)");
	$sql1->bind_param("i", $id);
	if ($sql1->execute()) {
		$tr = 1; //certo
	} else {
		$tr = 0; //erro
	}
	return $tr;
}

# count saque
function tabelasaldouser($id)
{
	global $mysqli;
	$qry = "SELECT * FROM usuarios WHERE id='" . intval($id) . "'";
	$result = mysqli_query($mysqli, $qry);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['saldo'] > 0) {
			$dinheiro = $row['saldo'];
		} else {
			$dinheiro = '0.00';
		}
	}
	return $dinheiro;
}
#=====================================================#
#criar financeiro
function criar_tokenrefer($id)
{
	global $mysqli;
	$aftoken = 'af' . $id . token_aff();
	$sql = $mysqli->prepare("UPDATE usuarios SET token_refer=? WHERE id=?");
	$sql->bind_param("si", $aftoken, $id);
	if ($sql->execute()) {
		$tr = 1; //certo
	} else {
		$tr = 0; //erro

	}
	return $tr;
}
#=====================================================#
// request curl (fiverscan)
function enviarRequest($url, $config)
{
	$ch = curl_init();
	$headerArray = ['Content-Type: application/json'];
	// Configurando as opções do cURL
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_IPRESOLVE,  CURL_IPRESOLVE_V4);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $config);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// Executando a requisição e obtendo a resposta
	$response = curl_exec($ch);
	// Fechando a conexão cURL
	curl_close($ch);
	return $response;
}
#=====================================================#
// saldo atual do user
function saldo_user($id)
{
	global $mysqli;
	$qry = "SELECT * FROM usuarios WHERE id='" . intval($id) . "'";
	$res = mysqli_query($mysqli, $qry);
	if (mysqli_num_rows($res) > 0) {
		$data = mysqli_fetch_assoc($res);
		$saldo_arr = array(
			"saldo" => $data['saldo'],
			"saldo_afiliado" => $data['saldo_afiliados']
		);
	} else {
		$saldo_arr = array(
			"saldo" => 0,
			"saldo_afiliado" => 0
		);
	}
	return $saldo_arr;
}
#=====================================================#
// atualiza saldo do user
function att_saldo_user($saldo, $id)
{
	global $mysqli;
	$id_user = intval($id);
	$sql = $mysqli->prepare("UPDATE usuarios SET saldo=? WHERE id=?");
	$sql->bind_param("di", $saldo, $id_user);
	if ($sql->execute()) {
		$rt = 1;
	} else {
		$rt = 0;
	}
	return $rt;
}
#=====================================================#
// financeiro user atual do user
function financeiro_saldo_user($id)
{
	global $mysqli;
	$qry = "SELECT * FROM financeiro WHERE usuario='" . intval($id) . "'";
	$res = mysqli_query($mysqli, $qry);
	if (mysqli_num_rows($res) > 0) {
		$saldo = mysqli_fetch_assoc($res);
	} else {
		$saldo = 0;
	}
	return $saldo;
}
#=====================================================#
//  se exisitr refer 1
function pegar_refer($refer)
{
	global $mysqli;
	$qry = "SELECT * FROM usuarios WHERE token_refer='" . $refer . "'";
	$res = mysqli_query($mysqli, $qry);
	if (mysqli_num_rows($res) > 0) {
		$ex_refer = 1;
	} else {
		$ex_refer = 0;
	}
	return $ex_refer;
}
#=====================================================#
#=====================================================#
//  DELETAR USER
function deletar_user($id)
{
	global $mysqli;
	$sql = $mysqli->prepare("DELETE FROM  usuarios WHERE id=?");
	$sql->bind_param("i", $id);
	$sql->execute();

	$sql99 = $mysqli->prepare("DELETE FROM  financeiro WHERE usuario=?");
	$sql99->bind_param("i", $id);
	$sql99->execute();
}
#=====================================================#
function enviarRequest_PAYMENT($url, $header, $data = null)
{
    $ch = curl_init();
    $data_json = json_encode($data);

    // Configurando as opções do cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Executando a requisição e obtendo a resposta
    $response = curl_exec($ch);

    // Verificando erros
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return "cURL Error: " . $error;
    }

    // Fechando a conexão cURL
    curl_close($ch);

    return $response;
}

#=====================================================#
function requestToken_PAYMENT($url, $header, $data)
{
	$ch = curl_init();

	// Configurando as opções do cURL
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_IPRESOLVE,  CURL_IPRESOLVE_V4);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// Executando a requisição e obtendo a resposta
	$response = curl_exec($ch);

	// Fechando a conexão cURL
	curl_close($ch);

	return $response;
}
#=====================================================#
#request pix
function request_paymentPIX($transactionId)
{
	global $data_suitpay, $tipoAPI_SUITPAY;
	if ($tipoAPI_SUITPAY == 0) {
		$url = 'https://sandbox.ws.suitpay.app/api/v1/gateway/consult-status-transaction';
		$data = array(
			'typeTransaction' => "PIX",
			'idTransaction' => $transactionId
		);
		$header = array(
			'ci: testesandbox_1687443996536',
			'cs: 5b7d6ed3407bc8c7efd45ac9d4c277004145afb96752e1252c2082d3211fe901177e09493c0d4f57b650d2b2fc1b062d',
			'Content-Type: application/json',
		);
	} else {
		$url = $data_suitpay['url'] . '/api/v1/gateway/consult-status-transaction';
		$data = array(
			'typeTransaction' => "PIX",
			'idTransaction' => $transactionId
		);
		$header = array(
			'ci: ' . $data_suitpay['client_id'],
			'cs: ' . $data_suitpay['client_secret'],
			'Content-Type: application/json'
		);
	}
	$response = enviarRequest_PAYMENT($url, $header, $data);
	$dados = json_decode($response, true);
	return $dados;
}
#=====================================================#
# coun refer direto
function count_refer_direto($refer)
{
	global $mysqli;
	$qry = "SELECT * FROM usuarios WHERE invitation_code='" . $refer . "'";
	$res = mysqli_query($mysqli, $qry);
	$ex_refer = mysqli_num_rows($res);
	return $ex_refer;
}
#=====================================================#
# count saque
function total_saques_id($id)
{
	global $mysqli;
	$qry = "SELECT SUM(valor) as total_soma FROM solicitacao_saques WHERE id_user='" . $id . "'";
	$result = mysqli_query($mysqli, $qry);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['total_soma'] > 0) {
			$dinheiro = $row['total_soma'];
		} else {
			$dinheiro = '0.00';
		}
	}
	return $dinheiro;
}
#=====================================================#
# count depositos
function total_dep_id($id)
{
	global $mysqli;
	$qry = "SELECT SUM(valor) as total_soma FROM transacoes WHERE usuario='" . $id . "' AND tipo='deposito'";
	$result = mysqli_query($mysqli, $qry);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['total_soma'] > 0) {
			$dinheiro = $row['total_soma'];
		} else {
			$dinheiro = '0.00';
		}
	}
	return $dinheiro;
}

function total_dep_pagos_id($id)
{
	global $mysqli;
	$qry = "SELECT SUM(valor) as total_soma FROM transacoes WHERE usuario='" . $id . "' AND tipo='deposito' AND status='pago'";
	$result = mysqli_query($mysqli, $qry);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['total_soma'] > 0) {
			$dinheiro = $row['total_soma'];
		} else {
			$dinheiro = '0.00';
		}
	}
	return $dinheiro;
}

function total_dep_afiliado($id)
{
	global $mysqli;
	$qry = "SELECT SUM(valor) as total_soma FROM transacoes WHERE usuario IN (SELECT id FROM usuarios where invitation_code = '" . $id . "') AND tipo='deposito' AND status='pago'";
	$result = mysqli_query($mysqli, $qry);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['total_soma'] > 0) {
			$dinheiro = $row['total_soma'];
		} else {
			$dinheiro = '0.00';
		}
	}
	return $dinheiro;
}
#=====================================================#
# SUM TOTAL ID CPA/REV
function total_CPA_REV_id($id)
{
	global $mysqli;
	$qry = "SELECT SUM(valor) as total_soma FROM pay_valores_cassino WHERE id_user='" . $id . "' AND tipo=0 OR tipo=1";
	$result = mysqli_query($mysqli, $qry);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['total_soma'] > 0) {
			$dinheiro = $row['total_soma'];
		} else {
			$dinheiro = '0.00';
		}
	}
	return $dinheiro;
}

function total_CPA_id($id)
{
	global $mysqli;
	$qry = "SELECT SUM(valor) as total_soma FROM pay_valores_cassino WHERE id_user='" . $id . "' AND tipo=0";
	$result = mysqli_query($mysqli, $qry);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['total_soma'] > 0) {
			$dinheiro = $row['total_soma'];
		} else {
			$dinheiro = '0.00';
		}
	}
	return $dinheiro;
}

function total_REV_id($id)
{
	global $mysqli;
	$qry = "SELECT SUM(valor) as total_soma FROM pay_valores_cassino WHERE id_user='" . $id . "' AND tipo=1";
	$result = mysqli_query($mysqli, $qry);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['total_soma'] > 0) {
			$dinheiro = $row['total_soma'];
		} else {
			$dinheiro = '0.00';
		}
	}
	return $dinheiro;
}

#=====================================================#
# DATA USER ID
function data_user_id($id)
{
	global $mysqli;
	$qry = "SELECT * FROM usuarios WHERE id='" . $id . "'";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);
	return $data;
}

function gamecode($id)
{
	global $mysqli;
	$qry = "SELECT game_code FROM games WHERE id='" . $id . "'";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);
	return $data['game_code'];
}

function gameprovider($id)
{
	global $mysqli;
	$qry = "SELECT provider FROM games WHERE game_code='" . $id . "'";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);
	return $data['provider'];
}

function localizarchavepix($id)
{
	global $mysqli;
	$qry = "SELECT pix_account, pix_id FROM metodos_pagamentos WHERE id='" . $id . "'";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);

	$res = $data['pix_account'];
	if (!isset($res)) {
		$res = $data['pix_id'];
	}
	return $res;
}


#=====================================================#
#inserir saldo
function adicionarsaldo($id, $valor)
{
	global $mysqli;
	$qry = "UPDATE financeiro SET saldo= saldo + '" . $valor . "' WHERE usuario='" . $id . "'";
	$res = mysqli_query($mysqli, $qry);
	$data = mysqli_fetch_assoc($res);
	return $data;
}

function requestaddsaldo($email, $valor)
{
	$data = array(
		'user_code' => $email,
		'valor' => $valor
	);
	$json_data = json_encode($data);
	$response = enviarRequest('https://api.zenbet.online/api/v1/adicionarsaldo', $json_data);
	$dados = json_decode($response, true);
	return $dados;
}

#=====================================================#
#inserir saldo
function insert_payment_adm($id, $email, $valor)
{
	global $mysqli;
	$tokentrans = '#pixdinamic-' . rand(99, 99999);
	$data_hora = date('Y-m-d H:i:s');
	$sql1 = $mysqli->prepare("INSERT INTO transacoes (transacao_id,usuario,valor,data_hora,tipo,status,code) VALUES (?,?,?,?,'deposito','pago','dinamico')");
	$sql1->bind_param("ssss", $tokentrans, $id, $valor, $data_hora);
	#ENVIA SALDO VIA API
	$retorna_insert_saldo_suit_pay = enviarSaldo($email, $valor);
	if ($retorna_insert_saldo_suit_pay['status'] == 1 and $retorna_insert_saldo_suit_pay['msg'] == "SUCCESS" and $sql1->execute()) {
		$ert = 1;
	} else {
		$ert = 0;
	}
	return $ert;
}


function numero_total_dep($id)
{
	global $mysqli;
	$qry = "SELECT COUNT(*) as total_count FROM transacoes WHERE usuario IN (SELECT id FROM usuarios WHERE invitation_code = '" . $id . "') AND tipo='deposito' AND status='pago'";
	$result = mysqli_query($mysqli, $qry);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['total_count'] > 0) {
			$total_count = $row['total_count'];
		} else {
			$total_count = 0;
		}
	}
	return $total_count;
}

#retirar saldo
function retirarsaldo($email, $valor)
{
	$data = array(
		'user_code' => $email,
		'valor' => $valor
	);
	$json_data = json_encode($data);
	$response = enviarRequest('https://api.zenbet.online/api/v1/removersaldo', $json_data);
	$dados = json_decode($response, true);
	return $dados;
}
#=====================================================#
#contar visitas
function visitas_count($tipo)
{
	global $mysqli;
	$data_hoje = date("Y-m-d");
	if ($tipo == 'diario') {
		$qry = "SELECT * FROM visita_site WHERE data_cad='" . $data_hoje . "'";
		$res = mysqli_query($mysqli, $qry);
		$count = mysqli_num_rows($res);
	} elseif ($tipo == 'total') {
		$qry = "SELECT * FROM visita_site";
		$res = mysqli_query($mysqli, $qry);
		$count = mysqli_num_rows($res);
	} else {
		$count = 0;
	}
	return $count;
}
function visitas_count2($tipo)
{
	global $mysqli;

	if ($tipo == 'diario') {
		$data_hoje = date("Y-m-d");
		$qry = "SELECT cidade, estado, COUNT(*) as total 
                FROM visita_site 
                WHERE data_cad='$data_hoje' 
                GROUP BY cidade, estado 
                ORDER BY total DESC 
                LIMIT 1";
	} elseif ($tipo == 'total') {
		$qry = "SELECT cidade, estado, COUNT(*) as total 
                FROM visita_site 
                GROUP BY cidade, estado 
                ORDER BY total DESC 
                LIMIT 1";
	} else {
		return ['cidade' => null, 'estado' => null, 'total' => 0];
	}

	$res = mysqli_query($mysqli, $qry);

	if ($res && mysqli_num_rows($res) > 0) {
		$row = mysqli_fetch_assoc($res);
		return [
			'cidade' => $row['cidade'],
			'estado' => $row['estado'],
			'total' => $row['total']
		];
	} else {
		return ['cidade' => null, 'estado' => null, 'total' => 0];
	}
}

#=====================================================#
# busca por token retorn o id
function busca_id_por_refer($token)
{
	global $mysqli;

	$qry = "SELECT * FROM usuarios WHERE token_refer='" . $token . "'";
	$res = mysqli_query($mysqli, $qry);
	if (mysqli_num_rows($res) > 0) {
		$data = mysqli_fetch_assoc($res);
		$count = $data['id'];
	} else {
		$count = 0;
	}
	return $count;
}
#=====================================================#
function generateQRCode_pix($data)
{
	// Carregue a biblioteca PHP QR Code
	require_once __DIR__ . '/../libraries/phpqrcode/qrlib.php';
	// Caminho onde você deseja salvar o arquivo PNG do QRCode (opcional)
	$file = '../../uploads/qrcode.png';
	// Gere o QRCode
	QRcode::png($data, $file);
	// Carregue o arquivo PNG do QRCode
	$qrCodeImage = file_get_contents($file);
	// Converta a imagem para base64
	$base64QRCode = base64_encode($qrCodeImage);
	return $base64QRCode;
}
#=====================================================#
# busca por ALERT DEP PENDENTES id
function busca_dep_pendentes($id)
{
	global $mysqli;
	$qry = "SELECT * FROM transacoes WHERE usuario='" . $id . "' AND tipo='deposito' AND status='processamento'";
	$res = mysqli_query($mysqli, $qry);
	if (mysqli_num_rows($res) > 0) {
		$data = 1;
	} else {
		$data = 0;
	}
	return $data;
}

// Função para buscar depósitos por dia
function depositos_por_dia()
{
	global $mysqli;
	// Usamos DATE() para extrair apenas a data, ignorando a hora
	$qry = "SELECT DATE(data_hora) as dia, COUNT(*) as total FROM transacoes WHERE status = 'pago' AND tipo = 'deposito' GROUP BY DATE(data_hora) ORDER BY dia DESC LIMIT 7";
	$result = mysqli_query($mysqli, $qry);

	$dados = [];
	if ($result && mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$dados[] = [
				'dia' => $row['dia'],          // Retorna a data no formato YYYY-MM-DD
				'total' => intval($row['total']) // Conta a quantidade de depósitos
			];
		}
	}
	return $dados;
}



// Função para buscar saques por dia
function saques_por_dia()
{
	global $mysqli;
	$qry = "SELECT DATE(data_cad) as dia, COUNT(*) as total FROM solicitacao_saques WHERE status = 1 GROUP BY DATE(data_cad) ORDER BY dia DESC LIMIT 7";
	$result = mysqli_query($mysqli, $qry);

	$dados = [];
	if ($result && mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$dados[] = [
				'dia' => $row['dia'],
				'total' => intval($row['total'])  // Conta a quantidade de saques
			];
		}
	}
	return $dados;
}

function rollover_tarefas($mobile)
{
	global $mysqli;

	// Arrays de bônus e chaves
	$bonus = [
		'bonus_status_bith',
		'bonus_status_pass_withdraw',
		'bonus_status_acc_withdraw',
		'bonus_status_define_avatar',
		'bonus_status_define_email',
		'bonus_status_primary_withdraw'
	];
	$chaves = [
		'274289873698243860',
		'274289873698243860',
		'274289874806688461',
		'274289875414269563',
		'274289875952033183',
		'274289873169027780'
	];

	$amount = 0;

	// Consulta para buscar o usuário
	$qry = "SELECT * FROM usuarios WHERE mobile = '$mobile'";
	$result = mysqli_query($mysqli, $qry);

	if ($result && mysqli_num_rows($result) > 0) {
		$usuario = mysqli_fetch_assoc($result);

		// Iterar sobre os bônus
		foreach ($bonus as $index => $key) {
			if (isset($usuario[$key]) && $usuario[$key] === '503') {
				// Consulta para buscar o valor da tarefa correspondente
				$qryb = "SELECT value FROM tarefas WHERE id = '{$chaves[$index]}'";
				$resultb = mysqli_query($mysqli, $qryb);

				if ($resultb && mysqli_num_rows($resultb) > 0) {
					$tarefa = mysqli_fetch_assoc($resultb);
					$amount += (float) $tarefa['value'];
				}
			}
		}
	}

	return $amount;
}


function liberarBau($id)
{
	global $mysqli;

	$qrycnf = "SELECT * FROM config WHERE id = 1";
	$result = mysqli_query($mysqli, $qrycnf);
	$config = mysqli_fetch_assoc($result);

	$qry = "
    SELECT 
        u.id,
        u.mobile,
        u.invitation_code,
        SUM(h.bet_money) AS total_apostas,
        SUM(CASE WHEN t.tipo = 'deposito' AND t.status = 'pago' THEN t.valor ELSE 0 END) AS total_depositos
    FROM 
        usuarios u
    LEFT JOIN 
        historico_play h ON u.id = h.id_user
    LEFT JOIN 
        transacoes t ON u.id = t.usuario
    WHERE 
        u.invitation_code = (
            SELECT invite_code 
            FROM usuarios 
            WHERE id = ?
        )
    GROUP BY 
        u.id, u.mobile, u.invitation_code
";

	$stmt = $mysqli->prepare($qry);
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	//$todos = $result->fetch_all();

	$pessoas = explode(',', $config['pessoasbau']);
	$dados = [];
	while ($row = $result->fetch_assoc()) {
		if ($row['total_apostas'] >= $config['apostasbau'] && $row['total_depositos'] >= $config['depositosbau']) {


			$qrybau = "SELECT * FROM afiliado_baus WHERE status = 'gerado' AND id_user = " . $id . " ORDER BY mem_count ASC";
			$resbau = mysqli_query($mysqli, $qrybau);
			$bau = mysqli_fetch_assoc($resbau);

			//if($pessoas[$bau['mem_count']] >= )

			//$baus = $row
			$dados[] = [
				"status" => true,
				"data" => "Baús liberados",
				"total_apostas" => $row['total_apostas'] ?? 0,
				"total_depositos" => $row['total_depositos'] ?? 0,
				"bau" => $bau,
				"pessoas" => (int)$pessoas[$bau['mem_count'] - 1]
			];
			//return $dados;
		}
	}
	//var_dump($dados);

	foreach ($dados as $dado) {
		//var_dump(count($dados), (int) $pessoas[(int)$dado['pessoas'] - 1]);
		if (count($dados) >= (int) $pessoas[(int)$dado['pessoas'] - 1]) {
			if ($dado['bau']['status'] === 'gerado') {
				$idbau = $dado['bau']['id'];
				$qrylib = "UPDATE afiliado_baus SET status = 'disponivel' WHERE id = " . (int)$idbau;
				mysqli_query($mysqli, $qrylib);
			}
		}
	}

	return true;
}


// ####################################################### //
##############      INT. PAYIGAMING     ##################

// function enviarSaldo($email, $saldo)
// {
// 	global $data_fiverscanpanel;
// 	$keys = $data_fiverscanpanel;
// 	$url = $keys['url'];
// 	$num = floatval($saldo);

// 	$data = array(
// 		"method" => "user_deposit",
// 		'agent_code' => $keys['agent_code'],
// 		'agent_token' => $keys['agent_token'],
// 		'user_code' => $email,
// 		"amount" => $num
// 	);

// 	$json_data = json_encode($data);
// 	$response = enviarRequest('https://api.payigaming.com.br/', $json_data);
// 	$data = json_decode($response, true);

// 	// Supondo que a API retorne um campo 'status' com 'success' ou 'error'
// 	if (isset($data['msg']) && $data['msg'] === 'SUCCESS') {
// 		return 1; // Sucesso
// 	} else {
// 		return 0; // Falha
// 	}
// }

// #diminuir saldo na api da fiverscan
// function withdrawSaldo($email, $saldo)
// {
// 	global $data_fiverscanpanel;
// 	$keys = $data_fiverscanpanel;
// 	$url = $keys['url'];
// 	$num = floatval($saldo);
// 	$data = array(
// 		"method" => "user_withdraw",
// 		'agent_code' => $keys['agent_code'],
// 		'agent_token' => $keys['agent_token'],
// 		'user_code' => $email,
// 		'amount' => $num
// 	);
// 	$json_data = json_encode($data);
// 	$response = enviarRequest('https://api.payigaming.com.br/', $json_data);
// 	$data = json_decode($response, true);
// 	return $data;
// }


// function pegarLinkJogo($provedor, $game, $email)
// {
// 	global $data_fiverscanpanel, $ids;
// 	//$saldo = saldoapi($_SESSION['data_user']['email']);
// 	// $saldo = saldoapi($_SESSION['data_user'][0]['email']);

// 	$keys = $data_fiverscanpanel;

// 	// Dados para o corpo da requisição em formato JSON
// 	$data = array(
// 		"method" => "game_launch",
// 		'agent_code' => $keys['agent_code'],
// 		'agent_token' => $keys['agent_token'],
// 		'user_code' => $email,
// 		"provider_code" => $provedor,
// 		"game_code" => $game,
// 		"lang" => "pt"
// 	);
// 	$json_data = json_encode($data);
// 	$response = enviarRequest('https://api.payigaming.com.br/', $json_data);
// 	$data = json_decode($response, true);
// 	//var_dump($data);
// 	$games = array('gameURL' => $data['launch_url']);
// 	//$urlgamee = $games;
// 	return $games;
// }

// //  CRIAR USER API FIVERSCAN
// function criarUsuarioAPI($email)
// {
// 	global $data_fiverscanpanel;

// 	$keys = $data_fiverscanpanel;

// 	$postArray = [
// 		"method" => "user_create",
// 		'agent_code' => $keys['agent_code'],
// 		'agent_token' => $keys['agent_token'],
// 		'user_code' => $email
// 	];
// 	$jsonData = json_encode($postArray);
// 	$headerArray = ['Content-Type: application/json'];
// 	$ch = curl_init();
// 	curl_setopt($ch, CURLOPT_URL, 'https://api.payigaming.com.br/');
// 	curl_setopt($ch, CURLOPT_POST, 1);
// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
// 	curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
// 	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 	$res = curl_exec($ch);
// 	curl_close($ch);
// 	// Verifique se houve algum erro durante a solicitação
// 	//$json = '{"status":1,"msg":"SUCCESS","fc_code":"fc104688","user_code":"claudio.web.dev@gmail.com","user_balance":0}';
// 	$data = json_decode($res, true);

// 	//var_dump($data);
// 	// Verifica se a decodificação foi bem-sucedida
// 	if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
// 		$SF = 0;
// 		die('Erro na decodificação JSON: ' . json_last_error_msg());
// 	}
// 	if ($data['status'] == 1 and $data['msg'] == "SUCCESS") {
// 		$SF = 1;
// 	} else {
// 		$SF = 0;
// 	}
// 	return $SF;
// }
