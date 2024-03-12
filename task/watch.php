<?php
require_once 'daemon.ini.php';
$debug=false;
$params=$argv;
$php='php';
$queue_arr=[
	//'queue_scode.php',
	'queue_notice.php',
	'queue_dpay.php',
	//'queue_profit.php'
];

$disable_functions=ini_get('disable_functions');
if($disable_functions){
	$dis_funs=explode(',',$disable_functions);
	if(in_array('exec',$dis_funs)){
		exit("请修改php.ini配置文件里面的disable_functions项，允许exec函数执行！\n");
	}
}

if($params[1]=='start'){
	start();
}elseif($params[1]=='stop'){
	stop();
}elseif($params[1]=='reload'){
	reload();
}else{
	exit("允许命令： php ./watch.php start|stop|reload\n");
}

//开始
function start(){
	global $queue_arr;
	echo "----------------start-------------------\n";
	foreach($queue_arr as $qv){
		$pid=getRunningPid($qv);
		if(!$pid){
			$res=execPhp($qv);
			if($res!==true){
				echo $res."\n";
			}else{
				$pid=getRunningPid($qv);
				echo "{$qv} 进程本次运行，pid：{$pid}\n";
			}
		}else{
			echo "{$qv} 已运行，pid：{$pid}\n";
		}
	}
	echo "----------------start-------------------\n";
}

//停止
function stop(){
	global $queue_arr;
	echo "----------------stop-------------------\n";
	foreach($queue_arr as $qv){
		$pid=getRunningPid($qv);
		if(!$pid){
			echo "{$qv} 进程已停止\n";
		}else{
			killPhp($qv);
			$pid=getRunningPid($qv);
			if($pid){
				echo "{$qv} 进程关闭失败\n";
			}else{
				echo "{$qv} 进程已停止\n";
			}
		}
	}
	echo "----------------stop-------------------\n";
}

//重载
function reload(){
	echo "#################reload#################\n";
	stop();
	start();
	echo "#################reload#################\n";
}

//获取正在执行文件的程序id
function getRunningPid($fileName){
	if(!$fileName){
		return false;
	}
	exec("ps -ef|grep '{$fileName}'|grep -v 'grep'",$result);
	if(!$result[0]){
		return false;
	}
	$result_str=preg_replace("/\s(?=\s)/","\\1",$result[0]);
	if(!$result_str){
		return false;
	}
	$resultArr=explode(' ',$result_str);
	$pid=intval($resultArr[1]);
	return $pid;
}

//执行php
function execPhp($fileName){
	global $php,$debug;
	$file=ROOT_PATH.'daemon/'.$fileName;
	if(!file_exists($file)){
		return "不存在文件：{$file}";
	}
	$log_file='/dev/null';
	if($debug){
		$log_file=ROOT_PATH.'logs/daemon.txt';
	}
	exec("{$php} {$file} >>{$log_file} &",$result,$resultInt);
	file_put_contents(ROOT_PATH.'logs/daemon.txt',date('Y-m-d H:i:s')."-开启进程：{$fileName}\n\n",FILE_APPEND);
	return true;
}

//结束运行
function killPhp($fileName){
	global $debug;
	$pid=getRunningPid($fileName);
	$log_file='/dev/null';
	if($debug){
		$log_file=ROOT_PATH.'logs/daemon.txt';
	}
	exec("kill {$pid} >>{$log_file}");
	file_put_contents(ROOT_PATH.'logs/daemon.txt',date('Y-m-d H:i:s')."-结束进程：{$fileName}\n\n",FILE_APPEND);
}

?>