<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class PppayController extends BaseController
{
    function GetPayName()
    {
	    return "pppay";
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'pppay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));  
        $params = json_decode($jsonStr, true);
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/pay/pppay.php';
        $sign = payCallbackSign($params);
        if ($sign != $params['retsign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $params['state'] == '4' ? 1 : -1,
            'osn' => $params['orderid'],
            'amount' => $params['amount'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata, 'pppay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('jsonStr : ' . $jsonStr, 'pppay/notify/cash');
        $params = explode("&", $jsonStr);
        if (!$params)
            $params = $_POST;

        foreach ($params as $k => $v) {
            $arr = explode("=", $v);
            $rdata[$arr[0]] = urldecode($arr[1]);
        }
        require_once APP_PATH . 'common/cash/pppay.php';
        $sign = CashSign($rdata);
        if ($sign != $rdata['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $rdata['orderno'],
            'out_osn' => $rdata['porderno'],
            'pay_status' => $rdata['status'] == '2' ? 9 : 3,
            'pay_msg' => $rdata['status'] == '2' ? 'success' : 'fail',
            'amount' => $rdata['amount'] ,
            'successStr' => 'success',
            'failStr' => 'fail'
        ];

        $this->cashAct($pdata);
    }
}