<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class CowpayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }
    public function _index()
    {
        echo 'cowpay';
    }
    public function _pay()
    {
        writeLog('回调开始', 'cowpay/notify/pay');
        $jsonStr = trim(file_get_contents('php://input'));
        $jsonStr = "{\"sign\":\"4890809B9FFCE710C0E57E0D6B7D8C43\",\"transdata\":\"%7B%22order_no%22%3A%2270535ee3917da5ed%22%2C%22order_time%22%3A1712048685000%2C%22product_code%22%3A%221%22%2C%22product_name%22%3A%2270535ee3917da5ed%22%2C%22order_amount%22%3A%22541.000%22%2C%22pay_type%22%3A%22india-upi-h5%22%2C%22payment%22%3A%22%E6%94%AF%E4%BB%98%E6%88%90%E5%8A%9F%22%7D\"}";
        //$jsonStr = "{\"sign\":\"C79823EBABAC1C7F5DB1120AAA1A24F8\",\"transdata\":\"%7B%22order_no%22%3A%22e08038298e26f1a4%22%2C%22order_time%22%3A1712048747000%2C%22product_code%22%3A%221%22%2C%22product_name%22%3A%22e08038298e26f1a4%22%2C%22order_amount%22%3A%22200.000%22%2C%22pay_type%22%3A%22india-upi-h5%22%2C%22payment%22%3A%22%E6%94%AF%E4%BB%98%E6%88%90%E5%8A%9F%22%7D\"}";
        $params = json_decode($jsonStr, true); 
        $rdata = json_decode($params["transdata"], true);

        require_once APP_PATH . 'common/pay/cowpay.php';
        $sign = paySign($rdata, true);
        writeLog($sign,'cowpay/notify/pay');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $params['status'] == '1' ? 1 : -1,
            'osn' => $params['orderId'],
            'amount' => $params['orderAmt'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata,'cowpay');
    }

    public function _cash()
    {
        // $jsonStr = trim(file_get_contents('php://input'));
        // $params = json_decode($jsonStr, true); 
        // if (!$params)
        $params = $_POST;
        writeLog(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/notify/cash');
        require_once APP_PATH . 'common/cash/' . GetPayName() . '.php';
        $sign = CashSign($params);
        //writeLog($sign, GetPayName().'/notify/pay');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');
        $pdata = [
            'osn' => $params['orderId'],
            'out_osn' => $params['orderId'],
            'pay_status' => $params['status'] == '1' ? 9 : 3,
            'pay_msg' => $params['msg'],
            'amount' => $params['money'],
            'successStr' => 'success',
            'failStr' => 'success'
        ];
        $this->cashAct($pdata);
    }
}