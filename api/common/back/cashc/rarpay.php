<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['rarpay'] = [
	'mch_id' => 'Siemenss',
	'rarpay_key' => 'CEEC24E41EEBEB1953BF608F99491EDA',
	'dpay_url' => 'https://merchant.rarpay.com/dpayapi',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/rarpay/cash'
];

function rarpayCashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['rarpay'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "rarpay/cash/{$time}.txt";
	/*
	 
	*/
    date_default_timezone_set('PRC');
	$pdata = [
		'type' => 'bank_dpay',
		'mchNo' => $config['mch_id'],
		'orderNo' => $fin_cashlog['osn'],
		'fee' =>  $fin_cashlog['real_money'],
		'time' => date('Y-m-d H:i:s'),
		'customerEmail' => $phone . "@Email.com",
		//'bankName' => 'IDPT0001',
		'customerPhone' => $phone,
		'customerName' => $fin_cashlog['receive_realname'],
		'bankAccount' => ($fin_cashlog['receive_account']),
		'ifsc' => $fin_cashlog['receive_ifsc'],
		//'city' => 'city',
		'notifyUrl' => $config['dnotify_url']
	];
	date_default_timezone_set("Asia/Kolkata");
	$pdata['sign'] = rarpayCashSign($pdata);
	$url = $config['dpay_url'];
	//file_put_contents($logpathd, $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = rarpayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	//file_put_contents($logpathd, $url . "\r\n" . var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['code'] != '1') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $fin_cashlog['osn']
		]
	];
	return $return_data;
}

function rarpayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['rarpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'pay_md5sign' || $key == 'attach' || !$val) {
			continue;
		}
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= 'key=' .  $config['rarpay_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "rarpay/paySign{$time}.txt";
	//file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n", FILE_APPEND);
	$outstr = (md5($signStr));
	//file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . var_export($params, true), FILE_APPEND);
	return $outstr;
}
function rarpayCurlPost($url, $data = [], $timeout = 30)
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
