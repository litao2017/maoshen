<?php
namespace app\wap\controller;
use app\wap\controller\Wechat;
use think\Request;
/*订单状态 初始：0    付款：1    发货:2    收货:3  退货：4  作废：-1 */
class Order extends Wechat
{
    public $Model;
    public $order_status=["未付款","已付款","已发货","已收货","退货","作废"];
    public function _initialize(){
        //如果自己有构造函数则执行自己的构造函数，如果没有则执行父级的构造函数
        parent::_initialize();
        $this->Model=new \app\wap\model\Order();
    }
    public function index()
    {
       
        $user_id=$this->user_id;
        $s=input('s/d');
        $order=$this->Model->get_list($user_id,$s);
        $this->assign("s",$s);
        $this->assign("order_list",$order['order_list']);
        $this->assign("html_page",$order['html_page']);
        $this->assign("shop_config",$this->shop_config);
  
        return $this->fetch();
    }

    public function makeorder(){
        
        $user_id=$this->user_id;
        $address_info=$this->Model->get_address($user_id);//地址信息

        //lt:使用微信收货信息
          $reg='/MicroMessenger\/(\d*)/';
          preg_match($reg,$_SERVER['HTTP_USER_AGENT'],$out); //判断微信版本号。版本>5.0启用改功能
         if($out[1]>=5 && !$address_info){

             $options=['appid'=>$this->appid,"appsecret"=>$this->appsecret];
            $address_sign=get_address_sigin($options);
            $this->assign("addrSign",$address_sign);
            $this->assign("show_addrSign",1);//显示微信收货地址
         }
        //lt：end
        
        $goods_id=input('id/d');

        $sign_package=get_sign_package($this->shop_config);
        $this->assign("signPackage",$sign_package);
         
        if($goods_id){
       
            //s:用来验证是否是通过自己的表单提交的
            $postcode=mt_rand(1000,9999);
            cookie("postcode_makeorder",$postcode);
            $this->assign("postcode",$postcode);
            //end
            
            
            if(!$address_info){
                //如果没有先获得省份
                $provinces=$this->Model->get_address_region(1);
                $this->assign("provinces",$provinces);
            }
            $goods_info=$this->Model->get_goods_info($goods_id); //商品信息
            $this->assign("address_info",$address_info);
            $this->assign("goods_info",$goods_info);
            
            //获得地址
            return $this->fetch();
        }
        
    }

    public function get_city(){
        $pid=isset($_POST['pid'])?intval($_POST['pid']):0;
        if($pid){
               $citys=$this->Model->get_address_region($pid);
               $options="";
            if($citys){
                
                foreach($citys as $val){
                    $options.='<option value="'.$val['region_id'].'">'.$val['region_name'].'</option>';
                }
            }
           //file_put_contents('ll.log',$options);
            echo $options;
            exit;
        }
     
    }

