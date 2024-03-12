<?php
require_once(dirname(__FILE__) . '/daemon.ini.php');

use think\facade\Db;

error_reporting(7);


while (true) {
    $now_time = time();
    $now_day = date('Ymd', $now_time);
    $list = Db::table('sys_user')->where("gid!=1 and (pidg1=-1 or pidg1=1 or pidg1=0 or pidg1 is null)")->order(['id' => 'desc'])->page(1, 1)->select()->toArray();
    if (!$list) {
        output('没有数据暂停5秒');
        sleep(5);
        continue;
    }

    foreach ($list as $user) {
        Db::startTrans();
        try {
            $pidg2 = updataUsercpid_gid81($user['id']);
            if ($pidg2 == 0) {
                Db::table('sys_user')->where('id=' . $user['id'])->update(['pidg2' => -2, 'pidg1' => -2]);
                $pidg2 = -2;
            }


            Db::commit();
            output('id:' . $user['id'] . ' : ' . $pidg1 . '|' . $pidg2);
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

}