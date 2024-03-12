<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class HipayController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	// www.bhimapi.vip   
	public function _cpay()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$url = 'https://www.bhimapi.vip/api/order/createorder';
		$result =  $this->CurlPost($url, $params, 30);
		$json_str = json_encode($result, 256);
		$time = date("Y-m-d H:i:s", time());
	    $fliepath =	LOGS_PATH . 'hipay/pay/' . date("Y-m-d", time()) . '.txt';
    	file_put_contents($fliepath, json_encode($params, JSON_UNESCAPED_SLASHES) . "\r\n" . $json_str . "\r\n\r\n", FILE_APPEND);

		echo $json_str;
	}
	public function _ccash()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$url = 'https://www.bhimapi.vip/api/payout/create';
		$result =  $this->CurlPost($url, $params, 30);
		$json_str = json_encode($result, 256);
		echo $json_str;
	}

	public function _index()
	{
		echo 'notify';
	}

	public function _pay()
	{
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$flpath = LOGS_PATH . 'hipay/notify/pay' . date("Y-m-d", time()) . '.txt';

		$sign = dSign($params);
		file_put_contents($flpath, NOW_DATE . "\r\n" . $sign . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES)  . "\r\n\r\n", FILE_APPEND);
		if (!$sign) {
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => ($params['status'] == '2') ? 1 : -1,
			'osn' => $params['orderId'],
			'amount' =>  $params['amount'],
			'successStr' => 'success'
		];

		$this->payAct($pdata);
	}

	public function _cash()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$log_file = LOGS_PATH . 'hipay/notify/cash' . date("Y-m-d", time()) . '.txt';
		file_put_contents($log_file, NOW_DATE . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES)   . "\r\n\r\n", FILE_APPEND);

		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';

		$signFunc = 'dSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		if (!$sign) {
			jReturn(-1, 'Sign error');
		}
		//$order=Db::table('fin_cashlog')->where("osn='{$params['merchantCode']}'")->find(); 
		$pdata = [
			'osn' => $params['orderId'],
			'out_osn' => $params['payoutNo'],
			'pay_status' => $params['status'] == '2' ? 9 : 3,
			'pay_msg' => $params['orderMessage'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
