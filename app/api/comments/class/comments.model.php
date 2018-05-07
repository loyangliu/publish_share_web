<?php
class CommentsModel extends AppModel {
	/**
	 * 重载init，初始化数据库
	 */
	protected function init() {
		$this->db = DbFactory::instance()->createDBO('publish_share');
	}
	
	/**
	 * 提交评论
	 * @param $data
	 */
	public function publish(& $data) {
		$time = date('Y-m-d H:i:s');
		
		$row = [
				'article_id' => $data['article_id'],
				'from' => $data['from'],
				'to' => $data['to'],
				'message' => $data['message'],
				'commit_at' => $time
		];
		
		// 插入帖子
		$this->db->insert($data, 'comments');
	}
	
	/**
	 * 获取帖子评论
	 */
	public function get($article_id) {
		$where = ' where article_id=' . $article_id;
		return $this->db->getAll("select id,article_id,from,to,commit_at from articles{$where}");
	}
}