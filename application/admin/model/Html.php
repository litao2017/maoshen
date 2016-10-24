<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Html extends Model
{
   
  public function html_mk(){
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  $html = file_get_contents("http://www.maogod.com/"); 
	   
	 
	   
	 $index =  file_put_contents('html/index.html',$html);
	   
	  $article =  file_put_contents('html/article_lists.html',file_get_contents("http://www.maogod.com/index/article/lists"));
	   
	  
	   $this->error('生成成功','/admin/index/info'); 
	  
	  } 

	  public function get_content_num($model,$is_html=0){
	  	if($is_html){
	  		return Db::name($model)->where("html",1)->count();
	  	}else{
	  		return Db::name($model)->count();
	  	}
	  }
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
}