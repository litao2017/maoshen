<?php
namespace app\wap\controller;
use app\wap\controller\Wechat;
use think\Request;
class User extends Wechat
{
    public $Model;
    public $user_status;
    public $order_status=["未付款","已付款","已发货","已收货","退货","作废"];
    public function _initialize(){
        parent::_initialize();
        $this->Model=new \app\wap\model\User();
        $this->user_status=[$this->shop_config['default_name'],$this->shop_config['level_name'],$this->shop_config['level2_name'],$this->shop_config['level3_name']];
    }
    //首页
    public function index()
    {
  
        $user_id=$this->user_id;
       // $user_id=1;
        //用户信息
        $user_info=$this->user_info;
        //用户推荐人信息
        if($user_info['parent_id']){
          $parent_info=$this->Model->get_user_info($user_info['parent_id'],1);
          $this->assign("parent_info",$parent_info);
        }
        //私信
        $message_num=$this->Model->get_message_num($user_id);
        $this->assign('message_num',$message_num?$message_num:'');
       // pp($this->user_status);
       $user_info['level_name']=$this->user_status[$user_info['user_level']];
       //待收货
       //如果购买过再计算待收货
       $dsh_num=$dpl_num=$tuan_num=0;
       if($user_info['user_level']){
          $dsh_num=\app\wap\model\Order::get_order_num($user_id,2);
          //待评论
           $dpl_num=db("order")->where('status=3 and is_comment=0 and user_id='.$user_id)->count();
           //团队数量
           $tuan_num=db("user")->where('user_level>1 and tuan_id='.$user_id)->count();
       }
       
       //各级粉丝数
       $fans1_num=$fans2_num=$fans3_num=0;
       $fans1_num=$this->Model->get_fans_num($user_id,1);
       if($fans1_num){
          $fans2_num=$this->Model->get_fans_num($user_id,2);
       }
       if($fans2_num){
          $fans3_num=$this->Model->get_fans_num($user_id,3);
       }
       
       $fans_info['fans1_num']=$fans1_num;
       $fans_info['fans2_num']=$fans2_num;
       $fans_info['fans3_num']=$fans3_num;
       $fans_info['fans_all_num']=$fans1_num+$fans2_num+$fans3_num;
       $this->assign("fans_info",$fans_info);
       $this->assign("user_info",$user_info);
       $this->assign("dsh_num",$dsh_num);
       $this->assign("dpl_num",$dpl_num);
       $this->assign("tuan_num",$tuan_num);
       $this->assign("shop_config",$this->shop_config);
        return $this->fetch();
    }
    //粉丝界面
  public function fans()
    {
        //$Model=new \app\wap\model\Wechat();
       // $user_info=$Model->login();
        //var_dump($user_info);
         $user_id=$this->user_id;
       $fans_level=Request::instance()->param("level",1,"intval");
       $search_level=Request::instance()->param("slevel",1,"intval");

       //查找用户等级
       
     
            $fans_list=$this->Model->get_fans_list($user_id,$fans_level,$search_level);
$this->assign("title",$this->shop_config['level_name_'.$fans_level]);
          $this->assign("fans_list",$fans_list);
          $this->assign("fans_level",$fans_level);
          $this->assign("search_level",$search_level);
          $this->assign("shop_config",$this->shop_config);
          return $this->fetch();
     
    
    }
     public function ajax_fans(){
      $user_id=$this->user_id;
       $num=Request::instance()->post("num",1,"intval");
      $page=Request::instance()->post("page",1,"intval");
       $search_level=Request::instance()->post("slevel",1,"intval");
      $fans_level=Request::instance()->post("level",1,"intval");
 
      $curr=$num*$page;
      $limit="{$curr},{$num}";
      $fans_list=$this->Model->get_fans_list($user_id,$fans_level,$search_level,$limit);
      $info='';
    foreach ($fans_list as $key => $value) {
       $tuandui=$search_level==1?'<span>团队：'.$value['tuan_money'].'</span>':'';
       $info.=' <li><img src="'.$value['headimgurl'].'" style="float:left;"><div class="user_name"><p>昵&nbsp;&nbsp;&nbsp;&nbsp;称：'.$value['nickname'].'</p><p>会员号：'.$value['user_name'].'&nbsp;&nbsp;<a href="/wap/message/send_message/get_id/'.$value['user_id'].'" class="message">私信</a></p></div>'.$tuandui.'</li>';
        
    }
  echo $info;
  exit;
    }
    //单个粉丝订单页
    public function fans_order_one(){
//$user_id=$this->user_id;
$user_id=input('uid/d');
if(!$user_id){
        js_alert('无订单');
      }
 $fans_level=input('fans_level/d');      
        $field='order_id';
     
 
        $this->Model->set_shop_config($this->shop_config);
        $order_list=$this->Model->get_order_list($user_id,$field);
       // pp($order_list);
  //pp($fans_order_list);
        if($fans_level==1){
          $parent_level="parent_level";
        }elseif($fans_level==2){
         $parent_level="parent2_level";
        }elseif($fans_level==3){
          $parent_level="parent3_level";
        }
        
        foreach ($order_list as  &$order) {
          $order['fencheng_percent']=$this->Model->get_fencheng_percent($order[$parent_level],$fans_level);
      
          $order['get_fencheng_price']=round($order['fencheng_price']*$order['fencheng_percent'],2);
        }
        unset($order);
        $this->assign("order_status",$this->order_status);
        $this->assign("fans_level",$fans_level);
        $this->assign("uid",$user_id);
        $this->assign("order_list",$order_list);
        return $this->fetch();
    }
 public function ajax_fans_order_one(){
      $user_id=Request::instance()->post("uid",0,"intval");
      if(!$user_id){
        exit;
      }

       $num=Request::instance()->post("num",1,"intval");
      $page=Request::instance()->post("page",1,"intval");
      $fans_level=Request::instance()->post("fans_level",1,"intval");
      if($fans_level==1){
          $parent_level="parent_level";
        }elseif($fans_level==2){
         $parent_level="parent2_level";
        }elseif($fans_level==3){
          $parent_level="parent3_level";
        }
        $field='order_id';
      
      $curr=$num*$page;
      $limit="{$curr},{$num}";
      $this->Model->set_shop_config($this->shop_config);
      $order_list=$this->Model->get_order_list($user_id,$field,$limit);
      $info='';
    foreach ($order_list as $key => $value) {
       $value['fencheng_percent']=$this->Model->get_fencheng_percent($order[$parent_level],$fans_level);
       
      $value['get_fencheng_price']=round($order['fencheng_price']*$order['fencheng_percent'],2);
      if($value['status']==1){
        $color='#f29307';
      }elseif($value['status']==2){
        $color='#1b75b6';
      }elseif($value['status']==3){
        $color='#89bf00';
      }
      $info.=' <li><span class="span1">'.$value['user_name'].'<br/>
    <font style="color:'.$color.'"> '.$this->order_status[$value['status']].'</font> </span><span class="span2">'.$value['order_sn'].'<br/><font style="color:#888">'.$value['pay_time'].'</font></span><span class="span3">'.$value['fencheng_price'].'</span><span class="span4">'.($value['fencheng_percent']*100).'%</span><span class="span5">'.$value['get_fencheng_price'].'</span></li>';
        
    }
      echo $info;
       exit;
    }
    //粉丝订单页
     public function fans_order(){
        $user_id=$this->user_id;
        $paixu=input('order/d');
        $field='order_id';
        if($paixu==1){
            $field='amount';
        }
        $fans_level=input('level/d');
        $this->Model->set_shop_config($this->shop_config);
        $fans_order_list=$this->Model->get_fans_order_list($user_id,$fans_level,$field);
  //pp($fans_order_list);
        if($fans_level==1){
          $parent_level="parent_level";
        }elseif($fans_level==2){
         $parent_level="parent2_level";
        }elseif($fans_level==3){
          $parent_level="parent3_level";
        }
        
        foreach ($fans_order_list as  &$order) {
          $order['fencheng_percent']=$this->Model->get_fencheng_percent($order[$parent_level],$fans_level);
      
          $order['get_fencheng_price']=round($order['fencheng_price']*$order['fencheng_percent'],2);
        }
        unset($order);
        $this->assign("paixu",$paixu);
        $this->assign("order_status",$this->order_status);
        $this->assign("fans_level",$fans_level);
        $this->assign("order_list",$fans_order_list);
        $this->assign("fans_order_list",$fans_order_list);
        return $this->fetch();
    }
  
