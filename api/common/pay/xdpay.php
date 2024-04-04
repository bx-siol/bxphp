<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "xdpay";
}

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
        'merchant' => $config['mch_id'],
		'payCode' => $config['pay_type'],
		'amount' => strval($fin_paylog['money']),
        'orderId'=> $fin_paylog['osn'],
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

	if ($result['code'] != 1) {
		return $result;
	}
	
	$resultArr = $result['output'];
	if ($resultArr['code'] != '200') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => '',
			'pay_url' => $resultArr['data']['url'] 
		]
	];
	return $return_data;
}

//查询余额
function balance()
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];	
	$pdata = [
		'merchant' => $config['mch_id'],
	];
	$pdata['sign'] = pay1Sign($pdata);
	$result = CurlPost($config['balance_url'], $pdata, 30);
	if ($result['code'] != 1)
		return $result;

	writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$resultArr = $result['output'];
	if ($resultArr['code'] != '200') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['respCode'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $resultArr['data']['balanceAll'],
			'payout_balance' => $resultArr['data']['balanceUsable'],
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
		if (empty ($key) || empty ($value) || $key == 'sign') {
			continue;
		}
		$signOriginStr .=  "$key=$value&";
	}
    $signOriginStr = $signOriginStr . "key=$appSecret";	
	writeLog('signOriginStr : ' . $signOriginStr, 'xdpay/notify/pay');
    return  strtolower(md5($signOriginStr));
}