<?php
namespace app\index\controller;
use think\Controller;
class Images extends Pc
{
	public $Model;
    public function _initialize(){
      parent::_initialize();
        
		$this->Model=new \app\index\model\Imgtext();
		
		
    }
	
   public function lists(){
	   
	   $cate_id = input('cate_id/d');
	   
	   
	   $cat_list = $this->Model->get_cat_list();
	   
	   
	   
	   $list = $this->Model->get_cat_img_text_list($cate_id);
	   
	   $this->assign('img_text_list',$list);
	   $this->assign('cat_list',$cat_list);
	   
    return $this->fetch();
   }
 	public function content(){
		
		$id = input('id/d');
		
		
		$info = $this->Modelget_info($id);
		
		
		
		
		$this->assign('img_text',$info);
		
		return $this->fetch();
		
		}
   
   
   
   
}
