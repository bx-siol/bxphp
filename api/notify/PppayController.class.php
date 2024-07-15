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
        //$jsonStr = trim(file_get_contents('php://input'));
        $jsonStr = "{\"amount\":\"311.00\",\"createtime\":\"2024-07-15 11:54:18\",\"id\":\"6c2d3ae5c88ed692038cd7f81d33e72c\",\"note\":null,\"orderid\":\"6ac0cf2dadd9e0f4\",\"ordertype\":\"3\",\"paytypes\":\"银行\",\"recvcharge\":\"3.73\",\"recvheader\":\"https://img0523.pppay12.com/img/header/default/028.jpg\",\"recvid\":\"c1f73462-a56a-40b5-82e9-d1ee80a33011\",\"recvnickname\":\"xiaofeng1\",\"remark\":null,\"retsign\":\"da8d63ca51ca57fce3d9b6e6096c307f\",\"sendcharge\":\"9.33\",\"sendheader\":\"https://img0523.pppay12.com/img/header/default/033.jpg\",\"sendid\":\"7e0e3cf0-beba-4916-a3b5-ed57b2735728\",\"sendnickname\":\"cicici\",\"sign\":\"58d2b9ff75adc2dee41a8075d938b3f3\",\"state\":\"4\",\"transtime\":\"2024-07-15 11:58:47\"}";
        writeLog('jsonStr : ' . $jsonStr, 'pppay/notify/cash');
        $params = json_decode($jsonStr, true);
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/cash/pppay.php';
        $sign = CashCallbackSign($params);
        if ($sign != $params['retsign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $params['orderid'],
            'out_osn' => $params['id'],
            'pay_status' => $params['state'] == '4' ? 9 : 3,
            'pay_msg' => $params['state'] == '4' ? 'success' : 'fail',
            'amount' => $params['amount'] ,
            'successStr' => 'success',
            'failStr' => 'fail'
        ];

        $this->cashAct($pdata);
    }

    
    public function _order()
    {
		$params = $this->params;
        $fin_cashlog = Db::table('fin_cashlog')->where("id={$params['id']}")->find();
        require_once APP_PATH . 'common/cash/pppay.php';
        $result = CashOrder($fin_cashlog);
        
	    return $result;
    }
}