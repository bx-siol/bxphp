<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['wepay'] = [
	'mch_id' => '100313802',
	'mch_key' => 'f16eefdf6f794bfeb6bc1a9f59ac39cb',
	'pay_url' => 'https://payment.weglobalpayment.com/pay/web',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/wepay/pay',
	'page_url' => REQUEST_SCHEME . '://' . HTTP_HOST
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['wepay'];
	//$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	//$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "wepay/pay/{$time}.txt";
	$pdata = [
		'version' => '1.0',
		'goods_name' => 'goodsname',
		'mch_id' => $config['mch_id'],
		'notify_url' => $config['notify_url'], //异步从通知回调地址
		'mch_order_no' => $fin_paylog['osn'], //商家自己平台的订单号
		'pay_type' => $sub_type, //通道编码，商户后台有配置
		'trade_amount' => $fin_paylog['money'], //价格
		'order_date' => date('Y-m-d H:i:s'),
		'page_url' => $config['page_url'], //支付成功之后的跳转页面 
	];
	$pdata['sign'] = paySign($pdata);
	$pdata['sign_type'] = 'MD5';
	//file_put_contents($logpathd, $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);

	if ($result['code'] != 1) {
		//file_put_contents($logpathd,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	if (!$resultArr['payInfo']) {
		return ['code' => -1, 'msg' => $resultArr['tradeMsg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['orderNo'],
			'pay_url' => $resultArr['payInfo']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['wepay'];
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
	$config = $_ENV['PAY_CONFIG']['wepay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'signType' || $key == 'attach') {
			continue;
		}

		if ($val != null) {
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$signStr .= 'key=' .  $config['mch_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "wepay/paySign{$time}.txt";
	$outstr =  md5($signStr) ;
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
	$logpath = LOGS_PATH . "wepay/Post{$time}.txt";
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
