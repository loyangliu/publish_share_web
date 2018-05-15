<?php
class CommentsController extends AppController {
	/**
	 * 业务侧根据需要重载自定义登录态验证
	 */
	public function loginCheck() {
		$needCheckActions =  ['publish', 'prise'];
		if( in_array($this->ruler->actionName, $needCheckActions) ) {
			$api_token = addslashes($_REQUEST['api_token']);
			if($api_token){
				$user = $this->model->db->getRow("select * from users where api_token='{$api_token}'");
				if(!$user){
					echo apiJson(1000, '未认证');
					return false;
				}
			} else {
				echo apiJson(1001, '未知的登录态参数');
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * 提交评论
	 */
	public function publish() {
		$params = array_map(function($v){
			return addslashes(trim($v));
		}, $_POST);
		
		$this->model->log(json_encode($params));
			
		if(!isset($params['article_id'])){
			echo apiJson(-1, 'article_id 不能为空！');return;
		}
		
		if(!isset($params['from'])){
			echo apiJson(-1, 'from 不能为空！');return;
		}
		
		if(!isset($params['to'])){
			echo apiJson(-1, 'to 不能为空！');return;
		}
		
		if(!isset($params['message'])){
			echo apiJson(-1, 'message 不能为空！');return;
		}
		
		$data = [
				'article_id' => $params['article_id'],
				'from' => $params['from'],
				'to' => $params['to'],
				'message' => $params['message']
		];
		
		$this->model->publish($data);
		
		echo apiJson(0, '发布成功！');
	}
	
	/**
	 * 提交点赞
	 */
	public function prise() {
		$params = array_map(function($e){
			return addslashes(trim($e));
		}, $_POST);
		
		if(!isset($params['article_id'])){
			echo apiJson(-1, 'article_id 不能为空！');return;
		}
		
		if(!isset($params['from'])){
			echo apiJson(-1, 'from 不能为空！');return;
		}
		
		$prises = $this->model->getStars($params['article_id']);
		$froms = array_column($prises, 'from');
		
		if(in_array($params['from'], $froms)) {
			echo apiJson(-1, '已经点赞！');
			return;
		}
		
		$data = [
				'article_id' => $params['article_id'],
				'from' => $params['from']
		];
		
		$this->model->prise($data);
		
		echo apiJson(0, '发布成功！');
	}
	
	/**
	 * 获取帖子评论
	 */
	public function get() {
		$articleId = intval($_GET['article_id']);
		$comments = $this->model->get($articleId);
		echo apiJson(0, null, ['article_id'=>$articleId , 'comments' => $comments]);
	}
}