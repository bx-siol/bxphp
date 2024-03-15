<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class BaseController extends CommonCtl
{

	//6062622551:AAEDUjElq6M3eHGyBbgXpgT4DOOlyzYmCgc
	//https://api.telegram.org/bot6063458434:AAHuS8OUB4Xy9wYtQCqKJWzCaWln6GFP_po/getUpdates
	protected $token = '6063458434:AAHuS8OUB4Xy9wYtQCqKJWzCaWln6GFP_po';
	protected $tokens = [
		'232723' => ['id' => '-1001974041997', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//05
		'710849' => ['id' => '-1001974041997', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//11
		'218946' => ['id' => '-1001974041997', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//09 

		'676922' => ['id' => '-1001857373097', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//06   
		'727374' => ['id' => '-1001834084818', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//03  
		'675079' => ['id' => '-1001834084818', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//04 
		'522519' => ['id' => '-1001908498610', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//01 
		'954439' => ['id' => '-1001810740604', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//02 
		'216758' => ['id' => '-1001748709723', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//07 
		'722526' => ['id' => '-1001975703973', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//08  

		'618902' => ['id' => '-1001957422808', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//10   
		'276423' => ['id' => '-1001985646420', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//12  
		'832687' => ['id' => '-1001926855946', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//13 
		'398850' => ['id' => '-1001975941853', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//14
		'465396' => ['id' => '-1001810911853', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'], //15
	];

	public function __construct()
	{
		parent::__construct();
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
	protected function CurlPost($url, $data = [], $timeout = 30)
	{
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => $timeout,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json;charset=UTF-8'
				)
			)
		);

		$response = curl_exec($curl);
		if ($curl->error) {
			$arrCurlResult = json_decode($curl->errorMessage, true);
		} else {
			$arrCurlResult = json_decode($response, true);
		}
		curl_close($curl);
		unset($curl);
		return $arrCurlResult;
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
				exit($pdata['successStr']);
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
				'remark' => 'Recharge:' + $pdata['amount']
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
			//writeLog($osn . " is ok", $paytype . '/notify/pay');

			// if ($order['gplayerId'] != null && $order['gaccount'] != null) {
			// 	$gurl = 'http://8.218.132.62/index.php/admin/login/doaddscore.html';
			// 	$this->curl_post($gurl, ['addscore' => $order['money'] * 100, 'username' => $order['gaccount'], 'uid' => $order['gplayerId']]); //添加游戏积分
			// }

			try {
				$glstr = [
					'🗣恭喜出单，业绩长虹，蒸蒸日上，大吉大利❤️🤏',
					'🗣恭喜出单，人生就是这样,耐得住寂寞才能守得住繁华,该奋斗的年龄不要选择了安逸❤️🤏',
					'🗣恭喜出单，人有理想才有希望,那怕看到的是很迷茫,只要有坚定不移的信念,就不惧艰难痛苦去勇敢面对。❤️🤏',
					'🗣恭喜出单，成功的道路上充满荆棘,苦战方能成功。祝贺你成功开首单,你成功的道路正在开启。❤️🤏',
					'🗣恭喜开单，你的努力终于有了回报，你的经历，正在谱写你的简历；你平时的坚持，藏着你未来的样子。❤️🤏',
					'🗣恭喜出单，勤奋的态度和主动积极的精神才能让你获得成功，恭喜你，你做到了！这是你的第一单，但绝对不是最后一单！❤️🤏',
					'🗣恭喜出单，你的努力终于有了回报，你的经历，正在谱写你的简历；你平时的坚持，藏着你未来的样子。❤️🤏',
					'🗣恭喜出单，人有理想才有希望,那怕看到的是很迷茫,只要有坚定不移的信念,就不惧艰难痛苦去勇敢面对。❤️🤏',
					'💥恭喜开单，你从来不知道，一个认真努力的你，可以有多么优秀。☀️ ',
					'🌈恭喜开单，每一道做对的题都是为了让你遇到更优秀的人，每一道做错的题都是为了让你遇到更匹配的人。🌞',
					'🌞恭喜开单，为此，我会一直努力下去，每天充满动力，沿途春暖花开。☄️',
					'❤️‍🔥恭喜开单，当你感觉到难的时候，就是你在进步的时候。机械、重复的工作是最低效的成长，而逼自己去做难的事、你没干过的事，就能大大提升你的潜力。❤️',
					'💞恭喜开单，成功不是将来才有的，而是从决定去做的那一刻起，持续累积而成。🔅',
					'🔱恭喜开单，不要让自己每天的时间分散，碎片化是效率的生死大敌，少刷朋友圈，少水群，因为这只会浪费你的时间。📣',
					'♥️恭喜开单，生活不会向你许诺什么，尤其不会向你许诺成功。它只会给你挣扎、痛苦和煎熬的过程。所以要给自己一个梦想，之后朝着那个方向前进。📢',
					'🗣恭喜开单，只要你不颓废，不消极，一直悄悄酝酿着乐观，培养着豁达，坚持着善良，始终朝着梦想前行，永远在路上，就没有到达不了的远方。🤭',
					'🗣恭喜开单！选一个方向，定一个时间。剩下的只管努力与坚持，时间会给我们最后的答案☀️',

					'🔥恭喜开单，没有口水与汗水，就没有成功的泪水。坚持就是胜利，顺利拿下阿三人头！😡',
					'💥恭喜开单，部门业绩大比拼，大家团结亦齐心，领导带队冲向前，各队精明又干练。看到终点齐冲刺，老板见此予鼓励，加油都是好样地，业绩比以往翻三番！🫂',
					'🥰朋友，恭喜你出单，我特邀请了几位群友前来祝贺：“开单大吉”，上午到了“万事如意”，中午来了“开心每天”，下午陪了“鸿运当头”，晚上有了“和和睦睦”！祝贺你天天开单，坐着就有单随你来！嘿嘿！😘',
					'🫂成功靠朋友，成长靠对手，成就靠团队。创意是金钱，策划显业绩，思考才致富。知道是知识，做到才智慧，多做少多说。积极激励我，多劳多收获，汗水育成果。梦想聚团队，团队铸梦想，激情快乐人',
					'💥胜利女神一定会眷顾我们，但是你不奋斗，你的才华如何配得上你的任性。不奋斗你的脚步又如何赶上家人老去的速度。不奋斗，世界这么大你怎么去看',
					'🔥恭喜出单，如果你只局限于对别人成就的羡慕和徒做无聊的叹息,从不为争取自己的理想而付出努力,那么你心中的巨人将永远沉睡。因此,只有积极的心志才能唤醒你心中酣睡的巨人,才能让你从消极走向积极,从被动走向成功!',
					'🫂我们经常不能坚持完成自己梦想,是因为我们没有毅力,我们害怕困难,不懂得怎么面对困难,对他是敬而远之。我们害怕失败。其实毅力是带给所有人夺取胜利之果的动力。如果连追求自己理想的毅力都没有的话,又怎么奢望能够像别人一样实现自己的理想,到达梦想中的终点呢?',
				];

				//	$userinfo = Db::table('sys_user')->where("id='{$order['uid']}'")->find();

				$up_user = getUpUser($order['uid'], true);
				$tjr = '/';
				$rjdl = '/';
				$pidg1 = '0';
				foreach ($up_user as $uk => $uv) {
					if ($uk == 0)
						$tjr = $uv['account'];
					if ($uv['gid'] == 81)
						$rjdl = $uv['account'];
					if ($uv['gid'] == 71)
						$pidg1 = $uv['id'];
				}
				$token = $this->token;
				$chatId = '-1001977279590';
				foreach ($this->tokens as $key => $val) {
					if ($key == $pidg1) {
						$token = $val['tk'];
						$chatId = $val['id'];
						break;
					}
				}
				$account = rand(0, count($glstr) - 1);
				$str = "订单号：{$osn}\n二级代理：{$rjdl}\n 推荐人：{$tjr}\n 订单金额：{$order['money']}\n{$glstr[$account]}";

				//$temps =	$this->send_photo('-709672358', $str);
				$temps = $this->send_photo($chatId, $str, $token);
				//file_put_contents($log_file,   "\r\n{$osn}\r\n" . $temps . '\r\n' . var_export($up_user, true) . "\r\n" . $str . "\r\n\r\n", FILE_APPEND);
			} catch (\Exception $ed) {
				writeLog($osn . "订单异常", $paytype . '/notify/pay');
			}
		} catch (\Exception $e) {
			writeLog($osn . "订单异常" . json_encode($e, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $paytype . '/notify/pay');
			Db::rollback();
			if ($pdata['failStr']) {
				exit($pdata['failStr']);
			} else {
				ReturnToJson(-1, 'fail:' . $e->getMessage());
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
				exit($successStr);
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
						//ReturnToJson(-1,'钱包获取异常');
						return ['code' => -1, 'msg' => '钱包获取异常'];
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

			if ($selectifsc) {
				$ifscres = $this->Getifsc($order['receive_ifsc']);
				if ($ifscres == '"Not Found"') {
					$back_money = $order['money'];
					if ($order['fee_mode'] == 2) {
						$back_money = $order['money'] + $order['fee'];
					}
					$fin_cashlog['status'] = 3; //3失败 
					$wallet = getWallet($order['uid'], 2);
					if (!$wallet) {
						echo $successStr;
						return;
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
						echo $successStr;
						return;
					}
					$fin_cashlog['pay_msg'] .= ' | ifsc错误 | 退款成功 ';
				} else {
					$fin_cashlog['pay_msg'] .= ' ifsc 长度:' . strlen($ifscres);
				}
			}
			Db::table('fin_cashlog')->where("id={$order['id']}")->update($fin_cashlog);
			Db::commit();
			//file_put_contents($log_file,   "\r\n{$osn} is ok ".json_encode($pdata)."\r\n\r\n", FILE_APPEND);
		} catch (\Exception $e) {
			Db::rollback();
			//file_put_contents($log_file,   "\r\n {$osn} err" . var_export($e, true) . "\r\n\r\n", FILE_APPEND);
			ReturnToJson(-1, $failStr);
		}
		echo $successStr;
	}


	protected function cashAct1($pdata = [])
	{
		//###########################################
		$osn = $osn; //本地单号
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
				$pay_msg .= " | 订单反转 ";
				$needbackcash = true;
			} else {
				exit($successStr . '9');
			}
		} elseif ($order['pay_status'] == 3) {
			//exit($failStr);
		} else {
			//exit($failStr); 
			if ($pay_status == 3) {
				$selectifsc = true;
			}
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
						//ReturnToJson(-1,'钱包获取异常');
						return ['code' => -1, 'msg' => '钱包获取异常'];
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
					$fin_cashlog['osn'] = getRsn(); //3失败
					$fin_cashlog['oldosn'] = $order['osn']; //3失败
					$fin_cashlog['pay_msg'] .= ' | 退款成功 原始单号:' . $order['osn'];
				}
			}
			if ($selectifsc) {
				$ifscres = $this->Getifsc($order['receive_ifsc']);
				if ($ifscres == '"Not Found"') {
					$fin_cashlog['status'] = 3; //3失败 
					$wallet = getWallet($order['uid'], 2);
					if (!$wallet) {
						echo $successStr;
						return;
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
						echo $successStr;
						return;
					}
					$fin_cashlog['pay_msg'] .= ' | ifsc错误 | 退款成功 ';
				}
			}
			Db::table('fin_cashlog')->where("id={$order['id']}")->update($fin_cashlog);
			Db::commit();
			//file_put_contents($log_file,   "\r\n{$osn} is ok ".json_encode($pdata)."\r\n\r\n", FILE_APPEND);
		} catch (\Exception $e) {
			Db::rollback();
			//file_put_contents($log_file,   "\r\n {$osn} err" . var_export($e, true) . "\r\n\r\n", FILE_APPEND);
			ReturnToJson(-1, $failStr);
		}
		echo $successStr;
	}

	protected function curl_post($url, $data)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}

	protected function send_photo($chatId, $text, $token)
	{
		$cnf_domain = trim(trim($_ENV['cnf_domain']), '/');
		$tt = rand(0, 1);
		$num = 0;
		$imgurl = '';
		if ($tt == 1) {
			$ttc = rand(1, 106);
			$imgurl = $_SERVER['REQUEST_SCHEME'] . '://' . $cnf_domain . '/h5/img/img (' . $ttc . ').gif';
		} else {
			$num = rand(1, 42);
			$imgurl = $_SERVER['REQUEST_SCHEME'] . '://' . $cnf_domain . '/h5/img/' . $num . '.gif';
		}
		$url = '';
		$data = [];
		if (strstr($imgurl, 'jpg') == false) {
			$url = 'https://api.telegram.org/bot' . $token . '/sendAnimation';
			$data = [
				'chat_id' => $chatId,
				'animation' => $imgurl,
				'caption' => $text,
			];
		} else {
			$url = 'https://api.telegram.org/bot' . $this->token . '/sendPhoto';
			$data = [
				'chat_id' => $chatId,
				'caption' => $text,
				'photo' => $imgurl
			];
		}
		return $this->curl_post($url, $data);
	}



	protected function sendMessage($chatId, $text)
	{
		$url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage';
		$param = "?chat_id=" . $chatId . "&text=" . $text;
		$data = [
			'chat_id' => $chatId,
			'text' => $text,
		];
		return $this->curl_post($url, $data);
	}
}