<?php
namespace app\wap\controller;
use think\Controller;
class wap extends Controller
{
    public $appid;
    public $appsecret;
    public $access_token;
    public $shop_config;
    public function _initialize()
    {
   
        $this->shop_config=db("config")->find(1);
   
        $this->appid=$this->shop_config['appid'];
        $this->appsecret=$this->shop_config['appsecret'];
        $this->access_token=get_access_token($this->shop_config);
		
    }
     public function _empty($name)
    {
        //把所有城市的操作解析到city方法
        return $this->showError($name);
    }
      protected function showError($name)
    {
        //和$name这个城市相关的处理
         return  $name;
    }
}
