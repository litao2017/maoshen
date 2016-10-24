<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Anli extends Admin {
  
    public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Anli();
    }
    function showlist(){
    
        $anli=$this->Model->get_list();
		
        $this -> assign('anli_list', $anli['list']);
		
		$this -> assign('html_page', $anli['html_page']);
        
         return $this->fetch();
    }
    
    //添加案例
  

    function add(){
    		
		 
		  	
         if(Request::instance()->isPost()){
			 
         // pp(input());
            $anli_id=Request::instance()->post('anli_id',0,'intval'); 
           
		    $data['anli_name']=Request::instance()->post('anli_name','','addslashes'); 
            
           
		    $data['cate_id']=Request::instance()->post('cate_id',0,'intval'); 
           
		  
		   
            $data['content']=Request::instance()->post('content','','htmlspecialchars'); 
            
  													
			
           if(empty($data['anli_name'])||empty($data['cate_id'])){

              $this->error('信息不完整');
            
		    	}
          $image=$this->upload_file('image');
          $image && $data['image']=$image;
          $qrcode=$this->upload_file('qrcode');
          $qrcode && $data['qrcode']=$qrcode;
          $logo=$this->upload_file('logo');
          $logo && $data['logo']=$logo;
       
			
        
            

            if($anli_id){
                //如果有goods_id则更新
				
                $this->Model->update_anli($data,$anli_id);
                $this->error('更新成功','showlist');
            }else{
                //新增
                $data['create_time']=time(); 
                if($new_id=$this->Model->add_anli($data)){
                  
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
    $data['goods_src']=$info->getFilename(); // 输出 42a79759f284b767dfcb2a0197904287.jpg
}else{
    // 上传失败获取错误信息
    echo $file->getError();
    exit;
}*/
             
         }else{
			 
            $anli_id = input('anli_id/d');
        	
            //$anli=[];
           
				
                $anli=$this->Model->get_anli_info($anli_id);
				
				$cate = db('anli')->where('anli_id',$anli_id)->find();
				
				$this->assign('cate_id',$cate['cate_id']);
               
          
			
			
            $cate_list=$this->Model->get_cate_list();
          $this->assign('cate_list',$cate_list);
             $this->assign("anli",$anli);
            return $this->fetch();
         }
     
    }

	
	
	 public function search(){
        //$brand_id=isset($_GET['brand_id'])?intval($_GET['brand_id']):0;
        //$category_id=isset($_GET['category_id'])?intval($_GET['category_id']):0;
       
	    $keyword=input('keyword','','addslashes');
        
		
        if($keyword){
			
			 $list['list']=db("anli")->join('anli_cate','ms_anli_cate.cate_id=ms_anli.cate_id','left')->where('anli_name','like','%'.$keyword.'%')->select();
			 			 $list['html_page']='';

            //$where = "name like '%".$keyword."%'";
        }else{
			
			 $list=$this->Model->get_list();
			
			}
       
        //$list=db("products")->where($where)->select();
         
             
           
        $this->assign('anli_list',$list['list']);
		
	   $this->assign('html_page',$list['html_page']);

        
      return  $this->fetch('showlist');
    }
	









        //ajax删除方法
    function delete_ajax(){

       $anli_id=Request::instance()->post('anli_id',0,'intval'); 
       
 
        $this->Model->delete_anli($anli_id);
        return '{"status":1,"msg":"删除成功"}';
       
        
       
    }


    //分类列表
    function catelist(){
        
 
        $cate_list=$this->Model->get_cate_list();
    $this -> assign('domain', request()->domain());
        $this->assign("cate_list",$cate_list);
        return $this->fetch();
    }
    //添加或修改商品分类
 public function add_cate(){

    if(!empty($_POST)){
       $cate_id=Request::instance()->post('cate_id',0,'intval'); 
       $data['cate_name']=Request::instance()->post('cate_name','','addslashes'); 
	   
	   if(!$data['cate_name']){
		   
		   $this->error('分类名称不能为空');
		   }
	   
       $data['cate_pid']=Request::instance()->post('cate_pid',0,'intval'); 
       if($cate_id){
        if($cate_id==$data['cate_pid']){

          $this->error('添加失败,自己不能成为自己的分类上级');
        }
        $this->Model->update_cate($data,$cate_id);
        $this->error('更新成功','catelist');
       }else{
          $new_id=$this->Model->add_cate($data);
          if($new_id){
            $this->error('添加成功','catelist');
          }else{
            $this->error('添加失败');
          }
       }
       
    }else{
         $cate_id=input('cate_id/d');
        $cate_list=$this->Model->get_cate_list();
        //$category=[];
       
			
            $category=$this->Model->get_anli_cate_info($cate_id);
           
     
         $this->assign("category",$category);
           $this->assign("cate_list",$cate_list);
        return $this->fetch();
    }
 
}
    //删除分类
    function del_cate(){

       $cate_id=input('cate_id/d');
       //判断改分类下有没有商品
      $this->Model->del_cate($cate_id);
       $this->error('删除成功','catelist');
    }
   
    
    //设置缓存
    function s1(){
        S('name','tom',10);
        S('age',25);
        S('addr','北京');
        S('hobby',array('篮球','排球','棒球'));
        echo "success";
    }
    
    //读取缓存数据
    function s2(){
        echo S('name'),"<br />";
        echo S('age'),"<br />";
        echo S('addr'),"<br />";
        print_r(S('hobby'));echo "<br />";
    }
    
    function s3(){
        //S('age',null);
        echo "delete";
    }
    
    function y1(){
        //外部用户访问的方法
        show_bug($this -> y2());
    }
    function y2(){
        //被其他方法调用的方法，获得指定信息
        //第一次从数据库获得，后续在有效期从缓存里边获得
        $info = S('anli_info');
        if($info){
            return $info;
        } else {
            //没有缓存信息，就从数据库获得数据，再把数据缓存起来
            //连接数据库
            $dt = "apple5s".time();
            S('anli_info',$dt,10);
            return $dt;
        }
    }
}

