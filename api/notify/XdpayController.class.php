<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class XdpayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }
    public function _index()
    {
        echo 'xdpay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('pdatajwt : ' . $jsonStr, 'xdpay/notify/pay');
        
        require_once APP_PATH . 'common/pay/xdpay.php';
        $sign = paySign($rdata, true);
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $rdata['payment'] == '支付成功' ? 1 : -1,
            'osn' => $rdata['order_no'],
            'amount' => $rdata['order_amount'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata,'xdpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('pdatajwt : ' . $jsonStr, 'xdpay/notify/cash');
        $params = json_decode($jsonStr, true);
        $rdata = json_decode(urldecode($params["transdata"]), true);

        require_once APP_PATH . 'common/cash/xdpay.php';
        $sign = CashSign($rdata);
        writeLog('sign : ' . $sign, 'xdpay/notify/cash');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $rdata['order_no'],
            'out_osn' => $rdata['order_no'],
            'pay_status' => $rdata['resp_code'] == 'S' ? 9 : 3,
            'pay_msg' => $rdata['message'],
            'amount' => $rdata['order_amount'],
            'successStr' => 'success',
            'failStr' => 'success'
        ];
        $this->cashAct($pdata);
    }
}