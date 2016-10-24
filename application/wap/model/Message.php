<?php
namespace app\wap\model;
use think\Model;
use think\Db;
class Message extends Model
{



    public function get_list($user_id,$is_send=0,$limit=15){
        if($is_send){
             $message_list=Db::name('message')->alias('m')->join('user u','m.get_id=u.user_id')->join('wechat_user w','w.wechat_user_id=u.wechat_user_id')->field('m.create_time,w.nickname,w.headimgurl,u.user_name,u.user_id,m.content,m.send_id,m.get_id')->where('m.send_id='.$user_id)->limit($limit)->order('is_view desc,message_id desc')->select();

        }else{
                   $message_list=Db::name('message')->alias('m')->join('user u','m.send_id=u.user_id')->join('wechat_user w','w.wechat_user_id=u.wechat_user_id')->field('m.create_time,w.nickname,w.headimgurl,u.user_name,u.user_id,m.content,m.send_id,m.get_id')->where('m.get_id='.$user_id)->limit($limit)->order('is_view desc,message_id desc')->select();
        }


        return $message_list;
    }
    public function add_message($data){
      Db::name("message")->insert($data);
      return Db::getLastInsID();
    }
    /*
    将接收者为user_id全部更新为1
     */
   public function is_view($user_id){
      $count=Db::name("message")->where('get_id='.$user_id)->count();
      if($count>0){
        //如果有未读的则更新
        Db::name("message")->where('get_id='.$user_id.' and is_view=0')->update(['is_view'=>1]);
      }
   
    }
}