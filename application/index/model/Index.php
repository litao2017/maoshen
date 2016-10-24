<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Index extends Model
{

public function get_anli_list($limit){
	
	
	
	
	$anli_list = db('anli')->limit($limit)->order('create_time')->select();
		
		
		return $anli_list;
	
	
	}
	
public function get_pro_list($limit){
	
	$pro_list = db('products')->limit($limit)->order('create_time')->select();
	
	return $pro_list;
	
	}	
public function get_slide_list($limit){
	
	$slide_list = db('slide')->limit($limit)->order('create_time desc')->select();
	
	
	
	return $slide_list;
	
	} 
  
  
  
  
  
}