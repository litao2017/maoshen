<?php
namespace app\wap\model;
use think\Model;
use think\Db;
use think\WechatApi;
class Wechat extends Model
{
    public $shop_config;
    public $appid;
    public $appsecret;
    public $access_token;
    public function __construct($shop_config){
      $this->shop_config=$shop_config;
      $this->appid=$this->shop_config['appid'];
      $this->appsecret=$this->shop_config['appsecret'];
      $this->access_token=get_access_token($shop_config);
   }
    public function login(){
      $options=['appid'=>$this->appid,"appsecret"=>$this->appsecret];
      $Wechat=new WechatApi($options);
      $oauth2_url=$Wechat->getOauthRedirect(get_url());
      $user_info=cookie('user_info');
      $user_id=isset($user_info['user_id'])?$user_info['user_id']:0;
      if(!empty($user_id)){
        $wechat_user_info = Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->where("u.user_id = ".$user_id)->find();

        if(empty($wechat_user_info)){
         
          cookie('user_info',null);
           header('location:'.$oauth2_url);
            exit;
        }else{
          return $wechat_user_info;
        }
        
      }else{
        $code = input('code');
        if(!empty($code)){
            
            $openid_access_token = $Wechat->getOauthAccessToken($code);
            $openid = $openid_access_token['openid'];
            $access_token = $openid_access_token['access_token'];
            if(!empty($openid) && !empty($access_token)){
                $oauth2_snsapi_userinfo = $Wechat->getUserInfo($access_token,$openid);
                           

                if($oauth2_snsapi_userinfo && isset($oauth2_snsapi_userinfo['openid'])){
                    $nickname = filter_emoji($oauth2_snsapi_userinfo['nickname']);
                   // file_put_contents('text2332',$nickname);
                    $headimgurl = $oauth2_snsapi_userinfo['headimgurl'];

                 /*   session('openid',$openid);
                    session('nickname',$nickname);
                    session('headimgurl',$headimgurl);*/

                     $wechat_user_info = Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->where("openid = '".$openid."'")->find();

                     if(!$wechat_user_info){

                     $parent_id=isset($_GET['uid'])?intval($_GET['uid']):0;
                        //注册微信到表
                        $wechat_info['openid']=$openid;

                        $wechat_info['nickname']=$nickname;
                        $wechat_info['headimgurl']=$headimgurl;
                        $wechat_info['city']=$oauth2_snsapi_userinfo['city'];
                        $wechat_info['province']=$oauth2_snsapi_userinfo['province'];
                        $wechat_info['unionid']=isset($oauth2_snsapi_userinfo['unionid'])?$oauth2_snsapi_userinfo['unionid']:'';
                        $wechat_info['create_time']=time();
                        $wechat_info['scene_id']=$parent_id;
                        Db::name("wechat_user")->insert($wechat_info);
                        $wechat_user_id=Db::getLastInsID();
                        //注册用户到表
                        $user_info['wechat_user_id']=$wechat_user_id;
                        $user_info['create_time']=$user_info['last_login_time']=time();
                        $user_info['sex']=$oauth2_snsapi_userinfo['sex'];
                        $user_info['user_name']=$this->shop_config['user_start']+$wechat_user_id;
                        $user_info['ip']=get_ip();
                        //判断url是否有上级id
                        $user_info['parent_id']=$parent_id;
                        if($user_info['parent_id']){

                          $parent_info=Db::name('user')->field('parent_id,parent2_id')->find($user_info['parent_id']);
                          $user_info['parent2_id']=$parent_info['parent_id'];
                          $user_info['parent3_id']=$parent_info['parent2_id'];
                           //推送消息
                           
                        }
                         Db::name("user")->insert($user_info);
                         $user_id=Db::getLastInsID();
                       /*  session('user_id',$user_id);
                         session('user_name', $user_info['user_name']);
                         session('user_level',0);*/
                         cookie('user_info',["user_id"=>$user_id,"user_name"=>$user_info['user_name'],"openid"=>$openid,"nickname"=>$nickname,"headimgurl"=>$headimgurl]);
                          return $this->get_user_info($user_id);
                     }else{
                        //登录
                        cookie('user_info',["user_id"=>$wechat_user_info['user_id'],"user_name"=>$wechat_user_info['user_name'],"openid"=>$openid,"nickname"=>$nickname,"headimgurl"=>$headimgurl]);

                      /*  session('user_id',$wechat_user_info['user_id']);
                        session('user_name',$wechat_user_info ['user_name']);
                        session('user_level',$wechat_user_info['user_level']);*/
                        return $wechat_user_info;
                         
                     }
                    
          
                  }else{
                      header('location:'.$oauth2_url);
                      exit;
                  }
              }else{
                  header('location:'.$oauth2_url);
                  exit;
              }
          }else{
              header('location:'.$oauth2_url);
              exit;
          }
      }

      return false;
    }
    public function get_shop_config(){
       return $this->shop_config;
    }
    public function get_user_info($user_id){
      $wechat_user_info = Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->where("u.user_id = {$user_id}")->find();
       return $wechat_user_info;
    }

}