<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

/*通用控制器基类*/

class CommonCtl
{
	protected $redis;
	protected $params;
	protected $pageSize = 30;

	public function __construct()
	{
		$this->redis = new MyRedis();
		$this->params = $this->param();
	}

	public function _index()
	{
		echo 'bindex';
	}

	//登录
	public function _login()
	{
		$params = $this->params;
		$account = strtolower($params['account']);
		$password = $params['password'];
		$vcode = strtolower($params['vcode']);
		if (!$password) {
			ReturnToJson(-1, '请填写登录密码');
		} else {
			$weak_arr = [md5('123456')];
			if (in_array($password, $weak_arr)) {
				ReturnToJson(-1, '密码过于简单，请更换再试');
			}
		}
		//校验验证码 

		$mem_key = '';
		$session_id = $params['sid'];
		if (!$session_id) {
			ReturnToJson(-1, '缺少验证参数');
		}
		$mem_key = 'vcode_' . $session_id;
		$code = $this->redis->get($mem_key);
		if (!$code) {
			ReturnToJson(-1, '验证码已过期');
		}
		$this->redis->rm($mem_key);
		if ($vcode != $code) {
			ReturnToJson(-1, '图形验证码不正确');
		}

		$user = '';
		try {
			$user = getUserByAccount($account);
		} catch (\Throwable $th) {
			ReturnToJson(-1, json_encode($th, true));
		}
		$login_status = 0;
		if (!$user || $user['status'] >= 99) {
			$login_status = 1;
		} else {
			$dcx = $password;
			$password = getPassword($password);

			if ($password != $user['password']) {
				$login_status = 2;
				ReturnToJson(-1, '账号或密码错误');
			} else {
				if ($user['status'] != 2) {
					ReturnToJson(-1, '该账号被禁止登录');
				}
			}
		}
		if ($login_status) {
			ReturnToJson(-1, '账号或密码错误2');
		}

		$token = $this->doLogin($user, []);
		$return_data = [
			'account' => $user['account'],
			'token' => $token
		];
		ReturnToJson(1, '登录成功', $return_data);
	}

	//执行登录
	private function doLogin($user, $wx_user = [], $from = '')
	{
		if (!$user['id']) {
			return false;
		}
		//清理其他端
		//clearToken($user['id']);

		//生成token
		$token = getToken($user);
		if (!$token) {
			ReturnToJson('-1', 'token生成失败');
		}

		$user_data = [
			'login_time' => NOW_TIME,
			'login_ip' => CLIENT_IP,
			'language' => getLang()
		];
		session_start();

		$res = updateUserinfo($user['id'], $user_data);
		if (!$res) {
			ReturnToJson('-1', '登录失败');
		}

		if ($from == 'mini') {
			$opt_name = '微信小程序登录';
		} else if ($from == 'app') {
			$opt_name = '微信App应用登录';
		} else {
			if ($from == 'wechat') {
				$opt_name = '微信公众号登录';
			} else {
				$opt_name = '普通前台登录';
			}
		}
		actionLog(['opt_name' => $opt_name, 'sql_str' => $token, 'logUid' => $user['id']]);
		return $token;
	}

	protected function downloadHead($url)
	{
		$filename = getRsn(md5($url . time() . SYS_KEY));
		$savepath = 'uploads/head/' . date('Ym') . '/' . $filename . '.png';
		$respath = downloadFile($url, $savepath);
		return $respath;
	}

	//退出登录
	public function _logout()
	{
		doLogout();
		ReturnToJson(1, '退出成功');
	}

