<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class NewsController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	//文章分类
	public function _category()
	{
		checkPower();
		$list = Db::table('news_category')->where("status<99")->order(['sort' => 'desc'])->select()->toArray();
		$tree = list2tree($list);
		$return_data = [
			'list' => $tree
		];
		jReturn(1, 'ok', $return_data);
	}

	public function _category_update()
	{
		checkPower();
		$params = $this->params;
		$params['id'] = intval($params['id']);
		$params['pid'] = intval($params['pid']);
		$params['sort'] = intval($params['sort']);
		$params['status'] = intval($params['status']);
		if (!$params['name']) {
			jReturn(-1, '请填写分类名称');
		}
		if ($params['pid']) {
			$pitem = Db::table('news_category')->where("id={$params['pid']} and status<99")->find();
			if (!$pitem) {
				jReturn(-1, '不存在相应的父级分类');
			}
		}
		$news_category = [
			'pid' => $params['pid'],
			'sort' => $params['sort'],
			'status' => $params['status'],
			'name' => $params['name'],
			'cover' => $params['cover'],
			'remark' => $params['remark'],
			'update_time' => NOW_TIME
		];
		try {
			if ($params['id']) {
				$item = Db::table('news_category')->where("id={$params['id']} and status<99")->find();
				if (!$item) {
					jReturn(-1, '不存在相应的记录');
				}
				Db::table('news_category')->where("id={$item['id']}")->update($news_category);
			} else {
				$news_category['create_time'] = NOW_TIME;
				Db::table('news_category')->insertGetId($news_category);
			}
		} catch (\Exception $e) {
			jReturn(-1, '系统繁忙请稍后再试');
		}
		jReturn(1, '操作成功');
	}

	public function _category_delete()
	{
		checkPower();
		$params = $this->params;
		$params['id'] = intval($params['id']);
		$item = Db::table('news_category')->where("id={$params['id']}")->find();
		if (!$item) {
			jReturn(-1, '该记录已被删除');
		}
		$news_category = ['status' => 99];
		$list = Db::table('news_category')->where("status<99")->order(['sort' => 'desc'])->select()->toArray();
		$ids = getTreeIds($list, $item['id']);
		$ids_str = implode(',', $ids);
		try {
			Db::table('news_category')->where("id in ({$ids_str})")->update($news_category);
		} catch (\Exception $e) {
			jReturn(-1, '系统繁忙请稍后再试');
		}
		jReturn(1, '操作成功');
	}

	///////////////////////////////////////////////////////////////////////////
	//文章列表	
	public function _article()
	{
		checkPower();
		$params = $this->params;
		$params['s_cid'] = intval($params['s_cid']);
		$where = "log.status<99";
		$where .= empty($params['s_cid']) ? '' : " and log.cid={$params['s_cid']}";
		if (is_numeric($params['s_is_recommend'])) {
			$params['s_is_recommend'] = intval($params['s_is_recommend']);
			$where .= " and log.is_recommend={$params['s_is_recommend']}";
		}
		$where .= empty($params['s_keyword']) ? '' : " and (log.id='{$params['s_keyword']}' or log.title like '%{$params['s_keyword']}%')";

		$count_item = Db::table('news_article log')
			->leftJoin('news_category cat', 'log.cid=cat.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['news_article' => 'log'], ['*'])
			->view(['news_category' => 'cat'], ['name' => 'cname'], 'log.cid=cat.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$sys_arc_status = getConfig('sys_arc_status');
		$yes_or_no = getConfig('yes_or_no');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i', $item['create_time']);
			$item['publish_time_flag'] = date('Y-m-d H:i:s', $item['publish_time']);
			$item['status_flag'] = $sys_arc_status[$item['status']];
			$item['is_recommend_flag'] = $yes_or_no[$item['is_recommend']];
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$category_list = Db::table('news_category log')
				->field(['id', 'pid', 'name'])
				->where('status<99')
				->orderRaw("log.sort desc")
				->select()->toArray();
			$category_tree = list2Select($category_list);
			$return_data['category_tree'] = $category_tree;
		}
		jReturn(1, 'ok', $return_data);
	}

	public function _article_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$cid = intval($params['cid']);
		$status = intval($params['status']);
		$is_recommend = intval($params['is_recommend']);
		$category = [];
		if (!$cid) {
			jReturn(-1, '请选择分类');
		} else {
			$category = Db::table('news_category')->where("id={$cid} and status<99")->find();
			if (!$category) {
				jReturn(-1, '不存在相应分类');
			}
		}
		if (!$params['title']) {
			jReturn(-1, '请填写文章标题');
		}
		if (!$params['cover']) {
			jReturn(-1, '请上传封面图');
		}
		if (!$status) {
			jReturn(-1, '未知文章状态');
		}
		if ($params['publish_time_flag']) {
			$publish_time = strtotime($params['publish_time_flag']);
		} else {
			$publish_time = NOW_TIME;
		}
		$news_article = [
			'title' => $_POST['title'],
			'ndesc' => $params['ndesc'],
			'author' => $params['author'],
			'url' => $params['url'],
			'label' => str_replace('，', ',', $params['label']),
			'status' => $status,
			'is_recommend' => $is_recommend,
			'cid' => $cid,
			'publish_time' => $publish_time,
			'cover' => $params['cover'],
			'content' => $_POST['content']
		];
		try {
			if ($item_id) {
				$res = Db::table('news_article')->whereRaw('id=:id', ['id' => $item_id])->update($news_article);
				$news_article['id'] = $item_id;
			} else {
				$news_article['create_id'] = $pageuser['id'];
				$news_article['create_time'] = NOW_TIME;
				$res = Db::table('news_article')->insertGetId($news_article);
				$news_article['id'] = $res;
			}
		} catch (\Exception $e) {
			jReturn(-1, '系统繁忙请稍后再试');
		}
		$sys_arc_status = getConfig('sys_arc_status');
		$yes_or_no = getConfig('yes_or_no');
		$return_data = [
			'cname' => $category['name'],
			'publish_time' => $publish_time,
			'status_flag' => $sys_arc_status[$news_article['status']],
			'is_recommend_flag' => $yes_or_no[$news_article['is_recommend']],
		];
		jReturn(1, '操作成功', $return_data);
	}

	public function _article_delete()
	{
		checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			jReturn(-1, '缺少参数');
		}
		$news_article = ['status' => 99];
		try {
			Db::table('news_article')->whereRaw('id=:id', ['id' => $item_id])->update($news_article);
		} catch (\Exception $e) {
			jReturn(-1, '系统繁忙请稍后再试');
		}
		jReturn(1, '操作成功');
	}

	/////////////////公告列表/////////////

	public function _notice()
	{
		$pageuser = checkPower();
		$params = $this->params;

		$where = "log.status<99";
		$where .= empty($params['s_keyword']) ? '' : " and (log.text like '%{$params['s_keyword']}%')";

		$count_item = Db::table('news_notice log')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();
		$list = Db::view(['news_notice' => 'log'], ['*'])
			->where($where)
			->order(['log.sort' => 'desc', 'log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i', $item['create_time']);
		}
		$data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		jReturn(1, 'ok', $data);
	}


	//更新
	public function _notice_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['sort'] = intval($params['sort']);
		if (!$params['text']) {
			jReturn(-1, '请填写内容');
		}
		$news_notice = [
			'title' => $params['title'],
			'text' => $params['text'],
			'sort' => $params['sort'] ? $params['sort'] : 100,
		];
		try {
			if ($item_id) {
				$res = Db::table('news_notice')->whereRaw('id=:id', ['id' => $item_id])->update($news_notice);
				$news_notice['id'] = $item_id;
			} else {
				$news_notice['create_time'] = NOW_TIME;
				$res = Db::table('news_notice')->insertGetId($news_notice);
				$news_notice['id'] = $res;
			}
		} catch (\Exception $e) {
			jReturn(-1, '系统繁忙请稍后再试');
		}
		actionLog(['opt_name' => '更新', 'sql_str' => json_encode($news_notice)]);
		$return_data = [];
		jReturn(1, '操作成功', $return_data);
	}

	//删除
	public function _notice_delete()
	{
		checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			jReturn(-1, '缺少参数');
		}
		$item = Db::table('news_notice')->where("id={$item_id} and status<99")->find();
		if (!$item) {
			jReturn(-1, '该记录已删除');
		}
		$news_notice = ['status' => 99];
		try {
			$res = Db::table('news_notice')->whereRaw('id=:id', ['id' => $item_id])->update($news_notice);
		} catch (\Exception $e) {
			jReturn(-1, '系统繁忙请稍后再试');
		}
		jReturn(1, '操作成功');
	}



	///////////////////////////////////////////////////////////////////////////
	//community列表	
	public function _community()
	{
		checkPower();
		$params = $this->params;
		$where  = ' log.status<99 ';
		$where .= empty($params['s_keyword']) ? '' : " and (log.id='{$params['s_keyword']}' or log.nikename like '%{$params['s_keyword']}%')";

		$count_item = Db::table('news_community log')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['news_community' => 'log'], ['*'])
			->where($where)
			->order(['log.sort' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['releasetime'] = date('Y-m-d H:i:s', $item['releasetime']);
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		jReturn(1, 'ok', $return_data);
	}

	public function _community_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);

		if (!$params['content']) {
			jReturn(-1, '请填写社区内容');
		}
		if (!$params['imgs']) {
			jReturn(-1, '请上传社区图');
		}

		if ($params['releasetime']) {
			$publish_time = strtotime($params['releasetime']);
		} else {
			$publish_time = NOW_TIME;
		}
		$news_community = [
			'nikename' => $_POST['nikename'],
			'headimg' => $params['headimg'],
			'content' => $_POST['content'],
			'commendatory' => $params['commendatory'],
			'sort' =>   $params['sort'],
			'comments' =>   $params['comments'],
			'releasetime' => $publish_time,
			'imgs' => $params['imgs'],
			'status' => $params['status'],
		];
		try {
			if ($item_id) {
				$res = Db::table('news_community')->whereRaw('id=:id', ['id' => $item_id])->update($news_community);
				$news_community['id'] = $item_id;
			} else {
				$res = Db::table('news_community')->insertGetId($news_community);
				$news_community['id'] = $res;
			}
		} catch (\Exception $e) {
			jReturn(-1, '系统繁忙请稍后再试');
		}
		$return_data = [
			'id' => $news_community['id'],
			'publish_time' => $publish_time,
		];
		jReturn(1, '操作成功', $return_data);
	}

	public function _community_delete()
	{
		checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			jReturn(-1, '缺少参数');
		}
		$news_community = ['status' => 99];
		try {
			Db::table('news_community')->whereRaw('id=:id', ['id' => $item_id])->update($news_community);
		} catch (\Exception $e) {
			jReturn(-1, '系统繁忙请稍后再试');
		}
		jReturn(1, '操作成功');
	}
}
