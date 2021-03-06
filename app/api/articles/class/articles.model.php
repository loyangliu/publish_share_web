<?php

use Location\Coordinate;
use Location\Distance\Vincenty;

class ArticlesModel extends AppModel
{
	const STATIC_RES_URL = "https://www.loyangliu.com";

	/**
	 * 重载init，初始化数据库
	 */
	protected function init() {
		$this->db = DbFactory::instance()->createDBO('publish_share');
	}
	
	private function transformDate($time) {
		$time = new \Carbon\Carbon($time);
		
		return $time->diffForHumans();
	}
	
	private function calDistance($latitude1, $longitude1, $latitude2, $longitude2) {
		$coordinate1 = new Coordinate($latitude1, $longitude1); // Mauna Kea Summit
		$coordinate2 = new Coordinate($latitude2, $longitude2); // Haleakala Summit
		
		$calculator = new Vincenty();
		
		return $calculator->getDistance($coordinate1, $coordinate2); // returns xxx (meters; ≈128 kilometers)
	}
	
    /**
     * 发布帖子
     * @param $data
     */
    public function publish($data)
    {
        $time = date('Y-m-d H:i:s');

        $row = [
            'description' => $data['description'],
        	'telphone' => $data['telphone'],
        	'location' => $data['location'],
        	'location_name' => $data['location_name'],
        	'location_latitude' => $data['location_latitude'],
        	'location_longitude' => $data['location_longitude'],
            'publish_at' => $time,
            'create_at' => $time,
            'update_at' => $time,
            'delete_at' => null,
            'user_id' => $data['userid'],
        ];

        $this->db->autoCommit(false);

        // 插入帖子
        $id = $this->create($row);

        // 插入帖子图片
        $this->createImages($id, $data['images']);

        $this->db->commit();
    }

    /**
     * 创建帖子
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        $this->db->insert($data, 'articles');
        return $this->db->lastId();
    }

    /**
     * 保存帖子图片
     * @param $id
     * @param $images
     */
    public function createImages($id, $images)
    {
        $data = [];

        $dir = $this->getImageRootDir();

        $seq = -1;
        foreach($images as $image){
            $seq++;

            $fullPath = $dir . '/' . $image;

            if(!file_exists($fullPath)){
                continue;
            }

            $data[] = [
                'article_id' => $id,
                'image_path' => $image,
                'seq' => $seq
            ];
        }

        if($data){
            $this->db->insertAll($data, 'article_images');
        }
    }

    /**
     * 获取缩略图路径
     * @return string
     */
    public function getThumbnailImageRootDir()
    {
        return WEBROOT_PATH . '/storage/article_thumbnail_images';
    }

    /**
     * 获取首页文章列表
     * @param $offsetId
     * @param $page
     * @param int $rowSize
     * @return array
     */
    public function getHomeArticles($latitude, $longitude, $offsetId, $page, $rowSize = 5)
    {
        if(!$offsetId){
            $offsetId = $this->getMaxId();
        }

        $map = [
            'delete_at is null',
            "id<={$offsetId}"
        ];

        if(!$page){
            $page = 1;
        }
 
        $offset = $rowSize  * ($page - 1);
        $limit = "{$offset},{$rowSize}";

        $where = $map ? ' where ' . implode(' and ', $map) : '';

        $data = $this->getArticles($where, $limit, 'order by publish_at');
        if($data) {
        	foreach ($data as & $item) {
        		if($item['location'] != '' && $item['location'] != null) {
        			$distance = $this->calDistance($latitude, $longitude, floatval($item['location_latitude']), floatval($item['location_longitude']));
        			$item['distance'] = $distance;
        		}
        	}
        }

        return [
            'data' => $data,
            'page' => $page,
            'rowSize' => $rowSize,
            'offsetId' => $offsetId
        ];
    }
    
