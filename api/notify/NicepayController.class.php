<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class NicepayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'Nicepay1';
    }
    public function _pay()
    {
        // $jsonStr = trim(file_get_contents('php://input'));
        // $params = json_decode($jsonStr, true);
        // if (!$params)
        $params = $_POST;
        writeLog('pdata : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'nicepay/notify/pay');
        require_once APP_PATH . 'common/pay/nicepay.php';
        $sign = paySign($params, true);
        writeLog($sign, 'nicepay/notify/pay');
        if (!$sign)
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $params['status'] == '1' ? 1 : -1,
            'osn' => $params['orderId'],
            'amount' => $params['orderAmt'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata, 'nicepay');
    }

    public function _cash()
    {
        // $jsonStr = trim(file_get_contents('php://input'));
        // $params = json_decode($jsonStr, true); 
        // if (!$params)
        $params = $_POST;
        writeLog('pdata : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'nicepay/notify/cash');
        require_once APP_PATH . 'common/cash/nicepay.php';
        $sign = CashSign($params);
        //writeLog($sign, 'nicepay/notify/pay');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');
        $pdata = [
            'osn' => $params['orderId'],
            'out_osn' => $params['orderId'],
            'pay_status' => $params['status'] == '1' ? 9 : 3,
            'pay_msg' => $params['msg'],
            'amount' => $params['money'],
            'successStr' => 'success',
            'failStr' => 'success'
        ];
        $this->cashAct($pdata);
    }
}


// merId:202403664
// orderId:5d81d13816f2993b
// sysOrderId:171127140136278
// desc:desc
// orderAmt:600.00
// status:1
// nonceStr:0uY2c6R1IToOsePb4FlDLSC7z9Ayqj8W
// sign:TlxZuNLeRcSo6ZqIZNJHi15NHqBW/Pxe7jy2kFpJi6hUEW11A1PvyuI+uZp7rOBACelo0EAldp/6S/DXLFBM0wlJRiiTXCZsVtGknjWTilMFbvljF1FfySRzPAKNYJohyB1wxGoo+zizmZcfUel9wE+ZrRPjq1sv0lQhDwoljTFh9dFKa0TOjuPF+xa1f1veg8fK2hpGghOHG93l5RYMVJZZsC3OjHXO9pBsbSp6KkJ42qyijlVHAKgYEaFUumSy8FAMePNYWuafjbI6lcmGUjl/CjtkQTdqqFJuab+QJw/FLQqOzA1saxTGDJeVrG6S2kLdQK8ScmN0/xEPe6r2zQ==