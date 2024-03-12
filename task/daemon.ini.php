<?php
define('APP_DEBUG',true);
require_once(dirname(__FILE__).'/../global/global.ini.php');
if(!PHP_CLI){
	exit('run in cli');
}
?>