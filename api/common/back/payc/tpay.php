<?php

use Curl\Curl;
use think\facade\Db;

/*
哥伦比亚	977000001	哥伦比亚网关一类=1100	哥伦比亚网关二类=1120	支付密钥：572ec680736f4a42a711c83a44d312d9
代付密钥：VMARQ7ULGHUMBJ5KF9WWTDFQHCGY3GEO
*/

$_ENV['PAY_CONFIG']['tpay'] = [
	'mch_id' => 'C1665837327387',
	'mch_key' => 'QHiMlGPki7pR87h0Ocd0eGliddjxCgqOAN0GwK',
	'pay_url' => 'https://www.tpays.in/openApi/pay/createOrder',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/tpay/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['tpay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d H:i:s", time());
	$fliepath =	LOGS_PATH . 'tpay/pay/' . date("Y-m-d", time()) . '.txt';

	/*
	merchant	是	String	商户号，平台分配账号
	orderId	是	String	商户订单号（唯一），字符长度40以内
	amount	是	String	金额，单位卢币(最多保留两位小数)
	
	customName	是	String	客户姓名 格式：英文、小数点、空格
	customMobile	是	String	客户电话 格式：10位数字
	customEmail	是	String	客户email地址

	channel	否	String	通道编码，编码值从商户后台“账户信息”页面中或者找客服获取，为兼容旧接口，该参数目前选填，当商户开通多个通道时候，该参数必填
	notifyUrl	是	String	异步通知回调地址
	callbackUrl	是	String	页面回跳地址（客户操作 支付成功或失败后跳转页面。）
	sign	是	String	签名
 
	*/
	$pdata = [
		'merchant' => $config['mch_id'],
		'orderId' => $fin_paylog['osn'],
		'amount' => strval($fin_paylog['money']),

		'customName' => $phone,
		'customMobile' => $phone,
		'customEmail' => $phone . '@Email.com',
//T001
		'callbackUrl' => strval($config['page_url']),
		'channel' => strval($sub_type),
		'notifyUrl' => $config['notify_url']
	];
	$pdata['sign'] = paySign($pdata);
	//$pdata['sign_type'] = 'MD5';
	$result = payCurlPost($config['pay_url'], $pdata, 30);
	//file_put_contents($fliepath, json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n" . json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n", FILE_APPEND);

	if ($result['code'] != 1) {
		file_put_contents($fliepath,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];

	if ($resultArr['code'] != '200') {
		return ['code' => -1, 'msg' =>'Channel is not open'];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['tradeMsg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['platOrderId'],
			'pay_url' => $resultArr['data']['url']
		]
	];
	return $return_data;
}


function paySign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['tpay'];
	ksort($pdata);
	$str = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign' || $pk == 'sign_type' || $pk == 'signType') {
			continue;
		}
		$str .= "{$pk}={$pv}&";
	}
	$str .= 'key=' . $config['mch_key'];
	//file_put_contents($fliepath, "\r\n" . '=============>' . $str . "\r\n\r\n" . md5($str) . "\r\n\r\n", FILE_APPEND);
	return (md5($str));
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
		// CURLOPT_HTTPHEADER => array(
		// 	'Content-Type: application/json'
		// )
	));

	$response = curl_exec($curl);

	//file_put_contents(LOGS_PATH . 'payOrderwow.txt', '=============>' . $response  . "\r\n\r\n", FILE_APPEND);
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
