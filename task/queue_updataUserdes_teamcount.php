 

<?php
require_once(dirname(__FILE__) . '/daemon.ini.php');

use think\facade\Db;

error_reporting(7);

$redis = new MyRedis();
$lonflg = $redis->get('task_looplonflg');
if ($lonflg <= 0) {
	$result = Db::table('sys_user')
		->where('first_pay_day', '>', 0)
		->order('lonflg', 'desc')
		->field('lonflg')
		->limit(1)
		->find(); // 注意这里使用find代替之前的page和select方法组合
	$lonflg = $result !== null ? $result['lonflg'] : 0;
}

while (true) {
	$now_time = time();
	$now_day = date('Ymd', $now_time);
	$list = Db::table('sys_user')->where("first_pay_day >0 and teamcount >0 and lonflg=" . $lonflg)->order(['teamcount' => 'desc'])->limit(5)->select()->toArray();
	if (!$list) {
		output('没有数据暂停5秒');
		sleep(5);
		$lonflg = $lonflg + 1;
		$redis->set('task_looplonflg', $lonflg);
		continue;
	}

	foreach ($list as $user) {
		Db::startTrans();
		try {
			$teamcount = 0;
			$teamcount = updateUserTeamCount($user['id']);
			Db::table('sys_user')->where(' id=' . $user['id'])
				->update(['lonflg' => $lonflg + 1]);
			// if (!$user['pidg1'] || $user['pidg1'] == 0) {
			// 	$pidg1 = updataUsercpid_gid71($user['id']);
			// }
			//更新用户的二级代理
			if (!$user['pidg2'] || $user['pidg2'] == 0) {
				$pidg2 = updataUserPidGid($user['id']);
			}
			Db::commit();
			output('id:' . $user['id']    . ' : ' . $teamcount . '|' . $pidg2);
		} catch (\Exception $e) {
			Db::rollback();
		}
	}
}
