<?php
namespace app\index\controller;
use think\Controller;
class Anli extends Pc
{
	public $Model;
    public function _initialize(){
      parent::_initialize();
        
		$this->Model=new \app\index\model\Anli();
		
		
    }
	
   public function lists(){
	   
	   $cate_id = input('cate_id/d');
	   
	   $cate_info=db("anli_cate")->find($cate_id);
	   $cate_list = $this->Model->get_cat_list();
	   
	   
	   
	   $anli_list = $this->Model->get_list(9,$cate_id);
	  
	   $this->assign('anli_list',$anli_list['list']);
	   $this->assign('cate_list',$cate_list);
	   $this->assign('cate_info',$cate_info);

	   $this->assign('html_page',$anli_list['html_page']);

	   
    return $this->fetch();
   }
  public function content(){
    
  }
   
}
