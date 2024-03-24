<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "bobopay";
}


function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	//$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$pdata = [
		'merchantId' => $config['mch_id'],
		'orderId' => $fin_paylog['osn'],
		'phone' => $phone,
		'amount' => strval($fin_paylog['money']),
		'timestamp' => time(),
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
	//writeLog( json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName().'/pay');
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	if ($resultArr['status'] != '200') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => 'Channel is not open'];
	}
	$resultArr['params'] = json_decode($resultArr['params'], true);
	$return_data = [
		'code' => 1,
		'msg' => $result['message'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['payOrderId'],
			'pay_url' => $resultArr['data']['paymentUrl']
		]
	];
	return $return_data;
}
//查询余额
function balance()
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$microtime = microtime(true); // 获取浮点数形式的当前时间戳
	$timestamp = round($microtime * 1000); // 将时间戳转换为毫秒级
	$pdata = [
		'merchantId' => $config['mch_id'],
		'timestamp' => $timestamp,
	];
	$pdata['sign'] = paySign($pdata, 1);
	$url = $config['balance_url'];
	//writeLog( json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	if ($resultArr['status'] != 200) {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['message']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['message'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $resultArr['data']['totalAmount'],
			'payout_balance' => $resultArr['data']['availableAmount'],
		]
	];
	return $return_data;
}

function paySign($params, $type = 0)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
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
