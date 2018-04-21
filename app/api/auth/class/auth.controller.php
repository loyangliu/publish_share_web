<?php

class AuthController extends AppController
{
    public function login()
    {
        $data = array_map(function($v){
            return addslashes(trim($v));
        }, $_POST);

        // 认证
        $user = $this->model->authorizationWX($data);

        // 认证
        if(!$user){
            echo apiJson(1, '登录失败！');
            return;
        }

        echo apiJson(0, '登录成功！', ['user' => $user]);
    }
}