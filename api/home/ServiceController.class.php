<?php
!defined('ROOT_PATH') && exit;
use think\facade\Db;

class ServiceController extends BaseController{
	
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
		jReturn(1,'ok',$return_data);
	}
	
	public function _GetService_Online()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$user = Db::table('sys_user')->where("id={$pageuser['id']} ")->find();
		writeLog("user" .$user,"bobopay1");
		$data = Db::table('ext_service')
		->where(" type={$params['type']} and uid={$user['pidg1']} ")
		->whereOr("uid","{$user['pidg2']}")
		->select();		
		jReturn(1,'ok',$data);
	}
}
?>