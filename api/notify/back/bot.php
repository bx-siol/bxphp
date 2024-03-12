<?php
//https://api.telegram.org/bot5517417102:AAHoiLelXosWXkYTHalRcvXTKQWt1ydRnDQ/getUpdates


function curl_post($url, $data)
{

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
function send_photo($chatId, $text, $imgurl)
{
    $token = '5517417102:AAHoiLelXosWXkYTHalRcvXTKQWt1ydRnDQ';
    $url = '';
    $data = [];
    if (strstr($imgurl, 'jpg') == false) {
        $url = 'https://api.telegram.org/bot' . $token . '/sendAnimation';
        $data = [
            'chat_id' => $chatId,
            'animation' => $imgurl,
            'caption' => $text,
        ];
    } else {
        $url = 'https://api.telegram.org/bot' . $token . '/sendPhoto';
        $data = [
            'chat_id' => $chatId,
            'caption' => $text,
            'photo' => $imgurl
        ];
    }
    return curl_post($url, $data);
}
function sendMessage($chatId, $text)
{
    $token = '5517417102:AAHoiLelXosWXkYTHalRcvXTKQWt1ydRnDQ';
    $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
    ];
    return curl_post($url, $data);
}
$stx = '';
if ($_POST['key'] == "AAHoiLelXosWXkYTHalRcvXTKQWt1ydRnDQ") {
    if ($_POST['type'] == '0') {
        $stx = sendMessage($_POST['chatId'], $_POST['text']);
    } else if ($_POST['type'] == '1') {
        $stx = send_photo($_POST['chatId'], $_POST['text'], $_POST['imgurl']);
    }
}
echo $stx;
