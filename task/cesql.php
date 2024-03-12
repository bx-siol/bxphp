<?php
require_once(dirname(__FILE__) . '/daemon.ini.php');

use think\facade\Db;

error_reporting(7);

$now_time = time();
$now_day = date('Ymd', $now_time);
$list = Db::table('sys_user')->where("gid=71")->order(['id' => 'desc'])->select()->toArray();
Db::startTrans();
$sql = '';
try {
	foreach ($list as $user) {
		$sql .= "CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
				ALTER TABLE  pro_reward_{$user['id']} MODIFY COLUMN `id` int(11) NOT NULL FIRST;";
		// CREATE TABLE IF NOT EXISTS wallet_log_{$user['id']} LIKE wallet_log;
		// ALTER TABLE wallet_log_{$user['id']} MODIFY COLUMN `id` int(11) NOT NULL FIRST;
		// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
		// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
		// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
		// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
		// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
		// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
		// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;  
	}
	$res = Db::execute($sql);
	output($res);
	Db::commit();
} catch (\Exception $e) {
	Db::rollback();
}
