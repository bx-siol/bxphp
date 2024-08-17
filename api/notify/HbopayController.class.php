<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class HbopayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }
    public function _index()
    {
        echo 'hbopay';
    }
    public function _pay()
    {
        
        $reqData = $this->request->post(false); // post参数
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('pdatajwt : ' . $jsonStr, 'hbopay/notify/pay');
        writeLog('reqData : ' . $reqData, 'hbopay/notify/pay');
        $params = json_decode($jsonStr, true); 
        
        require_once APP_PATH . 'common/pay/hbopay.php';
        $sign = paySign($params, true);
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $params['pay_state'] == '1' ? 1 : -1,
            'osn' => $params['mchOrderNo'],
            'amount' => $params['amount'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata,'hbopay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('pdatajwt : ' . $jsonStr, 'hbopay/notify/cash');
        $params = json_decode($jsonStr, true);
        $rdata = json_decode(urldecode($params["transdata"]), true);

        require_once APP_PATH . 'common/cash/hbopay.php';
        $sign = CashSign($rdata);
        writeLog('sign : ' . $sign, 'hbopay/notify/cash');
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