<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
class Admin extends Controller
{
    public $config;
   
    public $admin_info;
    //初始化
    public function _initialize()
    {

        $this->config=db('config')->find(1);
		
		 $this->assign('config',$this->config);
        
		$admin_id=cookie('admin_id')?cookie('admin_id'):'';
  
        if(empty($admin_id)){
          //echo LOG_PATH;
            $this->error('请先登录',url('index/login'));
            //载入系统变量
                    
            exit;
        }else{
           //允许操作的方法
            //$access_action=['index/index','index/login'];

            $this->admin_info=db("admin")->find($admin_id);
           $controller=Request::instance()->controller();
           $action=Request::instance()->action();
		   
          // $access=$controller.'/'.$action;
          // if(!in_array($access,$access_action)){
        	 $menu=db("menu")->where("menu_c='".$controller."' and menu_a='".$action."' and  menu_id in (".$this->admin_info['menu'].") ")->find();
               // echo "auth_c='".$controller."' and auth_a='".$action."' and auth_id in (".$this->admin_info['auth'].") ";exit;
             
			  if(!$menu && $admin_id!=1){
				
                $this->error('没有权限','/admin/index/info');
				
              }
         //  }
         
        }
    }
	
	
	
	
	public function upload_file($name){
		if($_FILES[$name]['type']=="image/jpeg" || $_FILES[$name]['type']=="image/png"){
		   $file = request()->file($name,'','addslashes');
             $move_path=ROOT_PATH .'upload/image/';
           
            $info = $file->move($move_path);
            if($info){
              

			return  str_replace(ROOT_PATH,"/",$info->getPathname());
               // echo $info->getExtension(); // 输出 jpg
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
                exit;
            }
        }else{
          return '';
        }

	}

		
		
}
	

	
	
	


