<?php

use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['didapay'] = [
	'mch_id' => 'E3013',

 
	'mch_key' =>  'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAPzrWRRu2J3l4lPRts8nfqUu8uKCHrCD3fqt26qPhCkB2smzGKpGXD1Imn/Kns6hcTr+gZr6LhH2/VnvmAl3/mp6gfpILBJLJ/48DbA4y5FAwLB/4/H3dLM+iytOoFNAaCdLh99kg8xoico4xd9MMDarHawwBHKEphMITyv+z+UvAgMBAAECgYEAlR5ttw5jyTTw7FqJXjn7AYlcyw/M6GXXSyWWjklvsn3VcXaW5E33tGpKLW5Zk5q8F/xWjflTkGP/nDcXfP2ykgj6+cPQFYS3DlcJVnCvu7C4mp/kOnTuhSgf3QgTmeLsuNF/qVdfDL0KEi6jO8yWOukQm6171eHJPhkV0V84zQECQQD/RdFSMDYc4/0P7CISfRZKLEz4NGuyek3wI8VQ1a8CexGXCXJgMgfVAqfoeeo4M1iQN1e2akjcMD7QdfSzCpM3AkEA/aPQWXTa4Hs2ZiukczmD+HWSiiGfVOzdyfiyJ977OmJag3a4w2OnxOF0aL9uxaMjb7uUMjYts7PXmElMkcWpyQJAfnlC6iuEw++ZM8hUYUCkSH+Gavrd2QGLl8zBN/mGyf3biy9dAZgIFVtJgX7Vsp6N5HXm+TPTgXRNys6GPCajJQJAVywB43zh7Nzr6Vl+f4t469+cqZS8qfdukofC0ykztvEuopgfECgj9Op3k7iXXZ2gBDq3yDoOowBgTJqEkmUY4QJBAJDF5hq7a+kP8C8rGWYRmwcbk2CmXejia6ms8/nJ+86clSfxzzXYugEi1CHQPFuGXZ8HOQQB1NC4VO6g9W4pbwA=', 


	'p_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCmJMCX6W68dJmZ5AhGoj9DHU/x5ILiQI93ewiLDdFgOdZXPMmNP9Xon6S2Xl2Ki5xbnml2NmKrK3DrJ93XOLCJWejyAu/q2L2QUTyGMhP+7dbhKe5bLdrqnQ/s1fBSXjnTosH46fxz2yMNEk5xwfrUHnk4D9qcM25qxhkXY5+skQIDAQAB',
	 
	'pay_url' => 'https://api.didapay.in/api/payin',
	'query_url' => '',
	'notify_url' => 'http://'.PAY_BACKURL . '/api/Notify/didapay/pay',
	'page_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
];



/*



-----BEGIN PRIVATE KEY-----

MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAPzrWRRu2J3l4lPRts8nfqUu8uKCHrCD3fqt26qPhCkB2smzGKpGXD1Imn/Kns6hcTr+gZr6LhH2/VnvmAl3/mp6gfpILBJLJ/48DbA4y5FAwLB/4/H3dLM+iytOoFNAaCdLh99kg8xoico4xd9MMDarHawwBHKEphMITyv+z+UvAgMBAAECgYEAlR5ttw5jyTTw7FqJXjn7AYlcyw/M6GXXSyWWjklvsn3VcXaW5E33tGpKLW5Zk5q8F/xWjflTkGP/nDcXfP2ykgj6+cPQFYS3DlcJVnCvu7C4mp/kOnTuhSgf3QgTmeLsuNF/qVdfDL0KEi6jO8yWOukQm6171eHJPhkV0V84zQECQQD/RdFSMDYc4/0P7CISfRZKLEz4NGuyek3wI8VQ1a8CexGXCXJgMgfVAqfoeeo4M1iQN1e2akjcMD7QdfSzCpM3AkEA/aPQWXTa4Hs2ZiukczmD+HWSiiGfVOzdyfiyJ977OmJag3a4w2OnxOF0aL9uxaMjb7uUMjYts7PXmElMkcWpyQJAfnlC6iuEw++ZM8hUYUCkSH+Gavrd2QGLl8zBN/mGyf3biy9dAZgIFVtJgX7Vsp6N5HXm+TPTgXRNys6GPCajJQJAVywB43zh7Nzr6Vl+f4t469+cqZS8qfdukofC0ykztvEuopgfECgj9Op3k7iXXZ2gBDq3yDoOowBgTJqEkmUY4QJBAJDF5hq7a+kP8C8rGWYRmwcbk2CmXejia6ms8/nJ+86clSfxzzXYugEi1CHQPFuGXZ8HOQQB1NC4VO6g9W4pbwA=

-----END PRIVATE KEY-----


-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQD861kUbtid5eJT0bbPJ36lLvLi
gh6wg936rduqj4QpAdrJsxiqRlw9SJp/yp7OoXE6/oGa+i4R9v1Z75gJd/5qeoH6
SCwSSyf+PA2wOMuRQMCwf+Px93SzPosrTqBTQGgnS4ffZIPMaInKOMXfTDA2qx2s
MARyhKYTCE8r/s/lLwIDAQAB
-----END PUBLIC KEY-----




*/


