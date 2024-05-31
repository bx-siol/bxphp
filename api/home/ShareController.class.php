<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class ShareController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		$pageuser = checkLogin(); //isLogin
		$tg_img = $this->genCover2($pageuser, true);
		$where = " log.type=10 and uid='" . $pageuser['id'] . "'";

		$RS = Db::table('wallet_log log')
			->where($where)
			->sum('money');

		$where = " log.pid='" . $pageuser['id'] . "'";
		$people = Db::table('sys_user log')
			->where($where)
			->count();

		$return_data = [
			'icode' => $pageuser['icode'],
			//'url' => $this->getQrcodeUrl($pageuser['icode']),
			'qrcode' => $tg_img . '?rt=' . mt_rand(11111, 99999),
			'RS' => $RS,
			'people' => $people
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _download()
	{
		//$pageuser=checkLogin();
		$filename = ROOT_PATH . urldecode($this->params['src']);
		$filename_arr = explode('?', $filename);
		$filename = $filename_arr[0];
		$mime = 'application/force-download';
		header('Pragma: public'); // required
		header('Expires: 0'); // no cache
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		header('Content-Type: ' . $mime);
		header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
		header('Content-Type: application/octet-stream; name=' . $filename);
		header('Content-Transfer-Encoding: binary');
		header('Connection: close');
		readfile($filename);
		exit();
	}

	private function genCover2($user, $reflush = false)
	{
		$icode = $user['icode'];
		$tg_img = 'uploads/qrcode/tg_' . $icode . '.jpg';
		if (file_exists(ROOT_PATH . $tg_img) && !$reflush) {
			return $tg_img;
		}

		$qrcode = ROOT_PATH . $this->genQrcode($icode);

		//$tpl=ROOT_PATH.'public/images/tpl.jpg';
		$tpl = $qrcode;
		$image = new \Imagick($tpl);
		$width = $image->getImageWidth();
		$height = $image->getImageHeight();
		//首先进行一个图片绘画
		//$newImg = new Imagick();//ROOT_PATH . $user['headimgurl']
		//$percent = 0.28;
		//$qwidth = $width * $percent;
		//$qheight = $width * $percent;
		//$newImg->thumbnailImage($qwidth, $qheight);

		// $radius = $qwidth * 0.15;
		// $mask = new Imagick();
		// $mask->newImage($qwidth, $qheight, new ImagickPixel('transparent'), 'png');
		// $shape = new ImagickDraw();
		// $shape->setFillColor(new ImagickPixel('magenta'));
		// $shape->roundRectangle(0, 0, $qwidth, $qheight, $radius, $radius);
		// $mask->drawImage($shape);
		// $newImg->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);

		//$newImg->newImage($width * $xNum + ($xNum - 1) * $xDistance, $height * $yNum + ($yNum - 1) * $yDistance, '#AAAAAA', 'jpg');
		//$image->compositeImage($newImg, Imagick::COMPOSITE_OVER, ($width - $qwidth) / 2, ($height - $qheight) / 2);

		$draw = new ImagickDraw();
		//$draw->setTextKerning(10); // 设置文字间距
		$draw->setFont(ROOT_PATH . 'public/fonts/simhei.ttf');
		$draw->setFontWeight(800); // 字体粗体
		$draw->setFillColor('#333333'); // 字体颜色
		//$draw->setFontFamily( "Palatino" );
		$draw->setFontSize(32);
		$draw->setGravity(\Imagick::GRAVITY_NORTH);
		//$image->annotateImage($draw,-76,565+$qheight, 0, '推荐码:') ;
		//$phone=substr($user['phone'],0,3).'***'.substr($user['phone'],8);
		//$image->annotateImage($draw, 4,435, 0, $phone) ;
		$draw->setFillColor('#f860d7'); // 字体颜色
		$draw->setFontSize(50);
		//$image->annotateImage($draw,60,556+$qheight, 0, $icode) ;
		//header("Content-Type: image/{$image->getImageFormat()}");
		//echo $image->getImageBlob();
		file_put_contents(ROOT_PATH . $tg_img, $image->getImageBlob());
		return $tg_img;
	}

	private function genCover($user, $reflush = false)
	{
		$icode = $user['icode'];
		$tg_img = 'uploads/qrcode/tg_' . $icode . '.jpg';
		if (file_exists(ROOT_PATH . $tg_img) && !$reflush) {
			return $tg_img;
		}

		$qrcode = ROOT_PATH . $this->genQrcode($icode);

		$tpl = ROOT_PATH . 'public/images/tg.png';
		$image = new \Imagick($tpl);
		$width = $image->getImageWidth();
		$height = $image->getImageHeight();
		//首先进行一个图片绘画
		$newImg = new Imagick($qrcode);
		$qwidth = 420;
		$qheight = 420;
		$newImg->thumbnailImage($qwidth, $qheight);
		//$newImg->newImage($width * $xNum + ($xNum - 1) * $xDistance, $height * $yNum + ($yNum - 1) * $yDistance, '#AAAAAA', 'jpg');
		$image->compositeImage($newImg, Imagick::COMPOSITE_OVER, ($width - $qwidth) / 2, 560);

		$draw = new ImagickDraw();
		//$draw->setTextKerning(10); // 设置文字间距
		$draw->setFont(ROOT_PATH . 'public/fonts/simhei.ttf');
		$draw->setFontWeight(800); // 字体粗体
		$draw->setFillColor('#333333'); // 字体颜色
		//$draw->setFontFamily( "Palatino" );
		$draw->setFontSize(32);
		$draw->setGravity(\Imagick::GRAVITY_NORTH);
		$image->annotateImage($draw, -76, 565 + $qheight, 0, '推荐码:');
		//$phone=substr($user['phone'],0,3).'***'.substr($user['phone'],8);
		//$image->annotateImage($draw, 4,435, 0, $phone) ;
		$draw->setFillColor('#f860d7'); // 字体颜色
		$draw->setFontSize(50);
		$image->annotateImage($draw, 60, 556 + $qheight, 0, $icode);
		//header("Content-Type: image/{$image->getImageFormat()}");
		//echo $image->getImageBlob();
		file_put_contents(ROOT_PATH . $tg_img, $image->getImageBlob());
		return $tg_img;
	}

	private function genQrcode($icode)
	{
		$icode_name = getRsn();
		$qrcode = 'uploads/qrcode/' . date('Ym') . '/' . $icode_name . '.png';
		if (file_exists($qrcode)) {
			return $qrcode;
		}
		$qrcode_str = $this->getQrcodeUrl($icode);
		if (!is_dir(dirname(ROOT_PATH . $qrcode))) {
			mkdir(dirname(ROOT_PATH . $qrcode), 0755, true);
		}
		QRcode::png($qrcode_str, ROOT_PATH . $qrcode, 'L', 14, 1);
		return $qrcode;
	}

	private function getQrcodeUrl($icode)
	{
		$qrcode_str = REQUEST_SCHEME . '://' . HTTP_HOST . "/h5/#/register?icode={$icode}";
		return $qrcode_str;
	}

	//---------------------礼品活动-----------------------------------
	//雀巢礼品活动
	public function _giftproject()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$list = Db::table('pro_goods')->where("cid={$params['cid']}")->select();

		$return_data = [
			'list' => $list
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//领取礼品
	public function _giftreceive()
	{
		$time = time();
		if($time > 1717612169){
			ReturnToJson(1, 'The activity has ended.');
		}		

		$pageuser = checkLogin();
		$params = $this->params;

		switch ($params['goodsid']) {
			case 225:
				$gid = 219;
				break;
			case 226:
				$gid = 220;
				break;
			case 227:
				$gid = 221;
				break;
			case 228:
				$gid = 222;
				break;		
		}
		
		//下级购买
		// $SubordinateBuy = Db::query("select gid,count(*) totalnum from pro_order where  uid in 
		// (select id from sys_user where pid={$pageuser['id']} and first_pay_day >0 ) and gid ={$gid}   group by gid");

		//当日日期
		$oneMinuteLaterDate = date('Ymd', NOW_TIME);
		$SubordinateBuy = Db::query("select gid,count(*) totalnum from ( 
			SELECT uid,gid FROM pro_order where uid in 
			(select id from sys_user where pid={$pageuser['id']} and first_pay_day ={$oneMinuteLaterDate} ) GROUP BY uid ORDER BY create_time
			) a where gid ={$gid}  group by gid");

		if(is_null($SubordinateBuy[0]['totalnum']))
			ReturnToJson(1, 'Please invite people to participate in the event.');

		//自己领取
		$MyReceive = Db::query("select gid,count(*) totalnum from pro_order where uid={$pageuser['id']} 
		and gid ={$params['goodsid']} and create_day={$oneMinuteLaterDate} group by gid");

		if(is_null($MyReceive[0]['totalnum']))
			$MyReceive['totalnum'] = 0;

		if($SubordinateBuy[0]['totalnum'] > $MyReceive[0]['totalnum']){
			//发礼物
			$goodInfo = Db::table('pro_goods')->where("id = {$params['goodsid']}")->find();
			for ($i = 0; $i < $SubordinateBuy[0]['totalnum']-$MyReceive[0]['totalnum']; $i++) {
				Db::table('pro_order')->insertGetId([
					'uid'=> $pageuser['id'],
					'osn'=> getRsn(),
					'pid' => $pageuser['pid'],
					'cid' => $goodInfo['cid'],
					'gid' => $params['goodsid'],
					'days' => $goodInfo['days'],
					'rate' => $goodInfo['rate'],
					'price' => $goodInfo['price'],
					'price1' => 0,
					'price2' => 0,
					'p1' => 1,
					'p2' => 1,
					'p3' => 0,
					'status' => 1,
					'money' => $goodInfo['price'],
					'num' => 1,
					'create_day' => date('Ymd', NOW_TIME),
					'create_time' => NOW_TIME,
					'is_give' => 1,
					'is_exchange' => 1,
					'discount' => 1,
					'w1_money' => $goodInfo['price'],
					'w2_money' => 0,
				]);
			}
		}
		else{
			ReturnToJson(1, 'Please invite people to participate in the event.');
		}

		$return_data = [
			
		];
		ReturnToJson(200, 'Received successfully', $return_data);
	}

	//------------------------------------邀请任务
	//先正达
	//邀请任务查询
	public function _getTakDat()
	{
		$pageuser = checkLogin();
		
		$todaystart = strtotime(date('Y-m-d'));
		$todayend = strtotime(date('Y-m-d'))+86399;
		$today = date('Ymd', NOW_TIME);
		//今日注册
		$todayregister = Db::table('sys_user')->where("pid={$pageuser['id']} and reg_time >= {$todaystart} and reg_time<= {$todayend}")->count();
		//今日注册加充值
		$todayRecharge = Db::table('sys_user')->where("pid={$pageuser['id']} and reg_time >= {$todaystart} and reg_time<= {$todayend} and first_pay_day = {$today}")->count();

		//每3日内充值
		$top = strtotime('2024-06-01 00:00:00');
		//计算时间段
		$time = time();
		$flag = true;
		$i = 1;
		$regstart = 0;
		$regend = 0;
		if($time > $top)
		{
			while($flag){
				$start = strtotime(date('Y-m-d', strtotime($top . '+'. 3*($i-1) .' days')));
				$end = strtotime(date('Y-m-d', strtotime(date('Y-m-d',$start) . '+3 days')))-1;
				if($start >= $time && $time <= $end){
					$flag = false;
					$regstart = $start;
					$regend = $end;
				}
				$i++;
			};
		}
		
		$threedayRecharge = 0;
		if($regstart !=0 && $regend !=0){
			$threedayRecharge = Db::table('sys_user')
			->where("pid={$pageuser['id']} and reg_time >= {$regstart} and reg_time<= {$regend} 
					and first_pay_day >= {date('Y-m-d',$regstart)} and first_pay_day <= {date('Y-m-d',$regend)}")
			->count();
		}

		$return_data = [
			'todayregister' => $todayregister,
			'todayRecharge' => $todayRecharge,
			'threedayRecharge' => $threedayRecharge
		];
		ReturnToJson(200, 'ok', $return_data);
	}

	//领取当日3人注册送100积分
	public function _Claimpoints()
	{
		$pageuser = checkLogin();
		$todaystart = strtotime(date('Y-m-d'));
		$todayend = strtotime(date('Y-m-d'))+86399;
		$today = date('Ymd', NOW_TIME);
		$todayregister = Db::table('sys_user')->where("pid={$pageuser['id']} and reg_time >= {$todaystart} and reg_time<= {$todayend}")->count();
		if($todayregister <3)		
			ReturnToJson(1, 'Please invite people to register first to receive rewards.');

		$wallet = getWallet($pageuser['id'], 3);
		if (!$wallet)
			ReturnToJson(1, 'Wallet acquisition exception.');		
		
		$walletlog = Db::table('wallet_log')->where("uid={$pageuser['id']} and type=111 and create_day={$today} ")->count();
		if($walletlog > 0)
			ReturnToJson(1, 'Received today.');		
		
		Db::startTrans();
		try{
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$wallet_data = [
				'balance' => $wallet['balance'] + 100
			];
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 111,
				'money' => 100,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => '',
				'remark' => 'Inviting 3 people to register today to earn 100 points'
			]);
			if (!$result)
				throw new \Exception('Failed to write journal records.');

			Db::commit();
		}
		catch (Exception $e) {
			Db::rollback();
			ReturnToJson(1, 'The system is busy, please try again later.', ['e' => $e->getMessage()]);
		}
		
		ReturnToJson(200, 'Received successfully');
	}
	
	//领取当日3人注册充值送50余额
	public function _ClaimRS()
	{
		$pageuser = checkLogin();
		$todaystart = strtotime(date('Y-m-d'));
		$todayend = strtotime(date('Y-m-d'))+86399;
		$today = date('Ymd', NOW_TIME);
		$todayRecharge = Db::table('sys_user')->where("pid={$pageuser['id']} and reg_time >= {$todaystart} and reg_time<= {$todayend} and first_pay_day = {$today}")->count();
		if($todayRecharge <3)		
			ReturnToJson(1, 'Please invite people to recharge and receive rewards first.');

		$wallet = getWallet($pageuser['id'], 2);
		if (!$wallet)
			ReturnToJson(1, 'Wallet acquisition exception.');		
		
		$walletlog = Db::table('wallet_log')->where("uid={$pageuser['id']} and type=112 and create_day={$today} ")->count();
		if($walletlog > 0)
			ReturnToJson(1, 'Received today.');		
		
		Db::startTrans();
		try{
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$wallet_data = [
				'balance' => $wallet['balance'] + 50
			];
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 112,
				'money' => 50,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => '',
				'remark' => 'Inviting 3 people to register today to earn 100 points'
			]);
			if (!$result)
				throw new \Exception('Failed to write journal records.');

			Db::commit();
		}
		catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(1, 'The system is busy, please try again later.', ['e' => $e]);
		}
		
		ReturnToJson(200, 'Received successfully');
	}

	//领取3日内邀请5人注册充值送100余额
	public function _FivePersonReward()
	{
		$pageuser = checkLogin();
		//每3日内充值
		$top = strtotime('2024-05-31 00:00:00');
		//计算时间段
		$time = time();
		$flag = true;
		$i = 1;
		$regstart = 0;
		$regend = 0;
		if($time > $top)
		{
			while($flag){
				$start = strtotime(date('Y-m-d', strtotime($top . '+'. 3*($i-1) .' days')));
				$end = strtotime(date('Y-m-d', strtotime(date('Y-m-d',$start) . '+3 days')))-1;
				if($start >= $time && $time <= $end){
					$flag = false;
					$regstart = $start;
					$regend = $end;
				}
				$i++;
			};
		}
		if($regstart ==0 || $regend ==0)
			ReturnToJson(1, 'The system is busy, please try again later.');
		
		$threedayRecharge = Db::table('sys_user')
							->where("pid={$pageuser['id']} and reg_time >= {$regstart} and reg_time<= {$regend} 
									and first_pay_day >= {date('Y-m-d',$regstart)} and first_pay_day <= {date('Y-m-d',$regend)}")
							->count();;
		
		if($threedayRecharge < 5)		
			ReturnToJson(1, 'Please invite people to recharge and receive rewards first.');

		$wallet = getWallet($pageuser['id'], 2);
		if (!$wallet)
			ReturnToJson(1, 'Wallet acquisition exception.');		
		
		$walletlog = Db::table('wallet_log')->where("uid={$pageuser['id']} and type=113 and create_day >={$regstart} and create_day <={$regend} ")->count();
		if($walletlog > 0)
			ReturnToJson(1, 'Received today.');		
		
		Db::startTrans();
		try{
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$wallet_data = [
				'balance' => $wallet['balance'] + 100
			];
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 113,
				'money' => 100,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => '',
				'remark' => 'Inviting 3 people to register today to earn 100 points'
			]);
			if (!$result)
				throw new \Exception('Failed to write journal records.');

			Db::commit();
		}
		catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(1, 'The system is busy, please try again later.', ['e' => $e]);
		}
		
		ReturnToJson(200, 'Received successfully');
	}

	//领取3日内邀请5人注册充值送100余额
	public function _TenPersonReward()
	{
		$pageuser = checkLogin();
		//每3日内充值
		$top = strtotime('2024-06-01 00:00:00');
		//计算时间段
		$time = time();
		$flag = true;
		$i = 1;
		$regstart = 0;
		$regend = 0;
		if($time > $top)
		{
			while($flag){
				$start = strtotime(date('Y-m-d', strtotime($top . '+'. 3*($i-1) .' days')));
				$end = strtotime(date('Y-m-d', strtotime(date('Y-m-d',$start) . '+3 days')))-1;
				if($start >= $time && $time <= $end){
					$flag = false;
					$regstart = $start;
					$regend = $end;
				}
				$i++;
			};
		}
		if($regstart ==0 || $regend ==0)
			ReturnToJson(1, 'The system is busy, please try again later.');
		
		$threedayRecharge = Db::table('sys_user')
							->where("pid={$pageuser['id']} and reg_time >= {$regstart} and reg_time<= {$regend} 
									and first_pay_day >= {date('Y-m-d',$regstart)} and first_pay_day <= {date('Y-m-d',$regend)}")
							->count();;
		
		if($threedayRecharge < 10)		
			ReturnToJson(1, 'Please invite people to recharge and receive rewards first.');

		$wallet = getWallet($pageuser['id'], 2);
		if (!$wallet)
			ReturnToJson(1, 'Wallet acquisition exception.');		
		
		$walletlog = Db::table('wallet_log')->where("uid={$pageuser['id']} and type=114 and create_day >={$regstart} and create_day <={$regend} ")->count();
		if($walletlog > 0)
			ReturnToJson(1, 'Received today.');		
		
		Db::startTrans();
		try{
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$wallet_data = [
				'balance' => $wallet['balance'] + 200
			];
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 114,
				'money' => 200,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => '',
				'remark' => 'Inviting 3 people to register today to earn 100 points'
			]);
			if (!$result)
				throw new \Exception('Failed to write journal records.');

			Db::commit();
		}
		catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(1, 'The system is busy, please try again later.', ['e' => $e]);
		}
		
		ReturnToJson(200, 'Received successfully');
	}
}
