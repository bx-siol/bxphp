<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "zippay";
}
function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'merId' => $config['mch_id'],
		'orderId' => $fin_paylog['osn'],
		'orderAmt' => number_format($fin_paylog['money'], 2, '.', ''),
		'desc' => 'desc',
		'channel' => 'IND0',
		'ip' => SERVER_IP,
		'notifyUrl' => $config['notifyUrl'],
		'returnUrl' => $config['returnUrl'],
		'nonceStr' => getRsn()
	];
	$pdata['sign'] = paySign($pdata);
	$url = $config['pay_url'];
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	if ($resultArr['code'] != '1') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['sysorderno'],
			'pay_url' => $resultArr['data']['payurl’']
		]
	];
	return $return_data;
}

//查询余额
function balance()
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'merId' => $config['mch_id'],
		'nonceStr' => getRsn()
	];
	$pdata['sign'] = paySign($pdata);
	$url = $config['balance_url'];
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	if ($resultArr['code'] != '1') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $resultArr['data']['balance'],
			'payout_balance' => $resultArr['data']['payout_balance'],
		]
	];
	return $return_data;
}


function paySign($params, $verify = false)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	if ($verify) {
		$signstr = create_sign($params, $config['md5_key']);
		$sign = strtoupper(md5(trim($signstr)));
		$outstr = rsa_verify($sign, $params['sign'], $config['publickey']);
	} else {
		$signstr = create_sign($params, $config['md5_key']);
		$sign = strtoupper(md5(trim($signstr)));
		$outstr = rsa_sign($sign, $config['privatekey']);
	}
	return $outstr;
}
// 创建签名字符串
function create_sign($params, $appSecret)
{
	$signOriginStr = '';
	ksort($params);
	foreach ($params as $key => $value) {
		if (empty ($key) || empty ($value) || $key == 'sign' || $key == 'signType' || $key == 'signature') {
			continue;
		}
		$signOriginStr = "$signOriginStr$key=$value&";
	}
	return $signOriginStr . "key=$appSecret";
}

// rsa签名
function rsa_sign($dataString, $privateKey)
{
	$pem = chunk_split($privateKey, 64, "\n");
	$pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
	$privKey = openssl_pkey_get_private($pem);
	$signature = false;
	openssl_sign($dataString, $signature, $privKey, OPENSSL_ALGO_SHA256);
	return base64_encode($signature);
}

// rsa验证签名
function rsa_verify($dataString, $signString, $publicKey)
{
	$pem = chunk_split($publicKey, 64, "\n");
	$pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
	$pubKey = openssl_pkey_get_public($pem);
	$signature = base64_decode($signString);
	$flg = openssl_verify($dataString, $signature, $pubKey, OPENSSL_ALGO_SHA256);
	return $flg;
}
