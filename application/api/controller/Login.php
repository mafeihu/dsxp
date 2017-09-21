<?php
namespace app\api\controller;
use lib\Live;
use think\Controller;
use think\View;
use think\Db;
use opensearch;
use \think\Session;
use \think\Request;

class Login extends Common
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    private $appid="wx0bb583c2e89057d8";
    private $appsecret="0ee7ada595ff7197c5e79a62f1b22703";
    /*
     *获取微信授权登录的二维码
     */
    public function wx_authorization(){
        $redirect_uri ='http%3a%2f%2fdspx.tstmobile.com%2fapi%2flogin%2fwx_callback';
        //https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf45a25a21d57b0b5&redirect_uri=" + wxUrl + "&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        header("Location: ".$url);
    }
    //微信回调地址(获取用户的相关信息)
    public function wx_callback ()
    {
        //获取code
        $code = ($_GET["code"]);
        //通过code获取access_token
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->appid . "&secret=" . $this->appsecret . "&code=" . $code . "&grant_type=authorization_code";
        $result = curl_get($url);
        $arr = json_decode($result, true);
        $url1 = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=" . $this->appid . "&grant_type=refresh_token&refresh_token=" . $arr['refresh_token'];
        $arr1 = curl_get($url1);
        $arr1 = json_decode($arr1, true);
        $url2 = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $arr1['access_token'] . "&openid=" . $this->appid . "&lang=zh_CN";
        $arr2 = curl_get($url2);
        $openid = $arr['openid'];
        if (!empty($openid)) {
            $check = DB::name("user")->where(["openid" => $openid])->find();
            if (!$check) {
                $arr2 = json_decode($arr2, true);
                $data['openid'] = $openid;
                $data['token'] = uniqid();
                $data['intime'] = date("Y-m-d H:i:s", time());
                $data['username'] = $arr2['nickname'];
                $data['img'] = $arr2['headimgurl'];
                $data['sex'] = $arr2['sex'];
                $data['province'] = $arr2['province'];
                $data['city'] = $arr2['city'];
                $result = DB::name("user")->insert($data);
                $this->api_return("201","登录成功");
            } else {
                $this->api_return("201","登录成功");
            }
        }else{
            $this->api_return("404","授权失败");
        }
    }
    public function test(){
        $live = new Live();
        $live->test();
    }

}
