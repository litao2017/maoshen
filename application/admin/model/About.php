<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class About extends Model
{
   
   
    public function get_list(){
		
   
 

       $list=db('About')->select();
	   
	  

        return $list;
    
       /* $Admin = new Admin;
        $Admin->data(['user_name'=>'Thinkphp','email'=>'thinkphp@qq.com']);

        $Admin->save();*/
    }

   
    public function update_article($data,$about_id){
       
	    $data['about_id']=$about_id;
		
       return db('About')->update($data);
       
    }
   
    public function get_article_info($about_id){
        $article_info=db('About')->where('about_id',$about_id)->find();
        return $article_info;
    }
	

}