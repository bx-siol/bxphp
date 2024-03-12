<?php
use Curl\Curl;
use think\facade\Db;

function offlineCashOrder($fin_cashlog){
	
	$return_data=[
		'code'=>1,
		'msg'=>'ok',
		'data'=>[
			'mch_id'=>'',
			'osn'=>$fin_cashlog['osn'],
			'out_osn'=>''
		]
	];
	return $return_data;
}

?>