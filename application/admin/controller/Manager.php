<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Manager extends Admin {
  
    public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Manager();
    }
    function showlist(){
    
        $manager_list=$this->Model->get_list();
        $this -> assign('manager_list', $manager_list);
         return $this->fetch();
    }
    
    //添加商品
  

    function add(){
         if(isset($_POST['manager_name'])){
            $manager_id=Request::instance()->post('manager_id',0,'intval'); 
            $data['admin_name']=Request::instance()->post('manager_name','','addslashes'); 
            if( empty($data['admin_name'])){
                    $this->error('姓名不能为空');
                }
         
            $password=Request::instance()->post('password','','addslashes');
            $data['tel']=Request::instance()->post('tel','','addslashes');
            $data['position']=Request::instance()->post('position','','addslashes'); 
            $password2=Request::instance()->post('password2','','addslashes'); 
            if($password!=$password2){
              $this->error('两次密码不一致');
            }else{
              if(!empty($password)){
                //不为空修改密码
                 $miyao='maoshen';//密码密钥，数据库中保存的密码为md5(输入密码+密钥)
                 $data['password']=md5($password.$miyao);
              }
             
            }
            $menu=$_POST['menu']; 
            //pp($auth);
            $menu_list='';
            foreach ($menu as $key => $value) {
               $menu_list.=$menu_list?",".$value:$value;
            }
			
            $data['menu']=$menu_list?$menu_list:0;

            if($manager_id){

                //如果有manager_id则更新
                $this->Model->update_manager($data,$manager_id);
                $this->error('更新成功','showlist');
            }else{
                //新增
                if(empty($password)){
                    $this->error('密码不能为空');
                }
               $data['create_time']=time(); 
                if($new_id=$this->Model->add_manager($data)){
                  
                    $this->error('增加成功','showlist');
                  
                }else{
                    $this->error('增加失败');
                } 
            }
            

             
         }else{
          
            $manager_id=Request::instance()->param('admin_id','','intval');
            $manager=[];
           
            $manager=$this->Model->get_manager_info($manager_id);
               
            
            $menu_list=db("menu")->select();
              foreach ($menu_list as $key => $value) {
            $id=$value['menu_id'];
            $pid=$value['menu_pid'];
            if($pid==0){
              $new_list[$id]=$value;
            }else{
               $new_list[$pid]['child'][]=$value;
            }
            
          }
          $this->assign('menu_list',$new_list);
             $this->assign("manager",$manager);
            return $this->fetch();
         }
     
    }

        //ajax删除方法
    function delete(){

       $manager_id=input('admin_id/d'); 
       if($manager_id){

        $this->Model->delete_manager($manager_id);
        $this->error('删除成功','showlist');
      }else{
         $this->error('删除失败','showlist');
      }
 
       
        
       
    }
 //设置
    function set(){
      $manager_id=Request::instance()->get('id',0,'intval'); 
        if($manager_id){
                $manager=$this->Model->get_manager_info($manager_id);
               
            }
 
   

  }
}

