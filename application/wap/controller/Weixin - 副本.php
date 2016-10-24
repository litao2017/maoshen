<?php
namespace app\wap\controller;
use app\wap\controller\Wap;
use think\Db;
class Weixin extends Wap
{
  public function index(){
        //获得参数 signature nonce token timestamp echostr
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
             $echostr   = $_GET['echostr'];   
    $token = $this->shop_config['token'];
    $tmpArr = array($token, $timestamp, $nonce);
    sort($tmpArr, SORT_STRING);
    $tmpStr = implode( $tmpArr );

        if( $tmpStr  == $signature && $echostr ){
            //第一次接入weixin api接口的时候
        
             echo  $echostr;
            exit;
        }else{
          // $this->reponse_msg();
          
             // echo 'as';
        }
    }

    public function reponse_msg(){

  
    $postObj = simplexml_load_string( $postArr );
    //回复用户消息(纯文本格式) 
        $toUser   = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
          $time     = time();
        $msgType  =  'text';
        $openid=$toUser;
    $WechatApi=new \think\WechatApi();

    //判断该数据包是否是订阅的事件推送
    if( strtolower( $postObj->MsgType) == 'event'){
      //如果是关注 subscribe 事件
      if( strtolower($postObj->Event == 'subscribe') ){

        if (strlen($postObj->EventKey) == 0){
          $scene_id=0;
        }else{
          //带参数二维码新用户关注
          $scene_id_arr = explode("qrscene_", $qrscene);
          $scene_id = $scene_id_arr[1];
        }
        //注册微信到表
        
        $access_token=get_access_token($this->shop_config);
        $wxuser_info=$WechatApi->getUserInfo($access_token,$appid);
        $wechat_info['openid']=$openid;
        $wechat_info['nickname']=$wxuser_info['nickname'];
        $wechat_info['headimgurl']=$wxuser_info['headimgurl'];
        $wechat_info['city']=$wxuser_info['city'];
        $wechat_info['province']=$wxuser_info['province'];
        $wechat_info['unionid']=isset($wxuser_info['unionid'])?$wxuser_info['unionid']:'';
        $wechat_info['create_time']=$time;
        $wechat_info['scene_id']=$scene_id;
        Db::name("wechat_user")->insert($wechat_info);
        $wechat_user_id=Db::getLastInsID();
        //注册用户到表
        $user_info['wechat_user_id']=$wechat_user_id;
        $user_info['create_time']=$user_info['last_login_time']=$time;
        $user_info['sex']=$wxuser_info['sex'];
        $user_info['user_name']=$this->shop_config['user_start']+$wechat_user_id;
        $user_info['ip']=get_ip();
        //判断url是否有上级id
        
        if($scene_id){
          $user_info['parent_id']=$scene_id;
          $parent_info=Db::name('user')->field('parent_id,parent2_id')->find($scene_id);
          $user_info['parent2_id']=$parent_info['parent_id'];
          $user_info['parent3_id']=$parent_info['parent2_id'];
           //推送消息
           
        }
        Db::name("user")->insert($user_info);
       $user_id=Db::getLastInsID();
       //lt:向上级发送模版消息
       if($user_id && $scene_id){
          //$WechatApi->sendTemplateMessage();
       }
       //end
       session('user_id',$user_id);
       session('wechat_user_id',$wechat_user_id);
       session('user_name', $user_info['user_name']);
        
        if(isset($scene_id)){
        $content  = '您由'.$parent_info.'推荐';
      }else{
        $content  = '欢迎关注我们的微信公众账号'.$postObj->FromUserName.'-'.$postObj->ToUserName;
      }
        $template = $WechatApi->getTpl($msgType);
        $info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
        echo $info;

      
      }
      /*-------------------已关注用户扫二维码------------------------*/
      if( strtolower( $postObj->MsgType) == 'scan'){

      $content='您已经关注过了';
        if (strlen($postObj->EventKey) == 0){
          $scene_id=0;
        }else{
          //带参数二维码新用户关注
          $scene_id_arr = explode("qrscene_", $qrscene);
          $scene_id = $scene_id_arr[1];
        }
        if($scene_id){
             $wxuser_info=Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->field('w.scene_id,u.user_id')->where("openid='".$openid."'")->find();
            if($wxuser_info['scene_id']){
              //已经有上级
              $content='您已经有了上级，上级名称请在会员中心查看';
            }else{
              //如果有下级不能绑定
              $child=Db::name('user')->where('parent_id='.$wxuser_info['user_id'])->find();
              if($child){
                $content='您已经有了下级，不能绑定上级';
              }else{
                 //重新绑定
                 //找上级的1、2级
                 $parent_info=Db::name('user')->find($scene_id);
                Db::name('wechat_user')->where("wechat_user_id=".$wxuser_info['wechat_user_id'])->update(['scene_id'=>$scene_id]);
                Db::name('user')->where("user_id=".$wxuser_info['user_id'])->update(['parent_id'=>$scene_id,'parent2_id'=>$parent_info['parent2_id'],'parent3_id'=>$parent_info['parent3_id']]);
                $content='恭喜您绑定成功，您现在的上级会员号是'.$parent_info['user_name'];
              }
             
            }
        }
     
         $template = $WechatApi->getTpl('text');
         
         $info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
     }
    }
    if( strtolower($postObj->MsgType) == 'text'){
      //如果用户回复消息
      //
      
    }
   }
 
 
}