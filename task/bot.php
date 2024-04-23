<?php
if (!defined('ROOT_PATH'))
    define('ROOT_PATH', __DIR__ . '/');
define('LOGS_PATH', ROOT_PATH . 'logs/');



// 定义日志方法 自动判断文件目录是否存在，不存在则创建。并且自动检测当前文件是否存在，不存在则创建。文件路径可以自定义。如传入参数为'clisyslog/Error/'则创建日志文件为logs/clisyslog/Error/2019-08-01.log。判断文件大小，超过10M则自动分割。
function writeLog($content, $dir = "sys")
{
    $log_file = LOGS_PATH . $dir . date('Y-m-d') . '.log';
    $log_content = '[' . date('Y-m-d H:i:s') . '] - ' . $content .   PHP_EOL . PHP_EOL;
    if (!file_exists($log_file)) {
        $dir = dirname($log_file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($log_file, $log_content, FILE_APPEND);
    } else {
        $log_size = filesize($log_file);
        if ($log_size > 10 * 1024 * 1024) {
            $log_file_bak = $log_file . '_' . date('YmdHis') . '.bak';
            rename($log_file, $log_file_bak); //备份日志文件
            file_put_contents($log_file, $log_content, FILE_APPEND);
        } else {
            file_put_contents($log_file, $log_content, FILE_APPEND);
        }
    }
}

// 你的Telegram Bot的Token
$botToken = "6085322172:AAFM9QQ7Y1wKbTuQEEe9xkS7WkFNrDpqlos";

// 你想要消息转发到的群组ID
$chatId = "-4116796522";

// 获取Telegram发送过来的更新
$content = file_get_contents("php://input");
$update = json_decode($content, true);

//writeLog($content);

// 检查是否有信息和文本
if (isset($update["message"]) && isset($update["message"]["text"])) {
    $text = $update["message"]["text"];
    $userId = $update["message"]["from"]["id"];

    // 检查文本是否以"/t"开头
    if (strpos($text, "/t") === 0) {
        // 剥离"/t"，获取实际消息
        $message = substr($text, 2);
        // 使用CURL发送消息到指定的群聊
        $url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($message);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);
    }
}

echo "OK";
