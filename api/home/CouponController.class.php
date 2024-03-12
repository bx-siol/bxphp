<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;
use Curl\Curl;

class CouponController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _list()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);
		if ($params['page'] < 1) {
			$params['page'] = 1;
		}
		$params['type'] = intval($params['type']);
		$params['status'] = intval($params['status']);
		if (!$params['status']) {
			$params['status'] = 1;
		}
		$now_time = time();

		$where = "log.uid={$pageuser['id']} and log.status<99";
		$where .= empty($params['type']) ? '' : " and log.type={$params['type']}";
		if ($params['status'] == 1) {
			$where .= " and log.status in (1,2)";
		} elseif ($params['status'] == 2) {
			$where .= " and log.status=9";
		} else {
			$where .= " and (log.status=4 or (log.used<log.num and log.effective_time>0 and log.effective_time<{$now_time}))";
		}
		$count_item = Db::table('coupon_log log')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['coupon_log' => 'log'], ['*'])
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		$goods_arr = rows2arr(Db::table('pro_goods')
			->where("1=1")
			->field(['id', 'name'])
			->order(['sort' => 'desc', 'id' => 'desc'])->select());
		$coupon_arr = rows2arr(Db::table('coupon_list')->where("1=1")->field(['id', 'name', 'cover'])->select());
		foreach ($list as &$item) {
			$item['discount'] = floatval($item['discount']);
			$item['money'] = floatval($item['money']);
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
			if ($item['effective_time'] > 0) {
				$item['effective_time'] = date('m-d H:i:s', $item['effective_time']);
			} else {
				$item['effective_time'] = 'Permanent';
			}
			$item['coupon_name'] = $coupon_arr[$item['cid']]['name'];

			$gids = json_decode($item['gids'], true);
			if (!$gids) {
				$gids = [];
			}
			$goods = [];
			foreach ($gids as $gid) {
				$goods[] = $goods_arr[$gid]['name'];
			}
			$item['goods'] = $goods;
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
		}
		jReturn(1, 'ok', $return_data);
	}

	public function _exchange()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$item_id = intval($this->params['id']);
		Db::startTrans();
		try {
			$coupon = Db::table('coupon_log')->where("id={$item_id}")->lock(true)->find();
			if (!$coupon || $coupon['uid'] != $pageuser['id'] || $coupon['status'] >= 99) {
				jReturn(-1, '不存在相应的记录');
			}
			if ($coupon['type'] != 2) {
				jReturn(-1, 'This discount coupon is not available');
			}
			if ($coupon['status'] > 2) {
				jReturn(-1, 'This discount coupon is not available');
			}
			if ($coupon['num'] <= $coupon['used']) {
				jReturn(-1, 'This discount coupon is not available');
			}
			if ($coupon['effective_time'] && $coupon['effective_time'] <= NOW_TIME) {
				jReturn(-1, 'This coupon has expired');
			}

			//检测是否符合邀请条件
			$pro_order = Db::table('pro_order')->where("pid={$pageuser['id']} and is_exchange=0 and create_time>={$coupon['create_time']}")->lock(true)->find(); //and create_time>={$coupon['create_time']}
			if (!$pro_order) {
				jReturn(-1, 'Exchange conditions are not met. Invite 1 person to join and get cash.');
			}
			$pro_order_data = [
				'is_exchange' => 1
			];
			Db::table('pro_order')->where("id={$pro_order['id']}")->update($pro_order_data);

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
				'uid' => $pageuser['id'],
				'gid' => 0,
				'oid' => 0,
				'num' => 1,
				'discount' => $coupon['discount'],
				'money' => $coupon['money'],
				'create_day' => date('Ymd', NOW_TIME),
				'create_time' => NOW_TIME
			];
			Db::table('coupon_used')->insertGetId($coupon_used);

			$wallet2 = getWallet($pageuser['id'], 2); //余额钱包
			if (!$wallet2) {
				throw new \Exception('钱包获取异常');
			}
			$wallet2 = Db::table('wallet_list')->where("id={$wallet2['id']}")->lock(true)->find();
			$wallet_data2 = [
				'balance' => $wallet2['balance'] + $coupon['money']
			];
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
			//写入流水记录

			$result2 = walletLog([
				'wid' => $wallet2['id'],
				'uid' => $wallet2['uid'],
				// 'cid' => $wallet2['cid'],
				// 'wtype' => $wallet2['type'],
				'type' => 14,
				'money' => $coupon['money'],
				'ori_balance' => $wallet2['balance'],
				'new_balance' => $wallet_data2['balance'],
				'fkey' => $coupon['id'],
				'remark' => 'Exchange'
			]);


			if (!$result2) {
				throw new \Exception('流水记录写入失败');
			}

			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			jReturn(-1, '系统繁忙请稍后再试', $e->getMessage());
		}
		$return_data = [
			'status' => $coupon_log['status'],
			'used' => $coupon_log['used'],

		];
		jReturn(1, 'You have received (' . $coupon['money'] . ') RS bonus', $return_data);
	}
}
