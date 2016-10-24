<?php
namespace app\admin\model;
use think\Model;
use app\admin\model\Common;
use think\Db;
class Database extends Common
{
    
   public function __construct(){
    parent::_initialize();
       
   }
   /*
 
   keyword 关键字，可以会员号，微信昵称
    money_status 会员等级
    field 倒序排序字段
    */
    public function get_list($keyword='',$limit=20){
       
	    $where=" 1 ";
    
        if($keyword){
            $where.=" AND db_name like '%".$keyword."%'";
        }
   
 $page=isset($_GET['page'])?intval($_GET['page']):1;
          //$url=$_SERVER['REQUEST_URI'];//获得当前url地址，最好去掉page参数
          $url=get_url(); 
          $url=preg_replace('/[&?]page=\d+/','',$url); 
          //echo $url;exit;
          $count=Db::name('database')->where($where)->count();
           $enum=$limit;  //每页多少个
            $pages=ceil($count/$enum);  //总页数
            $page=$page>$pages?$pages:$page;   //修正页数
            $page=$page<1?1:$page; //修正页数
            $start_page=($page-1)*$enum; //起始数
            $limit=" {$start_page},{$enum}";  //sql语句
            $html_page=build_page($count,$enum,$page,$url);  //生成html代码

       $list=db('database')->where($where)->order('db_id desc')->limit( $limit)->select();
	   
	  

        return ["list"=>$list,"html_page"=>$html_page];
    
	}
	
	
  public function add_database($data){
    Db::name('database')->insert($data);
    $new_id=Db::getLastInsID();
    return $new_id?$new_id:0;
  }
  
  
  public function get_db($db_id){
	  
	  $data = db('database')->where('db_id',$db_id)->value('db_name'); 
	  
	  return $data;
	  
	  
	  
	  }
  public function del_db($db_id){
	  
	$data = db('database')->where('db_id',$db_id)->delete();   
	  
	  
	  } 
  
  
  
  
  
  
  
  
}