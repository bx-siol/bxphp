<?php
!defined('ROOT_PATH') && exit;
use think\facade\Db;

class TestController extends BaseController{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _ua(){
		//p($_SERVER['HTTP_USER_AGENT']);
		$user=flushUserinfo(413746);
		p($user);
	}
	
	public function _index(){
		//echo date('Ymd',strtotime(date('Y-m-d')." -".($w ? $w - $first : 6).' days'));
		//$dtype=Db::table('fin_dtype')->where("status=3")->orderRaw("sort desc,rand()")->find();
		//p($dtype);
		exit('Test_index');
	}
	
	public function _upuser(){
		$uid=655952;
		$up_arr=getUpUser($uid,true);
		p($up_arr);
	}
	
	public function _user(){
		exit;
		echo date('Y-m-d H:i:s').'<br>';
		$account='gz5006';
		$account='gz007';
		$user=Db::table('sys_user')->where("account='{$account}'")->find();
		$down_arr=getDownUser($user['id'],false);
		p(count($down_arr));
		
		$ids=[];
		foreach($down_arr as $dv){
			//$ids[]=$dv['id'];
			$ids[]=$dv;
		}
		sort($ids);
		foreach($ids as $id){
			echo "{$id}<br>";
		}
		echo date('Y-m-d H:i:s').'<br>';
	}
	
	public function _user2(){
		exit;
		echo date('Y-m-d H:i:s').'<br>';
		$account='gz5006';
		$account='gz007';
		$user=Db::table('sys_user')->where("account='{$account}'")->find();
		$sql="with RECURSIVE temp as (select id as t,id,pid from sys_user
	union all
	select temp.t,a1.id,a1.pid from sys_user a1
	join temp on a1.id=temp.pid
) select t from temp where id={$user['id']}";

		$down_arr=Db::query($sql);
		//p($down_arr);
		$ids=[];
		foreach($down_arr as $dv){
			$ids[]=$dv['t'];
		}
		sort($ids);
		foreach($ids as $id){
			echo "{$id}<br>";
		}
		echo date('Y-m-d H:i:s').'<br>';
	}
	
	
	public function _acc(){
		$redis=new MyRedis(0);
		$account='67872871671';
		$rkey='acc2uid_'.$account;
		$res=$redis->get($rkey);
		p($res);
		$redis->rm($rkey);
	}
	
	public function _notice(){
		exit('die');
		$data=[
			'act'=>'Notice/order2b',
			'osn'=>'e0745a3eab92627e'
		];
		$result=sendNotice($data);
		//addNotice($data,'测试消息');
		echo 'ok';
	}
	
	public function _tree(){
		$uid=216851;
		//memberTryUpgrade($uid);
		echo 'end';
		/*
		require_once APP_PATH.'common/tree.php';
		$uid=492414;
		$uid=1;
		$down_ids=getDownUser($uid,false);
		$down_ids[]=0;
		$down_ids_str=implode(',',$down_ids);
		$user_arr=Db::table('sys_user')->where("id in({$down_ids_str})")
		->field(['id','account','nickname','reg_time'])
		->order(['reg_time'=>'asc'])
		->select()->toArray();
		$root=build_cbtree($user_arr);
		p($root);
		//bf_traverse($root);
		//p($user_arr);
		*/
	}
	
}
?>