<?php
namespace app\index\controller;
use app\index\controller\Pc;
use think\Request;

class Index extends Pc
{
	public $Model;
    public function _initialize(){
      parent::_initialize();
        
		$this->Model=new \app\index\model\Index();
		
		
    }
	
	
  public function index(){
    	
	$article = new \app\index\model\Article();
      

	  $anli_list =  $this->Model->get_anli_list(8);
	  
	  $pro_list =  $this->Model->get_pro_list(6);
	  
	  $slide_list =   $this->Model->get_slide_list(3);
	  
	  
      $cat_list = $article->get_cat_list();
	 
	 foreach($cat_list as $value){
		 
		 $article_list[] = $article->get_cat_article_list($value['cate_id'],5);
		 
		 }	
	 
	  //$article_list = $this->Model->get_cat_article_list($cat_id,5);
	 
	  $this->assign('slide_list',$slide_list);
	 	
	 	 $this->assign('pro_list',$pro_list);
	 
	   $this->assign('article_list',$article_list);
	   
	   $this->assign('cat_list',$cat_list);
	 
	  $this->assign('anli_list',$anli_list);
	 
	 $this->assign('about_us',$this->get_about(1));
	 return $this->fetch();
	 
	 
	 
  	}
   
}
