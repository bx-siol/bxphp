<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class QeapayController extends BaseController
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
		$logpathd = LOGS_PATH . 'qeapay/notify/pay' . date("Y-m-d", time()) . '.txt';
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}


		$sign = paySign($params);
		//file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			file_put_contents($logpathd, NOW_DATE . "\r\n" .  'singree' . "\r\n", FILE_APPEND);
			ReturnToJson(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['tradeResult'] == '1' ? 1 : -1,
			'osn' => $params['mchOrderNo'],
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
		$logpathd = LOGS_PATH . 'qeapay/notify/cash' . date("Y-m-d", time()) . '.txt';
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			ReturnToJson(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}
		$pdata = [
			'osn' => $params['merTransferId'],
			'out_osn' => $params['tradeNo'],
			'pay_status' => $params['tradeResult'] == '1' ? 9 : 3,
			'pay_msg' => $params['respCode'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}


// "tradeResult":"1","oriAmount":"100.00","amount":"100.00","mchId":"400002002","orderNo":"7244543015469982","mchOrderNo":"ef77ffbfeade9e98","sign":"fe7b5f73958341f6003226d82875f5db","signType":"MD5","orderDate":"2022-10-16 02:11:59"