<?php
!defined('ROOT_PATH') && exit;
use think\facade\Db;

class TransController extends BaseController{
	
	public function __construct(){
		parent::__construct();
    }
	
	public function _index(){
		$pageuser=checkPower();
		$lang_arr=Db::table('sys_lang')->where('is_show=1')->select()->toArray();
		$data=[
			'lang_arr'=>$lang_arr,
			'powerTransUpdate'=>hasPower($pageuser,'Trans_trans_update')?1:0,
			'powerTransSave'=>hasPower($pageuser,'Trans_trans_save')?1:0,
		];
		$this->display($data);
	}
	
	public function _trans_save(){
		$pageuser=checkPower();
		$items=$this->params['items'];
		foreach($items as $item){
			$sys_lang=[
				'tw'=>trim($item['tw']),
				'en'=>trim($item['en'])
			];
			Db::table('sys_lang')->where('id=:id',['id'=>$item['id']])->update($sys_lang);
		}
		$this->genLangFile();
		ReturnToJson('1','保存成功');
	}
	
	public function _trans_google(){
		$url='https://translation.googleapis.com/language/translate/v2';
		$lang_arr=Db::table('sys_lang')->select()->toArray();
		$pdata=[
			'source'=>'cn',
			'target'=>'en',
			'format'=>'text'
		];
		foreach($lang_arr as $lv){
			$pdata['q']=$lv['cn'];
			$result=curl_post($url,$pdata,30,'json');
			echo $result['output'];exit;
		}
	}
	
	private function genLangFile(){
		$lang_arr=Db::table('sys_lang')->select()->toArray();
		$tw=[];
		$en=[];
		foreach($lang_arr as $lv){
			if($lv['tw']){
				$tw[$lv['cn']]=$lv['tw'];
			}
			if($lv['en']){
				$en[$lv['cn']]=$lv['en'];
			}
		}
		file_put_contents(GLOBAL_PATH.'lang/zh-tw.php',"<?php\nreturn ".var_export($tw,true).";\n?>");
		file_put_contents(GLOBAL_PATH.'lang/en-us.php',"<?php\nreturn ".var_export($en,true).";\n?>");
		return true;
	}
	
	public function _trans_update(){
		$pageuser=checkPower();
		$data=['其他杂项','常见问答','新闻公告'];
		//'title'=>'设置',
		//ReturnToJson('-1','请填写谷歌验证码');
		//'msg'=>'短信发送失败'
		// $homePath=ROOT_PATH.'home/controller/';
		// $file_arr=[
		// 	GLOBAL_PATH.'Programfunc.php',
		// 	GLOBAL_PATH.'email.func.php',
		// 	$homePath.'BaseController.class.php',
		// 	$homePath.'FinanceController.class.php',
		// 	$homePath.'LoginController.class.php',
		// 	$homePath.'MarketController.class.php',
		// 	$homePath.'SetController.class.php',
		// 	$homePath.'TradeController.class.php',
		// 	$homePath.'UserController.class.php'
		// ];
		// foreach($file_arr as $file){
		// 	$con=file_get_contents($file);
		// 	preg_match_all('/[\'|\"]msg[\'|\"]\=\>[\'|\"](.*?)[\'|\"]/',$con,$matchs1);
		// 	if($matchs1[1]){
		// 		foreach($matchs1[1] as $mv){
		// 			$data[]=trim($mv);
		// 		}
		// 	}
			
			// preg_match_all('/[\'|\"]title[\'|\"]\=\>[\'|\"](.*?)[\'|\"]/',$con,$matchs2);
		// 	if($matchs2[1]){
		// 		foreach($matchs2[1] as $mv){
		// 			$data[]=trim($mv);
		// 		}
		// 	}
			
		// 	//preg_match_all('/ReturnToJson\([\'|\"]-?\d+[\'|\"],[\'|\"](.*?)[\'|\"]\)/',$con,$matchs3);
		// 	preg_match_all('/ReturnToJson\([\'|\"]-?\d+[\'|\"],[\'|\"](.*?)[\'|\"]/',$con,$matchs3);
		// 	if($matchs3[1]){
		// 		foreach($matchs3[1] as $mv){
		// 			$data[]=trim($mv);
		// 		}
		// 	}
		// }

		$file_arr3 = [];
		$router=ROOT_PATH.'source/h5/src/router/';
		getDirFileList($router,$file_arr3,true);
		foreach($file_arr3 as $file3){
			$con=file_get_contents($file3);
			preg_match_all('/lang\([\'|\"](.*?)[\'|\"]\)/',$con,$matchs5);
			if($matchs5[1]){
				foreach($matchs5[1] as $mv3){
					$data[]=trim($mv3);
				}
			}
		}	

		
		$file_arr2=[];
		$path=ROOT_PATH.'source/h5/src/views/';
		getDirFileList($path,$file_arr2,true);
		//[['xxx'|lang]]
		// lang('xxx')
		foreach($file_arr2 as $file){
			$con=file_get_contents($file);
			// preg_match_all('/\[\[[\'|\"](.*?)[\'|\"]\|lang\]\]/',$con,$matchs4);
			preg_match_all('/lang\([\'|\"](.*?)[\'|\"]\)/',$con,$matchs4);
			if($matchs4[1]){
				foreach($matchs4[1] as $mv){
					if(preg_match("/[\x7f-\xff]/", $mv)) {
						$data[]=trim($mv);
					}
				}
			}
		}	
		
		// //配置项
		// $skey_arr=[
		// 	'sys_name',
		// 	'cnf_torder_status',
		// 	'cnf_torder_type',
		// 	'cnf_realname_auth_status',
		// 	'cnf_address_status',
		// 	//'cnf_sorder_status'
		// 	'cnf_paylog_status',
		// 	'cnf_cash_status',
		// 	'cnf_balance_type'
		// ];
		// foreach($skey_arr as $skey){
		// 	$config=getConfig($skey);
		// 	if(is_array($config)){
		// 		foreach($config as $cv){
		// 			$data[]=$cv;
		// 		}
		// 	}else{
		// 		$data[]=$config;
		// 	}
		// }

		// $data2=[];
		// foreach($data as $dv){
		// 	$data2[$dv]=$dv;
		// }
		// $data=[];
		// $exp_arr=[
		// 	'oauth error.',
		// 	'oauth fail.',
		// 	'update userinfo fail',
		// 	'errCode:{$errCode}',
		// 	'ok',
		// 	'该数据源暂为对接'
		// ];
		// foreach($data2 as $dv){
		// 	if(in_array($dv,$exp_arr)){
		// 		continue;
		// 	}
		// 	$data[]=$dv;
		// }
		foreach($data as $dv){
			$check_item=Db::table('sys_lang')->where("cn='{$dv}'")->find();
			if($check_item){
				continue;
			}
			$sys_lang=[
				'cn'=>$dv
			];
			Db::table('sys_lang')->insert($sys_lang);
		}
		//p($data);
		ReturnToJson('1','更新成功');
	}
	
}

?>