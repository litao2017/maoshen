<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Flink extends Admin {
	
	 public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Flink();
    }
	
	
	public function flink_list(){
		
		$flink_list = db('flink')->select();
		
	
		
		$this->assign('flink_list',$flink_list);
	return	$this->fetch();
		}
	
	
  
    public  function add(){
		
		$id = input('id/d')?input('id/d'):0;
		
		if(request()->isPOST()){
			
			$id = input('id/d')?input('id/d'):0;
			
			//$id = input('id/d');
			
			$data['title'] = input('title','','addslashes');
			
			$data['url'] = input('url','','addslashes');
					
			
			if($_FILES['image']['type']=="image/jpeg"){
		
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
			
			$this->Model->update_flink($data,$id);
			return $this->success('保存成功','flink_list');
			
			}	
		
		$ins = $this->Model->add_flink($data);	
			
		if($ins){
			
			return $this->success('保存成功','flink_list');
			
			}
						
				}
		
		$flink = $this->Model->get_flink_info($id);
		
		$this->assign('flink',$flink);
				
		return	$this->fetch();
		}
    
	
	
	
	
	public function del_flink(){
		
		$id = input('id/d')?input('id/d'):0;
		
		$del = db('flink')->where('id',$id)->delete();
		
		if($del){
		
		return $this->success('删除成功','flink_list');
			}
		
		}
	
	
	
	
	
	
	
}