    /**
     * 获取附件的文章列表
     * 最多 30 条 & 一个月以内的发帖 & 距离不超过200公里
     */
    public function getNearbyArticles($latitude, $longitude) {
    	$now = \Carbon\Carbon::now();
    	$pastMonthDate = $now->subMonths(1)->toDateTimeString();
    	
    	$where = " where publish_at > {$pastMonthDate}";
    	$articleInMonth = $this->db->getAll("select * from articles where publish_at > '{$pastMonthDate}' order by publish_at desc");
    	
    	$counter = 0;
    	$data = [];
    	if($articleInMonth) {
    		for($index=0; $index < count($articleInMonth); $index++) {
    			if($counter < 30) {
    				$distance = $this->calDistance($latitude, $longitude, floatval($articleInMonth[$index]['location_latitude']), floatval($articleInMonth[$index]['location_longitude']));
    				if($distance <= 200000) {
    					$articleInMonth[$index]['distance'] = $distance;
    					$data[] = $articleInMonth[$index];
    				}
    			} else {
    				break;
    			}
    		}
    	}
    	
    	usort($data, function($item1, $item2){
    		if($item1['distance'] == $item2['distance']) {
    			return 0;
    		}
    		
    		return ($item1['distance'] < $item2['distance']) ? 1 : -1; 
    	});
    	
    	return [
    		'data' => $data
    	];
    }

    /**
     * 获取文章列表
     * @param $where
     * @param $limit
     * @param $orderBy
     * @return mixed
     */
    public function getArticles($where, $limit, $orderBy)
    {
        return $this->db->getAll("select id,description,telphone,location,location_name,location_latitude,location_longitude,publish_at,user_id from articles{$where} {$orderBy} desc", $limit);
    }

    /**
     * 获取文章最大id
     * @return int
     */
    public function getMaxId()
    {
        return $this->db->getOne('select max(id) from articles') * 1;
    }

    /**
     * 获取文章，并加载或转换文章相关信息
     * @param $offsetId
     * @param $page
     * @param int $rowSize
     * @return array
     */
    public function getHomeArticlesWithAll($userid, $latitude, $longitude, $offsetId, $page, $rowSize = 5) {
    	$articles = $this->getHomeArticles($latitude, $longitude, $offsetId, $page, $rowSize);

        // 加载发布者信息
        $this->articlesWithUser($articles['data']);

        // 格式化发布时间
        $this->articlesTransformPublishAt($articles['data']);

        // 加载图片
        $this->articlesWithImage($articles['data']);
        
        // 加载留言
        $this->articlesWithComments($articles['data']);

        // 是否关注
        $this->articlesWithSubscribe($userid, $articles['data']);
        
        // 加载关注列表
        $this->getArticlesWithSubsribers($articles['data']);

        // 加载点赞
        $this->articleWithStars($articles['data']);

        return $articles;
    }
    
    /**
     * 获取附件的文章列表
     * 最多 30 条 & 一个月以内的发帖 & 距离不超过200公里
     */
    public function getNearbyArticlesWithAll($userid, $latitude, $longitude) {
    	$articles = $this->getNearbyArticles($latitude, $longitude);
    	
    	// 加载发布者信息
    	$this->articlesWithUser($articles['data']);
    	
    	// 格式化发布时间
    	$this->articlesTransformPublishAt($articles['data']);
    	
    	// 加载图片
    	$this->articlesWithImage($articles['data']);
    	
    	// 加载留言
    	$this->articlesWithComments($articles['data']);
    	
    	// 是否关注
    	$this->articlesWithSubscribe($userid, $articles['data']);
    	
    	// 加载关注列表
    	$this->getArticlesWithSubsribers($articles['data']);
    	
    	// 加载点赞
    	$this->articleWithStars($articles['data']);
    	
    	return $articles;
    }

    /**
     * 加载帖子发布者信息
     * @param $articles
     */
    public function articlesWithUser(& $articles)
    {
        $userIds = array_filter(array_unique(array_column($articles, 'user_id')));
        $users = $userIds ? $this->db->getAll('select id,wx_nick_name,wx_avatar_url from users where id in (' . implode(',', $userIds) . ')', null, 1, 'id') : [];
        foreach($articles as & $article){
            $article['user'] = isset($users[$article['user_id']]) ? $users[$article['user_id']] : null;
            unset($article['user_id']);
        }
    }

