<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class XdpayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }
    public function _index()
    {
        echo 'xdpay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        writeLog('pdatajwt : ' . $jsonStr, 'xdpay/notify/pay');
        $params = json_decode($jsonStr, true); 

        require_once APP_PATH . 'common/pay/xdpay.php';
        $sign = paySign($params, true);
        //writeLog('sign : ' . $sign, 'xdpay/notify/pay');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $params['status'] == '1' ? 1 : -1,
            'osn' => $params['orderId'],
            'amount' => $params['amount'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata,'xdpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $jsonStr="{\"platOrderId\":\"20240406192054985826\",\"orderId\":\"09ea7c1a8309ddc1\",\"amount\":200.22,\"status\":1,\"reverse\":false,\"remark\":\"IDFB0042562\",\"sign\":\"56a4b90ff545dd770c08826ffb07d599\"}";
        writeLog('pdatajwt : ' . $jsonStr, 'xdpay/notify/cash');
        $params = json_decode($jsonStr, true);

        require_once APP_PATH . 'common/cash/xdpay.php';
        $sign = CashSign($params);
        writeLog('sign : ' . $sign, 'xdpay/notify/cash');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $params['orderId'],
            'out_osn' => $params['platOrderId'],
            'pay_status' => $params['status'] == 1 ? 9 : 3,
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
        require_once APP_PATH . 'common/cash/xdpay.php';
        $result = CashOrder($fin_cashlog);
        
	    return $result;
    }
}