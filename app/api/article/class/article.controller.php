<?php

class ArticleController extends AppController
{
    protected $apiAuthentication = true;

    public function __construct()
    {
        parent::__construct();

        if(!in_array($this->ruler->action, ['index'])){
            $this->model->apiAuthentication();
        }
    }

    public function loginCheck()
    {
        return true;
    }

    public function index()
    {
        var_dump('index');
    }

    /**
     * 发布
     */
    public function publish()
    {
        
    }
}