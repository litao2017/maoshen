<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Imgtext extends Model
{
   
   
     public function get_list($limit=20){
		
   
 $page=isset($_GET['page'])?intval($_GET['page']):1;
          //$url=$_SERVER['REQUEST_URI'];//获得当前url地址，最好去掉page参数
          $url=get_url(); 
          $url=preg_replace('/[&?]page=\d+/','',$url); 
          //echo $url;exit;
		  $where='';
          $count=Db::name('img_text')->where($where)->count();
           $enum=$limit;  //每页多少个
            $pages=ceil($count/$enum);  //总页数
            $page=$page>$pages?$pages:$page;   //修正页数
            $page=$page<1?1:$page; //修正页数
            $start_page=($page-1)*$enum; //起始数
            $limit=" {$start_page},{$enum}";  //sql语句
            $html_page=build_page($count,$enum,$page,$url);  //生成html代码


       		$list=db('img_text')->alias('i')->join('img_text_cate c','c.cate_id=i.cate_id','left')->where($where)->order('create_time desc')->limit($limit)->select();
	   
	  

        return ["list"=>$list,"html_page"=>$html_page];
    
       /* $Admin = new Admin;
        $Admin->data(['user_name'=>'Thinkphp','email'=>'thinkphp@qq.com']);

        $Admin->save();*/
    }

    public function add_img_text($data){
       
            
        Db::name('img_text')->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
    }
    public function update_img_text($data,$id){

        $data['it_id']=$id;
        Db::name('img_text')->update($data);
       
    }
    public function delete_img_text($it_id){
        
        $del = Db::name('img_text')->delete($it_id);
       
	   return $del;
	   
    }
	
    public function get_img_text_info($id){
        $info=Db::name('img_text')->where('it_id',$id)->find();
        return $info;
    }
/*---------分类-----------*/
    public function get_cate_list(){
        $cate_list=Db::name("img_text_cate")->order('cate_id desc')->select();
		
		foreach($cate_list as $k=>$cate){
			
		$c = db("img_text_cate")->where('cate_id',$cate['cate_pid'])->find();
			
		$cate_list[$k]['pid_name']=	$c['cate_name'];
			
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
          return $cate_list;
    }
public function get_cate_info($cate_id){
    return Db::name("img_text_cate")->find($cate_id);

}
 public function update_cate($data,$cate_id){
    $data['cate_id']=$cate_id;
    Db::name("img_text_cate")->update($data);
 }
 public function add_cate($data){
    Db::name("img_text_cate")->insert($data);
    return Db::getLastInsID();
 }
  public function del_cate($cate_id){
    Db::name("img_text_cate")->delete($cate_id);
 }
}