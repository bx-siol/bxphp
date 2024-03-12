<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['didapay'] = [
	'mch_id' => 'E3013',

	//'didapay_key' => 'aavse2silxjgteodvpnpp5894morbmqn',


	'dpay_url' => 'https://api.didapay.in/api/payout',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/didapay/cash',

	'mch_key' =>  'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAPzrWRRu2J3l4lPRts8nfqUu8uKCHrCD3fqt26qPhCkB2smzGKpGXD1Imn/Kns6hcTr+gZr6LhH2/VnvmAl3/mp6gfpILBJLJ/48DbA4y5FAwLB/4/H3dLM+iytOoFNAaCdLh99kg8xoico4xd9MMDarHawwBHKEphMITyv+z+UvAgMBAAECgYEAlR5ttw5jyTTw7FqJXjn7AYlcyw/M6GXXSyWWjklvsn3VcXaW5E33tGpKLW5Zk5q8F/xWjflTkGP/nDcXfP2ykgj6+cPQFYS3DlcJVnCvu7C4mp/kOnTuhSgf3QgTmeLsuNF/qVdfDL0KEi6jO8yWOukQm6171eHJPhkV0V84zQECQQD/RdFSMDYc4/0P7CISfRZKLEz4NGuyek3wI8VQ1a8CexGXCXJgMgfVAqfoeeo4M1iQN1e2akjcMD7QdfSzCpM3AkEA/aPQWXTa4Hs2ZiukczmD+HWSiiGfVOzdyfiyJ977OmJag3a4w2OnxOF0aL9uxaMjb7uUMjYts7PXmElMkcWpyQJAfnlC6iuEw++ZM8hUYUCkSH+Gavrd2QGLl8zBN/mGyf3biy9dAZgIFVtJgX7Vsp6N5HXm+TPTgXRNys6GPCajJQJAVywB43zh7Nzr6Vl+f4t469+cqZS8qfdukofC0ykztvEuopgfECgj9Op3k7iXXZ2gBDq3yDoOowBgTJqEkmUY4QJBAJDF5hq7a+kP8C8rGWYRmwcbk2CmXejia6ms8/nJ+86clSfxzzXYugEi1CHQPFuGXZ8HOQQB1NC4VO6g9W4pbwA=',
	'p_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCmJMCX6W68dJmZ5AhGoj9DHU/x5ILiQI93ewiLDdFgOdZXPMmNP9Xon6S2Xl2Ki5xbnml2NmKrK3DrJ93XOLCJWejyAu/q2L2QUTyGMhP+7dbhKe5bLdrqnQ/s1fBSXjnTosH46fxz2yMNEk5xwfrUHnk4D9qcM25qxhkXY5+skQIDAQAB',
];

function didapayCashOrder($fin_cashlog)
{
	$config = $_ENV['PAY_CONFIG']['didapay'];
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "didapay/cash/{$time}.txt";

	$pdata = [
		'merchantNo' => $config['mch_id'],
		'merchantOrderNo' => $fin_cashlog['osn'],
		'description' => 'description', //'10703',
		'payAmount' =>  $fin_cashlog['real_money'],
		'mobile' => $phone,
		'email' => $fin_cashlog['receive_account'] . '@gmail.com',
		// 'ifsc' => $fin_cashlog['receive_ifsc'],
		'notifyUrl' => $config['dnotify_url'],
		'bankNumber' => $fin_cashlog['receive_account'],
		'bankCode' => $fin_cashlog['receive_ifsc'],
		'accountHoldName' => $fin_cashlog['receive_realname'],

		/*''
		'name' => $fin_cashlog['receive_realname'],
		'account' => intval($fin_cashlog['receive_account']),
		
		'card_info_type' => $fin_cashlog['receive_bank_id'],
		'bank_name' => $fin_cashlog['receive_bank_name'],
		*/
		//json_encode([
		// 	'accountType' => "BANK",
		// 	'accountInfo' => ["name" => $fin_cashlog['receive_realname'], "accountNumber" => $fin_cashlog['receive_account'], "ifsc" =>  $fin_cashlog['receive_ifsc']],
		// ], JSON_UNESCAPED_SLASHES)
	];
	$pdata['sign'] = didapayCashSign($pdata);
	$url = $config['dpay_url'];

	$result = didapayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd, $url . "\r\n ================= \r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES) . "\r\n" . json_encode($resultArr, JSON_UNESCAPED_SLASHES) . "\r\n\r\n", FILE_APPEND);

	if ($resultArr['status'] != '200') {
		return ['code' => -1, 'msg' => $resultArr['message']];
	}
	//$ooc = json_decode(stripslashes($resultArr['data']), true);
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['data']['platOrderNo']
		]
	];
	return $return_data;
}

function didapayCashSign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['didapay'];
	$fliepath =	LOGS_PATH . 'didapay/csignpay' . date("Y-m-d", time()) . '.txt';

	ksort($pdata);
	$request_data = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign' || $pk == 'sign_type' || $pk == 'signType') {
			continue;
		}
		$request_data .= "{$pv}";
	}

	//解析私钥
	$res = openssl_pkey_get_private("-----BEGIN RSA PRIVATE KEY-----\n" . $config['mch_key'] . "\n-----END RSA PRIVATE KEY-----");

	$content = '';
	//使用私钥加密
	foreach (str_split($request_data, 117) as $str1) {
		openssl_private_encrypt($str1, $crypted, $res);
		$content .= $crypted;
	}
	$encrypted = base64_encode($content);
	file_put_contents($fliepath, "\r\n" . $config['mch_key'] . "\r\n\r\n" . $request_data . "\r\n\r\n" . json_encode($pdata)   . "\r\n" . $encrypted . "\r\n\r\n", FILE_APPEND);
	//编码转换
	return $encrypted;
}


function dSign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['didapay'];
	$fliepath =	LOGS_PATH . 'didapay/dcsignpay' . date("Y-m-d", time()) . '.txt';

	ksort($pdata);
	$request_data = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign') {
			continue;
		}
		$request_data .= "{$pv}";
	}

	$encrypted = $pdata['sign'];
	$encrypted = str_replace('-', '+', $encrypted);
	$encrypted = str_replace('_', '/', $encrypted);
	//解析公钥
	$res = openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" . $config['p_key'] . "\n-----END PUBLIC KEY-----");

	openssl_public_decrypt(base64_decode($encrypted), $decrypted, $res);

	//var_dump($decrypted);
	file_put_contents($fliepath, "\r\n" . json_encode($pdata) . "\r\n" . $decrypted . "\r\n" . $pdata['sign']  . "\r\n" . $config['p_key'] . "\r\n\r\n", FILE_APPEND);
	//编码转换
	return $request_data == $decrypted;
}


function didapayCurlPost($url, $data = [], $timeout = 30)
{
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => $timeout,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json;charset=UTF-8'
		)
	));

	$response = curl_exec($curl);
	if ($curl->error) {
		$arrCurlResult = [
			'code' => -1,
			'msg' => $curl->errorMessage
		];
	} else {
		$arrCurlResult = [
			'code' => 1,
			'msg' => 'ok',
			'output' => json_decode($response, true)
		];
	}
	curl_close($curl);
	unset($curl);
	return $arrCurlResult;
}
