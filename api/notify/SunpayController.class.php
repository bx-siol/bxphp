<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class SunpayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'sunpay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog('pdatajwt : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'sunpay/notify/pay');
        if (!$params)
            $params = $_POST;
        require_once APP_PATH . 'common/pay/sunpay.php';
        $sign = paySign($params);
        writeLog($sign, 'sunpay/notify/pay');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');
        $pdata = [
            'code' => $params['status'] == '1' ? 1 : -1,
            'osn' => $params['mchOrderNo'],
            'amount' => $params['amount'] / 100,
            'successStr' => 'OK'
        ];
        writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'sunpay/notify/pay');
        $this->payAct($pdata, 'sunpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog('pdata : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'sunpay/notify/cash');
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/cash/sunpay.php';
        $sign = CashSign($params);
        writeLog($sign, 'sunpay/notify/pay');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $params['merTransferId'],
            'out_osn' => $params['tradeNo'],
            'pay_status' => $params['tradeResult'] == '1' ? 9 : 3,
            'pay_msg' => $params['respCode'],
            'amount' => $params['transferAmount'] / 100,
            'successStr' => 'OK',
            'failStr' => 'OK1'
        ];

        //冲正状态
        if ($params['status'] == '4')
            $pdata['pay_status'] = 4;

        $this->cashAct($pdata);
    }
}