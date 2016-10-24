<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Comment extends Model
{
   
    public function get_list(){
       
        $comment_list=db('comment')->order('comment_id desc')->select();
        //pp($admin);
        if($comment_list){
           
       
                return $comment_list;
          
        }
        return false;
       /* $Admin = new Admin;
        $Admin->data(['user_name'=>'Thinkphp','email'=>'thinkphp@qq.com']);

        $Admin->save();*/
    }

    public function add_comment($data){
        Db::name('comment')->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
    }
    public function update_comment($data,$comment_id){
        $data['comment_id']=$comment_id;
        Db::name('comment')->update($data);
       
    }
    public function delete_comment($comment_id){
        
        Db::name('comment')->delete($comment_id);
       
    }
    public function get_comment_info($comment_id){
        $comment_info=Db::name('comment')->where('comment_id',$comment_id)->find();
        return $comment_info;
    }

}