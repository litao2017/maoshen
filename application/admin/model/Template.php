<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Template extends Model
{
  
  public function update_tpl_id($tpl_id){
	
 		return db('config')->where('id',1)->update(['tpl_id'=>$tpl_id]);  
	  
	  
	  
	  }
  	 
  
  
   
   
  
     
   
   

}