<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class DidapayController extends BaseController
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
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$flpath = LOGS_PATH . 'didapay/notify/pay' . date("Y-m-d", time()) . '.txt';

		$sign = dSign($params);
		//file_put_contents($flpath, NOW_DATE . "\r\n" . $sign . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)  . "\r\n\r\n", FILE_APPEND);
		if (!$sign) {
			ReturnToJson(-1, 'Sign error');
		}

		$pdata = [
			'code' => ($params['orderStatus'] == 'ARRIVED' || $params['orderStatus'] == 'SUCCESS' || $params['orderStatus'] == 'CLEARED') ? 1 : -1,
			'osn' => $params['merchantOrderNo'],
			'amount' =>  $params['factAmount'],
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
		$log_file = LOGS_PATH . 'didapay/notify/cash' . date("Y-m-d", time()) . '.txt';
		file_put_contents($log_file, NOW_DATE . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)   . "\r\n\r\n", FILE_APPEND);

		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';

		$signFunc = 'dSign';
		if (!function_exists($signFunc)) {
			ReturnToJson(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		if (!$sign) {
			ReturnToJson(-1, 'Sign error');
		}
		//$order=Db::table('fin_cashlog')->where("osn='{$params['merchantCode']}'")->find(); 
		$pdata = [
			'osn' => $params['merchantOrderNo'],
			'out_osn' => $params['platOrderNo'],
			'pay_status' => $params['orderStatus'] == 'SUCCESS' ? 9 : 3,
			'pay_msg' => $params['orderMessage'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
