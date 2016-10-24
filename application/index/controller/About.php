<?php
namespace app\index\controller;
use app\index\controller\Pc;
use think\Request;
class About extends Pc
{
 
 		 public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\index\model\About();
    }
   	 
  
   public function content(){
	   

	   
	//  $is_html=input('html',0,'intval');
	   
	   
	   $about_id = input('about_id/d')?input('about_id/d'):1;
	   
	   $list = $this->Model->get_list();
	   
	   $article_info = $this->Model->get_article_info($about_id);
	   
	   
		//ss($article);
		
		
		 $this->assign('article_info',$article_info);
	   	
		 $this->assign('list',$list);

	  	 
	   
	   	  return $this->fetch();
	  
	   }	
  

}
