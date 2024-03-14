<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class NewsController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	//系统公告
	public function _notice()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);

		$where = '1=1';
		$count_item = Db::table('news_notice log')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(
			['news_notice' => 'log'],
			[
				'id', 'title', 'text', 'create_time'
			]
		)
			->where($where)
			->order(['id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//消息公告
	public function _feedback()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);
		$params['s_cid'] = intval($params['s_cid']);

		$where = '1=1';
		$where .= empty($params['s_cid']) ? '' : " and log.cid={$params['s_cid']}";
		$where .= " and log.status=2";
		$count_item = Db::table('news_article log')
			->leftJoin('news_category c', 'log.cid=c.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(
			['news_article' => 'log'],
			[
				'id', 'title', 'publish_time', 'is_recommend', 'author', 'cover', 'ndesc', 'content'
			]
		)
			->view(['news_category' => 'c'], ['name' => 'cat_name', 'cover' => 'cat_cover'], 'log.cid=c.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$yes_or_no = getConfig('yes_or_no');
		foreach ($list as &$item) {
			$item['publish_time'] = date('m-d H:i', $item['publish_time']);
			$item['is_recommend_flag'] = $yes_or_no[$item['is_recommend']];
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$category_arr = Db::table('news_category')->field(['id', 'name'])->where("pid=2")->select()->toArray();
			$return_data['category_arr'] = $category_arr;
			$cnf_problems = getConfig('cnf_problems');
			$return_data['problems_arr'] = $cnf_problems;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _feedbackAct()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['problem'] = intval($params['problem']);
		if (!$params['title']) {
			ReturnToJson(-1, '请填写标题');
		}
		if (!$params['content']) {
			ReturnToJson(-1, '请填写内容');
		}
		$db_item = [
			'uid' => $pageuser['id'],
			'problem' => $params['problem'],
			'title' => $params['title'],
			'content' => $params['content'],
			'covers' => json_encode($params['covers']),
			'create_time' => NOW_TIME
		];
		try {
			Db::table('news_feedback')->insertGetId($db_item);
		} catch (\Exceptioin $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '提交成功');
	}

	public function _feedbackList()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);

		$where = "log.uid={$pageuser['id']}";
		$count_item = Db::table('news_feedback log')
			//->leftJoin('news_category c','log.cid=c.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(
			['news_feedback' => 'log'],
			['*']
		)
			//->view(['news_category'=>'c'],['name'=>'cat_name'],'log.cid=c.id','LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_problems = getConfig('cnf_problems');
		$cnf_problem_status = getConfig('cnf_problem_status');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i', $item['create_time']);
			$item['problem_flag'] = $cnf_problems[$item['problem']];
			$item['status_flag'] = $cnf_problem_status[$item['status']];
			$item['covers'] = json_decode($item['covers'], true);
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _list()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);
		$params['s_cid'] = intval($params['s_cid']);

		$where = '1=1';

		if ($params['ishot'] == '1') {
			$where .= " and log.is_recommend=1";
		}
		$where .= empty($params['s_cid']) ? '' : " and log.cid={$params['s_cid']}";
		$where .= " and log.status=2";
		$count_item = Db::table('news_article log')
			->leftJoin('news_category c', 'log.cid=c.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(
			['news_article' => 'log'],
			[
				'id', 'title', 'publish_time', 'is_recommend', 'author', 'cover', 'ndesc', 'content'
			]
		)
			->view(['news_category' => 'c'], ['name' => 'cat_name'], 'log.cid=c.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$yes_or_no = getConfig('yes_or_no');
		foreach ($list as &$item) {
			$item['publish_time'] = date('m-d H:i', $item['publish_time']);
			$item['is_recommend_flag'] = $yes_or_no[$item['is_recommend']];
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			if ($params['s_cid']) {
				$return_data['category'] = Db::table('news_category')->where("id={$params['s_cid']}")->find();
			}
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	//文章详情
	public function _info()
	{
		//$pageuser=checkLogin();
		$id = intval($this->params['id']);
		$where = "log.id={$id}";
		$item = Db::view(
			['news_article' => 'log'],
			[
				'id', 'title', 'publish_time', 'url', 'is_recommend', 'author', 'cover', 'ndesc', 'content'
			]
		)
			->view(['news_category' => 'c'], ['name' => 'cat_name'], 'log.cid=c.id', 'LEFT')
			->where($where)->find();
		if (!$item) {
			ReturnToJson(-1, '不存在相应的记录');
		}
		$item['publish_time'] = date('m-d H:i', $item['publish_time']);
		$pre = Db::table('news_article')->whereRaw("id<{$item['id']}")->field(['id', 'title'])->order(['id' => 'desc'])->limit(1)->find();
		$next = Db::table('news_article')->whereRaw("id>{$item['id']}")->field(['id', 'title'])->order(['id' => 'asc'])->limit(1)->find();
		$return_data = [
			'info' => $item,
			'pre' => $pre,
			'next' => $next
		];
		ReturnToJson(1, 'ok', $return_data);
	}
	//社区新闻列表
	public function _communitylist()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);

		$mem_key = 'indexcommunitylist:' . $params['page'] . $pageuser['id'];
		$return_data = $this->redis->get($mem_key);
		if (!$return_data) {
			$where  = ' status=2 ';
			$list = Db::table('news_community')->where($where)
				->order(['sort' => 'desc'])
				->page($params['page'], $this->pageSize)
				->select()
				->toArray();
			$count_item =  Db::table('news_community')->fieldRaw('count(1) as cnt')->find();
			$total_page = ceil($count_item['cnt'] / $this->pageSize);

			foreach ($list as &$item) {
				$item['releasetime'] = date('d/m/y H:i:s', $item['releasetime']);
			}
			$return_data = [
				'list' => $list,
				'count' => intval($count_item['cnt']),
				'page' => $params['page'] + 1,
				'finished' => $params['page'] >= $total_page ? true : false,
				'limit' => $this->pageSize
			];
			$this->redis->set($mem_key, $return_data, 120);
		}

		ReturnToJson(1, 'ok', $return_data);
	}

	//添加赞
	public function _communityadd()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$item = Db::table('news_community')->where(' id=' . $params['id'])->lock(true)->find();
		Db::table('news_community')->where(' id=' . $params['id'])->update(['commendatory' => $item['commendatory'] + 1]);
		ReturnToJson(1, 'ok');
	}
}
