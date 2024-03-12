<?php
require_once(dirname(__FILE__) . '/daemon.ini.php');
use think\facade\Db;

// 设置每次处理的数据量
$chunkSize = 100;

// 获取当前时间戳和日期
$nowTime = time();
$nowDay = date('Ymd', $nowTime);

// 循环处理数据
while (true) {
    // 获取需要处理的数据
    $list = Db::table('sys_user')
        ->where('gid != 1')
        ->whereIn('pidg1', [-2, -1, 0, null])
        ->order('id')
        ->limit($chunkSize)
        ->select();

    // 如果没有数据，则等待 5 秒后继续
    if ($list->isEmpty()) {
        output('没有数据暂停5秒');
        sleep(5);
        continue;
    }

    // 定义一个标识变量，用于记录是否有数据更新成功
    $hasUpdated = false;

    // 遍历数据，更新 pidg1 和 pidg2 字段
    foreach ($list as $user) {
        // 更新 pidg1 和 pidg2 字段
        Db::startTrans();
        try {
            $pidg2 = updata_pidg($user['id']);
            if ($pidg2 == 0) {
                Db::table('sys_user')
                    ->where('id', $user['id'])
                    ->update([
                        'pidg1' => -2,
                        'pidg2' => -2
                    ]);
                $pidg2 = -1;
            }
            Db::commit();

            // 输出更新成功的数据
            output('id:' . $user['id'] . ' : ' . $user['pidg1'] . '|' . $pidg2);

            // 更新标识变量
            $hasUpdated = true;
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    // 如果所有数据都处理完毕并且没有更新成功的数据，则等待 5 秒后继续
    if (!$hasUpdated) {
        output('没有需要更新的数据，暂停5秒');
        sleep(5);
    }
}

/**
 * 更新用户的 pidg1 和 pidg2 字段
 *
 * @param integer $uid 用户 ID
 * @return integer 返回更新后的 pidg2 值，如果没有更新成功，则返回 0
 */
function updata_pidg($uid)
{
    $uid = intval($uid);
    $sql = "WITH RECURSIVE cte AS (
                SELECT id, pid, gid
                FROM sys_user
                WHERE id = {$uid}
            UNION ALL
                SELECT t.id, t.pid, t.gid
                FROM sys_user t
                JOIN cte ON t.id = cte.pid
            ) 
            UPDATE sys_user
            SET pidg1 = (SELECT id FROM cte WHERE gid = 71),
                pidg2 = (SELECT id FROM cte WHERE gid = 81) 
            WHERE id = {$uid}";
    $pidg2 = Db::execute($sql);
    return $pidg2;
}