<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "sunpay";
}

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
        'merchant' => '1.0',
		'payCode' => $config['mch_id'],
		'amount' => $config['notify_url'],
        'orderId'=> $config['returnUrl'],
		'notifyUrl' => $fin_paylog['osn'],
		'callbackUrl' => $config['pay_type'],
	];
    $pdata['sign'] = paySign($pdata);

	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	$result = [];
	try {
		$result = curl_post2($config['pay_url'], $pdata, 30);
	} catch (\Throwable $th) {
		return ['code' => -1, 'msg' => ''];
	}

	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	if ($resultArr['respCode'] != 'SUCCESS') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => 'Channel is not open'];
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['message'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['orderNo'],
			'pay_url' => $resultArr['payInfo'] 
		]
	];
	return $return_data;
}

//查询余额
function balance()
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];	
	$pdata = [
		'mch_id' => $config['mch_id'],
	];
	$pdata['sign'] = pay1Sign($pdata);
	$pdata['sign_type'] = 'MD5';
	$result = curl_post2($config['balance_url'], $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	if ($resultArr['respCode'] != 'SUCCESS') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['errorMsg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['respCode'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $resultArr['amount'],
			'payout_balance' => $resultArr['availableAmount'],
		]
	];
	return $return_data;
}


//签名
function paySign($params)
{
    ksort($params);
    $config = $_ENV['PAY_CONFIG'][GetPayName()];
    $appSecret = $config['mch_key'];
    foreach ($params as $key => $value) {
		if (empty ($key) || empty ($value) || $key == 'sign' || $key == 'sign_type' || $key == 'signType') {
			continue;
		}
		$signOriginStr .=  "$key=$value&";
	}
    $signOriginStr = $signOriginStr . "key=$appSecret";
    return  md5($signOriginStr);
}

function pay1Sign($params)
{
    ksort($params);
    $config = $_ENV['PAY_CONFIG'][GetPayName()];
    $appSecret = $config['dmch_key'];
    foreach ($params as $key => $value) {
		if (empty ($key) || empty ($value) || $key == 'sign' || $key == 'sign_type' || $key == 'signType') {
			continue;
		}
		$signOriginStr .=  "$key=$value&";
	}
    $signOriginStr = $signOriginStr . "key=$appSecret";
    return  md5($signOriginStr);
}
