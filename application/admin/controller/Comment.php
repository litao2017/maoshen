<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Comment extends Admin {
  
    public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Comment();
    }
    function showlist(){
    
        $comment_list=$this->Model->get_list();

        $this -> assign('comment_list', $comment_list);
        
         return $this->fetch();
    }
    
    //添加商品
  

    function add(){
         if(!empty($_POST)){
            $comment_id=Request::instance()->post('comment_id',0,'intval'); 
            $data['content']=Request::instance()->post('content','','htmlspecialchars'); 
         
           
            $data['status']=Request::instance()->post('status',0,'intval'); 
            $data['user_name']=Request::instance()->post('user_name',0,'addslashes'); 
            

            if($comment_id){
                //如果有comment_id则更新
                $this->Model->update_comment($data,$comment_id);
                $this->error('更新成功','showlist');
            }else{
                //新增
               $data['create_time']=time(); 
                if($new_id=$this->Model->add_comment($data)){
                  
                    $this->error('增加成功','showlist');
                  
                }else{
                    $this->error('增加失败');
                } 
            }
            
/*$file = Request::instance()->file('image');
pp($file);
// 移动到服务器的上传目录
$info = $file->move(APP_PATH);
if($info){
    // 成功上传后 获取上传信息
   // echo $info->getExtension(); // 输出 jpg
    $data['comment_src']=$info->getFilename(); // 输出 42a79759f284b767dfcb2a0197904287.jpg
}else{
    // 上传失败获取错误信息
    echo $file->getError();
    exit;
}*/
             
         }else{
            $comment_id=Request::instance()->param('comment_id','','intval');
            $comment=[];
            if($comment_id){
                $comment=$this->Model->get_comment_info($comment_id);
               
            }
             $this->assign("comment",$comment);
            return $this->fetch();
         }
     
    }

        //ajax删除方法
    function delete_ajax(){

       $comment_id=Request::instance()->post('comment_id',0,'intval'); 
       
 
        $this->Model->delete_comment($comment_id);
        return '{"status":1,"msg":"删除成功"}';
       
        
       
    }



  
}

