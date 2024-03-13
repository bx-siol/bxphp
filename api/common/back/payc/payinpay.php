<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['payinpay'] = [
	'mch_id' => '220748492',
	'mch_key' => '38v2bfa4lj8sq3xvv6ot908jslsp25l8',
	'pay_url' => 'https://Payin.pro/Pay_Index.html',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/payinpay/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['payinpay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "payinpay/pay/{$time}.txt";
	/*
	pay_memberid	商户号	是	是	平台分配商户号
	pay_orderid	订单号	是	是	上送订单号唯一, 字符长度20
	pay_applydate	提交时间	是	是	时间格式：2016-12-26 18:18:18
	pay_bankcode	银行编码	是	是	在商户中心查询
	pay_notifyurl	服务端通知	是	是	服务端返回地址.（POST返回数据）
	pay_callbackurl	页面跳转通知	是	是	页面跳转返回地址（POST返回数据）
	pay_amount	订单金额	是	是	单位：元	
	
	pay_md5sign	MD5签名	是	是	请查看签名算法
	pay_productname	商品名称	是	否[不参与签名]	
	*/
	$pdata = [
		'pay_memberid' => $config['mch_id'],
		'pay_notifyurl' => $config['notify_url'], //异步从通知回调地址
		'pay_orderid' => $fin_paylog['osn'], //商家自己平台的订单号
		'pay_bankcode' => $sub_type, //通道编码，商户后台有配置
		'pay_amount' => $fin_paylog['money'], //价格
		'pay_applydate' => date('Y-m-d H:i:s'),
		'pay_callbackurl' => $config['page_url'], //支付成功之后的跳转页面 
	];
	$pdata['pay_md5sign'] = paySign($pdata);
	$pdata['pay_productname'] = 'name';
	//file_put_contents($logpathd, $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);

	if ($result['code'] != 1) {
		//file_put_contents($logpathd,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	//p($resultArr);exit;

	if (!$resultArr['pay_url']) {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $fin_paylog['osn'],
			'pay_url' => $resultArr['pay_url']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['payinpay'];
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
	$config = $_ENV['PAY_CONFIG']['payinpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'pay_md5sign' || $key == 'signType' || $key == 'attach') {
			continue;
		}

		if ($val != null) {
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$signStr .= 'key=' .  $config['mch_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "payinpay/paySign{$time}.txt";
	$outstr = strtoupper(md5($signStr));
	//file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr, FILE_APPEND);
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
		// CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => http_build_query($data),
		CURLOPT_HTTPHEADER => array(
			'Content-Type:application/x-www-form-urlencoded',
		)
		// CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
		// CURLOPT_HTTPHEADER => array(
		// 	'Content-Type: application/json;charset=UTF-8'
		// )
	));

	$response = curl_exec($curl);
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "payinpay/Post{$time}.txt";
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
