<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Article extends Model
{
public $config=[];

  /*
  设置商城配置
   */
  public function set_config($config){
    $this->config=$config;
  }

   /*
   查询用户
   article_id int 文章id
    */
    public function get_article_info($article_id){
        $article_info=db('article')->where('article_id',$article_id)->find();
        return $article_info;
    }
	 public function get_next_prev_article($article_id,$is_next=1){
        if($is_next){
            return Db::name("article")->where("article_id>".$article_id," and status=1")->order('article_id asc')->find();
        }else{
            return Db::name("article")->where("article_id<".$article_id," and status=1")->order('article_id desc')->find();
        }
     }
	public function get_cat_article_list($cate_id,$limit=''){
		
	$article_list=db('article')->where('cate_id',$cate_id)->where('status',1)->order('create_time desc')->limit($limit)->select();
        
		return $article_list;
		
		}
	
	public function get_cat_list($cat_pid=0){
		
		
		 $cat_list=db('article_cate')->where('cate_pid',$cat_pid)->select();
        
		return $cat_list;
		
		
		}
	
	public function get_cate_info($id){
		
		$cate = db('article_cate')->where('cate_id',$id)->find();
		
		
		return $cate;
		}
	
	
	
	
	public function get_list($limit=15,$cate_id=0){
		
		if($cate_id){
		 $where = "cate_id=$cate_id and status=1";	
			}else{
		 $where = "cate_id!=8 and status=1";			
				}	
		
		// $page=isset($_GET['page'])?intval($_GET['page']):1;
         $page=input('page',1,'intval');

          //$url=$_SERVER['REQUEST_URI'];//获得当前url地址，最好去掉page参数
          $url=get_url(); 
          $url=preg_replace('/[&?]page=\d+/','',$url); 
          //echo $url;exit;
          $count=db('article')->where($where)->count();
           $enum=$limit;  //每页多少个
            $pages=ceil($count/$enum);  //总页数
            $page=$page>$pages?$pages:$page;   //修正页数
            $page=$page<1?1:$page; //修正页数
            $start_page=($page-1)*$enum; //起始数
            $limit=" {$start_page},{$enum}";  //sql语句
            if(input('is_html')){
                 $html_page=page($page,$count,$enum,5,'lists_'.$cate_id);
             }else{
                 $html_page=build_page($count,$enum,$page,$url);  //生成html代码
             }
          
           
       $list=db('article')->where($where)->order('create_time desc')->limit($limit)->select();
	   
	  

        return ["list"=>$list,"html_page"=>$html_page];
    
		
		
		
		
		
		
		
		
		
		
		
		
		}	
	
	
	
	
	
	
	
	
	
	
	
	
  
}