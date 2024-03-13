<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['ppay'] = [
	'mch_id' => '888356024',
	// 'appId' => '30842dd53efc40e493922b33471467cc',
	'mch_key' => 'bb7d7a4f015841948929405a762543da',
	'pay_url' => 'https://ord.ppayglobal.com/pay/order',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL. '/api/Notify/ppay/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['ppay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	//$time = date("Y-m-d H:i:s", time());

	$pdata = [
		'merNo' => $config['mch_id'],
		'notifyUrl' => $config['notify_url'],
		'merchantOrderNo' => $fin_paylog['osn'],
		'payCode' => $sub_type,
		'currency' => 'INR',
		'amount' => $fin_paylog['money'],
		'payerName' => $name,
		'payerEmail' => $phone . 'ns@gmail.com',
		'payerPhone' => $phone,
		'payerAccno' => '',
		'attch' => '',
		'goodsName' => 'recharge',
	];
	$pdata['sign'] = paySign($pdata);
	//$pdata['sign_type'] = 'MD5';
	file_put_contents(LOGS_PATH . 'p/payOrderpppay.txt', $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);

	if ($result['code'] != 1) {
		file_put_contents(LOGS_PATH . 'p/payOrderpppay.txt',  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	//p($resultArr);exit;

	if ($resultArr['code'] != 'SUCCESS') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['ptOrderNo'],
			'pay_url' => $resultArr['payLink']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['ppay'];
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

function paySign($params)
{
	$config = $_ENV['PAY_CONFIG']['ppay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {


		if ($key == 'sign' || $key == 'sign_type' || $key == 'signType' || !$val) {
			continue;
		}

		if ($val != null) {
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$signStr .= 'key=' .  $config['mch_key'];
	file_put_contents(LOGS_PATH . 'p/payOrderpppay.txt',  "signstr:\r\n" . $signStr . "\r\n", FILE_APPEND);
	return strtolower(md5($signStr));
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
		CURLOPT_HTTPHEADER => array(
			//'Content-Type: application/json'
			'User-Agent: ozilla/5.0 (X11; Linux i686) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1',
			'Accept-Charset: UTF-8,utf-8;q=0.7,*;q=0.3',
			'Content-Type:application/x-www-form-urlencoded',
		)
	));

	$response = curl_exec($curl);

	file_put_contents(LOGS_PATH . 'p/payOrderpppay.txt', '=============>' . $response  . "\r\n\r\n", FILE_APPEND);
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
