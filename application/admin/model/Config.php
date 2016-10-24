<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Config extends Model
{
   
 
	public function insert_config($data){
		
	db('config')->insert($data);	
		
		}

    public function update_config($data,$id){
		
        $data['id']=$id;
		
       db('config')->update($data);
       
    }
    public function get_config($id){
		
        $config=db('config')->where('id',$id)->find();
        return $config;
    }
 
   
   public function add_nav($data){
	   
	   db('nav')->insert($data);
	   
	    $new_id=Db::getLastInsID();
        
		return $new_id?$new_id:0;
	   
	   }
        

   public function update_nav($data,$id){
        $data['nav_id']=$id;
        db('nav')->update($data);
       
    }
	
    public function get_nav(){
		
        $nav_list=db('nav')->order('sort')->select();
		
		foreach($nav_list as $k=>$nav){
			
			$n = db('nav')->where('nav_id',$nav['nav_pid'])->find();
			
		$nav_list[$k]['pid_name'] = $n['nav_name'];	
			
			}
		
		
		
        return $nav_list;
    }
	
	public function del_nav($nav_id){
		
		return  db('nav')->where('nav_id',$nav_id)->delete();
		
		}

}
