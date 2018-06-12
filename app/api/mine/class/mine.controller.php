<?php
class MineController extends AppController {
	/**
     * 业务侧根据需要重载自定义登录态验证
     */
    public function loginCheck() {
    	$needCheckActions =  ['home', 'myPublish', 'mySubscribe'];
    	if(in_array($this->ruler->actionName, $needCheckActions)) {
    		$api_token = addslashes($_REQUEST['api_token']);
    		$this->model->user = $this->user = $api_token ? $this->model->db->getRow("select * from users where api_token='{$api_token}'") : false;
    		
    		if(!$this->user) {
    			echo apiJson(1000, '未认证');
    			return false;
    		}
    	}
    	
    	return true;
    }
    
    /**
     * 初始化数据
     */
    public function home() {
    	if($this->user) {
    		$publisNum = $this->model->getPublishNum();
    		$commentNum = $this->model->getCommentNum();
    		$subscribeNum = $this->model->getSubscribeNum();
    		
    		if($publisNum != null && $commentNum != null && $subscribeNum != null) {
    			$data = [
    				'publisNum' => $publisNum,
    				'commentNum' => $commentNum,
    				'subscribeNum' => $subscribeNum,
    			];
    			echo apiJson(0, null, ['my_num' => $data]);
    		} else {
    			echo apiJson(-1, "内部错误！");
    		}
    	} else {
    		echo apiJson(-2, "未登录！");
    	}
    }
    
    /**
     * 获取“我的发布”
     */
    public function myPublish() {
    	$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    	$pageSize = isset($_GET['page_size']) ? intval($_GET['page_size']) : 10;
    	
    	$publishs = $this->model->getMyPublishArticlesWithAll($page, $pageSize);
    	if($publishs) {
    		echo apiJson(0, null, [
    				'my_publish' => $publishs, 
    				'page' => $page,
    				'page_size' => $pageSize
    		]);
    	} else {
    		echo apiJson(-1, "获取异常");
    	}
    }
    
    /**
     * 获取“我的关注”
     */
    public function mySubscribe() {
    	
    	if(!isset($_GET['latitude']) || !isset($_GET['longitude'])) {
    		echo apiJson(-1, "参数异常");
    		return;
    	}
    	
    	$latitude = floatval($_GET['latitude']); //我的经度
    	$longitude = floatval($_GET['longitude']); //我的纬度
    	
    	$subscribes = $this->model->getMySubscribeWithAll($latitude, $longitude);
    	if($subscribes) {
    		echo apiJson(0, null, ['my_subscribe' => $subscribes]);
    	} else {
    		echo apiJson(-1, "获取异常");
    	}
    }
    

}