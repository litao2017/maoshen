<?php
namespace app\wap\model;
use think\Model;
use think\Db;
class Tuan extends Model
{


   /*
   查询用户
   user_id int 用户id
 has_wx 是否获得微信信息 1是 0否
    */
    public function get_tuan_list($user_id,$has_wx=0,$order='user_id',$limit=15){
       if($has_wx){
       $list=Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->where("u.tuan_id = ".$user_id)->order('u.'.$order.' desc')->limit($limit)->select();
      }else{
        $list=Db::name('user')->where('tuan_id='.$user_id)->order($order.' desc')->limit($limit)->select();
      }
       
        return $list;
    }

      

}