<?php

use Curl\Curl;


function GetPayName()
{
	return "atpay";
}


function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	// $microtime = microtime(true); // 获取浮点数形式的当前时间戳
	// $timestamp = round($microtime * 1000); // 将时间戳转换为毫秒级
	$name = getRsn();
	//$rand_arr = [6, 7, 8, 9];
	//$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$pdata = [
		'buyerId' => $name,
		'channelCode' => '601',
		'outTradeNo' => $fin_paylog['osn'],
		'totalAmount' => $fin_paylog['money'],
		'notifyUrl' => $config['notify_url'],
	];
	$result = [];
	$headerarr = [
		'X-Qu-Access-Key' => $config['mch_key'],
		'X-Qu-Mid' => $config['mch_id'],
		'X-Qu-Nonce' => getRsn("", 1),
		'X-Qu-Signature-Method' => 'HmacSHA256',
		'X-Qu-Timestamp' => time(),
		'X-Qu-Signature-Version' => 'v1.0'
	];
	$headers = paySign($headerarr);

	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
		PHP_EOL . json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');

	try {
		$result = CurlPost($config['pay_url'], $pdata, 30, $headers);
	} catch (\Throwable $th) {
		return ['code' => -1, 'msg' => 'Channel is not open.-9001'];
	}
	writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	if ($resultArr['code'] != '00000') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => 'Channel is not open'];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_key'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $fin_paylog['osn'],
			'pay_url' => $resultArr['data']['payUrl']
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
	// $pdata = ['currency' => 'INR'];
	$headerarr = [
		'X-Qu-Access-Key' => $config['mch_key'],
		'X-Qu-Mid' => $config['mch_id'],
		'X-Qu-Nonce' => getRsn("", 1),
		'X-Qu-Signature-Method' => 'HmacSHA256',
		'X-Qu-Timestamp' => time(),
		'X-Qu-Signature-Version' => 'v1.0'
	];
	$headers = paySign($headerarr);

	writeLog(json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');

	$result = CurlGet($config['balance_url'] . '?currency=INR', 30, $headers);
	// $url = $config['balance_url'];
	//writeLog( json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	// $result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	if ($resultArr['code'] != '00000') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $resultArr['data']['totalBanlance'],
			'payout_balance' => $resultArr['data']['withdrawBanlance'],
		]
	];
	return $return_data;
}

function build_sorted_query_string($params)
{
	// 按照键名对数组进行ksort排序
	ksort($params);
	// 初始化拼接字符串
	$str = '';
	// 遍历数组并将键值对拼接到字符串
	foreach ($params as $key => $value) {
		$str .= $key . '=' . $value . '&';
	}
	$str = rtrim($str, '&');
	return $str;
}



function paySign($headerarr)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$headers = array();


	$sign_str = build_sorted_query_string($headerarr);

	$sign = hash_hmac('sha256', $sign_str, $config['secret_key'], false);
	// 转大写
	$sign = strtoupper($sign);
	$headers[] = 'Content-Type: ' . 'application/json;charset=UTF-8';
	$headers[] = 'X-Qu-Signature: ' . $sign;
	foreach ($headerarr as $key => $value) {
		$headers[] = $key . ': ' . $value;
	}
	return $headers;
}
