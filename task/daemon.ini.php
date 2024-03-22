<?php
define('APP_DEBUG',true);
require_once(dirname(__FILE__).'/../global/Program.php');
if(!PHP_CLI){
	exit('run in cli');
}
?>