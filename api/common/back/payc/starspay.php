<?php


use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['starspay'] = [
	'mch_id' => '070546',
	'mch_key' => 'b1f48cf4ec60b710563005986b9d11f9',
	'pay_url' => 'https://api.stars-pay.com/api/gateway/pay',
	'query_url' => '',
	'notify_url' => 'http://47.243.82.107/api/Notify/starspay/pay',
	'page_url' => REQUEST_SCHEME . '://' . HTTP_HOST
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['starspay'];
	//$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	//$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$filepath = LOGS_PATH . 'starspay/pay/payOrderwow' . $time . '.txt';
	/* 
	merchant_ref 是 string 商户订单号
	product 是 string 产品：  TRC20Buy 
	amount 	是 string 金额（法币：保留两位小数；数字货币：保留6 位小数）
	extra 是 Object 额外参数 
	*/

	$pdata = [
		'merchant_no' => $config['mch_id'],
		'timestamp' => time(),
		'sign_type' => 'MD5',
		'params' => json_encode([
			'merchant_ref' => $fin_paylog['osn'],
			'product' => $sub_type,
			'amount' => strval($fin_paylog['money']),
			'extra' => ['fiat_currency' => 'INR'],
		])
	]; // merchant_no + params + sign_type + timestamp + Key 
	$pdata['sign'] = paySign($pdata['merchant_no'] .  $pdata['params']  . $pdata['sign_type'] . $pdata['timestamp']);
	$result = payCurlPost($config['pay_url'], $pdata, 30);
	file_put_contents($filepath, "\r\n" . json_encode($pdata) . "\r\n" . json_encode($result) . "\r\n\r\n", FILE_APPEND);

	if (intval($result['code']) != 1) {
		//file_put_contents($filepath,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	$params = json_decode(stripslashes($resultArr['params']), true);
	//p($resultArr);exit; 
	if ($resultArr['code'] != '200') {
		return ['code' => -1, 'msg' => $resultArr['message']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['message'] ?? 'ok',
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $params['system_ref'],
			'pay_url' => $params['payurl']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['starspay'];
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

function paySign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['starspay'];
	$str  = $pdata . $config['mch_key'];
	$time = date("Y-m-d", time());
	$filepath = LOGS_PATH . 'starspay/pay/sign' . $time . '.txt';
	file_put_contents($filepath, "\r\n" . '=============>' . $str . "\r\n" . md5($str) . "\r\n\r\n", FILE_APPEND);
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
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/x-www-form-urlencoded'
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
