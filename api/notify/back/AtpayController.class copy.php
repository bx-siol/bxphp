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




	public function _cpay()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$params['notifyUrl'] = urldecode($params['notifyUrl']);

		$url = 'http://43.205.60.80/ctp_hz/view/server/aotori/addTrans.php';
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

		$url = 'http://43.205.60.80/ctp_hz/view/server/aotori/propayTrans.php';
		$result =  $this->CurlPost($url, $params, 30);
		$json_str = json_encode($result, 256);
		echo $json_str;
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

		$sign = paySign($params);
		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), FILE_APPEND);
		if (!$sign) {
			file_put_contents($logpathd, NOW_DATE . "\r\n" .  'singree' . "\r\n", FILE_APPEND);
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['respCode'] == '0000' ? 1 : -1,
			'osn' => $params['merReqNo'],
			'amount' =>  $params['amt'] / 100,
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
		$logpathd = LOGS_PATH . 'atpay/notify/' . "cash" . $time . ".txt";
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = 'atpayCashSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}

		$sign = $signFunc($params);
		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), FILE_APPEND);
		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'osn' => $params['merReqNo'],
			'out_osn' => $params['serverRspNo'],
			'pay_status' => $params['respCode'] == '0000' ? 9 : 3,
			'pay_msg' => $params['respDesc'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
 