function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG']['didapay'];
	$name = getRsn();
	$rand_arr = [6, 7, 8, 9];
	$phone = $rand_arr[mt_rand(0, count($rand_arr) - 1)] . mt_rand(1000, 9999) . mt_rand(10000, 99999);
	$time = date("Y-m-d H:i:s", time());
	$fliepath =	LOGS_PATH . 'didapay/pay/' . date("Y-m-d", time()) . '.txt';

	/*
	merchantNo 是string支付系统提供给合作商户的唯一标识
	method 是String代收方式 ，目前固定值：UPI
	merchantOrderNo 是string商户唯一订单
	payAmount 是BigDecimal支付金额（单位是元，可以有2位小数点）
	
	mobile 是string付款人手机号，越真实，成功率越高，触发风控系数越低
	name 是string付款人姓名，越真实，成功率越高，触发风控系数越低
	email 是string付款人邮箱，越真实，成功率越高，触发风控系数越低后缀使用gmail.com，如：abc@gmail.com
	
	notifyUrl 是string回调地址
	returnUrl 否string收银台页面跳转地址， 去掉转义字符“\”,参考格式 https://www.abc.com/如果不传，跳转系统默认成功页
	description 是string描述
	sign 是string签名


	*/
	$pdata = [
		'merchantNo' => $config['mch_id'],
		'merchantOrderNo' => $fin_paylog['osn'],
		'payAmount' => strval($fin_paylog['money']),

		'description' => 'User payment amount:' . strval($fin_paylog['money']),
		'name' => $name,
		'mobile' => $phone,
		'email' => $phone . '@gmail.com',

		'returnUrl' => strval($config['page_url']),
		'method' =>  strval($sub_type),
		'notifyUrl' => $config['notify_url']
	];
	$pdata['sign'] = paySign($pdata);
	$result = payCurlPost($config['pay_url'], $pdata, 30);
	file_put_contents($fliepath, json_encode($pdata, JSON_UNESCAPED_SLASHES) . "\r\n" . json_encode($result, JSON_UNESCAPED_SLASHES) . "\r\n\r\n", FILE_APPEND);

	if ($result['code'] != 1) {
		file_put_contents($fliepath,  $result['code']  . "\r\n\r\n", FILE_APPEND);
		return $result;
	}
	$resultArr = $result['output'];

	if ($resultArr['status'] != '200') {
		return ['code' => -1, 'msg' => $resultArr['message']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $result['tradeMsg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['platOrderNo'],
			'pay_url' => $resultArr['data']['paymentInfo']
		]
	];
	return $return_data;
}


function paySign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['didapay'];
	$fliepath =	LOGS_PATH . 'didapay/signpay' . date("Y-m-d", time()) . '.txt';

	ksort($pdata);
	$request_data = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign' || $pk == 'sign_type' || $pk == 'signType') {
			continue;
		}
		$request_data .= "{$pv}";
	}

	//解析私钥
	$res = openssl_pkey_get_private("-----BEGIN RSA PRIVATE KEY-----\n" . $config['mch_key'] . "\n-----END RSA PRIVATE KEY-----");

	$content = '';
	//使用私钥加密
	foreach (str_split($request_data, 117) as $str1) {
		openssl_private_encrypt($str1, $crypted, $res);
		$content .= $crypted;
	}
	$encrypted = base64_encode($content);
	file_put_contents($fliepath, "\r\n" . $config['mch_key'] . "\r\n\r\n" . $request_data . "\r\n\r\n" . json_encode($pdata)   . "\r\n" . $encrypted . "\r\n\r\n", FILE_APPEND);
	//编码转换
	return $encrypted;
}

function dSign($pdata)
{
	$config = $_ENV['PAY_CONFIG']['didapay'];
	$fliepath =	LOGS_PATH . 'didapay/dsignpay' . date("Y-m-d", time()) . '.txt';

	ksort($pdata);
	$request_data = '';
	foreach ($pdata as $pk => $pv) {
		if ($pk == 'sign') {
			continue;
		}
		$request_data .= "{$pv}";
	}

	// $encrypted =  $pdata['sign'];
	// $encrypted = str_replace('-', '+', $encrypted);
	// $encrypted = str_replace('_', '/', $encrypted);
	// //解析公钥
	// $res = openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" . $config['p_key'] . "\n-----END PUBLIC KEY-----");

	// openssl_public_decrypt(base64_decode($encrypted), $decrypted, $res);



	$encrypted = $pdata['sign'];
	$encrypted = str_replace('-', '+', $encrypted);
	$encrypted = str_replace('_', '/', $encrypted);
	//解析公钥
	$res = openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" . $config['p_key'] . "\n-----END PUBLIC KEY-----");

	openssl_public_decrypt(base64_decode($encrypted), $decrypted, $res);

	//var_dump($decrypted);
	file_put_contents($fliepath, "\r\n" . json_encode($pdata) . "\r\n" . $decrypted . "\r\n" . $pdata['sign']  . "\r\n" . $config['p_key'] . "\r\n\r\n", FILE_APPEND);
	//编码转换
	return $request_data == $decrypted;
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
		CURLOPT_HTTPHEADER => array('Content-Type: application/json;charset=UTF-8')
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
