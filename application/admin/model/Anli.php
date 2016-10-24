<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Anli extends Model
{
   
   
     public function get_list($limit=20){
		
   
 $page=isset($_GET['page'])?intval($_GET['page']):1;
          //$url=$_SERVER['REQUEST_URI'];//获得当前url地址，最好去掉page参数
          $url=get_url(); 
          $url=preg_replace('/[&?]page=\d+/','',$url); 
          //echo $url;exit;
		  $where='';
          $count=Db::name('anli')->where($where)->count();
           $enum=$limit;  //每页多少个
            $pages=ceil($count/$enum);  //总页数
            $page=$page>$pages?$pages:$page;   //修正页数
            $page=$page<1?1:$page; //修正页数
            $start_page=($page-1)*$enum; //起始数
            $limit=" {$start_page},{$enum}";  //sql语句
            $html_page=build_page($count,$enum,$page,$url);  //生成html代码

       $list=db('anli')->join('anli_cate','ms_anli_cate.cate_id=ms_anli.cate_id','left')->where($where)->order('anli_id desc')->limit($limit)->select();
	   
	  

        return ["list"=>$list,"html_page"=>$html_page];
    
       /* $Admin = new Admin;
        $Admin->data(['user_name'=>'Thinkphp','email'=>'thinkphp@qq.com']);

        $Admin->save();*/
    }

    public function add_anli($data){
       
            
        Db::name('anli')->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
    }
    public function update_anli($data,$anli_id){

        $data['anli_id']=$anli_id;
        Db::name('anli')->update($data);
       
    }
    public function delete_anli($anli_id){
        
        Db::name('anli')->delete($anli_id);
       
    }
    public function get_anli_info($anli_id){
        $anli_info=Db::name('anli')->where('anli_id',$anli_id)->find();
        return $anli_info;
    }
/*---------分类-----------*/
    public function get_cate_list(){
		
        $cate_list=Db::name("anli_cate")->order('cate_id desc')->select();
		
		foreach($cate_list as $k=>$cate){
			
		$c = db("anli_cate")->where('cate_id',$cate['cate_pid'])->find();
			
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
public function get_anli_cate_info($cate_id){
    return Db::name("anli_cate")->find($cate_id);

}
 public function update_cate($data,$cate_id){
    $data['cate_id']=$cate_id;
    Db::name("anli_cate")->update($data);
 }
 public function add_cate($data){
    Db::name("anli_cate")->insert($data);
    return Db::getLastInsID();
 }
  public function del_cate($cate_id){
    Db::name("anli_cate")->delete($cate_id);
 }
}