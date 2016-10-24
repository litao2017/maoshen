<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Imgtext extends Model
{

   /*
   查询用户
   article_id int 文章id
    */
    /*public function get_anli_info($anli_id){
        $anli_info=Db::name('anli')->find($anli_id);
        return $anli_info;
    }*/
	
	
	public function get_cat_img_text_list($cate_id){
		
		$list = db('img_text')->where('cate_id',$cate_id)->order('create_time')->select();
		
		
		return $list;
		}
	
	public function get_cat_list(){
		
		$cat_list = db('img_text_cate')->select();
		
		return $cat_list;
		
		}
	public function get_info($id){
		
		$info = db('img_text')->where('it_id',$id)->find();
		
		return $info;
		
		
		}
	
	
	


  
}