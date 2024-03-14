<?php

use Curl\Curl;
use think\facade\Db;

function CashOrder($fin_cashlog)
{
	$config = $_ENV['PAY_CONFIG']['nicepay'];
	//$rand_arr = [6, 7, 8, 9];
	//$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$pdata = [
		'merId' => $config['mch_id'],
		'orderId' => $fin_cashlog['osn'],
		'money' => $fin_cashlog['real_money'],
		'name' => $fin_cashlog['receive_realname'],
		'ka' => $fin_cashlog['receive_account'],   //bank card number
		'bank' => "bank",
		'zhihang' => $fin_cashlog['receive_ifsc'],   //ifsc
		'nonceStr' => $fin_cashlog['osn'],
		'notifyUrl' => $config['dnotify_url'],
	];
	$pdata['sign'] = CashSign($pdata);
	$url = $config['dpay_url'];
	writeLog(json_encode('pdata : ' . $pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'nicepay/cash');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1) {
		return $result;
	}
	$resultArr = $result['output'];
	writeLog(json_encode('resultArr : ' . $resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'nicepay/cash');
	if ($resultArr['code'] != '1') {
		writeLog('result : ' . json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'nicepay/cash/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['data']['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_cashlog['osn'],
			'out_osn' => $resultArr['data']['orderId']
		]
	];
	return $return_data;
}

function CashSign($params, $verify = false)
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



/**
 * create sign
 * @param string $dataString sign data
 * @param string $privateKey privateKey  
 * @return string
 */
function rsa_sign($dataString, $privateKey)
{
	$pem = chunk_split($privateKey, 64, "\n");
	$pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
	$privKey = openssl_pkey_get_private($pem);
	$signature = false;
	openssl_sign($dataString, $signature, $privKey, OPENSSL_ALGO_SHA256);
	return base64_encode($signature);
}

/**
 * verify sign
 * @param string $dataString 
 * @param string $signString 
 * @param string $publicKey 
 */
function rsa_verify($dataString, $signString, $publicKey)
{
	$pem = chunk_split($publicKey, 64, "\n");
	$pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
	$pubKey = openssl_pkey_get_public($pem);

	$signature = base64_decode($signString);
	$flg = openssl_verify($dataString, $signature, $pubKey, OPENSSL_ALGO_SHA256);
	return $flg;
}

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