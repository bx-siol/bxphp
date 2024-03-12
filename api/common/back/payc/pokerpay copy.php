<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['pokerpay'] = [
	'mch_id' => 'PG-1085',

	'p_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCbO0wQ+g643h7yIONIe4YVbpIOJjxvVIRorE5xAmU+F6pevUTz3iZwaz0BaRHgSlZMjeTUJCfIwhfJqo/t9jUPMr+05NOJX924pc50+Ap9XuahTAvsvguDPB97Bo3RtmKsnuUNi1DJOkfjhxb7HZ/Aw5VwoKmLnap0TDlQrFLMHwIDAQAB',
	
	'mch_key' =>  'MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBAL5br2kcR6TcAGmU0Ij+PHkTiksn0s2PBGaLslW0mof09tl5iMFVJkF3UTAG7wW9MHeFH1Ayu0EGbkpsXir9yd5R4zyQ9L0uvpGxSfO3CdpGVUaxTizYTHrbdCJMWet3lPm2UOJ1woo3pPqZHnhU+aaJJzzSW2F4TQ5aj70f4WmDAgMBAAECgYEAiHmfVxKJYu6/9PJWWAE+Ref2fE9+2RUyKHr7Tmr/Z33/BIgXvdRYaxMqR+6Qq2KqPuZYPt1AVyxPIlhzYws1EEuEkSHor//MJvNbK2pPeH9nRu06bxcnR1Fo4SSq8lTO+h8SubWaYCTr+0NtHMoOSzwGYa9fASm8WNnuK9ASQmECQQDxXzSv/QIrM8Nr9mzXqUwZ+HD5h/t4Dgcol+jtdYRdY9B2HaprzvElrt1tr5YSa2ntHTJrJf91y3L/aYN+aoH7AkEAyeUFLHvjX4hfJbPy8IjvStISh89HdIr0zQtZmya/kHszQ8UGiyTwt6tWsoqSUzuZS+fha+EoeGbwZMbNfSqoGQJAYq6T8ee0/Ui6eudS9JEIxg1m0v4fd6P0lUoWNw82wJ/QWJokVNNUkB1/9ho1du5nbkPjmx775IL7TyUqV4LgBQJBAISgeiGa0Ob1AuwVpkX07p1MGvg0ZlBc6Cu6hQazEaysAiVGzOGjRq6hU7a96RncUPvYO/FOW/OcS9cn8d2DpCECQQDDx7X/cXnkw15bHMXppxBrYrIS0jklq9NasmyUC2t8kgxuZDKAHUyF2IN2LsJO7QjL0e4XXyWDr2ikWkQ2DxzO',
	
	'pay_url' => 'https://api.pokerpay.in/api/pkv2/repayment/create',

	'query_url' => '',
	'notify_url' => 'http://www.gamedreamer.in' . '/api/Notify/pokerpay/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];

function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['pokerpay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d H:i:s", time());
	$fliepath =	LOGS_PATH . 'pokerpay/pay/' . date("Y-m-d", time()) . '.txt';

	$pdata = [
		"amount" => strval($fin_paylog['money']),
		"callbackUrl" => $config['notify_url'],
		"des" => "tdes",
		"email" => $phone . "@gamil.com",
		"merNo" => $config['mch_id'],
		"method" => "UPI",
		"mobile" => $phone,
		"name" => $name,
		"orderNo" => $fin_paylog['osn'],
	];
	$pdata['sign'] = paySign($pdata);
	$result = payCurlPost($config['pay_url'], $pdata, 30);
	file_put_contents($fliepath, json_encode($pdata, JSON_UNESCAPED_SLASHES) . "\r\n" . json_encode($result, JSON_UNESCAPED_SLASHES) . "\r\n\r\n", FILE_APPEND);

	if ($result['code'] != 1) {
		file_put_contents($fliepath,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];

	if ($resultArr['code'] != '200') {
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['pkOrderNo'],
			'pay_url' => $resultArr['data']['paymentLink']
		]
	];
	return $return_data;
}


function paySign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['pokerpay'];
	$encrypted = getSignGenerator($pdata, $config['mch_key']);
	//编码转换
	return $encrypted;
}

/**
 * 生成待签名的字符串
 * @param $data 参与签名的参数数组
 * @return string 待签名的字符串
 */