	//注册
	public function _register()
	{
		$return_data = [
			'reg_type' => getConfig('cnf_register_type') == '1' ? 1 : 2,
			'need_imgcode' => getConfig('cnf_reg_need_imgcode') == '是' ? true : false,
			'need_icode' => getConfig('cnf_reg_need_icode') == '是' ? true : false,
		];
		ReturnToJson(1, 'ok', $return_data);
	}
	//注册
	public function _registerAct()
	{
		$cnf_regms_open = getConfig('cnf_regms_open');
		if ($cnf_regms_open != '是') {
			ReturnToJson(-1, '暂未开放注册');
		}
		$params = $this->params;

		if (!$params['account']) {
			ReturnToJson(-1, '请填写账号');
		}
		$params['account'] = strtolower($params['account']);
		$params['phone'] = '';

		$cnf_register_type = getConfig('cnf_register_type');
		if ($cnf_register_type == 1) {
			if (!isPhone($params['account'])) {
				ReturnToJson(-1, '请填写正确的手机号');
			}
			if (!$params['scode']) {
				ReturnToJson(-1, '请填写短信验证码');
			}
			$params['vcode'] = $params['scode'];
			$params['phone'] = $params['account'];
		} elseif ($cnf_register_type == 2) {
			if (!isEmail($params['account'])) {
				ReturnToJson(-1, '请填写正确的邮箱');
			}
			if (!$params['ecode']) {
				ReturnToJson(-1, '请填写邮件验证码');
			}
			$params['vcode'] = $params['ecode'];
		} else {
			if (!$params['account']) {
				ReturnToJson(-1, '请填写用户名');
			}
			if (!preg_match("/^[a-zA-Z0-9_]{6,15}/i", $params['account'])) {
				ReturnToJson(-1, '账号必须是6-15位的字母数字下划线组合');
			}
		}


		//   $cnf_reg_need_imgcode=getConfig('cnf_reg_need_imgcode');
		//   if($params['reffer']&&$cnf_reg_need_imgcode=='是'){
		//   if(!$params['imgcode']){
		//   ReturnToJson(-1,'请填写图形验证码');
		//   }
		//   $session_id=$params['sid'];
		//   if(!$session_id){
		//   ReturnToJson(-1,'缺少验证参数');
		//   }
		//   $mem_key='vcode_'.$session_id;
		//   $code=$this->redis->get($mem_key);
		//   $this->redis->rm($mem_key);
		//   if($params['imgcode']!=$code){
		//   ReturnToJson(-1,'图形验证码不正确');
		//   }
		//   } 
		if (!$params['password']) {
			ReturnToJson(-1, '请填写登录密码');
		} else {
			if ($params['password'] == md5('123456')) {
				ReturnToJson(-1, '密码过于简单，请更换再试');
			}
		}
		$cnf_reg_need_icode = getConfig('cnf_reg_need_icode');
		$check_puser = [];
		if ($cnf_reg_need_icode == '是') {
			$check_puser = Db::table('sys_user')->whereRaw('icode=:icode', ['icode' => $params['icode']])->find();
			if (!$check_puser) {
				ReturnToJson(-1, '邀请码不正确');
			}
		}
		$params['pid'] = intval($check_puser['id']);


		$params['pidg2'] = $check_puser['pidg2'];
		$params['pidg1'] = $check_puser['pidg1'];
		if ($cnf_register_type == 1) {
			$checkVcode = checkPhoneCode(['stype' => 1, 'phone' => $params['account'], 'code' => $params['vcode']]);
			if ($checkVcode['code'] != 1) {
				exit(json_encode($checkVcode));
			}
			$user_phone = Db::table('sys_user')->field(['id'])->whereRaw('phone=:phone', ['phone' => $params['account']])->find();
			if ($user_phone) {
				ReturnToJson(-1, '该手机号已被注册');
			}
		}
		// elseif ($cnf_register_type == 2) {
		// 	$checkVcode = checkEmailCode(['stype' => 1, 'email' => $params['account'], 'code' => $params['vcode']]);
		// 	if ($checkVcode['code'] != 1) {
		// 		exit(json_encode($checkVcode));
		// 	}
		// 	$user_account = Db::table('sys_user')->field(['id'])->whereRaw('account=:account', ['account' => $params['account']])->find();
		// 	if ($user_account) {
		// 		ReturnToJson(-1, '该邮箱已被注册');
		// 	}
		// }

		$user_phone = Db::table('sys_user')->field(['id'])->whereRaw('phone=:phone', ['phone' => $params['account']])->find();
		if ($user_phone) {
			ReturnToJson(-1, '该手机号已被注册');
		}
		$user_data = $this->doRegister($params);
		if (!$user_data) {
			ReturnToJson(-1, '注册失败');
		}

		$return_data = [
			'account' => $user_data['account'],
			'ererer' => $user_data['erererer'],
			//'$pids' => $pids,
		];
		ReturnToJson(1, '注册成功', $return_data);
	}

