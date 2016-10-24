<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
/*订单状态 初始：0    付款：1    发货:2*/
class User extends Admin {
    public $user_status;
    public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\User();
        $this->user_status=[$this->shop_config['default_name'],$this->shop_config['level_name'],$this->shop_config['level2_name'],$this->shop_config['level3_name']];
    }
    function showlist(){
     // echo strtotime('2016-06-19');
      $keyword='';
      $field='';
      $status='';
      $birthday=0;
      if(isset($_GET['status'])){
        $keyword=Request::instance()->param('keyword','','addslashes'); 
       $status=Request::instance()->param('status','','addslashes'); 
       $field=Request::instance()->param('field','','addslashes'); 
       $birthday=Request::instance()->param('birthday',0,'intval'); 
    //   pp($_GET);
      }
      
        $user=$this->Model->get_list($keyword,$status,$field,$birthday);

        $this -> assign('user_list', $user['user_list']);
        $this -> assign('html_page', $user['html_page']);
        $this -> assign('user_status', $this->user_status);
     //   pp($this->shop_config);
         return $this->fetch();
    }
    

/* public function get_info_ajax(){
        $user_id=Request::instance()->post('user_id',0,'intval'); 
        if(empty($user_id)){
          echo '{"error":1}';
          exit;
        }
        $user=$this->Model->get_user_info($user_id);
       $json=new Json();
       die($json->encode($user));
 }*/

  public  function edit(){
         if(!empty($_POST)){
            $user_id=Request::instance()->post('user_id',0,'intval'); 
            $act=Request::instance()->post('act','','addslashes'); 
            $Fencheng=new \app\admin\model\Fencheng();
            if($act=='pay'){
              //付款
              $data['status']=1;
              $user=$this->Model->get_user_info($user_id);
              //各级得到相应的未确认积分
              $Fencheng->give_parents_nosure_fencheng($user);
            }elseif ($act=='invalid') {
             //作废
             $data['status']=-1;
            }elseif ($act=='send') {
              //发货
              $data['status']=2;
            }elseif ($act=='over') {
              //收货确认
               //各级得到相应的确认积分
              $Fencheng->give_parents_sure_fencheng($user);
              $data['status']=3;
            }elseif ($act=='back') {
              //退货
              $data['status']=4;
            }
            $data['user_price']=Request::instance()->post('user_price','','floatval'); 
            $data['fencheng_price']=Request::instance()->post('fencheng_price','','floatval'); 
            $data['sales']=Request::instance()->post('sales',0,'intval'); 
            $data['user_number']=Request::instance()->post('user_number',1000,'intval'); 
            $data['user_content']=Request::instance()->post('user_content','','htmlspecialchars'); 
            if($user_id){
                //如果有user_id则更新
                $this->Model->update_user($data,$user_id);
                $this->error('更新成功','showlist');
            }
             
         }else{
            $user_id=Request::instance()->param('user_id','','intval');
            $user=[];
            if($user_id){
                $user=$this->Model->get_user_info_all($user_id);
              // pp($user);
            }
             $this->assign("user",$user['user_info']);//用户信息
             $this->assign("childs_count",$user['childs_count']);//下级数量
             $this->assign("parents_info",$user['parents_info']);//上级信息
             $this -> assign('user_status', $this->user_status);
            // $this->assign("shop_config",$this->Model->shop_config);//系统配置信息
            return $this->fetch();
         }
     
    }

    public function up_level(){
        $level=Request::instance()->param('level','','intval');
        $user_id=Request::instance()->param('user_id','','intval');
        $user_info=$this->Model->get_user_info($user_id);
        $this->Model->up_user_level($user_id,$level);
        $this->Model->log('将用户['.$user_id.']升级为'.$level);
        //lt 模版通知
          $WechatApi=new \think\WechatApi(["access_token"=>$this->access_token]);
      
        
                 $tp_id=$this->shop_config['tpl_gradechange'];
 
                 $data=array(
                      "touser"=>$user_info['openid'],
                  "template_id"=>$tp_id,

                  "data"=>array(
                   // "first"=>array("value"=>"订单支付成功！感谢您的支持！","color"=>"#173177"),
                   // "first"=>array("value"=>"恭喜注册成功","color"=>"#173177"),
                    "keyword1"=>array("value"=>$this->user_status[$user_info['user_level']],"color"=>"#173177"),
                    "keyword2"=>array("value"=>$this->user_status[$level],"color"=>"#173177"),
                        )
                      );
                  $WechatApi->sendTemplateMessage($data);
        $this->error('成功');
    }
    //升级用户列表
  public function up_user_list(){
    $keyword='';
     $keyword=Request::instance()->param('keyword','','addslashes'); 
     $user=$this->Model->get_up_user_list($keyword);

        $this -> assign('user_list', $user['user_list']);
        $this -> assign('html_page', $user['html_page']);
        $this -> assign('user_status', $this->user_status);
        return $this->fetch();
  }
//异步添加备注
  public function ajax_add_note(){
    $user_id=Request::instance()->post('user_id',0,'intval'); 
    $data['note']=Request::instance()->post('note','','addslashes'); 
    if($user_id && $data['note']){
       $this->Model->update_user($data,$user_id);
       $json_arr['error']=0;
       $json_arr['msg']='备注成功';
     }else{
      $json_arr['error']=1;
       $json_arr['msg']='备注失败';
     }
   echo json_encode($json_arr);
  }

  public function check_all(){
     $s=isset($_POST['s'])?addslashes($_POST['s']):'';
      if($s){
        $s=rtrim($s,"|");
        $arr=array();
        $arr=explode("|",$s);
        $success_id='';
        $success_count=0;
        $count=count($arr);
        for($i=0;$i<$count;$i++){
          $this->Model->checke_user($arr[$i]);
          $success_id.=$arr[$i].',';
          $success_count++;
        }
        echo '成功处理id：'.$success_id.'  个数:'.$success_count;
        exit;
      }
  }
}

