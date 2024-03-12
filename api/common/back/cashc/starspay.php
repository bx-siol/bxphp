<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['starspay'] = [
	'mch_id' => '100805906',
	'mch_key' => 'OT0CPA3SNHP7DQ8NVOYQOBX6QF4ZY981',
	'dpay_url' => 'https://interface.starspayglobal.com/pay/transfer',
	'dnotify_url' => 'http://47.243.82.107/api/Notify/starspay/cash'
];

function starspayCashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['starspay'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d H:i:s", time());

	/*
		sign_type	签名方式	String	Y	固定值MD5，不参与签名 
		sign	签名	String	Y	不参与签名 
		mch_id	商户代码	String	Y	平台分配唯一
		mch_transferId	商家转账订单号	String	Y	保证每笔订单唯一
		transfer_amount	转账金额	String	Y	整数，以元为单位 
		apply_date	申请时间	String	Y	时间格式：yyyy-MM-dd HH:mm:ss 
		bank_code	收款银行代码	String	Y	详见附件银行编码或商户后台银行代码表 
		receive_name	收款银行户名	String	Y	银行户名【注：菲律宾代付，该参数需要遵循 “LastName,FirstName,MiddleName” 这个规则提交(逗号为英文逗号)】
		receive_account	收款银行账号	String	Y	银行账号(巴西PIX代付填对应类型的PIX账号) 
		back_url	异步通知地址	String	N	若填写则需参与签名,不能携带参数 
		document_type	类型	String	N	  
		哥伦比亚代付填写：1:身份证、2:税号;
		注:哥伦比亚代付请在remark内填写此编号对应的值； 
	*/

	$tyy = '3';
	if (strlen($fin_cashlog['receive_account']) == 18) $tyy = '40';

	$pdata = [
		'mch_id' => $config['mch_id'],
		'mch_transferId' => $fin_cashlog['osn'],
		'transfer_amount' => floatval($fin_cashlog['real_money']),
		'receive_name' => $fin_cashlog['receive_realname'],
		'receive_account' => ($fin_cashlog['receive_account']),
		'bank_code' => $fin_cashlog['receive_bank_id'],
		'apply_date' => $time,
		'document_type' => '1',
		'remark' => $fin_cashlog['receive_ifsc'],
		'back_url' => $config['dnotify_url']
	];
	$pdata['sign'] = starspayCashSign($pdata);
	$pdata['sign_type'] = 'MD5';

	$url = $config['dpay_url'];
	file_put_contents(LOGS_PATH . 'cashOrderstarspay.txt', $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = starspayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents(LOGS_PATH . 'cashOrderstarspayResult.txt', $url . "\r\n" . var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['respCode'] != 'SUCCESS') {
		return ['code' => -1, 'msg' => $resultArr['errorMsg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['tradeNo']
		]
	];
	return $return_data;
}

function starspayCashSign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['starspay'];
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

function starspayCurlPost($url, $data = [], $timeout = 30)
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
		// CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
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
