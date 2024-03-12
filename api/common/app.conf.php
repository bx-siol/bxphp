<?php
require_once APP_PATH.'common/app.func.php';
$module_func=APP_PATH.'common/'.strtolower(MODULE_NAME).'.func.php';
if(file_exists($module_func)){
	require_once $module_func;
}

?>