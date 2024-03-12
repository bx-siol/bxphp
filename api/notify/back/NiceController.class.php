<?php
!defined('ROOT_PATH') && exit;
use think\facade\Db;

class NiceController extends BaseController{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function _index(){
		echo 'notify';
	}
	
	public function _pay(){
		require_once APP_PATH.'common/pay/'.strtolower(CONTROLLER_NAME).'.php';
		
		$jsonStr=trim(file_get_contents('php://input'));
		$params=json_decode($jsonStr,true);
		if(!$params){
			$params=$_POST;
		}
		file_put_contents(LOGS_PATH.'payNotify'.CONTROLLER_NAME.'.txt',NOW_DATE."\r\n".var_export($params,true)."\r\n\r\n",FILE_APPEND);
		/*
		$params=array (
		  'mer_no' => '22033',
		  'order_no' => 'da8d3126ac304d3c',
		  'paytypecode' => '11003',
		  'order_amount' => '100.00',
		  'order_realityamount' => '0.00',
		  'status' => 'success',
		  'sys_no' => 'PS2205131503068276828824',
		  'sign' => '7e8e2e2917ad1a32f6e5ac66ea807c3f',
		);*/
		
		$sign=paySign($params);
		if($params['sign']!=$sign){
			jReturn(-1,'Sign error');
		}

		$pdata=[
			'code'=>$params['status']=='success'?1:-1,
			'osn'=>$params['order_no'],
			'amount'=>$params['order_realityamount'],
			'successStr'=>'ok'
		];
		
		$this->payAct($pdata);
	}
	
	public function _cash(){
		$jsonStr=trim(file_get_contents('php://input'));
		$params=json_decode($jsonStr,true);
		if(!$params){
			$params=$_POST;
			/*
			$params=array (
			  'mer_no' => '22025',
			  'order_no' => '7ce4d7f0f12192c5',
			  'order_amount' => '114.00',
			  'order_realityamount' => '114.00',
			  'currency' => 'INR',
			  'result' => 'success',
			  'sys_no' => 'PF2205021730148115',
			  'sign' => '6d0aceda882d762e7b77d6a61ab4a09b',
			);*/
		}
		
		$code=strtolower(CONTROLLER_NAME);
		require_once APP_PATH.'common/cash/'.$code.'.php';
		$log_file=LOGS_PATH.'dfNotify'.ucfirst($code).'.txt';
		file_put_contents($log_file,NOW_DATE."\r\n".var_export($params,true)."\r\n\r\n",FILE_APPEND);

		$signFunc=$code.'CashSign';
		if(!function_exists($signFunc)){
			jReturn(-1,'Sign func no exist');
		}
		$sign=$signFunc($params);
		if($params['sign']!=$sign){
			jReturn(-1,'Sign error');
		}
		
		//$order=Db::table('fin_cashlog')->where("osn='{$params['merchantCode']}'")->find();

		$pdata=[
			'osn'=>$params['order_no'],
			'out_osn'=>$params['sys_no'],
			'pay_status'=>$params['result']=='success'?9:3,
			'pay_msg'=>$params['status'],
			'amount'=>$params['order_amount'],
			'successStr'=>'ok',
			'failStr'=>'no'
		];
		$this->cashAct($pdata);
	}
	
}
?>