<?php

use Curl\Curl;
use think\facade\Db;



function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['jwpay'];
	$microtime = microtime(true); // 获取浮点数形式的当前时间戳
	$milliseconds = round($microtime * 1000); // 将时间戳转换为毫秒级
	$pdata = [
		'merchantId' => $config['mch_id'],
		'orderId' => $fin_paylog['osn'],
		'amount' => strval($fin_paylog['money'] * 100),
		'timestamp' => $milliseconds,
		'notifyUrl' => $config['notify_url'],
	];
	$pdata['sign'] = paySign($pdata);
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay/pay');
	$result = [];
	try {
		$result = CurlPost($config['pay_url'], $pdata, 30);
	} catch (\Throwable $th) {
		return ['code' => -1, 'msg' => ''];
	}
	writeLog('result : ' . json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay/pay');
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	if ($resultArr['code'] != '100') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay/pay/error');
		return ['code' => -1, 'msg' => 'Channel is not open'];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['payOrderId'],
			'pay_url' => $resultArr['paymentUrl']
		]
	];
	return $return_data;
}

function paySign($params)
{
	// md5(amount+merchantId+orderId+timestamp+密钥)进行MD5，32位小写加密。密钥开户后提供. 	
	$config = $_ENV['PAY_CONFIG']['jwpay'];
	$signStr = $params['amount'] . $params['merchantId'] . $params['orderId'] . $params['timestamp'] . $config['mch_key'];
	$outstr = strtolower(md5($signStr));
	return $outstr;
}
function CurlPost($url, $data = [], $timeout = 30)
{
	$curl = curl_init();
	curl_setopt_array(
		$curl,
		array(
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
			CURLOPT_HTTPHEADER => array(
				'Content-Type:application/json'
			)
		)
	);
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