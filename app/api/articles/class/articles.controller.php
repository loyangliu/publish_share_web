<?php

class ArticlesController extends AppController
{
    // 开启认证，并忽略
    public $apiAuth = [
        'check' => true,
        'checkIgnoreActions' => ['home']
    ];

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

        $data = [
            'name' => $params['name'],
            'description' => $params['description'],
            'images' => json_decode($_POST['images'])
        ];

        $this->model->publish($data);

        echo apiJson(0, '发布成功！');
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
}