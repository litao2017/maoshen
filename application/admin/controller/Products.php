<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Products extends Admin {
  
    public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Products();
    }
	
    function showlist(){
    
        $pro=$this->Model->get_list();
		
        $this -> assign('pro_list', $pro['list']);
		$this -> assign('domain', request()->domain());
		$this -> assign('html_page', $pro['html_page']);
        
         return $this->fetch();
    }
    
    //添加产品
  

    function add(){
    		
		 
		  	
         if(request()->isPost()){
        	
			
		
            $products_id = input('products_id',0,'intval'); 
           
		    $data['products_name'] = input('products_name','','addslashes'); 
           
		   // $data['price']=Request::instance()->post('goods_price','','floatval'); 
           
		    //$data['promote_price']=Request::instance()->post('promote_price','','floatval'); 
            
			//$data['base_sales']=Request::instance()->post('base_sales',0,'intval'); 
           
            $data['cate_id']=input('cate_id',0,'intval'); 
           
		   // $data['number']=Request::instance()->post('goods_number',1000,'intval'); 
           
		    $data['content']=input('content','','htmlspecialchars'); 
            
  			
			
			
            if(empty($data['products_name'])){

             $this->error('产品信息不完整');
           
		    }
        $image=$this->upload_file('image');
        $image &&  $data['image']=$image;
         

  
            
            

            if($products_id){
                //如果有则更新
				
                $this->Model->update_products($data,$products_id);
                return $this->success('更新成功','showlist');
            }else{
                //新增
                $data['create_time']=time(); 
				
                if($new_id=$this->Model->add_products($data)){
                  
                  return $this->success('增加成功','showlist');
                  
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
			 
            $products_id=input('products_id/d');
        	
            $products=[];
			
          
				
                $products=$this->Model->get_pro_info($products_id);
				
				$cate = db('products')->where('products_id',$products_id)->find();
				
				$this->assign('cate_id',$cate['cate_id']);
               
           
			
			
            $cate_list=$this->Model->get_cate_list();
          $this->assign('cate_list',$cate_list);
             $this->assign("products",$products);
            return $this->fetch();
         }
     
    }

        //ajax删除方法
    function delete_ajax(){

       $products_id = input('products_id','','addslashes')?input('products_id','','addslashes'):0; 
       
	   $products_id = explode(',',$products_id);
 
        $this->Model->delete_products($products_id);
        
		return "删除成功";
       
        
       
    }
 //ajax还原
    function products_back(){

        $products = D("products");
    
       
          $udata['is_delete']=0;
    
       $udata['products_id']=intval($_POST['products_id']);
        $rst = $products -> save($udata);
        echo '{"status":1,"msg":"还原成功"}';
 
    }
	


    //分类列表
    function catelist(){
        
 
        $cate_list=$this->Model->get_cate_list();
      $this -> assign('domain', Request::instance()->domain());
        $this->assign("cate_list",$cate_list);
		
        return $this->fetch();
		
		
    }
    //添加或修改商品分类
 public function add_cate(){
		
    if(request()->isPost()){
		
       $cate_id=input('cate_id',0,'intval'); 
       $data['cate_name']=input('cate_name','','addslashes'); 
	    
		if(!$data['cate_name']){
		   
		   $this->error('分类名称不能为空');
		   }
       $data['cate_pid']=input('cate_pid',0,'intval'); 
       if($cate_id){
        if($cate_id==$data['cate_pid']){

          $this->error('添加失败,自己不能成为自己的分类上级');
        }
        $this->Model->update_cate($data,$cate_id);
        $this->error('更新成功','catelist');
       }else{
          $new_id=$this->Model->add_cate($data);
          if($new_id){
          return $this->success('添加成功','catelist');
          }else{
            $this->error('添加失败');
          }
       }
       
    }else{
         $cate_id=input('cate_id/d');
        $cate_list=$this->Model->get_cate_list();
        //$category=[];
        
			$pro_cat=[];
      if($cate_id){
         $pro_cat=$this->Model->get_pro_cat_info($cate_id);
      }
           
           
       
         $this->assign("pro_cat",$pro_cat);
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
	
	
    public function search(){
        //$brand_id=isset($_GET['brand_id'])?intval($_GET['brand_id']):0;
        //$category_id=isset($_GET['category_id'])?intval($_GET['category_id']):0;
       
	    $keyword=input('keyword','','addslashes');
        
		
        if($keyword){
			
			 $list['list']=db("products")->alias('p')->join('pro_cat c','c.cate_id=p.cate_id','left')->where('name','like','%'.$keyword.'%')->select();
			 			 
						 $list['html_page']='';

            //$where = "name like '%".$keyword."%'";
        }else{
			
			 $list=$this->Model->get_list();
			
			}
       
        //$list=db("products")->where($where)->select();
         
             
           
        $this->assign('pro_list',$list['list']);
				 $this->assign('html_page',$list['html_page']);

        
      return  $this->fetch('showlist');
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
        $info = S('pro_info');
        if($info){
            return $info;
        } else {
            //没有缓存信息，就从数据库获得数据，再把数据缓存起来
            //连接数据库
            $dt = "apple5s".time();
            S('pro_info',$dt,10);
            return $dt;
        }
    }
}

