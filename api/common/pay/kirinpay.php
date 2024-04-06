<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "kirinpay";
}

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
        'appid' => $config['mch_id'],
		'pay_type' => 'upi',
		'trade_type' => 'account',
		'amount' => strval($fin_paylog['money']),
        'out_trade_no' => $fin_paylog['osn'],
        'callback_url' => $config['notify_url'],
        'version' => 'v2.0',
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
		'appid' => $config['mch_id'],
	];
	$pdata['sign'] = paySign($pdata);
	$result = curl_post2($config['balance_url'], $pdata, 30);
	if ($result['code'] != 1)
		return $result;

	writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$resultArr = $result['output'];
	if ($resultArr['code'] != 1) {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

    $payout_balance = floatval($resultArr['data']->freeze_balance) +floatval($resultArr['data']->total_money)+floatval($resultArr['data']->use_balance);
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $payout_balance,
			'payout_balance' => $resultArr['data']->balance,
		]
	];
	return $return_data;
}


//签名
function paySign($params)
{
  
    $config = $_ENV['PAY_CONFIG'][GetPayName()];
    $appSecret = $config['mch_key'];
    //去除空參數
    $data = array_filter($params);
    //签名步骤一：按字典序排序参数
    ksort($data);
    $string_a = http_build_query($data);
    $string_a = urldecode($string_a);
    //签名步骤二：在string后加入KEY
    $string_sign_temp = $string_a . "&key=" . $appSecret;
    //签名步骤三：MD5加密
    $sign = md5($string_sign_temp);
    //签名步骤四：所有字符转为大写
    $result = strtoupper($sign);
    return $result;
}