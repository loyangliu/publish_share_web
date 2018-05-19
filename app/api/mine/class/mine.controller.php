<?php
class MineController extends AppController {
	/**
     * 业务侧根据需要重载自定义登录态验证
     */
    public function loginCheck() {
    	$needCheckActions =  ['myPublish'];
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
     * 获取“我的发布”
     */
    public function myPublish() {
    	$curserId = intval($_GET['offsetId']);// 帖子开始id，防止因数据库新增数据，引起页码偏移，导致重复加载数据
    	$page = intval($_GET['page']);// 页
    	$articles = $this->model->getMyPublishArticlesWithAll($curserId, $page);
    	echo apiJson(0, null, ['articles' => $articles]);
    }
}