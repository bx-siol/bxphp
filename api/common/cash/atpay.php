<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "atpay";
}

function CashOrder($fin_cashlog)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
    $pdata = [
		'currency' => 'INR',
        'tradeAmount' =>strval(floor($fin_cashlog['real_money'])),
        'outTradeNo' =>  $fin_cashlog['osn'],
        'bankCardNo' =>$fin_cashlog['receive_account'],
        'bankName' => 'bank',
        'bankAccountName' => $fin_cashlog['receive_realname'],
        'bankBranchName' => $fin_cashlog['receive_bank_name'],
        'bankNum' => $fin_cashlog['receive_ifsc'],	//ifsc
        'bankType' => 0,
        'notify_url' => $config['dnotify_url'],
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
    $headers = CashSign($headerarr);

	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
		PHP_EOL . json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
    
    try {
		$result = CurlPost($config['dpay_url'], $pdata, 30, $headers);
	} catch (\Throwable $th) {
		return ['code' => -1, 'msg' => 'Channel is not open.-9001'];
	}
	writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	if ($result['code'] != 1) {
		return $result;
	}

    $resultArr = $result['output'];
	if ($resultArr['code'] != '00000') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
		return ['code' => -1, 'msg' => $resultArr['msg'] ];
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['message'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['data']['tradeNo']
		]
	];
	return $return_data;
}

function CashSign($headerarr)
{
    $headers = array();
	$sign = getsignstr($headerarr);
	$headers[] = 'Content-Type: ' . 'application/json;charset=UTF-8';
	$headers[] = 'X-Qu-Signature: ' . $sign;
	foreach ($headerarr as $key => $value) {
		$headers[] = $key . ': ' . $value;
	}
	return $headers;
}

function getsignstr($headerarr)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$sign_str = build_sorted_query_string($headerarr);
	$sign = hash_hmac('sha256', $sign_str, $config['secret_key'], false);
	// 转大写
	$sign = strtoupper($sign);
	return $sign;
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