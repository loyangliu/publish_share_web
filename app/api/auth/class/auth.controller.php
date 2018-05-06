<?php

class AuthController extends AppController
{
	public function loginCheck() {
		return true;
	}
	
	// 登录态检查
	public function check() {
		$api_token = addslashes($_REQUEST['api_token']);
		if($api_token){
			$user = $this->model->db->getRow("select * from users where api_token='{$api_token}'");
			if($user){
				echo apiJson(0, '已经认证', ['user' => $user]);
				return;
			}
		}
		
		echo apiJson(1000, '未认证');
	}
	
	// 根据code转换用户信息
    public function login() {
        $data = array_map(function($v){
            return addslashes(trim($v));
        }, $_POST);

        // 认证
        $user = $this->model->authorizationWX($data);

        if(!$user){
            echo apiJson(1, '登录失败！');
            return;
        }

        echo apiJson(0, '登录成功', ['user' => $user]);
    }
}