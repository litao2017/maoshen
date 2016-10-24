<?php
namespace app\wap\model;
use think\Model;
use think\Db;
class Money extends Model
{
public $shop_config=[];

  /*
  设置商城配置
   */
  public function set_shop_config($shop_config){
    $this->shop_config=$shop_config;
  }
/*
得到可提现金额
 */
  public function get_sure_fencheng($user_id){
    $fencheng=Db::name("user")->field('sure_fencheng')->find($user_id);
    return $fencheng?$fencheng['sure_fencheng']:0;
  }
/*
添加提现账户信息
 */
   public function add_tx_info($data){
    Db::name("tx_info")->insert($data);
    return Db::getLastInsID();
  }
  /*
  添加提现信息
   */
  public function add_money_send($data){
    $this->del_sure_money($data['user_id'],$data['money']);
    Db::name("money_send")->insert($data);
    return Db::getLastInsID();
  }
  public function del_sure_money($user_id,$money){
    //应该记录日志
    Db::name("user")->where('user_id='.$user_id)->setDec('sure_fencheng',$money);
    $this->money_log($money,$user_id,0,'提现');
    return Db::getLastInsID();
  }

  public function money_log($money,$send_user,$get_user,$content=''){

    $data['create_time']=time();
    $data['send_user']=$send_user;
    $data['get_user']=$get_user;  //0为系统
    $data['money']=$money;
    $data['content']=$content;
    Db::name("money_log")->insert($data);
  }
/*
获得提现列表
user_id 会员id
return 列表数据
 */
  public function get_sendmoney_list($user_id){
    $list=Db::name("money_send")->where("user_id=".$user_id)->order('money_send_id desc')->paginate(10);
    return $list;
  }
}