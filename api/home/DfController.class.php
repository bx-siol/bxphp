<?php
!defined('ROOT_PATH') && exit;
use think\facade\Db;

class DfController extends BaseController{
	
	public function __construct(){
		parent::__construct();
	}
	
	// private function dfNotifyAct($pdata=[]){
	// 	//###########################################
	// 	$osn=$pdata['osn'];//本地单号
	// 	$out_osn=$pdata['out_osn'];//通道单号
	// 	$pay_status=$pdata['pay_status'];//代付状态 9或3
	// 	$pay_msg=$pdata['pay_msg'];
	// 	$amount=$pdata['amount'];//付款额度
	// 	$successStr=$pdata['successStr']?$pdata['successStr']:'success';//处理成功后输出字符串
	// 	$failStr=$pdata['failStr']?$pdata['failStr']:'fail';//处理失败后输出字符串
	// 	//###########################################
		
	// 	$order=Db::table('fin_cashlog')->where("osn='{$osn}'")->find();
	// 	if(!$order){
	// 		jReturn(-1,'No order');
	// 	}
	// 	if($order['pay_status']!=2){
	// 		if($order['pay_status']==9){
	// 			exit($successStr);
	// 		}elseif($order['pay_status']==3){
	// 			//exit($failStr);
	// 		}else{
	// 			//exit($failStr);
	// 		}
	// 	}
	// 	if(abs($order['real_money']-$amount)>0.1){
	// 		jReturn(-1,'Money error');
	// 	}
	// 	Db::startTrans();
	// 	try{
	// 		$fin_cashlog=[
	// 			'pay_status'=>$pay_status,
	// 			'pay_msg'=>$pay_msg,
	// 			'pay_time'=>time()
	// 		];
	// 		if($out_osn){
	// 			$fin_cashlog['out_osn']=$out_osn;
	// 		}
	// 		Db::table('fin_cashlog')->where("id={$order['id']}")->update($fin_cashlog);
	// 		Db::commit();
	// 	}catch(\Exception $e){
	// 		Db::rollback();
	// 		jReturn(-1,$failStr);
	// 	}
	// 	echo $successStr;
	// }
	
	// public function _pay102(){
	// 	$params=$_POST;
	// 	/*
	// 	$params=array (
	// 	  'tradeResult' => '1',
	// 	  'merTransferId' => '7261dca606f9329b',
	// 	  'merNo' => '100010015',
	// 	  'tradeNo' => '1244799241',
	// 	  'transferAmount' => '115.90',
	// 	  'sign' => '789149f02cbb59e724a5b36f71200513',
	// 	  'signType' => 'MD5',
	// 	  'applyDate' => '2022-03-03 09:42:51',
	// 	  'version' => '1.0',
	// 	  'respCode' => 'SUCCESS'
	// 	);
	// 	*/
	// 	$code=ACTION_NAME;
	// 	require_once APP_PATH.'common/cash/'.$code.'.php';
	// 	$log_file=LOGS_PATH.'dfNotify'.ucfirst($code).'.txt';
	// 	file_put_contents($log_file,NOW_DATE."\r\n".var_export($params,true)."\r\n\r\n",FILE_APPEND);
		
	// 	$signFunc=$code.'CashSign';
	// 	if(!function_exists($signFunc)){
	// 		jReturn(-1,'Sign func no exist');
	// 	}
	// 	$sign=$signFunc($params);
	// 	if($params['sign']!=$sign){
	// 		jReturn(-1,'Sign error');
	// 	}
		
	// 	$pdata=[
	// 		'osn'=>$params['merTransferId'],
	// 		'out_osn'=>$params['tradeNo'],
	// 		'pay_status'=>$params['tradeResult']=='1'?9:3,
	// 		'pay_msg'=>$params['tradeResult'],
	// 		'amount'=>$params['transferAmount'],
	// 		'successStr'=>'success',
	// 		'failStr'=>'fail'
	// 	];
	// 	$this->dfNotifyAct($pdata);
	// }
	
	// public function _ocpay(){
	// 	$params=$_POST;
		
	// 	$code=ACTION_NAME;
	// 	require_once APP_PATH.'common/cash/'.$code.'.php';
	// 	$log_file=LOGS_PATH.'dfNotify'.ucfirst($code).'.txt';
	// 	file_put_contents($log_file,NOW_DATE."\r\n".var_export($params,true)."\r\n\r\n",FILE_APPEND);

	// 	$signFunc=$code.'CashSign';
	// 	if(!function_exists($signFunc)){
	// 		jReturn(-1,'Sign func no exist');
	// 	}
	// 	$sign=$signFunc($params);
	// 	if($params['signs']!=$sign){
	// 		jReturn(-1,'Sign error');
	// 	}
		
	// 	$pdata=[
	// 		'osn'=>$params['merissuingcode'],
	// 		'out_osn'=>$params['issuingcode'],
	// 		'pay_status'=>$params['returncode']=='SUCCESS'?9:3,
	// 		'pay_msg'=>$params['message'],
	// 		'amount'=>$params['amount'],
	// 		'successStr'=>'OK',
	// 		'failStr'=>'Fail'
	// 	];
	// 	$this->dfNotifyAct($pdata);
	// }
	
	// public function _recpay(){
	// 	$params=$_POST;
		
	// 	$code=ACTION_NAME;
	// 	require_once APP_PATH.'common/cash/'.$code.'.php';
	// 	$log_file=LOGS_PATH.'dfNotify'.ucfirst($code).'.txt';
	// 	file_put_contents($log_file,NOW_DATE."\r\n".var_export($params,true)."\r\n\r\n",FILE_APPEND);

	// 	$signFunc=$code.'CashSign';
	// 	if(!function_exists($signFunc)){
	// 		jReturn(-1,'Sign func no exist');
	// 	}
	// 	$sign=$signFunc($params);
	// 	if($params['sign']!=$sign){
	// 		jReturn(-1,'Sign error');
	// 	}
		
	// 	$pdata=[
	// 		'osn'=>$params['order_no'],
	// 		'out_osn'=>$params['trade_no'],
	// 		'pay_status'=>$params['code']=='00'?9:3,
	// 		'pay_msg'=>$params['message'],
	// 		'amount'=>$params['amount'],
	// 		'successStr'=>'ok',
	// 		'failStr'=>'fail'
	// 	];
	// 	$this->dfNotifyAct($pdata);
	// }
	
}
 