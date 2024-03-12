<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class AtpayController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		echo 'notify';
	}

	public function _pay()
	{
		$time = date("Y-m-d", time());
		$logpathd = LOGS_PATH . 'atpay/notify/' . "pay" . $time . ".txt";
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$pdata1 = [
			'X-Qu-Access-Key' => $_SERVER['HTTP_X_QU_ACCESS_KEY'],
			'X-Qu-Mid' => $_SERVER['HTTP_X_QU_MID'],
			"X-Qu-Nonce" => $_SERVER['HTTP_X_QU_NONCE'],
			'X-Qu-Signature-Method' => $_SERVER['HTTP_X_QU_SIGNATURE_METHOD'],
			"X-Qu-Timestamp" => $_SERVER['HTTP_X_QU_TIMESTAMP'],
			"X-Qu-Signature-Version" => $_SERVER['HTTP_X_QU_SIGNATURE_VERSION'],
			"X-Qu-Signature" => $_SERVER['HTTP_X_QU_SIGNATURE']
		];
		$sign = paySign($pdata1);
		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . $pdata1['X-Qu-Signature'] . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES) . "\r\n", FILE_APPEND);
		//. json_encode($_SERVER, JSON_UNESCAPED_SLASHES)
		if ($sign != $pdata1['X-Qu-Signature']) {
			//file_put_contents($logpathd, NOW_DATE . "\r\n" . 'singree' . "\r\n", FILE_APPEND);
			jReturn(-1, 'Sign error');
		}
		if ($params['resource']['tradeStatus'] == 'PROCESSING') {
			echo 'OK';
			exit;
		}
		$pdata = [
			'code' => $params['resource']['tradeStatus'] == 'SUCCESS' ? 1 : -1,
			'osn' => $params['resource']['outTradeNo'],
			'amount' => $params['resource']['tradeAmount'],
			'successStr' => 'OK'
		];
		$this->payAct($pdata);
	}


	public function _query()
	{
		$time = date("Y-m-d", time());
		$logpathd = LOGS_PATH . 'atpay/notify/' . "query" . $time . ".txt";
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';
		$params = $_GET;
		$pdata = query($params['orderid']);
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
		$logpathd = LOGS_PATH . 'atpay/notify/' . "cash" . $time . ".txt";
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = 'atpayCashSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}
		$pdata1 = [
			'X-Qu-Access-Key' => $_SERVER['HTTP_X_QU_ACCESS_KEY'],
			'X-Qu-Mid' => $_SERVER['HTTP_X_QU_MID'],
			"X-Qu-Nonce" => $_SERVER['HTTP_X_QU_NONCE'],
			'X-Qu-Signature-Method' => $_SERVER['HTTP_X_QU_SIGNATURE_METHOD'],
			"X-Qu-Timestamp" => $_SERVER['HTTP_X_QU_TIMESTAMP'],
			"X-Qu-Signature-Version" => $_SERVER['HTTP_X_QU_SIGNATURE_VERSION'],
			"X-Qu-Signature" => $_SERVER['HTTP_X_QU_SIGNATURE']
		];
		$sign = $signFunc($pdata1);
		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES), FILE_APPEND);
		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}

		if ($params['resource']['payStatus'] == 'PROCESSING') {
			echo 'OK';
			exit;
		}

		$pdata = [
			'osn' => $params['resource']['outTradeNo'],
			'out_osn' => $params['resource']['tradeNo'],
			'pay_status' => $params['resource']['payStatus'] == 'SUCCESS' ? 9 : 3,
			'pay_msg' => $params['errMsg'],
			'successStr' => 'OK',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}