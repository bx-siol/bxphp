<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['mx22613'] = [
	'mch_id' => '20210601001',
	//'appId' => 'dd1fffce91964a17a26991d50c78753f',
	'mch_key' => 'ab94c5389cf0ab453b2ef3d8372d1ca9',
	'dpay_url' => ' https://colopen.citipayweb.com/openapi/open/withdraw',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/mx22613/cash'
];

function mx22613CashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['mx22613'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("YmdHis", time());

	/*
		mch_no 是 string 分配给各合作商户的唯一识别码，商户后台获取 
		out_trade_no 是 string 用户支付后商户网站产生的一个唯一的定单号，该订 单号不重复 
		amount 是 decimal 字符串传递，用户支付订单的金额，两位小数。不可 以为零，必需符合金额标准。 
		
		type 是 string BANK, NEQUI 大小写敏感 
		
		notify_url 是 string 异步通知地址 
		ext 是 string 传透参数，异步通知原样返回 
		
		version 是 string 目前填写1.0.0 
		time 是 string 格式：yyyyMMddHHmmss 
		name 是 string 姓名 
		account 是 string 银行收款账号/NEQUI收款地址 
		
		bank_name 否 string 银行名称，收款类型为BANK时必填 
		card_info_type 否 int 卡信息类型 1.身份证 2.税号，收款类型为BANK时填 写 
		card_info 否 string 信息值，收款类型为BANK时填写 
		
		sign 是 string MD5签名
	*/

	$tyy = 'BANK';
	if (strlen($fin_cashlog['receive_account']) == 18) $tyy = 'NEQUI';

	$pdata = [
		'mch_no' => $config['mch_id'],
		'out_trade_no' => $fin_cashlog['osn'],
		'amount' => floatval($fin_cashlog['real_money']) * 100,
		'type' => $tyy,
		'version'=>'1.0.0',
		'name' => $fin_cashlog['receive_realname'],
		'account' => intval($fin_cashlog['receive_account']),
		
		'card_info_type' => $fin_cashlog['receive_bank_id'],
		'bank_name' => $fin_cashlog['receive_bank_name'],
		'time' => $time,
		'remark' => 'remark',
		'notify_url' => $config['dnotify_url']
	];
	$pdata['sign'] = mx22613CashSign($pdata);
	$url = $config['dpay_url'];
	file_put_contents(LOGS_PATH . 'cashOrdermx22613.txt', $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = mx22613CurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents(LOGS_PATH . 'cashOrdermx22613Result.txt', $url . "\r\n" . var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['retCode'] != 'SUCCESS') {
		return ['code' => -1, 'msg' => $resultArr['retMsg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['agentpayOrderId']
		]
	];
	return $return_data;
}

function mx22613CashSign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['mx22613'];
	ksort($pdata);
	$str = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign' || !$pv) {
			continue;
		}
		$str .= "{$pk}={$pv}&";
	}
	$str .= 'key=' . $config['mch_key'];
	return strtoupper(md5($str));
}

function mx22613CurlPost($url, $data = [], $timeout = 30)
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
