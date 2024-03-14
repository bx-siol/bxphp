<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class YupayController extends BaseController
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
		$time = date("Y-m-d", time());
		$logpathd = LOGS_PATH . "yupay/notify/pay{$time}.txt";

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$sign = paySign($params);
		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . json_encode($params) . "\r\n\r\n", FILE_APPEND);

		if ($params['sign'] != $sign) {
			file_put_contents($logpathd, NOW_DATE . "\r\n" .  'singree' . "\r\n\r\n", FILE_APPEND);
			ReturnToJson(-1, 'Sign error');
		}
		$pdata = [
			'code' => $params['status'] == 'success' ? 1 : -1,
			'osn' => $params['merchantOrderNo'],
			'amount' =>  $params['amount'],
			'successStr' => 'SUCCESS'
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
		$time = date("Y-m-d", time());
		$logpathd = LOGS_PATH . "yupay/notify/cash{$time}.txt";
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';


		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			ReturnToJson(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);

		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . json_encode($params) . "\r\n\r\n", FILE_APPEND);

		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}

		//$order=Db::table('fin_cashlog')->where("osn='{$params['merchantCode']}'")->find();

		$pdata = [
			'osn' => $params['merchantOrderNo'],
			'out_osn' => $params['merchantOrderNo'],
			'pay_status' => $params['status'] == 'success' ? 9 : 3,
			'pay_msg' => $params['type'],
			//'amount' => $params['order_amount'],
			'successStr' => 'SUCCESS',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
