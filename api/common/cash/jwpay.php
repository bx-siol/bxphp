<?php

use Curl\Curl;
use think\facade\Db;



function CashOrder($fin_cashlog)
{
	$config = $_ENV['PAY_CONFIG']['jwpay'];
	$microtime = microtime(true); // 获取浮点数形式的当前时间戳
	$milliseconds = round($microtime * 1000); // 将时间戳转换为毫秒级
	$pdata = [
		'outType' => 'IMPS',
		'merchantId' => $config['mch_id'],
		'orderId' => $fin_cashlog['osn'],
		'timestamp' => $milliseconds,
		'amount' => floor($fin_cashlog['real_money'] * 100),
		'accountHolder' => $fin_cashlog['receive_realname'],
		'accountNumber' => ($fin_cashlog['receive_account']),
		'ifsc' => $fin_cashlog['receive_ifsc'],
		'notifyUrl' => $config['dnotify_url']
	];
	$pdata['sign'] = CashSign($pdata);
	$url = $config['dpay_url'];
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay/cash');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay/cash');
	if ($resultArr['code'] != '100') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay/cash/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['payOrderId']
		]
	];
	return $return_data;
}

function CashSign($params)
{
	// sign	是	string	签名，md5(amount+merchantId+orderId+timestamp+secret)进行MD5加密，32位小写。
	$config = $_ENV['PAY_CONFIG']['jwpay'];
	$signStr = $params['amount'] . $params['merchantId'] . $params['orderId'] . $params['timestamp'] . $config['mch_key'];
	//writeLog(json_encode('signStr : ' . $signStr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay');
	$outstr = strtolower(md5($signStr));
	//writeLog(json_encode('outstr : ' . $outstr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay');
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