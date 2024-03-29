<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class GreenpayController extends BaseController
{



    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'greenpay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'greenpay/notify/pay');
        if (!$params)
            $params = $_POST;
        require_once APP_PATH . 'common/pay/greenpay.php';
        $sign = dsign($params);
        writeLog($sign, 'greenpay/notify/pay');
        if ($sign != $params['signature_n'])
            ReturnToJson(-1, 'Sign error');
        $pdata = [
            'code' => $params['data']['status'] == 'SUCCESS' ? 1 : -1,
            'osn' => $params['data']['merchantOrderNo'],
            'amount' => $params['data']['amount'],
            'successStr' => '{"code":200}'
        ];
        writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'greenpay/notify/pay');
        $this->payAct($pdata, GetPayName());
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog('pdata : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'greenpay/notify/cash');
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/cash/greenpay.php';
        $sign = CashSign($params);
        //writeLog($sign, GetPayName().'/notify/pay');
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
