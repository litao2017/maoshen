<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Manager extends Model
{
   
    public function get_list(){
       
        $manager_list=db('admin')->order('admin_id desc')->select();
        //pp($admin);
        if($manager_list){
              return $manager_list;
        }
        return false;
       /* $Admin = new Admin;
        $Admin->data(['user_name'=>'Thinkphp','email'=>'thinkphp@qq.com']);

        $Admin->save();*/
    }

    public function add_manager($data){
        db('admin')->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
    }
    public function update_manager($data,$manager_id){
        $data['admin_id']=$manager_id;
        db('admin')->update($data);
       
    }
    public function delete_manager($manager_id){
        
       db('admin')->delete($manager_id);
       
    }
    public function get_manager_info($manager_id){
        $manager_info=db('admin')->where('admin_id',$manager_id)->find();
        return $manager_info;
    }

}