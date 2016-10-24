<?php
namespace app\wap\controller;
use app\wap\controller\Wap;


class Error extends Wap
{
    public function index()
    {
        //根据当前控制器名来判断要执行那个城市的操作
        //$cityName = Request::instance()->controller();
       
        return $this->city($cityName);
    }

    //注意 city方法 本身是 protected 方法
    protected function city($name)
    {
        //和$name这个城市相关的处理
         return '';
    }
    public function aaa(){
       
    }
    public function bb(){
       /* $options=['appid'=>$this->appid,"appsecret"=>$this->appsecret];
      $Wechat=new WechatApi($options);
        $oauth2_url=$Wechat->getOauthRedirect(get_url());
          if(!empty($_GET['code'])){
            $code = $_GET['code'];
            $openid_access_token = $Wechat->getOauthAccessToken($code);
            $openid = $openid_access_token['openid'];
            $access_token = $openid_access_token['access_token'];
           // echo $access_token.' '.$openid;exit;
            if(!empty($openid) && !empty($access_token)){
                $oauth2_snsapi_userinfo = $Wechat->getWechatUserInfo($this->access_token,$openid);
                if($oauth2_snsapi_userinfo && isset($oauth2_snsapi_userinfo['openid'])){
                    $nickname = filter_emoji($oauth2_snsapi_userinfo['nickname']);
                    echo $nickname;exit;
                    $headimgurl = ($oauth2_snsapi_userinfo['headimgurl']);
                    file_put_contents('text2332.log',$nickname);
                    $parent_id=isset($_GET['uid'])?intval($_GET['uid']):0;
                        //注册微信到表
                        $wechat_info['openid']=$openid;

                        $wechat_info['nickname1']=$nickname;
                        $wechat_info['headimgurl']=$headimgurl;
                        $wechat_info['city']=$oauth2_snsapi_userinfo['city'];
                        $wechat_info['province']=$oauth2_snsapi_userinfo['province'];
                        $wechat_info['unionid']=isset($oauth2_snsapi_userinfo['unionid'])?$oauth2_snsapi_userinfo['unionid']:'';
                        $wechat_info['create_time']=time();
                        $wechat_info['scene_id']=$parent_id;
                        Db::name("wechat_user")->insert($wechat_info);
                }
            }
        }else{
             header('location:'.$oauth2_url);
                      exit;
        }*/
    }
}