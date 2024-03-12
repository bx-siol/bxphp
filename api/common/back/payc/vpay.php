<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['vpay'] = [
	'mch_id' => '8000450',
	// 'appId' => '30842dd53efc40e493922b33471467cc',
	'mch_key' => 'yimebhnzr1fbgk06svcfae3s9dglowxy',
	'pay_url' => 'https://api.victory-pay.com/payweb/recharge',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/vpay/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['vpay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "vpay/pay{$time}.txt";
	$pdata = [
		'merchant_id' => $config['mch_id'],
		'notify_url' => $config['notify_url'], //异步从通知回调地址
		'mer_order_num' => $fin_paylog['osn'], //商家自己平台的订单号
		'pay_code' =>  $sub_type, //通道编码，商户后台有配置
		'price' => $fin_paylog['money'], //价格
		'attach' => '', //附带字段
		'page_url' => $config['page_url'], //支付成功之后的跳转页面
		'order_date' => date('Y-m-d H:i:s'), //订单时间
		'timestamp' => time(), //时间戳
	];
	$pdata['sign'] = paySign($pdata);
	$pdata['sign_type'] = 'MD5';
	file_put_contents($logpath, $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES) . "\r\n\r\n ===============", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);

	if ($result['code'] != 1) {
		file_put_contents($logpath,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	//p($resultArr);exit;

	if ($resultArr['code'] != 'SUCCESS') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['ptOrderNo'],
			'pay_url' => $resultArr['payLink']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['vpay'];
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
	$config = $_ENV['PAY_CONFIG']['vpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'signType' || !$val) {
			continue;
		}
		if ($val != null) {
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$signStr .= 'key=' .  $config['mch_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "vpay/paySign{$time}.txt";
	file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n", FILE_APPEND);
	return strtoupper(md5($signStr));
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
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'User-Agent: ozilla/5.0 (X11; Linux i686) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1',
			'Accept-Charset: UTF-8,utf-8;q=0.7,*;q=0.3',
			// 'Content-Type:application/x-www-form-urlencoded',
		)
	));

	$response = curl_exec($curl);
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "vpay/Post{$time}.txt";
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
