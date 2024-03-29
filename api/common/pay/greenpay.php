<?php

use Curl\Curl;


function GetPayName()
{
	return "greenpay";
}


function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	// $microtime = microtime(true); // 获取浮点数形式的当前时间戳
	// $timestamp = round($microtime * 1000); // 将时间戳转换为毫秒级
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$pdata = [
		'customerName' => $name,
		'customerEmail' => $phone . '@gmail.com',
		'customerPhone' => $phone,
		'merchantOrderNo' => $fin_paylog['osn'],
		'amount' => $fin_paylog['money'],
		'notifyUrl' => $config['notify_url'],
	];



	$str = json_encode(($pdata));

	$data = paySign($str, $config['aeskey'], $config['aeslv']);
	$info['data'] = $data;
	//$data = json_encode($info);

	//$pdata['sign'] = paySign($pdata);
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL . $info, GetPayName() . '/pay');

	$result = [];
	try {
		$headers = array();
		$headers[] = 'Content-Type: ' . 'application/json;charset=UTF-8';
		$headers[] = 'merchant_key: ' . $config['mch_key'];


		$result = CurlPost($config['pay_url'], $info, 30, $headers);
	} catch (\Throwable $th) {
		return ['code' => -1, 'msg' => 'Channel is not open.-9001'];
	}
	writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	if ($resultArr['code'] != '0') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => 'Channel is not open'];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_key'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['orderNo'],
			'pay_url' => $resultArr['data']['paymentLinkUrl']
		]
	];
	return $return_data;
}

function dsign($pdata)
{
	//MD5 加密,,merchantKey+message+amount+status+merchantOrderNo+orderNo+aeskey
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$outstr = strtolower(md5($config['mch_key'] . $pdata['data']['message']
		. $pdata['data']['amount'] . $pdata['data']['status'] . $pdata['data']['merchantOrderNo'] . $pdata['data']['orderNo'] . $config['aeskey']));
	return $outstr;
}

//查询余额
function balance()
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];

	$headers = array();
	$headers[] = 'Content-Type: ' . 'application/json;charset=UTF-8';
	$headers[] = 'merchant_key: ' . $config['mch_key'];


	$result = CurlPost($config['pay_url'], [], 30, $headers);
	$url = $config['balance_url'];
	//writeLog( json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	// $result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	if ($resultArr['code'] != 100) {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $resultArr['balance'],
			'payout_balance' => $resultArr['balance'],
		]
	];
	return $return_data;
}



function paySign($str, $key, $iv)
{
	//$zeroPack = pack('i*', $aes_iv);
	// $iv = str_repeat($zeroPack, 4);
	return base64_encode(openssl_encrypt($str, 'AES-128-CBC', (($key)), OPENSSL_RAW_DATA, $iv));
}
