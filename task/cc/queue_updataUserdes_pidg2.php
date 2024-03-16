 

<?php
require_once(dirname(__FILE__) . '/daemon.ini.php');

use think\facade\Db;

error_reporting(7);

 		
while (true) {
	$now_time = time();
	$now_day = date('Ymd', $now_time); 
	$list = Db::table('sys_user')->where("pidg1=0 or pidg1 is null" )->order(['id'])->page(1, 1)->select()->toArray();
	if (!$list) {
		output('没有数据暂停5秒');
		sleep(5); 
		continue;
	}

	foreach ($list as $user) {
		Db::startTrans();
		try {
		  
            if (!$user['pidg1']||$user['pidg1']==0) {
                $pidg1 = updataUserPidGid($user['id'] );
		    } 
			// //更新用户的二级代理
		    if (!$user['pidg2']||$user['pidg2']==0) {
		  	    $pidg2 = updataUserPidGid($user['id'] );
		    }
            
			Db::commit();
			output('id:' . $user['id']    . ' : ' .$pidg1.'|'.$pidg2);
		} catch (\Exception $e) {
			Db::rollback();
		}
	}
 
}
