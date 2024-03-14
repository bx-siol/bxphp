<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['mx22613'] = [
	'mch_id' => '20210601001',
	//'appId' => 'dd1fffce91964a17a26991d50c78753f',
	'mch_key' => 'ab94c5389cf0ab453b2ef3d8372d1ca9',
	'pay_url' => 'https://colopen.citipayweb.com/openapi/open/topup',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL. '/api/Notify/mx22613/pay',
	'page_url' => REQUEST_SCHEME . '://' . HTTP_HOST
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['mx22613'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("YmdHis", time());
	/*
	mch_no 是 string 分配给各合作商户的唯一识别码，商户后台获取 
	out_trade_no 是 string 用户支付后商户网站产生的一个唯一的定单号，该订 单号不重复 
	amount 是 decimal 字符串传递，用户支付订单的金额，两位小数。不可 以为零，必需符合金额标准。 
	notify_url 是 string 异步通知地址 
	return_url 否 string 支付成功回跳到商户的地址 
	ext 是 string 传透参数，异步通知原样返回 
	version 是 string 目前填写1.0.0 
	time 是 string 格式：yyyyMMddHHmmss 
	
	sign 是 string MD5签名
	*/
	$pdata = [
		'mch_no' => $config['mch_id'],
		'out_trade_no' => $fin_paylog['osn'],
		'amount' => ($fin_paylog['money']),
		'time' => $time,
		'ext' => $phone,
		'version' => '1.0.0',
		'return_url' => $config['page_url'],
		'notify_url' => $config['notify_url']
	];
	$pdata['sign'] = paySign($pdata);
	file_put_contents(LOGS_PATH . 'payOrderlc.txt', $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);

	if ($result['code'] != 1) {
		file_put_contents(LOGS_PATH . 'payOrderlc.txt',  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	//p($resultArr);exit;

	if ($resultArr['code'] != '200') {
		return ['code' => -1, 'msg' => $resultArr['message']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['message'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['platform_order_no'],
			'pay_url' => $resultArr['data']['url']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['mx22613'];
	$pdata = [
		'pay_orderid' => $order['osn'],
		'pay_memberid' => $config['mch_id']
	];
	$pdata['pay_md5sign'] = paySign($pdata);
	$result = payCurlPost($config['query_url'], $pdata, 30);
	$resultArr = $result['output'];
	if ($resultArr['returncode'] != '00') {
		return ['code' => -1, 'msg' => $resultArr['trade_state']];
	}
	return ['code' => 1, 'msg' => $resultArr['trade_state'], 'data' => $resultArr];
}

function paySign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['mx22613'];
	$str  = $pdata['mch_no'] . $pdata['out_trade_no'] . $pdata['amount'] . $config['mch_key'];
	return (md5($str));
}

function payCurlPost($url, $data = [], $timeout = 30)
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
		// CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => http_build_query($data),
		// CURLOPT_HTTPHEADER => array(
		// 	'Content-Type: application/json'
		// )
	));

	$response = curl_exec($curl);

	file_put_contents(LOGS_PATH . 'payOrderlc.txt', '=============>' . $response  . "\r\n\r\n", FILE_APPEND);
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
