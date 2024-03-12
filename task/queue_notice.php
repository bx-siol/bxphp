<?php
require_once(dirname(__FILE__).'/daemon.ini.php');
use think\facade\Db;

while(true){
	$list=Db::table('sys_notice')->where('status=0')->page(1,50)->select()->toArray();
    if(!$list){
        output('没有数据暂停1秒');
        sleep(1);
        continue;
    }
	
    foreach($list as $item){
		$sys_notice=[
			'send_time'=>time(),
			'status'=>2
		];
		$res=Db::table('sys_notice')->where("id={$item['id']}")->update($sys_notice);
		if(!$res){
			continue;
		}
		$params=json_decode($item['params'],true);
		if(!$params||empty($params['act'])){
			continue;
		}
		sendNotice($params);//发送
		$now_min=intval(date('i'));
		if($now_min==1){
			$limit_id=$item['id']-10;//保留10条
			Db::table('sys_notice')->where("id<{$limit_id}")->delete();
		}
    }
	output('处理完一批，暂停1秒');
    sleep(1);
}

?>