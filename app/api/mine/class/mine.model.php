<?php
class MineModel extends AppModel {
	/**
	 * 重载init，初始化数据库
	 */
	protected function init() {
		$this->db = DbFactory::instance()->createDBO('publish_share');
	}
	
	/**
	 * 将时间转换成 X天前
	 */
	private function transformDate($time) {
		$time = new \Carbon\Carbon($time);
		
		return $time->diffForHumans();
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
		foreach($articles as $article) {
			$article['publish_at_human'] = $this->transformDate($article['publish_at']);
		}
		
		return $articles;
	}
	
	/**
	 * 对 帖子 关联 图片
	 * @param $articles
	 */
	public function getMyPublishArticlesWithImage(& $articles) {
		if($articles){
			$ids = array_column($articles, 'id');
			$in = implode(',', $ids);
			$rows = $this->db->getAll("select * from article_images where article_id in ({$in})");
			
			// 排序 ps：使用php对局部数组排序较mysql全部数据排序效率要高
			usort($rows, function($a, $b){
				if ($a==$b) return 0;
				return ($a['seq']<$b['seq'])?-1:1;
			});
				
			$images = [];
			foreach($rows as $row){
				$images[$row['article_id']][] = [
						'path' => $this->getImageUrl($row['image_path']),
						'thumbnail_path' => $this->getThumbnailImageUrl($row['image_path'])
				];
			}
			
			foreach($articles as & $article){
				$article['images'] = isset($images[$article['id']]) ? $images[$article['id']] : [];
			}
		}
	}
	
	/**
	 * 对 帖子 关联 关注者
	 */
	public function getMyPublishArticlesWithSubsribers(& $articles) {
		if($articles) {
			$article_ids = implode(',', array_column($articles, 'id'));
			$subscribes = $this->db->getAll("select * from subscribe where article_id in {$article_ids}");
			
			$subscribeInfo = [];
			foreach($subscribes as $subscribe) {
				$subscribeInfo[$subscribe['article_id']][] = array(
						'user_id' => $subscribe['user_id'],
						'user_nickname' => $subscribe['user_nickname'],
						'telphone' => $subscribe['telphone'],
						'message' => $subscribe['message'],
						'subscribe_time' => $subscribe['subscribe_time'],
						'subscribe_time_human' => $this->transformDate($subscribe['subscribe_time'])
				);
			}
			
			foreach($articles as & $article) {
				$article['subscribe'] = isset($subscribeInfo[$article['id']]) ? $subscribeInfo[$article['id']] : [];
			}
		}
	}
	
	/**
	 * 对 帖子 关联 评论
	 */
	public function getMyPublishArticlesWithComments(& $articles) {
		if($articles) {
			$ids = array_column($articles, 'id');
			$in = implode(',', $ids);
			$rows = $this->db->getAll("select * from comments where article_id in ({$in})");
			
			$comments = [];
			foreach($rows as $row) {
				$comments[$row['article_id']][] = array(
						'article_id'=>$row['article_id'],
						'from'=>$row['from'],
						'from_userid'=>$row['from_userid'],
						'to'=>$row['to'],
						'to_userid'=>$row['to_userid'],
						'message'=>$row['message'],
						'commit_at'=>$row['commit_at']
				);
			}
			
			foreach($articles as & $article) {
				$article['comments'] = isset($comments[$article['id']]) ? $comments[$article['id']] : [];
			}
		}
	}
	
	/**
	 * 对 帖子 关联 点赞
	 */
	public function getMyPublishArticlesWithStars(& $articles) {
		if($articles) {
			$ids = array_column($articles, 'id');
			$in = implode(',', $ids);
			$rows = $this->db->getAll("select * from stars where article_id in ({$in})");
			
			$stars = [];
			foreach ($rows as $row) {
				$froms = isset($stars[$row['article_id']]) ? array_column($stars[$row['article_id']], 'from') : [];
				
				if(!in_array($row['from'], $froms)) {
					$stars[$row['article_id']][] = array(
							'article_id' => $row['article_id'],
							'from' => $row['from'],
							'commit_at' => $row['commit_at']
					);
				}
			}
			
			foreach ($articles as & $article) {
				$article['prises'] = isset($stars[$article['id']]) ? $stars[$article['id']] : [];
			}
		}
	}
	
	/**
	 * 获取“我的发布”
	 */
	public function getMyPublishArticlesWithAll($page, $pageSize) {
		if(!$this->user) {
			return null;
		}
		
		$articles = $this->getMyPublishArticles($this->user[id], $page, $pageSize);
		
		// 对 帖子 关联 图片
		$this->getMyPublishArticlesWithImage($articles);
		
		// 对 帖子 关联 关注者
		$this->getMyPublishArticlesWithSubsribers($articles);
		
		// 对 帖子 关联 评论
		$this->getMyPublishArticlesWithComments($articles);
		
		// 对 帖子 关联 点赞
		$this->getMyPublishArticlesWithStars($articles);
		
		return $articles;
	}
}