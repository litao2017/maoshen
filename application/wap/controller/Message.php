<?php
namespace app\wap\controller;
use app\wap\controller\Wechat;
use think\Request;
class Message extends Wechat
{
    public $Model;
    public function _initialize(){
        parent::_initialize();
        $this->Model=new \app\wap\model\Message();
  
    }
 
    //评论界面
  public function index()
    {
      // $user_id=1;
       $user_id=$this->user_id;
       //查找用户等级
       
  $is_send=input('is_send/d');
//更新全部为已读
$this->Model->is_view($user_id);
        $message_list=$this->Model->get_list($user_id,$is_send);
        $this->assign("is_send",$is_send);
        $this->assign("message_list",$message_list);
        return $this->fetch();
    }
     public function ajax_message(){
    $user_id=$this->user_id;
       $num=Request::instance()->post("num",1,"intval");
      $page=Request::instance()->post("page",1,"intval");
    $is_send=Request::instance()->post("is_send",0,"intval");
      $curr=$num*$page;
      $limit="{$curr},{$num}";
      $message_list=$this->Model->get_list($user_id,$is_send,$limit);
      $info='';
    foreach ($message_list as $key => $value) {
      $info.=' <li><img src="'.($value['headimgurl']?$value['headimgurl']:"/static/wap/image/cfr.jpg").'" style="float:left;"><div class="user_name"><p>昵&nbsp;&nbsp;&nbsp;&nbsp;称：'.$value['nickname'].'&nbsp;&nbsp;&nbsp;<font style="font-size: 11px;color:#888">'.date('Y-m-d',$value['create_time']).'</font>&nbsp;&nbsp;<a href="/wap/message/send_message/get_id/'.$value['send_id'].'" class="message">回信</a></p><p>内&nbsp;&nbsp;&nbsp;&nbsp;容：'.$value['content'].'</p></div></li>';

     
    }
  echo $info;
  exit;
    }

    public function send_message(){
      
     
      if(!empty($_POST)){
        $user_id=$this->user_id;
        $postcode=Request::instance()->post("postcode","","addslashes");
          if(cookie("postcode_message")!=$postcode){
            js_alert('发送失败');
          }
           $level=Request::instance()->post("level",1,"intval");
          $data['send_id']= $user_id;
        
 
          $data['content']=Request::instance()->post("content",1,"addslashes");
        
          $data['create_time']=time();
          $data['get_id']=Request::instance()->post("get_id",0,"intval");
          
          $message_id=$this->Model->add_message($data);

          if($message_id){
            js_alert('发送成功');
          }else{
            js_alert('发送失败');
          }
      }else{
      $get_id=Request::instance()->param("get_id",0,"intval");
       $level=Request::instance()->param("level",0,"intval");
        if($get_id){
          //s:用来验证是否是通过自己的表单提交的
            $postcode=mt_rand(1000,9999);
            cookie("postcode_message",$postcode);
            $this->assign("postcode",$postcode);
            //end
          $userModel=new \app\wap\model\User();
          $get_info=$userModel->get_user_info($get_id,1);
          $this->assign('get_info',$get_info);
          $this->assign('level',$level);
          return $this->fetch();
        }
         
         
      }
    }
  
}
