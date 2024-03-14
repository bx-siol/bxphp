<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['bullpay'] = [
	'mch_id' => 'BP-37',
	'mch_key' => 'c525ca5fcda44f07bcb61073a149d40b',
	'game_url' =>   'https://api.bullpay.in/bullp/trade/in',
	'notify_url' => 'http://' . PAY_BACKURL . '/api/Notify/bullpay/pay',
	'page_url' => REQUEST_SCHEME . '://' . HTTP_HOST
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['bullpay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d H:i:s", time());
	$fliepath =	LOGS_PATH . 'bullpay/pay/' . date("Y-m-d", time()) . '.txt';

	$pdata =   [
		"appNo" => $config['mch_id'],
		"callbackUrl" => $config['notify_url'],
		"outTradeNo" => $fin_paylog['osn'],
		"payAmount" => $fin_paylog['money'],
		"payerEmail" => $phone . "@gamil.com",
		"payerMobile" => $phone,
		"payerName" => $name,
		"remark" => $fin_paylog['osn'],
		"resultLink" => $config['page_url'],
		"type" => "UPI"
	];
	$pdata['sign'] = paySign($pdata);
	$result = payCurlPost($config['game_url'], $pdata, 30);
	file_put_contents($fliepath, json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n" . json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n", FILE_APPEND);

	if ($result['code'] != 1) {
		//file_put_contents($fliepath,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];

	if ($resultArr['status'] != '200') {
		return ['code' => -1, 'msg' => 'Channel is not open'];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['tradeNo'],
			'pay_url' => $resultArr['data']['tradeUrl']
		]
	];
	return $return_data;
}




function paySign($params)
{
	$config = $_ENV['PAY_CONFIG']['bullpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign'   || !$val) {
			continue;
		}
		$valc = $val;
		if ($key == 'amount' || $key == 'realAmount') {
			$valc = str_ireplace(",", '', number_format($valc, 2));
		}
		$signStr .= $key . '=' . $valc . '&';
	}
	$signStr .= 'key=' . $config['mch_key'];

	$outstr = strtoupper(md5($signStr));
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "bullpay/paySign{$time}.txt";
	//file_put_contents($logpath,  "signstr:\r\n" . $outstr . "\r\n" . $signStr . "\r\n" . json_encode($params), FILE_APPEND);
	return $outstr;
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
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
		CURLOPT_HTTPHEADER => array('Content-Type: application/json; charset=utf-8')
	));

	$response = curl_exec($curl);

	//file_put_contents(LOGS_PATH . 'payOrderwow.txt', '=============>' . $response  . "\r\n\r\n", FILE_APPEND);
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
