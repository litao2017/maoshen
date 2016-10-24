<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class About extends Admin {
  
    public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\About();
    }
   
   
  function edit(){
	  
	  $about_id=input("about_id/d");  
	  
	  $article = $this->Model->get_article_info($about_id);
	  
	  $this->assign('article',$article);
	  	 
	  if(request()->isPost()){
		  
		 	 $about_id=Request::instance()->post('about_id',0,'intval'); 
            
            
			$content=Request::instance()->post('content','','htmlspecialchars'); 
		          
            $status=Request::instance()->post('status',0,'intval'); 
			
			
			    $map=$this->upload_file('map');
				
        		
		
				
			if($map){
			
			$data = ['content'=>$content,'status'=>$status,'map'=>$map];}else{$data = ['content'=>$content,'status'=>$status];}
			
		  
		   $update = $this->Model->update_article($data,$about_id); 
		 
		  if($update){
			  
			 return $this->success('修改成功','/admin/about/edit/about_id/'.$about_id);
			  
			  }
		  
		  
		  
		  }
	  
	  
	  return $this->fetch();
	  
	  
	  }
	


}

