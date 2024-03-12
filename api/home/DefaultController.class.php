<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class DefaultController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}
	public function _notice1()
	{
		$pageuser = checkLogin();
		$user = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
		$now_day = date('Ymd');
		$where = "log.create_day={$now_day}";
		$list = Db::view(['gift_lottery_log' => 'log'], ['money'])
			->view(['sys_user' => 'u'], ['nickname', 'account'], 'log.uid=u.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->limit(10)
			->select()->toArray();
		$notice = [];
		foreach ($list as $item) {
			$account = substr($item['account'], -3);
			$account1 = substr($item['account'], 0, 3);
			$notice[] = [
				'time' => date('d/m/Y', $item['create_time']), //21/12/2022
				'account' => "{$account1}****{$account}", //21/12/2022
				'RS' => $item['money'], //21/12/2022

			];
		}

		$notice_ext = [
			'52*****652 recharge 280RS',
			'52*****614 withdrawal of 454RS',
			'52*****354 get 254RS Commission',
			'52*****654 recharge 164RS',
			'52*****614 withdrawal of 254RS has been received',
			'52*****857 withdrawal of 657RS',
			'52*****950 recharge 154RS',
			'52*****604 withdrawal of 14.3RS',
			'52*****654 withdrawal of 184RS',
			'52*****604 withdrawal of 14.3RS has been received',
			'52*****154 withdrawal of 954RS',
			'52*****224 recharge 454RS',
			'52*****857 get 654RS Commission',
			'52*****654 withdrawal of 184RS has been received',
			'52*****025 get 365RS Commission',
			'52*****359 recharge 103RS',
			'52*****547 withdrawal of 350RS',
			'52*****975 get 60RS Commission',
			'52*****364 recharge 88RS',
			'52*****547 withdrawal of 350RS has been received',
			'52*****094 withdrawal of 500RS has been received',
		];
		foreach ($notice_ext as $nv) {
			$account = rand(100, 999);
			$account2 = rand(600, 999);
			$RS = rand(370, 10000);
			$notice[] = [
				'time' => date('d/m/Y', time()),
				//21/12/2022
				'account' => "{$account2}****{$account}",
				//21/12/2022
				'RS' => $RS, //21/12/2022
			];
		}

		$return_data = [
			'notice' => $notice,
		];
		jReturn(1, 'ok', $return_data);
	}

	public function _notice()
	{
		$pageuser = checkLogin();
		$prize = Db::table('gift_prize')->select()->toArray();
		$notice = [];
		$notice_ext = [
			'52*****652 recharge 280RS',
			'52*****614 withdrawal of 454RS',
			'52*****354 get 254RS Commission',
			'52*****654 recharge 164RS',
			'52*****614 withdrawal of 254RS has been received',
			'52*****857 withdrawal of 657RS',
			'52*****950 recharge 154RS',
			'52*****604 withdrawal of 14.3RS',
			'52*****654 withdrawal of 184RS',
			'52*****604 withdrawal of 14.3RS has been received',
			'52*****154 withdrawal of 954RS',
			'52*****224 recharge 454RS',
			'52*****857 get 654RS Commission',
			'52*****654 withdrawal of 184RS has been received',
			'52*****025 get 365RS Commission',
			'52*****359 recharge 103RS',
			'52*****547 withdrawal of 350RS',
			'52*****975 get 60RS Commission',
			'52*****364 recharge 88RS',
			'52*****547 withdrawal of 350RS has been received',
			'52*****094 withdrawal of 500RS has been received',
		];
		foreach ($notice_ext as $nv) {
			$account = rand(100, 999);
			$account2 = rand(600, 999);
			$RS = rand(0, 7);
			$notice[] = [
				'time' => date('d/m/Y', time()),
				//21/12/2022
				'account' => "{$account2}****{$account}",
				//21/12/2022
				'RS' => $prize[$RS]['name'], //21/12/2022 
			];
		}

		$return_data = [
			'notice' => $notice,
			//'p' => $prize
		];
		jReturn(1, 'ok', $return_data);
	}

	public function _GetService()
	{
		$pageuser = checkLogin();
		$service_arr = [];
		$up_users = getUpUser($pageuser['id'], true);
		foreach ($up_users as $uv) {
			$service_arr = Db::table('ext_service')->where("uid={$uv['id']}")->field(['type', 'name', 'account'])->order(['id' => 'desc'])->limit(4)->select()->toArray();
			if ($service_arr) {
				break;
			}
		}
		if (!$service_arr) {
			$service_arr = Db::table('ext_service')->where("gid<=70 or uid={$pageuser['id']}")->field(['type', 'name', 'account'])->order(['id' => 'desc'])->limit(4)->select()->toArray();
		}

		$cnf_service_type = getConfig('cnf_service_type');
		foreach ($service_arr as &$sv) {
			$sv['type_flag'] = $cnf_service_type[$sv['type']];
		}
		$return_data = [
			'service_arr' => $service_arr
		];
		jReturn(1, 'ok', $return_data);
	}

	public function _get120rs()
	{
		jReturn(-1, 'The event has expired, please look forward to new events');
		return;
		$pageuser = checkLogin();
		$wallet = getWallet($pageuser['id'], 2);
		if (!$wallet) {
			throw new \Exception('钱包获取异常');
		}
		$user = Db::table('sys_user')->where("id={$pageuser['id']}")->lock(true)->find();
		if (intval($user['gift']) != 0) {
			jReturn(-1, 'You have received the new reward');
			return;
		}
		Db::table('sys_user')->where("id={$user['id']}")->update(['gift' => 1]);
		$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
		$wallet_data = [
			'balance' => $wallet['balance'] + 120
		];
		//更新钱包余额
		Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
		//写入流水记录
		$result = walletLog([
			'wid' => $wallet['id'],
			'uid' => $wallet['uid'],
			'type' => 10,
			'money' => 120,
			'ori_balance' => $wallet['balance'],
			'new_balance' => $wallet_data['balance'],
			'fkey' => $user['id'],
			'remark' => 'Give away'
		]);
		if (!$result) {
			throw new \Exception('流水记录写入失败');
		}
		jReturn(1, 'ok');
	}
	public function _index()
	{
		$pageuser = checkLogin();
		$notice = Db::table('news_notice')->where("status<99")
			->field(['title', 'text' => 'content'])
			->order(['sort' => 'desc', 'id' => 'desc'])
			->limit(20)
			->select()->toArray();
		if (!$notice) {

			$user = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
			$now_day = date('Ymd');
			$where = "log.create_day={$now_day}";
			$list = Db::view(['gift_lottery_log' => 'log'], ['money'])
				->view(['sys_user' => 'u'], ['nickname', 'account'], 'log.uid=u.id', 'LEFT')
				->where($where)
				->order(['log.id' => 'desc'])
				->limit(10)
				->select()->toArray();
			$notice = [];
			foreach ($list as $item) {
				$account = substr($item['account'], -3);
				$account1 = substr($item['account'], 0, 3);
				$notice[] = [
					'title' => "{$account1}*****{$account} get {$item['money']}RS Commission"
				];
			}
			// if (!$notice) {
			// 	$notice[] = ['title' => 'Buy products and get lucky draw'];
			// }
			$ddd = [
				'recharge',
				'withdrawal of',
				'get',
			];
			$notice_ext = [
				'52*****652 recharge 280RS',
				'52*****614 withdrawal of 454RS',
				'52*****354 get 254RS Commission',
				'52*****654 recharge 164RS',
				'52*****614 withdrawal of 254RS has been received',
				'52*****857 withdrawal of 657RS',
				'52*****950 recharge 154RS',
				'52*****604 withdrawal of 14.3RS',
				'52*****654 withdrawal of 184RS',
				'52*****604 withdrawal of 14.3RS has been received',
				'52*****154 withdrawal of 954RS',
				'52*****224 recharge 454RS',
				'52*****857 get 654RS Commission',
				'52*****654 withdrawal of 184RS has been received',
				'52*****025 get 365RS Commission',
				'52*****359 recharge 103RS',
				'52*****547 withdrawal of 350RS',
				'52*****975 get 60RS Commission',
				'52*****364 recharge 88RS',
				'52*****547 withdrawal of 350RS has been received',
				'52*****094 withdrawal of 500RS has been received',
			];
			foreach ($notice_ext as $nv) {
				$account = rand(100, 999);
				$account2 = rand(600, 999);
				$account1 = rand(370, 10000);
				$notice[] = ['title' => "{$account2}*****{$account} recharge {$account1}RS"];
				$account = rand(100, 999);
				$account2 = rand(600, 999);
				$account1 = rand(370, 10000);
				$notice[] = ['title' => "{$account2}*****{$account} withdrawal {$account1}RS"];
				$account = rand(100, 999);
				$account2 = rand(600, 999);
				$account1 = rand(370, 10000);
				$notice[] = ['title' => "{$account2}*****{$account} get {$account1}RS Commission"];
				$account = rand(100, 999);
				$account2 = rand(600, 999);
				$account1 = rand(370, 10000);
				$notice[] = ['title' => "{$account2}*****{$account} withdrawal of {$account1}RS has been received"];
			}

			//$notice=[]; 
		}

		// $video = Db::table('news_article')->where("id=35")->field(['id', 'title', 'ndesc', 'cover', 'url'])->find();
		// if (!$video) {
		// 	$video = [];
		// }

		// $about = Db::table('news_article')->where("id=40")->field(['id', 'title', 'ndesc', 'cover'])->find();
		// if (!$about) {
		// 	$about = [];
		// }
		// $news = Db::table('news_article')->where("cid=50 and status<99")->field(['id', 'title', 'ndesc', 'cover'])->order(['id' => 'desc'])->limit(6)->select()->toArray();
		// if (!$news) {
		// 	$news = [];
		// }

		// $user = [
		// 	'account' => $pageuser['account'],
		// 	'headimgurl' => $pageuser['headimgurl'],
		// 	'group' => getGroups($pageuser['gid'])
		// ];

		$RSyright = getPset('RSyright');

		$tipId = 48;
		if (IS_WIN) {
			$tipId = 48;
		}
		$tip = Db::table('news_article')->where("id={$tipId} and status=2")->field(['title', 'content'])->find();
		$newscount = Db::table('news_article')->where("cid=50 and status=2")->field(['id'])->select()->toArray();

		$return_data = [
			'newscount' => count($newscount),
			'newsids' => $newscount,
			// 'user' => $user,
			'kv' => getPset('indexKv'),
			'RSyright' => $RSyright,
			'notice' => $notice,
			// 'video' => $video,
			// 'about' => $about,
			// 'news' => $news,
			'tip' => $tip,
			'gift' => $pageuser['gift'],
			//'service_arr' => $service_arr
		];
		jReturn(1, 'ok', $return_data);
	}
}
