<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['antpay'] = [
	'mch_id' => 'W099',
	'mch_key' => 'e284ac8b0a7749256a99a4a241ac26bb',
	'pay_url' => 'http://india.yunbao2019.cn/antpay.php?a=pay',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/antpay/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['antpay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	//$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "antpay/pay/{$time}.txt";
	
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);

	$pdata = [
		'name' => $name,
		'email' => $phone . '@email.com',
		'phone' => $phone,

		'appId' => $config['mch_id'],
		'notifyUrl' => $config['notify_url'], //异步从通知回调地址
		'orderNo' => $fin_paylog['osn'], //商家自己平台的订单号
		//'type' => $sub_type, //通道编码，商户后台有配置
		'monto' => $fin_paylog['money'], //价格  
	];
	$pdata['sign'] = paySign($pdata);

	$result = payCurlPost($config['pay_url'], $pdata, 30);
	file_put_contents($logpathd, $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES) . "\r\n\r\n ===============", FILE_APPEND);

	if ($result['code'] != 1) {
		//file_put_contents($logpathd,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	if ($resultArr['code'] != 200) {
		return ['code' => -1, 'msg' => $resultArr['info']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' =>  $resultArr['msg']['accountNumber'],
			'pay_url' => $resultArr['msg']['url']
		]
	];
	return $return_data;
}



function paySign($params)
{
	$config = $_ENV['PAY_CONFIG']['antpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'signType' || !$key) {
			continue;
		}

		if ($val != null) {
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$signStr .= 'key=' .  $config['mch_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "antpay/paySign{$time}.txt";
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
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json;charset=UTF-8'
		)
		// CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
		// CURLOPT_HTTPHEADER => array(
		// 	'Content-Type: application/json;charset=UTF-8'
		// )
	));

	$response = curl_exec($curl);
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "antpay/Post{$time}.txt";
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
