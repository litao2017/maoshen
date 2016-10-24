<?php
namespace app\index\controller;
use think\Controller;
class Public extends Controller
{
   public function footer(){
    return $this->fetch();
   }
  
   public function header(){
	   
	   
    return $this->fetch();
  
   }
}
