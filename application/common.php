<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
error_reporting();

function test($par){
	
	var_dump($par);
	
	exit;
	
	
	}
    function createdir($path){
    if (is_dir($path)){  //判断目录存在否，存在不创建
    }else{ //不存在创建
       $re=mkdir($path,0755,true); //第三个参数为true即可以创建多极目录
      if ($re){
          
       }else{
               echo "目录创建失败";
      }
    }
}

//分页函数 
function page($page,$total,$pagesize,$pagelen=5,$id){
    $pagecode = "";             
    $page = intval($page);  
    $total = intval($total);    
    if(!$total) return array(); 
    $pages = ceil($total/$pagesize);    
    if($page<1) $page = 1;
    if($page>$pages) $page = $pages;
    $offset = $pagesize*($page-1);
    $init = 1;//起始页码数
    $max = $pages;//结束页码数
    $pagelen = ($pagelen%2)?$pagelen:$pagelen+1;
    $pageoffset = ($pagelen-1)/2;
    if($page!=1){
    $pagecode.="<a href=\"".$id."_1.html\">首页</a>";//首页 
    $pagecode.="<a href=\"".$id."_".($page-1).".html\"><font>上一页</font></a>";//上一页
    }
    if($pages>$pagelen){
        if($page<=$pageoffset){
            $init=1;
            $max = $pagelen;
        }else{                    //如果当前页大于左偏移
            if($page+$pageoffset>=$pages+1){
                $init = $pages-$pagelen+1;
            }else{
                $init = $page-$pageoffset;
                $max = $page+$pageoffset;
            }
         }
    }
    for($i=$init;$i<=$max;$i++){
        if($i==$page){
        $pagecode.='<a id="dif">'.$i.'</a>';
        } else {
        $pagecode.="<a href=\"".$id."_".$i.".html\">".$i."</a>";       //显示的几页
        }
    }
    if($page!=$pages){
        $pagecode.="<a href=\"".$id."_".($page+1).".html\">下一页</a>";//下一页
        $pagecode.="<a href=\"".$id."_".$pages.".html\">尾页</a>"; //最后一页
    }
       // return array('pagecode'=>$pagecode,'sqllimit'=>' limit '.$offset.','.$pagesize); //以数组的形式输出
       return $pagecode;
} 
?>