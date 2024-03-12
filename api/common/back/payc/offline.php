<?php
use Curl\Curl;
use think\facade\Db;

//线下支付

function payOrder($fin_paylog, $sub_type = '')
{
	$pay_url = '/h5/#/payinfo?osn=' . $fin_paylog['osn'];
	$return_data = [
		'code' => 1,
		'msg' => 'ok',
		'data' => [
			'osn' => $fin_paylog['osn'],
			'pay_url' => $pay_url
		]
	];
	return $return_data;
}

?>