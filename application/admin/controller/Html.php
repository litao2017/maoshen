<?php
namespace app\admin\controller;
use app\admin\controller\Admin;
use think\Request;
class Html extends Admin {
	  public $Model;
    public function _initialize(){
   		 parent::_initialize();
   		 $this->Model=new \app\admin\model\Html();
    }
     public function html(){
	  //文章模型
	$article['name']='文章';
	$article['model']='article';
	$article['all_num']=$this->Model->get_content_num('article');
	$article['html_num']=$this->Model->get_content_num('article',1);
	
	//产品模型
	$products['name']='产品';
	$products['model']='products';
	$products['all_num']=$this->Model->get_content_num('products');
	$products['html_num']=$this->Model->get_content_num('products',1);
	
//案例模型
	$anli['name']='案例';
	$anli['model']='anli';
	$anli['all_num']=$this->Model->get_content_num('anli');
	$anli['html_num']=$this->Model->get_content_num('anli',1);
	
//图文模型
	$images['name']='图文';
	$images['model']='imgtext';
	$images['all_num']=$this->Model->get_content_num('imgtext');
	$images['html_num']=$this->Model->get_content_num('imgtext',1);
	$models=[$article,$products,$anli,$images];
	$this->assign("models",$models);
	 return $this->fetch();  
	
	 
	 
}    

public function tran_index(){
	$html_filepath='index.html';
	$url=request()->domain().'/index/index/index';
	$this->tran_html($url,$html_filepath);
	echo '生成首页成功<br/>';
	echo '<button onclick="location.href=\''.url('html').'\'" style="width:120px;display:block;  margin-bottom: 10px; background-color: #3c6;color:#fff;border-radius: 5px;line-height: 30px;border:none;padding: 5px 5px;margin: 10px auto;">返回</button>';
}
public function tran_list(){  
	$model=input("model","","addslashes");
	$cate_id=input("cate_id",0,"intval");
	$page=input("page",1,"intval");
		$domain=request()->domain();
		//$list=db($model.'_cate')->order('cate_id desc')->select();
		$dir=$model.'/';
		createdir($dir);
		$html_filepath=$dir.'lists_'.$cate_id.'_'.$page.'.html';
		if($cate_id>0){
			//列表页
			$url=$domain."/index/{$model}/lists/cate_id/".$cate_id."/page/".$page;
		}else{
			//分类首页
			$url=$domain."/index/{$model}/index/cate_id/".$cate_id."/page/".$page;
		}
				

				$this->tran_html($url,$html_filepath);

				$where=$cate_id?'cate_id={$cate_id} and status=1':'status=1';

				 $count=db($model)->where($where)->count();
           		 $pagesize=$this->config['pagesize'];  //每页多少个
                $pages=ceil($count/$pagesize);  //总页数
				if($page<$pages){
					echo '生成列表'.$html_filepath.' 成功<br/>';
					$next_url=$domain."/admin/html/tran_list/model/".$model."/cate_id/".$cate_id."?page=".($page+1);
					echo '<script type="text/javascript">this.location="'.$next_url.'"; </script>';
				}else{
					echo '生成列表生成完成<br/>';
					echo '<button onclick="location.href=\''.url('html').'\'" style="width:120px;display:block;  margin-bottom: 10px; background-color: #3c6;color:#fff;border-radius: 5px;line-height: 30px;border:none;padding: 5px 5px;margin: 10px auto;">返回</button>';
				}
				
	//echo '<button onclick="location.href=\''.url('html').'\'" style="width:120px;display:block;  margin-bottom: 10px; background-color: #3c6;color:#fff;border-radius: 5px;line-height: 30px;border:none;padding: 5px 5px;margin: 10px auto;">返回</button>';
}

