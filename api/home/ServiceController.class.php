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
		$where = " 1=1 ";
		try {
			$pageuser = checkLogin();
			$params = $this->params;
			$where = " 1=1 ";
			if ($params["type"] != 0)
				$where .= " and type={$params['type']} ";

			$where .= " and (";
			if ($pageuser['pidg1'])
				$where .= " uid={$pageuser['pidg1']} ";
			if ($pageuser['pidg2'])
				$where .= " or uid={$pageuser['pidg2']}";
			$where .= " ) ";
			$data = Db::table('ext_service')->where($where)
				->field(['account', 'name', 'type', 'qrcode', 'remark'])->select()->toArray();

			if ($data == null || !$data) {
				$data = Db::table('ext_service')->where("uid=1 and type={$params['type']}")
					->field(['account', 'name', 'type', 'qrcode', 'remark'])->select()->toArray();
			}
			$list = ['list' => $data];
			ReturnToJson(1, 'ok', $list);
		} catch (Exception $e) {
			ReturnToJson(0, $e->getMessage(), ['e' => $where]);
		}
	}
}
