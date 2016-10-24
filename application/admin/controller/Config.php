<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Config extends Admin {
  
    public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Config();
    }
  
    
    //系统设置
  

    function set(){
		
		
         if(request()->isPost()){
			 
            $id = input('id')?input('id'):0; 
			
			
            $data['title']=Request::instance()->post('title','','addslashes'); 
            $data['keywords']=Request::instance()->post('keywords','','addslashes'); 
            $data['description']=Request::instance()->post('description','','addslashes'); 
           
			$data['copyright']=Request::instance()->post('copyright','','addslashes'); 

			
            $data['name']=Request::instance()->post('name','','addslashes'); 
            $data['address']=Request::instance()->post('address','','addslashes'); 
            $data['tel']=Request::instance()->post('tel','','addslashes'); 
            $data['qq']=Request::instance()->post('qq',0,'intval'); 
            
		   	$data['fax']=Request::instance()->post('fax','','addslashes'); 
            $data['wx']=Request::instance()->post('wx','','addslashes'); 
            $data['email']=Request::instance()->post('email','','addslashes'); 
            $data['site']=Request::instance()->post('site','','addslashes'); 
		   $data['beian']=Request::instance()->post('beian','','addslashes'); 
 
            $data['notice']=Request::instance()->post('notice','','addslashes'); 
         
                //如果有id则更新
			if($id){
				
            $this->Model->update_config($data,$id);
			
			}else{
				
			$this->Model->insert_config($data);
				
				}
           
		    return $this->success('更新成功','set');
      
            

             
         }else{
            
         
		 
                $config=$this->Model->get_config(1);
         
            $this->assign("config",$config);
			
            return $this->fetch();
         }
      
    }

 	
 	
	
 public function nav_list(){
	 
	 
	 
	 
	 
	 $nav_list=$this->Model->get_nav();
        $this->assign("nav_list",$nav_list);
        return $this->fetch();  
	 
	 
	 
	 }

	

 public function nav_set(){
    
	
    if(request()->isPost()){
		
		$nav_id = input('nav_id/d');
		
		$data['nav_name']=input('nav_name','','addslashes');
		$data['nav_url']=input('nav_url','','addslashes');
		$data['is_show']=input('is_show/d');
		$data['sort']=input('sort/d');
		$data['type']=input('type/d');
		$data['nav_pid']=input('nav_pid/d');
		
       
	   if($nav_id){
		   
		 if($nav_id==$data['nav_pid']){

          $this->error('修改失败,自己不能成为自己的上级');
        
		}  
		   
		$this->Model->update_nav($data,$nav_id);  
		 return $this->success('更新成功','nav_list'); 
		   
		   }else{
			   
        
		$new = $this->Model->add_nav($data);
		if($new){
		
		 return $this->success('添加成功','nav_list');
		 
		}
		   }

     
   
    }else{
		
		$nav_list = $this->Model->get_nav();
		
		$this->assign('nav_list',$nav_list);
		
		$nav_id = input('nav_id/d');
		
		$nav = db('nav')->where('nav_id',$nav_id)->find();
			
			
        $this->assign("nav",$nav);
        return $this->fetch(); 
    }
    
 }
 	public function del_nav(){
		
		$nav_id = input('nav_id/d')?input('nav_id/d'):0;
		
		$del =  $this->Model->del_nav($nav_id);
		
		if($del){
			
			return $this->success('删除成功','nav_list');
			
			}
		
		
		
		}
 
 
 
 
 
}

