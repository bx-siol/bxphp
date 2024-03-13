<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['adkpay'] = [
	'mch_id' => '1007',
	'mch_key' => 'KmBltE7FB5acNSTF',
	'pay_url' => 'https://top.adkjk.in/rpay-api/order/submit',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/adkpay/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];
function getMillisecond()
{
	list($msec, $sec) = explode(' ', microtime());
	$msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	return substr($msectime, 0, 13);
}
function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['adkpay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "adkpay/pay/{$time}.txt";
	$pdata = [
		'merchantId' => $config['mch_id'],
		'notifyUrl' => $config['notify_url'], //异步从通知回调地址
		'merchantOrderId' => $fin_paylog['osn'], //商家自己平台的订单号
		'remark' => $sub_type,
		'amount' => $fin_paylog['money'], //价格 
		'callbackUrl' => $config['page_url'], //支付成功之后的跳转页面 
		'timestamp' => getMillisecond(),
		'payType' => $sub_type, //通道编码，商户后台有配置
	];

	$pdata['sign'] = paySign(['merchantId' => $pdata['merchantId'], 'merchantOrderId' => $pdata['merchantOrderId'], 'amount' => $pdata['amount']]);
	$result = payCurlPost($config['pay_url'], $pdata, 30);
	if ($result['code'] != 1) {
		file_put_contents($logpathd,  json_encode($result)  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd,  json_encode($resultArr)  . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['code'] != '0') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$ooc = $resultArr['data']; // json_decode(stripslashes($resultArr['data']), true);
	file_put_contents($logpathd,   "\r\n =============== \r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n" .  json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n"
		. $resultArr['data'] . "\r\n", FILE_APPEND);

	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $ooc['orderId'],
			'pay_url' => $ooc['h5Url']
		]
	];
	return $return_data;
}


function paySign($params)
{
	$config = $_ENV['PAY_CONFIG']['adkpay'];
	// ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= $config['mch_key'];
	//$time = date("Y-m-d", time());
	//$logpath = LOGS_PATH . "adkpay/paySign{$time}.txt";
	$outstr = strtolower(md5($signStr));
	//file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . var_export($params, true), FILE_APPEND);
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
	// $time = date("Y-m-d", time());
	// $logpath = LOGS_PATH . "adkpay/pay/Post{$time}.txt";
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
