<?php
namespace app\index\model;
use think\Model;
use think\Db;
class About extends Model
{



   /*
   查询用户
   article_id int 文章id
    */
    public function get_article_info($id){
        $article_info=db('About')->where("about_id=$id and status=1")->find();
        return $article_info;
    }
	
	

	
	
	public function get_list(){
		
		

       $list=db('About')->where('status',1)->select();
	   
	  

        return $list;
    

		
		
		}	
	
	
	
	
	
	
	
	
	
	
	
	
  
}