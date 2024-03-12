<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['payinpay'] = [
	'mch_id' => '220748492',
	'payinpay_key' => '38v2bfa4lj8sq3xvv6ot908jslsp25l8',
	'dpay_url' => 'https://Payin.pro/Payment_Dfpay_add.html',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/payinpay/cash'
];

function payinpayCashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['payinpay'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "payinpay/cash/{$time}.txt";
	$pdata = [
		'mchid' => $config['mch_id'],
		'out_trade_no' => $fin_cashlog['osn'],
		//'passageId' => '10703', //'11102',
		'money' =>  $fin_cashlog['real_money'],
		'bankname' => $fin_cashlog['receive_account'],
		'accountname' => $fin_cashlog['receive_realname'],
		'cardnumber' => ($fin_cashlog['receive_account']),
		'province' => $fin_cashlog['receive_ifsc'],
		'city' => 'city',
		'subbranch' => $config['dnotify_url']
	];
	$pdata['pay_md5sign'] = payinpayCashSign($pdata);
	//$pdata['sign_type'] = 'MD5'; 
	$url = $config['dpay_url'];
	file_put_contents($logpathd, $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = payinpayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd, $url . "\r\n" . var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['status'] != 'success') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['transaction_id']
		]
	];
	return $return_data;
}

function payinpayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['payinpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'pay_md5sign' || $key == 'attach' || !$val) {
			continue;
		}
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= 'key=' .  $config['payinpay_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "payinpay/paySign{$time}.txt";
	file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n", FILE_APPEND);
	$outstr = strtoupper(md5($signStr));
	file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . var_export($params, true), FILE_APPEND);
	return $outstr;
}
function payinpayCurlPost($url, $data = [], $timeout = 30)
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
			'Content-Type:application/x-www-form-urlencoded',
		)
		// CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
		// CURLOPT_HTTPHEADER => array(
		// 	'Content-Type: application/json;charset=UTF-8'
		// )
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
