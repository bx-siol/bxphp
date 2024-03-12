<?php
require_once(dirname(__FILE__).'/daemon.ini.php');
use think\facade\Db;
error_reporting(7);

while(true){
	$list=Db::table('sys_user')->where('is_count=0')->page(1,200)->select()->toArray();
    if(!$list){
        output('没有数据暂停5秒');
        sleep(5);
        continue;
    }
	
    foreach($list as $item){
		
		//统计总投资
		$w1_money=Db::table('pro_order')->where("uid={$item['id']} and is_give=0")->sum('w1_money');
		$w2_money=Db::table('pro_order')->where("uid={$item['id']} and is_give=0")->sum('w2_money');
		$total_invest=$w1_money+$w2_money;
		$total_invest2=Db::table('pro_order')->where("uid={$item['id']} and is_give=0")->sum('money');
		
		$sys_user=[
			'total_invest'=>$total_invest,
			'total_invest2'=>$total_invest2,
			'is_count'=>1
		];
		$res=Db::table('sys_user')->where("id={$item['id']}")->update($sys_user);
    }
	output('处理完一批，暂停2秒');
    sleep(2);
}

?>