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