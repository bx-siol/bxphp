<?php

use think\facade\Db;

//获取权限节点nkey
function getUserNkey($uid)
{
	$neky_arr = [];
	$nodes = getAccessNodes($uid);
	foreach ($nodes as $nv) {
		$neky_arr[] = $nv['nkey'];
	}
	return $neky_arr;
}

//获取个人菜单
function getUserMenu($uid)
{
	if (!$uid) {
		return false;
	}

	$memcache = new MyRedis(0);
	$mem_key = 'menu_arr_' . $uid;
	$menu_arr = $memcache->get($mem_key);
	if (!$menu_arr) {
		$nodes = getAccessNodes($uid);
		$nodes_list = [];
		foreach ($nodes as $nv) {
			if (!$nv['type']) {
				continue;
			}
			$ca_arr = explode('_', $nv['nkey']);
			$path = '/' . strtolower($ca_arr[0]);
			if ($ca_arr[1]) {
				$path .= '/' . $ca_arr[1];
			}
			$node = [
				'id' => $nv['id'],
				'pid' => $nv['pid'],
				'name' => $nv['name'],
				'path' => $path,
				'nkey' => $nv['nkey'],
				'ico' => $nv['ico'],
				'public' => $nv['public'],
				'url' => $nv['url']
			];
			$nodes_list[] = $node;
		}
		$menu_arr = list2tree($nodes_list, 'id', 'pid', 'sub_node');
		$tag = 'usernodes_' . $uid;
		$memcache->set($mem_key, $menu_arr, 0, $tag);
	} else {
		//p($menu_arr);exit;
	}
	$memcache->close();
	unset($memcache);
	return $menu_arr;
}

//检查权限
function checkPower($nkey = '')
{
	$user = checkLogin();
	$result = hasPower($user, $nkey);
	if (!$result) {
		ReturnToJson(-99, '抱歉没有权限');
	}
	return $user;
}

//检查数据操作权限
function checkDataAction($groups = [])
{
	$pageuser = checkLogin();
	if (!$pageuser) {
		return false;
	}
	if ($groups && is_array($groups)) {
		return in_array($pageuser['gid'], $groups);
	}
	return $pageuser['gid'] < 42;
}

function hasPower($user, $nkey)
{
	if ($user['id'] == 1 || $user['gid'] == 1) { //超管不用检测权限
		return true;
	}
	if (!$nkey) {
		$nkey = NKEY;
	}
	$nodes = getAccessNodes($user['id']);
	if (!$nodes) {
		return false;
	}
	$result = false;
	foreach ($nodes as $nv) {
		if ($nv['nkey'] == $nkey) {
			$result = true;
			break;
		}
	}
	return $result;
}

//获取某个用户拥有的权限节点
function getAccessNodes($uid = 0)
{
	if (!$uid) {
		return false;
	}
	$user = getUserinfo($uid);
	if (!$user) {
		return false;
	}

	$memcache = new MyRedis(0);
	$mem_key2 = 'access_nodes_' . $uid;
	$nodes = $memcache->get($mem_key2);
	if (!$nodes) {
		$tag = 'usernodes_' . $user['id'];
		$where = '1=1';
		if ($user['id'] != 1 && $user['gid'] != 1) {
			$mem_key = 'access_ids_' . $uid;
			$access_ids_arr = $memcache->get($mem_key);
			if (!$access_ids_arr) {
				$access = Db::table('sys_access')
					->field(['node_ids'])
					->where("uid={$user['id']} or gid={$user['gid']}")
					->select()
					->toArray();
				$access_ids_arr = [];
				foreach ($access as $acv) {
					if (!$acv['node_ids']) {
						continue;
					}
					$tmp_node_ids = explode(',', $acv['node_ids']);
					foreach ($tmp_node_ids as $tv) {
						$i_tv = intval($tv);
						if ($i_tv) {
							$access_ids_arr[] = $i_tv;
						}
					}
				}
				if ($access_ids_arr) {
					$access_ids_arr = array_unique($access_ids_arr);
				}
				$memcache->set($mem_key, $access_ids_arr, 0, $tag);
			}
			$access_ids_arr[] = 0;
			$access_ids_str = implode(',', $access_ids_arr);
			$where = "(id in({$access_ids_str}) or public=1)";
		}
		$nodes = Db::table('sys_node')
			->where($where)
			->order(['pid', 'sort' => 'desc', 'id'])
			->select()->toArray();
		$memcache->set($mem_key2, $nodes, 0, $tag);
	}
	$memcache->close();
	unset($memcache);
	return $nodes;
}
