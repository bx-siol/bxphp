<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "crpay";
}
function payOrder($fin_paylog, $sub_type = '')
{    
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'appid' => $config['mch_id'],
		'orderno' => $fin_paylog['osn'],
        'amount' => strval(floor($fin_paylog['real_money'])),
        'channel' => $config['pay_type'],
        'goodsname' => 'Top up ' . strval(floor($fin_paylog['real_money'])),
        'timestamp' => time(),
        'pageurl' =>  $config['returnUrl'],
        'notifyurl' => $config['notify_url'],
        'username' => $fin_paylog['gaccount'],
        'email' => $fin_paylog['gaccount'],// .'@wilnetonline.net',
        'phone' => $fin_paylog['gaccount'],
	];

	$pdata['sign'] = CashSign($pdata);
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	$result = [];
	try {
		$result = curl_post2($config['pay_url'], $pdata, 30);
	} catch (\Throwable $th) {
		return ['code' => -1, 'msg' => ''];
	}

    writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'][0];
	if ($resultArr['code'] != 0) {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['message'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => '',
			'pay_url' => $resultArr['payurl'] 
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
    return  md5($signOriginStr);
}
