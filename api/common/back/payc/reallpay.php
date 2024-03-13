<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['reallpay'] = [
	'mch_id' => '707003566',
	'mch_key' => '5e34a1db59a942b382e1abb532076a90',
	'pay_url' => 'http://pay.reallypay.xyz/pay/order/create',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/reallpay/pay',
	'page_url' => REQUEST_SCHEME . '://' . HTTP_HOST
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['reallpay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d H:i:s", time());

	$pdata = [
		'mer_no' => $config['mch_id'],
		'order_no' => $fin_paylog['osn'],
		'order_amount' => strval($fin_paylog['money']),
		'currency' => 'INR',
		'order_date' => strval($time),
		'callbackUrl' => strval($config['page_url']),
		'pay_code' => strval($sub_type),
		'notifyUrl' => $config['notify_url']
	];
	$pdata['sign'] = paySign($pdata);
	file_put_contents(LOGS_PATH . '/reallpay/pay/Orderwow.txt',    json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);

	if ($result['code'] != '1') {
		file_put_contents(LOGS_PATH . '/reallpay/pay/payOrderwow.txt',  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents(LOGS_PATH . '/reallpay/pay/Orderwow.txt',  json_encode($resultArr)  . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['code'] != 'SUCCESS') {
		return ['code' => -1, 'msg' => $resultArr['tradeMsg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['tradeMsg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['orderId'],
			'pay_url' => $resultArr['pay_url']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['reallpay'];
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
	$config = $_ENV['PAY_CONFIG']['reallpay'];
	ksort($pdata);
	$str = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign' || $pk == 'sign_type' || $pk == 'signType' || !$pv) {
			continue;
		}
		$str .= "{$pk}={$pv}&";
	}
	$str .= 'key=' . $config['mch_key'];
	file_put_contents(LOGS_PATH . 'payOrderwowsing.txt', "\r\n" . '=============>' . $str . "\r\n\r\n" . md5($str) . "\r\n\r\n", FILE_APPEND);
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

	file_put_contents(LOGS_PATH . 'payOrderwow.txt', '=============>' . $response  . "\r\n\r\n", FILE_APPEND);
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
