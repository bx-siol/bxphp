<?php

 
echo "<h2>Request Information</h2>";

// 请求方法
echo "<b>Request Method:</b> " . $_SERVER['REQUEST_METHOD'] . "<br>";

// 请求URI
echo "<b>Request URI:</b> " . $_SERVER['REQUEST_URI'] . "<br>";

// 查询字符串
echo "<b>Query String:</b> " . $_SERVER['QUERY_STRING'] . "<br>";

// 完整的URL
echo "<b>Full URL:</b> ";
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $protocol = "https://"; else $protocol = "http://";
echo $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br>";

// POST数据
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<b>POST Data:</b><pre>";
    print_r($_POST);
    echo "</pre>";
}

// 文件上传
if (!empty($_FILES)) {
    echo "<b>Uploaded Files:</b><pre>";
    print_r($_FILES);
    echo "</pre>";
}

// 请求头
if (function_exists('getallheaders')) {
    echo "<b>Request Headers:</b><pre>";
    print_r(getallheaders());
    echo "</pre>";
} else {
    echo "<b>Request Headers: </b>Function 'getallheaders()' not available.<br>";
}

// 浏览器信息
echo "<b>User Agent:</b> " . $_SERVER['HTTP_USER_AGENT'] . "<br>";

 
echo "<h2>Server and Execution Environment Information</h2>";
echo "<pre>";
foreach ($_SERVER as $key => $value) {
    echo "$key => $value\n";
}
echo "</pre>";
 
 
/*

ALTER TABLE `fin_cashlog` CHANGE `check_ip` `check_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL, CHANGE `client_ip` `client_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
ALTER TABLE `fin_paylog` CHANGE `check_ip` `check_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
ALTER TABLE `gift_lottery_log` CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
ALTER TABLE `gift_prize_log`    CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
ALTER TABLE `gift_redpack_detail` CHANGE `receive_ip` `receive_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
ALTER TABLE `pro_order` CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
ALTER TABLE `sys_log` CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
ALTER TABLE `sys_mcode` CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
ALTER TABLE `sys_user` CHANGE `reg_ip` `reg_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
ALTER TABLE `sys_user` CHANGE `login_ip` `login_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
ALTER TABLE `sys_vcode` CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;




*/