	//执行注册写入
	private function doRegister($params, $wx_user = [])
	{
		$user_data = [
			'gid' => 92,
			'icode' => genIcode(),
			'password' => getPassword($params['password']),
			'password2' => getPassword($params['password']),
			'balance' => 0,
			'cbank' => 0,
			'teamcount' => 0,
			'pid' => intval($params['pid']),
			'nickname' => $params['nickname'] ? $params['nickname'] : 'nk' . substr(getRsn(), 2, 8),
			'account' => $params['account'],
			'openid' => $params['account'],
			'unionid' => $params['account'],
			'email' => $params['email'],
			'province' => $params['province'],
			'reg_time' => NOW_TIME,
			'reg_ip' => CLIENT_IP,
			'headimgurl' => 'public/avatar/headimgurl.jpg',
			'gift' => 0,
			'pidg1' => $params['pidg1'],
			'pidg2' => $params['pidg2'],
		];

		if ($params['phone']) {
			$user_data['phone'] = $params['phone'];
		}

		if ($params['realname']) {
			$user_data['realname'] = $params['realname'];
		} else {
			$user_data['realname'] = $user_data['nickname'];
		}

		if (!$wx_user && isWx()) {
			session_start();
			$wx_user = $_SESSION['wx_user'];
		}

		if (isWx()) {
			session_start();
			$icode = $_SESSION['icode'];
			if ($icode) {
				$puser = Db::table('sys_user')->field('id,status')->where("icode='{$icode}'")->find();
				if ($puser && $puser['status'] == 2) {
					$user_data['pid'] = $puser['id'];
				}
			}
		}

		if ($wx_user) {
			$user_data['nickname'] = $wx_user['nickname'];
			$user_data['openid'] = $wx_user['openid'];
			$user_data['unionid'] = $wx_user['unionid'];
			$user_data['headimgurl'] = $wx_user['headimgurl'];
			$user_data['avatarurl'] = $wx_user['avatarurl'];
			$user_data['sex'] = $wx_user['sex'];
			$user_data['country'] = $wx_user['country'];
			$user_data['province'] = $wx_user['province'];
			$user_data['city'] = $wx_user['city'];
		}

		$res = 0;
		$count = 0;
		do {
			$count++;
			$user_data['id'] = mt_rand(100000, 999999);
			$res = Db::table('sys_user')->insertGetId($user_data);
			if ($res) {
				break;
			}
			if ($count > 100) {
				break;
			}
		} while (true);
		if (!$res) {
			return false;
		}
		$sq = 0;
		$sql = "update sys_user inner join(select  t.id, CONCAT(t.pid,',',u1.pid,',',u2.pid) vpids  
						from sys_user t left join sys_user u1 on u1.id=t.pid left join sys_user u2 on u2.id=u1.pid ) b
						on sys_user.id=b.id
						set sys_user.pids=b.vpids where sys_user.id={$res};"; //更新当前用户的 pids
		$sq += Db::execute($sql);

