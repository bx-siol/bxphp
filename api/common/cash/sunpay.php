<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "sunpay";
}
function CashOrder($fin_cashlog)
{    
	writeLog("开始", GetPayName() . '/cash');
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'mch_id' => $config['mch_id'],
		'mch_transferId' => $fin_cashlog['osn'],
        'transfer_amount' => $fin_cashlog['real_money'],
        'apply_date' => date("Y-m-d H:i:s"),
        'bank_code' => 'IDPT0001',
        'receive_name' => $fin_cashlog['receive_realname'],
        'receive_account' => $fin_cashlog['receive_account'],
        'remark' => $fin_cashlog['receive_ifsc'],
        'back_url' => $config['dnotify_url'],
        'sign_type' => 'MD5',
	];

	$pdata['sign'] = CashSign($pdata);
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	$result = curl_post2($config['dpay_url'], $pdata, 30);

	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	if ($resultArr['respCode'] != 'SUCCESS') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
		return ['code' => -1, 'msg' => $resultArr['errorMsg']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['msg']['tradeNo']
		]
	];
	return $return_data;
}

function CashSign($params)
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
    
	writeLog($signOriginStr, GetPayName() . '/cash');
    return  md5($signOriginStr);
}
