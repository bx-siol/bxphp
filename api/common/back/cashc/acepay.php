<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['acepay'] = [
	'mch_id' => '10005',
	'acepay_key' => 'kqxl7z18gn42wu9p6rsxzamwidmokpfh',
	'dpay_url' => 'https://api.ace-pay.vip/acepay/pay_out',
	'dnotify_url' => 'http://' . PAY_BACKURL . '/' . 'api/Notify/acepay/cash'
];

function acepayCashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['acepay'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());

	/*
			
			 */

	$pdata = [
		'mch_id' => $config['mch_id'],
		'mch_order_num' => $fin_cashlog['osn'],
		'country' => 'INR',
		'price' => $fin_cashlog['real_money'],
		'account_name' => $fin_cashlog['receive_realname'],
		'account_num' => intval($fin_cashlog['receive_account']),
		'account_bank' => 'SBI',
		'ifsc' => $fin_cashlog['receive_ifsc'],
		'notify_url' => $config['dnotify_url'],
		'timestamp' => time(),
	];
	$pdata['sign'] = acepayCashSign($pdata);
	$pdata['sign_type'] = 'MD5';

	$url = $config['dpay_url'];
	file_put_contents(LOGS_PATH . 'acepay/cash/Order' . $time . '.txt', var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = acepayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents(LOGS_PATH . 'acepay/cash/Result' . $time . '.txt', var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);

	if ($resultArr['code'] != '200') {
		return ['code' => -1, 'msg' => $resultArr['errorMsg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $fin_cashlog['osn'],
		]
	];
	return $return_data;
}

function acepayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['acepay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'signType') {
			continue;
		}
		if ($val != null) {
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$time = date("Y-m-d", time());
	$signStr .= 'key=' . $config['acepay_key'];
	file_put_contents(LOGS_PATH . 'acepay/cash/CashSign' . $time . '.txt', "signstr:\r\n" . $signStr . "\r\n", FILE_APPEND);
	return strtoupper(md5($signStr));
}
function acepayCurlPost($url, $data = [], $timeout = 30)
{
	$curl = curl_init();
	curl_setopt_array(
		$curl,
		array(
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
			CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
			CURLOPT_HTTPHEADER => array(
				'Content-Type:application/json'
			)
		)
	);

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