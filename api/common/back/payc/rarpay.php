<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['rarpay'] = [
	'mch_id' => 'Siemenss',
	'mch_key' => 'CEEC24E41EEBEB1953BF608F99491EDA',
	'pay_url' => 'https://merchant.rarpay.com/payapi',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/rarpay/pay',
	'page_url' => REQUEST_SCHEME . '://' . HTTP_HOST
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['rarpay'];
	//$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "rarpay/pay/{$time}.txt";
	/*
	mchNo	是	string	商户号
	time	是	string	请求时间（北京时间），格式为年月日时分秒(如:2022-07-08 13:13:34)
	sign	是	string	通用参数，见通用说明
	type	是	string	支付通道的类型
	fee	是	string	支付金额
	orderNo	是	string	订单号,不允许重复
	notifyUrl	否	string	支付结果通知地址，如不填，则不通知
	backUrl	否	string	支付完成后的回跳地址，如不填，则不跳转(部分通道不支持)
	*/
	date_default_timezone_set('PRC');
	$pdata = [
		'mchNo' => $config['mch_id'],
		'notifyUrl' => $config['notify_url'], //异步从通知回调地址
		'orderNo' => $fin_paylog['osn'], //商家自己平台的订单号
		'type' => $sub_type, //通道编码，商户后台有配置
		'fee' => $fin_paylog['money'], //价格 
		'time' => date('Y-m-d H:i:s'),
		'backUrl' => $config['page_url'], //支付成功之后的跳转页面 
	];
	date_default_timezone_set("Asia/Kolkata");
	$pdata['sign'] = paySign($pdata);
	//$pdata['sign_type'] = 'MD5';
	//file_put_contents($logpathd, $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);
	file_put_contents($logpathd, json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	if ($result['code'] != 1) {
		//file_put_contents($logpathd,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	if ($resultArr['code'] != '1') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['ordernum'],
			'pay_url' => $resultArr['data']['pay_url']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['rarpay'];
	$pdata = [
		'pay_orderid' => $order['osn'],
		'pay_memberid' => $config['mch_id']
	];
	$pdata['pay_md5sign'] = paySign($pdata);
	$result = payCurlPost($config['query_url'], $pdata, 30);
	$resultArr = $result['output'];
	if ($resultArr['returncode'] != '00') {
		return ['code' => -1, 'msg' => $resultArr['trade_state']];
	}
	return ['code' => 1, 'msg' => $resultArr['trade_state'], 'data' => $resultArr];
}

function paySign($params)
{
	$config = $_ENV['PAY_CONFIG']['rarpay'];
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
	$logpath = LOGS_PATH . "rarpay/pay/paySign{$time}.txt";
	$outstr =  md5($signStr);
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
	$logpath = LOGS_PATH . "rarpay/Post{$time}.txt";
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
