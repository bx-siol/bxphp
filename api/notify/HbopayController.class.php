<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class HbopayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }
    public function _index()
    {
        echo 'hbopay';
    }
    public function _pay()
    {
        $params = $_POST;
        writeLog('pdata2 : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'hbopay/notify/pay');
        
        require_once APP_PATH . 'common/pay/hbopay.php';
        $sign = paySign($params, true);
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => ($params['pay_state'] == '1' || $params['pay_state'] == '2') ? 1 : -1,
            'osn' => $params['mchOrderNo'],
            'amount' => $params['amount'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata,'hbopay');
    }

    public function _cash()
    {        
        $params = $_POST;
        writeLog('pdata2 : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'hbopay/notify/cash');

        require_once APP_PATH . 'common/cash/hbopay.php';
        $sign = CashSign($params);
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $params['mchOrderNo'],
            'out_osn' => $params['my_order_no'],
            'pay_status' => $params['transfer_state'] == '4' ? 9 : 3,
            'pay_msg' => $params['result_info'],
            'amount' => $params['transfer_amount'],
            'successStr' => 'success',
            'failStr' => 'fail'
        ];

        //冲正状态
        if ($params['transfer_state'] == '5')
            $pdata['pay_status'] = 4;

        $this->cashAct($pdata);
    }

    // public function _order()
    // {
	// 	$params = $this->params;
    //     $fin_cashlog = Db::table('fin_cashlog')->where("id={$params['id']}")->find();
    //     require_once APP_PATH . 'common/cash/hbopay.php';
    //     $result = CashOrder($fin_cashlog);        
	//     return $result;
    // }
}