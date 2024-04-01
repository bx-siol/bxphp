<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class SunpayController extends BaseController
{
    function GetPayName()
    {
	    return "sunpay";
    }

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
        writeLog('pdatajwt : ' . $jsonStr, 'sunpay/notify/pay');
        $params = explode("&", $jsonStr);
        if (!$params)
            $params = $_POST;

        foreach ($params as $k => $v) {
            $arr = explode("=", $v);
            $rdata[$arr[0]] = urldecode($arr[1]);
        }
        require_once APP_PATH . 'common/pay/sunpay.php';
        $sign = paySign($rdata);
        if ($sign != $rdata['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $rdata['tradeResult'] == '1' ? 1 : -1,
            'osn' => $rdata['mchOrderNo'],
            'amount' => $rdata['amount'],
            'successStr' => 'OK'
        ];
        $this->payAct($pdata, 'sunpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('jsonStr : ' . $jsonStr, 'sunpay/notify/cash');
        $jsonStr = "tradeResult=1&merTransferId=3e667d4e2b7954f0&merNo=100001002&tradeNo=9638172&transferAmount=197.40&sign=dc0363f618e7d17980cd47c05ff61d0c&signType=MD5&applyDate=2024-04-02+06%3A32%3A39&version=1.0&respCode=SUCCESS";
        $params = explode("&", $jsonStr);
        if (!$params)
            $params = $_POST;

        foreach ($params as $k => $v) {
            $arr = explode("=", $v);
            $rdata[$arr[0]] = urldecode($arr[1]);
        }
        require_once APP_PATH . 'common/cash/sunpay.php';
        $sign = CashSign($rdata);
        writeLog($sign, 'sunpay/notify/cash');
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