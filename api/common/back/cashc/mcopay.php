<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['mcopay'] = [
	'mch_id' => 'M1660139787',
	'appId' => '62f3b90be4b0f98e9b919037',
	'mcopay_key' => 'sg2BBrvYF72atfSl5qrCXQNyDd8178Db',
	'dpay_url' => 'https://pay.mcopay.net/api/transferOrder',
	'dnotify_url' => 'http://'.PAY_BACKURL.'/' . 'api/Notify/mcopay/cash'
];

function mcopayCashOrder($fin_cashlog)
{
	if (strlen($fin_cashlog['receive_account']) < 14) {
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config = $_ENV['PAY_CONFIG']['mcopay'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d", time());
	$logpathd = LOGS_PATH . "mcopay/cash/{$time}.txt";
	$pdata = [
		'mchNo' => $config['mch_id'],	//	是	string	商户号	商户号
		'appId' => $config['appId'],	//	是	string	应用ID	应用ID
		'mchOrderNo' => $fin_cashlog['osn'],	//	是	string	商户订单号	商户生成的订单号
		'ifCode' => '213',	//	是	string	通道编码	请查看商户后台通道信息
		'entryType' => 'IDPT0001',	//	是	string	入账方式	具体请参考接口文档银行编码，巴西PIX代付填固定值：PIX
		'amount' => intval($fin_cashlog['real_money']),	//	是	string	代付金额	代付金额，整数，以元为单位		
		'currency' => 'INR',	//	是	string	货币代码	三位货币代码,卢布:INR		
		'accountNo' => $fin_cashlog['receive_account'],	//	是	string	收款银行账号	收款人银行账号(巴西PIX代付填PIX账号)
		'accountName' => $fin_cashlog['receive_realname'],	//	是	string	收款银行户名	收款人姓名
		'transferDesc' => $fin_cashlog['receive_ifsc'],	//	否	string	备注信息	收款人的CPF或其他密钥号码(所有巴西代付类型都必填该参数,印度代付必填IFSC码)		  
		'notifyUrl' => $config['dnotify_url'],	//	否	string	异步通知地址	代付结果异步回调URL,只有传了该值才会发起回调
		'reqTime' => time(),	//	是	string	请求时间	请求接口时间
		'version' => '1.0',	//	是	string	接口版本	接口版本号，固定：1.0 
		'signType' => 'MD5',	//	是	string	签名类型	参与签名，签名类型，固定MD5
	];
	$pdata['sign'] = mcopayCashSign($pdata);
	//$pdata['sign_type'] = 'MD5'; 
	$url = $config['dpay_url'];
	file_put_contents($logpathd, $url . "\r\n" . var_export($pdata, true) . "\r\n\r\n", FILE_APPEND);
	$result = mcopayCurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	file_put_contents($logpathd, var_export($resultArr, true) . "\r\n\r\n", FILE_APPEND);
	if ($resultArr['code'] != '0') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['data']['transferId']
		]
	];
	return $return_data;
}

function mcopayCashSign($params)
{
	$config = $_ENV['PAY_CONFIG']['mcopay'];
	ksort($params);
	$signStr = '';
	foreach ($params as $key => $val) {
		if ($key == 'sign' || $key == 'sign_type' || $key == 'sys_sign' || $key == 'attach' || !$val) {
			continue;
		}
		$signStr .= $key . '=' . $val . '&';
	}
	$signStr .= 'key=' .  $config['mcopay_key'];
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "mcopay/paySign{$time}.txt";
	file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n", FILE_APPEND);
	$outstr = strtoupper(md5($signStr));
	// file_put_contents($logpath,  "signstr:\r\n" . $signStr . "\r\n" . $outstr . "\r\n" . var_export($params, true), FILE_APPEND);
	return $outstr;
}
function mcopayCurlPost($url, $data = [], $timeout = 30)
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
