<?php
require_once(dirname(__FILE__) . '/daemon.ini.php');

use think\facade\Db;

while (true) {
	$now_time = time();
	$now_day = date('Ymd', $now_time);

	$tomorrow = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
	$ytime = date("Ymd", $tomorrow);
	$time = date("Ymd", time());
	$sqlstr = "cid=3 and balance>=1 and (lasttime=0 or ({$ytime}-lasttime>0))";
	//output($sqlstr);
	// lasttime
	$list = Db::table('wallet_list')->where($sqlstr)->page(1, 50)->select()->toArray();
	//output(count($list));
	if (!$list) {
		output('没有数据暂停5秒');
		sleep(5);
		continue;
	}

	foreach ($list as $item) {
		Db::startTrans();
		try {
			if (intval($item['balance'])  <= 0) {
				continue;
			}
			$ral = getConfig('yeb_syl');
			$reward = ($ral / 365 / 100) * $item['balance'];

			$wallet_data = [
				'balance' => $item['balance'] + $reward,
				'lasttime' => $time
			];
			//写入流水记录
			$result = walletLog([
				'wid' => $item['id'],
				'uid' => $item['uid'],
				'type' => 53,
				'money' => $reward,
				'ori_balance' => $item['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => $item['id'],
				'remark' => 'RS:+' . $reward
			]);
			if (!$result) {
				throw new \Exception('流水记录写入失败');
			}
			//写入收益记录
			$pro_reward = [
				'uid' => $item['uid'],
				'oid' => $item['id'],
				'osn' => $item['id'],
				'type' => 3,
				'base_money' => $item['balance'],
				'rate' => $ral,
				'money' => $reward,
				'remark' => 'RS:+' . $reward,
				'create_time' => $now_time,
				'create_day' => $now_day
			];
			Db::table('pro_reward')->insertGetId($pro_reward);
			/*
				<th>{{ lang('时间') }}</th>
				<th>{{ lang('投资') }}</th>
				<th>{{ lang('费率') }}(%)</th>
				<th>{{ lang('金额') }}</th>
				<th>{{ lang('备注') }}</th>
					51: "余额宝转入"
					52: "余额宝转出"
					53: "余额宝收益"
			*/
			//更新钱包余额
			Db::table('wallet_list')->where("id={$item['id']}")->update($wallet_data);

			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
		}
	}
	output('处理完一批，暂停1秒');
	sleep(1);
}
