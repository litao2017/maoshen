<?php
namespace app\index\controller;
use app\index\controller\Pc;
use think\Request;
use app\index\controller\Article;
class Products extends Pc{
	
	
 		 public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\index\model\Products();
    }
   	 
	
  public function index(){
	  

	  $pro_list = $this->Model->get_pro_list();
	  
	  
	  $this->assign('pro_list',$pro_list);
	  
	
	  return $this->fetch();
	
	  }
	  
	  
  public function lists(){
	  
	    $cate_id = input('cate_id/d');
  $cate=db("products_cate")->find($cate_id);
	  $list = $this->Model->get_pro_list();
	  
   $this->assign('cate',$cate);
	  $this->assign('pro_list',$list);
	  
	
	  return $this->fetch();
	
	  
	  }
	  
	  
	
  public function content(){
  	$list = $this->Model->get_pro_list();
	  
   
	  $this->assign('pro_list',$list);
	  $pro_id = input('products_id/d'); 
	  $pro_info = $this->Model->get_pro_info($pro_id);
	  $this->assign('products_info',$pro_info);
 	  return $this->fetch();
	 }
	

	




}
