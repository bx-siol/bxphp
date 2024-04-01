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
        $jsonStr = "tradeResult=1&merTransferId=daa4b5199b4c9b12&merNo=100001002&tradeNo=9637878&transferAmount=313.02&sign=88f94a13b0f2952fc5a48928877ce5a0&signType=MD5&applyDate=2024-04-02+05%3A56%3A10&version=1.0&respCode=SUCCESS";
        $params = explode("&", $jsonStr);
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/cash/sunpay.php';
        $sign = CashSign($params);
        writeLog($sign, 'sunpay/notify/cash');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $params['merTransferId'],
            'out_osn' => $params['tradeNo'],
            'pay_status' => $params['tradeResult'] == '1' ? 9 : 3,
            'pay_msg' => $params['respCode'],
            'amount' => $params['transferAmount'] ,
            'successStr' => 'OK',
            'failStr' => 'OK1'
        ];

        //冲正状态
        if ($params['status'] == '4')
            $pdata['pay_status'] = 4;

        $this->cashAct($pdata);
    }

    public function _CashOrder()
    {
		$params = $this->params;
        $fin_cashlog = Db::name('fin_cashlog')->where("id={$params['id']}")->find();
        $pay_file = APP_PATH . 'common/cash/sunpay.php';
        require_once $pay_file;
        $result = CashOrder($fin_cashlog);
    }
}