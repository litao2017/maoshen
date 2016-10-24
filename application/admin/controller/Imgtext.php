<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Imgtext extends Admin{
  
    public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Imgtext();
    }
    function showlist(){
    
        $img_text=$this->Model->get_list();
		
		foreach($img_text['list'] as $k=>$value){
			
			$imgurls=[];
			
			$imgurls = array_filter(explode(',',$value['imgurls']));

			$img_text['list'][$k]['imgurls'] = $imgurls;

			}
		
		
        $this -> assign('img_text_list', $img_text['list']);
		
		$this -> assign('html_page', $img_text['html_page']);
        
         return $this->fetch();
    }
    
    //添加案例
  

    function add(){
    		
		 
		  	
         if(Request::instance()->isPost()){
			 
         // pp(input());
            $it_id=Request::instance()->post('it_id',0,'intval'); 
           
		    $data['title']=Request::instance()->post('title','','addslashes'); 
            
           
		    $data['cate_id']=Request::instance()->post('cate_id',0,'intval'); 
           
		   
            $data['content']=Request::instance()->post('content','','htmlspecialchars'); 
            
			$data['imgurls'] = input('imgurls','','addslashes');
  			
		
          
            if($it_id){
                //如果有id则更新
				if(!$data['imgurls']){
					unset($data['imgurls']);
					}
				
                $this->Model->update_img_text($data,$it_id);
                $this->error('更新成功','showlist');
            }else{
                //新增
				
					
           if(empty($data['title'])||empty($data['imgurls'])) $this->error('信息不完整');

                $data['create_time']=time();
				 
                if($new_id=$this->Model->add_img_text($data)){
                  
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
			 
            $it_id = input('it_id/d');
        	
            //$anli=[];
           
				
                $img_text = $this->Model->get_img_text_info($it_id);
				
				if($img_text){
				
				foreach($img_text as $k=>$imgurls){
			
			
						$imgurls = array_filter(explode(',',$imgurls));

							$img_text['imgurls'] = $imgurls;

			}
				
				}
				
				$cate = db('img_text')->where('it_id',$it_id)->find();
				
				$this->assign('cate_id',$cate['cate_id']);
               
        
			
			
            $cate_list=$this->Model->get_cate_list();
          $this->assign('cate_list',$cate_list);
             $this->assign("img_text",$img_text);
            return $this->fetch();
         }
     
    }


	public function upload(){
		
		
			if(request()->isAjax()){
		
				$dir = date('Ymd',time());
				
				 $img_name = time(); 
			
			  $move_path=ROOT_PATH.'/upload/image/'.$dir.'/';
		
   			if(!is_dir($move_path)) mkdir($move_path);
			

			 
        foreach($_FILES['images']['name'] as $k=>$file)  
        {  
				
				$ext[]=strrchr($file,'.');
				
				}
		 
        foreach($_FILES['images']['tmp_name'] as $k=>$file)  
        {  
		
				
            move_uploaded_file($file, $move_path.$img_name.$k.$ext[$k]);  
           
		    $data.=  '/upload/image/'.$dir.'/'.$img_name.$k.$ext[$k].',';  
        
			
			
		}  
    
	
			}
			
			
		//print_r($_FILES['images']);
		
		
		return $data;
			
		
			
			
		
		
			
			
			
	}
		
		
			
			

	
	










	
	
	 public function search(){
        //$brand_id=isset($_GET['brand_id'])?intval($_GET['brand_id']):0;
        //$category_id=isset($_GET['category_id'])?intval($_GET['category_id']):0;
       
	    $keyword=input('keyword','','addslashes');
        
		
        if($keyword){
			
			 $list['list']=db("img_text")->alias('i')->join('img_text_cate c','c.cate_id=i.cate_id','left')->where('title','like','%'.$keyword.'%')->select();
			 
			 $list['html_page']='';
            //$where = "name like '%".$keyword."%'";
        }else{
			
			 $list=$this->Model->get_list();
			
			}
			
			foreach($list['list'] as $k=>$value){
			
			$imgurls=[];
			
			$imgurls = array_filter(explode(',',$value['imgurls']));

			$list['list'][$k]['imgurls'] = $imgurls;

			}
       
        //$list=db("products")->where($where)->select();
 
        $this->assign('img_text_list',$list['list']);
		
		 $this->assign('html_page',$list['html_page']);
        
      return  $this->fetch('showlist');
   
   
    }
	




        //ajax删除方法
   public function delete_ajax(){
		
		$it_id = input('it_id','','addslashes')?input('it_id','','addslashes'):0;
		
		
      
		$it_id = explode(',',$it_id);
		 	
        $del =  $this->Model->delete_img_text($it_id);
		
		
		
		if($del){
			
         return'删除成功';
		
		}
    }
 //ajax还原
    /*function img_text_back(){

        $goods = D("Goods");
    
       
          $udata['is_delete']=0;
    
       $udata['anli_id']=intval($_POST['anli_id']);
        $rst = $anli -> save($udata);
        echo '{"status":1,"msg":"还原成功"}';
 
    }*/
    /*function recycle(){

         $where=$this->seller_where;
        $anli = D("anli");
        $where['is_delete']=1;
        //1. 获得当前记录总条数
        $total = $anli ->where($where)-> count();
        $per = 1;
        //2. 实例化分页类对象
        $page = new \Think\Page($total, $per); //autoload

        $show       = $page->show();// 分页显示输出
     
      $list = $anli->where($where)->order('add_time desc')->limit($page->firstRow.','.$page->listRows)->select();
      
         //获得分类
         $category_list=D("Goods_category")->where($where)->select();
         foreach ($category_list as $key => $value) {
             $arr_category[$value['category_id']]=$value['category_name'];
         }

         //获得品牌
         $brand_list=D("Goods_brand")->where($where)->select();
         $this->assign('brand_list',$brand_list);
         $this->assign('category_list',$category_list);
         $this->assign('arr_category',$arr_category);
        $this -> assign('list', $list);
        $this -> assign('show', $show);
        $this -> display();
       
        
       
    }*/


    //分类列表
    function catelist(){
        
 
        $cate_list=$this->Model->get_cate_list();
      

        $this->assign("cate_list",$cate_list);
        return $this->fetch();
    }
    //添加或修改商品分类
 public function add_cate(){

    if(request()->isPost()){
		
       $cate_id=Request::instance()->post('cate_id',0,'intval'); 
       $data['cate_name']=Request::instance()->post('cate_name','','addslashes'); 
	   if(empty($data['cate_name'])){
		   
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
		
        $cate_id=input('cate_id/d')?input('cate_id/d'):0;
		 
        $cate_list=$this->Model->get_cate_list();
        //$category=[];
        
			
            $category=$this->Model->get_cate_info($cate_id);
           
     
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

