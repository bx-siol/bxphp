<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "hbopay";
}
function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'appId' => $config['mch_id'],
		'mchOrderNo' => $fin_paylog['osn'],
		'amount' => strval($fin_paylog['money']),
		'currency' => 'INR',
		'notifyUrl' => $config['notify_url'],
		//'successJumpUrl' => '',
        //'failJumpUrl' => '',
        'body' => $fin_paylog['osn'],
        'notifyFormat' => 'FORM-DATA',
		'my_order_no' => ''
	];
	$pdata['sign'] = paySign($pdata);

	writeLog(json_encode($pdata), GetPayName() . '/pay');
	$result = CurlPost($config['pay_url'], $pdata, 30);
	if ($result['code'] != 1)
		return $result;

    $resultArr = $result['output'];
	if ($resultArr['code'] != 1) {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['data']['my_order_no'],
			'pay_url' => $resultArr['data']['pay_jump_url']
		]
	];
	return $return_data;
}

//查询余额
function balance()
{
	return ['code' => -1, 'msg' => '没有查询余额的接口，去商户后台查'];
}


function paySign($params, $verify = false)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$signOriginStr =$params['appId'] .$params['mchOrderNo'] .$params['my_order_no'] .$params['amount'] .$params['notifyUrl'] .$config['mch_key'];
	$sign = md5($signOriginStr);
	return  $sign;
}
