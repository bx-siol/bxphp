<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class CrpayController extends BaseController
{
    function GetPayName()
    {
	    return "crpay";
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'crpay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('pdatajwt : ' . $jsonStr, 'crpay/notify/pay');
        $params = explode("&", $jsonStr);
        if (!$params)
            $params = $_POST;

        foreach ($params as $k => $v) {
            $arr = explode("=", $v);
            $rdata[$arr[0]] = urldecode($arr[1]);
        }
        require_once APP_PATH . 'common/pay/crpay.php';
        $sign = paySign($rdata);
        if ($sign != $rdata['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $rdata['status'] == '1' ? 1 : -1,
            'osn' => $rdata['orderno'],
            'amount' => $rdata['amount'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata, 'crpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('jsonStr : ' . $jsonStr, 'crpay/notify/cash');
        $params = explode("&", $jsonStr);
        if (!$params)
            $params = $_POST;

        foreach ($params as $k => $v) {
            $arr = explode("=", $v);
            $rdata[$arr[0]] = urldecode($arr[1]);
        }
        require_once APP_PATH . 'common/cash/crpay.php';
        $sign = CashSign($rdata);
        if ($sign != $rdata['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $rdata['merTransferId'],
            'out_osn' => $rdata['tradeNo'],
            'pay_status' => $rdata['tradeResult'] == '1' ? 9 : 3,
            'pay_msg' => $rdata['respCode'],
            'amount' => $rdata['transferAmount'] ,
            'successStr' => 'OK',
            'failStr' => 'OK1'
        ];

        //冲正状态
        if ($rdata['tradeResult'] == '5')
            $pdata['pay_status'] = 4;

        $this->cashAct($pdata);
    }
}