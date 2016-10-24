<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Anli extends Model
{

   /*
   查询用户
   article_id int 文章id
    */
    /*public function get_anli_info($anli_id){
        $anli_info=Db::name('anli')->find($anli_id);
        return $anli_info;
    }*/
	
	public function get_list($limit=15,$cate_id=''){
		
		if($cate_id){
			
		 $where = "cate_id=$cate_id";	
			
			}else{
				
		 $where = "cate_id!=8";			
				}	
		
		 $page=isset($_GET['page'])?intval($_GET['page']):1;
          //$url=$_SERVER['REQUEST_URI'];//获得当前url地址，最好去掉page参数
          $url=get_url(); 
          $url=preg_replace('/[&?]page=\d+/','',$url); 
          //echo $url;exit;
          $count=db('anli')->where($where)->count();
           $enum=$limit;  //每页多少个
            $pages=ceil($count/$enum);  //总页数
            $page=$page>$pages?$pages:$page;   //修正页数
            $page=$page<1?1:$page; //修正页数
            $start_page=($page-1)*$enum; //起始数
            $limit=" {$start_page},{$enum}";  //sql语句
            $html_page=build_page($count,$enum,$page,$url);  //生成html代码

       $list=db('anli')->where($where)->order('create_time desc')->limit($limit)->select();
	   
	  

        return ["list"=>$list,"html_page"=>$html_page];
    
	
		
		}	
		
	
	public function get_cat_list(){
		
		$cat_list = db('anli_cate')->select();
		
		return $cat_list;
		
		}
	
  
}