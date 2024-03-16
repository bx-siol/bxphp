<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class ServiceController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _online()
	{
		$pageuser = checkLogin();
		$service = getPset('service');
		$service_arr = [];
		if ($service['show']) {
			foreach ($service['show'] as $sv) {
				if ($sv == 'show') {
					continue;
				}
				$service_arr[$sv] = $service[$sv];
			}
		}
		$return_data = [
			'service_arr' => $service_arr
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _GetService_Online()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$where = " 1=1 ";
		if ($params["type"] != 0)
			$where .= " and type={$params['type']} ";
		$data = Db::table('ext_service')->where(" {$where} and (uid={$pageuser['pidg1']} or uid={$pageuser['pidg2']} ) ")
			->field(['account', 'name', 'type', 'qrcode', 'remark'])->find();
		if ($data == null || !$data) {
			$data = Db::table('ext_service')->where(" {$where} and uid=1 ")
				->field(['account', 'name', 'type', 'qrcode', 'remark'])->find();
		}
		$list = ['list' => $data];
		ReturnToJson(1, 'ok', $list);
	}
}