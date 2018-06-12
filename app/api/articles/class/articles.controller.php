<?php

class ArticlesController extends AppController
{
    /**
     * 业务侧根据需要重载自定义登录态验证
     */
    public function loginCheck() {
    	$needCheckActions =  ['publish', 'uploadImage', 'subscribe'];
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
     * 获取帖子
     */
    public function home()
    {
    	$userId = intval($_GET['userid']);
    	$startId = intval($_GET['offsetId']);// 帖子开始id，防止因数据库新增数据，引起页码偏移，导致重复加载数据
    	$page = intval($_GET['page']);// 页
    	$latitude = floatval($_GET['latitude']); //我的经度
    	$longitude = floatval($_GET['longitude']); //我的纬度
    	$articles = $this->model->getHomeArticlesWithAll($userId, $latitude, $longitude, $startId, $page);
    	echo apiJson(0, null, ['articles' => $articles]);
    }
    
    /**
     * 附件的帖子
     */
    public function nearby() {
    	$userId = intval($_GET['userid']);
    	$page = intval($_GET['page']);
    	$pageSize = intval($_GET['pagesize']);
    	$curserid = intval($_GET['curserid']);
    	$latitude = floatval($_GET['latitude']); //我的经度
    	$longitude = floatval($_GET['longitude']); //我的纬度
    	
    	$articles = $this->model->getNearbyArticlesWithAll($userId, $page, $pageSize, $curserid, $latitude, $longitude);
    	echo apiJson(0, null, ['articles' => $articles]);
    }
    
    /**
     * 指定的帖子
     */
    public function detail() {
    	$userId = intval($_GET['userid']);
    	$articleId = intval($_GET['articleid']);
    	$latitude = floatval($_GET['latitude']); //我的经度
    	$longitude = floatval($_GET['longitude']); //我的纬度
    	
    	$articles = $this->model->getCertainArticle($userId, $articleId, $latitude, $longitude);
    	echo apiJson(0, null, ['articles' => $articles]);
    }


    /**
     * 上传图片
     */
    public function uploadImage()
    {
        // 创建存储目录
        $dirRoot = WEBROOT_PATH . '/storage/article_images';
        $parentDir =  date('Ymd');
        $dir = $dirRoot . '/' . $parentDir;
        is_dir($dir) or mkdir($dir);

        $file = new \Upload\File('image', new \Upload\Storage\FileSystem($dir));
        $new_filename = uniqid();
        $file->setName($new_filename);

        // 添加文件验证规则
        $file->addValidations(array(
            new \Upload\Validation\Mimetype(['image/jpeg', 'image/png', 'image/gif', 'image/bmp']),
            new \Upload\Validation\Size('5M'),
            new \Upload\Validation\Extension(['jpeg', 'png', 'jpg', 'gif', 'bmp']),
        ));

        // 验证规则
        if(!$file->validate()){
            echo apiJson(1, '上传失败！');
            return;
        }

        // 上传
        $file->upload();

        $path = $parentDir . '/' . $file->getNameWithExtension();

        // 生成缩略图
        $this->model->makeThumbnail($path);

        echo apiJson(0, '上传成功！', ['file' => $path]);
    }

    /**
     * 发布
     */
    public function publish()
    {
        $params = array_map(function($v){
            return addslashes(trim($v));
        }, $_POST);

        if(!$params['description']){
            echo apiJson(-1, '描述不能为空！');return;
        }
        
        if(!$params['userid']){
            echo apiJson(-1, 'user_id不能为空！');return;
        }

        $data = [
            'userid' => $params['userid'],
            'description' => $params['description'],
        	'telphone' => isset($params['telphone'])?$params['telphone']:null,
        	'location' => isset($params['location'])?$params['location']:null,
        	'location_name' => isset($params['location_name'])?$params['location_name']:null,
        	'location_latitude' => isset($params['location_latitude'])?$params['location_latitude']:null,
        	'location_longitude' => isset($params['location_longitude'])?$params['location_longitude']:null,
            'images' => json_decode($_POST['images'])
        ];

        $this->model->publish($data);

        echo apiJson(0, '发布成功！');
    }

    /**
     * 关注帖子
     */
    public function subscribe()
    {
    	$articleId = isset($_POST['article_id']) ? intval($_POST['article_id']) : null;
        if(!$articleId || !$article = $this->model->find($articleId)){
            echo apiJson(-1, '帖子被删除了！');
            return;
        }
        
        $telphone = isset($_POST['telphone']) ? $_POST['telphone'] : '';
        $message = isset($_POST['message']) ? $_POST['message'] : '';

        $ret = $this->model->subscribe($this->user['id'], $this->user['wx_nick_name'], $articleId, $telphone, $message);
        if($ret) {
        	echo apiJson(0, '已关注！');
        } else {
        	echo apiJson(-2, '关注失败！');
        }
    }

    /**
     * 取消关注
     */
    public function cancenSubscribe()
    {
        $articleId = intval($_POST['article_id']);
        if(!$articleId || !$article = $this->model->find($articleId)){
            echo apiJson(-1, '帖子被删除了！');
            return;
        }

        // 已关注
        if(!$this->model->getUserSubscribe($this->user['id'], $articleId)){
            echo apiJson(0, '尚未关注！');
            return;
        }

        $this->model->cancelSubscribe($this->user['id'], $articleId);
        echo apiJson(0);
    }
    
    /**
     * 获取是否有“新消息”
     * 新消息定义：在最近3个月内，发表的文章有被关注，或被回复，或者自己留言被回复。
     * $now = \Carbon\Carbon::now();
    	$pastMonthDate = $now->subMonths(1)->toDateTimeString();
     */
    public function fetchNewMessages()  {
    	$userId = intval($_GET['userid']);
    	
    	$msgList = $this->model->fetchNewMessages($userId);
    	echo apiJson(0, null, ["newmsg"=>$msgList]);
    }
}
