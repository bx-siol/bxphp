<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['mcopay'] = [
	'mch_id' => 'M1660139787',
    'appId' => '62f3b90be4b0f98e9b919037',
	'mch_key' => 'sg2BBrvYF72atfSl5qrCXQNyDd8178Db',
	'pay_url' => 'https://pay.mcopay.net/api/anon/pay/unifiedOrder',
	'query_url' => '',

	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/mcopay/pay',
	'page_url' => REQUEST_SCHEME . '://' . HTTP_HOST
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['mcopay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "mcopay/pay/{$time}.txt";

	$pdata = [
		'mchNo' => $config['mch_id'],	//	是	string	商户号	商户号
		'appId' => $config['appId'],	//	是	string	应用ID	应用ID
		'mchOrderNo' => $fin_paylog['osn'],	//	是	string	商户订单号	商户生成的订单号
		'wayCode' => $sub_type,	//	是	string	支付方式	支付方式,请查阅商户后台通道编码
		//'bankCode' => $config['mch_id'],	//	否	string	银行编码	网银通道必填，其他类型一定不能填该参数
		'amount' => $fin_paylog['money'],	//	是	string	支付金额	支付金额，整数
		'currency' => 'INR',	//	是	string	货币代码	三位货币代码,卢布:INR
		'subject' => 'subject',	//	是	string	商品标题	商品标题
		'body' => 'body',	//	是	string	商品描述	商品描述（尼日利亚代收填付款人姓名）
		'notifyUrl' => $config['notify_url'],	//	否	string	异步通知地址	支付结果异步回调URL,只有传了该值才会发起回调
		'reqTime' => time(),	//	是	string	请求时间	请求接口时间
		'version' => '1.0',	//	是	string	接口版本	接口版本号，固定：1.0 
		'signType' => 'MD5',	//	是	string	签名类型	签名类型，参与签名，固定MD5

	];
	$pdata['sign'] = paySign($pdata);
    file_put_contents($logpathd, $pdata['sign'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);

	if ($result['code'] != 1) {
		//file_put_contents($logpathd,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];

	if ($resultArr['code'] != '0') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['payOrderId'],
			'pay_url' => $resultArr['data']['payData']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['mcopay'];
	$pdata = [
		'pay_orderid' => $order['osn'],
		'pay_memberid' => $config['mch_id']
	];
	$pdata['pay_md5sign'] = paySign($pdata);
	$result = payCurlPost($config['query_url'], $pdata, 30);
	$resultArr = $result['output'];
	if ($resultArr['returncode'] != '00') {
		return ['code' => -1, 'msg' => $resultArr['trade_state']];
	}
	return ['code' => 1, 'msg' => $resultArr['trade_state'], 'data' => $resultArr];
}

function paySign($params)
{
	$config = $_ENV['PAY_CONFIG']['mcopay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sys_sign'   || $key == 'attach') {
			continue;
		}

		if ($val != null) {
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$signStr .= 'key=' .  $config['mch_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "mcopay/paySign{$time}.txt";
	$outstr = strtoupper(md5($signStr));
	  file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr, FILE_APPEND);
	return $outstr;
}

function payCurlPost($url, $data = [], $timeout = 30)
{
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => $timeout,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => http_build_query($data),
		CURLOPT_HTTPHEADER => array(
			'Content-Type:application/x-www-form-urlencoded',
		)
	));

	$response = curl_exec($curl);
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "mcopay/Post{$time}.txt";
	//file_put_contents($logpath, '=============>' . $response  . "\r\n\r\n", FILE_APPEND);
	if ($curl->error) {
		$arrCurlResult = [
			'code' => -1,
			'msg' => $curl->errorMessage
		];
	} else {
		$arrCurlResult = [
			'code' => 1,
			'msg' => 'ok',
			'output' => json_decode($response, true)
		];
	}

	curl_close($curl);
	unset($curl);
	return $arrCurlResult;
}
