<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['wepayplus'] = [
	'mch_id' => '3o254058',
	'mch_key' => '9f6cd10fecb943e19cf0e5b6db06f20c',
	'pay_url' => 'http://apis.wepayplus.com/client/collect/create',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/wepayplus/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['wepayplus'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "wepayplus/pay/{$time}.txt";

	$pdata = [
		'mchId' => $config['mch_id'],	//String	否	商户号   
		'notifyUrl' => $config['notify_url'], //异步从通知回调地址
		'orderNo' => $fin_paylog['osn'], //商家自己平台的订单号 
		'passageId' => $sub_type, //通道编码，商户后台有配置
		'amount' => $fin_paylog['money'], //价格 
		'callBackUrl' => $config['page_url'], //支付成功之后的跳转页面 
	];
	$pdata['sign'] = paySign($pdata);
	//file_put_contents($logpathd, $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);

	if ($result['code'] != 1) {
		//file_put_contents($logpathd,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd, $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n" . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	if ($resultArr['code'] != '200') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $fin_paylog['osn'],
			'pay_url' => $resultArr['data']['payUrl']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['wepayplus'];
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
	$config = $_ENV['PAY_CONFIG']['wepayplus'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sys_sign' || $key == 'signType' || $key == 'attach') {
			continue;
		}
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= 'key=' .  $config['mch_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "wepayplus/paySign{$time}.txt";
	$outstr = (md5($signStr));
	file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr, FILE_APPEND);
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
	$logpath = LOGS_PATH . "wepayplus/Post{$time}.txt";
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
