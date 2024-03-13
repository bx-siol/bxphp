<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['qlpay'] = [
	'mch_id' => '1068272',
	'qlpay_key' => '0blrDueZpgqZKioSybnaRGQWrBNOLOHJ',
	'dpay_url' => 'https://https://gw.kirinpayment.net/mch/withdrawin',
	'dnotify_url' => 'http://www.gamedreamer.in/' . 'api/Notify/qlpay/cash'
];

function qlpayCashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['qlpay'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "qlpay/cash/{$time}.txt";

	$pdata = [
		'appid' => $config['mch_id'],
		'out_trade_no' => $fin_cashlog['osn'],
		'money' =>  $fin_cashlog['real_money'],
		'name' => $fin_cashlog['receive_realname'],
		'account' => ($fin_cashlog['receive_account']),
		'ifsc_code' => $fin_cashlog['receive_ifsc'],
		'callback' => $config['dnotify_url'],
		'type' => 'IMPS',
		'remark' => $fin_cashlog['receive_realname'],
	];
	$pdata['sign'] = qlpayCashSign($pdata);
	//$pdata['sign_type'] = 'MD5'; 
	$url = $config['dpay_url'];
	file_put_contents($logpathd, $url . "\r\n" . json_encode($pdata) . "\r\n\r\n", FILE_APPEND);
	$result = qlpayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd, $url . "\r\n" . json_encode($resultArr) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['status'] != 'PAYOUT_SUCCESS') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['data']['out_trade_no']
		]
	];
	return $return_data;
}

function qlpayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['qlpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'images' || !$val) {
			continue;
		}
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= 'key=' .  $config['qlpay_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "qlpay/cash/CashSign{$time}.txt";

	$outstr = strtoupper(md5($signStr));
	file_put_contents($logpath,  "\r\nsignstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . json_encode($params), FILE_APPEND);
	return $outstr;
}
function qlpayCurlPost($url, $data = [], $timeout = 30)
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
