<?php

use Curl\Curl;
use think\facade\Db;

/*
哥伦比亚	977000001	哥伦比亚网关一类=1100	哥伦比亚网关二类=1120	支付密钥：572ec680736f4a42a711c83a44d312d9
代付密钥：VMARQ7ULGHUMBJ5KF9WWTDFQHCGY3GEO
*/

$_ENV['PAY_CONFIG']['wowpay'] = [
	'mch_id' => '100805906',
	'mch_key' => '1697eb2df6564382a737b7e406c81e21',
	'pay_url' => 'https://interface.wowpayglobal.com/pay/web',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/wowpay/pay',
	'page_url' => REQUEST_SCHEME . '://' . HTTP_HOST
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['wowpay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d H:i:s", time());
	$fliepath =	LOGS_PATH . 'wowpay/pay/' . date("Y-m-d", time()) . '.txt';
	/*
	version	版本号	String	N	需同步返回JSON 必填，固定值 1.0
	mch_id	商户号	String	Y	平台分配唯一
	notify_url	异步通知地址	String	Y	不超过 200 字节,支付成功后发起,不能携带参数
	page_url	同步跳转地址	String	N	不超过 200 字节,支付成功后跳转地址,不能携带参数
	mch_order_no	商家订单号	String	Y	保证每笔订单唯一
	pay_type	支付类型	String	Y	请查阅商户后台通道编码
	trade_amount	交易金额	String	Y	以元为单位
	order_date	订单时间	String	Y	时间格式yyyy-MM-dd HH:mm:ss 
	goods_name	商品名称	String	Y	不超过 50 字节 
	sign_type	签名方式	String	Y	固定值 MD5，不参与签名
	sign	签名	String	Y	不参与签名
 
	*/
	$pdata = [
		'mch_id' => $config['mch_id'],
		'mch_order_no' => $fin_paylog['osn'],
		'trade_amount' => strval($fin_paylog['money']),
		'version' => '1.0',
		'order_date' => strval($time),
		'goods_name' => 'goods_name',
		'page_url' => strval($config['page_url']),
		'pay_type' => strval($sub_type),
		'notify_url' => $config['notify_url']
	];
	$pdata['sign'] = paySign($pdata);
	$pdata['sign_type'] = 'MD5';
	file_put_contents($fliepath, $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n ===============", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);

	if ($result['code'] != 1) {
		file_put_contents($fliepath,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];
	//p($resultArr);exit;

	if ($resultArr['respCode'] != 'SUCCESS') {
		return ['code' => -1, 'msg' => $resultArr['tradeMsg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['tradeMsg'],
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
	$config = $_ENV['PAY_CONFIG']['wowpay'];
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

function paySign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['wowpay'];
	ksort($pdata);

	$fliepath =	LOGS_PATH . 'wowpay/paySign' . date("Y-m-d", time()) . '.txt';
	$str = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign' || $pk == 'sign_type' || $pk == 'signType' || !$pv) {
			continue;
		}
		$str .= "{$pk}={$pv}&";
	}
	$str .= 'key=' . $config['mch_key'];
	file_put_contents($fliepath, "\r\n" . '=============>' . $str . "\r\n\r\n" . md5($str) . "\r\n\r\n", FILE_APPEND);
	return (md5($str));
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
		// CURLOPT_HTTPHEADER => array(
		// 	'Content-Type: application/json'
		// )
	));

	$response = curl_exec($curl); 
	// file_put_contents(LOGS_PATH . 'payOrderwow.txt', '=============>' . $response  . "\r\n\r\n", FILE_APPEND);
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
