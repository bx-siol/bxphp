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
        //$jsonStr = trim(file_get_contents('php://input'));  
        $jsonStr = '{\"amount\":\"25500.00\",\"createtime\":\"2024-07-15 10:21:25\",\"id\":\"853fbcc2-c67d-4c33-898f-b7251a304a30\",\"note\":null,\"orderid\":\"de31f09fd6a31521\",\"ordertype\":\"2\",\"paytypes\":\"UPI\",\"recvcharge\":\"1530.00\",\"recvheader\":\"https://img0523.pppay12.com/img/header/default/033.jpg\",\"recvid\":\"7e0e3cf0-beba-4916-a3b5-ed57b2735728\",\"recvnickname\":\"cicici\",\"remark\":null,\"retsign\":\"53af81b33d5c8c3b954e71a507dbd3f6\",\"sendcharge\":\"561.00\",\"sendheader\":\"https://img0523.pppay12.com/img/header/default/028.jpg\",\"sendid\":\"c1f73462-a56a-40b5-82e9-d1ee80a33011\",\"sendnickname\":\"xiaofeng1\",\"sign\":\"4fdceeee12bb1e3df4b624e20fdf3fe8\",\"state\":\"4\",\"transtime\":\"2024-07-15 10:22:48\"}';
        writeLog('pdatajwt : ' . $jsonStr, 'pppay/notify/pay');
        $params = json_decode($jsonStr, true);
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/pay/pppay.php';
        $sign = payCallbackSign($params);
        
        writeLog('sign : ' . $sign, 'pppay/notify/pay');
        if ($sign != $params['retsign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $params['state'] == '4' ? 1 : -1,
            'osn' => $params['orderid'],
            'amount' => $params['amount'],
            'successStr' => 'success'
        ];
        writeLog('pdata : ' . json_encode($pdata), 'pppay/notify/pay');
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