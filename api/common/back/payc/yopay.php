<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['yopay'] = [
	'mch_id' => '94c85ec2591841939e4e68cfdc6dfcb5',
	'mch_key' => 'aavse2silxjgteodvpnpp5894morbmqn',
	'pay_url' => 'https://gvnk5jh49q.yopay.vip/api/create',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/yopay/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['yopay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "yopay/pay/{$time}.txt";
	$pdata = [
		'userid' => $config['mch_id'],
		'notifyurl' => $config['notify_url'], //异步从通知回调地址
		'orderid' => $fin_paylog['osn'], //商家自己平台的订单号
		'type' => $sub_type, //通道编码，商户后台有配置
		'amount' => $fin_paylog['money'], //价格 
		'returnurl' => $config['page_url'], //支付成功之后的跳转页面 
	];
	$pdata['sign'] = paySign([$pdata['orderid'], $pdata['amount']]);
	$result = payCurlPost($config['pay_url'], $pdata, 30);
	if ($result['code'] != 1) {
		file_put_contents($logpathd,  json_encode($result)  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];

	if ($resultArr['code'] != '1') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	$ooc = json_decode(stripslashes($resultArr['data']), true);
	file_put_contents($logpathd,   "\r\n =============== \r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES) . "\r\n" .  json_encode($resultArr, JSON_UNESCAPED_SLASHES) . "\r\n"
		. $resultArr['data'] . "\r\n", FILE_APPEND);


	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $ooc['ticket'],
			'pay_url' => $ooc['pageurl']
		]
	];
	return $return_data;
}


function paySign($params)
{
	$config = $_ENV['PAY_CONFIG']['yopay'];
	// ksort($params);
	$signStr = $config['mch_key'];
	foreach ($params as $key => $val) {
		$signStr .= $val;
	}
	//$time = date("Y-m-d", time());
	//$logpath = LOGS_PATH . "yopay/paySign{$time}.txt";
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
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
		CURLOPT_HTTPHEADER => array('Content-Type: application/json;charset=UTF-8')
	));
	$response = curl_exec($curl);
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "yopay/pay/Post{$time}.txt";
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
