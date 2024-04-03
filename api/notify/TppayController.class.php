<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class tppayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'tppay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $jsonStr = "{\"merchantId\":\"129\",\"orderId\":\"c36de1d6896c236c\",\"amount\":60000,\"timestamp\":1712141324357,\"notifyUrl\":\"https://admin.nestleinr.com/api/Notify/jwpay/pay\",\"sign\":\"e85960e05b52859ef6d59e23214930a4\"}";
        $params = json_decode($jsonStr, true);
        writeLog('pdatajwt : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'tppay/notify/pay');
        if (!$params)
            $params = $_POST;
        require_once APP_PATH . 'common/pay/tppay.php';
        $sign = paySign($params);
        writeLog($sign, 'tppay/notify/pay');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');
        $pdata = [
            'code' => $params['status'] == '1' ? 1 : -1,
            'osn' => $params['orderId'],
            'amount' => $params['amount'] / 100,
            'successStr' => 'OK'
        ];
        //writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'tppay/notify/pay');
        $this->payAct($pdata, 'tppay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog('pdata : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'tppay/notify/cash');
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/cash/tppay.php';
        $sign = CashSign($params);
        //writeLog($sign, 'tppay/notify/pay');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

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
