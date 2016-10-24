<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Admin extends Model
{
   
    public function login($admin_name,$password,$verify){
      if(strtolower($verify)==strtolower(cookie("verify"))){
        $miyao='maoshen';//密码密钥，数据库中保存的密码为md5(输入密码+密钥)
        $admin=db('admin')->where('admin_name',$admin_name)->find();
        //pp($admin);
        if($admin){
			
            $md5_pwd=md5($password.$miyao);
			
            if($md5_pwd==$admin['password']){
				
				$data['last_login_time'] = time();
				
				$data['ip'] = getIP();
				
				db('admin')->where('admin_name',$admin_name)->update($data);
				
                return $admin;
            }
        }
      }
        
        return false;
       /* $Admin = new Admin;
        $Admin->data(['user_name'=>'Thinkphp','email'=>'thinkphp@qq.com']);

        $Admin->save();*/
    }
  
     
   
   

}