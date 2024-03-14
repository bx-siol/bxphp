<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['reallpay'] = [
	'mch_id' => '707003566',
	'mch_key' => '5e34a1db59a942b382e1abb532076a90',
	'dpay_url' => 'http://pay.reallypay.xyz/withdraw/order/create',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/reallpay/cash'
];

function reallpayCashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['reallpay'];
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d H:i:s", time());

	/*
		mer_no	商户号	String	Y	平台分配商户号
		settle_id	代付订单号	String	Y	商户订单号唯一
		currency	货币代码	String	Y	查看商户后台
		settle_amount	代付金额	String	Y	最多2位小数，法币
		bankCode	收款银行编码	String	Y	收款账户对应的银行编码,upi时填UPI
		accountName	收款人	String	Y	收款人名称
		accountNo	收款账户	String	Y	收款人银行账户,UPI时为upi账户
		ifsc	ifsc编码	String	N	印度银行卡代付必填，upi时可为空
		settle_date	代付申请时间	String	Y	时间格式：yyyy-MM-dd HH:mm:ss
		notifyUrl	异步通知地址	String	N	异步通知代付结果：仅成功或失败时通知 
		receivePhone	收款人手机号码	String	Y	若填写则需参与签名(墨西哥、肯尼亚代付必填)
		sign	签名	String	Y	MD5签名值
	*/

	$tyy = '3';
	if (strlen($fin_cashlog['receive_account']) == 18) $tyy = '40';

	$pdata = [
		'mer_no' => $config['mch_id'],
		'settle_id' => $fin_cashlog['osn'],
		'settle_amount' => floatval($fin_cashlog['real_money']),
		'accountName' => $fin_cashlog['receive_realname'],
		'accountNo' => intval($fin_cashlog['receive_account']),
		'bankCode' => 'SBIN',
		'settle_date' => $time,
		'currency' => 'INR',
		'receivePhone' => $phone,
		'ifsc' => $fin_cashlog['receive_ifsc'],
		'notifyUrl' => $config['dnotify_url']
	];
	$pdata['sign'] = reallpayCashSign($pdata);
	//$pdata['sign_type'] = 'MD5';

	$url = $config['dpay_url'];
	file_put_contents(LOGS_PATH . 'reallpay/cash/Orderreallpay.txt', $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = reallpayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents(LOGS_PATH . 'reallpay/cash/OrderreallpayResult.txt', $url . "\r\n" . var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['code'] != 'SUCCESS') {
		return ['code' => -1, 'msg' => $resultArr['errorMsg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['settle_id']
		]
	];
	return $return_data;
}

function reallpayCashSign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['reallpay'];
	ksort($pdata);
	$str = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign' || $pk == 'sign_type' || $pk == 'signType' || !$pv) {
			continue;
		}
		$str .= "{$pk}={$pv}&";
	}
	$str .= 'key=' . $config['mch_key'];
	return (md5($str));
}

function reallpayCurlPost($url, $data = [], $timeout = 30)
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
		// CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
		// CURLOPT_HTTPHEADER => array(
		// 	'Content-Type: application/json'
		// )
	));

	$response = curl_exec($curl);
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
