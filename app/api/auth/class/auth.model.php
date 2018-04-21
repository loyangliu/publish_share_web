<?php

class AuthModel extends AppModel
{
    public function __construct()
    {
        parent::__construct();

        $this->wx = new \tools\WX\WX;
    }

    public function authorizationWX($data)
    {
        // 获取微信认证
        $authorization = $this->wx->authorization($data['code']);

        if(!$authorization){
            return false;
        }

        // 解密用户微信信息
        $data = $this->wx->decryptData($authorization['session_key'], $data['encryptedData'], $data['iv']);
        if(!$data){
            return false;
        }

        // 创建用户或更新
        $this->refreshUser(array_merge($authorization, $data));

        // 刷新api token
        $this->refreshApiToken($authorization);

        return $this->getUserByOpenId($authorization['openid']);
    }

    /**
     * 刷新token
     * @param $authorization
     */
    public function refreshApiToken($authorization)
    {
        $apiToken = $this->genRefreshApiToken($authorization);
        $this->db->update([
            'api_token' => $apiToken,
            'api_token_refresh_at' => date('Y-m-d H:i:s')
        ], 'users', "wx_openid='{$authorization['openid']}'");
    }

    /**
     * 生成api token
     * @param $authorization
     * @return string
     */
    public function genRefreshApiToken($authorization)
    {
        return md5($authorization['openid'] . uniqid() . $authorization['session_key']) . uniqid();
    }

    /**
     * 获取用户
     * @param $openId
     * @return array
     */
    public function getUserByOpenId($openId)
    {
        return $this->db->getRow("select * from users where wx_openid='{$openId}'");
    }

    /**
     * 微信用户是否已存在
     * @param $openId
     * @return bool
     */
    public function existsUserByOpenId($openId)
    {
        return !!$this->getUserByOpenId($openId);
    }

    /**
     * 用户存在则更新，不存在则创建
     * @param $data
     */
    public function refreshUser($data)
    {
        $this->existsUserByOpenId($data['openid']) ? $this->updateUser($data) : $this->createUser($data);
    }

    /**
     * 创建用户
     * @param $data
     */
    public function createUser($data)
    {
        $row = [
            'register_at' => date('Y-m-d H:i:s'),
            'wx_openid' => $data['openid'],
            'wx_session_key' => $data['session_key'],
            'wx_nick_name' => $data['nickName'],
            'wx_avatar_url' => $data['avatarUrl'],
            'wx_gender' => $data['gender'],
            'wx_city' => $data['city'],
            'wx_province' => $data['province'],
            'wx_country' => $data['country'],
            'wx_language' => $data['language'],
        ];
        $this->db->insert($row, 'users');
    }

    /**
     * 更新用户
     * @param $data
     */
    public function updateUser($data)
    {
        $row = [
            'wx_session_key' => $data['session_key'],
            'wx_nick_name' => $data['nickName'],
            'wx_avatar_url' => $data['avatarUrl'],
            'wx_gender' => $data['gender'],
            'wx_city' => $data['city'],
            'wx_province' => $data['province'],
            'wx_country' => $data['country'],
            'wx_language' => $data['language'],
        ];
        $this->db->update($row, 'users', "wx_openid='{$data['openid']}'");
    }
}