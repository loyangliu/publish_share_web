<?php

class ArticlesController extends AppController
{
    /**
     * 业务侧根据需要重载自定义登录态验证
     */
    public function loginCheck() {
    	$needCheckActions =  ['publish', 'uploadImage'];
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
     * 获取帖子
     */
    public function home()
    {
    	$startId = intval($_GET['offsetId']);// 帖子开始id，防止因数据库新增数据，引起页码偏移，导致重复加载数据
    	$page = intval($_GET['page']);// 页
    	$articles = $this->model->getHomeArticlesWithAll($startId, $page);
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

        if(!$params['name']){
            echo apiJson(-1, '名称不能为空！');return;
        }

        if(!$params['description']){
            echo apiJson(-1, '描述不能为空！');return;
        }
        
        if(!$params['userid']){
            echo apiJson(-1, 'user_id不能为空！');return;
        }

        $data = [
            'userid' => $params['userid'],
            'name' => $params['name'],
            'description' => $params['description'],
            'images' => json_decode($_POST['images'])
        ];

        $this->model->publish($data);

        echo apiJson(0, '发布成功！');
    }

    
}
