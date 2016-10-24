<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Products extends Model
{


	public function get_pro_list(){
		
	
	$pro_list = db('products')->select();
		
	return 	$pro_list;
		
		
		
		
		
		}
  
  
    public function get_pro_info($pro_id){
        $pro_info=db('products')->find($pro_id);
        return $pro_info;
    }

   
   

  
  
}