<?php

use Curl\Curl;
use think\facade\Db;



function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['nicepay'];
	//$rand_arr = [6, 7, 8, 9];
	//$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);	 
	$pdata = [
		'merId' => $config['mch_id'],
		'orderId' => $fin_paylog['osn'],
		'orderAmt' => $fin_paylog['money'],
		'desc' => 'desc',
		'channel' => 'IND0',
		'ip' => CLIENT_IP,
		'notifyUrl' => $config['notifyUrl'],
		'returnUrl' => $config['returnUrl'],
		'nonceStr' => $fin_paylog['osn']
	];
	$pdata['sign'] = paySign($pdata);
	$url = $config['pay_url'];
	writeLog('resultArr : ' . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'nicepay/pay');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;

	$resultArr = $result['output'];
	writeLog('resultArr : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'nicepay/pay');
	if ($resultArr['code'] != '1') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'nicepay/pay/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}



	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['sysorderno'],
			'pay_url' => $resultArr['data']['payurl’']
		]
	];
	return $return_data;
}

function paySign($params, $verify = false)
{
	// sign	是	string	签名，md5(amount+merchantId+orderId+timestamp+secret)进行MD5加密，32位小写。
	$config = $_ENV['PAY_CONFIG']['nicepay'];
	if ($verify) {
		$signstr = create_sign($params, $config['md5_key']);
		$sign = strtoupper(md5(trim($signstr)));
		$outstr = rsa_verify($sign, $params['sign'], $config['ptkey']);
	} else {
		$signstr = create_sign($params, $config['md5_key']);
		$sign = strtoupper(md5(trim($signstr)));
		$outstr = rsa_sign($sign, $config['skey']);
	}
	return $outstr;
}
// 创建签名字符串
function create_sign($params, $appSecret)
{
	$signOriginStr = '';
	ksort($params);
	foreach ($params as $key => $value) {
		if (empty($key) || empty($value) || $key == 'sign' || $key == 'signType' || $key == 'signature') {
			continue;
		}
		$signOriginStr = "$signOriginStr$key=$value&";
	}
	return $signOriginStr . "key=$appSecret";
}

// rsa签名
function rsa_sign($dataString, $privateKey)
{
	$pem = chunk_split($privateKey, 64, "\n");
	$pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
	$privKey = openssl_pkey_get_private($pem);
	$signature = false;
	openssl_sign($dataString, $signature, $privKey, OPENSSL_ALGO_SHA256);
	return base64_encode($signature);
}

// rsa验证签名
function rsa_verify($dataString, $signString, $publicKey)
{
	$pem = chunk_split($publicKey, 64, "\n");
	$pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
	$pubKey = openssl_pkey_get_public($pem);

	$signature = base64_decode($signString);
	$flg = openssl_verify($dataString, $signature, $pubKey, OPENSSL_ALGO_SHA256);
	return $flg;
}
function CurlPost($url, $data = [], $timeout = 30)
{
	$time = date("Y-m-d", time());
	$logpath = LOGS_PATH . "nicepay/Post{$time}.txt";
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
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			CURLOPT_HTTPHEADER => array('Content-Type:application/json')
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
