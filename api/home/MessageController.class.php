<?php
!defined('ROOT_PATH') && exit;
use think\facade\Db;

class MessageController extends BaseController{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _list(){
		$pageuser=checkLogin();
		$params=$this->params;
		$params['page']=intval($params['page']);
		
		$where='1=1';
		//$where.=empty($params['s_cid'])?'':" and log.cid={$params['s_cid']}";
		$where.=" and log.uid={$pageuser['id']}";
		$where.=" and log.status<99";
		$count_item=Db::table('msg_list log')
		//->leftJoin('news_category c','log.cid=c.id')
		->fieldRaw('count(1) as cnt')
		->where($where)
		->find();
		
		$list=Db::view(['msg_list'=>'log'],['*'])
		//->view(['news_category'=>'c'],['name'=>'cat_name'],'log.cid=c.id','LEFT')
		->where($where)
		->order(['log.id'=>'desc'])
		->page($params['page'],$this->pageSize)
		->select()
		->toArray();
		
		$yes_or_no=getConfig('yes_or_no');
		foreach($list as &$item){
			$item['create_time']=date('m-d H:i',$item['create_time']);
			unset($item['id']);
		}
		$total_page=ceil($count_item['cnt']/$this->pageSize);
		$return_data=[
			'list'=>$list,
			'count'=>intval($count_item['cnt']),
			'page'=>$params['page']+1,
			'finished'=>$params['page']>=$total_page?true:false,
			'limit'=>$this->pageSize
		];
		jReturn(1,'ok',$return_data);
	}
	
	//详情
	public function _info(){
		$pageuser=checkLogin();
		$where="log.msn='{$this->params['msn']}' and log.status<99";
		$item=Db::view(['msg_list'=>'log'],['*'])
		//->view(['news_category'=>'c'],['name'=>'cat_name'],'log.cid=c.id','LEFT')
		->where($where)->find();
		if(!$item){
			jReturn(-1,'不存在相应的记录');
		}
		$item['create_time']=date('m-d H:i',$item['create_time']);
		$item['content']=nl2br($item['content']);
		$item['covers']=json_decode($item['covers'],true);
		unset($item['id']);
		$return_data=[
			'item'=>$item
		];
		jReturn(1,'ok',$return_data);
	}
	
	public function _add(){
		$pageuser=checkLogin();
		$params=$this->params;
		
		if(!$params['content']){
			jReturn(-1,'请输入内容');
		}
		
		if(!$params['phone']){
			jReturn(-1,'请输入手机号');
		}else{
			if(!isPhone($params['phone'])){
				jReturn(-1,'手机号不正确');
			}
		}
		
		if(!$params['email']){
			jReturn(-1,'请输入邮箱');
		}else{
			if(!isEmail($params['email'])){
				jReturn(-1,'邮箱不正确');
			}
		}
		/*
		if(!$params['title']){
			jReturn(-1,'请输入标题');
		}*/
		$covers=[];
		if($params['covers']){
			foreach($params['covers'] as $cv){
				$covers[]=$cv;
			}
		}
		$msg_list=[
			'uid'=>$pageuser['id'],
			'msn'=>getRsn(),
			'phone'=>$params['phone'],
			'email'=>$params['email'],
			'title'=>$params['title'],
			'content'=>$params['content'],
			'covers'=>json_encode($covers),
			'create_time'=>NOW_TIME
		];
		try{
			Db::table('msg_list')->insertGetId($msg_list);
		}catch(\Exceptioin $e){
			jReturn(-1,'系统繁忙请稍后再试');
		}
		$return_data=[
			'msn'=>$msg_list['msn']
		];
		jReturn(1,'提交成功',$return_data);
	}
	
	public function _log_list(){
		$pageuser=checkLogin();
		$params=$this->params;
		$params['page']=intval($params['page']);
		$mitem=Db::table('msg_list')->where("msn='{$params['msn']}'")->find();
		if(!$mitem||$mitem['uid']!=$pageuser['id']){
			jReturn(-1,'不存在相应的记录');
		}
		$where='1=1';
		//$where.=empty($params['s_cid'])?'':" and log.cid={$params['s_cid']}";
		$where.=" and log.mid={$mitem['id']}";
		$count_item=Db::table('msg_list_log log')
		//->leftJoin('news_category c','log.cid=c.id')
		->fieldRaw('count(1) as cnt')
		->where($where)
		->find();
		
		$list=Db::view(['msg_list_log'=>'log'],['*'])
		//->view(['news_category'=>'c'],['name'=>'cat_name'],'log.cid=c.id','LEFT')
		->where($where)
		->order(['log.id'=>'desc'])
		->page($params['page'],$this->pageSize)
		->select()
		->toArray();
		
		foreach($list as &$item){
			$item['create_time']=date('m-d H:i',$item['create_time']);
			if($item['fuid']==$pageuser['id']){
				$item['from']='我';
			}else{
				$item['from']='系统';
			}
			$item['content']=nl2br($item['content']);
			unset($item['id']);
		}
		$total_page=ceil($count_item['cnt']/$this->pageSize);
		$return_data=[
			'list'=>$list,
			'count'=>intval($count_item['cnt']),
			'page'=>$params['page']+1,
			'finished'=>$params['page']>=$total_page?true:false,
			'limit'=>$this->pageSize
		];
		jReturn(1,'ok',$return_data);
	}
	
	public function _addLog(){
		$pageuser=checkLogin();
		$params=$this->params;
		if(!$params['msn']){
			jReturn(-1,'缺少参数');
		}
		if(!$params['content']){
			jReturn(-1,'请填写内容');
		}
		$item=Db::table('msg_list')->where("msn='{$params['msn']}'")->find();
		if(!$item||$item['uid']!=$pageuser['id']){
			jReturn(-1,'不存在相应的记录');
		}
		$msg_list_log=[
			'mid'=>$item['id'],
			'fuid'=>$pageuser['id'],
			'content'=>$params['content'],
			'create_time'=>NOW_TIME
		];
		try{
			Db::table('msg_list_log')->insertGetId($msg_list_log);
			Db::table('msg_list')->where("id={$item['id']}")->update(['is_new'=>1]);
		}catch(\Exceptioin $e){
			jReturn(-1,'系统繁忙请稍后再试');
		}
		jReturn(1,'提交成功');
	}
	
}
?>