		$sql = "WITH RECURSIVE cte AS (SELECT id, pid, gid FROM sys_user WHERE id = {$res}
						UNION ALL SELECT t.id, t.pid, t.gid FROM sys_user t JOIN cte ON t.id = cte.pid) 
						UPDATE sys_user
						SET pidg1 = (SELECT id FROM cte WHERE gid = 71),
						pidg2 = (SELECT id FROM cte WHERE gid = 81) 
						WHERE sys_user.id ={$res}"; //更新当前用户的 pidg1 pidg2	   
		$sq += Db::execute($sql);
		createWallet($res); //创建钱包
		$user_data['id'] = $res;
		return $user_data;
	}

	//找回密码
	public function _forget()
	{
		$return_data = [
			'reg_type' => getConfig('cnf_register_type') == '1' ? 1 : 2
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _forgetAct()
	{
		$params = $this->params;
		if (!$params['account']) {
			ReturnToJson(-1, '请填写账号');
		}
		$params['account'] = strtolower($params['account']);
		$cnf_register_type = getConfig('cnf_register_type');
		if ($cnf_register_type == 1) {
			if (!isPhone($params['account'])) {
				ReturnToJson(-1, '请填写正确的手机号');
			}
			if (!$params['scode']) {
				ReturnToJson(-1, '请填写短信验证码');
			}
			$params['vcode'] = $params['scode'];
		} else {
			if (!isEmail($params['account'])) {
				ReturnToJson(-1, '请填写正确的邮箱');
			}
			if (!$params['ecode']) {
				ReturnToJson(-1, '请填写邮件验证码');
			}
			$params['vcode'] = $params['ecode'];
		}


		// $session_id = $params['sid'];
		// if (!$session_id) {
		// 	ReturnToJson(-1, '缺少验证参数');
		// }
		// $mem_key = 'vcode_' . $session_id;
		// $code = $this->redis->get($mem_key);
		// $this->redis->rm($mem_key);
		// if (!$params['imgcode'] || $params['imgcode'] != $code) {
		// 	ReturnToJson(-1, '图形验证码不正确');
		// }

		if (!$params['password']) {
			ReturnToJson(-1, '请填写新密码');
		} else {
			if ($params['password'] == md5('123456')) {
				ReturnToJson(-1, '密码过于简单，请更换再试');
			}
		}

		if ($cnf_register_type == 1) {
			$checkVcode = checkPhoneCode(['stype' => 3, 'phone' => $params['account'], 'code' => $params['vcode']]);
			if ($checkVcode['code'] != 1) {
				exit(json_encode($checkVcode));
			}
		}
		// else {
		// 	$checkVcode = checkEmailCode(['stype' => 3, 'email' => $params['account'], 'code' => $params['vcode']]);
		// 	if ($checkVcode['code'] != 1) {
		// 		exit(json_encode($checkVcode));
		// 	}
		// }
		$user = Db::table('sys_user')->whereRaw('account=:account', ['account' => $params['account']])->find();
		if (!$user['id']) {
			ReturnToJson(-1, '不存在该账号');
		}
		$user_data = [
			'password' => getPassword($params['password'])
		];
		$res = updateUserinfo($user['id'], $user_data);
		if (!$res) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		clearToken($user['id']);
		$return_data = [
			'account' => $user['account']
		];
		ReturnToJson(1, '找回成功', $return_data);
	}

	/////////////////////////////////////////////////////////////////////
	//设置语言
	public function _language()
	{
		$language = $this->params['lang'];
		if (!array_key_exists($language, $_ENV['LANG_ARR'])) {
			$language = $_ENV['LANG_DEF'];
		}
		session_start();
		$_SESSION['language'] = $language;
		$pageuser = checkLogin();
		if ($pageuser) {
			$user_data = [
				'language' => $language
			];
			$res = updateUserinfo($pageuser['id'], $user_data);
		}
		ReturnToJson(1, '操作成功');
	}

	/////////////////////////////////////////////////////////////////////

	//获取用户信息
	public function _userinfo()
	{
		$pageuser = checkLogin();
		if (!$pageuser) {
			ReturnToJson(-98, '请先登录', ['is_login' => $pageuser]);
		}
		$sys_group = getConfig('sys_group');
		$user_data = [
			'id' => $pageuser['id'],
			'gid' => $pageuser['gid'],
			'gname' => $sys_group[$pageuser['gid']],
			'account' => $pageuser['account'],
			//'unionid'=>$pageuser['unionid'],
			'openid' => $pageuser['openid'],
			'icode' => $pageuser['icode'],
			'phone' => $pageuser['phone'],
			'email' => $pageuser['email'],
			//'coin'=>$pageuser['coin'],
			'balance' => $pageuser['balance'],
			'fz_balance' => $pageuser['fz_balance'],
			'nickname' => $pageuser['nickname'],
			'realname' => $pageuser['realname'],
			'headimgurl' => $pageuser['headimgurl'],
			//'avatarurl'=>$pageuser['avatarurl'],
			'login_ip' => $pageuser['login_ip'],
			'login_time' => date('Y-m-d H:i:s', $pageuser['login_time'])
		];
		if ($user_data['phone']) {
			$user_data['phone_flag'] = substr($user_data['phone'], 0, 3) . '***' . substr($user_data['phone'], -3);
		} else {
			$user_data['phone_flag'] = '';
		}
		if ($this->params['is_ht']) {
			$user_data['menus'] = getUserMenu($pageuser['id']);
			$user_data['nkeys'] = getUserNkey($pageuser['id']);
			$open_menus = [];
			foreach ($user_data['menus'] as $mv) {
				if (in_array($mv['path'], ['/news', '/sys'])) {
					continue;
				}
				$open_menus[] = $mv['path'];
			}
			$user_data['open_menus'] = $open_menus;
		}
		ReturnToJson(1, 'ok', $user_data);
	}

	//获取系统必须配置
	public function _getConfig()
	{
		$cnf_domain = trim(trim($_ENV['cnf_domain']), '/');
		$language_code = 'en-us';
		$return_data = [
			//'img_url' => REQUEST_SCHEME . '://' . HTTP_HOST,
			'name' => getConfig('sys_name'),
			'version' => 'v1',
			'yes_or_no' => ['1' => '是', '0' => '否'],
			'is_mobile' => isMobileReq(),
			'language' => loadLang(),
			'language_code' => $language_code,
			'language_name' => $_ENV['LANG_ARR'][$language_code],
			'language_arr' => $_ENV['LANG_ARR']
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//获取图形验证码
	public function _getVcode()
	{
		$num = 4;
		$code = getVarifyCode($num);
		$session_id = getRsn();
		$mem_key = 'vcode_' . $session_id;
		$tt = $this->redis->set($mem_key, strtolower($code), 300);
		$return_data = [
			'session_id' => $session_id,
			't' => 'is ok!-1.21',
			//'code' => (HTTP_HOST == '172.21.221.245' || HTTP_HOST == 'localhost:8000') ? $code : '',
			'url' => '/api/?m=' . MODULE_NAME . '&a=showVcode&sid=' . $session_id
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//显示图形验证码
	public function _showVcode()
	{
		$session_id = $this->params['sid'];
		$mem_key = 'vcode_' . $session_id;
		$code = $this->redis->get($mem_key);
		if (!$code) {
			ReturnToJson(-1, '验证码已过期');
		}
		drawVarifyCode($code, 24, 100, 40);
	}

	//获取短信验证码
	public function _getPhoneCode()
	{
		//ReturnToJson(1,'test');
		$phone = $this->params['phone'];
		if (!$phone) {
			$user = checkLogin();
			if (!$user) {
				ReturnToJson(-1, '缺少手机号');
			}
			$phone = $user['phone'];
			if (!$phone) {
				ReturnToJson(-1, '未绑定手机号');
			}
		}
		$stype = intval($this->params['stype']);
		if (!isPhone($phone)) {
			ReturnToJson(-1, '手机号不正确');
		}
		if (!$stype) {
			ReturnToJson(-1, '验证码类型不正确');
		}
		$data = [
			'phone' => $phone,
			'stype' => $stype
		];
		$res = getPhoneCode($data);
		exit(json_encode($res));
	}

	//获取邮箱验证码
	public function _getEmailCode()
	{
		// $email = $this->params['email'];
		// if (!$email) {
		// 	$user = checkLogin();
		// 	if (!$user) {
		// 		ReturnToJson('-1', '邮箱不正确');
		// 	}
		// 	$email = $user['account'];
		// }
		// $stype = intval($this->params['stype']);
		// if (!isEmail($email)) {
		// 	ReturnToJson('-1', '邮箱不正确');
		// }
		// if (!$stype) {
		// 	ReturnToJson('-1', '验证码类型不正确');
		// }
		// $data = [
		// 	'email' => $email,
		// 	'stype' => $stype
		// ];
		// $res = getEmailCode($data);
		// $res['msg'] = lang($res['msg']);
		// exit(json_encode($res));
	}

	//获取省市
	public function _getPc()
	{
		checkLogin();
		$id = intval($this->params['id']);
		$list = Db::table('cnf_area')
			->field(['id', 'name'])
			->whereRaw("pid=:pid", ['pid' => $id])
			->select()->toArray();
		$return_data = [
			'list' => $list
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//文件上传
	public function _upload()
	{
		checkLogin();
		$upload = new UploadFile();
		$upload->maxSize = 100 * 1024 * 1024;
		$info = $upload->upload();
		if (!$info) {
			ReturnToJson(-1, $upload->getErrorMsg());
		} else {
			$up_data = [];
			foreach ($upload->getUploadFileInfo() as $file) {
				$up_data[] = [
					'src' => 'uploads/' . trim($file['savename'], '/'),
					'oriName' => $file['name'],
					'name' => basename($file['savename'])
				];
			}
			$return_data = $up_data[0];
			ReturnToJson(1, 'ok', $return_data);
		}
	}

	//base64图片上传
	public function _uploadImg64()
	{
		checkLogin();
		$base64_image_content = $_POST['imgdata'];
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
			$type = 'jpeg';
			$save_path = 'uploads/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
			$dir_path = ROOT_PATH . $save_path;
			if (!is_dir($dir_path)) {
				mkdir($dir_path, 0755, true);
			}
			$filename = getRsn() . ".{$type}";
			$new_file = $dir_path . $filename;
			$save_res = file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)));
			if (!$save_res) {
				ReturnToJson(-1, '图片上传失败');
			}
		} else {
			ReturnToJson(-1, '参数错误');
		}
		$return_data = [
			'src' => $save_path . $filename
		];
		ReturnToJson(1, '上传成功', $return_data);
	}

	protected function param($key = '')
	{
		$params = getParam($key);
		return $params;
	}

	protected function display()
	{
		$args = func_get_args();
		$tpl = '';
		$data = [];
		if (count($args) < 2) {
			$tpl = CONTROLLER_NAME . '/' . ACTION_NAME . '.html';
			if ($args[0]) {
				$data = $args[0];
			}
		} else {
			$tpl = $args[0];
			$data = $args[1];
		}
		if (!$data['title']) {
			$data['title'] = getConfig('sys_name');
		}
		display($tpl, $data, $args[2]);
	}

	//获取下级-缓存
	protected function getDownUser($uid, $need_all = false, $agent_level = 1, $agent_level_limit = 0)
	{
		return;
		if (!$need_all) {
			$mem_key = 'duser_' . $agent_level . '_' . $agent_level_limit . '_' . $uid;
		} else {
			$mem_key = 'duser2_' . $agent_level . '_' . $agent_level_limit . '_' . $uid;
		}
		$down_arr = $this->redis->get($mem_key);
		if (!$down_arr) {
			$down_arr = getDownUser($uid, $need_all, $agent_level, $agent_level_limit);
			if (!$down_arr) {
				$down_arr = [];
			}
			$this->redis->set($mem_key, $down_arr, 3600);
		}
		return $down_arr;
	}
}
