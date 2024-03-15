<?php
require_once(dirname(__FILE__).'/daemon.ini.php');
use think\facade\Db;

while(true){
	$now_time=time();
	$now_day=date('Ymd',$now_time);
	$list=Db::table('pro_order')->where("status=1 and reward_day<{$now_day} and create_day<{$now_day}")->page(1,50)->select()->toArray();
    if(!$list){
        output('没有数据暂停5秒');
        sleep(5);
        continue;
    }
	
    foreach($list as $item){
		$project=getPset('project');
		$commission_arr=[];
		$com_arr=explode(',',$project['commission']);
		foreach($com_arr as $cv){
			$cv_arr=explode('=',$cv);
			$cv_level=intval($cv_arr[0]);
			$cv_val=floatval($cv_arr[1]);
			if($cv_level<1||$cv_val<0){
				continue;
			}
			$commission_arr[$cv_level]=$cv_val;
		}
		Db::startTrans();
		try{
			$item=Db::table('pro_order')->where("id={$item['id']}")->lock(true)->find();
			if($item['status']!=1){
				continue;
			}
			$reward=($item['rate']/100)*$item['money'];
			$pro_order=[
				'reward_time'=>$now_time,
				'reward_day'=>$now_day,
				'total_reward'=>$item['total_reward']+$reward,
				'total_days'=>$item['total_days']+1,
			];
			if($pro_order['total_days']>=$item['days']){
				$pro_order['status']=9;
			}
			Db::table('pro_order')->where("id={$item['id']}")->update($pro_order);
			$wallet=getWallet($item['uid'],2);
			if(!$wallet){
				throw new \Exception('钱包获取异常');
			}
			$wallet=Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$wallet_data=[
				'balance'=>$wallet['balance']+$reward
			];
			//写入流水记录
			$result=walletLog([
				'wid'=>$wallet['id'],
				'uid'=>$wallet['uid'],
				'type'=>6,
				'money'=>$reward,
				'ori_balance'=>$wallet['balance'],
				'new_balance'=>$wallet_data['balance'],
				'fkey'=>$item['id'],
				'remark'=>'Profit:'.$item['osn']
			]);
			if(!$result){
				throw new \Exception('流水记录写入失败');
			}
			//写入收益记录
			$pro_reward=[
				'uid'=>$item['uid'],
				'oid'=>$item['id'],
				'osn'=>$item['osn'],
				'type'=>1,
				'base_money'=>$item['money'],
				'rate'=>$item['rate'],
				'money'=>$reward,
				'remark'=>$item['osn'],
				'create_time'=>$now_time,
				'create_day'=>$now_day
			];
			Db::table('pro_reward')->insertGetId($pro_reward);
			/*
			//归还本金
			if($pro_order['status']==9){
				//写入流水记录
				$result=walletLog([
					'wid'=>$wallet['id'],
					'uid'=>$wallet['uid'],
					'type'=>4,
					'money'=>$item['money'],
					'ori_balance'=>$wallet_data['balance'],
					'new_balance'=>$wallet_data['balance']+$item['money'],
					'fkey'=>$item['id'],
					'remark'=>'Principal:'.$item['osn']
				]);
				if(!$result){
					throw new \Exception('流水记录写入失败');
				}
				$wallet_data['balance']+=$item['money'];
			}
			*/
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			
			//返佣
			$up_users=getUpUser($item['uid'],true);
			
			foreach($up_users as $uv){
				$rate=$commission_arr[$uv['agent_level']];
				if(!$rate||$rate<0){
					continue;
				}
				$rebate=$reward*($rate/100);
				$wallet2=getWallet($uv['id'],2);
				if(!$wallet2){
					throw new \Exception('钱包获取异常');
				}
				$wallet2=Db::table('wallet_list')->where("id={$wallet2['id']}")->lock(true)->find();
				$wallet_data2=[
					'balance'=>$wallet2['balance']+$rebate
				];
				Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
				//写入流水记录
				$result2=walletLog([
					'wid'=>$wallet2['id'],
					'uid'=>$wallet2['uid'],
					'type'=>8,
					'money'=>$rebate,
					'ori_balance'=>$wallet2['balance'],
					'new_balance'=>$wallet_data2['balance'],
					'fkey'=>$item['id'],
					'remark'=>'Commission:'.$item['osn']
				]);
				if(!$result2){
					throw new \Exception('流水记录写入失败');
				}
				//写入收益记录
				$pro_reward2=[
					'uid'=>$uv['id'],
					'oid'=>$item['id'],
					'osn'=>$item['osn'],
					'type'=>2,
					'level'=>$uv['agent_level'],
					'base_money'=>$reward,
					'rate'=>$rate,
					'money'=>$rebate,
					'remark'=>$item['osn'],
					'create_time'=>$now_time,
					'create_day'=>$now_day
				];
				Db::table('pro_reward')->insertGetId($pro_reward2);
			}
			
			Db::commit();
			 
		}catch(\Exception $e){
			Db::rollback();
		}
    }
	output('处理完一批，暂停1秒');
    sleep(1);
}

?>