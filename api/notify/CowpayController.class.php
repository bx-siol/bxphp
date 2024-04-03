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
        $jsonStr="{\"sign\":\"C15E862E12F182C783A59F4DE2434040\",\"transdata\":\"%7B%22order_no%22%3A%22b7b6ea5b987bbbcf%22%2C%22message%22%3A%22%E6%8F%90%E7%8E%B0%E6%88%90%E5%8A%9F%22%2C%22order_amount%22%3A%22385.000%22%2C%22resp_code%22%3A%22S%22%7D\"}";
        //$jsonStr="{\"sign\":\"434F3DDC6AF071B83AC4DB92483CCF1D\",\"transdata\":\"%7B%22order_no%22%3A%22a4706629902b5d66%22%2C%22message%22%3A%22%E6%8F%90%E7%8E%B0%E6%88%90%E5%8A%9F%22%2C%22order_amount%22%3A%22208.000%22%2C%22resp_code%22%3A%22S%22%7D\"}";
        $params = json_decode($jsonStr, true);
        writeLog('urldecode : ' . urldecode($params["transdata"]), 'cowpay/notify/cash');
        $rdata = json_decode(urldecode($params["transdata"]), true);
        require_once APP_PATH . 'common/cash/cowpay.php';
        $sign = CashSign($params);
        writeLog('sign : ' . $sign, 'cowpay/notify/cash');
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

    // public function _order()
    // {
    //     $params = $this->params;
    //     $fin_cashlog = Db::table('fin_cashlog')->where("id={$params['id']}")->find();
    //     $pay_file = APP_PATH . 'common/cash/cowpay.php';
    //     require_once $pay_file;
	// 	$result = CashOrder($fin_cashlog);
    // }
}