 		public function tran_list1(){  
 			$model=input("model","","addslashes");
 			$domain=request()->domain();
 			$list=db($model.'_cate')->order('cate_id desc')->select();
 			$dir=$model.'/';
			createdir($dir);
			if($list){
				foreach ($list as $key => $value) {

	 				$html_filepath=$dir.'lists_'.$value['cate_id'].'.html';
	 				$url=$domain."/index/{$model}/lists/cate_id/".$value['cate_id'];
	 				$this->tran_html($url,$html_filepath);
			
					if($key==0){
						$this->tran_html($url,$dir.'index.html');
						
						echo '生成列表'.$dir.'index.html 成功<br/>';
					}
					echo '生成列表'.$html_filepath.' 成功<br/>';
	 			}
			}
			echo '<button onclick="location.href=\''.url('html').'\'" style="width:120px;display:block;  margin-bottom: 10px; background-color: #3c6;color:#fff;border-radius: 5px;line-height: 30px;border:none;padding: 5px 5px;margin: 10px auto;">返回</button>';
 			
 		}
 			public function tran_all_content(){
 				$model=input("model","","addslashes");
 				db($model)->where('html=1')->update(["html"=>0]);
 				$this->redirect('tran_content',["model"=>$model]);
 			}
	public function tran_content(){
		$model=input("model","","addslashes");
		$row=db($model)->where('html=0')->find();

		if(!$row){

			echo '全部html生成完成';
			echo '<button onclick="location.href=\''.url('html').'\'" style="width:120px;display:block;  margin-bottom: 10px; background-color: #3c6;color:#fff;border-radius: 5px;line-height: 30px;border:none;padding: 5px 5px;margin: 10px auto;">返回</button>';
			exit;

		}

	

	$dir=$model.'/';
	createdir($dir);
	$id=$row[$model.'_id'];
	$title=$row[$model.'_name'];
	$html_filepath=$dir.'/'.$id.'.html';


	$url=request()->domain()."/index/{$model}/content/{$model}_id/".$id;
	$this->tran_html($url,$html_filepath);
		//mysql_query("update article set html=1 where id=".$id."",$conn);
	db($model)->update(["html"=>1,"{$model}_id"=>$id]);
		
	//$this->tran_content($model);
	$this_url=get_url();
	echo '生成标题为《'.$title.'》html成功';
	echo '<script type="text/javascript">this.location="'.$this_url.'"; </script>';

	}
public function tran_content_by_id($model,$id){
	$dir=$model.'/';
	createdir($dir);
	$html_filepath=$dir.'/'.$id.'.html';


	$url=request()->domain()."/index/{$model}/content/{$model}_id/".$id;
	$this->tran_html($url,$html_filepath);
		//mysql_query("update article set html=1 where id=".$id."",$conn);
	db($model)->update(["html"=>1,"{$model}_id"=>$id]);

}
public function tran_content_by_cateid($model,$cate_id){
	$domain=request()->domain();
	$list=db($model.'_cate')->order('cate_id desc')->select();
	$dir=$model.'/';
	createdir($dir);
	//html列表
	if($list){
		foreach ($list as $key => $value) {
			$html_filepath=$dir.'lists_'.$value['cate_id'].'.html';
			$url=$domain."/index/{$model}/lists/cate_id/".$value['cate_id'];
			$this->tran_html($url,$html_filepath);

			if($key==0){
				$this->tran_html($url,$dir.'index.html');

			 }
		
		}
	}
	//html分类下文章
	$article_list=db($model)->where("cate_id",$cate_id)->select();
	if($article_list){
		foreach ($article_list as $key => $value) {
			$this->tran_content_by_id($model,$value[$model.'_id']);
		}
		db($model)->where("cate_id",$cate_id)->update(["html"=>1]);
	}
	

}

 	public function tran_html($url,$html_filepath){
 		$url.='/is_html/1';

		$content=file_get_contents($url);	
		file_put_contents($html_filepath,$content);
	}
	/*public function tran_replace($content,$model){
			$arr1=["index/{$model}/lists/cate_id/","index/{$model}/content/{$model}_id/"];
			$arr2=["{$model}/lists_","{$model}/"];
			$content=str_replace($arr1, $arr2, $content);
			return $content;
	}*/
}
?>



