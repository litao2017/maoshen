<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\controller\Admin;
use think\Db;
use think\Request;

class Template extends Admin
{
    
	  public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Template();
    }
	
	public function index(){
		
		$theme_dir = APP_PATH."index/view/tpl";	//要遍历的文件夹
		
		//$config = WebConfigHelper::getObject();
		
		$template_list = [];
		
    	$list = scandir($theme_dir);
		
    	foreach($list as $template_dir){
			
    		if($template_dir != ".." && $template_dir != "." ){
				
    			if(is_dir($theme_dir."/".$template_dir)){
					
    				$template['template_dir'] = $theme_dir."/".$template_dir;
					
					$template['tpl_id'] = substr($template_dir,3);
					
					$template['thum'] = INDEX_PUBLIC_URL.'tpl/'.$template_dir.'/image/preview/index.jpg';
					
					
						$template_list[] = $template;
						
				}
    		}
    	}
						
						
		$this->assign("template_list",$template_list);

	
	return	$this->fetch();
	
	}
	
	//使用该模板
	public function change(){
		
		
		$data["tpl_id"] = input('tpl_id','','addslashes');
		
		
		
		 $result = $this->Model->update_tpl_id($data["tpl_id"]);
		
		 
		//$result = WebConfigHelper::update($data);
		
		if($result === false){	//失败，则提示错误信息，并返回上一个页面[记录了上一个页面，用户填写的信息]
			//echo CommonUtil::ajaxReturn(Status::$ERROR,"服务器繁忙，请稍后重试！");
			$this->error('修改失败');
			
		}else{	//修改成功，提示成功信息，并且返回上一个页面
			//清除RUNTIME...
	    	//CommonUtil::clearRuntime();
			//echo CommonUtil::ajaxReturn(Status::$RELOAD_PAGE,"修改成功！");
			return $this->success('修改成功','index');
			
		}
		
	}
	
	
	
	
	
	
    
}
