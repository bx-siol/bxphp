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
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('pdatajwt : ' . $jsonStr, 'cowpay/notify/pay');
        $params = json_decode($jsonStr, true); 
        $rdata = json_decode(urldecode($params["transdata"]), true);
        
        require_once APP_PATH . 'common/pay/cowpay.php';
        $sign = paySign($rdata, true);
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $rdata['payment'] == '支付成功' ? 1 : -1,
            'osn' => $rdata['order_no'],
            'amount' => $rdata['order_amount'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata,'cowpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('pdatajwt : ' . $jsonStr, 'cowpay/notify/cash');
        $params = json_decode($jsonStr, true);
        $rdata = json_decode(urldecode($params["transdata"]), true);

        require_once APP_PATH . 'common/cash/cowpay.php';
        $sign = CashSign($rdata);
        writeLog('sign : ' . $sign, 'cowpay/notify/cash');
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