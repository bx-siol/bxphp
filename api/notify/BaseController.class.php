<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class BaseController extends CommonCtl
{
	public function __construct()
	{
		parent::__construct();
	}

	//代收回调统一处理函数
	protected function payAct($pdata = [], $paytype = '')
	{
		//writeLog("payAct:" . $paytype . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $paytype . '/notify/pay');
		if (!$pdata['successStr']) {
			$pdata['successStr'] = 'success';
		}
		if ($pdata['code'] != 1) {
			ReturnToJson(-1, $pdata['failStr']);
		}

		//$time = date("Y-m-d", time());
		$osn = $pdata['osn'];
		//writeLog("payAct:1" . $paytype . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $paytype . '/notify/pay');
		Db::startTrans();
		try {
			$order = Db::table('fin_paylog')->where("osn='{$osn}'")->lock(true)->find();
			if (!$order) {
				writeLog($osn . "No order", $paytype . '/notify/pay');
				throw new \Exception('No order');
			}
			if ($order['status'] == 9) {
				exit ($pdata['successStr']);
			}
			if (abs($order['money'] - $pdata['amount']) > 0.1) {
				writeLog($osn . "Money error", $paytype . '/notify/pay');
				throw new \Exception('Money error');
			}
			//writeLog("payAct:2" . $paytype . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $paytype . '/notify/pay');
			$check_order = Db::table('fin_paylog')->where("uid={$order['uid']} and status=9")->find();
			$fin_paylog = [
				'status' => 9,
				'pay_time' => time()
			];
			if (!$check_order) {
				$fin_paylog['is_first'] = 1;
			}
			Db::table('fin_paylog')->where("id={$order['id']}")->update($fin_paylog);
			//writeLog("payAct:3" . $paytype . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $paytype . '/notify/pay');
			$wallet = getWallet($order['uid'], 1);
			if (!$wallet) {
				throw new \Exception('fail2');
			}
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$wallet_data = [
				'balance' => $wallet['balance'] + $pdata['amount']
			];
			if ($wallet_data['balance'] < 0) {
				throw new \Exception('fail3');
			}
			//writeLog("payAct:4" . $paytype . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $paytype . '/notify/pay');
			//更新钱包余额
			$rs = Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			//writeLog("payAct:5" . $paytype . json_encode($rs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $paytype . '/notify/pay');
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 21,
				'money' => $order['money'],
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => $order['osn'],
				'remark' => 'Recharge:' . $pdata['amount']
			], 1);
			//writeLog("payAct:6" . $paytype . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $paytype . '/notify/pay');
			if (!$result) {
				throw new \Exception('流水记录写入失败');
			}
			//writeLog("payAct:7" . $paytype . json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $paytype . '/notify/pay');
			$cuser = Db::table('sys_user')->where("id={$order['uid']}")->find();
			if ($cuser['first_pay_day'] > 0) {
			} else {
				Db::table('sys_user')->where("id={$cuser['id']}")->update(['first_pay_day' => date('Ymd')]);
			}
			Db::commit();
			$this->redis->rmall(RedisKeys::USER_WALLET . $order['uid']);

			//writeLog($osn . " is ok", $paytype . '/notify/pay');
			// if ($order['gplayerId'] != null && $order['gaccount'] != null) {
			// 	$gurl = 'http://8.218.132.62/index.php/admin/login/doaddscore.html';
			// 	$this->curl_post($gurl, ['addscore' => $order['money'] * 100, 'username' => $order['gaccount'], 'uid' => $order['gplayerId']]); //添加游戏积分
			// }

			try {
				$up_user = getUpUser($order['uid'], true);
				$rs = sendbdjt($up_user, $order['money'], $osn);
				writeLog($rs, 'bot');
			} catch (\Exception $ed) {
				writeLog($osn . "订单异常", $paytype . 'bot/err');
			}
		} catch (\Exception $e) {
			writeLog($osn . "订单异常" . json_encode($e, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $paytype . '/notify/pay');
			Db::rollback();
			if ($pdata['failStr']) {
				exit ($pdata['failStr']);
			} else {
				ReturnToJson(-1, 'fail1:' . $e->getMessage());
			}
		}
		echo $pdata['successStr'];
	}

	protected function cashAct($pdata = [])
	{
		//###########################################
		$osn = $pdata['osn']; //本地单号
		$out_osn = $pdata['out_osn']; //通道单号
		$pay_status = $pdata['pay_status']; //代付状态 9或3
		$pay_msg = $pdata['pay_msg'];
		$successStr = $pdata['successStr'] ? $pdata['successStr'] : 'success'; //处理成功后输出字符串
		$failStr = $pdata['failStr'] ? $pdata['failStr'] : 'fail'; //处理失败后输出字符串
		//###########################################
		$time = date("Y-m-d", time());
		$log_file = LOGS_PATH . 'Notify/cashAct/' . $time . '.txt';

		$needbackcash = false;
		$selectifsc = false;
		$order = Db::table('fin_cashlog')->where("osn='{$osn}'")->find();
		if (!$order) {
			ReturnToJson(-1, 'No order');
		}

		if ($order['pay_status'] == 9) {
			if ($pay_status == 3) {
				$needbackcash = true;
			} elseif ($pay_status == 4) {
				$needbackcash = true;
			} else
				exit ($successStr);
		} elseif ($order['pay_status'] == 3) {
			//exit($failStr);
		} else {
			//exit($failStr); 
			if ($pay_status == 3) {
				$selectifsc = true;
			}
		}
		//判断是否需要退款 pay_status=4 是必要退款项
		if ($pay_status == 4) {
			$needbackcash = true;
			$pay_status = 3;
		}
		Db::startTrans();
		try {
			$fin_cashlog = [
				'pay_status' => $pay_status,
				'pay_msg' => $pay_msg,
				'pay_time' => time()
			];
			if ($out_osn) {
				$fin_cashlog['out_osn'] = $out_osn;
			}
			if ($needbackcash) {
				$back_money = $order['money'];
				if ($order['fee_mode'] == 2) {
					$back_money = $order['money'] + $order['fee'];
				}
				if ($back_money > 50000) {
					$fin_cashlog['pay_msg'] .= ' | 订单金额大于50000未退款 原始单号:' . $order['osn'];
				} else {
					$fin_cashlog['status'] = 3; //3失败 
					$wallet = getWallet($order['uid'], 2);
					if (!$wallet) {
						//ReturnToJson(-1,'Wallet acquisition exception.');
						return ['code' => -1, 'msg' => 'Wallet acquisition exception.'];
					}
					$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
					$wallet_data = [
						'balance' => $wallet['balance'] + $back_money
					];
					Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
					$result = walletLog([
						'wid' => $wallet['id'],
						'uid' => $wallet['uid'],
						'type' => 33,
						'money' => $back_money,
						'ori_balance' => $wallet['balance'],
						'new_balance' => $wallet_data['balance'],
						'fkey' => $order['osn'],
						'remark' => 'Withdrawal refund-' . $order['osn']
					]);
					if (!$result) {
						throw new Exception('写入流水日志失败');
					}
					//$fin_cashlog['osn'] = getRsn(); //3失败
					$fin_cashlog['oldosn'] = $order['osn']; //3失败
					$fin_cashlog['pay_msg'] .= ' | 订单反转 | 退款成功 原始单号:' . $order['osn'];
				}
			}

			// if ($selectifsc) {
			// 	$ifscres = $this->Getifsc($order['receive_ifsc']);
			// 	if ($ifscres == '"Not Found"') {
			// 		$back_money = $order['money'];
			// 		if ($order['fee_mode'] == 2) {
			// 			$back_money = $order['money'] + $order['fee'];
			// 		}
			// 		$fin_cashlog['status'] = 3; //3失败 
			// 		$wallet = getWallet($order['uid'], 2);
			// 		if (!$wallet) {
			// 			echo $successStr;
			// 			return;
			// 		}
			// 		$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			// 		$wallet_data = [
			// 			'balance' => $wallet['balance'] + $back_money
			// 		];
			// 		Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			// 		$result = walletLog([
			// 			'wid' => $wallet['id'],
			// 			'uid' => $wallet['uid'],
			// 			'type' => 33,
			// 			'money' => $back_money,
			// 			'ori_balance' => $wallet['balance'],
			// 			'new_balance' => $wallet_data['balance'],
			// 			'fkey' => $order['osn'],
			// 			'remark' => 'Withdrawal refund-' . $order['osn']
			// 		]);
			// 		if (!$result) {
			// 			echo $successStr;
			// 			return;
			// 		}
			// 		$fin_cashlog['pay_msg'] .= ' | ifsc错误 | 退款成功 ';
			// 	} else {
			// 		$fin_cashlog['pay_msg'] .= ' ifsc 长度:' . strlen($ifscres);
			// 	}
			// }

			Db::table('fin_cashlog')->where("id={$order['id']}")->update($fin_cashlog);
			Db::commit();
			$this->redis->rmall(RedisKeys::USER_WALLET . $order['uid']);
			//file_put_contents($log_file,   "\r\n{$osn} is ok ".json_encode($pdata)."\r\n\r\n", FILE_APPEND);
		} catch (\Exception $e) {
			Db::rollback();
			//file_put_contents($log_file,   "\r\n {$osn} err" . var_export($e, true) . "\r\n\r\n", FILE_APPEND);
			ReturnToJson(-1, $failStr);
		}
		echo $successStr;
	}

	public function Getifsc($ifsc)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_URL, 'http://8.210.239.216:3387/?ifsc=' . $ifsc);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($curl);
		return $res;
	}
}