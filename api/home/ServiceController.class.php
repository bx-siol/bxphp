<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class ServiceController extends BaseController
{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _online(){
		$pageuser=checkLogin();
		$service=getPset('service');
		$service_arr=[];
		if($service['show']){
			foreach($service['show'] as $sv){
				if($sv=='show'){
					continue;
				}
				$service_arr[$sv]=$service[$sv];
			}
		}
		$return_data=[
			'service_arr'=>$service_arr
		];
		ReturnToJson(1,'ok',$return_data);
	}
	
	public function _GetService_Online()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$where = "";
		if($params["type"] != 0)
			$where .= " type={$params['type']} ";

		$user = Db::table('sys_user')->where("id={$pageuser['id']} ")->find();
		$data = Db::query(" select * from ext_service where  {$where} and (uid={$user['pidg1']} or uid={$user['pidg2']} ) ");		
		ReturnToJson(1,'ok',$data);
	}
}