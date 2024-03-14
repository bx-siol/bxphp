<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['adkpay'] = [
	'mch_id' => '1007',
	'adkpay_key' => 'KmBltE7FB5acNSTF',
	'dpay_url' => 'https://top.adkjk.in/rpay-api/payout/submit',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/adkpay/cash'
];
function getMillisecond()
{
	list($msec, $sec) = explode(' ', microtime());
	$msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	return substr($msectime, 0, 13);
}
function adkpayCashOrder($fin_cashlog)
{
	$config = $_ENV['PAY_CONFIG']['adkpay'];
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "adkpay/cash/{$time}.txt";

	$pdata = [
		"merchantId" => $config['mch_id'],
		"merchantOrderId" => $fin_cashlog['osn'],
		"amount" => $fin_cashlog['real_money'],
		"timestamp" => getMillisecond(),
		"notifyUrl" => $config['dnotify_url'],
		"fundAccount" => [
			"accountType" => "bank_account",
			"contact" => [
				"name" => $fin_cashlog['receive_realname'],
				"email" => $phone . "@yahoo.com",
				"mobile" => $phone,
			],
			"bankAccount" => [
				"name" => $fin_cashlog['receive_realname'],
				"ifsc" => $fin_cashlog['receive_ifsc'],
				"accountNumber" => $fin_cashlog['receive_account']
			],
			// "vpa" => [
			// 	"address" => "Apt. 004 9080 Wisozk Overpass， North Chris， IA 29280-1112"
			// ]
		],
	];
	$pdata['sign'] = adkpayCashSign(['merchantId' => $pdata['merchantId'], 'merchantOrderId' => $pdata['merchantOrderId'], 'amount' => $pdata['amount']]);
	$url = $config['dpay_url'];
	$result = adkpayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd, $url . "\r\n ================= \r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n" . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n", FILE_APPEND);
	$ooc = $resultArr['data']; //json_decode(stripslashes(), true);
	if ($resultArr['code'] != 0 || $ooc['status'] == '2') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $ooc['payoutId']
		]
	];
	return $return_data;
}

function adkpayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['adkpay'];
	$signStr = '';
	foreach ($params as $key => $val) {
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= $config['adkpay_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "adkpay/paySign{$time}.txt";
	$outstr = strtolower(md5($signStr));
	file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . var_export($params, true), FILE_APPEND);
	return $outstr;
}
function adkpayCurlPost($url, $data = [], $timeout = 30)
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
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
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
