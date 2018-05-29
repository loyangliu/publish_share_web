<?php

namespace tools\WX;

use GuzzleHttp\Client;

require_once WEBROOT_PATH . '/tools/WX/wxBizDataCrypt/wxBizDataCrypt.php';

class WX {

    private $AppID = 'wxbca0fbd15a965bdf';
    private $AppSecret = '92b4d56fcc68fd859580419d2604adcf';
    private $authorizationUrl = 'https://api.weixin.qq.com/sns/jscode2session';

    public function __construct()
    {
        $this->http = new Client;
    }

    /**
     * 认证用户
     * @param $code
     * @return bool|array
     */
    public function authorization($code)
    {
        $response = $this->http->post($this->authorizationUrl, [
            'form_params' => [
                'appid' => $this->AppID,
                'secret' => $this->AppSecret,
                'js_code' => $code,
                'grant_type' => 'authorization_code'
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if(!$data['session_key'] || !$data['openid']){
            return false;
        }

        return $data;
    }

    /**
     * 解密用户微信信息
     * @param $sessionKey
     * @param $encryptedData
     * @param $iv
     * @return bool|array
     */
    public function decryptData($sessionKey, $encryptedData, $iv)
    {
        $pc = new \WXBizDataCrypt($this->AppID, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        if($errCode != 0){
            return false;
        }
        return json_decode($data, JSON_UNESCAPED_UNICODE);
    }
}
