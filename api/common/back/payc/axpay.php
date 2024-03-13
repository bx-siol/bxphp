<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['axpay'] = [
	'mch_id' => '8mBorxB3ORUWX33Y69KOuAQyq2',
	'mch_key' => 'dKmDxk6GbkS4l7Gr833ZTOpBwZaeL5cbbVdkVOm3',
	'pay_url' => 'https://merchant.axpay.vip/api/PayV2/submit',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/axpay/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['axpay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "axpay/pay/{$time}.txt";
	$pdata = [
		'Timestamp' => time(),
		'AccessKey' => $config['mch_id'],
		'CallbackUrl' => $config['notify_url'], //异步从通知回调地址
		'OrderNo' => $fin_paylog['osn'], //商家自己平台的订单号
		'PayChannelId' => $sub_type == 0 ? 103 : $sub_type, //通道编码，商户后台有配置
		'Amount' => $fin_paylog['money'], //价格  
	];
	$pdata['sign'] = paySign($pdata);
	$result = payCurlPost($config['pay_url'], $pdata, 30);
	//file_put_contents($logpathd, '\r\n' . json_encode($pdata) . '\r\n' . json_encode($result)  . "\r\n\r\n", FILE_APPEND);
	if ($result['code'] != 1) {
		file_put_contents($logpathd,  json_encode($result)  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];

	if ($resultArr['Code'] != '0') {
		return ['code' => -1, 'msg' => 'Channel is not open'];
	}

	// file_put_contents($logpathd,   "\r\n =============== \r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n" .  json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n"
	// 	. $resultArr['data'] . "\r\n", FILE_APPEND);

	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['Data']['OrderNo'],
			'pay_url' => $resultArr['Data']['PayeeInfo']['CashUrl']
		]
	];
	return $return_data;
}


function paySign($params)
{
	$config = $_ENV['PAY_CONFIG']['axpay'];
	ksort($params);

	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'Sign' || is_null($val)) {
			continue;
		}
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= 'SecretKey=' . $config['mch_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "axpay/paySign{$time}.txt";
	$outstr = strtolower(md5($signStr));
//	file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . json_encode($params, true), FILE_APPEND);
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
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
		CURLOPT_HTTPHEADER => array('Content-Type: application/json;charset=UTF-8')
	));
	$response = curl_exec($curl);
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "axpay/pay/Post{$time}.txt";
	//file_put_contents($logpath, '=============>' . $response  . "\r\n\r\n", FILE_APPEND);
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
