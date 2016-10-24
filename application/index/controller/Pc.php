<?php
namespace app\index\controller;
use think\Controller;
class Pc extends Controller
{
    
   
    public $user_id;
    public $config;
    public $nav;
    public function _initialize()
    {
   
        $this->config=db("config")->find(1);
        $is_html=input('is_html/d');
		$this->assign("is_html",$is_html);  
       // $this->nav=db("nav")->where('is_show',1)->order('sort asc')->select();
       
	 
		$nav_list =  db('nav')->where(['is_show'=>1,'nav_pid'=>0])->order('sort')->select();
	  	$this->assign('nav_list',$nav_list);
        //$this->access_token=get_access_token($this->shop_config);
        $this->user_id=session('user_id')?session('user_id'):0;
        $this->assign('config',$this->config);
		
		 //lt:模版选择,tpl_id模版id，为0则为默认模版
        $tpl_id=$this->config['tpl_id'];//模版id
		
		
		
		//$module = request()->module();
		
		$controller=strtolower(request()->controller());
			
        $action=request()->action();
		

		
		$tpl_id=1;
           if($tpl_id){
			   
          	$this->template='tpl/tpl'.$tpl_id.'/'.$controller.'_'.$action;//设置模版路径
          
		   //$template='tpl/tpl'.$tpl_id.'/';
		   
		   //config('template.view_path',$template);
		  
		   $this->assign('tpl_path',INDEX_PUBLIC_URL.'tpl/tpl'.$tpl_id.'/');
		   $this->assign('static',INDEX_PUBLIC_URL.'tpl/tpl'.$tpl_id.'/static/');
		   }
		  
		
			   

		   
		   
		   
		  /* if($action=="index"){
		   
		  $this->redirect('/html/'.$module.'/'.$controller.'/');
		 
		   }else{
			   
		   $this->redirect('/html/'.$module.'/'.$controller.'/'.$action);
   
			   }*/
          // define('INDEX_PUBLIC_URL', '/staticl/');
           //end
		
    }



     public function _empty($name)
  
    {
        //把所有城市的操作解析到city方法
        return $this->showError($name);
    }
      protected function showError($name)
    {
        //和$name这个城市相关的处理
         return  $name;
    }
	
	 public function get_about($id){
       return db("about")->find($id);
     }
  

	
	
	
}

