<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['bullpay'] = [
	'mch_id' => 'BP-37',
	'bullpay_key' => 'c525ca5fcda44f07bcb61073a149d40b',
	'dpay_url' => 'https://api.bullpay.in/bullp/trade/out',
	'dnotify_url' => 'http://' . PAY_BACKURL . '/' . 'api/Notify/bullpay/cash'
];

function bullpayCashOrder($fin_cashlog)
{

	$config = $_ENV['PAY_CONFIG']['bullpay'];
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "bullpay/cash/{$time}.txt";

	$pdata = [
		"accountName" => $fin_cashlog['receive_realname'],
		"accountNumber" => $fin_cashlog['receive_account'],
		"amount" => str_ireplace(",", '', number_format($fin_cashlog['real_money'], 2)),
		"appNo" => $config['mch_id'],
		"callbackUrl" => $config['dnotify_url'],
		"email" => $phone . "@gmail.com",
		"ifscCode" => $fin_cashlog['receive_ifsc'],
		"mobile" => $phone,
		"outTradeNo" =>  $fin_cashlog['osn'],
		"remark" =>  $fin_cashlog['osn'],
	];
	$pdata['sign'] = bullpayCashSign($pdata);
	//$pdata['sign_type'] = 'MD5'; 
	$url = $config['dpay_url'];
	//file_put_contents($logpathd, $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = bullpayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	//file_put_contents($logpathd, $url . "\r\n" . var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['status'] != '200') {
		return ['code' => -1, 'msg' => $resultArr['message']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['orderNo'],
			'out_osn' => $resultArr['data']['tradeNo']
		]
	];
	return $return_data;
}

function bullpayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['bullpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign') {
			continue;
		}
        if ($key == 'utr') {
			continue;
		}
		$valc = $val;
		if ($key == 'amount' || $key == 'realAmount') {
			$valc = str_ireplace(",", '', number_format($valc, 2));
		}
		$signStr .= $key . '=' . $valc . '&';
	}
	$signStr .= 'key=' . $config['bullpay_key'];

	$outstr = strtoupper(md5($signStr));
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "bullpay/CashSign{$time}.txt";
	//file_put_contents($logpath,  "signstr:\r\n" . $outstr . "\r\n" . $signStr . "\r\n" . json_encode($params), FILE_APPEND);
	return $outstr;
}


function bullpayCurlPost($url, $data = [], $timeout = 30)
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
		// CURLOPT_POSTFIELDS => http_build_query($data),
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
