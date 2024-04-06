<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "lamepay";
}
function CashOrder($fin_cashlog)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	// $microtime = microtime(true); // 获取浮点数形式的当前时间戳
	// $milliseconds = round($microtime * 1000); // 将时间戳转换为毫秒级
	// $name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$pdata = [
		'authNo' =>	$config['mch_id'], //是	string	支付提供给商家的唯一认证标识
		'bankAccHoldName' => $fin_cashlog['receive_realname'], //是	string	银行账户姓名
		'bankAccNumber' =>	$fin_cashlog['receive_account'], //否	string	银行账户号(upiVal为空时必填)
		'ifscNo' =>	$fin_cashlog['receive_ifsc'], //否	string	印度ifsc编号(upiVal为空时必填) 
		'outTxnNo' => $fin_cashlog['osn'], //是	string	商家订单号
		'txnAmount' => sprintf("%.2f", $fin_cashlog['real_money']), //是	number	交易金额(必须保留2位小数,四舍五入)
		'txnCbUrl' =>	$config['dnotify_url'], //是	string	回调地址
		'txnEmail' => $phone . '@gmail.com', //是	string	客户邮箱
		'txnMobile' =>	$phone, //是	string	客户手机号码(6789开头的10位数字，数字可以随机)
		'txnExtend' =>	$fin_cashlog['osn'], //是	string	商家冗余字段，原样返回 
		'reversedNotifyUrl' =>	$config['dnotify_url'], //否	string	接代付回退通知的回调地址，选填 
	];
	$pdata['sign'] = CashSign($pdata);
	$url = $config['url'] . $config['dpay_url'];
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;

	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash');
	if ($resultArr['code'] != '0000') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/cash/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['data']['txnNo']
		]
	];
	return $return_data;
}

function CashSign($params)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$appSecret = $config['mch_key'];
	$iv = $config['iv'];
	ksort($params);
	$signArr = [];
	foreach ($params as $key => $item) {
		if ($key != 'sign') {
			if (in_array($key, ['txnAmount', 'txnFinalAmount'])) {
				$signArr[] = $key . '=' . sprintf("%.2f", $item); // 保留2位小数
			} else {
				$signArr[] = $key . '=' . $item;
			}
		}
	}
	$signStr = implode('&', $signArr);
	$signArr .= '&key=' . $appSecret;
	return encrypt($signStr, $appSecret, $iv);
}

function encrypt($text, $key, $iv)
{
	$size = 16;
	$pad = $size - (strlen($text) % $size);
	$padtext = $text . str_repeat(chr($pad), $pad);
	$crypt = openssl_encrypt($padtext, "AES-256-CBC", base64_decode($key), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
	return base64_encode($crypt);
}
