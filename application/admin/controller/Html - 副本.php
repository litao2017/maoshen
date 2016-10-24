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
	  
	 $mkHtml = 0;
	 $total = 0;
	 
	 if (request()->isPost()){
		
		
			
	if($_POST['isContent']!='1' && $_POST['isLists']!='1'){
		
		$this->error("内容页，分类页列表页至少选择一个");
		
		
	}	
	// print_r($_POST);exit;
	//检查根目录是否可以生成文件
	// print_r($_POST);exit;
	$start_date = strtotime($_POST['start_date']);
	
	
	//文章的详细页网址：
	if ($start_date){
		$where = "create_time >=".$start_date;
	}else{
		$where = '';
	}
	// echo $where;exit;
	// echo $start_date;exit;
	$urls = ROOT_PATH.'urls.txt';
	
	if (!@fopen($urls,'w')){
		$this->error( '错误：根目录不允许写文件！' );
	}
	
	//读取配置文件，看是否已经开启了静态模式：
	/*if ($GLOBALS["CONF"]["CatchOpen"]!="1"){
		$this->error( '错误：系统未开启生成HTML设置！');
	}*/
	$host = 'http://'.$_SERVER['HTTP_HOST'];
	
	
	//对的


	if ($_POST['isHome']){
				
		$url[] = $host.'/index/index';
		
	}
	
	//读取文章的数据，生成网址：
	if ($_POST['article']){
		
		// echo "select * from {P}_news_con where iffb='1' {$where} ";exit;
		if ($_POST['isContent']){
			
			//文章的详细页网址：
		
			$record = db('article')->where('cat_id',8)->where($where)->select();
	
			foreach($record as $value){
				$id=$value['article_id'];
				$url[]=$host."/index/article/about_us/article_id/".$id;
				
				
			}
			
			
			$record = db('article')->where('cat_id','<>',8)->where($where)->select();
			
				foreach($record as $value){
				$id=$value['article_id'];
				$url[]=$host."/index/article/content/article_id/".$id;
				
			}
		}
		
		if ($_POST['isLists']){

			//分类的网址：
			$url[]=$host."/index/article/about_us";
			
			$num =  db('article')->where('cat_id','<>',8)->count(); 
			
			$page = ceil($num/15);
			
			if($page>1){
				
				for($i=1;$i<=$page;$i++){
					
					$url[]=$host."/index/article/lists?page=".$i;
					
					}	
				
			}
				
				
			
			$url[]=$host."/index/article/lists";
			
			
			
			
			
			
			
			$record = db('article_cat')->where('cat_pid',7)->select();
			
			foreach ($record as $value){
				
				
				
				$cat_id=$value['cat_id'];
				
				
				$num =  db('article')->where('cat_id',$cat_id)->count(); 
			
				$page = ceil($num/15);
			
				if($page>1){
				
				for($i=1;$i<=$page;$i++){
					
					$url[]=$host."/index/article/lists/cat_id/".$cat_id."?page=".$i;
					
					}
				
				
				}
								
				
				
					$url[]=$host."/index/article/lists/cat_id/".$cat_id;
				
								

			}
			
		}
		
			//首页：
			//$url[]=$host."/news/,news/index.html";
	
		//}
		// print_r($url);exit;
		
	
		
	}
	//}
	
	//读取产品的数据，生成网址：
	if ($_POST['products']){
		
	//	分类首页
	
		$url[] = $host.'/index/products';
		
		
		if ($_POST['isContents']){
			//产品的详细页网址：
			$record = db('products')->where($where)->select();
			foreach ($record as $value){
				$id=$value['pro_id'];
				$url[]=$host."/index/products/pro_info/pro_id/".$id;
			}
		}

		
		if ($_POST['isLists']){

			//分类的网址：
			$record = db('pro_cat')->select();
			
			/*foreach ($record as $value){
				$id=$value['cate_id'];
				$url[]=$host."/index/products/pro_info/pro_id/".$id;
			}*/

			//首页：
		}

		
	
	}
	
	
	//读取图文的数据，生成网址：
	/*if ($_POST['imgtext']){
		
		if ($_POST['isContent']){
			//图片的详细页网址：
			$msql->query("select * from {P}_photo_con where iffb='1' {$where} ");
			while ($msql->next_record()){
				$id=$msql->f('id');
				$url[]=$host."/photo/html/?".$id.".html,photo/html/{$id}.html";
			}
		}
		
		if ($_POST['isLists']){

			//分类的网址：
			$msql->query("select * from {P}_photo_cat");
			while ($msql->next_record()){
				$catid=$msql->f("catid");
				
				if($ifchannel!="1"){
					$url[]=$host."/photo/class/?".$catid.".html,photo/class/{$catid}.html";
				}
			}

			//首页：
			$url[]=$host."/photo/,photo/index.html";		
		}


	}*/
	
	//首页的网址：
	
	//生成xml文件：
	
	
	
	
	if (is_array($url)){
		
		$fp1 = @fopen($urls,'w');
		$total = count($url);
		// echo $total;exit;
		foreach ($url as $v){
			//生成google的xml：
			$body = "$v\r\n";
			// echo $body;exit;
			fwrite($fp1,$body);
		
		}
		fclose($fp1);
		@chmod($urls,0755);
		$mkHtml = 1;
		// sayok( 'SITEMAP已经成功生成！', "", "确定" );
	
	}else{
	
		
	$this->error( '错误：没有需要生成的网址！');
	
	}
	
}


