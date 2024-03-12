<?php
require_once(dirname(__FILE__) . '/daemon.ini.php');

use think\facade\Db;
 
Db::name('sys_user')->update(['lottery' => 0]);

output('更新验证码成功');
