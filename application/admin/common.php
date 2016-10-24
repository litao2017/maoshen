<?php
/**
 * @Author: anchen
 * @Date:   2016-05-26 21:32:17
 * @Last Modified by:   anchen
 * @Last Modified time: 2016-06-21 17:32:54
 */

//获得ip
function getIP() { 
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
        $realip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) { 
        $realip = $_SERVER['HTTP_CLIENT_IP']; 
    } else { 
        $realip = $_SERVER['REMOTE_ADDR']; 
        } 
    return $realip; 
}

//调试函数
function pp($data){
    if(is_array($data)){
        echo '<pre>';
        var_dump($data);
    }else{
        echo $data;
    }
    exit;
}
 /**
     * 获取access_token
     * @param array shop_config商城配置，包括微信配置
     * @return string
     */
 function get_access_token($shop_config) {

      
        $appid = $shop_config['appid'];
        $appsecret = $shop_config['appsecret'];
        $expire_time = $shop_config['expire_time'];
        $time = time();
        $access_token=$shop_config['access_token'];
        if (($time - $expire_time) > 7100) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";

            $ret_json = https_request($url);
            $ret = json_decode($ret_json);
            if ($ret -> access_token) {
                $data['id']=$shop_config['id'];
                $data['access_token']=$ret -> access_token;
           $data['expire_time']=$time;
                db("config")->update($data);
              
                $access_token=$ret -> access_token;
            } else{
                return $access_token->errcode;
            }
        } 
        return $access_token;
    } 



 /**
     * 获取url返回信息
     * @param string url api地址
     * @param string data 传送的数据
     * @return string
     */
 function https_request($url, $data = null)
 {
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
  if (!empty($data)){
   curl_setopt($curl, CURLOPT_POST, 1);
   curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
  }
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($curl);
  curl_close($curl);
  return $output;
 }

  /**
 * 生成翻页html代码
 * @param int $count总数
 * @param int $enum一页的数量
 * @param int $page 当前页数
 * @param string $url 当前的url
 * @param int $show_limit 除了头尾 中间显示的数量
 * @return string html代码
 */
function build_page($count,$enum,$page,$url,$show_limit=3){
  $fu=strstr($url,'?')?'&':'?';
  $pages=ceil($count/$enum);
  $page=$page>$pages?$pages:$page;
  $page=$page<1?1:$page;
  $html='';
if($pages<2)
        return $html;
  if($page==1){
   $html.='<span class="page_curr">1</span>';
            if($pages>1){
                if($pages<=$show_limit){
                    for($i=2;$i<=$pages;$i++){
                        $html.='<a href="'.$url.$fu.'page='.$i.'">'.$i.'</a>';
                    }
                }else{
                    for($i=2;$i<=$show_limit;$i++){
                        $html.='<a href="'.$url.$fu.'page='.$i.'">'.$i.'</a>';
                    }
                     $html.='<span >...</span><a href="'.$url.$fu.'page='.$pages.'">'.$pages.'</a>';

                }
                $html.='<a href="'.$url.$fu.'page='.($page+1).'">下一页</a>';
            }
 
           
  }elseif ($page==$pages) {
            if($pages>1){
                 $html.='<a href="'.$url.$fu.'page='.($page-1).'">上一页</a>';
                $html.='<a href="'.$url.$fu.'page=1">1</a>';
               
                if($pages<=$show_limit){
                   
                    for($i=2;$i<$pages;$i++){
                        $html.='<a href="'.$url.$fu.'page='.$i.'">'.$i.'</a>';
                    }
                }else{
                    $start=$pages-$show_limit;
                     $html.='<span >...</span>';
                    for($i=$start;$i<$pages;$i++){

                        $html.='<a href="'.$url.$fu.'page='.$i.'">'.$i.'</a>';
                    }
                     

                }
            }

   $html.='<span class="page_curr">'.$pages.'</span>';
  }else{
             $html.='<a href="'.$url.$fu.'page='.($page-1).'">上一页</a>';
   $html.='<a href="'.$url.$fu.'page=1">1</a>';
   if($show_limit>=($pages-2)){
    for($i=2;$i<$pages;$i++){
     if($i==$page){
      $html.='<span class="page_curr">'.$i.'</span>';
     }else{
      $html.='<a href="'.$url.$fu.'page='.$i.'">'.$i.'</a>';
     }
     
    }
   }else{
    $start=$page-floor($show_limit/2);
    $end=$show_limit+$start-1;
    if($start>2){
     $html.='<span >...</span>';
    }
    for($i=$start;$i<=$end;$i++){
     if($i==1) continue;
     if($i>=$pages) continue;
     if($i==$page){
      $html.='<span class="page_curr">'.$i.'</span>';
     }else{
      $html.='<a href="'.$url.$fu.'page='.$i.'">'.$i.'</a>';
     }
     
    }
   
    if($end<=($pages-2) ){
     $html.='<span >...</span>';
    }
   }
   $html.='<a href="'.$url.$fu.'page='.$pages.'">'.$pages.'</a>';
            $html.='<a href="'.$url.$fu.'page='.($page+1).'">下一页</a>';
  }
  return $html;
 }


    /**
     * 得到当前url
     */
     function get_url(){
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return $url;
    }