    public function ajax_fans_order(){
      $user_id=$this->user_id;
       $num=Request::instance()->post("num",1,"intval");
      $page=Request::instance()->post("page",1,"intval");
      $fans_level=Request::instance()->post("level",1,"intval");
      $paixu=Request::instance()->post("order",0,"intval");
      if($fans_level==1){
          $parent_level="parent_level";
        }elseif($fans_level==2){
         $parent_level="parent2_level";
        }elseif($fans_level==3){
          $parent_level="parent3_level";
        }
        $field='order_id';
        if($paixu==1){
            $field='amount';
        }
      $curr=$num*$page;
      $limit="{$curr},{$num}";
      $this->Model->set_shop_config($this->shop_config);
      $fans_order_list=$this->Model->get_fans_order_list($user_id,$fans_level,$field,$limit);
      $info='';
    foreach ($fans_order_list as $key => $value) {
       $value['fencheng_percent']=$this->Model->get_fencheng_percent($value[$parent_level],$fans_level);
      //  file_put_contents('filename1.log',$value['fencheng_percent']);  
      $value['get_fencheng_price']=round($value['fencheng_price']*$value['fencheng_percent'],2);
      if($value['status']==1){
        $color='#f29307';
      }elseif($value['status']==2){
        $color='#1b75b6';
      }elseif($value['status']==3){
        $color='#89bf00';
      }
      $info.=' <li><span class="span1">'.$value['user_name'].'<br/>
    <font style="color:'.$color.'"> '.$this->order_status[$value['status']].'</font> </span><span class="span2">'.$value['order_sn'].'<br/><font style="color:#888">'.$value['pay_time'].'</font></span><span class="span3">'.$value['fencheng_price'].'</span><span class="span4">'.($value['fencheng_percent']*100).'%</span><span class="span5">'.$value['get_fencheng_price'].'</span></li>';
        
    }
      echo $info;
       exit;
    }
    //个人资料
    public function user_info(){
      $user_id=$this->user_id;
      if(!empty($_POST)){
          $postcode=Request::instance()->post("postcode","","addslashes");
          if(cookie("postcode_userinfo")!=$postcode){
            js_alert('资料修改失败');
          }
          
          $month=Request::instance()->post("month","","addslashes");
          $day=Request::instance()->post("day","","addslashes");
          if( $month && $day){
            $year=2016;//默认一个，反正没用，只记录月日就行了
            $data['birthday']=strtotime($year.'-'.$month.'-'.$day);
          }
          
          $data['tel']=Request::instance()->post("tel","","addslashes");
          $this->Model->update_user_info($user_id,$data);
          js_alert('修改成功');
      }else{
          //s:用来验证是否是通过自己的表单提交的
            $postcode=mt_rand(1000,9999);
            cookie("postcode_userinfo",$postcode);
            $this->assign("postcode",$postcode);
            //end
            /* $year_options=$month_options=$day_options='';
             for ($i=1500; $i <2008 ; $i++) { 
              $year_options.='<option value="'.$i.'">'.$i.'</option>';
             }*/
             for ($j=1; $j <13 ; $j++) { 
              $month_options.='<option value="'.$j.'">'.$j.'</option>';
             }
             for ($k=1; $k <32 ; $k++) { 
              $day_options.='<option value="'.$k.'">'.$k.'</option>';
             }
             $user_info=$this->Model->get_user_info($user_id);
             //$this->assign("year_options",$year_options);
             $this->assign("month_options",$month_options);
             $this->assign("day_options",$day_options);
             $this->assign("user_info",$user_info);
             return $this->fetch();       
      }
      
    }
    public function qrcode(){
      return $this->fetch();
    }
    //二维码界面
    public function get_qrcode(){
      $uid=input('uid/d');
      $name=input('name');
      if($uid){
        //如果是通过分享二维码连接出去的直接输出二维码
        $qrcode=db("qrcode")->where('user_id='.$uid)->find();
        $this->assign('qrcode_url',$qrcode['qrcode_img']);
        $this->assign("show_footer",0);
          return $this->fetch();
          exit;
      }
   // $user_id=1;
       $user_id=$this->user_id;
        $user_info=$this->Model->get_user_info($user_id);
        if($user_info['user_level']<1){
          js_alert('没有二维码权限');
        }
        //判断有没有生成过二维码
       $qrcode_info= $this->Model->get_qrcode_info_byuser($user_id);
        if($qrcode_info['qrcode_img']){
             $qrcode_img=$qrcode_info['qrcode_img'];
        }else{
          if(empty($name)){
            header('Location:/wap/user/qrcode');
            exit;
          }
          if($name=='user_nickname'){
            $name=$this->nickname;
          }
         // echo $name;exit;
            $scene_id=$user_id;
            //获得ticket
            $ticket=$this->Model->get_qrcode_ticket($user_id); 
            if(!$ticket){
                $ticket=$this->get_qrcode_ticket($scene_id); 
             }
            $qrcode_url='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket); //通过ticket获得二维码远程路径
            $qrcode_img=$this->get_qrcode_img($qrcode_url,$name);  //通过连接获得图片
            $qrcode_img=str_replace('./', '/', $qrcode_img);
            //保存二维码到数据库
            $this->Model->add_qrcode($user_id,$ticket,$scene_id,$qrcode_img);
        }
         $this->assign('qrcode_url',$qrcode_img);
         //分享连接
         $url=get_url();
        $user_level=$this->user_info['user_level'];
        if($user_level>0){
          $link=strstr($url,'?')?$url.'&uid='.$this->user_id:$url.'?uid='.$this->user_id;
        }else{
          $link=$url;
        }
        $this->assign("show_footer",1);
        $this->assign("link",$link);
        $this->assign("title",$this->shop_config['shop_name']);
        $this->assign("desc",$this->shop_config['share_word']);
        $this->assign("imgUrl",$this->headimgurl);
        $sign_package=get_sign_package($this->shop_config);
        $this->assign("signPackage",$sign_package);

           return $this->fetch();
    }
     private function get_qrcode_img($qrcode_url,$name=''){
           $qrcode_path='./qrcode/'.date('Ymd');//生成存放路径
           make_dir($qrcode_path);
            
            $qrcode_info=downloadimageformweixin($qrcode_url);

                
            $qrcode_img=$qrcode_path.'/'.uniqid().mt_rand(100,999).'.jpg';

            file_put_contents($qrcode_img,$qrcode_info);

    
        $arr = getimagesize($qrcode_img); 
     
        $ewm_width=240;//二维码长度和宽度
        $ewm_height=240;
        $imgsrc = imagecreatefromjpeg($qrcode_img); 
        $image = imagecreatetruecolor($ewm_width, $ewm_height);
        imagecopyresampled($image, $imgsrc, 0, 0, 0, 0,$ewm_width,$ewm_height,$arr[0], $arr[1]);
        
        imagejpeg($image,$qrcode_img);
          //打印二维码
            $target='./qrcode/qrcode.jpg';
            $target_img = imagecreatefromjpeg($target);
            $source = imagecreatefromjpeg($qrcode_img);
            imagecopy($target_img,$source,143,290,0,0,$ewm_width,$ewm_height); //二维码位置
            //打印字体
            $nickname=$name?$name:$this->nickname;
            $fontfile = "./fonts/SIMYOU.TTF";
            $textcolor = imagecolorallocate($target_img,56,38,59); //字体颜色
            //$text1="我是".$nickname;
            imagettftext($target_img,18,0,235,228,$textcolor,$fontfile,$nickname);//字体位置
            //生成二维码  
            imagejpeg($target_img,$qrcode_img);
           
            return $qrcode_img;
     }

