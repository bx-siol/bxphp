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
	
}
?>