<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['nice'] = [
	'mch_id' => 'xx',
	'appId'=>'30842dd53efc40e493922b33471467cc',
	'mch_key' => 'Z5HRZD7NZCRNWSVZXNOJVMWFTPVGS3J4SKGBXXLCOMKVHKBPLBA1AVQRVZAULQ9RIYBQTZ3QIEAUCJLL6X10CCEBYB2PNOV01BMLBJG6ZINZ8E2VU3DVS1VXGPDL7ISE',
	'pay_url' => 'http://123.56.24.108:3020/api/collection/create',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL. '/api/Notify/Nice/pay',
	'page_url' => REQUEST_SCHEME . '://' . HTTP_HOST
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['nice'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);

	/*
	mchId	商户ID	必填	分配的商户号	long
	appId	应用ID	必填	登录商户系统获得	String(32)
	productId	支付产品ID	必填	固定productId=9000	int
	amount	订单金额	非必填	支付金额,单位分	int
	idNumber	证件号码	必填	客户唯一标识	String
	contact	联系方式	必填	联系方式	String
	notifyUrl	支付结果后台回调URL	必填		String(128)
	sign	签名	必填	签名值，详见签名算法	String(32)
	*/
	$pdata = [
		'mchId' => $config['mch_id'],
		'idNumber' => $fin_paylog['osn'],
		'amount' => strval($fin_paylog['money']),
		'appId' => $config['appId'],
		'contact' => $phone,
		'productId' => '9000',
		'notifyUrl' => $config['notify_url']
	];
	$pdata['sign'] = paySign($pdata);
	//file_put_contents(LOGS_PATH.'payOrderZhongle.txt',$config['pay_url']."\r\n".var_export($pdata,true)."\r\n\r\n",FILE_APPEND);
	file_put_contents(LOGS_PATH . 'payOrderNice.txt', $config['pay_url'] . "\r\n" . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n", FILE_APPEND);
	$result = payCurlPost($config['pay_url'], $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	//p($resultArr);exit;
	if ($resultArr['status'] != 'success') {
		return ['code' => -1, 'msg' => $resultArr['status_mes']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['sys_no'],
			'pay_url' => $resultArr['order_data']
		]
	];
	return $return_data;
}

function queryOrder($order)
{
	$config = $_ENV['PAY_CONFIG']['nice'];
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
	$config = $_ENV['PAY_CONFIG']['nice'];
	ksort($pdata);
	$str = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign' || !$pv) {
			continue;
		}
		$str .= "{$pk}={$pv}&";
	}
	// $str = trim($str, '&');
	$str .= 'key=' . $config['mch_key'];
	return strtoupper(md5($str));
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
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		)
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
