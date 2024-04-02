<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class CowpayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }
    public function _index()
    {
        echo 'cowpay';
    }
    public function _pay()
    {
        writeLog('回调开始', 'cowpay/notify/pay');
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('jsonStr : ' . $jsonStr,'cowpay/notify/pay');
        $params = $_POST;
        writeLog("params" .json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'cowpay/notify/pay');

        require_once APP_PATH . 'common/pay/cowpay.php';
        $sign = paySign($params, true);
        writeLog($sign,'cowpay/notify/pay');
        if (!$sign)
            ReturnToJson(-1, 'Sign error');
        $pdata = [
            'code' => $params['status'] == '1' ? 1 : -1,
            'osn' => $params['orderId'],
            'amount' => $params['orderAmt'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata,'cowpay');
    }

    public function _cash()
    {
        // $jsonStr = trim(file_get_contents('php://input'));
        // $params = json_decode($jsonStr, true); 
        // if (!$params)
        $params = $_POST;
        writeLog(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/notify/cash');
        require_once APP_PATH . 'common/cash/' . GetPayName() . '.php';
        $sign = CashSign($params);
        //writeLog($sign, GetPayName().'/notify/pay');
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