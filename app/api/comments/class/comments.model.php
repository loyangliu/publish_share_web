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
	public function publish($data) {
		$time = date('Y-m-d H:i:s');
		
		$row = [
				'article_id' => $data['article_id'],
				'from' => $data['from'],
				'to' => $data['to'],
				'message' => $data['message'],
				'commit_at' => $time
		];
		
		// 插入评论
		$this->db->insert($row, 'comments');
	}
	
	/**
	 * 获取帖子评论
	 */
	public function get($article_id) {
		$where = ' where article_id=' . $article_id;
		return $this->db->getAll("select id,article_id,from,to,message,commit_at from articles{$where}");
	}
	
	/**
	 * 点赞
	 */
	public function prise($data) {
		$time = date('Y-m-d H:i:s');
		
		$row = [
				'article_id' => $data['article_id'],
				'from' => $data['from'],
				'commit_at' => $time
		];
		
		// 插入点赞
		$this->db->insert($row, 'stars');
	}
	
	/**
	 * 获取点赞
	 */
	public function getStars($article_id) {
		$where = ' where article_id=' . $article_id;
		return $this->db->getAll("select `id`,`article_id`,`from`,`commit_at` from stars{$where}");
	}
}