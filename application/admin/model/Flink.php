<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Flink extends Model
{

 
 
 
 
public function update_flink($data,$id){
	 
	 $data['id']=$id;
	 
        db('flink')->update($data);
	
	 
	 }
 
public function add_flink($data){
	
	     
        db('flink')->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
	
	
	
	}
  public function delete_flink($id){
        
        db('flink')->delete($id);
       
    }
    public function get_flink_info($id){
		
        $flink_info=db('flink')->where('id',$id)->find();
        
		return $flink_info;
   
    } 
 
 
 
 
 
   
}