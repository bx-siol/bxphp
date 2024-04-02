<?php

use Curl\Curl;
use think\facade\Db;

function GetPayName()
{
	return "cowpay";
}
function payOrder($fin_paylog, $sub_type = '')
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];	
	$pdata = [
		'merchant_code' => $config['mch_id'],				//	M	string	20	商户编号	平台分配的唯一编号
		'order_no' => $fin_paylog['osn'],					//	M	string	30	商户订单号	平商户订单号，不可重复，最长30位
		'order_amount' => $fin_paylog['money'],				//	M	string		交易金额	单位：inr（只支持整数）
		'order_time' => time(),							//	M	string	15	交易时间	时间戳，纯数字
		'product_name' => $fin_paylog['osn'],				//	M	string	60	产品名称	请尽量不要传固定值，否则会影响成功率；请尽量不要带空格。
		'notify_url' => $config['notify_url'],				//	M	string	254	异步通知地址	异步回调通知地址，不支持参数传递
		'pay_type' => 'india-upi-h5',							//	M	string	30	支付类型	指定支付方式，详见 [支付类型]
		'return_url' => $config['returnUrl'],				//	C	string	30	成功回跳地址	提交成功后跳转的地址，非必填，但建议商户也传递该字段
		//'payer_info' => $fin_paylog['receive_realname'],	//	C	string	30	付款人姓名	付款人姓名
	];

	$rdata['signtype'] = "MD5";
	$rdata['sign'] = urlencode(strtoupper(paySign($pdata)));
	$rdata['transdata'] = urlencode(json_encode($pdata));

	writeLog("pdata：" .json_encode($pdata)."\r\n"."rdata：".json_encode($rdata), GetPayName() . '/pay');
	$result = curl_post($config['pay_url'], $rdata, 30,'json');
	writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	if ($result['response_code'] != 200)
		return $result;

	$resultArr = json_decode($result['output']);
	writeLog("resultArr：".json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay');
	writeLog("resultArr：code:".$resultArr['code'], GetPayName() . '/pay');
	if ($resultArr['code'] != 0) {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/pay/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}

	writeLog("11111111111", GetPayName() . '/pay');
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'mch_id' => $config['mch_id'],
			'osn' => $fin_paylog['osn'],
			'out_osn' => $resultArr['orderNo'],
			'pay_url' => $resultArr['payUrl']
		]
	];
	return $return_data;
}

//查询余额
function balance()
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$pdata = [
		'merId' => $config['mch_id'],
		'nonceStr' => getRsn()
	];
	$pdata['sign'] = paySign($pdata);
	$url = $config['balance_url'];
	writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	$result = CurlPost($url, $pdata, 30);
	if ($result['code'] != 1)
		return $result;
	$resultArr = $result['output'];
	writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance');
	if ($resultArr['code'] != '1') {
		writeLog(json_encode($resultArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/balance/error');
		return ['code' => -1, 'msg' => $resultArr['msg']];
	}
	$return_data = [
		'code' => 1,
		'msg' => $resultArr['msg'],
		'data' => [
			'merId' => $config['mch_id'],
			'balance' => $resultArr['data']['balance'],
			'payout_balance' => $resultArr['data']['payout_balance'],
		]
	];
	return $return_data;
}


function paySign($params, $verify = false)
{
	$config = $_ENV['PAY_CONFIG'][GetPayName()];
	$appSecret = $config['mch_key'];
	$signOriginStr = '';
	ksort($params);
	foreach ($params as $key => $value) 
		$signOriginStr = "$signOriginStr$key=$value&";
	
	$signOriginStr = $signOriginStr . "key=$appSecret";	
    return  md5($signOriginStr);
}