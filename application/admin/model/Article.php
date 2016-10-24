<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Article extends Model
{
   
    public function get_list($limit=20){
		
   
 $page=isset($_GET['page'])?intval($_GET['page']):1;
          //$url=$_SERVER['REQUEST_URI'];//获得当前url地址，最好去掉page参数
          $url=get_url(); 
          $url=preg_replace('/[&?]page=\d+/','',$url); 
          //echo $url;exit;
		  $where='';
          $count=Db::name('article')->where($where)->count();
           $enum=$limit;  //每页多少个
            $pages=ceil($count/$enum);  //总页数
            $page=$page>$pages?$pages:$page;   //修正页数
            $page=$page<1?1:$page; //修正页数
            $start_page=($page-1)*$enum; //起始数
            $limit=" {$start_page},{$enum}";  //sql语句
            $html_page=build_page($count,$enum,$page,$url);  //生成html代码

       $list=db('article')->alias('a')->join('article_cate c','c.cate_id=a.cate_id','left')->where($where)->order('article_id desc')->limit($limit)->select();
	   
	  

        return ["list"=>$list,"html_page"=>$html_page];
    
       /* $Admin = new Admin;
        $Admin->data(['user_name'=>'Thinkphp','email'=>'thinkphp@qq.com']);

        $Admin->save();*/
    }

    public function add_article($data){
        Db::name('article')->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
    }
    public function update_article($data,$article_id){
        $data['article_id']=$article_id;
        Db::name('article')->update($data);
       
    }
    public function delete_article($article_id){
        
      $del =   Db::name('article')->delete($article_id);
       return $del;
	   
    }
    public function get_article_info($article_id){
        $article_info=Db::name('article')->where('article_id',$article_id)->find();
        return $article_info;
    }
/*---------------------分类---------------------*/

	 public function get_cat_list(){
		 
        $cat_list=Db::name("article_cate")->order('cate_id desc')->select();
		
			foreach($cat_list as $k=>$cate){
			
		$c = db("article_cate")->where('cate_id',$cate['cate_pid'])->find();
			
		$cat_list[$k]['pid_name']=	$c['cate_name'];
			
			}
        /*  foreach ($cate_list as $key => $value) {
            $id=$value['cate_id'];
            $pid=$value['cate_pid'];
            if($pid==0){
              $new_list[$id]=$value;
            }else{
               $new_list[$pid]['child'][]=$value;
            }
            
          }*/
          return $cat_list;
    }
public function get_article_cat_info($cate_id){
	
   $article_cat_info=db("article_cate")->where('cate_id',$cate_id)->find();
   
   return   $article_cat_info;

}
 public function update_cat($data,$cate_id){
    $data['cate_id']=$cate_id;
    Db::name("article_cate")->update($data);
 }
 public function add_cat($data){
    Db::name("article_cate")->insert($data);
    return Db::getLastInsID();
 }
  public function del_cat($cate_id){
    Db::name("article_cate")->delete($cate_id);
 }



}