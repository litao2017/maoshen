<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Slide extends Model
{
	
	
	public function get_slide_list(){
		
		
		$slide_list = db('slide')->order('create_time desc')->select();
		
		
		return $slide_list;
		
		
		}
	public function del_slide($id){
		
		$del = db('slide')->where('id',$id)->delete();
		
		return $del;
		}
	
	
	public function update_slide($data,$id){
	 
	 $data['id']=$id;
	 
        db('slide')->update($data);
	
	 
	 }
 
	public function add_slide($data){
	
	     
        db('slide')->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
	
	
	
	}
  
  
    public function get_slide_info($id){
		
        $slide_info=db('slide')->where('id',$id)->find();
        
		return $slide_info;
   
    } 
 
	
	
	
	
	
	
	
	
	
   
}