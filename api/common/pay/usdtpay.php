<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "usdtpay";
}
function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'order_id' => $fin_paylog['osn'], //生成数据包，用到了的数组转json的jsonencode
		'amount' => $fin_paylog['money'],
		'notify_url' => $config['notify_url'],
		'redirect_url' => $config['returnUrl'],
	];

	$pdata['signature'] = paySign($pdata);
	writeLog(json_encode($pdata), GetPayName() . '/pay');
	$result = curl_post($config['pay_url'], $pdata, 30, 'json');
	if ($result['response_code'] != 200)
		return $result;

	$resultArr = json_decode($result['output'], true);
	if ($resultArr['status_code'] != 200) {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => $resultArr['message']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $resultArr['message'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['trade_id'],
			'pay_url' => $resultArr['data']['payment_url']
		]
	];
	return $return_data;
}

//查询余额
function balance()
{
	$return_data = [
		'code' => 1,
		'msg' => '',
		'data' => [
			'merId' => '1',
			'balance' => '0',
			'payout_balance' => '0',
		]
	];
	return $return_data;
	// $config = $_ENV['PAY_CONFIG'][GetPayName()];
	// $pdata = [
	// 	'merchant_code' => $config['mch_id'],
	// ];

	// $rdata['signtype'] = "MD5";
	// $rdata['sign'] = urlencode(paySign($pdata));
	// $rdata['transdata'] = urlencode(json_encode($pdata));
	// $result = curl_post($config['balance_url'], $rdata, 30, 'json');
	// if ($result['response_code'] != 200)
	// 	return $result;

	// $resultArr = json_decode($result['output'], true);
	// writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	// if ($resultArr['status'] != 'true') {
	// 	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
	// 	return ['code' => -1, 'msg' => $resultArr['msg']];
	// }

	// $return_data = [
	// 	'code' => 1,
	// 	'msg' => $resultArr['msg'],
	// 	'data' => [
	// 		'merId' => $config['mch_id'],
	// 		'balance' => $resultArr["balance"]['balance'],
	// 		'payout_balance' => $resultArr["balance"]['withdraw_balance'],
	// 	]
	// ];
	// return $return_data;
}

function paySign($parameter)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$signKey = $config['mch_key'];
	ksort($parameter);
	reset($parameter);
	$sign = '';
	//$urls = '';
	foreach ($parameter as $key => $val) {
		if ($val == '') continue;
		if ($key != 'signature') {
			if ($sign != '') {
				$sign .= "&";
				//$urls .= "&";
			}
			$sign .= "$key=$val";
			//$urls .= "$key=" . urlencode($val);
		}
	}
	$sign = md5($sign . $signKey); //密码追加进入开始MD5签名
	return $sign;
}
