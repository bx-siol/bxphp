<?php
!defined('ROOT_PATH') && exit;
use think\facade\Db;

class BaseController extends CommonCtl{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _changeTableVal(){
		checkPower();
		$params=$this->params;
		$table=$params['table']; // 表名
		$id_name = $params['id_name']; // 表主键id名
		$id_value = $params['id_value']; // 表主键id值
		$field=$params['field']; // 修改哪个字段
		$value=$params['value']; // 修改字段值
		try{
			Db::table($table)->where([$id_name=>$id_value])->save([$field=>$value]); // 根据条件保存修改的数据
		}catch(\Exception $e){
			ReturnToJson(-1,'系统繁忙请稍后再试');
		}
		ReturnToJson(1,'操作成功');
	}
	
}
?>