<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class AutoPayController extends BaseController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		echo 'notify';
	}

	public function _payAuto()
	{
		$token = $_POST["token"] ?? $_GET["token"];
		$user = getUserByToken($token);
		if (!$user || !is_array($user))
			ReturnToJson(-1, 'fail');
		if ($user['gid'] > 81)
			ReturnToJson(-1, 'fail');
		$osn = $_POST["osn"] ?? $_GET["osn"];
		$order = Db::table('fin_paylog')->where("osn='{$osn}'")->find();
		$pdata = [
			'code' => 1,
			'osn' => $order['osn'],
			'amount' => $order['money'],
			'successStr' => 'success'
		];
		$this->payAct($pdata);
	}
}
