<?php
!defined('ROOT_PATH') && exit;
use think\facade\Db;

class BaseController extends CommonCtl{
	
	public function __construct(){
		parent::__construct();
		//jReturn(-1,'System maintenance');
	}
	
	//获取用户的收款方式
	public function _getMyBanklog(){
		$pageuser=checkLogin();
		$types=$this->params['types'];
		$type=$this->params['type'];
		if($type){
			$types=$type;
		}
		$list=getBanklog($pageuser['id'],$types);
		$return_data=[
			'list'=>$list
		];
		jReturn(1,'ok',$return_data);
	}
	
	public function _getPc(){
		$id=intval($this->params['id']);
		$list=Db::table('cnf_pc')->where("pid={$id}")->limit(100)->select()->toArray();
		if(!$list){
			$list=[];
		}
		$return_data=[
			'list'=>$list
		];
		jReturn(1,'ok',$return_data);
	}
	
}
?>