function getSignStr($data)
{
	//排序
	ksort($data);
	//剔除sign 如果对方的签名叫sign 或者可以在调用方法的时候剔除
	unset($data['sign']);
	$stringToBeSigned = '';
	$i = 0;
	foreach ($data as $k => $v) {
		$stringToBeSigned .= ($v);
	}
	return $stringToBeSigned;
}
/**
 * 生成签名
 * @param array $params 待签名的所有参数
 * @param string $privateKey 待签名的所有参数
 * @return string 生成的签名
 */
function getSignGenerator($params, $private_key)
{
	//生成待验签的字符串
	$origin_request_data =  getSignStr($params);
	// $private_key = chunk_split($private_key, 64, "\n");
	$private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $private_key . "\n-----END RSA PRIVATE KEY-----";
	$res = openssl_pkey_get_private($private_key);

	$content = '';
	//使用私钥加密
	foreach (str_split($origin_request_data, 117) as $str1) {
		openssl_private_encrypt($str1, $crypted, $res);
		$content .= $crypted;
	}

	//编码转换
	$encrypted = base64_encode($content);



	// $pem = chunk_split($privateKey, 64, "\n");
	// $pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
	// $privKey = openssl_get_privatekey($pem); //openssl_get_privatekey    openssl_pkey_get_private
	// $signature = false;
	// openssl_sign($data, $signature, $privKey, OPENSSL_ALGO_SHA1);
	// $signature = base64_encode($signature);
	$fliepath =	LOGS_PATH . 'pokerpay/pay/signpay' . date("Y-m-d", time()) . '.txt';
	file_put_contents($fliepath,   "\r\n\r\n" . $origin_request_data . "\r\n\r\n" . json_encode($params)   . "\r\n" . $content . "\r\n pk: \r\n" . $private_key . "\r\n  {$private_key} \r\n", FILE_APPEND);
	return $encrypted;
}

/**
 * 验证签名
 * @param array $params 待签名的所有参数
 * @param string $sign 生成的签名
 * @return boolean 校验的结果
 */
function signCheck($params, $sign, $publicKey)
{
	//生成待验签的字符串
	$data = getSignStr($params);
	// //对方的公钥内容 一行的形式 
	// $pem = "-----BEGIN PUBLIC KEY-----\n" .
	// 	chunk_split($publicKey, 64, "\n") .
	// 	"-----END PUBLIC KEY-----\n";
	// $checkResult = (bool)openssl_verify($data, base64_decode($sign), $pem, OPENSSL_ALGO_SHA1);

	$sign = str_replace('-', '+', $sign);
	$sign = str_replace('_', '/', $sign);
	//解析公钥
	$res = openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" . $publicKey  . "\n-----END PUBLIC KEY-----");

	openssl_public_decrypt(base64_decode($sign), $checkResult, $res);

	$fliepath =	LOGS_PATH . 'pokerpay/pay/dsignpay' . date("Y-m-d", time()) . '.txt';
	file_put_contents($fliepath, json_encode($params)   . "\r\n" . $data . "\r\n pk: \r\n" . $checkResult . "\r\n  {$publicKey} \r\n", FILE_APPEND);
	return $checkResult == $data;
}
function dSign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['pokerpay'];
	$fliepath =	LOGS_PATH . 'pokerpay/pay/dsign' . date("Y-m-d", time()) . '.txt';
	//file_put_contents($fliepath,     "\r\n\r\n" . $config['p_key']    . "\r\n\r\n", FILE_APPEND);
	return signCheck($pdata, $pdata['sign'], $config['p_key']);
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
		// CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES),
		CURLOPT_HTTPHEADER => array('Content-Type: application/json; charset=utf-8')
	));

	$response = curl_exec($curl);

	//file_put_contents(LOGS_PATH . 'payOrderwow.txt', '=============>' . $response  . "\r\n\r\n", FILE_APPEND);
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
/*
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC+W69pHEek3ABplNCI/jx5E4pL
J9LNjwRmi7JVtJqH9PbZeYjBVSZBd1EwBu8FvTB3hR9QMrtBBm5KbF4q/cneUeM8
kPS9Lr6RsUnztwnaRlVGsU4s2Ex623QiTFnrd5T5tlDidcKKN6T6mR54VPmmiSc8
0ltheE0OWo+9H+FpgwIDAQAB
-----END PUBLIC KEY-----
*/