    public function addorder(){
        $user_id=$this->user_id;
        if(!empty($_POST)){
            //lt:判断这个人是不是已经有过未付款相同用户，同商品，同样数量的订单，如果有则进入该订单付款
            $goods_id=Request::instance()->post("goods_id",0,"intval");
            $goods_number=Request::instance()->post("goods_number",0,"intval");
            $goods_info=$this->Model->get_goods_info($goods_id);
            $order['address_id']=Request::instance()->post("address_id",0,"intval");
            if($order['address_id']){
               $same_order=$this->Model->get_same_order($user_id,$goods_id,$goods_number,$goods_info['goods_price'],$order['address_id']);
              if($same_order){
                   header('Location:/wap/wechatpay/index/order_name/'.$goods_info['goods_name'].'/order_sn/'.$same_order['order_sn']);
                   exit;
              }
            }
           
            //end
            
            $postcode=Request::instance()->post("postcode",'',"addslashes");
            if(cookie("postcode_makeorder")!=$postcode){
                js_alert('订单提交失败');//cookie验证提交是否为正常提交，防止恶意提交
            }
           
            
            if(!$goods_id || !$goods_number){
                $this->error('订单提交失败，请选择商品数量');
            
            }

            if(!$order['address_id']){
                //如果之前有地址记录
   
                //如果之前没有地址记录
                $data['consignee']=Request::instance()->post("consignee",'',"addslashes");
                $data['tel']=Request::instance()->post("tel",'',"addslashes");
                $data['address']=Request::instance()->post("address",'',"addslashes");
                $data['province']=Request::instance()->post("province",0,"intval");
                $data['city']=Request::instance()->post("city",0,"intval");
                $data['district']=Request::instance()->post("district",0,"intval");
                $data['user_id']=$user_id;
                if($data['consignee'] && $data['tel'] && $data['address']  && $data['user_id']){
                     $order['address_id']=$this->Model->add_address($data);
                     if(!$order['address_id']){
                        $this->error('保存地址信息失败');
                     }
                }else{
                    $this->error('保存地址信息失败');
                }
                //保存地址信息
               
            }

            $order['create_time']=time();
            $data['note']=Request::instance()->post("note",'',"addslashes"); //备注
            
           $order['amount']=round($goods_info['goods_price']*$goods_number,2);
            $order['fencheng_price']=round($goods_info['fencheng_price']*$goods_number,2);//订单分成
            $User=new \app\wap\model\User();
            $user_info=$User->get_user_info($user_id);
            $order['pay_type']=Request::instance()->post("pay_type",1,"intval");
            $order['order_sn']=date('ymdHis',$order['create_time']).mt_rand(100,999);
            $order['user_id']=$user_info['user_id'];
            $order['parent_id']=$user_info['parent_id'];
            $order['parent2_id']=$user_info['parent2_id'];
            $order['parent3_id']=$user_info['parent3_id'];
            $order['user_level']=$user_info['user_level'];
            $order['parent_level']=$User->get_user_level($user_info['parent_id']);
            $order['parent2_level']=$User->get_user_level($user_info['parent2_id']);
            $order['parent3_level']=$User->get_user_level($user_info['parent3_id']);

            $order_id=$this->Model->add_order($order);

            if(!$order_id){
                $this->error('订单提交失败');
            }else{
                $og['goods_id']=$goods_id;
                $og['buy_number']=$goods_number;
                $og['goods_price']=$goods_info['goods_price'];
                $og['goods_name']=$goods_info['goods_name'];
                $og['order_id']=$order_id;
                $order_goods_id=$this->Model->add_order_goods($og);
                if($order_goods_id){
                    //$this->redirect('wechatpay/index',['order_name'=>123]);
                    header('Location:/wap/wechatpay/index/order_name/'.$og['goods_name'].'/order_sn/'.$order['order_sn']);
                }else{
                    $this->error('订单提交失败');
                }
            }


        }
    }

      public function address(){
     $user_id=$this->user_id;
      //lt:用来验证是否是通过自己的表单提交的
           $postcode=mt_rand(1000,9999);
           cookie("postcode_address",$postcode);
         $this->assign("postcode",$postcode);
            //end
          
      //lt:如果有要返回上一个地址
      $is_rec=Request::instance()->param("is_rec",0,"intval");
      if($is_rec){
        //获得上一个地址地址
        $rec_url=urlencode($_SERVER['HTTP_REFERER']);
        $this->assign("rec_url",$rec_url);
      }
      $provinces=$this->Model->get_address_region(1);
      //end
      $address_list=$this->Model->get_address_list($user_id);
      $this->assign("address_list",$address_list);
      $this->assign("provinces",$provinces);
      return $this->fetch();
    }

