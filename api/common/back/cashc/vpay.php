<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['ppay'] = [
	'mch_id' => '8000450',
	'ppay_key' => 'bb7d7a4f015841948929405a762543da',
	'dpay_url' => 'https://withdraw.ppayglobal.com/withdraw/createOrder',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/ppay/cash'
];

function ppayCashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['ppay'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d H:i:s", time());

	/*
		merNo	商户代码	String	Y	平台分配唯一
		merchantOrderNo	商家转账订单号	String	Y	保证每笔订单唯一
		currency	货币代码	String	Y	参阅商户后台首页币种
		amount	转账金额	String	Y	以法币为单位，最多支持2位小数
		bankCode	收款银行代码	String	Y	详见银行代码表
		印度：固定SBI
	 
		customerName	收款银行户名	String	Y	银行户名
		customerAccount	收款银行账号	String	Y	银行账号:
		 
		印度：UPI时填UPI账户
		 
		accth	备注	String	N	若有值则参与签名
		印度：必填，IFSC码 
		notifyUrl	异步通知地址	String	N	若有值需参与签名
	*/

	$pdata = [
		'merNo' => $config['mch_id'],
		'merchantOrderNo' => $fin_cashlog['osn'],
		'currency' => 'INR',
		'amount' =>  $fin_cashlog['real_money'],
		'customerName' => $fin_cashlog['receive_realname'],
		'customerAccount' => intval($fin_cashlog['receive_account']),
		'bankCode' => 'SBI',
		'accth' => $fin_cashlog['receive_ifsc'],
		'notifyUrl' => $config['dnotify_url']
	];
	$pdata['sign'] = ppayCashSign($pdata);
	//$pdata['sign_type'] = 'MD5';

	$url = $config['dpay_url'];
	file_put_contents(LOGS_PATH . 'p/pcashOrderppay.txt', $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = ppayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents(LOGS_PATH . 'p/cashOrderppayResult.txt', $url . "\r\n" . var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['code'] != 'SUCCESS') {
		return ['code' => -1, 'msg' => $resultArr['errorMsg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['ptOrderNo']
		]
	];
	return $return_data;
}

function ppayCashSign1($pdata)
{
	$config = $_ENV['PAY_CONFIG']['ppay'];
	ksort($pdata);
	$str = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign' || $pk == 'signType' || !$pv) {
			continue;
		}
		$str .= "{$pk}={$pv}&";
	}
	$str .= 'key=' . $config['ppay_key'];
	return (md5($str));
}
function ppayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['ppay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'signType' || !$val) {
			continue;
		}
		if ($val != null) {
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$signStr .= 'key=' .  $config['ppay_key'];
	file_put_contents(LOGS_PATH . 'p/payOrderpppay.txt',  "signstr:\r\n" . $signStr . "\r\n", FILE_APPEND);
	return strtolower(md5($signStr));
}
function ppayCurlPost($url, $data = [], $timeout = 30)
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

		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
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
