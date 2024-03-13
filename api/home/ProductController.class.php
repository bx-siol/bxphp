<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class ProductController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		$category_arr = Db::table('pro_category')->where("pid=0 and status=2")->order(['sort' => 'desc', 'id' => 'asc'])->select()->toArray();
		$return_data = ['category_arr' => $category_arr];
		jReturn(1, 'ok', $return_data);
	}
	public function _list1()
	{
		$intime = time(); //
		$cpageSize = 200;
		$pageuser = checkLogin();
		$params = $this->params;
		$where = "1=1 and log.status>1 and log.status<99";
		if ($params['cid']) {
			$where .= " and log.cid={$params['cid']}";
		}

		if (intval($params['ishot']) == 1) {
			$where .= " and log.is_hot=1";
		}

		//mysql -hbxmysqldbrd.rwlb.rds.aliyuncs.com:3306 -P3306 -ubx -pQWE123!@#

		$list1time = Db::query('select count(1) from sys_user'); //
		//Db::query('select * from think_user where status=1');
		$category_arr = 177; //;Db::table('pro_category')->count();
		$c1time = time(); //

		$return_data = [
			'list' => 1,
			'count' => 0,
			'page' => $params['page'] + 1,
			'finished' => false,
			'limit' => $cpageSize,
			'category_arr' => $category_arr,
			'time' => [
				$intime,
				$list1time,
				$c1time,
			]
		];
		jReturn(1, 'ok', $return_data);
	}
	public function _list()
	{
		$intime = time(); //
		$cpageSize = 200;
		$pageuser = checkLogin();
		$params = $this->params;
		$logintime = time(); //
		$params['page'] = intval($params['page']);
		if ($params['page'] < 1) {
			$params['page'] = 1;
		}
		$params['cid'] = intval($params['cid']);

		$where = "1=1 and log.status>1 and log.status<99";
		if ($params['cid']) {
			$where .= " and log.cid={$params['cid']}";
		} else {
			if (intval($params['ishot']) != 1)
				$where .= " and log.cid !=1019";
		}

		if (intval($params['ishot']) == 1) {
			$where .= " and log.is_hot=1";
		}
		$count_item = Db::table('pro_goods log')
			->leftJoin('pro_category c', 'log.cid=c.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['pro_goods' => 'log'], ['*'])
			->view(['pro_category' => 'c'], ['name' => 'category_name'], 'log.cid=c.id', 'LEFT')
			->where($where)
			->order(['log.sort' => 'desc'])
			->page($params['page'], $cpageSize)
			->select()->toArray();
		$list1time = time(); //
		foreach ($list as &$item) {
			$this->goodsItem($item);
			if ($item['covers']) {
				$item['covers'] = [$item['covers'][0]];
			}
			$item['djss'] = time();
		}

		$category_arr = Db::table('pro_category')->where("pid=0 and status=2")->order(['sort' => 'desc', 'id' => 'asc'])->select()->toArray();
		$c1time = time(); //
		$total_page = ceil($count_item['cnt'] / $cpageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $cpageSize,
			'category_arr' => $category_arr,
			'time' => [
				$intime,
				$logintime,
				$list1time,
				$c1time,
			]
		];
		if ($params['page'] < 2) {
		}
		jReturn(1, 'ok', $return_data);
	}

	public function _goods()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		if (!$params['gsn']) {
			jReturn(-1, '缺少参数');
		}
		$item = Db::table('pro_goods')->where("gsn='{$params['gsn']}'")->find();
		if (!$item) {
			jReturn(-1, '不存在相应的记录');
		}
		$this->goodsItem($item);
		$wallet1 = getWallet($pageuser['id'], 1);
		$wallet2 = getWallet($pageuser['id'], 2);
		$wallet3 = getWallet($pageuser['id'], 3);
		//可用代金券
		$now_time = time();
		$coupon_list = rows2arr(Db::table('coupon_list')->where("1=1")->field(['id', 'name', 'cover'])->select()->toArray());

		$cp_where = " uid={$pageuser['id']} and type=1 and status in (1,2) and num > used and ( effective_time=0 or (effective_time>0 and effective_time>{$now_time})) ";
		$coupon_logs = Db::table('coupon_log')->where($cp_where)->field(['id', 'cid', 'money', 'discount', 'num', 'used', 'create_time', 'effective_time'])->select()->toArray();
		$coupon_arr = [];
		$coupon_cids = [];
		if ($coupon_logs) {
			foreach ($coupon_logs as $cp) {
				if (in_array($cp['cid'], $coupon_cids)) {
					continue;
				}
				$gids = json_decode($cp['gids'], true);
				if (!$gids || in_array($item['id'], $gids)) {
					$cp['coupon_name'] = $coupon_list[$cp['cid']]['name'];
					$cp['cover'] = $coupon_list[$cp['cid']]['cover'];
					$cp['money'] = floatval($cp['money']);
					$cp['discount'] = floatval($cp['discount']);

					$coupon_cids[] = $cp['cid'];
					$coupon_arr[] = $cp;
					if (count($coupon_arr) >= 5) {
						break;
					}
				}
			}
		}

		$return_data = [
			'info' => $item,
			'wallet1' => $wallet1,
			'wallet2' => $wallet2,
			'wallet3' => $wallet3,
			'coupon_arr' => $coupon_arr
		];
		jReturn(1, 'ok', $return_data);
	}

	private function goodsItem(&$item)
	{
		$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
		$item['price'] = floatval($item['price']);
		$item['invest_min'] = floatval($item['invest_min']);
		$item['profit_day'] = number_format($item['invest_min'] * ($item['rate'] / 100), 2, '.', '');
		$item['surplus'] = $item['scale'] - $item['invested'];
		$item['djss'] = time();
		if ($item['surplus'] < 0) {
			$item['surplus'] = 0;
		}
		$item['surplus'] = number_format($item['surplus'], 2, '.', '');
		$item['percent'] = '0';
		if ($item['scale'] > 0) {
			$percent = ($item['invested'] + $item['v_invested']) / $item['scale'];
			$item['percent'] = number_format($percent, 2, '.', '');
		}
		$item['covers'] = json_decode($item['covers'], true);
		if (!$item['covers']) {
			$item['covers'] = [];
		}
		unset($item['id']);
	}

	//弃用
	private function invest_new12()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$money = floatval($params['money']);
		$quantity = intval($params['quantity']);
		if ($quantity < 1 || $quantity > 1000) {
			jReturn(-1, 'Incorrect purchase quantity');
		}
		$return_data = [];
		Db::startTrans();
		try {
			$pageuser = Db::table('sys_user')->where("id='{$pageuser['id']}'")->find();
			$item = Db::table('pro_goods')->where("gsn='{$params['gsn']}'")->find();
			if (!$item) {
				jReturn(-1, '不存在相应的产品');
			} else {
				if ($item['djs'] != 0 && $item['djs'] < time()) {
					jReturn(-1, 'The final purchase deadline for the current product has expired');
				}
				if ($item['status'] != 3) {
					if ($item['status'] == 9) {
						//jReturn(-1,'该成品已没有可投资额度');
						jReturn(-1, 'The product has been sold out');
					} elseif ($item['status'] == 2) {
						jReturn(-1, 'Unable to activate during pre-sale');
					} else {
						jReturn(-1, '该产品已下线');
					}
				}
				$money = $quantity * $item['price'];
				//老用户专属购买  以下产品需要已经购买了其他产品才能购买
				if (in_array($item['id'], ['78', '79', '80', '81', '82', '83'])) {
					$user_numold = Db::table('pro_order')->where("uid={$pageuser['id']} and is_give=0 ")->sum('num');
					if ($user_numold <= 0) {
						jReturn(-1, 'Purchase failed, please contact the person in charge');
					}
				}
				//要求用户邀请新人才可以购买
				if ($item['yaoqing'] > 0) {
					// $xjcount = Db::table('fin_paylog log')
					// 	->leftJoin('sys_user su', 'log.uid=su.id')
					// 	->where(' su.pid=' . $pageuser['id'] . ' and log.status=9 and log.is_first =1')->count();

					$xjcount = Db::table('sys_user')->where(' first_pay_day>0 and pid=' . $pageuser['id'])->count();
					if ($xjcount < $item['yaoqing']) {
						jReturn(-1, 'This product needs more than ' . $item['yaoqing'] . ' people in your team to purchase valid products');
					}
				}

				if ($item['invest_limit'] > 0) {
					$user_num = Db::table('pro_order')->where("uid={$pageuser['id']} and gid={$item['id']}  ")->sum('num');
					$user_num1 = Db::table('pro_order')->where("uid={$pageuser['id']} ")->sum('num');
					$p3 = 0;
					if ($user_num1 == 0)
						$p3 = 1;
					if ($user_num + $quantity > $item['invest_limit']) {
						jReturn(-1, 'Purchase quantity exceeds the limit');
					}
				}
			}

			if ($item['is_xskc'] == 1) {
				if ($item['kc'] < $quantity) {
					Db::rollback();
					jReturn(-1, 'The current inventory is insufficient, please reduce the purchase quantity or contact your manager');
				}
				$kc = $item['kc'] - $quantity;
				$kc = $kc > 0 ? $kc : 0;
			}

			$item = Db::table('pro_goods')->where("gsn='{$params['gsn']}'")->lock(true)->find();
			$db_item = ['invested' => $item['invested'] + $money, 'kc' => $kc];
			//更新产品记录
			Db::table('pro_goods')->where("id={$item['id']}")->update($db_item);
			//是否分佣的标识
			$isgive = in_array($item['osn'], ['36049c0f092160d9']) ? 1 : 0;
			if (in_array($item['cid'], [1019, 1031])) {
				$isgive = 1;
			}
			$pro_order = [
				'uid' => $pageuser['id'],
				'osn' => getRsn(),
				'pid' => $pageuser['pid'],
				'cid' => $item['cid'],
				'gid' => $item['id'],
				'days' => $item['days'],
				'rate' => $item['rate'],
				'price' => $item['price'],
				'price1' => $item['price1'],
				'price2' => $item['price2'],
				'p1' => 0,
				'p2' => 0,
				'p3' => $p3,
				'money' => $money,
				'num' => $quantity,
				'create_day' => date('Ymd', NOW_TIME),
				'create_time' => NOW_TIME,
				'create_ip' => CLIENT_IP,
				'is_give' => $isgive,
				'is_exchange' => 0,
			];

			//判断是否积分商品
			if ($item['isgift'] == 1) {
				$wallet3 = Db::table('wallet_list')->where('uid=' . $pageuser['id'] . ' and cid=3')->lock(true)->find(); //积分
				$pro_order['discount'] = 1;
				$pro_order['w1_money'] = 0;
				$pro_order['w2_money'] = 0;
				$w3_money = $quantity * $item['price'];
				if ($wallet3['balance'] < $w3_money) {
					Db::rollback();
					jReturn(-1, 'Your points are insufficient');
				}
				$wallet_data3 = [
					'balance' => $wallet3['balance'] - $w3_money
				];
				//更新积分余额
				Db::table('wallet_list')->where("id={$wallet3["id"]}")->update($wallet_data3);
				//写入流水记录
				$result3 = walletLog([
					'wid' => $wallet3['id'],
					'uid' => $wallet3['uid'],
					'type' => 1019,
					'money' => -$w3_money,
					'ori_balance' => $wallet3['balance'],
					'new_balance' => $wallet_data3['balance'],
					'fkey' => '',
					'remark' => 'Buy:' . $pro_order['osn']
				]);
				if (!$result3) {
					throw new \Exception('流水记录写入失败');
				}
				$res = Db::table('pro_order')->insertGetId($pro_order);
			} else {
				//使用券
				$coupons_money = 0;
				$coupons_discount = 1; //默认不打折
				$coupon = [];
				if ($params['coupon'] != '-1' && $params['coupon']) {
					$coupon = Db::table('coupon_log')->where("id={$params['coupon']}")->lock(true)->find();
					if (!$coupon || $coupon['status'] > 2) {
						jReturn(-1, 'This discount coupon is not available');
					}
					if ($coupon['uid'] != $pageuser['id']) {
						jReturn(-1, 'This discount coupon is not available');
					}
					if ($coupon['num'] <= $coupon['used']) {
						jReturn(-1, 'This discount coupon is not available');
					}
					$gids = json_decode($coupon['gids'], true);
					if ($gids && !in_array($item['id'], $gids)) {
						jReturn(-1, 'This product cannot be used for this coupon');
					}
					if ($coupon['effective_time'] && $coupon['effective_time'] <= NOW_TIME) {
						jReturn(-1, 'This coupon has expired');
					}

					if ($coupon['cid'] == 16) {
						if (!in_array($item['id'], [76, 77, 78, 79, 80, 81])) {
							jReturn(-1, 'This product cannot use the coupon');
						}
					}

					if ($coupon['discount'] > 0) {
						$coupons_discount = $coupon['discount'] / 100;
					} else {
						$coupons_money = $coupon['money'];
					}
				}

				//先检测余额钱包
				$money = $quantity * $item['price'];
				$discount_total = $money * $coupons_discount - $coupons_money;
				if ($discount_total < 0) {
					$discount_total = 0;
				}
				$w1_money = 0;
				$w2_money = 0;
				$wallet1 = Db::table('wallet_list')->where('uid=' . $pageuser['id'] . ' and cid=1')->lock(true)->find(); //积分 //充值钱包
				$wallet2 = Db::table('wallet_list')->where('uid=' . $pageuser['id'] . ' and cid=2')->lock(true)->find(); //积分 //余额钱包
				if (!$wallet1 || !$wallet2) {
					throw new \Exception('钱包获取异常');
				}
				//检测当前用户是否是首次购买
				$check_num = Db::table('pro_order')->where("uid={$pageuser['id']} and is_give=0")->count('id');

				//只允许使用充值钱包
				if (intval($item['buyday']) >= 1) {
					$w1_money = $discount_total;
					$w2_money = 0;
					if (floatval($wallet1['balance']) < $discount_total) {
						Db::rollback();
						jReturn(-1, '您的余额不足');
					}
				} else {
					//首次购买，保留余额钱包
					if ($check_num <= 0) {
						if ($wallet1['balance'] > 0) { //充值钱包有余额
							if ($wallet1['balance'] >= $discount_total) {
								$w1_money = $discount_total;
							} else {
								Db::rollback();
								jReturn(-1, '您的余额不足');
							}
						} else {
							Db::rollback();
							jReturn(-1, '您的余额不足');
						}
					} else {
						if ($wallet2['balance'] > 0) { //余额钱包有余额
							if ($wallet2['balance'] >= $discount_total) {
								$w2_money = $discount_total;
								// $discount = 1;
							} else {
								//余额钱包不足
								$w1_money = ($discount_total - $wallet2['balance']);
								$w2_money = $wallet2['balance'];
							}
						} else {
							$w1_money = $discount_total; //全部使用充值钱包
						}
					}
				}

				$pro_order['discount'] = $coupons_discount;
				$pro_order['w1_money'] = $w1_money;
				$pro_order['w2_money'] = $w2_money;
				$res = Db::table('pro_order')->insertGetId($pro_order);

				if ($wallet1['balance'] < $w1_money) {
					Db::rollback();
					jReturn(-1, '您的余额不足');
				}
				if ($wallet2['balance'] < $w2_money) {
					Db::rollback();
					jReturn(-1, '您的余额不足');
				}


				//更新券使用
				if ($coupon) {
					$coupon_log = [
						'used' => $coupon['used'] + 1
					];
					if ($coupon_log['used'] >= $coupon['num']) {
						$coupon_log['status'] = 9;
					} else {
						$coupon_log['status'] = 2;
					}
					Db::table('coupon_log')->where("id={$coupon['id']}")->update($coupon_log);
					$coupon_used = [
						'cid' => $coupon['cid'],
						'clid' => $coupon['id'],
						'uid' => $pro_order['uid'],
						'gid' => $pro_order['gid'],
						'oid' => $pro_order['id'],
						'num' => 1,
						'discount' => $coupon['discount'],
						'money' => $coupon['money'],
						'create_day' => date('Ymd', NOW_TIME),
						'create_time' => NOW_TIME
					];
					Db::table('coupon_used')->insertGetId($coupon_used);
				}


				if ($w2_money > 0) {
					$wallet_data2 = [
						'balance' => $wallet2['balance'] - $w2_money
					];
					//更新钱包余额
					Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
					//写入流水记录
					$result2 = walletLog([
						'wid' => $wallet2['id'],
						'uid' => $wallet2['uid'],
						'type' => 1,
						'money' => -$w2_money,
						'ori_balance' => $wallet2['balance'],
						'new_balance' => $wallet_data2['balance'],
						'fkey' => $res,
						'remark' => 'Buy:' . $pro_order['osn']
					]);
					if (!$result2) {
						throw new \Exception('流水记录写入失败');
					}
				}
				if ($w1_money > 0) {
					$wallet_data1 = [
						'balance' => $wallet1['balance'] - $w1_money
					];
					//更新钱包余额
					Db::table('wallet_list')->where("id={$wallet1['id']}")->update($wallet_data1);
					//写入流水记录
					$result1 = walletLog([
						'wid' => $wallet1['id'],
						'uid' => $wallet1['uid'],
						'type' => 1,
						'money' => -$w1_money,
						'ori_balance' => $wallet1['balance'],
						'new_balance' => $wallet_data1['balance'],
						'fkey' => $res,
						'remark' => 'Buy:' . $pro_order['osn']
					]);
					if (!$result1) {
						throw new \Exception('流水记录写入失败');
					}
				}

				$sjcq = 0;
				$user = Db::table('sys_user')->where("id={$pageuser['id']}")->lock(true)->find();
				$sys_user = [
					'total_invest' => $user['total_invest'] + $w1_money + $w2_money,
					'total_invest2' => $user['total_invest2'] + $discount_total
				];
				Db::table('sys_user')->where("id={$user['id']}")->update($sys_user);

				$lottery = Db::table('gift_lottery')->where("id=1")->find();
				if ($item['price'] >= $lottery['lottery_min']) {
					$tttttt = $quantity * intval($item['cjcs']);
					Db::table('sys_user')->where("id={$pageuser['id']}")->inc('lottery', $tttttt)->update();
					//检测当前用户是否是首次购买 送上级抽奖次数
					if ($check_num <= 0) {
						$puser = Db::table('sys_user')->where('id=' . $pageuser['pid'])->lock(true)->findOrEmpty();
						$sjcq = Db::table('sys_user')->where("id=" . $puser['id'])->update(['lottery' => $puser['lottery'] + intval($item['sjcjcs'])]);

						if ($item['Integral'] > 0 && $puser != null) {

							//更新上级的积分钱包
							$wallet3 = getWallet($puser['id'], 3);
							if ($wallet3 == []) {
								$db_item = [
									'uid' => $puser['id'],
									'waddr' => getRsn(),
									'cid' => 3,
									'balance' => 0,
									'create_time' => time(),

								];
								Db::table('wallet_list')->insertGetId($db_item);
								$wallet3 = $db_item;
							}

							$wallet3 = Db::table('wallet_list')->where('uid=' . $puser['id'] . ' and cid=3')->lock(true)->find(); //积分  
							$wallet_data3 = [
								'balance' => $wallet3['balance'] + $item['Integral']
							];
							//更新钱包余额
							Db::table('wallet_list')->where("id={$wallet3['id']}")->update($wallet_data3);
							//写入流水记录
							$result3 = walletLog([
								'wid' => $wallet3['id'],
								'uid' => $wallet3['uid'],
								'type' => 1019,
								'money' => $item['Integral'],
								'ori_balance' => $wallet3['balance'],
								'new_balance' => $wallet_data3['balance'],
								'fkey' => $res,
								'remark' => 'Buy:' . $pro_order['osn']
							]);
							if (!$result3) {
								throw new \Exception('流水记录写入失败');
							}
						}
					}
				}

				//检测当前用户是否是首次购买 
				if ($check_num <= 0) {
					$pro_ordertopuser = [];
					//送推荐人
					if ($item['gifttopuser']) {
						$gifttopuser = Db::table('pro_goods')->where("id={$item['gifttopuser']}")->find();
						$puser = Db::table('sys_user')->where("id={$pageuser['pid']}")->find();
						$pro_ordertopuser = [
							'uid' => $puser['id'],
							'osn' => getRsn(),
							'pid' => $puser['pid'],
							'cid' => $gifttopuser['cid'],
							'gid' => $gifttopuser['id'],
							'days' => $gifttopuser['days'],
							'rate' => $gifttopuser['rate'],
							'price' => $gifttopuser['price'],
							'price1' => $gifttopuser['price1'],
							'price2' => $gifttopuser['price2'],
							'p1' => 1,
							'p2' => 1,
							'p3' => 1,
							'money' => $gifttopuser['price'],
							'num' => 1,
							'create_day' => date('Ymd', NOW_TIME),
							'create_time' => NOW_TIME,
							'create_ip' => CLIENT_IP,
							'is_give' => 1,
							'is_exchange' => 0,
						];
					}
					if ($pro_ordertopuser != [])
						Db::table('pro_order')->insertGetId($pro_ordertopuser);
					//首次购买送自己
					if ($item['price1'] > 0) {
						$wallet2 = Db::table('wallet_list')->where("uid={$pageuser['id']} and cid=2")->lock(true)->find();
						$wallet_data2 = [
							'balance' => $wallet2['balance'] + $item['price1']
						];
						//更新钱包余额
						Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
						//写入流水记录
						$result1 = walletLog([
							'wid' => $wallet2['id'],
							'uid' => $wallet2['uid'],
							'type' => 10,
							'money' => $item['price1'],
							'ori_balance' => $wallet2['balance'],
							'new_balance' => $wallet_data2['balance'],
							'fkey' => $res,
							'remark' => 'one Buy:' . $pro_order['osn']
						]);
					}
					//首次购买送上级
					if ($item['price2'] > 0) {
						$wallet2 = Db::table('wallet_list')->where("uid={$pageuser['pid']} and cid=2")->lock(true)->find();
						$wallet_data2 = [
							'balance' => $wallet2['balance'] + $item['price2']
						];
						//更新钱包余额
						Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
						//写入流水记录
						$result1 = walletLog([
							'wid' => $wallet2['id'],
							'uid' => $wallet2['uid'],
							'type' => 10,
							'money' => $item['price2'],
							'ori_balance' => $wallet2['balance'],
							'new_balance' => $wallet_data2['balance'],
							'fkey' => $res,
							'remark' => 'Team Buy:' . $pro_order['osn']
						]);
					}
				} else {
					//复购送自己
					if ($item['price0'] > 0) {
						$wallet2 = Db::table('wallet_list')->where("uid={$pageuser['id']} and cid=2")->lock(true)->find();
						$wallet_data2 = [
							'balance' => $wallet2['balance'] + $item['price0']
						];
						//更新钱包余额
						Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
						//写入流水记录
						$result1 = walletLog([
							'wid' => $wallet2['id'],
							'uid' => $wallet2['uid'],
							'type' => 10,
							'money' => $item['price0'],
							'ori_balance' => $wallet2['balance'],
							'new_balance' => $wallet_data2['balance'],
							'fkey' => $res,
							'remark' => 'Repeat purchase:' . $pro_order['osn']
						]);
					}
				}

				$pro_ordertoself = [];
				//送自己
				if ($item['gifttoself']) {
					$gifttoself = Db::table('pro_goods')->where("id={$item['gifttoself']}")->find();
					$pro_ordertoself = [
						'uid' => $pageuser['id'],
						'osn' => getRsn(),
						'pid' => $pageuser['pid'],
						'cid' => $gifttoself['cid'],
						'gid' => $gifttoself['id'],
						'days' => $gifttoself['days'],
						'rate' => $gifttoself['rate'],
						'price' => $gifttoself['price'],
						'price1' => $gifttoself['price1'],
						'price2' => $gifttoself['price2'],
						'p1' => 1,
						'p2' => 1,
						'p3' => 1,
						'money' => $gifttoself['price'],
						'num' => 1,
						'create_day' => date('Ymd', NOW_TIME),
						'create_time' => NOW_TIME,
						'create_ip' => CLIENT_IP,
						'is_give' => 1,
						'is_exchange' => 0,
					];
				}
				if ($pro_ordertoself != [])
					Db::table('pro_order')->insertGetId($pro_ordertoself);
				if ($item['selfintegral'] > 0) {
					//更新上级的积分钱包
					$wallet3 = getWallet($pageuser['id'], 3);
					if ($wallet3 == []) {
						$db_item = [
							'uid' => $pageuser['id'],
							'waddr' => getRsn(),
							'cid' => 3,
							'balance' => 0,
							'create_time' => time(),
						];
						Db::table('wallet_list')->insertGetId($db_item);
						$wallet3 = $db_item;
					}
					$wallet3 = Db::table('wallet_list')->where('uid=' . $pageuser['id'] . ' and cid=3')->lock(true)->find(); //积分  
					$wallet_data3 = [
						'balance' => $wallet3['balance'] + $item['selfintegral']
					];
					//更新钱包余额
					Db::table('wallet_list')->where("id={$wallet3['id']}")->update($wallet_data3);
					//写入流水记录
					$result3 = walletLog([
						'wid' => $wallet3['id'],
						'uid' => $wallet3['uid'],
						'type' => 1019,
						'money' => $item['selfintegral'],
						'ori_balance' => $wallet3['balance'],
						'new_balance' => $wallet_data3['balance'],
						'fkey' => $res,
						'remark' => 'Buy:' . $pro_order['osn']
					]);
					if (!$result3) {
						throw new \Exception('流水记录写入失败');
					}
				}
				if ($item['selfbg'] > 0) {
					$wallet2 = Db::table('wallet_list')->where('uid=' . $pageuser['id'] . ' and cid=2')->lock(true)->find();
					$wallet_data2 = [
						'balance' => $wallet2['balance'] + $item['selfbg']
					];
					//更新钱包余额
					Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
					//写入流水记录
					$result2 = walletLog([
						'wid' => $wallet2['id'],
						'uid' => $wallet2['uid'],
						'type' => 11,
						'money' => $item['selfbg'],
						'ori_balance' => $wallet2['balance'],
						'new_balance' => $wallet_data2['balance'],
						'fkey' => $res,
						'remark' => 'Buy:' . $pro_order['osn']
					]);
					if (!$result2) {
						throw new \Exception('流水记录写入失败');
					}
				}
			}
			Db::commit();
			$return_data['osn'] = $pro_order['osn'];
			$return_data['osn1'] = $sjcq;
		} catch (\Exception $e) {
			Db::rollback();
			jReturn(-1, '系统繁忙请稍后再试', ['e' => json_encode($e)]);
		}
		jReturn(1, 'Successful purchase', $return_data);
	}


	/*******************购买产品相关***********************/
	//购买产品
	public function _invest()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$money = floatval($params['money']);
		$quantity = intval($params['quantity']);
		if ($quantity < 1 || $quantity > 1000)
			jReturn(-1, 'Incorrect purchase quantity');
		$return_data = [];
		Db::startTrans();
		try {
			$item = $this->redis->get('pro_goods_' . $params['gsn']);
			$money = $quantity * $item['price'];
			if (!$item) {
				$item = Db::table('pro_goods')->where("gsn='{$params['gsn']}'")->find();
				if (!$item)
					jReturn(-1, '不存在相应的产品');
				$this->redis->set('pro_goods_' . $params['gsn'], $item, 60 * 60);
			}
			$pro_order = $this->reinvest_date($params, $pageuser, $item, $quantity, $money);
			// $return_data['err'] = $pro_order['err'];
			// $return_data['u'] = $pageuser;
			//判断是否积分商品
			if ($item['pointshop'] == 1) {
				$this->invest_date_gift($pageuser, $item, $quantity, $pro_order);
			} else {
				$check_num = Db::table('pro_order')->where("uid={$pageuser['id']} and is_give=0")->count('id');
				$this->invest_date($params, $pageuser, $item, $quantity, $check_num, $pro_order);
				$this->Productgift($item, $quantity, $pageuser, $check_num, $pro_order);
			}
			Db::commit();
			$return_data['osn'] = $pro_order['osn'];
		} catch (\Exception $e) {
			Db::rollback();
			jReturn(-1, '系统繁忙请稍后再试', ['e' => json_encode($e)]);
		}
		jReturn(1, 'Successful purchase', $return_data);
	}

	//验证产品购买条件
	public function reinvest_date($params, $pageuser, $item, $quantity, $money)
	{
		$err = "";
		$p3 = 0;
		if ($item['djs'] != 0 && $item['djs'] < time()) {
			jReturn(-1, 'The final purchase deadline for the current product has expired');
		}
		if ($item['status'] != 3) {
			if ($item['status'] == 9) {
				jReturn(-1, 'The product has been sold out');
			} elseif ($item['status'] == 2) {
				jReturn(-1, 'Unable to activate during pre-sale');
			} else {
				jReturn(-1, 'The product has been sold out');
			}
		}
		//老用户专属购买  以下产品需要已经购买了其他产品才能购买
		// if (in_array($item['id'], ['78', '79', '80', '81', '82', '83'])) {
		// 	$user_numold = Db::table('pro_order')->where("uid={$pageuser['id']} and is_give=0 ")->sum('num');
		// 	if ($user_numold <= 0) {
		// 		jReturn(-1, 'Purchase failed, please contact the person in charge');
		// 	}
		// }
		//要求用户邀请新人才可以购买
		if ($item['yaoqing'] > 0) {
			$xjcount = Db::table('sys_user')->where(' first_pay_day>0 and pid=' . $pageuser['id'])->count();
			if ($xjcount < $item['yaoqing']) {
				jReturn(-1, 'This product needs more than ' . $item['yaoqing'] . ' people in your team to purchase valid products');
			}
		}

		if ($item['invest_limit'] > 0) {
			// $err .= "uid={$pageuser['id']} and gid={$item['id']}";
			$user_num = Db::table('pro_order')->where("uid={$pageuser['id']} and gid={$item['id']}")->sum('num');
			$user_num1 = Db::table('pro_order')->where("uid={$pageuser['id']} ")->sum('num');
			if ($user_num1 == 0)
				$p3 = 1;
			if ($user_num + $quantity > $item['invest_limit']) {
				jReturn(-1, 'Purchase quantity exceeds the limit');
			}
		}

		if ($item['is_xskc'] == 1) {
			if ($item['kc'] < $quantity) {
				jReturn(-1, 'The current inventory is insufficient, please reduce the purchase quantity or contact your manager');
			}
			$kc = $item['kc'] - $quantity;
			$kc = $kc > 0 ? $kc : 0;
		}

		$item = Db::table('pro_goods')->where("gsn='{$params['gsn']}'")->lock(true)->find();
		$db_item = ['invested' => $item['invested'] + $money, 'kc' => $kc];
		//更新产品记录
		Db::table('pro_goods')->where("id={$item['id']}")->update($db_item);
		//是否分佣的标识
		$isgive = $item['gift'];

		return [
			'uid' => $pageuser['id'],
			'osn' => getRsn(),
			'pid' => $pageuser['pid'],
			'cid' => $item['cid'],
			'gid' => $item['id'],
			'days' => $item['days'],
			'rate' => $item['rate'],
			'price' => $item['price'],
			'price1' => $item['price1'],
			'price2' => $item['price2'],
			'p1' => 0,
			'p2' => 0,
			'p3' => $p3,
			'money' => $money,
			'num' => $quantity,
			'create_day' => date('Ymd', NOW_TIME),
			'create_time' => NOW_TIME,
			'create_ip' => CLIENT_IP,
			'is_give' => $isgive,
			'is_exchange' => 0,
			// 'err' => $err,
		];

	}

	//积分商品
	public function invest_date_gift($pageuser, $item, $quantity, $pro_order)
	{
		$wallet3 = Db::table('wallet_list')->where('uid=' . $pageuser['id'] . ' and cid=3')->lock(true)->find(); //积分
		$pro_order['discount'] = 1;
		$pro_order['w1_money'] = 0;
		$pro_order['w2_money'] = 0;
		$w3_money = $quantity * $item['price'];
		if ($wallet3['balance'] < $w3_money)
			jReturn(-1, 'Your points are insufficient');
		updateWalletBalanceAndLog($pageuser['id'], -$w3_money, 3, 1019, 'Buy:' . $pro_order['osn']);
		Db::table('pro_order')->insertGetId($pro_order);
	}

	//计算商品价格以及插入数据库
	public function invest_date($params, $pageuser, $item, $quantity, $check_num, $pro_order)
	{
		//使用券
		$coupons_money = 0;
		$coupons_discount = 1; //默认不打折
		$coupon = [];
		if ($params['coupon'] != '-1' && $params['coupon']) {
			$coupon = Db::table('coupon_log')->where("id={$params['coupon']}")->lock(true)->find();
			if (!$coupon || $coupon['status'] > 2) {
				jReturn(-1, 'This discount coupon is not available');
			}
			if ($coupon['uid'] != $pageuser['id']) {
				jReturn(-1, 'This discount coupon is not available');
			}
			if ($coupon['num'] <= $coupon['used']) {
				jReturn(-1, 'This discount coupon is not available');
			}
			$gids = json_decode($coupon['gids'], true);
			if ($gids && !in_array($item['id'], $gids)) {
				jReturn(-1, 'This product cannot be used for this coupon');
			}
			if ($coupon['effective_time'] && $coupon['effective_time'] <= NOW_TIME) {
				jReturn(-1, 'This coupon has expired');
			}

			if ($coupon['cid'] == 16) {
				if (!in_array($item['id'], [76, 77, 78, 79, 80, 81])) {
					jReturn(-1, 'This product cannot use the coupon');
				}
			}

			if ($coupon['discount'] > 0) {
				$coupons_discount = $coupon['discount'] / 100;
			} else {
				$coupons_money = $coupon['money'];
			}
		}

		//先检测余额钱包
		$money = $quantity * $item['price'];
		$discount_total = $money * $coupons_discount - $coupons_money;
		if ($discount_total < 0) {
			$discount_total = 0;
		}
		$w1_money = 0;
		$w2_money = 0;
		//检测当前用户是否是首次购买

		$wallet1 = Db::table('wallet_list')->where('uid=' . $pageuser['id'] . ' and cid=1')->lock(true)->find();   //充值钱包
		$wallet2 = Db::table('wallet_list')->where('uid=' . $pageuser['id'] . ' and cid=2')->lock(true)->find();   //余额钱包
		if (!$wallet1 || !$wallet2) {
			throw new \Exception('钱包获取异常');
		}

		//只允许使用充值钱包
		if (intval($item['buyday']) >= 1) {
			$w1_money = $discount_total;
			$w2_money = 0;
			if (floatval($wallet1['balance']) < $discount_total) {
				jReturn(-1, '您的余额不足');
			}
		} else {
			//首次购买，保留余额钱包
			if ($check_num <= 0) {
				if ($wallet1['balance'] > 0) { //充值钱包有余额
					if ($wallet1['balance'] >= $discount_total) {
						$w1_money = $discount_total;
					} else {
						jReturn(-1, '您的余额不足');
					}
				} else {
					jReturn(-1, '您的余额不足');
				}
			} else {
				if ($wallet2['balance'] > 0) { //余额钱包有余额
					if ($wallet2['balance'] >= $discount_total) {
						$w2_money = $discount_total;
						// $discount = 1;
					} else {
						//余额钱包不足
						$w1_money = ($discount_total - $wallet2['balance']);
						$w2_money = $wallet2['balance'];
					}
				} else {
					$w1_money = $discount_total; //全部使用充值钱包
				}
			}
		}

		$pro_order['discount'] = $coupons_discount;
		$pro_order['w1_money'] = $w1_money;
		$pro_order['w2_money'] = $w2_money;
		Db::table('pro_order')->insertGetId($pro_order);
		if ($wallet1['balance'] < $w1_money) {
			jReturn(-1, '您的余额不足');
		}
		if ($wallet2['balance'] < $w2_money) {
			jReturn(-1, '您的余额不足');
		}

		//更新券使用
		if ($coupon) {
			$coupon_log = [
				'used' => $coupon['used'] + 1
			];
			if ($coupon_log['used'] >= $coupon['num']) {
				$coupon_log['status'] = 9;
			} else {
				$coupon_log['status'] = 2;
			}
			Db::table('coupon_log')->where("id={$coupon['id']}")->update($coupon_log);
			$coupon_used = [
				'cid' => $coupon['cid'],
				'clid' => $coupon['id'],
				'uid' => $pro_order['uid'],
				'gid' => $pro_order['gid'],
				'oid' => $pro_order['id'],
				'num' => 1,
				'discount' => $coupon['discount'],
				'money' => $coupon['money'],
				'create_day' => date('Ymd', NOW_TIME),
				'create_time' => NOW_TIME
			];
			Db::table('coupon_used')->insertGetId($coupon_used);
		}

		if ($w2_money > 0) {
			updateWalletBalanceAndLog($pageuser['id'], -$w2_money, 2, 1, 'Buy:' . $pro_order['osn']);
		}
		if ($w1_money > 0)
			updateWalletBalanceAndLog($pageuser['id'], -$w1_money, 1, 1, 'Buy:' . $pro_order['osn']);

		$user = Db::table('sys_user')->where("id={$pageuser['id']}")->lock(true)->find();
		$sys_user = [
			'total_invest' => $user['total_invest'] + $w1_money + $w2_money,
			'total_invest2' => $user['total_invest2'] + $discount_total
		];
		Db::table('sys_user')->where("id={$user['id']}")->update($sys_user);
	}

	//购买后赠送的相关业务
	public function Productgift($item, $quantity, $pageuser, $check_num, $pro_order)
	{
		writeLog('---------------------------------------------------------------', 'bobopay1');
		//送抽奖
		$lotterynum = $quantity * intval($item['cjcs']);
		Db::table('sys_user')->where("id={$pageuser['id']}")->inc('lottery', $lotterynum)->update();
		//增加中奖记录
		$prizesList = Db::table('gift_prize')->order('probability')->select();

		$prize_arr = array();
		foreach ($prizesList as $k) {
			if($k['probability'] > 0)
				array_push($prize_arr,$k);
		}

		$prizeEmpty = array();
		foreach ($prizesList as $k) {
			if($k['type'] == 4)
				array_push($prizeEmpty,$k);
		}

		for ($i = 0; $i < $lotterynum; $i++) 
		{
			$prizeArr = array();
			foreach ($prize_arr as $k) {
				if($k['buyAmountStart'] >= 0 && $k['buyAmountEnd'] >0 && $k['buyAmountStart'] <= $pro_order['money'] && $pro_order['money'] <= $k['buyAmountEnd'])
					array_push($prizeArr,$k);
			}

			if(count($prizeArr) > 1)
			{
				shuffle($prizeArr);
				$prize = $prizeArr[0];
			}
			if(count($prizeArr) == 1)
			{
				$prize = $prizeArr[0];
			}
			if(empty($prize))
			{
				//查询除概率大于0的奖品
				foreach($prize_arr as $item)
					$total += $item['probability'] * 100;				
				
				$rand = mt_rand(1, $total);
				foreach($prize_arr as $item)
				{
					$count += $item['probability'] * 100;
					if($rand <= $count)
					{
						$prize = $item;
						break;
					}
				}
			}
			if(empty($prize))
				$prize = $prizeEmpty;
			
			// $db_data = [
			// 	'uid' => $pageuser['id'],
			// 	'type' => $prize['type'],
			// 	'money' => 0,
			// 	'gid' => $prize['gid'],
			// 	'coupon_id' => $prize['coupon_id'],
			// 	'prize_name' => $prize['name'],
			// 	'prize_cover' => $prize['cover'],
			// 	'remark' => $prize['remark'],
			// 	'create_time' => NOW_TIME,
			// 	'create_day' => date('Ymd', NOW_TIME),
			// 	'create_ip' => '',
			// 	'split_time' => NOW_TIME,
			// 	'order_money' => $pro_order['money'],
			// 	'is_user' => 0,
			// 	'gift_prize_id' => $prize['id'],
			// ];
			// Db::table('gift_prize_log')->insertGetId($db_data);	
		}

		//检测当前用户是否是首次购买 
		if ($check_num <= 0) {
			$puser = Db::table('sys_user')->where('id=' . $pageuser['pid'])->find();//送上级抽奖次数
			Db::table('sys_user')->where("id=" . $puser['id'])->update(['lottery' => $puser['lottery'] + intval($item['sjcjcs'])]);
			if ($item['Integral'] > 0 && $puser != null)   //首次购买送上级积分 
				updateWalletBalanceAndLog($puser['id'], $item['Integral'], 3, 1019, 'Team Buy:' . $pro_order['osn']);
			//送推荐人产品
			if ($item['gifttopuser']) {
				$gifttopuser = Db::table('pro_goods')->where("id={$item['gifttopuser']}")->find();
				Db::table('pro_order')->insertGetId([
					'uid' => $puser['id'],
					'osn' => getRsn(),
					'pid' => $puser['pid'],
					'cid' => $gifttopuser['cid'],
					'gid' => $gifttopuser['id'],
					'days' => $gifttopuser['days'],
					'rate' => $gifttopuser['rate'],
					'price' => $gifttopuser['price'],
					'price1' => $gifttopuser['price1'],
					'price2' => $gifttopuser['price2'],
					'p1' => 1,
					'p2' => 1,
					'p3' => 1,
					'money' => $gifttopuser['price'],
					'num' => 1,
					'create_day' => date('Ymd', NOW_TIME),
					'create_time' => NOW_TIME,
					'create_ip' => CLIENT_IP,
					'is_give' => 1,
					'is_exchange' => 0,
				]);
			}
			//首次购买送自己
			if ($item['price1'] > 0)
				updateWalletBalanceAndLog($pageuser['id'], $item['price1'], 2, 10, 'First Buy:' . $pro_order['osn']);

			//首次购买送上级
			if ($item['price2'] > 0)
				updateWalletBalanceAndLog($puser['id'], $item['price1'], 2, 10, 'Team First Buy:' . $pro_order['osn']);

		} else {
			if ($item['price0'] > 0)//复购送自己
				updateWalletBalanceAndLog($pageuser['id'], $item['price0'], 2, 10, 'Repeat purchase:' . $pro_order['osn']);
		}

		// 送自己产品
		if ($item['gifttoself']) {
			$giftitem = Db::table('pro_goods')->where("id={$item['gifttoself']}")->find();
			Db::table('pro_order')->insertGetId([
				'uid' => $pageuser['id'],
				'osn' => getRsn(),
				'pid' => $pageuser['pid'],
				'cid' => $giftitem['cid'],
				'gid' => $giftitem['id'],
				'days' => $giftitem['days'],
				'rate' => $giftitem['rate'],
				'price' => $giftitem['price'],
				'price1' => $giftitem['price1'],
				'price2' => $giftitem['price2'],
				'p1' => 1,
				'p2' => 1,
				'p3' => 1,
				'money' => $giftitem['price'],
				'num' => 1,
				'create_day' => date('Ymd', NOW_TIME),
				'create_time' => NOW_TIME,
				'create_ip' => CLIENT_IP,
				'is_give' => 1,
				'is_exchange' => 0,
			]);
		}

		if ($item['selfintegral'] > 0)   //送自己积分 
			updateWalletBalanceAndLog($pageuser['id'], $item['selfintegral'], 3, 1019, 'Buy:' . $pro_order['osn']);

		if ($item['selfbg'] > 0)  //送自己余额 不管什么情况都送
			updateWalletBalanceAndLog($pageuser['id'], $item['selfbg'], 2, 10, 'Buy:' . $pro_order['osn']);
		$this->afterPurchase($pageuser, $item);
	}

	//循环奖励
	public function afterPurchase($user, $product)
	{
		// 确定今天日期，用于每天重置奖励
		$today = date("Y-m-d");
		// 如果没有推荐人，不进行操作
		if (empty($user['pid']))
			return;
		$pid = $user['pid'];
		// 当天推荐人数，使用Redis记录，可以快速读写，同时方便每天重置
		$refCountKey = "refloopCount:{$pid}:{$product['id']}:{$today}";
		$refCount = $this->redis->get($refCountKey);
		if (!$refCount)
			$refCount = 0;
		// 确定奖励等级
		$level = ($refCount % 5) + 1;
		$rewardField = "loop{$level}";
		$rewardAmount = $product[$rewardField];
		if ($rewardAmount <= 0)
			return; // 奖励值为0或未设置，不进行操作 
		// 更新推荐人数
		$this->redis->set($refCountKey, ++$refCount, getEndOfDay());
		// 检查是否需要审核
		$globalLoopConfig = getConfig('cnf_global_loop');
		if ($globalLoopConfig > 0)
			// 进入审核流程
			$this->auditReward($rewardAmount, $user);
		else
			// 更新钱包余额 记录奖励发放日志
			updateWalletBalanceAndLog($pid, $rewardAmount, 2, 151, "Received level {$level} reward for product {$product['id']}");
	}

	//审核循环奖励
	public function auditReward($rewardAmount, $user)
	{
		Db::table('pro_audit')->insertGetId([
			'user_id' => $user['id'],//发起人
			'touser_id' => $user['pid'],//受益人
			'status' => 0,
			'time' => time(),
			'remark' => "",
			'type' => 1,
			'amount' => $rewardAmount,
			'pidg1' => $user['pidg1'],
			'pidg2' => $user['pidg2'],
		]);
	}

	/*******************购买产品相关***********************/



	public function _order()
	{
		$pageSizec = 100;
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);
		if ($params['page'] < 1) {
			$params['page'] = 1;
		}

		$where = "log.uid={$pageuser['id']} and log.status<99";

		if ($params['status']) {
			$where .= ' and log.status=' . $params['status'];
		}

		$count_item = Db::table('pro_order log')
			->leftJoin('pro_goods g', 'log.gid=g.id')
			->fieldRaw('count(1) as cnt,sum(money) as money')
			->where($where)
			->find();

		$list = Db::view(['pro_order' => 'log'], ['osn', 'status', 'days', 'price', 'num', 'money', 'rate', 'create_time', 'create_day', 'reward_day', 'total_reward', 'total_days'])
			->view(['pro_goods' => 'g'], ['name' => 'goods_name', 'icon', 'covers'], 'log.gid=g.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $pageSizec)
			->select()->toArray();

		$now_day = date('Ymd');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i', $item['create_time']);
			$item['money'] = floatval($item['money']);
			$item['receive'] = 0;
			if ($item['reward_day']) {
				if ($item['reward_day'] < $now_day) {
					$item['receive'] = 1;
				}
			} else {
				if ($item['create_day'] < $now_day) {
					$item['receive'] = 1;
				}
			}
			if ($item['status'] != 1) {
				$item['receive'] = 0;
			}
		}
		$total_page = ceil($count_item['cnt'] / $pageSizec);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'money' => number_format($count_item['money'], 2, '.', ''),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $pageSizec
		];
		if ($params['page'] < 2) {
		}
		jReturn(1, 'ok', $return_data);
	}

	/**
	 * 时间距今多少时间
	 * @author 18y
	 * @anotherdate 2018-06-19T18:23:03+0800
	 * @return [type] [description]
	 */

	function makeTimeAgo($time)
	{
		return floor((time() - $time) / 86400);
	}


	public function _ccsolxg()
	{
		$list = Db::table('sys_user')->where("gid=71")->order(['id' => 'desc'])->select()->toArray();
		$sql = '';
		$res = 0;
		foreach ($list as $user) {

			// CREATE TABLE IF NOT EXISTS wallet_log_{$user['id']} LIKE wallet_log;
			// ALTER TABLE wallet_log_{$user['id']} MODIFY COLUMN `id` int(11) NOT NULL FIRST;
			// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
			// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
			// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
			// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
			// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
			// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;
			// CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;  
			// $sql  = "CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward;";
			// $res += Db::execute($sql);

			// $sql  = "CREATE TABLE IF NOT EXISTS pro_reward_{$user['id']} LIKE pro_reward_;"; 创建表
			// $sql  = "ALTER TABLE pro_reward_{$user['id']} MODIFY COLUMN `id` int(11) NOT NULL FIRST;"; 修改表主键
			// $sql  = "TRUNCATE TABLE pro_reward_{$user['id']}"; 清空表
			// $sql  = " INSERT INTO pro_reward_{$user['id']} SELECT * FROM pro_reward WHERE pidg1={$user['id']};";//同步数据

			if ($user['id'] != 954439)
				$res += Db::execute($sql);
		}
		jReturn(0, "$res");
	}

	//领取收益
	public function _receiveProfit()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		if (!$params['osn']) {
			jReturn(-1, '缺少参数');
		}
		$now_time = NOW_TIME;
		$now_day = date('Ymd', NOW_TIME);

		$project = getPset('project');
		$commission_arr = [];
		$com_arr = explode(',', $project['commission']);
		foreach ($com_arr as $cv) {
			$cv_arr = explode('=', $cv);
			$cv_level = intval($cv_arr[0]);
			$cv_val = floatval($cv_arr[1]);
			if ($cv_level < 1 || $cv_val < 0) {
				continue;
			}
			$commission_arr[$cv_level] = $cv_val;
		}

		Db::startTrans();
		try {
			$item = Db::table('pro_order')->where("osn='{$params['osn']}'")->lock(true)->find();
			$pageuser = Db::table('sys_user')->where('id=' . $pageuser['id'])->find();
			if ($item['uid'] != $pageuser['id']) {
				jReturn(-1, '不存在相应的订单');
			}
			if ($item['status'] != 1) {
				jReturn(-1, 'Currently unavailable');
			}
			if ($item['reward_day']) {
				if ($item['reward_day'] >= $now_day) {
					jReturn(-1, 'Currently unavailable2');
				}
			} else {
				if ($item['create_day'] >= $now_day) {
					jReturn(-1, 'Currently unavailable3');
				}
			}


			$pro_order = [];
			$reward = ($item['rate'] / 100) * $item['money'];

			$item1 = Db::table('pro_goods')->where("id='{$item['gid']}'")->find();
			$dayout = intval($item1['dayout']);
			// 如果设置了到期时间，一次性到期
			if ($dayout > 0) {
				if (intval($item['total_reward']) > 0) {
					jReturn(-1, 'You have already received the income');
				}
				if ($this->makeTimeAgo($item['create_time']) <= $dayout) {
					jReturn(-1, 'It is not time to collect');
				}
				$pro_order = [
					'reward_time' => $now_time,
					'reward_day' => $now_day,
					'total_reward' => $item['total_reward'] + $reward,
					'total_days' => $item['days'],
				];
			} else {
				$pro_order = [
					'reward_time' => $now_time,
					'reward_day' => $now_day,
					'total_reward' => $item['total_reward'] + $reward,
					'total_days' => $item['total_days'] + 1,
				];
			}

			if ($pro_order == []) {
				jReturn(-1, '系统繁忙请稍后再试');
			}

			if ($pro_order['total_days'] >= $item['days']) {
				$pro_order['status'] = 9;
			}
			Db::table('pro_order')->where("id={$item['id']}")->update($pro_order);
			$wallet = getWallet($item['uid'], 2);
			if (!$wallet) {
				throw new \Exception('钱包获取异常');
			}
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$wallet_data = [
				'balance' => $wallet['balance'] + $reward
			];
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 6,
				'money' => $reward,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => $item['id'],
				'remark' => 'Profit:' . $item['osn']
			]);
			if (!$result) {
				throw new \Exception('流水记录写入失败');
			}
			//写入收益记录
			$pro_reward = [
				'uid' => $item['uid'],
				'oid' => $item['id'],
				'osn' => $item['osn'],
				'type' => 1,
				'base_money' => $item['money'],
				'rate' => $item['rate'],
				'money' => $reward,
				'remark' => $item['osn'],
				'create_time' => $now_time,
				'create_day' => $now_day,
				'pdig1' => $pageuser['pidg1'],
				'pdig2' => $pageuser['pidg2'],
			];
			Db::table('pro_reward')->insertGetId($pro_reward);

			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);

			if ($item['is_give'] == '0') {
				//返佣
				$up_users = getUpUser($item['uid'], true);

				foreach ($up_users as $uv) {
					if ($uv['stop_commission']) { //暂停佣金
						continue;
					}
					if ($uv['gid'] < 91) { //代理以及其它管理用户不给佣金
						continue;
					}
					$rate = $commission_arr[$uv['agent_level']];
					if (!$rate || $rate < 0) {
						continue;
					}

					//检测该用户是否有购买同等金额以上的设备
					$uv_order = Db::table('pro_order')->where("uid={$uv['id']} and status=1 and is_give=0")->order(['price' => 'desc'])->find();
					if ($item['price'] == 3000) {
						if (!$uv_order || $uv_order['price'] < 2500) {
							continue;
						}
					} else {
						if (!$uv_order || $uv_order['price'] < $item['price']) {
							continue;
						}
					}

					$rebate = $reward * ($rate / 100);
					$wallet2 = getWallet($uv['id'], 2);
					if (!$wallet2) {
						throw new \Exception('钱包获取异常');
					}
					$wallet2 = Db::table('wallet_list')->where("id={$wallet2['id']}")->lock(true)->find();
					$wallet_data2 = [
						'balance' => $wallet2['balance'] + $rebate
					];
					Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
					//写入流水记录
					$result2 = walletLog([
						'wid' => $wallet2['id'],
						'uid' => $wallet2['uid'],
						'type' => 8,
						'money' => $rebate,
						'ori_balance' => $wallet2['balance'],
						'new_balance' => $wallet_data2['balance'],
						'fkey' => $item['id'],
						'remark' => 'Commission:' . $item['osn']
					]);
					if (!$result2) {
						throw new \Exception('流水记录写入失败');
					}
					//写入收益记录
					$pro_reward2 = [
						'uid' => $uv['id'],
						'oid' => $item['id'],
						'osn' => $item['osn'],
						'type' => 2,
						'level' => $uv['agent_level'],
						'base_money' => $reward,
						'rate' => $rate,
						'money' => $rebate,
						'remark' => $item['osn'],
						'create_time' => $now_time,
						'create_day' => $now_day,
						'pdig1' => $uv['pidg1'],
						'pdig2' => $uv['pidg2'],
					];
					Db::table('pro_reward')->insertGetId($pro_reward2);
				}
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			jReturn(-1, '系统繁忙请稍后再试', ['e' => $e]);
		}
		$return_data = [
			'reward' => $reward,
			//'js' => ($item1)
		];
		jReturn(1, 'Received successfully', $return_data);
	}
}
