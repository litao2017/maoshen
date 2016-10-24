<?php
namespace app\wap\controller;
use app\wap\controller\Wap;
use think\Db;
use think\WechatApi;
class Weixin extends Wap
{
  public function index()
  {  
        $echoStr = $_GET["echostr"];
        if($this->check_signature() && $echoStr){
          echo $echoStr;
        }else{
            $this->reponse_msg();
        }
        
    }

    private function check_signature()
  {
        // you must define TOKEN by yourself
      
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
                
        $token = $this->shop_config['token'];
        $tmpArr = array($token, $timestamp, $nonce);
            // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
          return true;
        }else{
          return false;
        }
     
   
    }
   public function reponse_msg(){

    $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
   $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    //回复用户消息(纯文本格式) 
        $toUser   = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
          $time     = time();
        $msgType  =  'text';
        $openid=$toUser;
    $WechatApi=new WechatApi(['access_token'=>$this->access_token]);
	
	
    //判断该数据包是否是订阅的事件推送
    if( strtolower( $postObj->MsgType) == 'event'){

  /*-----------------------如果是取消关注 unsubscribe 事件--------------------------*/
    if( strtolower($postObj->Event == 'unsubscribe') ){
      $wxuser_info=Db::name('wechat_user')->where("openid='".$openid."'")->find();
      if($wxuser_info){
        Db::name('wechat_user')->where("openid='".$openid."'")->update(['subscribe'=>0]);
      }
    }
      /*-----------------------如果是关注 subscribe 事件--------------------------*/

      if( strtolower($postObj->Event == 'subscribe') ){
        
        if (strlen($postObj->EventKey) == 0){
          $scene_id=0;
          
        }else{
          //带参数二维码新用户关注
          $qrscene=$postObj->EventKey;
          $scene_id_arr = explode("qrscene_", $qrscene);
          $scene_id = $scene_id_arr[1]?$scene_id_arr[1]:0;
          
        }
        $content  = $this->shop_config['subscribe_word'];
       
         $wxuser_info=Db::name('wechat_user')->where("openid='".$openid."'")->find();
        if(!$wxuser_info){
        
            //如果没有注册过，注册微信到表
         //$url='https://api.weixin.qq.com/sns/userinfo?access_token=GeOuI71yAGHwVlNrKZi3DTOZ6H04CyDySI_icfMBjOR1wiXkPMMCeTAu0Pzm8TnRE4s5zYBFl5Mik4y6p04NK4YpwhVBMoRxv2veu0883zqEuHsbCEiq_mlD9tKB9W5OBAYbACAXWU&openid=ol_7zwE_O-Af89FOrGsJBB_tXYeE&lang=zh_CN'
            //$access_token=get_access_token($this->shop_config);
            $wxuser_info=$WechatApi->getWechatUserInfo($this->access_token,$openid);
            //file_put_contents('text2.log',json_encode($wxuser_info));
          // {"openid":{"0":"ol_7zwE_O-Af89FOrGsJBB_tXYeE"},"nickname":"2016","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/ATY0htTaTfwJrbqwWZGrT8mOqRVmDibM7Hk3Pt5PAcQq2bxEAAea54HqIqFC6eniaq76Xjricez3JqmNyicXnl4H2dNiblDE1I8qv\/0","city":"\u8862\u5dde","province":"\u6d59\u6c5f","unionid":"oFb8os8SwCrYxOMOi6xqURH3wYPw","create_time":1466155817,"scene_id":null}
            $wechat_info['openid']=$wxuser_info['openid'];
            $wechat_info['nickname']=filter_emoji($wxuser_info['nickname']);
            $wechat_info['headimgurl']=$wxuser_info['headimgurl'];
            $wechat_info['city']=$wxuser_info['city'];
            $wechat_info['province']=$wxuser_info['province'];
            $wechat_info['unionid']=isset($wxuser_info['unionid'])?$wxuser_info['unionid']:'';
            $wechat_info['create_time']=$time;
            $wechat_info['scene_id']=$scene_id;
            $wechat_info['subscribe']=1;
            
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
              $parent_info=Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->where("u.user_id = ".$scene_id)->field('parent_id,parent2_id,user_name,nickname,openid')->find();
              //$parent_info=Db::name('user')->field('parent_id,parent2_id')->find($scene_id);
              $user_info['parent2_id']=$parent_info['parent_id'];
              $user_info['parent3_id']=$parent_info['parent2_id'];
               //推送消息
               
            }
            Db::name("user")->insert($user_info);
           $user_id=Db::getLastInsID();
           //lt:向上级发送模版消息
           if($user_id && $scene_id && $parent_info['openid']){
             /* $data=[
              "touser"=>$parent_info['openid'],
              "template_id"=>"LDPkIS2_N_tgnUTl1VQ738m5uyNaQStNLQq1JWX0jxY",
             
              "data"=>[
                "first"=>["value"=>'恭喜您，您推荐了新会员['.$wechat_info['nickname'].']加入',"color"=>"#173177"],
                "keyword1"=>["value"=>$user_info['user_name'],"color"=>"#173177"],
                "keyword2"=>["value"=>date('Y-m-d H:i:s',$time),"color"=>"#173177"],
              ]
              ];
               
              $WechatApi->sendTemplateMessage($data);*/
              //$cTime=date('Y-m-d H:i:s',$time);
           
                $data=array(
                      "touser"=>$parent_info['openid'],
                  "template_id"=>$this->shop_config['tpl_newuser'],

                  "data"=>array(
                    "first"=>array("value"=>"恭喜您，您推荐了新会员[".$wechat_info['nickname']."]加入","color"=>"#173177"),
                   // "first"=>array("value"=>"恭喜注册成功","color"=>"#173177"),
                    "keyword1"=>array("value"=>$user_info['user_name'],"color"=>"#173177"),
                    "keyword2"=>array("value"=>date('Y-m-d H:i:s',$time),"color"=>"#173177"),
                        )
                      );
                $WechatApi->sendTemplateMessage($data);
              //file_put_contents('text22.log',$WechatApi->errCode);
           }
           //end
       /*    session('user_id',$user_id);
           session('wechat_user_id',$wechat_user_id);
           session('user_name', $user_info['user_name']);*/
            
          if(!empty($scene_id) && $parent_info){
            $content  = '您由会员ID：'.$parent_info['user_name'].'，昵称：'.$parent_info['nickname'].' 推荐 '.$content;
          }
       }else{
        
         Db::name('wechat_user')->where("wechat_user_id=".$wxuser_info['wechat_user_id'])->update(['subscribe'=>1]);
       }
        $template = $WechatApi->getTpl($msgType);
        $info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
        echo $info;

      
      }
      /*-------------------已关注用户扫二维码------------------------*/
      if( strtolower( $postObj->Event) == 'scan'){

      $content  = $this->shop_config['subscribe_word'];
        if (strlen($postObj->EventKey) == 0){
          $scene_id=0;
        }else{
          //带参数二维码新用户关注,这列的$postObj->EventKey已经是$scene_id了，所以不用分割
           $scene_id=$postObj->EventKey;
       
         
           // file_put_contents('text222222.log',$scene_id.' '.$parent_id);
        }
        if($scene_id){
         
             $wxuser_info=Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->field('w.scene_id,u.user_id,w.wechat_user_id,u.parent_id,w.openid,u.user_name,w.nickname,u.user_level')->where("openid='".$openid."'")->find();
              
          if($wxuser_info){
            if($wxuser_info['user_id']==$scene_id){
             $content='自己不能成为自己的上级';
               
            }else{
                if($wxuser_info['user_level']>1){
                $content='城市合伙人或全球合伙人不能成为别人下级';
                }else{

                   if($wxuser_info['parent_id']){
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
                       $parent_id=is_array($scene_id)?intval($scene_id[0]):intval($scene_id);
                      $parent_info=Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->where("u.user_id = ".$parent_id)->find();

                        
                        $data1['wechat_user_id']=$wxuser_info['wechat_user_id'];
                        $data1['scene_id']=$parent_id;

                      Db::name('wechat_user')->update($data1);
                      $data2['user_id']=$wxuser_info['user_id'];
                      $data2['parent_id']=$parent_id;
                      $data2['parent2_id']=$parent_info['parent_id'];
                      $data2['parent3_id']=$parent_info['parent2_id'];
                        // file_put_contents('text22.log',json_encode($data1).' '.json_encode($data2));
                      Db::name('user')->update($data2);
                      $content='恭喜您绑定成功，您现在的上级会员号是'.$parent_info['user_name'];
                      //向上级发模板
                      $data=array(
                      "touser"=>$parent_info['openid'],
                  "template_id"=>$this->shop_config['tpl_newuser'],
                  "data"=>array(
                    "first"=>array("value"=>"恭喜您，您推荐了新会员[".$wxuser_info['nickname']."]加入","color"=>"#173177"),
                   // "first"=>array("value"=>"恭喜注册成功","color"=>"#173177"),
                    "keyword1"=>array("value"=>$wxuser_info['user_name'],"color"=>"#173177"),
                    "keyword2"=>array("value"=>date('Y-m-d H:i:s',$time),"color"=>"#173177"),
                        )
                      );
                $WechatApi->sendTemplateMessage($data);
                    }
                   
                  }
              }
            }
            
             
          }
        }
     
         $template = $WechatApi->getTpl('text');
         
         $info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
          echo $info;
     }
    }
    /*-------------------------回复关键字--------------------------*/
    if( strtolower($postObj->MsgType) == 'text'){
         echo '';
            exit;
      //如果用户回复消息
        $keyword=$postObj->Content;
        switch ($keyword) {
          case '123':
            $content='你回复的是123';
            break;
          
          default:
            $content='有什么想说的';
            break;
        }
        $template = $WechatApi->getTpl('text');
         
         $info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
          echo $info;
     }
   }
 
}