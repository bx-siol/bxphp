<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['qlpay'] = [
	'mch_id' => 'Cili@bc',
	'appid' => 'stage', //'1068272',
	'mch_key' => 'STAGE_API_KEY', //.'0blrDueZpgqZKioSybnaRGQWrBNOLOHJ',
	'pay_url' => 'https://gw.kirinpayment.net/pay/unifiedorder?format=json',
	'query_url' => '',
	'notify_url' => 'http://www.gamedreamer.in' . '/api/Notify/qlpay/pay',
	'page_url' => 'http://www.gamedreamer.in'
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['qlpay'];
	//$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "qlpay/pay/{$time}.txt";


	$pdata = [
		'appid' => $config['appid'],
		'version' => 'v2.0',
		'trade_type' => 'account',
		'out_uid' => $phone,
		'return_type' => 'mobile',
		'error_url' => $config['page_url'],
		'callback_url' => $config['notify_url'], //异步从通知回调地址
		'out_trade_no' => $fin_paylog['osn'], //商家自己平台的订单号
		'pay_type' => 'upi', //通道编码，商户后台有配置
		'amount' =>   number_format($fin_paylog['money'], 2), //价格  
		'success_url' => $config['page_url'], //支付成功之后的跳转页面 
	];
	$pdata['sign'] = paySign($pdata);
	$result = payCurlPost($config['pay_url'], $pdata, 30);
	file_put_contents($logpathd,  "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n" . json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	if (!$resultArr['url']) {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['order_no'],
			'pay_url' => $resultArr['url']
		]
	];
	return $return_data;
}

function paySign($params)
{
	$config = $_ENV['PAY_CONFIG']['qlpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'signType' || $key == 'attach') {
			continue;
		}

		if ($val != null) {
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$signStr .= 'key=' .  $config['mch_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "qlpay/paySign{$time}.txt";
	$outstr = strtoupper(md5($signStr));
	//file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr, FILE_APPEND);
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
		CURLOPT_POSTFIELDS => http_build_query($data),
		CURLOPT_HTTPHEADER => array(
			'Content-Type:application/x-www-form-urlencoded',
		)
		// CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
		// CURLOPT_HTTPHEADER => array(
		// 	'Content-Type: application/json;charset=UTF-8'
		// )
	));

	$response = curl_exec($curl);
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "qlpay/payPost{$time}.txt";
	file_put_contents($logpath, '=============>' . $response  . "\r\n\r\n", FILE_APPEND);
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
