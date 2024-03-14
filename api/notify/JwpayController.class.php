<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class JwpayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'jwpay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog('pdatajwt : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay/notify/pay');
        if (!$params)
            $params = $_POST;
        require_once APP_PATH . 'common/pay/jwpay.php';
        $sign = paySign($params);
        writeLog($sign, 'jwpay/notify/pay');
        if ($sign != $params['sign'])
            jReturn(-1, 'Sign error');
        $pdata = [
            'code' => $params['status'] == '1' ? 1 : -1,
            'osn' => $params['orderId'],
            'amount' => $params['amount'] / 100,
            'successStr' => 'OK'
        ];
        writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay/notify/pay');
        $this->payAct($pdata, 'jwpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog('pdata : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'jwpay/notify/cash');
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/cash/jwpay.php';
        $sign = CashSign($params);
        writeLog($sign, 'jwpay/notify/pay');
        if ($sign != $params['sign'])
            jReturn(-1, 'Sign error');

        $pdata = [
            'osn' => $params['orderId'],
            'out_osn' => $params['payOrderId'],
            'pay_status' => $params['status'] == '1' ? 9 : 3,
            'pay_msg' => $params['statusDesc'],
            'amount' => $params['amount'] / 100,
            'successStr' => 'OK',
            'failStr' => 'OK1'
        ];

        //冲正状态
        if ($params['status'] == '4')
            $pdata['pay_status'] = 4;

        $this->cashAct($pdata);
    }
}