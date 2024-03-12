 

<?php
require_once(dirname(__FILE__) . '/daemon.ini.php');

use think\facade\Db;

error_reporting(7);


$_ENV['PAY_CONFIG']['looplonflg'] = ['lonflg' => 1  ];
$config = $_ENV['PAY_CONFIG']['looplonflg'];
$lonflg=$config['lonflg'];
Db::table('sys_user')->where('first_pay_day >0 and teamcount >0 and lonflg>0 ')->update(['lonflg' => 1]);
			
while (true) {
	$now_time = time();
	$now_day = date('Ymd', $now_time); 
	$list = Db::table('sys_user')->where("first_pay_day >0 and teamcount >0 and lonflg=".$lonflg)->order(['teamcount' => 'desc'])->page(1, 1)->select()->toArray();
	if (!$list) {
		output('没有数据暂停5秒');
		sleep(5);
		$lonflg=$lonflg+1;
		continue;
	}

	foreach ($list as $user) {
		Db::startTrans();
		try {
			$teamcount = 0;
			$teamcount = updateUserTeamCount($user['id']);
			Db::table('sys_user')->where(' id=' . $user['id'])
			->update(['lonflg' => $lonflg+1]);
            
           /*
            if (!$user['pidg1']||$user['pidg1']==0) {
                $pidg1 = updataUsercpid_gid71($user['id'] );
		    } 
			// //更新用户的二级代理
		    if (!$user['pidg2']||$user['pidg2']==0) {
		  	    $pidg2 = updataUsercpid_gid81($user['id'] );
		    }
           */
			Db::commit();
			output('id:' . $user['id']    . ' : ' . $teamcount.'|'.$pidg1.'|'.$pidg2);
		} catch (\Exception $e) {
			Db::rollback();
		}
	}
 
}
