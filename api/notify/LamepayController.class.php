<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class LamepayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'lamepay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog('pdatajwt : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'lamepay/notify/pay');
        if (!$params)
            $params = $_POST;
        require_once APP_PATH . 'common/pay/lamepay.php';
        $sign = paySign($params);
        //writeLog($sign, 'lamepay/notify/pay');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');
        $pdata = [
            'code' => $params['payStatus'] == 'SUCCESS' ? 1 : -1,
            'osn' => $params['outTxnNo'],
            'amount' => $params['txnFinalAmount'],
            'successStr' => 'success'
        ];
        //writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'lamepay/notify/pay');
        $this->payAct($pdata, 'lamepay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'lamepay/notify/cash');
        if (!$params)
            $params = $_POST;
        require_once APP_PATH . 'common/cash/lamepay.php';
        $sign = CashSign($params);
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $params['outTxnNo'],
            'out_osn' => $params['txnNo'],
            'pay_status' => $params['payStatus'] == 'SUCCESS' ? 9 : 3,
            'pay_msg' => $params['utrNo'],
            'amount' => $params['txnAmount'],
            'successStr' => 'success',
            'failStr' => 'success'
        ];

        //冲正状态 交易回退
        if ($params['reversed'] == 'TRUE')
            $pdata['pay_status'] = 4;

        $this->cashAct($pdata);
    }
}
