<?php
namespace app\admin\model;
use think\Model;
use app\admin\model\Common;
use think\Db;

class User extends Common
{
     protected $autoWriteTimestamp = true;
   public function __construct(){
    parent::_initialize();
       
   }
    public function add_user(){
        $user = new User;
        $user->data(['user_name'=>'Thinkphp','email'=>'thinkphp@qq.com']);

        $user->save();
    }
   /*
   得到会员列表
   keyword 关键字，可以会员号，微信昵称
    user_status 会员等级
    field 倒序排序字段
    */
    public function get_list($keyword='',$user_status='',$field='',$birthday=0){
        $where=" 1 ";
        $order='user_id desc';
        if($keyword){
            if(is_numeric($keyword)){
                $where.=" AND u.user_name like '%".$keyword."%'";
            }else{
      
                    $where.=" AND w.nickname like '%".$keyword."%'";
                }
            
        }
        if($user_status!==''){
            $user_status=intval($user_status);
            $where.=" AND user_level=".$user_status;
        }
         if($birthday){
          //查询生日
          //  系统默认保存的是  $year=2016;
           $year=2016;
           $month_day=date('m-d',time());
           $birthday=strtotime($year.'-'.$month_day);
            $where.=" AND birthday=".$birthday;
        }
      if($field!==''){
          
            $order=" $field desc ";

        }
           $page=isset($_GET['page'])?intval($_GET['page']):1;
          //$url=$_SERVER['REQUEST_URI'];//获得当前url地址，最好去掉page参数
          $url=get_url(); 
          $url=preg_replace('/[&?]page=\d+/','',$url); 
          //echo $url;exit;
          $count=Db::name('user')->alias('u')->join('wechat_user w','u.wechat_user_id = w.wechat_user_id')->where($where)->count();
           $enum=20;  //每页多少个
            $pages=ceil($count/$enum);  //总页数
            $page=$page>$pages?$pages:$page;   //修正页数
            $page=$page<1?1:$page; //修正页数
            $start_page=($page-1)*$enum; //起始数
            $limit=" {$start_page},{$enum}";  //sql语句
             $html_page=build_page($count,$enum,$page,$url);  //生成html代码
            $user_list=Db::name('user')->alias('u')->join('wechat_user w','u.wechat_user_id = w.wechat_user_id')->where($where)->order($order)->limit($limit)->select();
           


      
      //  $user_list=Db::name('user')->where($where)->paginate(10);

        if($user_list){
               
                return ["user_list"=>$user_list,"html_page"=>$html_page];
          
        }
        return false;
 
    }
/*
得到升级会员列表
 */
   public function get_up_user_list($keyword=''){
    $where='1';
    $order='up_id desc';
      if($keyword){
          if(is_numeric($keyword)){
              $where.=" AND u.user_name like '%".$keyword."%'";
          }else{
              $where.=" AND w.nickname like '%".$keyword."%'";
          }
      }
 $page=isset($_GET['page'])?intval($_GET['page']):1;
          //$url=$_SERVER['REQUEST_URI'];//获得当前url地址，最好去掉page参数
          $url=get_url(); 
          $url=preg_replace('/[&?]page=\d+/','',$url); 
          //echo $url;exit;
          $count=Db::name('up_user')->alias('uu')->join('user u','uu.user_id=u.user_id')->join('wechat_user w','u.wechat_user_id = w.wechat_user_id')->where($where)->count();
           $enum=20;  //每页多少个
            $pages=ceil($count/$enum);  //总页数
            $page=$page>$pages?$pages:$page;   //修正页数
            $page=$page<1?1:$page; //修正页数
            $start_page=($page-1)*$enum; //起始数
            $limit=" {$start_page},{$enum}";  //sql语句
             $html_page=build_page($count,$enum,$page,$url);  //生成html代码
            $user_list=Db::name('up_user')->alias('uu')->join('user u','uu.user_id=u.user_id')->join('wechat_user w','u.wechat_user_id = w.wechat_user_id')->where($where)->order($order)->limit($limit)->select();
             if($user_list){
                return ["user_list"=>$user_list,"html_page"=>$html_page];
        }
   }
    public function update_user($data,$user_id){
        $data['user_id']=$user_id;
        Db::name('user')->update($data);
       
    }
    public function delete_user($user_id){
        
        Db::name('user')->delete($user_id);
       
    }
    /*获得订单商品信息
    user_id 订单id
    */
    public function get_user_info($user_id){
        $user_info=Db::name('user')->alias('u')->join('wechat_user w','u.wechat_user_id = w.wechat_user_id')->where("u.user_id=".$user_id)->find();
        return $user_info;
    }
    /*获得用户详细信息，包括基本信息、上下级关系等
    user_id 订单id
    */
    public function get_user_info_all($user_id){
        $user_info=$this->get_user_info($user_id);
        //$user_order=Db::name('order')->where("user_id=".$user_id)->select();
        //一级数量
        $childs_count['child1']=Db::name('user')->where("parent_id=".$user_id)->count();
        //二级数量
        $childs_count['child2']=Db::name('user')->where("parent2_id=".$user_id)->count();
        //三级数量
        $childs_count['child3']=Db::name('user')->where("parent3_id=".$user_id)->count();
        //上级信息
         $parents_info['parent']=$this->get_user_info($user_info['parent_id']);
         //上上级信息
         $parents_info['parent2']=$this->get_user_info($user_info['parent2_id']);
         //上级信息
         $parents_info['parent3']=$this->get_user_info($user_info['parent3_id']);
         return ['user_info'=>$user_info,'childs_count'=>$childs_count,'parents_info'=>$parents_info];
   
    }
/*
用户等级升级
 */
 public function up_user_level($user_id,$level){
    $data['user_id']=$user_id;
    $data['user_level']=$level;
  //更新用户等级
    if($level==1){
      //分销商正常升级
      Db::name("user")->update($data);
    }elseif($level==2 || $level==3){
      //如果升为城市合伙人或者全球那
     
      $data['parent_id']=0;
      $data['parent2_id']=0;
      $data['parent3_id']=0;
      $user_info=$this->get_user_info($user_id);
      $data['tuan_id']=$user_info['parent_id'];
      $data['before_up_tuan_money']=$user_info['tuan_money'];
      Db::name("user")->update($data);//更新自己
      Db::name("user")->where("parent_id=".$user_id)->update(["parent2_id"=>0,"parent3_id"=>0]);//将原来1级是他的，2、3级都设为0
      Db::name("user")->where("parent2_id=".$user_id)->update(["parent3_id"=>0]); //将原来2级是他的，3级都设为0
     
   }
 }
   /*
   获得上级id
    */
   public function get_parent_id($user_id){
      $info=Db::name("user")->field('parent_id')->find($user_id);
      return $info?$info['parent_id']:0;
   }

   public function checke_user($user_id){
    $data['is_show_fx']=1;
    $data['user_id']=$user_id;
    Db::name("user")->update($data);
   }
    
}