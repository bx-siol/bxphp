<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class SettingController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	//手机号
	public function _phone()
	{
		$pageuser = checkLogin();
		$return_data = [
			'phone' => $pageuser['phone']
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _phoneAct()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		if (!$params['phone']) {
			ReturnToJson(-1, 'Please fill in your phone number.');
		} else {
			if (!isPhone($params['phone'])) {
				ReturnToJson(-1, 'Mobile phone number is incorrect.');
			}
			if ($params['phone'] == $pageuser['phone']) {
				ReturnToJson(-1, 'Mobile phone number has not changed.');
			}
		}
		if (!$params['scode']) {
			ReturnToJson(-1, 'Please fill in the verification code.');
		} else {
			$checkScode = checkPhoneCode(['stype' => 3, 'phone' => $params['phone'], 'code' => $params['scode']]);
			if ($checkScode['code'] != 1) {
				exit (json_encode($checkScode));
			}
		}
		$user = [];
		try {
			$sys_user = [
				'phone' => $params['phone']
			];
			$user = updateUserinfo($pageuser['id'], $sys_user);
		} catch (\Exception $e) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
		ReturnToJson(1, 'Binding successful.');
	}


	//修改密码
	public function _pwdAct()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		if (!$params['old_pwd']) {
			ReturnToJson(-1, 'Please enter original password.');
		}
		if (!$params['new_pwd']) {
			ReturnToJson(-1, 'Please enter a new password.');
		}
		if (!$params['imgcode']) {
			ReturnToJson(-1, 'Please fill in the graphic verification code.');
		}
		$session_id = $params['sid'];
		if (!$session_id) {
			ReturnToJson(-1, 'Missing validation parameters.');
		}
		$mem_key = 'vcode_' . $session_id;
		$code = $this->redis->get($mem_key);
		$this->redis->rm($mem_key);
		if ($params['imgcode'] != $code) {
			ReturnToJson(-1, 'The graphic verification code is incorrect.');
		}
		$old_pwd = getPassword($params['old_pwd']);
		$new_pwd = getPassword($params['new_pwd']);
		$sys_user = [];
		if ($params['type'] == 1) {
			if ($pageuser['password'] != $old_pwd) {
				ReturnToJson(-1, 'The original password is incorrect.');
			}
			$sys_user['password'] = $new_pwd;
		} else {
			if ($pageuser['password2'] != $old_pwd) {
				ReturnToJson(-1, 'The original payment password is incorrect.');
			}
			$sys_user['password2'] = $new_pwd;
		}
		$user = [];
		try {
			$user = updateUserinfo($pageuser['id'], $sys_user);
		} catch (\Exception $e) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
		ReturnToJson(1, 'Successfully modified.');
	}

	//#################################################################

	//修改个人信息
	public function _uinfo_update()
	{
		$pageuser = checkLogin();
		$params = $this->params;


		// if (!$params['nickname']) {
		// 	ReturnToJson(-1, 'Please fill in nickname.');
		// }
		// if (!$params['password2']) {
		// 	ReturnToJson(-1, 'Please fill out the payment password.');
		// } else {
		// 	if (getPassword($params['password2']) != $pageuser['password2']) {
		// 		ReturnToJson(-1, 'The payment password is incorrect.');
		// 	}
		// }
		$sys_user = [];
		if ($params['nickname']) {
			$sys_user['nickname'] = $params['nickname'];
			$sys_user['realname'] = $params['realname'];
		}
		if ($params['headimgurl']) {
			$sys_user['headimgurl'] = $params['headimgurl'];
		}
		if ($params['sex']) {
			$params['sex'] = intval($params['sex']);
			if ($params['sex'] > 0) {
				$sys_user['sex'] = $params['sex'] == 1 ? 1 : 2;
			} else {
				$sys_user['sex'] = 0;
			}
		}
		if ($params['birthday']) {
			$sys_user['birthday'] = strtotime($params['birthday'] . ' 00:00:01');
		}
		if (!$sys_user) {
			ReturnToJson(1, 'Successful operation.');
		}
		$user = [];
		try {
			$user = updateUserinfo($pageuser['id'], $sys_user);
		} catch (\Exception $e) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
		$return_data = [
			'nickname' => $user['nickname'],
			'headimgurl' => $user['headimgurl']
		];
		ReturnToJson(1, 'Successful operation.', $return_data);
	}

	//修改密码
	public function _password_update()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		if (!$params['scode'] && !$params['ecode']) {
			ReturnToJson(-1, 'Please fill in the verification code.');
		}
		if (!$params['password']) {
			ReturnToJson(-1, 'Please fill in new password.');
		}
		if ($params['scode']) {
			$checkScode = checkPhoneCode(['stype' => 3, 'phone' => $pageuser['phone'], 'code' => $params['scode']]);
			if ($checkScode['code'] != 1) {
				exit (json_encode($checkScode));
			}
		} else {
			// $checkEcode = checkEmailCode(['stype' => 3, 'email' => $pageuser['account'], 'code' => $params['ecode']]);
			// if ($checkEcode['code'] != 1) {
			// 	exit(json_encode($checkEcode));
			// }
		}
		$password = getPassword($params['password']);
		$sys_user = [];
		if ($params['type'] == 1) {
			$sys_user['password'] = $password;
		} elseif ($params['type'] == 2) {
			$sys_user['password2'] = $password;
		} else {
			ReturnToJson(-1, 'Parameter error.');
		}
		$user = [];
		try {
			$user = updateUserinfo($pageuser['id'], $sys_user);
		} catch (\Exception $e) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
		ReturnToJson(1, 'Successful operation.');
	}

	//谷歌认证
	// public function _google()
	// {
	// 	$pageuser = checkLogin();
	// 	$user = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
	// 	$ga = new PHPGangsta_GoogleAuthenticator();
	// 	if (!$user['google_secret']) {
	// 		$google_secret = $ga->createSecret();
	// 		$sys_user = ['google_secret' => $google_secret];
	// 		$user = updateUserinfo($user['id'], $sys_user);
	// 	}
	// 	$user['google_qrcode'] = $ga->getQRCodeGoogleUrl($user['account'], $user['google_secret']);
	// 	$sys_switch = getConfig('sys_switch');
	// 	$return_data = [
	// 		'user' => [
	// 			'account' => $user['account'],
	// 			'google_secret' => $user['google_secret'],
	// 			'google_qrcode' => $user['google_qrcode'],
	// 			'is_google' => $user['is_google']
	// 		],
	// 		'sys_switch' => $sys_switch
	// 	];
	// 	ReturnToJson(1, 'ok', $return_data);
	// }

	public function _google_update()
	{
		// $pageuser = checkLogin();
		// $params = $this->params;
		// $params['is_google'] = intval($params['is_google']);
		// if ($params['is_google']) {
		// 	$params['is_google'] = 1;
		// }
		// $user = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
		// if (!$params['password']) {
		// 	ReturnToJson(-1, 'Please fill out the payment password.');
		// }
		// $password2 = getPassword($params['password']);
		// if ($user['password2'] != $password2) {
		// 	ReturnToJson(-1, 'The payment password is incorrect.');
		// }
		// $sys_user = [
		// 	'is_google' => $params['is_google']
		// ];
		// $user = updateUserinfo($user['id'], $sys_user);
		ReturnToJson(1, 'Successful operation.');
	}

	public function _google_secret()
	{
		// $pageuser = checkLogin();
		// $ga = new PHPGangsta_GoogleAuthenticator();
		// $google_secret = $ga->createSecret();
		// $sys_user = ['google_secret' => $google_secret];
		// $user = updateUserinfo($pageuser['id'], $sys_user);
		// $google_qrcode = $ga->getQRCodeGoogleUrl($user['account'], $user['google_secret']);
		// $return_data = [
		// 	'google_secret' => $google_secret,
		// 	'google_qrcode' => $google_qrcode
		// ];
		// ReturnToJson(1, 'Successful operation.', $return_data);
	}

	//实名认证
	public function _auth()
	{
		$pageuser = checkLogin();
		$auth = Db::table('sys_user_rauth')->where("uid={$pageuser['id']}")->field(['type', 'realname', 'number', 'status'])->find();
		if (!$auth) {
			$auth = [];
		}
		$return_data = [
			'auth' => $auth
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _auth_update()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['type'] = intval($params['type']);
		if (!$params['realname']) {
			ReturnToJson(-1, 'Please fill in your name.');
		}
		if (!$params['type']) {
			ReturnToJson(-1, 'Please select the document type.');
		} else {
			if (!in_array($params['type'], [1, 2, 3, 4])) {
				ReturnToJson(-1, 'unknown type.');
			}
		}
		if (!$params['number']) {
			ReturnToJson(-1, 'Please fill in the ID number.');
		}

		$db_data = [
			'uid' => $pageuser['id'],
			'realname' => $params['realname'],
			'type' => $params['type'],
			'number' => $params['number'],
			'status' => 3,
			'update_time' => NOW_TIME
		];
		$model = Db::table('sys_user_rauth');
		try {
			$auth = $model->where("uid={$pageuser['id']}")->find();
			if ($auth) {
				if ($auth['status'] == 3) {
					//ReturnToJson(-1,'You have completed the certification.');
				}
				$res = $model->where("uid={$auth['uid']}")->update($db_data);
			} else {
				$db_data['create_time'] = NOW_TIME;
				$res = $model->insert($db_data);
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
		//更新到个人信息
		updateUserinfo($pageuser['id'], ['realname' => $params['realname']]);
		ReturnToJson(1, 'Submitted successfully.');
	}

	//收款方式
	public function _bank()
	{
		$pageuser = checkLogin();
		$bank = Db::table('cnf_banklog')->where("uid={$pageuser['id']}")->find();
		$user = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
		if (!$bank) {
			$bank = [];
		} else {
			unset($bank['id']);
		}
		$bank_arr = Db::table('cnf_bank')->select()->toArray();
		$return_data = [
			'bank' => $bank,
			'user' => $user,
			'bank_arr' => $bank_arr
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _bank_update()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['bank_id'] = intval($params['bank_id']);

		$project = getConfig("sys_name");
		if($project != "Syngenta")
			if (!$params['code'])
				ReturnToJson(-1, 'Please enter OTP');

		if (!$params['realname'])
			ReturnToJson(-1, 'Please fill in the cardholder’s name.');
		if (!$params['bank_name'])
			ReturnToJson(-1, 'Please fill in the account opening bank.');
		if (!$params['account'])
			ReturnToJson(-1, 'Please enter bank account number.');
		if (!$params['ifsc'])
			ReturnToJson(-1, 'Please enter ifsc');
		if($project != "Syngenta")
		{
			$checkVcode = checkPhoneCode(['stype' => 9, 'phone' => $pageuser['account'], 'code' => $params['code']]);
			if ($checkVcode['code'] != 1)
				ReturnToJson(-1, $checkVcode['msg'] .' Please enter the correct OTP');
		}

		// if (strlen($params['ifsc']) < 8 || strlen($params['ifsc']) > 11) {
		// 	ReturnToJson(-1, 'The length of the ID number should be 8-11 digits.');
		// } 
		$ifsc = $params['ifsc'];

		// if ($pageuser['password2'] != getPassword($params['password2'])) {
		// 	ReturnToJson(-1, 'The payment password is incorrect.');
		// }
		$check_bank = Db::table('cnf_bank')->where("id={$params['bank_id']}")->find();
		if (!$check_bank) {
			ReturnToJson(-1, 'Please select a bank in the bank list or re-enter the current page.');
		}
		$banklog = [
			'type' => 1,
			'realname' => $params['realname'],
			'bank_id' => strval($check_bank['code']),
			'bank_name' => $check_bank['name'],
			'account' => $params['account'],
			'ifsc' => $ifsc
		];
		try {
			$bank = Db::table('cnf_banklog')->where("uid={$pageuser['id']}")->find();
			if (!$bank) {
				$banklog['create_time'] = NOW_TIME;
				$banklog['create_id'] = $pageuser['id'];
				$banklog['uid'] = $pageuser['id'];
				Db::table('cnf_banklog')->insertGetId($banklog);
			} else {
				Db::table('cnf_banklog')->where("id={$bank['id']}")->update($banklog);
			}
			//不更新用户的银行卡更改权限
			//Db::table('sys_user')->where("id={$pageuser['id']}")->update(['cbank' => 1]); 
		} catch (\Exception $e) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
		ReturnToJson(1, 'Submitted successfully.');
	}

	//收款方式
	public function _banklog()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);

		$where = "uid={$pageuser['id']} and log.status<99";
		$where .= empty ($params['s_keyword']) ? '' : " and (log.account='{$params['s_keyword']}' or log.realname='{$params['s_keyword']}')";

		$count_item = Db::table('cnf_banklog log')
			->leftJoin('cnf_bank b', 'log.bank_id=b.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['cnf_banklog' => 'log'], ['*'])
			->view(['cnf_bank' => 'b'], ['name' => 'bank_name'], 'log.bank_id=b.id', 'LEFT')
			->view(['cnf_currency' => 'c'], ['name' => 'currency_name'], 'log.currency_id=c.id', 'LEFT')
			->where($where)
			->order(['log.create_time' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_banklog_type = getConfig('cnf_banklog_type');
		foreach ($list as &$item) {
			$this->parseBanklog($item);
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
			$return_data['banklog_type'] = $cnf_banklog_type;
			$return_data['banks'] = Db::table('cnf_bank')->field(['id', 'name'])->where("status=2")->select()->toArray();
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _banklog_update()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['id'] = intval($params['id']);
		$params['type'] = intval($params['type']);
		$params['bank_id'] = intval($params['bank_id']);
		$params['province_id'] = intval($params['province_id']);
		$params['city_id'] = intval($params['city_id']);
		$params['currency_id'] = intval($params['currency_id']);
		$params['protocal'] = intval($params['protocal']);
		$cnf_banklog_type = getConfig('cnf_banklog_type');
		if (!array_key_exists($params['type'], $cnf_banklog_type)) {
			ReturnToJson(-1, 'unknown type.');
		}
		if ($params['type'] == 1) {
			if (!$params['bank_id']) {
				ReturnToJson(-1, 'Please select a bank.');
			}
			if (!$params['province_id'] || !$params['city_id']) {
				ReturnToJson(-1, 'Please select a province or city.');
			}
		} elseif (in_array($params['type'], [2, 3])) {
			if (!$params['qrcode']) {
				ReturnToJson(-1, 'Please upload the payment code.');
			}
		} elseif ($params['type'] == 4) { //数字钱包
			$currency = Db::table('cnf_currency')->where("id={$params['currency_id']}")->find();
			if (!$currency) {
				ReturnToJson(-1, 'Please select the correct currency.');
			}
			//必要时判断协议
			//...
			if (!$params['address']) {
				ReturnToJson(-1, 'Please fill in the wallet address.');
			}
		}

		if ($params['type'] < 4) {
			if (!$params['realname']) {
				ReturnToJson(-1, 'Please fill in your name.');
			}
			if (!$params['account']) {
				ReturnToJson(-1, 'Please fill in the account number.');
			}
		}

		if (!$params['password2']) {
			ReturnToJson(-1, 'Please fill out the payment password.');
		} else {
			if (getPassword($params['password2']) != $pageuser['password2']) {
				ReturnToJson(-1, 'The payment password is incorrect.');
			}
		}
		$db_item = [
			'type' => $params['type'],
			'bank_id' => $params['bank_id'],
			'province_id' => $params['province_id'],
			'city_id' => $params['city_id'],
			'realname' => $params['realname'],
			'account' => $params['account'],
			'qrcode' => $params['qrcode'],
			'routing' => $params['routing'],
			'remark' => $params['remark'],
			'address' => $params['address'],
			'currency_id' => $params['currency_id'],
			'protocal' => $params['protocal'],
			'status' => 2
		];
		if ($params['type'] == 1) {
			$db_item['qrcode'] = '';
		} else {
			$db_item['bank_id'] = 0;
			$db_item['province_id'] = 0;
			$db_item['city_id'] = 0;
		}
		try {
			$model = Db::table('cnf_banklog');
			if ($params['id']) {
				$item = $model->where("id={$params['id']} and uid={$pageuser['id']} and status<99")->find();
				if (!$item) {
					ReturnToJson(-1, 'No corresponding record exists.');
				}
				$model->where("id={$item['id']}")->update($db_item);
			} else {
				$db_item['uid'] = $pageuser['id'];
				$db_item['create_id'] = $pageuser['id'];
				$db_item['create_time'] = NOW_TIME;
				while (true) {
					try {
						$db_item['id'] = mt_rand(100000, 999999);
						$model->insertGetId($db_item);
					} catch (\Exception $e) {
						continue;
					}
					break;
				}
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
		ReturnToJson(1, 'Successful operation.');
	}

	public function _banklog_info()
	{
		$pageuser = checkLogin();
		$id = intval($this->params['id']);
		if (!$id) {
			ReturnToJson(-1, 'Missing parameters.');
		}
		$item = Db::table('cnf_banklog log')
			->field('log.*,b.name as bank_name,c.name as currency_name')
			->leftJoin('cnf_bank b', 'log.bank_id=b.id')
			->leftJoin('cnf_currency c', 'log.currency_id=c.id')
			->where("log.id={$id} and log.uid={$pageuser['id']} and log.status<99")->find();
		if (!$item) {
			ReturnToJson(-1, 'No corresponding record exists.');
		}
		$this->parseBanklog($item);
		ReturnToJson(1, 'ok', $item);
	}

	public function _banklog_delete()
	{
		$pageuser = checkLogin();
		$id = intval($this->params['id']);
		$model = Db::table('cnf_banklog');
		$item = $model->where("id={$id} and uid={$pageuser['id']} and status<99")->find();
		if (!$item) {
			ReturnToJson(-1, 'No corresponding record exists.');
		}
		$res = $model->where("id={$item['id']}")->delete();
		if (!$res) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
		ReturnToJson(1, 'Successful operation.');
	}

	private function parseBanklog(&$item)
	{
		$item['create_time'] = date('m-d H:i:s', $item['create_time']);
		$cnf_banklog_type = getConfig('cnf_banklog_type');
		$item['type_flag'] = $cnf_banklog_type[$item['type']];
		if ($item['protocal']) {
			$cnf_protocal = getConfig('cnf_protocal');
			$item['protocal_flag'] = $cnf_protocal[$item['protocal']];
		}
		if ($item['province_id']) {
			$item['province_name'] = getArea($item['province_id']);
		}
		if ($item['city_id']) {
			$item['city_name'] = getArea($item['city_id']);
		}
		unset($item['create_time'], $item['create_id'], $item['uid']);
		return $item;
	}
}