//读取网站中有多少个可以生成sitemap的模块：
/*$modsStr = 'page,news,photo,product,shop,down,job,view';

$modArr = explode(',',$modsStr);
$mods = array();
$modsName = array();
$selectArr = array();
$i = 0;
foreach ($modArr as $mod){
	$sql = "select coltype,sname from {P}_base_coltype
			where coltype = '{$mod}' and installed = 1";
	$fsql->query($sql);
	$fsql->next_record();
	if ($fsql->f('coltype')){
		// $mods[] = $fsql->f('coltype');
		// $modNameArr[] = $fsql->f('sname');
		
		$selectArr[$i]['val'] = $fsql->f('coltype');
		$selectArr[$i]['name'] = $fsql->f('sname');
		$i ++;
		
	}*/


	
	
   	
	  $this->assign('date',time());
	  $this->assign('mkHtml',$mkHtml);
	  
	  $this->assign('total',$total);
	 
	 return $this->fetch();  
	
	 
	 
}    
 	  
	  
	  

	  
	  
	  
   public function html_mk(){
	   
	  
		$urls = ROOT_PATH.'urls.txt';
		
		$body = trim(file_get_contents($urls));



// echo $body;exit;
		$arr = explode("\r\n",$body);
		
		
		
		$nowTotal = count($arr);
		
		
		
		
		
// echo $nowTotal.'<br>';
// print_r($arr);exit;
		$time=time();
		
		$host = 'http://'.$_SERVER['HTTP_HOST'];
		
		if (!empty($arr)){
			
			$i = 0;
			
			foreach ($arr as $url){
				
				if ($url){
					$i++;
				if ($i == 1){
				// echo $i.'<br>';
				// echo "$url\r\n<br><br>";
				
				// print_r($arr1);exit;
				
				//$html = @file_get_contents($url);
				
				
					//print_r ($html);
				//替换<!-reload-!>
								
								//exit;	
					//生成文件
				
					 $arr1 = explode('/',$url); //$arr3[0]= product/class/22
					 
					 $arr2 = explode('=',$url);
					 //print_r($arr1);
					//exit;
					//判断模块
					
					
					
					if($arr1[4]=='index'){
						//首页
						
						
						$this->buildHtml(end($arr1),HTML_PATH.$arr1['4'].'/',$url);
						
					//$x = file_put_contents(HTML_PATH.'/index/index.html',$html);
						
					//echo HTML_PATH.$arr1['3'].'/'.$arr1['4'].'/';
					
					
						
					}else if($arr1[4]!='index'&&$arr1[5]!='index'&&$arr1[5]!=false){
                        //详情页 分类页
						if(!$arr1[6]){		
										
							if($arr2[1]){
								
						$this->buildHtml($arr1['5'].'_'.$arr2[1],HTML_PATH.$arr1['4'].'/',$url);

								
								}else{
									
	
						
						$this->buildHtml($arr1['5'],HTML_PATH.$arr1['4'].'/',$url);
							
							}
						
						}else{
							
								if($arr2[1]){
									
									
								}else{
							
						$this->buildHtml($arr1['5'].'-'.$arr1['7'].'_'.$arr2[1],HTML_PATH.$arr1['4'].'/',$url);}
		
							
							}
							
						
					}else {
						//模块首页
	                    $this->buildHtml('index',HTML_PATH.$arr1['4'].'/',$url);	

					}
				
				// $html=str_replace("<!-reload-!>",$reload,$html);
				 
				 //替换<!-reload-!>
				 
				 
				
				$fp1 = @fopen($urls,'w');
				fwrite($fp1,'');
				fclose($fp1);
				@chmod($urls,0755);
				$goon = $i;//是否继续的标识；
				
					}else{
				// echo $i.'<br>';
				//其他写回原表；
				$fp1 = @fopen($urls,'a');
				$body1 = "$url\r\n";
				// echo $body1;
				fwrite($fp1,$body1);
				fclose($fp1);
				@chmod($urls,0755);
				
					}
			// break;
			// echo $path;exit;
				}
			}
		}else{

	
			$goon = 0;
			
			

	}

//计算完成比例：
	$total = input('total/d');
	$nowTotal= $total-$nowTotal;
	$per =  $nowTotal/$total*100;
	$per = (int)$per;
// $per = (int)($nowTotal / $total)*100;
	$url = '/admin/html/html_mk/total/'.$total;
// echo $url;exit;
// echo $per;exit;
// echo $goon;exit;
	
	
	$this->assign('total',$total);
	$this->assign('url',$url);
	 $this->assign('per',$per);
	 
	  $this->assign('goon',$goon);
	   
	  return $this->fetch();
	  
	
	
	 
		}
   
   
    protected function buildHtml($htmlfile='',$htmlpath='',$url='') {
	
	
		unlink($htmlpath.$htmlfile.HTML_FILE_SUFFIX);
		
		$content = @file_get_contents($url);	
		
		if($content){
			
        $htmlpath   = !empty($htmlpath)?$htmlpath:HTML_PATH;
        $htmlfile =  $htmlpath.$htmlfile.HTML_FILE_SUFFIX;
        if(!is_dir(dirname($htmlfile)))
            // 如果静态目录不存在 则创建
            mkdir(dirname($htmlfile),0755,true);
				
				
			
        if(false === file_put_contents($htmlfile,$content))
		
		$this->error('出错');
       
	    return $content;
		
		}else{
			
		$this->error('出错');
				
			}
		
	
	}
   
   
   
   
   
   
 
 
 
   
   
   
}