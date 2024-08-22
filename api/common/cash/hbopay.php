<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "hbopay";
}
function CashOrder($fin_cashlog)
{    
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'appId' => $config['mch_id'],
		'mchOrderNo' => $fin_cashlog['osn'],
        'amount' => strval(floor($fin_cashlog['real_money'])),
        'passageId' => $config['dpay_id'],
        'currency' => 'INR',
        'notifyUrl' => $config['dnotify_url'],
        'bankCode' => $fin_cashlog['receive_ifsc'],
        'accountName' => $fin_cashlog['receive_realname'],
        'accountNo' => $fin_cashlog['receive_account'],
        'notifyFormat' => 'FORM-DATA',
        'my_order_no' => ''
	];
	$pdata['sign'] = CashSign($pdata);    
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');

	$result = curl_post2($config['dpay_url'], $pdata, 30);

	if ($result['code'] != 1)
		return $result;

    writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	$resultArr = $result['output'];
	if ($resultArr['code'] != '1') {
		if($resultArr['msg'] != '商户订单号已存在，请勿重复提交！')
		{
			writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
			return ['code' => -1, 'msg' => $resultArr['msg']];
		}		
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
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
    $signOriginStr =$params['appId'] .$params['mchOrderNo'] .$params['my_order_no'] .$params['amount'] .$params['currency'] .$params['notifyUrl'] .$config['mch_key'];
	$sign = md5($signOriginStr);
	return  $sign;
}
