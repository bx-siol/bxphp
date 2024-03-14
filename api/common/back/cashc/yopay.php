<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['yopay'] = [
	'mch_id' => '94c85ec2591841939e4e68cfdc6dfcb5',
	'yopay_key' => 'aavse2silxjgteodvpnpp5894morbmqn',
	'dpay_url' => 'https://gvnk5jh49q.yopay.vip/api/wd',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/yopay/cash'
];

function yopayCashOrder($fin_cashlog)
{
	$config = $_ENV['PAY_CONFIG']['yopay'];
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "yopay/cash/{$time}.txt";

	$pdata = [
		'userid' => $config['mch_id'],
		'orderid' => $fin_cashlog['osn'],
		// 'passageId' => '11002', //'10703',
		'amount' =>  $fin_cashlog['real_money'],
		// 'userName' => $fin_cashlog['receive_realname'],
		// 'account' => ($fin_cashlog['receive_account']),
		// 'ifsc' => $fin_cashlog['receive_ifsc'],
		'notifyurl' => $config['dnotify_url'],
		'payload' => json_encode([
			'accountType' => "BANK",
			'accountInfo' => ["name" => $fin_cashlog['receive_realname'], "accountNumber" => $fin_cashlog['receive_account'], "ifsc" =>  $fin_cashlog['receive_ifsc']],
		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
	];
	$pdata['sign'] = yopayCashSign([$pdata['orderid'], $pdata['amount']]);
	$url = $config['dpay_url'];

	$result = yopayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd, $url . "\r\n ================= \r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n" . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['code'] != '1') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$ooc = json_decode(stripslashes($resultArr['data']), true);
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $ooc['ticket']
		]
	];
	return $return_data;
}

function yopayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['yopay'];
	// ksort($params);
	$signStr = $config['yopay_key'];
	foreach ($params as $key => $val) {
		$signStr .= $val;
	}
	//$time = date("Y-m-d", time());
	//$logpath = LOGS_PATH . "yopay/paySign{$time}.txt";
	$outstr = strtolower(md5($signStr));
	//file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . var_export($params, true), FILE_APPEND);
	return $outstr;
}
function yopayCurlPost($url, $data = [], $timeout = 30)
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
