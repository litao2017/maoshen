<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Slide extends Admin {
	
	 public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Slide();
    }
	
	
	
	
	public function index(){
		
		
		$slide_list =  $this->Model->get_slide_list();
		
		
		$this->assign('slide_list',$slide_list);
		
		return $this->fetch();
		}
	
	
	
	
	
	
  
    public  function add(){
		
		$id = input('id/d')?input('id/d'):0;
		
		if(request()->isPost()){
			
			$id = input('id/d')?input('id/d'):0;
			
			$data['title'] = input('title','','addslashes');
			
			$data['url'] = input('url','','addslashes');
			
			$data['create_time'] = time();
			
			if($_FILES['image']['type']=="image/jpeg"||$_FILES['image']['type']=="image/png"){
		
                 $file = request()->file('image','','addslashes');
				
			  					  
                 $move_path=ROOT_PATH .'upload/image/';
               
                $info = $file->move($move_path);
				
				
               
			    if($info){
					
					
                    $data['image'] = str_replace(ROOT_PATH,"/",$info->getPathname());
					
					
					
                   // echo $info->getExtension(); // 输出 jpg
                }else{
                    // 上传失败获取错误信息
                    echo $file->getError();
                    exit;
                
				
				 }
            }
		
		if($id){
			
			
		$this->Model->update_slide($data,$id);
			
			return $this->success('保存成功','index');
			}	
			
		
		$ins = $this->Model->add_slide($data);	
			
		if($ins){
			
			return $this->success('保存成功','index');
			
			}
		
				
				}
		
	$slide = $this->Model->get_slide_info($id);
	$this->assign('slide',$slide);
		
	return	$this->fetch();
		}
    
	
	public function del_slide(){
		
		$id = input('id/d')?input('id/d'):0;
		
		$del = $this->Model->del_slide($id);
		
		
		if($del){
			
			return $this->success('删除成功','index');
			
			}
		
		}
	
	
	
}

