 

<?php
require_once(dirname(__FILE__) . '/daemon.ini.php');

use think\facade\Db;

error_reporting(7);

while (true) {
	$now_time = time();
	$now_day = date('Ymd', $now_time);
	$list = Db::table('sys_user')->where(" first_pay_day >0 and lonflg=0")->order(['id' => 'desc'])->page(1, 1)->select()->toArray();
	if (!$list) {
		output('没有数据暂停5秒');
		sleep(5);
		continue;
	}

	foreach ($list as $user) {
		Db::startTrans();
		try { 
			$teamcount = 0; 
			$teamcount = updateUserTeamCount($user['id']); 
			$teamcount .= updateUserTeamCount($user['pid']);
			$upuser = Db::table('sys_user')->where(" id=" . $user['pid'])->find();
			$teamcount .= updateUserTeamCount($upuser['pid']);
			$upuser1 = Db::table('sys_user')->where(" id=" . $upuser['pid'])->find();
			$teamcount .= updateUserTeamCount($upuser1['pid']);
			$config = $_ENV['PAY_CONFIG']['looplonflg'];
            $lonflg=$config['lonflg']??1;
			Db::table('sys_user')->where(' id=' . $user['id'] . ' or id=' . $user['pid'] . ' or id=' . $upuser['pid'], ' or id=' . $upuser1['pid'])->update(['lonflg' => $lonflg]);

			Db::commit();
			output('id:' . $user['id'] . ' | ' . $user['pid'] . ' | ' . $upuser['pid'] . ' | ' . $upuser1['pid']  . ' : ' . $teamcount);
		} catch (\Exception $e) {
			Db::rollback();
		}
	} 
}
