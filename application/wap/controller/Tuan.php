<?php
namespace app\wap\controller;
use app\wap\controller\Wechat;
use think\Request;
class Tuan extends Wechat
{
    public $Model;
    public function _initialize(){
        parent::_initialize();
        $this->Model=new \app\wap\model\Tuan();
  
    }
 
    //评论界面
  public function index()
    {
    
        $user_id=$this->user_id;
  
      $limit="{$curr},{$num}";
        $order=input('order');
        $order=$order?$order:'user_id';
        $tuan_list=$this->Model->get_tuan_list($user_id,1,$order);

        $this->assign("tuan_list",$tuan_list);
        $this->assign("order",$order);
        return $this->fetch();
    }
     public function ajax_tuan(){
    
      $user_id=$this->user_id;
       $num=Request::instance()->post("num",15,"intval");
      $page=Request::instance()->post("page",1,"intval");
      $order=Request::instance()->post("order",'user_id',"addslashes");
 
      $curr=$num*$page;
      $limit="{$curr},{$num}";
     $tuan_list=$this->Model->get_tuan_list($user_id,1,$order,$limit);
      $info='';
    foreach ($tuan_list as $key => $value) {
       $tuandui='<span>团队：'.$value['tuan_money'].'</span>';
       $info.=' <li><img src="'.$value['headimgurl'].'" style="float:left;"><div class="user_name"><p>昵&nbsp;&nbsp;&nbsp;&nbsp;称：'.$value['nickname'].'</p><p>会员号：'.$value['user_name'].'&nbsp;&nbsp;<a href="/wap/message/send_message/get_id/'.$value['user_id'].'" class="message">私信</a></p></div>'.$tuandui.'</li>';
        
    }
  echo $info;
  exit;
    }

    public function docomment(){
      $user_id=$this->user_id;
      $orderModel = new \app\wap\model\Order();
      if(!empty($_POST)){
          $data['order_id']=Request::instance()->post("order_id",1,"intval");
          //防止多次提交
          $order_info=$orderModel->get_order_info($order_id);
          if($order_info['is_comment']){
            js_alert('您已经评价过了');
          }
          $data['content']=Request::instance()->post("content",1,"addslashes");
          $data['status']=0;
          $data['create_time']=time();
          $data['nickname']=$this->nickname;
          $data['user_name']=$this->user_name;
          $data['user_id']=$user_id;
          $data['headimgurl']=$this->headimgurl;
          
          $comment_id=$this->Model->add_comment($data);
          $orderModel->update_order(['is_comment'=>1],$data['order_id']);
          if($comment_id){
            js_alert('评论成功,审核后显示','/wap/user/');
          }else{
            js_alert('评论失败');
          }
      }else{
         $order_id=input("order_id/d");
         if($order_id){
            $order_info_all=$orderModel->get_order_info_all($order_id);
           $order_info=$order_info_all['order_info'];
           $order_goods=$order_info_all['order_goods'];

           if($order_info['user_id']!=$user_id){
             js_alert('不能评价');
           }
           if($order_info['is_comment']){
             js_alert('已评价过');
           }
           $this->assign("order_id",$order_id);
           $this->assign("order_goods",$order_goods);
           return $this->fetch();
         }
         
      }
    }
  
}
