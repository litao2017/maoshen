<?php
namespace app\wap\controller;
use app\wap\controller\Wechat;
use think\Request;
class Article extends Wechat
{
  
    public function _initialize(){
        parent::_initialize();
        $this->Model=new \app\wap\model\Article();
       
    }
    //首页
    public function showlist()
    {
  
        $user_id=$this->user_id;
       // $user_id=1;
       
    }
    //粉丝界面
  public function content()
    {
      
      $user_id=$this->user_id;
     $article_id=input('id/d');
     if($article_id){
      $article_info=$this->Model->get_article_info($article_id);
      $this->assign("article_info",$article_info);
       return $this->fetch();
     }
      
    }
    

}
