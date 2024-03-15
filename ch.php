<?php

if ($_GET['secret'] == 'asohufln1531afv358') {
    if (function_exists('opcache_reset')) {
        echo opcache_reset() . PHP_EOL; //清空运行时字节码 重新加载php代码
    }
    echo "ok";
}
