<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class KirinpayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }
    public function _index()
    {
        echo 'kirinpay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('jsonStr : ' . $jsonStr, 'kirinpay/notify/pay');
        $params = explode("&", $jsonStr);
        if (!$params)
            $params = $_POST;

        foreach ($params as $k => $v) {
            $arr = explode("=", $v);
            $rdata[$arr[0]] = urldecode($arr[1]);
        }

        require_once APP_PATH . 'common/pay/kirinpay.php';
        $sign = paySign($rdata, true);
        //writeLog('sign : ' . $sign, 'kirinpay/notify/pay');
        if ($sign != $rdata['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $rdata['callbacks'] == 'CODE_SUCCESS' ? 1 : -1,
            'osn' => $rdata['out_trade_no'],
            'amount' => $rdata['amount'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata,'kirinpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('pdatajwt : ' . $jsonStr, 'kirinpay/notify/cash');
        $params = json_decode($jsonStr, true);
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/cash/kirinpay.php';
        $sign = CashSign($params);
        //writeLog('sign : ' . $sign, 'kirinpay/notify/cash');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $params['out_trade_no'],
            'out_osn' => $params['order_no'],
            'pay_status' => $params['code'] == "CODE_FINISHED" ? 9 : 3,
            'pay_msg' => 'success',
            'amount' => $params['amount'],
            'successStr' => 'success',
            'failStr' => 'success'
        ];
        $this->cashAct($pdata);
    }

    
    public function _order()
    {
		$params = $this->params;
        $fin_cashlog = Db::table('fin_cashlog')->where("id={$params['id']}")->find();
        require_once APP_PATH . 'common/cash/kirinpay.php';
        $result = CashOrder($fin_cashlog);
        
	    return $result;
    }
}