    private function get_qrcode_ticket($scene_id){
   
        //判断有没有该有没有是否存在ticket，如果没有获取，如果有直接拿来用
    
                  $options=['appid'=>$this->appid,'appsecret'=>$this->appsecret,'access_token'=>$this->access_token];
        
                $WechatApi=new \think\WechatApi($options);
                $res=$WechatApi->getQRCode($scene_id,1);//0为临时二维码，1为永久二维码
            // $res['ticket']='gQEj7zoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3V6djZ5czNrSFhSa1BPaXBMeGRUAAIEd8heVwMEAAAAAA==';
                if(isset($res['ticket'])){
                   return $res['ticket'];
                }else{
                    return false;
                }
       
  
    }

    public function up_level(){
        $user_id=$this->user_id;
      //判断有没有提交过
      $up_user=$this->Model->get_up_user($user_id);
      if($up_user){
        js_alert('您已经提交过了');
      }
      if(!empty($_POST)){
          $postcode=Request::instance()->post("postcode","","addslashes");
          if(cookie("postcode_upuser")!=$postcode){
            js_alert('提交失败');
          }
          
          $data['up_type']=Request::instance()->post("up_type","","intval");
          $data['my_type']=Request::instance()->post("my_type","","intval");
          $data['wechat_hao']=Request::instance()->post("wechat_hao","","addslashes");
          $data['name']=Request::instance()->post("username","","addslashes");
          $data['tel']=Request::instance()->post("tel","","addslashes");
          $data['create_time']=time();
          $data['user_id']=$user_id;
          $this->Model->add_up_user($data);
          js_alert('提交成功,等待客服联系','/wap/user/');
      }else{
          //s:用来验证是否是通过自己的表单提交的
            $postcode=mt_rand(1000,9999);
            cookie("postcode_upuser",$postcode);
            $this->assign("postcode",$postcode);
            //end
          
             $user_info=$this->user_info;
             $parent_info=$this->Model->get_user_info($user_info['parent_id'],1);
             $this->assign("parent_info",$parent_info);
             $this->assign("user_info",$user_info);
             return $this->fetch();       
      }
    
    }

}
