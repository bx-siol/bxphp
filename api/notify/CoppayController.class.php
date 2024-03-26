<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class CoppayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }
    public function GetPayName()
    {
        return 'coppay';
    }
    public function _index()
    {
        echo 'coppay';
    }
    public function _pay()
    {
        // $jsonStr = trim(file_get_contents('php://input'));
        // $params = json_decode($jsonStr, true);
        // if (!$params)
        $params = $_POST;
        writeLog(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), GetPayName() . '/notify/pay');
        require_once APP_PATH . 'common/pay/' . GetPayName() . '.php';
        $sign = paySign($params, true);
        writeLog($sign, GetPayName() . '/notify/pay');
        if (!$sign)
            ReturnToJson(-1, 'Sign error');
        $pdata = [
            'code' => $params['status'] == '1' ? 1 : -1,
            'osn' => $params['orderId'],
            'amount' => $params['orderAmt'],
            'successStr' => 'success'
        ];
        $this->payAct($pdata, GetPayName() . '');
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