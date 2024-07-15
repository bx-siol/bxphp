<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "pppay";
}
function CashOrder($fin_cashlog)
{    
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
    $bankinfo = [
        'bank' => $fin_cashlog['receive_bank_name'],
        'account' => $fin_cashlog['receive_account'],
        'name' => $fin_cashlog['receive_realname'],
        'branch' =>  $fin_cashlog['receive_ifsc'],
    ];
	$pdata = [
		'sendid' => $config['mch_id'],
		'orderid' => $fin_cashlog['osn'],
        'amount' => strval(floor($fin_cashlog['real_money'])),
        'paytypes' => '银行',
        'notifyurl' => $config['dnotify_url'],
        'bankinfo' => json_encode($bankinfo),
        'memuid' =>md5($fin_cashlog['osn']),
	];

	$pdata['sign'] = CashSign($pdata);
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	$result = CurlPost($config['dpay_url'], $pdata, 30);
	if ($result['code'] != 1)
		return $result;

    writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
    $resultArr = $result['output'];
	if ($resultArr['code'] != '1') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
    $rs= json_decode($resultArr['data'],true);
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $rs['orderid'],
			'out_osn' => $rs['id']
		]
	];
	return $return_data;
}

function CashSign($params)
{
    ksort($params);
    $config = $_ENV['PAY_CONFIG'][GetPayName()];
    $appSecret = $config['mch_key'];
    $signOriginStr = $params['sendid'] .$params['orderid'] .$params['amount'] .$params['bankinfo'] .$appSecret;
    return  strtolower(md5($signOriginStr));
}

function CashCallbackSign($params)
{
    $config = $_ENV['PAY_CONFIG'][GetPayName()];
    $appSecret = $config['mch_key'];
    return  md5($params['sign'] .$appSecret );
}