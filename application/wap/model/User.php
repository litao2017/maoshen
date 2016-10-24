<?php
namespace app\wap\model;
use think\Model;
use think\Db;
class User extends Model
{
public $shop_config=[];

  /*
  设置商城配置
   */
  public function set_shop_config($shop_config){
    $this->shop_config=$shop_config;
  }

   /*
   查询用户
   user_id int 用户id
 has_wx 是否获得微信信息 1是 0否
    */
    public function get_user_info($user_id,$has_wx=0){
       if($has_wx){
       $user_info=Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->where("u.user_id = ".$user_id)->find();
      }else{
        $user_info=Db::name('user')->find($user_id);
      }
       
        return $user_info;
    }

      

    public function get_user_level($user_id){
            $user_level=Db::name('user')->field('user_level')->find($user_id);
        return $user_level['user_level']?$user_level['user_level']:0;
    }

     public function update_user_info($user_id,$data){
            $data['user_id']=$user_id;
            Db::name("user")->update($data);
    }
/*
如果有获得用户的ticket
user_id 用户id
 */
    public function get_qrcode_ticket($user_id){
        $ticket=Db::name('qrcode')->field('ticket')->where("user_id=".$user_id)->find();
        return $ticket['ticket']?$ticket['ticket']:'';
    }

    public function add_qrcode($user_id,$ticket,$scene_id,$qrcode_img){
        $qrcode_info=$this->get_qrcode_info_byuser($user_id);
        $data['ticket']=$ticket;
         $data['scene_id']=$scene_id;
          $data['qrcode_img']=$qrcode_img;
        if($qrcode_info){
          //如果该用户已有，则更新
         
          $data['qrcode_id']=$qrcode_info['qrcode_id'];
          Db::name("qrcode")->update($data);
        }else{
          $data['create_time']=time();
          $data['user_id']=$user_id;
          Db::name("qrcode")->insert($data);

        }
    }
/*
得到用户二维码数据信息
user_id 用户id
 */
   public function get_qrcode_info_byuser($user_id){
        $qrcode_info=Db::name('qrcode')->where("user_id=".$user_id)->find();
        return $qrcode_info;
    }
/*
获得下级列表
user_id 用户id
level 获得下级的级别 1获得一级，2是获得二级，3是获得三级
 */
    public function get_fans_list($user_id,$level,$search_level=1,$limit='10'){
      if($level==1){
      //  $fans_list=Db::name('user')->limit($limit)->select();
       // pp($fans_list);
       $fans_list=Db::name('user')->alias('u')->join('wechat_user w','u.wechat_user_id = w.wechat_user_id')->where("parent_id=".$user_id." and user_level=".$search_level)->limit($limit)->order('user_id desc')->select();
      }elseif($level==2){
        $fans_list=Db::name('user')->alias('u')->join('wechat_user w','u.wechat_user_id = w.wechat_user_id')->where("parent2_id=".$user_id." and user_level=".$search_level)->limit($limit)->order('user_id desc')->select();
      }elseif($level==3){

        $fans_list=Db::name('user')->alias('u')->join('wechat_user w','u.wechat_user_id = w.wechat_user_id')->where("parent3_id=".$user_id." and user_level=".$search_level)->limit($limit)->order('user_id desc')->select();

      }else{
        $fans_list=[];
      }
      return $fans_list;
    }
/*
获得下级订单列表
user_id 用户id
level 获得下级的级别 1获得一级，2是获得二级，3是获得三级
 */
    public function get_fans_order_list($user_id,$level,$field='order_id',$limit='10'){
      $order_by=' o.'.$field.' desc';
      if($level==1){
   
        $fans_order_list=Db::name('order')->alias('o')->join('user u','o.user_id = u.user_id')->where("o.parent_id=".$user_id." and o.status in (1,2,3)")->field('u.user_name,o.order_sn,o.amount,o.fencheng_price,o.parent_level,o.parent2_level,o.parent3_level,status,pay_time')->order($order_by)->limit($limit)->select();
      }elseif($level==2){
      
        $fans_order_list=Db::name('order')->alias('o')->join('user u','o.user_id = u.user_id')->where("o.parent2_id=".$user_id." and o.status in (1,2,3)")->field('u.user_name,o.order_sn,o.amount,o.fencheng_price,o.parent_level,o.parent2_level,o.parent3_level,status,pay_time')->order($order_by)->limit($limit)->select();
      }elseif($level==3){
      
        $fans_order_list=Db::name('order')->alias('o')->join('user u','o.user_id = u.user_id')->where("o.parent3_id=".$user_id." and o.status in (1,2,3)")->field('u.user_name,o.order_sn,o.amount,o.fencheng_price,o.parent_level,o.parent2_level,o.parent3_level,status,pay_time')->order($order_by)->limit($limit)->select();
      }else{
        $fans_order_list=[];
      }
   
  
  
      return $fans_order_list;
    }
    /*
    得到订单列表
     */
    public function get_order_list($user_id,$field='order_id',$limit='10'){
      $order_by=' o.'.$field.' desc';

      
        $order_list=Db::name('order')->alias('o')->join('user u','o.user_id = u.user_id')->where("o.user_id=".$user_id." and o.status in (1,2,3)")->field('u.user_name,o.order_sn,o.amount,o.fencheng_price,o.parent_level,o.parent2_level,o.parent3_level,status,pay_time')->order($order_by)->limit($limit)->select();
    
   
  
  
      return $order_list;
    }
    /*获得分成百分比
    level 用户所在的等级级别，如普通分销，全球合伙人
    parent_level 用户分成级别，如一级，二级
     */
    public function get_fencheng_percent($level,$parent_level){
/*pp($this->shop_config['level_1']);exit;*/

        if($level==1){
            //普通分销
            
             return $this->shop_config['level_'.$parent_level];
        }elseif ($level==2) {
            //城市
            return $this->shop_config['city_level_'.$parent_level];
        }elseif ($level==3) {
           //全球
           return $this->shop_config['global_level_'.$parent_level];
        }else{
            return 0;
        }
        
    }
/*
获得各级粉丝数量
user_id 用户id
level 需要获得粉丝的等级
return int 粉丝数
 */
    public function get_fans_num($user_id,$level){
       if($level==1){
            $where='parent_id='.$user_id;
        }elseif ($level==2) {
            $where='parent2_id='.$user_id;
        }elseif ($level==3) {
           $where='parent3_id='.$user_id;
        }
      return Db::name("user")->where($where)->count();
    }

    public function add_up_user($data){
      Db::name("up_user")->insert($data);
    }

    public function get_up_user($user_id){
      $info=Db::name("up_user")->where("user_id=".$user_id)->find();
      return $info;
    }

/*
获得私信数量
 */
    public function get_message_num($user_id){
      $count=Db::name("message")->where("get_id=".$user_id." and is_view=0")->count();
      return $count;
    }
}