    /**
     * 转换帖子时间
     * @param $articles
     */
    public function articlesTransformPublishAt(& $articles)
    {
        foreach($articles as & $article){
            $time = new \Carbon\Carbon($article['publish_at']);
            $article['publish_at'] = [
                'time' => $article['publish_at'],
                'diffForHumans' => $time->diffForHumans(),
            ];
        }
    }

    public function getImageRootDir(){
        return WEBROOT_PATH . '/storage/article_images';
    }

    /**
     * 获取图片绝对路径
     * @param $path
     * @return string
     */
    public function getImageAbsPath($path)
    {
        return $this->getImageRootDir() . '/' . $path;
    }

    public function getImageDir()
    {
        return 'storage/article_images';
    }

    public function getThumbnailImageDir()
    {
        return 'storage/article_thumbnail_images';
    }

    public function getImageUrl($path)
    {
    	return self::STATIC_RES_URL.'/'. $this->getImageDir() . '/' . $path;
    }

    public function getThumbnailImageUrl($path)
    {
    	return self::STATIC_RES_URL. '/' . $this->getThumbnailImageDir() . '/' . $path;
    }

    /**
     * 加载图片
     * @param $articles
     */
    public function articlesWithImage(& $articles)
    {
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
     * 加载留言
     * @param $articles
     */
    public function articlesWithComments(& $articles) {
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
     * 加载留言
     * @param $articles
     */
    public function articleWithStars(& $articles) {
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
     * 加载用户是否关注
     */
    public function articlesWithSubscribe($userid, & $articles) {
    	if($articles){
    		$ids = array_column($articles, 'id');
    		$in = implode(',', $ids);
    		
    	    if($userid != 0) {
    	    	$subscribes = $this->db->getCol("select article_id from subscribe where user_id={$userid} and article_id in ({$in})");
    		} else {
    			$subscribes = [];
    		}
    		
    		foreach($articles as & $article){
    			$article['isSubscribe'] = in_array($article['id'], $subscribes);
    		}
    	}
    }
    
    /**
     * 加载关注人列表
     */
    public function getArticlesWithSubsribers(& $articles) {
    	if($articles) {
    		$article_ids = implode(',', array_column($articles, 'id'));
    		$subscribes = $this->db->getAll("select * from subscribe where article_id in ({$article_ids})");
    		
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
     * 生成缩略图
     * @param $path
     */
    public function makeThumbnail($path)
    {
        $img = \Intervention\Image\ImageManagerStatic::make($this->getImageAbsPath($path));
        $size = 150;
        $img->fit($size, $size);
        $savePath = $this->getThumbnailImageRootDir() . '/' . $path;
        $dirname = dirname($savePath);
        is_dir($dirname) or mkdir($dirname);
        $img->save($savePath);
    }

    /**
     * 获取一个帖子
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->db->getRow("select id from articles where id='{$id}' and delete_at is null");
    }

    /**
     * 获取关注信息
     * @param $userId
     * @param $articleId
     * @return mixed
     */
    public function getUserSubscribe($userId, $articleId)
    {
        return $this->db->getRow("select * from subscribe where user_id='{$userId}' and article_id='{$articleId}'");
    }

    /**
     * 关注帖子
     * @param $userId
     * @param $articleId
     */
    public function subscribe($userId, $userName, $articleId, $telphone, $message)
    {
    	$table = "subscribe";
    	$data = [
    			'user_id' => $userId,
    			'user_nickname' => $userName,
    			'article_id' => $articleId,
    			'telphone' => $telphone,
    			'message' => $message,
    			'subscribe_time' => date('Y-m-d H:i:s')
    	];
    	$where = "article_id={$articleId} AND user_id={$userId}";
    	
    	return $this->db->update($data, $table, $where);
    }

    /**
     * 取消关注
     * @param $userId
     * @param $articleId
     */
    public function cancelSubscribe($userId, $articleId)
    {
        $this->db->query("delete from subscribe where user_id='{$userId}' and article_id='{$articleId}'");
    }
}
