<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['dpay'] = [
	'mch_id' => '188111038',
	//'appId' => '62f3b90be4b0f98e9b919037',
	'dpay_key' => 'b9f0e45c54ff4fbd8a4d0684ffffe3f1',
	'dpay_url' => 'https://api.3dpay.vip/withdraw/order/create',
	'dnotify_url' => 'http://' . PAY_BACKURL . '/' . 'api/Notify/dpay/cash'
];

function dpayCashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['dpay'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "dpay/cash/{$time}.txt";
	$pdata = [
		'mer_no' => $config['mch_id'],
		//	商户号	String	Y	平台分配商户号
		'settle_id' => $fin_cashlog['osn'],
		//	代付订单号	String	Y	商户订单号唯一
		'settle_amount' => $fin_cashlog['real_money'],
		//	代付金额	String	Y	最多2位小数，法币
		'bankCode' => 'SBIN',
		//	收款银行编码	String	Y	收款账户对应的银行编码,upi时填UPI
		'ifsc' => $fin_cashlog['receive_ifsc'],
		//	ifsc编码	String	N	印度银行卡代付必填，upi时可为空
		'settle_date' => date("Y-m-d H:i:s", time()),
		//	代付申请时间	String	Y	时间格式：yyyy-MM-dd HH:mm:ss 
		'currency' => 'INR',
		//	是	string	货币代码	三位货币代码,卢布:INR		
		'accountNo' => $fin_cashlog['receive_account'],
		//	是	string	收款银行账号	收款人银行账号(巴西PIX代付填PIX账号)
		'accountName' => $fin_cashlog['receive_realname'],
		//	是	string	收款银行户名	收款人姓名
		'notifyUrl' => $config['dnotify_url'], //	否	string	异步通知地址	代付结果异步回调URL,只有传了该值才会发起回调	 
	];
	$pdata['sign'] = dpayCashSign($pdata);
	//$pdata['sign_type'] = 'MD5'; 
	$url = $config['dpay_url'];
	file_put_contents($logpathd, $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = dpayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd, var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['code'] != 'SUCCESS') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
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

function dpayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['dpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'sys_sign' || $key == 'attach' || !$val) {
			continue;
		}
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= 'key=' . $config['dpay_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "dpay/paySign{$time}.txt";
	file_put_contents($logpath, "signstr:\r\n" . $signStr . "\r\n", FILE_APPEND);
	$outstr = (md5($signStr));
	// file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . var_export($params, true), FILE_APPEND);
	return $outstr;
}
function dpayCurlPost($url, $data = [], $timeout = 30)
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
		// CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
		// CURLOPT_HTTPHEADER => array(
		// 	'Content-Type: application/json;charset=UTF-8'
		// )
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