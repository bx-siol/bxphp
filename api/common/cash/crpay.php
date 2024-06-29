<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "crpay";
}
function CashOrder($fin_cashlog)
{    
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'appid' => $config['mch_id'],
		'orderno' => $fin_cashlog['osn'],
        'amount' => strval(floor($fin_cashlog['real_money'])),
        'username' => $fin_cashlog['receive_realname'],
        'bankaccount' => $fin_cashlog['receive_account'],
        'bankcode' => 'SBI',
        'bankname' => $fin_cashlog['receive_bank_name'],
        'phone' => strval(mt_rand(1000000000, 9999999999)),
        'email' => strval(mt_rand(1000000000, 9999999999)) .'@wilnetonline.net',
        'accth' => $fin_cashlog['receive_ifsc'],
        'notifyurl' => $config['dnotify_url'],
	];

	$pdata['sign'] = CashSign($pdata);
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	$result = curl_post2($config['dpay_url'], $pdata, 30);

	if ($result['code'] != 1)
		return $result;

    writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	$resultArr = json_decode($result['output'][0],true);
	if ($resultArr['code'] != '0') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $resultArr['orderno'],
			'out_osn' => $resultArr['porderno']
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
