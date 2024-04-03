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
        require_once APP_PATH . 'common/cash/cowpay.php';
        $sign = CashSign($params);
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

    public function _order()
    {
        $params = $this->params;
        $fin_cashlog = Db::table('fin_cashlog')->where("id={$params['id']}")->find();
        $pay_file = APP_PATH . 'common/cash/cowpay.php';
        require_once $pay_file;
		$result = CashOrder($fin_cashlog);
    }
}