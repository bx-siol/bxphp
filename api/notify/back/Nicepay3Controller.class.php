<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class Nicepay3Controller extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		echo 'notify';
	}

	public function _cpay()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$params['notifyUrl'] = urldecode($params['notifyUrl']);
		$params['returnUrl'] = urldecode($params['returnUrl']);

		$url = 'http://upi.nicepay.life/api/pay';
		$result =  $this->CurlPost($url, $params, 30);
		$json_str = json_encode($result, 256);
		echo $json_str;
	}
	public function _ccash()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$params['notifyUrl'] = urldecode($params['notifyUrl']);
		$url = 'http://upi.nicepay.life/api/pay/repay';
		$result =  $this->CurlPost($url, $params, 30);
		$json_str = json_encode($result, 256);
		echo $json_str;
	}

	public function _pay()
	{
		$time = date("Y-m-d", time());
		$logpathd = LOGS_PATH . 'nicepay3/notify/' . "pay" . $time . ".txt";
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$sign = dSign($params);
		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if (!$sign) {
			file_put_contents($logpathd, NOW_DATE . "\r\n" .  'singree' . "\r\n", FILE_APPEND);
			ReturnToJson(-1, 'Sign error');
		}
		$pdata = [
			'code' => $params['status'] == '1' ? 1 : -1,
			'osn' => $params['orderId'],
			'amount' =>  $params['orderAmt'],
			'successStr' => 'success'
		];
		$this->payAct($pdata);
	}

	public function _cash()
	{
		$time = date("Y-m-d", time());
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$logpathd = LOGS_PATH . 'nicepay3/notify/' . "cash" . $time . ".txt";
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = 'dSign';
		if (!function_exists($signFunc)) {
			ReturnToJson(-1, 'Sign func no exist');
		}

		$sign = $signFunc($params);
		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}

		$pdata = [
			'osn' => $params['orderId'],
			'out_osn' => $params['orderId'],
			'pay_status' => $params['status'] == '1' ? 9 : 3,
			'pay_msg' => $params['msg'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
// MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA002S4YXQ79pYlfhKFiZWXCjYpaxDw8W0er2oi37t9jBmHuv7I5gAxMzTm/XtzhBzzSqHHbxSNq8q1QDZKTSBr13kCwE+kRIm1P51uoG6AmbjV2TmJPC1j84AxAn5ailL9I/Djba0UlZftLyqnw9rJyubQzVK8n9zn6LU/iIGmkC6XiG4G960Tab/0DV3/NsivXd3f6nEh5zAFv7857iZb4X6vL4kW458P3Ze5HCC8oxUyjd2rXOK4ku84zu3roxbt+Q1jzWQIx+FdutjwP4POSecBFJBXYn35L6z5we+vQ6elbw889O+j30YjvFeq92LR+8v1x4470nusWg3o/PdmwIDAQAB



// pt:
// MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0+JGzjRdFIyVQj1xIyetE/n5YBjRXTIX5yVOE9MVObdYjE6Mow0yHVr24i81c4w3cpLRbPRaHcEIZud0qTnGh1tBU0qo7MqZh0FEb0sgoPpoHVTULevQmVRgA/iPI7u2KuXQ85AwiiiQzXaiCACAA1Bz/MhtIYYq5YY0wuSPUTYZ+9z+hsLnKbUxTBibIs0gSKfggCGDMzlLQ24t6y8L6Uvw8/+dasFjTdCxlvn8XTcCU9hM19brzenZCU/EuihiamL6FREV6UjMebH3ZeQoapfD2XexDLmeIb0PKS+1QSWoKxlm+PXULru84W0Jd8PV5nH9wgcuTtEPu4xrgpip7wIDAQAB

// -----BEGIN PRIVATE KEY-----
//MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDTTZLhhdDv2liV+EoWJlZcKNilrEPDxbR6vaiLfu32MGYe6/sjmADEzNOb9e3OEHPNKocdvFI2ryrVANkpNIGvXeQLAT6REibU/nW6gboCZuNXZOYk8LWPzgDECflqKUv0j8ONtrRSVl+0vKqfD2snK5tDNUryf3OfotT+IgaaQLpeIbgb3rRNpv/QNXf82yK9d3d/qcSHnMAW/vznuJlvhfq8viRbjnw/dl7kcILyjFTKN3atc4riS7zjO7eujFu35DWPNZAjH4V262PA/g85J5wEUkFdiffkvrPnB769Dp6VvDzz076PfRiO8V6r3YtH7y/XHjjvSe6xaDej892bAgMBAAECggEAesUxuDMF6LZWjhxK/3+a4cUhy3DBlrgCWuZjTVmcbVRFoWW+7zlcCPxxXsaPOxE4F1bEVrSamAdCvavWgShuyTOmUfaRIb0ILu0B/jFtoAOjx51qUsBA3aL1svGQpuwDo778AhTLxKNGzD5qbCyLN6EQfwYx25/N0EzNsDKY33n6BgEBoybPv6XJwlkv88AusEL5cBFnG7HW48V9O+kmpQRoS0ktSnqrwNy1i3ImLH1Zy9m8Hxv/14CcVwUz7OgH9iOoc7BW3WNUUWXzxfJpmM5BFMUPwymFo3qbf6kfoN2bcSAI5W0hJWlBJyqyswgjA1jRFFdwtJUcqL7/2UqvMQKBgQDrBTHh39vcrjXvblNLolisHdUzczBIRpc/KXqc1N2lpMsCaocvG3Ct8dFi4n//B7Va+oVfZ/+wB7L+Ll6SJ9UUvmSo7kCH8FQPzy1T4cTeNaHQ3Nxt+b6Vp0CFK/5xbCrDazQXG/8//5vX9PSdAkzk0seVcLclrywWtIUuqVGxlwKBgQDmKmEtIqmG/zwTs98QsEtz1lW0sWl6A7pWCZOW6St6KFJL/4nLoxtUiegeMy5erkuUDoTYUxKXzTb6wlp1LhtFGr1RESVd8ih7SMVa+BINj8sOG9eFe1EE3SZp0wiNxs3NbsmkwDfzgy8g1sqhYVBIlJ6jcQITGjaaYPtXcswsnQKBgHY8f9L+N3N3paTWYUt82pWE0Lz4p4LFGNRq/semA/iQxp1pkKcva+nm7YuNHg3lB+VEghL0lFswFGnyVBu9tGKiQqwGaSq6yt/FQC2iONP+MXGNm8wsSCisIzacYn8XhxAXf/ZcXHcLFpF4KBRRkvPP4x8YvNtVnJ2zpglKn9HzAoGALoJjzpYRgajzv9t5+MpXBNpLyU6MTM6SCa63tyf41B9vudGyS4bzD9sqH2giN5mqxglFCN2IhUo/kN5THxipBAbKOKZpUZmMWpUy5BX6t+jVlE1F+MAZeA0kJQAy20tP7PI1Juh8peVdUZp1qbSbX39AqzA7xhZm8D0mrN4SqfUCgYBMpMia4Aj+EiE0QDvY+OV+9qgutCLsh9nEjdd6wen32g2amgxU3xrtlFvDsxy+XGYgeag2HWNVLn71bcSRB1wcoQkMhOZXGwzGWPvFMS+xtrUcrIDpARmL27CgbnaQDjnwJHJogbl5dWMZxZyaNoGC9/7BhTWww026VHgcZyjnfw==
// -----END PRIVATE KEY-----



// HPplJSycRKIjUOxWwfvFidXNaYoDEMGq