    public function add_address(){
       
      $user_id=$this->user_id;
       $postcode=Request::instance()->post("postcode",'',"addslashes");
  
        if(cookie("postcode_address")!=$postcode){
            js_alert('保存地址信息失败');//cookie验证提交是否为正常提交，防止恶意提交
        }

        $address_id=Request::instance()->post("address_id",0,"intval");
        $is_del=Request::instance()->post("del",0,"intval");
        if($address_id && $is_del){
            $this->Model->delete_address($address_id);
            js_alert('删除地址成功');
        }
       $rec_url=Request::instance()->post("rec_url",'',"urldecode"); //获得回调地址
       
       $data['consignee']=Request::instance()->post("consignee",'',"addslashes");
                $data['tel']=Request::instance()->post("tel",'',"addslashes");
                $data['address']=Request::instance()->post("address",'',"addslashes");
               
                $data['province']=Request::instance()->post("province",0,"intval");
                $data['city']=Request::instance()->post("city",0,"intval");
                $data['district']=Request::instance()->post("district",0,"intval");
                $data['user_id']=$user_id;
                $data['is_wechat']=Request::instance()->post("is_wechat",0,"intval");
                $data['create_time']=time();
                if($data['consignee'] && $data['tel'] && $data['address']  && $data['user_id']){
                
                  if($address_id){
                    $data['address_id']=$address_id;
                    
                    $this->Model->update_address($data);
                    if($rec_url){
                      //echo $rec_url;
                          js_alert('保存地址信息成功',$rec_url);
                        }else{
                          js_alert('保存地址信息成功');
                        }
                  }else{
                     $address_id=$this->Model->add_address($data);
                     if(!$address_id){
                        js_alert('保存地址信息失败');
                     }else{
                        if($rec_url){
                          js_alert('保存地址信息成功',$rec_url);
                        }else{
                          js_alert('保存地址信息成功');
                        }
                        
                     }
                  }
                    
                }else{
                    js_alert('保存地址信息失败,信息不完整');
                }
    }

     public function order_info(){
        $order_id=input('order_id/d');
        $user_id=$this->user_id;
        $order_info_all=$this->Model->get_order_info_all($order_id);
        $order_info=$order_info_all['order_info'];
        if(!$order_info_all || $order_info['user_id']!=$user_id){
          js_alert('查看订单失败');
        }
          $kuaidi_name_c="";
          $kuaidi_arr=db("kuaidi100")->select();
        foreach ($kuaidi_arr as $key => $value) {
          if($value['code']==$order_info['kuaidi_name']){
            $kuaidi_name_c=$value['name'];
            break;
          }
        }
        $this->assign("kuaidi_name_c",$kuaidi_name_c);
        $this->assign("order_info",$order_info);
        $this->assign("order_goods",$order_info_all['order_goods']);
        $this->assign("order_status",$this->order_status);
        
       
        //pp($order_info_all['order_goods']);
        return $this->fetch();
     }


public function kuaidi() {
    $kuaidi_sn=input('kuaidi_sn');
    $kuaidi_name=input('kuaidi_name');
    $kuaidi_arr=db("kuaidi100")->select();
    /*$kuaidi_sn='9722602555201';
    $kuaidi_name='ems';*/
    $kuaidi_name_c="";
    foreach ($kuaidi_arr as $key => $value) {
      if($value['code']==$kuaidi_name){
        $kuaidi_name_c=$value['name'];
        break;
      }
    }

 
  $kurl = 'http://www.kuaidi100.com/query?type=' . $kuaidi_name . '&postid=' . $kuaidi_sn;

  $ret = https_request($kurl);
  $k_arr = json_decode($ret, true);
  $this->assign("kuaidi",$k_arr);
  $this->assign("kuaidi_name_c",$kuaidi_name_c);
  
  return $this->fetch();

} 
     //订单收货
    public function over(){
        $user_id=$this->user_id;
        $order_id=input("order_id/d"); 
        if(empty($order_id)){
          js_alert('收货失败');
          exit;
        }
        $orderModel=new \app\admin\model\Order();
        $order=$orderModel->get_order_info($order_id);
        if($user_id!=$order['user_id']){
          //不是该用户不能收货
          js_alert('收货失败');
          exit;
        }
       if($order['status']!=2){
        js_alert('收货失败,订单不是已发货订单');
       }else{
        //各级得到相应的确认积分
        
        $orderModel->give_parents_sure_fencheng($order);
        $data['status']=3;
        $data['over_time']=time();
        $orderModel->update_order($data,$order_id);
        js_alert('收货成功','/wap/order/index/s/3');
       }
        
   }
   //订单升级
   public function uporder(){
        $user_id=1;
        $user_level=1;

        if($user_level!=1){
          js_alert('您不能升级');
          exit;
        }
       if(!empty($_POST)){
         $goods_number=Request::instance()->post("goods_number",0,"intval");
         $goods_number=Request::instance()->post("goods_number",0,"intval");
       }
       /*if($order['status']!=2){
        js_alert('收货失败,订单不是已发货订单');
       }else{*/
        //各级得到相应的确认积分
        
        $orderModel->give_parents_sure_fencheng($order);
        $data['status']=3;
        $data['over_time']=time();
        $orderModel->update_order($data,$order_id);
        js_alert('收货成功','/wap/order/index/s/3');
       }

