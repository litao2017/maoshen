<?php
namespace app\wap\controller;
use app\wap\controller\Wechat;
class Index extends Wechat
{
  public function index(){
    //评论信息

      
        $goods_id=1;
       // $dpl_num=db("order")->where('status=3 and is_comment=0')->count();
       $pl_num=db("comment")->where('status=1')->count();
        $this->assign("pl_num",$pl_num);
        $this->assign("goods_id",$goods_id);
       //获得商品信息
       $goods_info=db("goods")->where('status=1')->find();
      // pp($goods_info);
       if($goods_info){
         $this->assign("goods_info",$goods_info);
        
        $url=get_url();
        $user_level=$this->user_info['user_level'];
        if($user_level>0){
          $link=strstr($url,'?')?$url.'&uid='.$this->user_id:$url.'?uid='.$this->user_id;
        }else{
          $link=$url;
        }
        
        $this->assign("link",$link);
        $this->assign("title",$this->shop_config['shop_name']);
        $this->assign("desc",$this->shop_config['share_word']);
        $this->assign("imgUrl",$this->headimgurl);
        $sign_package=get_sign_package($this->shop_config);
        $this->assign("signPackage",$sign_package);
        $this->assign("shop_name",$this->shop_config['shop_name']);
        $this->assign("qq",$this->shop_config['shop_qq']);
         return $this->fetch();
       }
       
    }
}
