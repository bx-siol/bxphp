<?php

use Curl\Curl;
use think\facade\Db;



function GetPayName()
{
	return "jwpay";
}
function CashOrder($fin_cashlog)
{
	writeLog("开始代付", GetPayName() . '/cash');
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
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
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	if ($resultArr['code'] != '100') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
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
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$signStr = $params['amount'] . $params['merchantId'] . $params['orderId'] . $params['timestamp'] . $config['mch_key'];
	//writeLog(json_encode('signStr : ' . $signStr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName());
	$outstr = strtolower(md5($signStr));
	//writeLog(json_encode('outstr : ' . $outstr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName());
	return $outstr;
}
