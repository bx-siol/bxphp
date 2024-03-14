<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class QepayController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		echo 'notify';
	}


	public function _pay()
	{
		$logpathd = LOGS_PATH . 'qepay/notify/';
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}


		$sign = paySign($params);
		file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" .  'singree' . "\r\n", FILE_APPEND);
			ReturnToJson(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['payStatus'] == '1' ? 1 : -1,
			'osn' => $params['orderNo'],
			'amount' =>  $params['amount'],
			'successStr' => 'success'
		];

		$this->payAct($pdata);
	}

	public  function _jqr()
	{
		$order['uid'] = '909631';
		$order['money'] = '999999999999';
		$osn = '/s';
		$log_file =  LOGS_PATH . 'jqr.txt';
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
			];

			//	$userinfo = Db::table('sys_user')->where("id='{$order['uid']}'")->find();

			$up_user = getUpUser($order['uid'], true);
			$tjr = '/';
			$rjdl = '/';
			foreach ($up_user as $uk => $uv) {
				if ($uk == 0)
					$tjr = $uv['account'];
				if ($uv['gid'] == 81)
					$rjdl = $uv['account'];
			}
			$account =  rand(0, count($glstr) - 1);
			$str = "二级代理：{$rjdl}\n 推荐人：{$tjr}\n 订单金额：{$order['money']}\n{$glstr[$account]}"; 
			$temps =	$this->send_photo('-723150711', $str);
			//$temps = $this->send_photo('-1001250655772', $str);
			file_put_contents($log_file,   "\r\n{$osn}" . $temps . var_export($up_user, true) . "\r\n" . $str . "\r\n\r\n", FILE_APPEND);
		} catch (\Exception $ed) {
			file_put_contents($log_file,   "\r\n{$osn}" . var_export($ed, true) . "\r\n\r\n", FILE_APPEND);
		}
	}

	public function _cash()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$logpathd = LOGS_PATH . 'qepay/notify/';
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';



		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			ReturnToJson(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		file_put_contents($logpathd . "cash.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}
		$pdata = [
			'osn' => $params['orderNo'],
			'out_osn' => $params['tradeNo'],
			'pay_status' => $params['payStatus'] == '1' ? 9 : 3,
			'pay_msg' => $params['code'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
