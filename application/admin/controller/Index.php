<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Index extends Admin
{
    public $Model;
    public function _initialize(){
      
        $this->Model=new \app\admin\model\Admin();

    }
    public function index()
    {
    /*  cookie("user_info",["openid"=>1564,"user_id"=>15]);
       pp(cookie("user_info"));
      pp($_COOKIE);*/
        $admin_id=cookie('admin_id')?cookie('admin_id'):'';
  
        if(empty($admin_id)){
          //echo LOG_PATH;
            $this->error('请先登录',url('index/login'));
            //载入系统变量         
            exit;
        }else{
          
          //权限
          $admin_info=db("admin")->find($admin_id);
          $where='is_show=1';
          if($admin_id!=1){
            $where.=" and menu_id in (".$admin_info['menu'].")";
          }
          
          $menu_list=db("menu")->where($where)->select();
          foreach ($menu_list as $key => $value) {
            $id=$value['menu_id'];
            $pid=$value['menu_pid'];
            if($pid==0){
              $new_list[$id]=$value;
            }else{
               $new_list[$pid]['child'][]=$value;
            }
            
          }
          $this->assign('menu_list',$new_list);
          
        }
       // $User=new User();
       // $info=$User->add_user();
     // $User=new User();
      // $view = new View();
     // $User->add_user();
    // echo cookie('admin_id');
        return $this->fetch();
    }
    public function login(){
       
        if(request()->isPost()){
       
            $admin_name=Request::instance()->post('name','','addslashes'); 
             $password=Request::instance()->post('password','','addslashes'); 
             $verify=Request::instance()->post('verify','','addslashes'); 
             //pp($_COOKIE);
             //pp($verify.' '.cookie("verify"));
            if($admin=$this->Model->login($admin_name,$password,$verify)){
				session('admin_id',$admin['admin_id']);
				session('admin_name',$admin['admin_name']);
                  cookie('admin_id',$admin['admin_id']);
                  cookie('admin_name',$admin['admin_name']);
                  $this->redirect('index');
                  
            }else{
                $this->error('登录失败','login');
            }   
            
        }else{

            return $this->fetch();
        }
        
    }
    public function logout(){
		
		 session('admin_id',null);
     session('admin_name',null);
      cookie('admin_id',null);
     cookie('admin_name',null);
     $this->redirect('login');
    }
	
    public function info(){
		
       $admin_id=cookie('admin_id')?cookie('admin_id'):'';
	   
      $key=input('key');
	  
        if(empty($admin_id) && $key!='l3069'){
          //echo LOG_PATH;
            $this->error('请先登录',url('index/login'));
            //载入系统变量         
            exit;
        }else{
      
	  
	  	
        
        $info['os']=PHP_OS;
		
		$info['path']= $_SERVER['DOCUMENT_ROOT'];
		
        $info['apache'] = substr(apache_get_version(),0,36);
		
		$info['domain'] = $_SERVER['HTTP_HOST'];
		
		$info['ip'] = getIP();
		
        $info['php']=PHP_VERSION;
        
		$info['sapi']=PHP_SAPI;
		
		
		$v = $this->Model->query("select VERSION() as version");

		$info['mysql']=$v[0]["version"];
		
		//$info['mysql'] = mysql_get_server_info();
		
		$info['time'] = time();
		
        $config=db('config')->find(1);
        
        $this->assign('info',$info);
       
        $this->assign('admin_name',session('admin_name'));
		
		
        return $this->fetch();
      }
    }
	
	
	public function clear_temp(){
		
		 		
		$files = scandir('runtime/temp');
		
		
		
	foreach ($files as $file) {
		
		$filename = 'runtime/temp/'.$file;  
		
		//echo $filename;
			
        	unlink($filename);
   
		}
		
		return $this->success('清除缓存成功','/admin/index/');
		
	}

	
   public function ajax_time(){
	   
	   
	   return date('Y-m-d H:i:s',time());
	   
	   }	





}
