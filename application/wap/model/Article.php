<?php
namespace app\wap\model;
use think\Model;
use think\Db;
class Article extends Model
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
   article_id int 文章id
    */
    public function get_article_info($article_id){
        $article_info=Db::name('article')->find($article_id);
        return $article_info;
    }

  
}