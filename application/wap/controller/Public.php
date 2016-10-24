<?php
namespace app\wap\controller;
use app\wap\controller\Wechat;
class Public extends Wechat
{
   public function footer_gr(){
    return $this->fetch();
   }
   public function footer_erm(){
    return $this->fetch();
   }
   public function wx_share(){
    $url=get_url();
    $link=strstr($url,'?')?$url.'&uid='.$this->user_id;

    $this->assign("link",$link);
    $this->assign("headimgurl",$this->headimgurl);
    return $this->fetch();
   }
}
