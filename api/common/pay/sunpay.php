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
        'version' => '1.0',
		'mch_id' => $config['mch_id'],
		'notify_url' => $config['notify_url'],
		'mch_order_no' => $fin_paylog['osn'],
		'pay_type' => '173',
		'trade_amount' => strval($fin_paylog['money']),
		'order_date' => date("Y-m-d H:i:s"),
        'goods_name' => $fin_paylog['osn'],
	];
    $pdata['sign_type'] = 'MD5';
    $pdata['key'] = $config['mch_key'];
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
//签名
function paySign($params)
{
    $signOriginStr = '';
    sort($params);
    foreach ($params as $key => $value) {
		if (empty ($key) || empty ($value) || $key == 'sign' || $key == 'sign_type') {
			continue;
		}
		$signOriginStr .=  $key ."=" . $value ."&";
	}    
    $signOriginStr =substr($signOriginStr, 0, strlen($signOriginStr)-1);
	writeLog('字符串：' .$signOriginStr, GetPayName() . '/pay');
    return  md5($signOriginStr);
}
