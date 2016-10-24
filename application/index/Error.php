<?php
namespace app\index\controller;

class Error 
{
    public function index()
    {
        //根据当前模块名来判断要执行那个城市的操作
         $cityName = CONTROLLER_NAME;
        return $this->city($cityName);
    }

    //注意 city方法 本身是 protected 方法
    protected function city($name)
    {
        //和$name这个城市相关的处理
         return '当前城市' . $name;
    }
}
