<?php
/**
 * @Author: anchen
 * @Date:   2016-06-03 18:57:55
 * @Last Modified by:   anchen
 * @Last Modified time: 2016-06-21 21:19:41
 */
/*$my_route=new \think\Route();
$my_route->get('aa/:id','Order/makeorder');*/
/*
调试函数
 */
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
     * 得到当前url
     */
     function get_url(){
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return $url;
    }
   /*
   得到ip
    */
    function get_iP() { 
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
        $realip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) { 
        $realip = $_SERVER['HTTP_CLIENT_IP']; 
    } else { 
        $realip = $_SERVER['REMOTE_ADDR']; 
        } 
        return $realip; 
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

        if ( $expire_time < $time) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";

            $ret_json = https_request($url);
            $ret = json_decode($ret_json);
            if ($ret -> access_token) {
                $data['id']=$shop_config['id'];
                $data['access_token']=$ret -> access_token;
                $data['expire_time']=$time+7180;
                db("config")->update($data);
              
                $access_token=$ret -> access_token;
            } else{
                return $ret->errcode;
            }
        } 
        return $access_token;
    } 
 /**
     * 获取signPackage
     * @param array shop_config商城配置，包括微信配置
     * @return string
     */
 function get_sign_package($shop_config) {

      
        $jsapiTicket = get_jsapi_ticket($shop_config);


    $url=get_url();
    $timeStamp = time();
    $nonceStr = rand(100000, 999999);

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timeStamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $shop_config['appid'],
      "nonceStr"  => $nonceStr,
      "timeStamp" => $timeStamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
    } 

function get_address_sigin($options){
     $url=get_url();
     $appid=$options['appid'];
     $Wechat=new \think\WechatApi($options);
        $oauth2_url=$Wechat->getOauthRedirect($url,1,'snsapi_base');
         if(!empty($_GET['code'])){
            $code = $_GET['code'];
            $openid_access_token = $Wechat->getOauthAccessToken($code);
            $access_token = $openid_access_token['access_token'];
        }else{
            header('location:'.$oauth2_url);
        }
        $timeStamp = time();
        $nonceStr = rand(100000, 999999);
        $array = array('appid' => $appid, 'url' => $url, 'timestamp' => $timeStamp, 'noncestr' => $nonceStr, 'accesstoken' => $access_token);
        ksort($array);
        $signPars = '';

        foreach ($array as $k => $v) {
            if ('' != $v) {
                if ($signPars == '') {
                    $signPars .= $k . '=' . $v;
                }
                else {
                    $signPars .= '&' . $k . '=' . $v;
                }
            }
        }

        $result = array('appId' => $appid, 'url' => $url, 'timeStamp' => $timeStamp, 'nonceStr' => $nonceStr, 'addrSign' => SHA1($signPars));
        return $result;

   
    /*
     $nonceStr = create_rond_str();
     $timestamp = time();
  $string = "accesstoken=$accesstoken&appid=$appid&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $addrSignStr= sha1($string);

$addrSign = array(
      "appId"     => $appid,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "addrSign" => $addrSignStr,
      "rawString" => $string
    );
return $addrSign;*/
}

/*
获得随机字符串
length 字符串长度
 */
  function create_rond_str($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }
 /**
     * 获取jsapi_ticket
     * @param array shop_config商城配置，包括微信配置
     * @return string
     */
   function get_jsapi_ticket($shop_config) {
     $expire_time = $shop_config['jsapi_expire_time'];
     $time = time();
     $jsapi_ticket=$shop_config['jsapi_ticket'];

    if ($expire_time < $time) {
          $access_token = get_access_token($shop_config);

          $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$access_token";
          $res = json_decode(https_request($url));
          $ticket = $res->ticket;


       if ($ticket) {
            $data['id']=$shop_config['id'];
            $data['jsapi_ticket']=$ticket;
            $data['jsapi_expire_time']=$time+7180;
            db("config")->update($data);
            return $ticket;
        } else{
            return $ret->errcode;
        }
    } 

    return $jsapi_ticket;
  }
  /*
  从微信服务器上下载远程图片
  url 远程图片url地址
   */
    function downloadimageformweixin($url) {  

        $ch = curl_init ();  
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );  
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );  
        curl_setopt ( $ch, CURLOPT_URL, $url );  
        ob_start ();  
        curl_exec ( $ch );  
        $return_content = ob_get_contents ();  
        ob_end_clean ();  

        $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );  
        return $return_content;  
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
/*
生成多级目录
dir 路径
index 是否生成index.html文件
 */
 function make_dir($dir, $index = true) {
        $res = true;
        if(!is_dir($dir)) {
            $res = @mkdir($dir, 0777,true);
            $index && @touch($dir.'/index.html'); //表示在目录下生成index.html文件，防止别人目录形式访问
        }
        return $res;
    }
/*

 */
function js_alert($content,$url=''){
if($url)
  $gourl='location.href="'.$url.'"';
else
  $gourl='history.go(-1);';
$html=<<<EOT
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<script src="http://libs.baidu.com/jquery/1.9.1/jquery.min.js"></script>
<style type="text/css">
    #smark{
        width:100%;
        height: 100%;
        background: #000;
        opacity: 0.3;
        position: fixed;
        top:0px;
        left: 0px;
        z-index: 100;
        display: none;
    }
    .dig{
        width:80%;
        text-align: center;
        height:80px;
        line-height: 80px;
        margin:  200px 10%;
        background: rgba(255, 255, 255, .5);
        font-size:18px;
        color:#fff;
    }
</style>
<div id="smark"><div class="dig"></div></div>
<script>
    ltAlter("$content",goback);
function ltAlter(msg,fun){
    $(".dig").html(msg);
    $("#smark").fadeIn(400);
    setTimeout(function(){
        $("#smark").fadeOut(400);
        if(fun)
            fun();
    }, 2000);

}
function goback(){
  $gourl;
}
</script>
EOT;
echo $html;
exit;
}

function filter_emoji($nickname){
  $nickname = preg_replace('~\xEE[\x80-\xBF][\x80-\xBF]|\xEF[\x81-\x83][\x80-\xBF]~', '', $nickname);
  return $nickname;
}
function filter_nickname($nickname){
$tmpStr = json_encode($nickname); //暴露出unicode
$tmpStr = preg_replace("#(\\\ue[0-9a-f]{3})#ie","addslashes('\\1')",$tmpStr); //将emoji的unicode留下，其他不动
$nickname = json_decode($tmpStr);
return $nickname;
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
