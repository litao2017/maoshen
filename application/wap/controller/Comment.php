<?php
namespace app\wap\controller;
use app\wap\controller\Wechat;
use think\Request;
class Comment extends Wechat
{
    public $Model;
    public function _initialize(){
        parent::_initialize();
        $this->Model=new \app\wap\model\Comment();
  
    }
 
    //评论界面
  public function index()
    {
        //$Model=new \app\wap\model\Wechat();
       // $user_info=$Model->login();
        //var_dump($user_info);
        // $user_id=$this->user_id;
       $goods_id=input("goods_id/d");
       //查找用户等级
       
       if($goods_id){
         $comment_list=$this->Model->get_list($goods_id);

        $this->assign("comment_list",$comment_list);
        $this->assign("goods_id",$goods_id);
        return $this->fetch();
       }
       
    }
     public function ajax_comment(){
    
       $num=Request::instance()->post("num",1,"intval");
      $page=Request::instance()->post("page",1,"intval");
       $goods_id=Request::instance()->post("goods_id",1,"intval"); //评论的商品
 
      $curr=$num*$page;
      $limit="{$curr},{$num}";
      $comment_list=$this->Model->get_list($goods_id,$limit);
      $info='';
    foreach ($comment_list as $key => $value) {
      $info.='   <li><img src="'.($value['headimgurl']?$value['headimgurl']:"/static/wap/image/cfr.jpg").'" style="float:left;"><div class="user_name"><p>会员号：'.$value['user_name'].'&nbsp;&nbsp;&nbsp;<font style="font-size: 11px;color:#888">'.date('Y-m-d',$value['create_time']).'</font></p><p>评&nbsp;&nbsp;&nbsp;&nbsp;论：'.$value['content'].'</p></div></li>';
     
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
         $order_id=input('order_id/d');
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
