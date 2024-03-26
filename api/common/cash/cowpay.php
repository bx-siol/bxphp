<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "cowpay";
}

function CashOrder($fin_cashlog)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$microtime = microtime(true); // 获取浮点数形式的当前时间戳
	$milliseconds = round($microtime * 1000); // 将时间戳转换为毫秒级
	// $pdata = [
	// 	'outType' => 'IMPS',
	// 	'merchantId' => $config['mch_id'],
	// 	'orderId' => $fin_cashlog['osn'],
	// 	'timestamp' => $milliseconds,
	// 	'amount' => floor($fin_cashlog['real_money'] * 100),
	// 	'accountHolder' => $fin_cashlog['receive_realname'],
	// 	'accountNumber' => ($fin_cashlog['receive_account']),
	// 	'ifsc' => $fin_cashlog['receive_ifsc'],
	// 	'notifyUrl' => $config['dnotify_url']
	// ];
    $pdata = [
		'merchant_code' => $config['mch_id'],
        'order_no' => $fin_cashlog['osn'],
        'order_amount' => floor($fin_cashlog['real_money'] * 100),
        'pay_type' =>'india-bank-repay',
        'bank_name' => 'Canara Bank',
        'bank_card' => $fin_cashlog['receive_account'],
        'bank_branch' => $fin_cashlog['receive_ifsc'],
        'user_name' => $fin_cashlog['receive_realname'],
        'notify_url' => $config['dnotify_url'],
	];
	$pdata['sign'] = CashSign($pdata);
    $pdata['signtype']  = "MD5";
    $pdata['transdata']  = urlencode(mb_convert_encoding($pdata, 'UTF-8', 'GB2312'));
	$url = $config['dpay_url'];
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	$result = CurlPost($url, $pdata, 30);
	writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	if ($result['status'] != 'true') {
		writeLog('result : ' . json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
		return ['code' => -1, 'msg' => $result['message']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['message'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => ''
		]
	];
	return $return_data;
}

function CashSign($params)
{
    // sign	是	string	签名，md5(amount+merchantId+orderId+timestamp+secret)进行MD5加密，32位小写。

	// // sign	是	string	签名，md5(amount+merchantId+orderId+timestamp+secret)进行MD5加密，32位小写。
	// $config = $_ENV['PAY_CONFIG'][GetPayName()];
	// $signStr = $params['amount'] . $params['merchantId'] . $params['orderId'] . $params['timestamp'] . $config['mch_key'];
	// //writeLog(json_encode('signStr : ' . $signStr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName());
	// $outstr = strtolower(md5($signStr));
	// //writeLog(json_encode('outstr : ' . $outstr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName());
	// return $outstr;



}