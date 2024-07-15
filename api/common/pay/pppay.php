<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "pppay";
}

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
        'recvid' => $config['mch_id'],
		'orderid' => $fin_paylog['osn'],
		'amount' => $fin_paylog['money'],
        'paytypes'=> 'UPI',
		'notifyurl' => $config['notify_url'],
		'returnurl' => $config['returnUrl'],
		'memuid' => md5($fin_paylog['osn']),
	];
    $pdata['sign'] = paySign($pdata);

	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	$result = CurlPost($config['pay_url'], $pdata, 30);

	if ($result['code'] != 1) {
		return $result;
	}
	
	writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	$resultArr = $result['output'];
	if ($resultArr['code'] != '1') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	
	$rs = json_decode($resultArr['data'],true);	
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $rs['id'],
			'pay_url' => $rs['navurl'] 
		]
	];
	return $return_data;
}

//查询余额
function balance()
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];	
	$url = $config['balance_url'] .'?id=' . $config['mch_id'] .'&pass=' . md5($config['mch_key']);
	$result = CurlGet($url);
	if ($result['code'] != 1)
		return $result;

	writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$resultArr = $result['output'];
	if ($resultArr['code'] != '1') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$rs = json_decode($resultArr['data'],true);	
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $rs["balance"],
			'payout_balance' => $rs["balance"],
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
    $signOriginStr = $params['recvid'] .$params['orderid'] .$params['amount'] .$appSecret;
    return  strtolower(md5($signOriginStr));
}

function payCallbackSign($params)
{ 
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
    $appSecret = $config['mch_key'];
	$signOriginStr = $params['recvid'] .$params['orderid'] . intval($params['amount']) .$appSecret;
	
	writeLog('pdata 1: ' . md5($signOriginStr), 'pppay/notify/pay');

	return md5(md5($signOriginStr) .$appSecret );
}