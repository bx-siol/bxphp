<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['quickpay'] = [
	'mch_id' => '20220810XKQJ43018OMAU',
	'quickpay_key' => 'GYWBFSFPWDOCNHVSBGCGUJQVDXAAMDBX',
	'dpay_url' => 'https://ep-sl.mer.sminers.com/api/formapi',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/quickpay/cash'
];

function quickpayCashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['quickpay'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "quickpay/cash/{$time}.txt";
	$pdata = [
		'sys_cert_no' => '20220810SKMSOAbbSWPVC', //String	否	密钥编号
		'sys_sign_method' => 'MD5',	//String	否	签名方式 
		'sys_api_name' => 'withdraw.create',	//String	否	请求接口名, trade.create、trade.query
		'sys_api_version' => '1.0',	//String	否	请求接口版本, 1.0
		'sys_user_id' => $config['mch_id'],	//String	否	商户号 

		'payPassword' => 'aa5566123', //	String	否	商户提现密码
		'outWithdrawSn' => $fin_cashlog['osn'], //	String	否	商户提现流水号
		'currency' => 'INR', //	String	否	币种, 参见币种表
		'amount' => $fin_cashlog['real_money'] * 100, //	Long	否	金额, 单位分
		'receiverBankName' => $fin_cashlog['receive_account'], //	String	否	收款银行名称 
		'receiverBranchBankNo' => $fin_cashlog['receive_ifsc'], //	String	是	收款银行支行行号（对公转账必填）印度账户IFSC编码 
		'accountType' => 'PERSONAL_BANK_ACCOUNT', //	String	否	收款银行账户类型, COMPANY_BANK_ACCOUNT 对公银行账户, PERSONAL_BANK_ACCOUNT 对私银行账户
		'receiverAccountNo' => $fin_cashlog['receive_account'], //	String	否	收款银行账户, 银行卡号, 对公户账号
		'receiverAccountName' => $fin_cashlog['receive_realname'], //	String	否	收款银行账户开户名 
		'receiverEmail' => $fin_cashlog['receive_account'] . '@email.com', //	String	是	收款人邮箱（印度转账必填）
		'callbackURL' => $config['dnotify_url'], //	String	是	回调通知URL


		// 'bankname' => $fin_cashlog['receive_account'],
		// 'accountname' => $fin_cashlog['receive_realname'],
		// 'cardnumber' => ($fin_cashlog['receive_account']),
		// 'province' => $fin_cashlog['receive_ifsc'],
		// 'city' => 'city',
		// 'subbranch' => $config['dnotify_url']
	];
	$pdata['sys_sign'] = quickpayCashSign($pdata);
	//$pdata['sign_type'] = 'MD5'; 
	$url = $config['dpay_url'];
	// file_put_contents($logpathd, $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = quickpayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd, var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['errorCode'] != 'SUCCEED') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['data']['withdrawSn']
		]
	];
	return $return_data;
}

function quickpayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['quickpay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'sys_sign' || $key == 'attach' || !$val) {
			continue;
		}
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= 'key=' .  $config['quickpay_key'];
	// $time = date("Y-m-d", time());
	// $logpath = LOGS_PATH . "quickpay/paySign{$time}.txt";
	// file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n", FILE_APPEND);
	$outstr = strtoupper(md5($signStr));
	// file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . var_export($params, true), FILE_APPEND);
	return $outstr;
}
function quickpayCurlPost($url, $data = [], $timeout = 30)
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
