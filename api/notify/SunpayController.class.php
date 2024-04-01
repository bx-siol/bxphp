<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class SunpayController extends BaseController
{

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
        writeLog('开始', 'sunpay/notify/pay');
        $jsonStr = trim(file_get_contents('php://input'));
        $jsonStr = "tradeResult=1&oriAmount=611.00&amount=611.00&mchId=100001002&orderNo=341644414&mchOrderNo=11c5f441642bbdc2&sign=a268431006928f3144a980b55f683dc8&signType=MD5&orderDate=2024-04-01+21%3A39%3A40";
        $params = explode("&", $jsonStr);
        writeLog('pdatajwt : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'sunpay/notify/pay');
        if (!$params)
            $params = $_POST;

        foreach ($params as $k => $v) {
            $arr = explode("=", $v);
            $rdata[$arr[0]] = urldecode($arr[1]);
        }
        writeLog('pdata : ' . json_encode($rdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'sunpay/notify/pay');

        require_once APP_PATH . 'common/pay/sunpay.php';
        ksort($rdata);
        $sign = paySign($rdata,$config['mch_key']);
        writeLog($sign, 'sunpay/notify/pay');
        if ($sign != $rdata['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'code' => $rdata['status'] == '1' ? 1 : -1,
            'osn' => $rdata['mchOrderNo'],
            'amount' => $rdata['amount'] / 100,
            'successStr' => 'OK'
        ];
        writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'sunpay/notify/pay');
        $this->payAct($pdata, 'sunpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        writeLog('pdata : ' . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'sunpay/notify/cash');
        if (!$params)
            $params = $_POST;

        require_once APP_PATH . 'common/cash/sunpay.php';
        $sign = CashSign($params);
        writeLog($sign, 'sunpay/notify/pay');
        if ($sign != $params['sign'])
            ReturnToJson(-1, 'Sign error');

        $pdata = [
            'osn' => $params['merTransferId'],
            'out_osn' => $params['tradeNo'],
            'pay_status' => $params['tradeResult'] == '1' ? 9 : 3,
            'pay_msg' => $params['respCode'],
            'amount' => $params['transferAmount'] / 100,
            'successStr' => 'OK',
            'failStr' => 'OK1'
        ];

        //冲正状态
        if ($params['status'] == '4')
            $pdata['pay_status'] = 4;

        $this->cashAct($pdata);
    }
}