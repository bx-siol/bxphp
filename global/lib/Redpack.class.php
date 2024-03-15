<?php
class Redpack
{

    /**
     * 红包金额(元)
     * 
     * -- 最小值:0.01。
     *
     * @var float
     */
    private $rewardMoney;
    private $realscatter;

    /**
     * 红包数量
     *
     * @var int
     */
    private $rewardNum;

    /**
     * 分散度值
     * 
     * -- 1 ~ 10000
     * --- 最佳值 100
     *
     * @var int
     */
    private $scatter;


    /**
     * @param  float  $totalMoney  随机总金额。
     * @param  int    $totalNum    拆分数量。
     * @param  int    $scatter      分散度值。
     * @return void
     */
    public function __construct($totalMoney, $totalNum, $scatter = 100)
    {
        $this->rewardMoney = $totalMoney;
        $this->rewardNum = $totalNum;
        $this->scatter = $scatter;
    }


    /**
     * 执行红包生成算法
     * @return array
     */
    public function getPack(): array
    {
        $this->realscatter = $this->scatter / 100;
        $avgRand = round(1 / $this->rewardNum, 4);
        $randArr = [];
        while (count($randArr) < $this->rewardNum) {
            $t = round(sqrt(mt_rand(1, 10000) / $this->realscatter));
            $randArr[] = $t;
        }
        $randAll   = round(array_sum($randArr) / count($randArr), 4);
        $mixrand   = round($randAll / $avgRand, 4);
        $rewardArr = [];
        foreach ($randArr as $key => $randVal) {
            $randVal     = round($randVal / $mixrand, 4);
            $rewardArr[] = round($this->rewardMoney * $randVal, 2);
        }
        sort($rewardArr);
        $rewardAll = array_sum($rewardArr);
        $rewardArr[$this->rewardNum - 1] = round($this->rewardMoney - ($rewardAll - $rewardArr[$this->rewardNum - 1]), 2);
        shuffle($rewardArr);
        return $rewardArr;
    }
}
