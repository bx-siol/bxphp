<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class AllpayController extends BaseController
{
    function GetPayName()
    {
	    return "allpay";
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'allpay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('pdatajwt : ' . $jsonStr, 'allpay/notify/pay');
        $params = explode("&", $jsonStr);
        if (!$params)
            $params = $_POST;

        foreach ($params as $k => $v) {
            $arr = explode("=", $v);
            $rdata[$arr[0]] = urldecode($arr[1]);
        }
        require_once APP_PATH . 'common/pay/allpay.php';
        $sign = paySign($rdata);
        if ($sign != $rdata['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $rdata['tradeResult'] == '1' ? 1 : -1,
            'osn' => $rdata['mchOrderNo'],
            'amount' => $rdata['amount'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata, 'allpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('jsonStr : ' . $jsonStr, 'allpay/notify/cash');
        $params = explode("&", $jsonStr);
        if (!$params)
            $params = $_POST;

        foreach ($params as $k => $v) {
            $arr = explode("=", $v);
            $rdata[$arr[0]] = urldecode($arr[1]);
        }
        require_once APP_PATH . 'common/cash/allpay.php';
        $sign = CashSign($rdata);
        if ($sign != $rdata['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $rdata['merTransferId'],
            'out_osn' => $rdata['tradeNo'],
            'pay_status' => $rdata['tradeResult'] == '1' ? 9 : 3,
            'pay_msg' => $rdata['respCode'],
            'amount' => $rdata['transferAmount'] ,
            'successStr' => 'success',
            'failStr' => 'fail'
        ];

        $this->cashAct($pdata);
    }

    // public function _order()
    // {
	// 	$params = $this->params;
    //     $fin_cashlog = Db::table('fin_cashlog')->where("id={$params['id']}")->find();
    //     require_once APP_PATH . 'common/cash/allpay.php';
    //     $result = CashOrder($fin_cashlog);        
	//     return $result;
    // }
}