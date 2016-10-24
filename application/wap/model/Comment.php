<?php
namespace app\wap\model;
use think\Model;
use think\Db;
class Comment extends Model
{



    public function get_list($goods_id,$limit=15){
        $comment_list=Db::name('comment')->where("goods_id=".$goods_id." and status=1")->limit($limit)->order('comment_id desc')->select();
        return $comment_list;
    }
    public function add_comment($data){
      Db::name("comment")->insert($data);
      return Db::getLastInsID();
    }
  
}