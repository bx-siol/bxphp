<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class AtpayController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function _index()
    {
        echo 'atpay';
    }
    public function _pay()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);
        // 在Nginx+FPM环境下获取HTTP头信息
        $xQuAccessKey = isset($_SERVER['HTTP_X_QU_ACCESS_KEY']) ? $_SERVER['HTTP_X_QU_ACCESS_KEY'] : '';
        $xQuMid = isset($_SERVER['HTTP_X_QU_MID']) ? $_SERVER['HTTP_X_QU_MID'] : '';
        $xQuNonce = isset($_SERVER['HTTP_X_QU_NONCE']) ? $_SERVER['HTTP_X_QU_NONCE'] : '';
        $xQuSignatureMethod = isset($_SERVER['HTTP_X_QU_SIGNATURE_METHOD']) ? $_SERVER['HTTP_X_QU_SIGNATURE_METHOD'] : '';
        $xQuTimestamp = isset($_SERVER['HTTP_X_QU_TIMESTAMP']) ? $_SERVER['HTTP_X_QU_TIMESTAMP'] : '';
        $xQuSignatureVersion = isset($_SERVER['HTTP_X_QU_SIGNATURE_VERSION']) ? $_SERVER['HTTP_X_QU_SIGNATURE_VERSION'] : '';
        $xQuSignature = isset($_SERVER['HTTP_X_QU_SIGNATURE']) ? $_SERVER['HTTP_X_QU_SIGNATURE'] : '';

        // 将获取的头信息存入数组，便于操作
        $headers = [
            'X-Qu-Access-Key' => $xQuAccessKey,
            'X-Qu-Mid' => $xQuMid,
            'X-Qu-Nonce' => $xQuNonce,
            'X-Qu-Signature-Method' => $xQuSignatureMethod,
            'X-Qu-Timestamp' => $xQuTimestamp,
            'X-Qu-Signature-Version' => $xQuSignatureVersion,
            // 'X-Qu-Signature' => $xQuSignature
        ];
        writeLog(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'atpay/notify/pay');
        writeLog(json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'atpay/notify/pay');
        if (!$params)
            $params = $_POST;
        require_once APP_PATH . 'common/pay/atpay.php';
        $sign = getsignstr($headers);
        writeLog($sign . '||||' . $xQuSignature, 'atpay/notify/pay');
        //writeLog($sign, 'atpay/notify/pay');
        if ($sign != $xQuSignature)
            ReturnToJson(-1, 'Sign error');
        $pdata = [
            'code' =>  $params['resource']['tradeStatus'] == 'SUCCESS' ? 1 : 0,
            'osn' => $params['resource']['outTradeNo'],
            'amount' => $params['resource']['tradeAmount'],
            'successStr' => 'OK'
        ];
        //writeLog(json_encode($pdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'atpay/notify/pay');
        $this->payAct($pdata, 'atpay');
    }

    public function _cash()
    {
        $jsonStr = trim(file_get_contents('php://input'));
        $params = json_decode($jsonStr, true);      
        // 在Nginx+FPM环境下获取HTTP头信息
        $xQuAccessKey = isset($_SERVER['HTTP_X_QU_ACCESS_KEY']) ? $_SERVER['HTTP_X_QU_ACCESS_KEY'] : '';
        $xQuMid = isset($_SERVER['HTTP_X_QU_MID']) ? $_SERVER['HTTP_X_QU_MID'] : '';
        $xQuNonce = isset($_SERVER['HTTP_X_QU_NONCE']) ? $_SERVER['HTTP_X_QU_NONCE'] : '';
        $xQuSignatureMethod = isset($_SERVER['HTTP_X_QU_SIGNATURE_METHOD']) ? $_SERVER['HTTP_X_QU_SIGNATURE_METHOD'] : '';
        $xQuTimestamp = isset($_SERVER['HTTP_X_QU_TIMESTAMP']) ? $_SERVER['HTTP_X_QU_TIMESTAMP'] : '';
        $xQuSignatureVersion = isset($_SERVER['HTTP_X_QU_SIGNATURE_VERSION']) ? $_SERVER['HTTP_X_QU_SIGNATURE_VERSION'] : '';
        $xQuSignature = isset($_SERVER['HTTP_X_QU_SIGNATURE']) ? $_SERVER['HTTP_X_QU_SIGNATURE'] : '';

        // 将获取的头信息存入数组，便于操作
        $headers = [
            'X-Qu-Access-Key' => $xQuAccessKey,
            'X-Qu-Mid' => $xQuMid,
            'X-Qu-Nonce' => $xQuNonce,
            'X-Qu-Signature-Method' => $xQuSignatureMethod,
            'X-Qu-Timestamp' => $xQuTimestamp,
            'X-Qu-Signature-Version' => $xQuSignatureVersion,
            // 'X-Qu-Signature' => $xQuSignature
        ];
        writeLog(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'atpay/notify/cash');
        writeLog(json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'atpay/notify/cash');
        if (!$params)
            $params = $_POST;
        require_once APP_PATH . 'common/cash/atpay.php';
        $sign = getsignstr($headers);
        writeLog($sign . '||||' . $xQuSignature, 'atpay/notify/cash');
        if ($sign != $xQuSignature)
            ReturnToJson(-1, 'Sign error');
      
        $pdata = [
            'osn' => $params['orderId'],
            'out_osn' => $params['payOrderId'],
            'pay_status' => $params['status'] == '1' ? 9 : 3,
            'pay_msg' => $params['statusDesc'],
            'amount' => $params['amount'] / 100,
            'successStr' => 'OK',
            'failStr' => 'OK1'
        ];

        $this->cashAct($pdata);
    }
    
    public function _order()
    {
		$params = $this->params;
        $fin_cashlog = Db::table('fin_cashlog')->where("id={$params['id']}")->find();
        require_once APP_PATH . 'common/cash/atpay.php';
        $result = CashOrder($fin_cashlog);
        
	    return $result;
    }
}
