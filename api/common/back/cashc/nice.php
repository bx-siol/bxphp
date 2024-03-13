<?php
use Curl\Curl;
use think\facade\Db;

$_ENV['PAY_CONFIG']['nice']=[
	'mch_id'=>'xxx',
	'mch_key'=>'xxx',
	'dpay_url'=>'http://xxx.shop/gateway/',
	'dnotify_url'=>$_ENV['API_URL'].'api/Notify/Nice/cash'
];

function niceCashOrder($fin_cashlog){
	if(strlen($fin_cashlog['receive_account'])<14){
		//return ['code'=>-1,'msg'=>'Account is too short'];
	}
	$config=$_ENV['PAY_CONFIG']['nice'];
	//$bank=Db::table('cnf_bank')->where("id={$fin_cashlog['receive_bank_id']}")->find();
	$rand_arr=[6,7,8,9];
	$phone=$rand_arr[mt_rand(0,count($rand_arr)-1)].mt_rand(1000,9999).mt_rand(10000,99999);
	$pdata=[
		'mer_no'=>$config['mch_id'],
		'order_no'=>$fin_cashlog['osn'],
		'method'=>'fund.apply',
		'order_amount'=>$fin_cashlog['real_money'],
		'currency'=>'INR',
		'acc_name'=>$fin_cashlog['receive_realname'],
		'acc_code'=>'BANK',
		'acc_no'=>$fin_cashlog['receive_account'],
		'province'=>$fin_cashlog['receive_ifsc'],
		'acc_email'=>getRsn().'@gmail.com',
		'acc_phone'=>$phone,
		'returnurl'=>$config['dnotify_url']
	];
	$pdata['sign']=niceCashSign($pdata);
	$url=$config['dpay_url'];
	file_put_contents(LOGS_PATH.'cashOrderNice.txt',$url."\r\n".var_export($pdata,true)."\r\n\r\n",FILE_APPEND);
	$result=niceCurlPost($url,$pdata,30);
	if($result['code']!=1){
		return $result;
	}
	$resultArr=$result['output'];
	file_put_contents(LOGS_PATH.'cashOrderNiceResult.txt',$url."\r\n".var_export($resultArr,true)."\r\n\r\n",FILE_APPEND);
	if($resultArr['status']!='success'){
		return ['code'=>-1,'msg'=>$resultArr['status_mes']];
	}
	$return_data=[
		'code'=>1,
		'msg'=>$result['msg'],
		'data'=>[
			'mch_id'=>$config['mch_id'],
			'osn'=>$fin_cashlog['osn'],
			'out_osn'=>$resultArr['sys_no']
		]
	];
	return $return_data;
}

function niceCashSign($pdata){
	$config=$_ENV['PAY_CONFIG']['nice'];
	ksort($pdata);
	$str='';
	foreach($pdata as $pk=>$pv){
		if($pk=='sign'||!$pv){
			continue;
		}
		$str.="{$pk}={$pv}&";
	}
	$str=trim($str,'&');
	$str.=$config['mch_key'];
	return md5($str);
}

function niceCurlPost($url,$data=[],$timeout=30){
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => $timeout,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS =>json_encode($data,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		)
	));

	$response = curl_exec($curl);
	if($curl->error){
		$arrCurlResult=[
			'code'=>-1,
			'msg'=>$curl->errorMessage
		];
	}else{
		$arrCurlResult=[
			'code'=>1,
			'msg'=>'ok',
			'output'=>json_decode($response,true)
		];
	}
	curl_close($curl);
	unset($curl);
	return $arrCurlResult;
}

?>