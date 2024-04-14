<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class UsdtpayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }
    public function _index()
    {
        echo 'usdtpay';
    }

    // {
    //     "trade_id": "202203251648208648961728",
    //     "order_id": "2022123321312321321",
    //     "amount": 100,
    //     "actual_amount": 15.625,
    //     "token": "TNEns8t9jbWENbStkQdVQtHMGpbsYsQjZK",
    //     "block_transaction_id": "123333333321232132131",
    //     "signature": "xsadaxsaxsa",
    //     "status": 2
    //   }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        if (!$params)
            $params = $_POST;
        writeLog(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'usdtpay/notify/pay');

        require_once APP_PATH . 'common/pay/usdtpay.php';
        $sign = paySign($params);
        if ($sign != $params['signature'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $params['status'] == 2 ? 1 : -1,
            'osn' => $params['order_id'],
            'amount' => $params['amount'],
            'successStr' => 'ok'
        ];
        $this->payAct($pdata, 'usdtpay');
    }
    // public function _cash()
    // {
    //     $jsonStr = trim(file_get_contents('php://input'));
    //     writeLog($jsonStr, 'usdtpay/notify/cash');
    //     $params = json_decode($jsonStr, true);
    //     $rdata = json_decode(urldecode($params["transdata"]), true);

    //     require_once APP_PATH . 'common/cash/usdtpay.php';
    //     $sign = CashSign($rdata);
    //     writeLog($sign, 'usdtpay/notify/cash');
    //     if ($sign != $params['sign'])
    //         ReturnToJson(-1, 'Sign error');

    //     $pdata = [
    //         'osn' => $rdata['order_no'],
    //         'out_osn' => $rdata['order_no'],
    //         'pay_status' => $rdata['resp_code'] == 'S' ? 9 : 3,
    //         'pay_msg' => $rdata['message'],
    //         'amount' => $rdata['order_amount'],
    //         'successStr' => 'ok',
    //         'failStr' => 'ok'
    //     ];
    //     $this->cashAct($pdata);
    // }
}
