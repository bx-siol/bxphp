<?php

use Curl\Curl;
use think\facade\Db;


function GetPayName()
{
	return "sunpay";
}
function CashOrder($fin_cashlog)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'mch_id' => $config['mch_id'],
		'mch_transferId' => $$fin_cashlog['osn'],
        'transfer_amount' => $$fin_cashlog['osn'],
        'apply_date' => $$fin_cashlog['osn'],
        'bank_code' => $$fin_cashlog['osn'],
        'receive_name' => $$fin_cashlog['osn'],
        'receive_account' => $$fin_cashlog['osn'],
        'remark' => $$fin_cashlog['osn'],
        'back_url' => $$fin_cashlog['osn'],
        'sign_type' => $$fin_cashlog['osn'],
	];

	$pdata['sign'] = CashSign($pdata);

	$url = $config['dpay_url'];
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	if ($resultArr['status'] != '1') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['msg']['transaction_id']
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
    return  md5($signOriginStr);
}