        //试用订单
  public function order_test(){
        $user_id=$this->user_id;
      //判断有没有提交过
      $order_test=$this->Model->get_order_test($user_id);
      if($order_test){
        if($order_test['status']>0){

      
        if($order_test['user_id']!=$user_id){
          js_alert('查看订单失败');
        }
          $kuaidi_name_c="";
          $kuaidi_arr=db("kuaidi100")->select();
        foreach ($kuaidi_arr as $key => $value) {
          if($value['code']==$order_test['kuaidi_name']){
            $kuaidi_name_c=$value['name'];
            break;
          }
        }
        $this->assign("kuaidi_name_c",$kuaidi_name_c);
        $this->assign("order_info",$order_test);
       // $this->assign("order_goods",['goods_name'=>$order_test['order_goods'],"goods_"]);
        $this->assign("order_status",$this->order_status);
        
         return $this->fetch('order_test_info');
        }else{
          header('Location:/wap/wechatpay/index/order_name/'.$order_test['goods_name'].'/order_sn/'.$order_test['order_sn']);
          exit;
        }
      }else{
       if(!empty($_POST)){
           
            $order['address_id']=Request::instance()->post("address_id",0,"intval");
          
         
     

            if(!$order['address_id']){
                //如果之前有地址记录
   
                //如果之前没有地址记录
                $data['consignee']=Request::instance()->post("consignee",'',"addslashes");
                $data['tel']=Request::instance()->post("tel",'',"addslashes");
                $data['address']=Request::instance()->post("address",'',"addslashes");
                $data['province']=Request::instance()->post("province",0,"intval");
                $data['city']=Request::instance()->post("city",0,"intval");
                $data['district']=Request::instance()->post("district",0,"intval");
                $data['user_id']=$user_id;
                if($data['consignee'] && $data['tel'] && $data['address']  && $data['user_id']){
                     $order['address_id']=$this->Model->add_address($data);
                     if(!$order['address_id']){
                        $this->error('保存地址信息失败');
                     }
                }else{
                    $this->error('保存地址信息失败');
                }
                //保存地址信息
               
            }

            $order['create_time']=time();
    
            
           $order['amount']=6;
           
            
          
           
            $order['order_sn']='test'.date('ymdHis',$order['create_time']).mt_rand(100,999);
            $order['user_id']=$user_id;
            $order['goods_name']='纯夫人竹纤维纸一包';


            $order_id=$this->Model->add_order_test($order);

            if(!$order_id){
                js_alert('订单提交失败');
            }else{
                header('Location:/wap/wechatpay/index/order_name/'.$order['goods_name'].'/order_sn/'.$order['order_sn']);
            }
     
        }else{
              $address_info=$this->Model->get_address($user_id);//地址信息

            //lt:使用微信收货信息
              $reg='/MicroMessenger\/(\d*)/';
              preg_match($reg,$_SERVER['HTTP_USER_AGENT'],$out); //判断微信版本号。版本>5.0启用改功能
             if($out[1]>=5 && !$address_info){

                 $options=['appid'=>$this->appid,"appsecret"=>$this->appsecret];
                $address_sign=get_address_sigin($options);
                $this->assign("addrSign",$address_sign);
                $this->assign("show_addrSign",1);//显示微信收货地址
             }
            //lt：end
            
            $goods_id=isset($_GET['id'])?intval($_GET['id']):1;

            $sign_package=get_sign_package($this->shop_config);
            $this->assign("signPackage",$sign_package);
              
       

          
                
                
                if(!$address_info){
                    //如果没有先获得省份
                    $provinces=$this->Model->get_address_region(1);
                    $this->assign("provinces",$provinces);
                }
                $goods_info=$this->Model->get_goods_info($goods_id); //商品信息
                $goods_info['goods_name']='纯夫人竹纤维纸一包';
                $this->assign("address_info",$address_info);
                $this->assign("goods_info",$goods_info);
                
                //获得地址
                return $this->fetch();
            
        
        }
 
      }
    
    }
        
   }

  

