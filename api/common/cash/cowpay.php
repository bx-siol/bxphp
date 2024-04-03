<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "cowpay";
}

function CashOrder($fin_cashlog)
{
	writeLog("开始", 'cowpay/cash');
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
    $pdata = [
		'merchant_code' => $config['mch_id'],
        'order_no' => $fin_cashlog['osn'],
        'order_amount' => floor($fin_cashlog['real_money'] * 100),
        'pay_type' =>'india-bank-repay',
        'bank_name' => 'Canara Bank',
        'bank_card' =>	'3339997788',			// $fin_cashlog['receive_account'],   //银行卡号
        'bank_branch' => 'HDFC0000961',			// $fin_cashlog['receive_ifsc'],	//ifsc
        'user_name' => ' Michael',				// $fin_cashlog['receive_realname'], //持卡人姓名
        'notify_url' => $config['dnotify_url'],
	];
	$rdata['sign'] = urlencode(CashSign($pdata));
    $rdata['signtype']  = "MD5";
    $rdata['transdata']  = urlencode(json_encode($pdata));

	writeLog("pdata：" .json_encode($pdata)."\r\n"."rdata：".json_encode($rdata), GetPayName() . '/cash');
	$result = curl_post($config['dpay_url'], $rdata, 30,'json');
	if ($result['response_code'] != 200)
		return $result;

	writeLog("result：" .json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');	
	$resultArr = json_decode($result['output'], true);
	if ($resultArr['code'] != 0) {
		writeLog('resultArr : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
		return ['code' => -1, 'msg' => $resultArr['message']];
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