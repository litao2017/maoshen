<?php
namespace app\wap\controller;
use app\wap\controller\Wap;
class Wechat extends Wap
{
    public $user_id;
    public $appid;
    public $nickname;
    public $headimgurl;
    public $user_name;
    public $user_info;
    public function _initialize()
    {
        parent::_initialize();
        $Wechat=new \app\wap\model\Wechat($this->shop_config);
        $user_info=cookie("user_info");

        $user_id=isset($user_info["user_id"])?$user_info["user_id"]:0;
        $openid=isset($user_info["openid"])?$user_info["openid"]:'';
        $user_name=isset($user_info["user_name"])?$user_info["user_name"]:'';
        $nickname=isset($user_info["nickname"])?$user_info["nickname"]:'';
        $headimgurl=isset($user_info["headimgurl"])?$user_info["headimgurl"]:'';
        $User=new \app\wap\model\User();
        $user_info=$User->get_user_info($user_id,1);
        //lt：如果没有页面授权，则进行授权。如果有直接获取cookie内容
        if(empty($user_id) || empty($openid) || empty($user_info)){
            $wechat_user_info=$Wechat->login();
            $this->user_id=$wechat_user_info['user_id'];
            $this->openid=$wechat_user_info['openid'];
            $this->user_name=$wechat_user_info['user_name'];
            $this->nickname=$wechat_user_info['nickname'];
            $this->headimgurl=$wechat_user_info['headimgurl'];
            $this->user_info=$wechat_user_info;
        }else{
            $this->user_id=$user_id;
            $this->openid=$openid;
            $this->user_name=$user_name;
            $this->nickname=$nickname;
            $this->headimgurl=$headimgurl;
            $this->user_info=$user_info;
        }
        //end
       

    }
    public function index()
    {
        return 'error';
    }
}
