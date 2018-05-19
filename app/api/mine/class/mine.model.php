<?php
class MineModel extends AppModel {
	/**
	 * 重载init，初始化数据库
	 */
	protected function init() {
		$this->db = DbFactory::instance()->createDBO('publish_share');
	}
	
	private function getMaxCurserId() {
		return $this->db->getOne("select max(id) from articles");
	}
	
	public function getMyPublishArticles($curserId, $page, $rowSize) {
		if(!$curserId) {
			$curserId = $this->getMaxCurserId();
		}
		
		$userid = $this->user['id'];
		
		$from = 'articles';
		//$where = "WHERE user_id='{$this->user['id']}' AND "
		return null;
	}
	
	/**
	 * 获取“我的发布”
	 */
	public function getMyPublishArticlesWithAll($curserId, $page, $rowSize = 5)
	{
		$articles = $this->getMyPublishArticles($curserId, $page, $rowSize);
		
		
		return $articles;
	}
}