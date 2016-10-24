<?php
namespace app\wap\model;
use think\Model;

use think\Db;
class Order extends Model
{
 /*订单状态 初始：0    付款：1    发货:2    收货:3  退货：4  作废：-1 */   

   /*
   查询订单
   user_id int 用户id
    order_status int 订单状态
 
    */
    public function get_list($user_id,$order_status=''){
        $where="o.user_id = ".$user_id;
        
        if($order_status!==''){
            $order_status=intval($order_status);
            if($order_status==1){
                $where.=" AND o.status in (1,2)";
            }else{
                $where.=" AND o.status=".$order_status;
            }
            
        }
   $page=isset($_GET['page'])?intval($_GET['page']):1;
          //$url=$_SERVER['REQUEST_URI'];//获得当前url地址，最好去掉page参数
          $url=get_url(); 
          $url=preg_replace('/[&?]page=\d+/','',$url); 
        
          $count=Db::name('order')->alias('o')->join('address a','o.address_id = a.address_id')->where($where)->count();
           $enum=2;  //每页多少个
            $pages=ceil($count/$enum);  //总页数
            $page=$page>$pages?$pages:$page;   //修正页数
            $page=$page<1?1:$page; //修正页数
            $start_page=($page-1)*$enum; //起始数
            $limit=" {$start_page},{$enum}";  //sql语句
            $html_page=build_page($count,$enum,$page,$url);  //生成html代码
       // $order_list=Db::name('order')->alias('o')->join('address a','o.address_id = a.address_id')->where($where)->paginate(1,true,['query'=>["keyword"=>$keyword]]);
        $order_list=Db::name('order')->alias('o')->join('address a','o.address_id = a.address_id')->where($where)->field('o.order_id,o.status,o.create_time,o.pay_time,o.shipping_fee,o.amount,o.order_sn,o.kuaidi_name,o.kuaidi_sn,o.is_comment')->limit($limit)->select();
        foreach ($order_list as $key => $value) {
            $order_list[$key]['goods_list']=Db::name('order_goods')->alias('og')->join('goods g','og.goods_id=g.goods_id')->field('og.goods_price,og.goods_name,og.buy_number,g.goods_src')->where('og.order_id='.$value['order_id'])->select();
        }
        //pp($order_list);
        if($order_list){
               
                return ["order_list"=>$order_list,"html_page"=>$html_page];
          
        }
        return false;
 
    }

   public function add_order($data){
      
        Db::name('order')->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
       
    }
    public function update_order($data,$order_id){
        $data['order_id']=$order_id;
        Db::name('order')->update($data);
       
    }
    public function delete_order($order_id){
        
        Db::name('order')->delete($order_id);
       
    }
    /*获得订单商品信息
    order_id 订单id
    */
    public function get_order_info($order_id){
        $order_info=Db::name('order')->where('order_id',$order_id)->find();
        return $order_info;
    }
    /*获得订单详细信息，包括订单商品信息，收货人信息等
    order_id 订单id
    */
   public function get_order_info_all($order_id){
        $order_info=Db::name('order')->alias('o')->join('address a','o.address_id = a.address_id')->where("o.order_id=".$order_id)->find();
        $order_goods=Db::name('order_goods')->alias('og')->join('goods g','og.goods_id = g.goods_id')->where("og.order_id=".$order_id)->select();
         return ['order_info'=>$order_info,'order_goods'=>$order_goods];
   
    }
 
    /*
    得到最新的一条收货地址
    user_id 用户id
     */
    public function get_address($user_id){
        $address_info=Db::name("address")->where("user_id=".$user_id." and is_show=1")->order("create_time desc")->find();
        return $address_info;
    }
   public function get_address_byid($address_id){
        $address_info=Db::name("address")->where("is_show=1 and address_id=".$address_id)->find();
        return $address_info;
    }
    public function get_address_region($parent_id){
        $region=Db::name("region")->where("parent_id=".$parent_id)->select();
        return $region;
    }
    public function add_address($data){
        Db::name("address")->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
    }
 public function update_address($data){
        Db::name("address")->update($data);     
    }
     public function delete_address($address_id){
        $data['address_id']=$address_id;
        $data['is_show']=0;
        Db::name("address")->update($data);
         
    }
    /*
得到收货地址列表
user_id 用户id
 */
    public function get_address_list($user_id){
        return Db::name("address")->where("user_id=".$user_id." and is_show=1")->select(5);
    }
   public function get_goods_info($goods_id){
        $goods_info=Db::name("goods")->find($goods_id);
        return $goods_info;
    }
    public function add_order_goods($data){
        Db::name("order_goods")->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
    }
    /*
    得到未付款相同用户，同商品，同样数量的订单，如果有则进入该订单付款
     */
    public function get_same_order($user_id,$goods_id,$goods_number,$goods_price,$address_id){
        $amount=round($goods_number*$goods_price,2);
        $same_order=Db::name("order")->alias('o')->join('order_goods og','o.order_id=og.order_id')->where("o.status=0 and o.user_id=".$user_id." and og.goods_id=".$goods_id." and og.buy_number=".$goods_number." and o.amount=".$amount)->order('o.order_id desc')->find();
    
        return $same_order['address_id']==$address_id?$same_order:false;
    }
/*
根据用户id获得openid
user_id 用户id
return openid
 */
    public function get_openid_by_userid($user_id){
       
        $user_info=Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->where("u.user_id = ".$user_id)->field('w.openid')->find();
        return $user_info['openid']?$user_info['openid']:'';  
    }
    /*


 */
    public function get_user_info($user_id){
       
        $user_info=Db::name('wechat_user')->alias('w')->join('user u','w.wechat_user_id=u.wechat_user_id')->where("u.user_id = ".$user_id)->find();
        return $user_info;  
    }
    /*
    获得不同状态的订单数量
    user_id 用户id
    status 状态
     */
    static function get_order_num($user_id,$status){
        $count=Db::name("order")->where("status=".$status." and user_id=".$user_id)->count();
        return $count;
    }
/*--------------------------试用订单------------------------*/
/*
得到试用订单
 */
    public function get_order_test($user_id){
        $order_test=Db::name("order_test")->where('user_id='.$user_id)->find();
        return $order_test;
    }

    public function add_order_test($data){
        Db::name('order_test')->insert($data);
        $new_id=Db::getLastInsID();
        return $new_id?$new_id:0;
    }

}