<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Common extends Model
{
    public $config;
    //初始化
    public function _initialize()
    {
   
            //载入系统变量
            $this->config=db('config')->find(1);

        
    }
    public function log($content){
        $data['create_time']=time();
        $data['admin_name']=cookie('admin_name');
        $data['content']=$content;
        Db::name("admin_log")->insert($data);
    }
}
