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
	$rdata['sign'] = urlencode(CashSign($pdata));
    $rdata['signtype']  = "MD5";
    $rdata['transdata']  = urlencode(json_encode($pdata));

	writeLog("pdata：" .json_encode($pdata)."\r\n"."rdata：".json_encode($rdata), GetPayName() . '/cash');
	$result = curl_post($config['dpay_url'], $rdata, 30,'json');
	writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	if ($result['status'] != true) {
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
    $config = $_ENV['PAY_CONFIG'][GetPayName()];
	$appSecret = $config['mch_key'];
	$signOriginStr = '';
	ksort($params);
	foreach ($params as $key => $value) 
		$signOriginStr = "$signOriginStr$key=$value&";
	
	$signOriginStr = $signOriginStr . "key=$appSecret";	
    return  strtoupper(md5($signOriginStr));
}