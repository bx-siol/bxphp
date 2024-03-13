<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class BobopayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'Bobopay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog(json_encode('pdata : ' . $params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'bobopay/notify/pay');
        if (!$params)
            $params = $_POST;
        require_once APP_PATH . 'common/pay/bobopay.php';
        $sign = paySign($params);
        writeLog($sign, 'bobopay/notify/pay');
        if ($sign != $params['sign'])
            jReturn(-1, 'Sign error');
        $pdata = [
            'code' => $params['status'] == '1' ? 1 : -1,
            'osn' => $params['orderId'],
            'amount' => $params['amount'],
            'successStr' => 'OK'
        ];
        $this->payAct($pdata);
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog(json_encode('pdata : ' . $params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'bobopay/notify/cash');
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/cash/bobopay.php';
        $sign = CashSign($params);
        writeLog($sign, 'bobopay/notify/pay');
        if ($sign != $params['sign'])
            jReturn(-1, 'Sign error');
        $pdata = [
            'osn' => $params['orderId'],
            'out_osn' => $params['payOrderId'],
            'pay_status' => $params['status'] == '2' ? 9 : 3,
            'pay_msg' => $params['utr'],
            'amount' => $params['amount'],
            'successStr' => 'OK',
            'failStr' => 'OK1'
        ];
        $this->cashAct($pdata);
    }
}