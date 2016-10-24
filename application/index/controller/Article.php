<?php
namespace app\index\controller;
use app\index\controller\Pc;
use think\Request;
use app\index\controller\Goods;
class Article extends Pc
{
 
 		 public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\index\model\Article();
    }
   	 
  
  public function index(){
	   $article = $this->Model->get_list($this->config['pagesize']);
	   $this->assign('article_list', $article['list']);
	   $this->assign('html_page', $article['html_page']);
	    return $this->fetch();
  }
  
   public function lists(){
	   
	   
	   $cate_id = input('cate_id/d');
	   
	   $cate_list = $this->Model->get_cat_list();
	   $article = $this->Model->get_list($this->config['pagesize'],$cate_id);
	   
		   
		
	   
	   //$article_list = $this->Model->get_cat_article_list($cate_id);
	   
	  
	   
	   $this->assign('article_list', $article['list']);
	   
	   $this->assign('html_page', $article['html_page']);
	   $this->assign('cate_list',$cate_list);
	     $cate_info=$this->Model->get_cate_info($cate_id);
	   $this->assign('cate_info',$cate_info); 
	   return $this->fetch();
	   }
  
   public function content(){
	   

	   
	//  $is_html=input('html',0,'intval');
	   
	   
	   $cate_list = $this->Model->get_cat_list();
	    $this->assign('cate_list',$cate_list);
	   $article_id = input('article_id/d')?input('article_id/d'):0;
	   
	    db('article')->where('article_id',$article_id )->setInc('click');
	   $article = $this->Model->get_article_info($article_id);
	     $this->assign('article_info',$article);
	   
	     $cate_info=$this->Model->get_cate_info($article['cate_id']);
	   $this->assign('cate_info',$cate_info); 
	  
	   //上一篇 下一篇
	   if($article_id>1){
	   	$prev_info=$this->Model->get_next_prev_article($article_id,0);
	   }else{
	   	$prev_info=[];
	   }
	   $next_info=$this->Model->get_next_prev_article($article_id);

	   $this->assign('prev_info',$prev_info); 
	   $this->assign('next_info',$next_info); 
	   return $this->fetch();
	   	}
   public function about_us(){
	   
	   $article_id = input('article_id/d')?input('article_id/d'):1;
	   
	   
	   
	   $article = $this->Model->get_article_info($article_id);
	   
	   $article_list = $this->Model->get_cat_article_list(8);
	   
	   
	   $cate=$this->Model->get_cate_info(8);
	   $this->assign('cate',$cate); 
	   
	   $this->assign('article',$article);
	   $this->assign('article_list',$article_list);
	   
	   return $this->fetch();
	   }	

}
