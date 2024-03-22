<?php

use Curl\Curl;


function GetPayName()
{
	return "jwpay";
}


function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$microtime = microtime(true); // 获取浮点数形式的当前时间戳
	$timestamp = round($microtime * 1000); // 将时间戳转换为毫秒级
	$pdata = [
		'merchantId' => $config['mch_id'],
		'orderId' => $fin_paylog['osn'],
		'amount' => strval($fin_paylog['money'] * 100),
		'timestamp' => $timestamp,
		'notifyUrl' => $config['notify_url'],
	];
	$pdata['sign'] = paySign($pdata);
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	$result = [];
	try {
		$result = CurlPost($config['pay_url'], $pdata, 30);
	} catch (\Throwable $th) {
		return ['code' => -1, 'msg' => ''];
	}
	//writeLog('result : ' . json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName().'/pay');
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	if ($resultArr['code'] != '100') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
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

//查询订单状态
function status($orderId)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName() . ''];
	$microtime = microtime(true); // 获取浮点数形式的当前时间戳
	$timestamp = round($microtime * 1000); // 将时间戳转换为毫秒级
	$pdata = [
		'merchantId' => $config['mch_id'],
		'timestamp' => $timestamp,
		'orderId' => $orderId,
	];
	$pdata['sign'] = paySign($pdata, 1);
	$url = $config['payquery_url'];
	//writeLog('resultArr : ' . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog('resultArr : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/status');
	if ($resultArr['code'] != 100) {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/status/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$stat = 0;
	switch ($resultArr['stat']) {
		case '1':
			$stat = 1;
			break;
		case '2':
			$stat = 2;
			break;
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'orderId' => $resultArr['orderId'],//匹配的订单号，只有stat结果为2时返回
			'stat' => $stat,//0未收到UTR，1收到UTR但未匹配订单可以补单，2已匹配订单）
			'amount' => $resultArr['amount'] / 100,//入账金额，单位分，只有stat结果为1时返回，示例：100.00时返回10000 
		]
	];
	return $return_data;
}

//utr 补单
function utrorder($fin_paylog)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName() . ''];
	$microtime = microtime(true); // 获取浮点数形式的当前时间戳
	$timestamp = round($microtime * 1000); // 将时间戳转换为毫秒级
	$pdata = [
		'merchantId' => $config['mch_id'],
		'timestamp' => $timestamp,
		'utr' => $fin_paylog['utr'],
		'orderId' => $fin_paylog['osn'],
	];
	$pdata['sign'] = paySign($pdata, 1);
	$url = $config['utrorder_url'];
	//writeLog('resultArr : ' . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog('resultArr : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/utrorder');
	if ($resultArr['code'] != 100) {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/utrorder/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'orderId' => $fin_paylog['osn'],
			'utr' => $fin_paylog['utr'],
		]
	];
	return $return_data;
}

//utr 查单
function utr($utr)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName() . ''];
	$microtime = microtime(true); // 获取浮点数形式的当前时间戳
	$timestamp = round($microtime * 1000); // 将时间戳转换为毫秒级
	$pdata = [
		'merchantId' => $config['mch_id'],
		'timestamp' => $timestamp,
		'utr' => $utr,
	];
	$pdata['sign'] = paySign($pdata, 1);
	$url = $config['utrorder_url'];
	//writeLog('resultArr : ' . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog('resultArr : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/utr');
	if ($resultArr['code'] != 100) {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/utr/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$stat = 0;
	switch ($resultArr['stat']) {
		case '1':
			$stat = 1;
			break;
		case '2':
			$stat = 2;
			break;
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'orderId' => $resultArr['orderId'],//匹配的订单号，只有stat结果为2时返回
			'stat' => $stat,//0未收到UTR，1收到UTR但未匹配订单可以补单，2已匹配订单）
			'amount' => $resultArr['amount'] / 100,//入账金额，单位分，只有stat结果为1时返回，示例：100.00时返回10000 
		]
	];
	return $return_data;
}

//查询余额
function balance()
{
	$config = $_ENV['PAY_CONFIG'][GetPayName() . ''];
	$microtime = microtime(true); // 获取浮点数形式的当前时间戳
	$timestamp = round($microtime * 1000); // 将时间戳转换为毫秒级
	$pdata = [
		'merchantId' => $config['mch_id'],
		'timestamp' => $timestamp,
	];
	$pdata['sign'] = paySign($pdata, 1);
	$url = $config['balance_url'];
	//writeLog('resultArr : ' . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog('resultArr : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	if ($resultArr['code'] != 100) {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $resultArr['balance'],
			'payout_balance' => $resultArr['balance'],
		]
	];
	return $return_data;
}



function paySign($params, $type = 0)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName() . ''];
	$signStr = '';
	switch ($type) {
		case 0://代收
			// md5(amount+merchantId+orderId+timestamp+密钥)进行MD5，32位小写加密。密钥开户后提供.
			$signStr = $params['amount'] . $params['merchantId'] . $params['orderId'] . $params['timestamp'] . $config['mch_key'];
			break;
		case 1:////查询余额 查询urt 补单utr
			//签名，md5(merchantId +timestamp+密钥)进行MD5加密，32位小写。
			$signStr = $params['merchantId'] . $params['timestamp'] . $config['mch_key'];
			break;
	}

	if ($signStr == '')
		return '';
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