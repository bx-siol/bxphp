<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "kirinpay";
}
function CashOrder($fin_cashlog)
{    
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'appid' => $config['mch_id'],
        'money' => strval(number_format($fin_cashlog['real_money'], 2, '.', '')),
        'out_trade_no' => $fin_cashlog['osn'],
        'type' => 'IMPS',
        'name' => $fin_cashlog['receive_realname'],
        'account' => $fin_cashlog['receive_account'],
        'ifsc_code' => $fin_cashlog['receive_ifsc'],
        'callback' => $config['dnotify_url'],
	];

	$pdata['sign'] = CashSign($pdata);
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	$result = curl_post2($config['dpay_url'], $pdata, 30);

	if ($result['code'] != 1)
		return $result;

	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	if ($resultArr['code'] != 1) {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['data']->order_no
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
    return  strtoupper(md5($signOriginStr));
}
