<?php
class MineModel extends AppModel {
	/**
	 * 重载init，初始化数据库
	 */
	protected function init() {
		$this->db = DbFactory::instance()->createDBO('publish_share');
	}
	
	
	public function getPublishNum() {
		return $this->db->getOne("select count(*) from articles where id={$this->user['id']}");
	}
	
	public function getCommentNum() {
		return $this->db->getOne("select count(*) from comments where from_userid={$this->user['id']}");
	}
	
	public function getSubscribeNum() {
		return $this->db->getOne("select count(*) from subscribe where user_id={$this->user['id']}");
	}
	
	
	public function getMyPublishArticles($userid, $page, $pageSize) {
		$start = ($page - 1) * $pageSize;
		
		$where = " where user_id={$userid}";
		$order = " order by publish_at desc";
		$limit = " limit {$start},{$pageSize}";
		
		$articles = $this->db->getAll("select * from articles {$where} {$order} {$limit}");
		return $articles;
	}
	
	/**
	 * 获取“我的发布”
	 */
	public function getMyPublishArticlesWithAll($page, $pageSize) {
		if(!$this->user) {
			return null;
		}
		
		$articles = $this->getMyPublishArticles($this->user[id], $page, $pageSize);
		
		return $articles;
	}
}