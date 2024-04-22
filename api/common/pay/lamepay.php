<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "lamepay";
}
function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	//NjE3MGU2NzRjOWNhNDMxOGFlOTkyYjAwMmU1NTk2YTA=
	//XjK6o8V646gv+CO8VHbePg==
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$pdata = [
		'authNo' => $config['mch_id'], //	是	string	支付提供给商家的唯一认证标识
		'outTxnNo' =>	$fin_paylog['osn'], //是	string	商家充值唯一订单号，不可重复 
		'txnAmount' => sprintf("%.2f", $fin_paylog['money']),	//是	number	交易金额(必须保留2位小数,四舍五入)
		'txnCbUrl' => $config['notify_url'],	//是	string	回调地址
		'txnEmail' => $phone . '@gmail.com',	//是	string	客户邮箱
		'txnExtend' => $fin_paylog['osn'], //是	string	商家冗余字段，原样返回
		'txnMobile' => $phone,	//是	string	充值用户手机号码(6789开头的10位数字，数字可以随机)
		'txnName' => $name, //是	string	充值用户真实姓名
		'txnType' => 'UPI', //是	string	充值交易类型，目前固定值 UPI 
	];
	$pdata['sign'] = paySign($pdata);
	$url = $config['url'] . $config['pay_url'];
	//writeLog($url, GetPayName() . '/pay');
	//writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	$result = CurlPost($url, $pdata);

	if ($result['code'] != 1)
		return $result;

	$resultArr =  $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	if ($resultArr['code'] != '0000') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['txnNo'],
			'pay_url' => $resultArr['data']['txnPayLink']
		]
	];
	return $return_data;
}

//查询余额
function balance()
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'authNo' => $config['mch_id'],
		'currentTime' => time() * 1000
	];
	$pdata['sign'] = paySign($pdata);
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$url = $config['url'] . $config['balance_url'];
	$result = curl_post($url, $pdata);

	if ($result['code'] != 1)
		return $result;

	$resultArr = $result['output'];
	//writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	if ($resultArr['code'] != "0000") {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $resultArr["data"]['totalFunds'],
			'payout_balance' => $resultArr["data"]['canWithdrawFunds'],
		]
	];
	return $return_data;
}


function paySign($params)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$appSecret = $config['mch_key'];
	$iv = $config['iv'];
	ksort($params);
	$signArr = [];
	foreach ($params as $key => $item) {
		if ($key == 'sign' || !$item)
			continue;
		if (in_array($key, ['txnAmount', 'txnFinalAmount'])) {
			$signArr[] = $key . '=' . sprintf("%.2f", $item); // 保留2位小数
		} else {
			$signArr[] = $key . '=' . $item;
		}
	}
	$signStr = implode('&', $signArr);
	$signStr .= '&key=' . $appSecret;
	return encrypt($signStr, $appSecret, $iv);
}

function encrypt($text, $key, $iv)
{
	$size = 16;
	$pad = $size - (strlen($text) % $size);
	//writeLog($pad, GetPayName() . '/pay');
	//writeLog(str_repeat(chr($pad), $pad), GetPayName() . '/pay');
	$padtext = $text . str_repeat(chr($pad), $pad);
	//writeLog($text, GetPayName() . '/pay');
	//writeLog($padtext, GetPayName() . '/pay');
	$crypt = openssl_encrypt($padtext, "AES-256-CBC", base64_decode($key), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
	return base64_encode($crypt);
}
