<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "xdpay";
}

function CashOrder($fin_cashlog)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
    $pdata = [
		'merchant' => $config['mch_id'],
        'payCode' => $config['dpay_type'],
		'amount' => strval($fin_cashlog['real_money']),
		'orderId' => $fin_cashlog['osn'],
		'notifyUrl' => $config['dnotify_url'],
		'bankAccount' => $fin_cashlog['receive_account'],
		'customName' => $fin_cashlog['receive_realname'],
		'remark' => $fin_cashlog['receive_ifsc'],
	];
	$rdata['sign'] = CashSign($pdata);

	writeLog("pdata：" .json_encode($pdata)."\r\n"."rdata：".json_encode($rdata), GetPayName() . '/cash');
	$result = CurlPost($config['dpay_url'], $rdata, 30);
	writeLog("result：" .json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');	
	if ($result['response_code'] != 200)
		return $result;

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
    ksort($params);
    $config = $_ENV['PAY_CONFIG'][GetPayName()];
    $appSecret = $config['mch_key'];
    foreach ($params as $key => $value) {
		if (empty ($key) || empty ($value) || $key == 'sign') {
			continue;
		}
		$signOriginStr .=  "$key=$value&";
	}
    $signOriginStr = $signOriginStr . "key=$appSecret";
    return  strtolower(md5($signOriginStr));
}