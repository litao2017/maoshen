<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Article extends Admin {
  
    public $Model;
    public function _initialize(){
      parent::_initialize();
        $this->Model=new \app\admin\model\Article();
    }
    function showlist(){
      
        $article=$this->Model->get_list();
        $this -> assign('domain', Request::instance()->domain());
        $this -> assign('article_list', $article['list']);
		$this->assign('html_page',$article['html_page']);        
         return $this->fetch();
    }
    
    //添加商品
  

    function add(){
		
         if(request()->isPost()){
            $article_id=Request::instance()->post('article_id',0,'intval'); 
            $data['article_name']=Request::instance()->post('article_name','','addslashes'); 
            $data['article_content']=Request::instance()->post('article_content','','htmlspecialchars'); 
			$data['author']=Request::instance()->post('author','','htmlspecialchars'); 
	
			
			if(!$data['article_name']||!$data['article_content']){
				
			$this->error('标题或内容不能为空');
				
				}
			
         	$data['cate_id']=Request::instance()->post('cate_id',0,'intval'); 
           
            $data['status']=Request::instance()->post('status',0,'intval'); 
           
            

            if($article_id){
                //如果有article_id则更新
                $this->Model->update_article($data,$article_id);
                if($_POST['html']){
                  $htmlModel=new \app\admin\controller\Html();
                  $htmlModel->tran_content_by_id('article',$article_id);
                }
                $this->error('更新成功','showlist');
            }else{
                //新增
               $data['create_time']=time(); 
                if($article_id=$this->Model->add_article($data)){
                  if($_POST['html']){
                    $htmlModel=new \app\admin\controller\Html();
                    $htmlModel->tran_content_by_id('article',$article_id);
                  }
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
    $data['article_src']=$info->getFilename(); // 输出 42a79759f284b767dfcb2a0197904287.jpg
}else{
    // 上传失败获取错误信息
    echo $file->getError();
    exit;
}*/
             
         }else{
            $article_id=Request::instance()->param('article_id','','intval');
            //$article=[];
           
                $article=$this->Model->get_article_info($article_id);
               
          
			$cat_list=$this->Model->get_cat_list();
            $this->assign('cat_list',$cat_list);
             $this->assign("article",$article);
            return $this->fetch();
         }
     
    }
	
  function edit(){
	  
	  $article_id=input("article_id/d");  
	  
	  $article = db('article')->where('article_id',$article_id)->find();
	  
	  $this->assign('article',$article);
	  
	   $cat_list=$this->Model->get_cat_list();
	   
	  
      
		$this->assign('cate_id',$article['cate_id']);
        $this->assign("cat_list",$cat_list);
	 
	  if(request()->isPost()){
		  
		 	 $article_id=Request::instance()->post('article_id',0,'intval'); 
            
			$article_name=Request::instance()->post('article_name','','addslashes'); 
            
			$article_content=Request::instance()->post('article_content','','htmlspecialchars'); 
			
			$data['author']=Request::instance()->post('author','','htmlspecialchars'); 

			
			if(!$article_name||!$article_content){
				
			$this->error('标题或内容不能为空');
				
				}
         	
			$cate_id=Request::instance()->post('cate_id',0,'intval'); 
           
            $status=Request::instance()->post('status',0,'intval'); 
			
			$data = ['article_name'=>$article_name,'article_content'=>$article_content,'cate_id'=>$cate_id,'status'=>$status];
			
		  
		   $update = db('article')->where('article_id',$article_id)->update($data); 
		 
		  if($update){
			  
			 return $this->success('修改成功','showlist');
			  
			  }
		  
		  
		  
		  }
	  
	  
	  return $this->fetch();
	  
	  
	  }
	
	
	 public function search(){
        //$brand_id=isset($_GET['brand_id'])?intval($_GET['brand_id']):0;
        //$category_id=isset($_GET['category_id'])?intval($_GET['category_id']):0;
       
	    $keyword=input('keyword','','addslashes');
        
		
        if($keyword){
			
			 $list['list']=db("article")->alias('a')->join('article_cat c','c.cate_id=a.cate_id','left')->where('article_name','like','%'.$keyword.'%')->select();
			 			 $list['html_page']='';

            //$where = "name like '%".$keyword."%'";
        }else{
			 $list=$this->Model->get_list();
			
			}
       
        //$list=db("products")->where($where)->select();
         
        $this -> assign('domain', Request::instance()->domain());    
           
        $this->assign('article_list',$list['list']);
        	   $this->assign('html_page',$list['html_page']);

      return  $this->fetch('showlist');
    }
	
	
	
	
	
	
  function delete(){

       $article_id=input("article_id/d"); 
       
 
        $this->Model->delete_article($article_id);
        $this->error('删除成功','showlist');
        
       
    }
        //ajax删除方法
    function delete_ajax(){

       $article_id = input('article_id','','addslashes')?input('article_id','','addslashes'):0; 
    
		$article_id = explode(',',$article_id);
		
        $del = $this->Model->delete_article($article_id);
		
		if($del){
	
        return "删除成功";
       
		}
       
    }
 //ajax还原
    function article_back(){

        $article = D("article");
    
       
          $udata['is_delete']=0;
    
       $udata['article_id']=intval($_POST['article_id']);
        $rst = $article -> save($udata);
        echo '{"status":1,"msg":"还原成功"}';
 
    }
	
	public  function catlist(){
        
 
        $cat_list=$this->Model->get_cat_list();
       $this -> assign('domain', Request::instance()->domain());

        $this->assign("cat_list",$cat_list);
        return $this->fetch();
    }
	
  public function add_cate(){

    if(request()->isPost()){
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
        $this->Model->update_cat($data,$cate_id);
        $this->error('更新成功','catlist');
       }else{
          $new_id=$this->Model->add_cat($data);
          if($new_id){
            if($_POST['html']){
                  $htmlModel=new \app\admin\controller\Html();
                  $htmlModel->tran_list('article',$article_id);
                }
            $this->error('添加成功','catlist');
          }else{
            $this->error('添加失败');
          }
       }
       
    }else{
         $cate_id=input('cate_id/d');
        $cat_list=$this->Model->get_cat_list();
        //$article_cat=[];
        
            $article_cat=$this->Model->get_article_cat_info($cate_id);
           
     
         $this->assign("article_cate",$article_cat);
           $this->assign("cat_list",$cat_list);
        return $this->fetch();
    }
 
}
    //删除分类
    function del_cate(){

       $cate_id=input('cate_id/d');
       $article=db("article")->where("cate_id",$cate_id)->find();
       if($article){
        $this->error('该分类下还有文章，请先删除文章');
       }else{
          $this->Model->del_cat($cate_id);
        $this->error('删除成功','catlist');
       }
     
    
    }
   





}

