<?php


// echo "<h2>Request Information</h2>"; 
// echo "<b>Request Method:</b> " . $_SERVER['REQUEST_METHOD'] . "<br>"; 
// echo "<b>Request URI:</b> " . $_SERVER['REQUEST_URI'] . "<br>";  
// echo "<b>Query String:</b> " . $_SERVER['QUERY_STRING'] . "<br>";  
// echo "<b>Full URL:</b> ";
// if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $protocol = "https://"; else $protocol = "http://";
// echo $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br>"; 
// // POST数据
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     echo "<b>POST Data:</b><pre>";
//     print_r($_POST);
//     echo "</pre>";
// } 
// // 文件上传
// if (!empty($_FILES)) {
//     echo "<b>Uploaded Files:</b><pre>";
//     print_r($_FILES);
//     echo "</pre>";
// } 
// // 请求头
// if (function_exists('getallheaders')) {
//     echo "<b>Request Headers:</b><pre>";
//     print_r(getallheaders());
//     echo "</pre>";
// } else {
//     echo "<b>Request Headers: </b>Function 'getallheaders()' not available.<br>";
// } 
// // 浏览器信息
// echo "<b>User Agent:</b> " . $_SERVER['HTTP_USER_AGENT'] . "<br>"; 
// echo "<h2>Server and Execution Environment Information</h2>";
// echo "<pre>";
// foreach ($_SERVER as $key => $value) {
//     echo "$key => $value\n";
// }
// echo "</pre>";  
// ALTER TABLE `fin_cashlog` CHANGE `check_ip` `check_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL, CHANGE `client_ip` `client_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
// ALTER TABLE `fin_paylog` CHANGE `check_ip` `check_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
// ALTER TABLE `gift_lottery_log` CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
// ALTER TABLE `gift_prize_log`    CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
// ALTER TABLE `gift_redpack_detail` CHANGE `receive_ip` `receive_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
// ALTER TABLE `pro_order` CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
// ALTER TABLE `sys_log` CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
// ALTER TABLE `sys_mcode` CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
// ALTER TABLE `sys_user` CHANGE `reg_ip` `reg_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
// ALTER TABLE `sys_user` CHANGE `login_ip` `login_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;
// ALTER TABLE `sys_vcode` CHANGE `create_ip` `create_ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL;



$botToken = '6765233252:AAHGBp9KbsrmJr9_-W1Bm_MqdQCsxxLkSEA'; // 替换为你的 Telegram Bot Token
// $apiURL = "https://api.telegram.org/bot{$botToken}/getUpdates";

// // 使用 file_get_contents 发起 API 请求（仅当 allow_url_fopen 在 php.ini 中被启用时可用）
// // $response = file_get_contents($apiURL);

// // 使用 cURL 发起 API 请求
// $ch = curl_init($apiURL);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// $response = curl_exec($ch);
// curl_close($ch);

// // 检查响应是否成功
// if ($response === false) {
//     die ('Failed to fetch updates from Telegram Bot API');
// }

// // 解码 JSON 响应
// $data = json_decode($response, true);

// if ($data['ok']) {
//     // 遍历返回的每个 update
//     foreach ($data['result'] as $update) {
//         // 检查 message 并确认是否包含 document 或 animation
//         if (isset ($update['message']['document']) || isset ($update['message']['animation'])) {
//             // 首先检查是否有 animation 类型的 GIF
//             if (isset ($update['message']['animation'])) {
//                 $fileId = $update['message']['animation']['file_id'];
//                 echo "找到 GIF 的 file_id: " . $fileId . PHP_EOL;
//             }
//             // 否则检查是否为普通文档类型的文件但 MIME 类型为 video/mp4
//             elseif (isset ($update['message']['document']) && $update['message']['document']['mime_type'] === 'video/mp4') {
//                 $fileId = $update['message']['document']['file_id'];
//                 echo "找到视频文件的 file_id (可能是 GIF): " . $fileId . PHP_EOL;
//             }
//         }
//     }
// } else {
//     die ('API 返回未成功状态');
// }

$files = [
    'CgACAgUAAxkBAAMqZf9I_Kuq1Z4ottwKwvHEKmfyyVUAAk4EAAKhOYlXW-I3oxk8wxo0BA',
    'CgACAgUAAxkBAAMrZf9I_zkOPxaV32HvJIVjFi4deXYAAoYFAAJeQMBVf1Qrt4QsJwQ0BA',
    'CgACAgUAAxkBAAMsZf9JAVmVbQ1WTs9r5KRoblWngNQAAmwIAALCFnBWjsHFlvWGeaE0BA',
    'CgACAgQAAxkBAAMtZf9JAoVQmOD68IqYrJgOSaDwZZIAAk4CAAK-D4xSN6ydi3SXtfE0BA',
    'CgACAgUAAxkBAAMuZf9JBPdCZhWqlEIoItCrVgSL8BsAApEKAAIPlyBXFCwoIfKxw9g0BA'
];
 
function sendAnimation($chatId, $fileId, $botToken) {
    $url = "https://api.telegram.org/bot{$botToken}/sendAnimation";
    $str = "订单号：1\n二级代理：2\n推荐人：3\n订单金额：4\n5";
    $postData = [
        'chat_id' => $chatId,
		'animation' => $fileId,
		'caption' => $str,
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
    $output = curl_exec($ch);
    
    curl_close($ch);
    
    return $output;
}
 
$chatId = '-4165632848';  // 替换成你想要发送文件的目标聊天ID

// 从 JSON 数据中获取 file_id
$fileId = $files[array_rand($files)] ;

// 发送动画/视频文件
$response = sendAnimation($chatId, $fileId, $botToken);

echo $response;  // 打印响应数据，查看是否成功或返回错误信息
 
