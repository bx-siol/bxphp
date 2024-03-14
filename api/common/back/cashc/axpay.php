<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['axpay'] = [
	'mch_id' => '8mBorxB3ORUWX33Y69KOuAQyq2',
	'axpay_key' => 'dKmDxk6GbkS4l7Gr833ZTOpBwZaeL5cbbVdkVOm3',
	'dpay_url' => 'https://merchant.axpay.vip/api/WithdrawalV2/submit',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/axpay/cash'
];

function axpayCashOrder($fin_cashlog)
{

	$config = $_ENV['PAY_CONFIG']['axpay'];
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "axpay/cash/{$time}.txt";
	$pdata = [
		'merchantNo' => $config['mch_id'],
		'orderNo' => $fin_cashlog['osn'],
		'type' => '1', //'10703',
		'amount' =>  $fin_cashlog['real_money'],
		'name' => $fin_cashlog['receive_realname'],
		'account' => ($fin_cashlog['receive_account']),
		'ifscCode' => $fin_cashlog['receive_ifsc'],
		'notifyUrl' => $config['dnotify_url'],
		'ext' => 'ext',
		'version' => '2.0.2',
	];
	$pdata['sign'] = axpayCashSign($pdata);
	//$pdata['sign_type'] = 'MD5'; 
	$url = $config['dpay_url'];
	file_put_contents($logpathd, $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = axpayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd, $url . "\r\n" . var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['code'] != '0') {
		return ['code' => -1, 'msg' => $resultArr['message']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['orderNo'],
			'out_osn' => $resultArr['platformOrderNo']
		]
	];
	return $return_data;
}

function axpayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['axpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'signType' || !$val) {
			continue;
		}
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= 'key=' .  $config['axpay_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "axpay/CashSign{$time}.txt";
	$outstr = strtoupper(md5($signStr));
	file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . var_export($params, true), FILE_APPEND);
	return $outstr;
}
function axpayCurlPost($url, $data = [], $timeout = 30)
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
