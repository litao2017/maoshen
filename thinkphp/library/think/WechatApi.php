<?php
/**
 * @Author: litao
 * @Date:   2016-06-03 18:35:40
 * @Last Modified by:   anchen
 * @Last Modified time: 2016-06-22 17:39:54
 */
namespace think;
class WechatApi{
    private $token;
    private $encodingAesKey;
    private $encrypt_type;
    private $appid;
    private $appsecret;
    public $access_token;
    private $jsapi_ticket;
    private $api_ticket;
    private $user_token;
    private $partnerid;
    private $partnerkey;
    private $paysignkey;
    private $postxml;
    public $debug =  false;
    public $errCode = 40001;
    public $errMsg = "no access";
    public function __construct($options){
        $this->token = isset($options['token'])?$options['token']:'';
        $this->encodingAesKey = isset($options['encodingaeskey'])?$options['encodingaeskey']:'';
        $this->appid = isset($options['appid'])?$options['appid']:'';
        $this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
        $this->access_token = isset($options['access_token'])?$options['access_token']:'';
        $this->debug = isset($options['debug'])?$options['debug']:false;

    }
    /**
     * oauth 授权跳转接口
     * @param string $callback 回调URI
     * @return string
     */
    public function getOauthRedirect($callback,$state=1,$scope='snsapi_userinfo'){
        return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
    }

    /**
     * 通过code获取Access Token
     * @param string code 微信返回的凭证
     * @return array {access_token,expires_in,refresh_token,openid,scope}
     */
    public function getOauthAccessToken($code){
        
     
        $result = $this->httpsRequest('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code');
        if ($result)
        {
            $json = json_decode($result,true);
            if (!empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->access_token = $json['access_token'];
            return $json;
        }
        return false;
    }
   /**
     * 获取关注者详细信息
     * @param string $access_token
     * @param string $openid
     * @return array {subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
public function getUserInfo($access_token,$openid){
    $result = $this->httpsRequest('https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN');
    if ($result)
        {
            $json = json_decode($result,true);
            if (!empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
      return false;
}
/*
只能获取关注过的信息
 */
  public function getWechatUserInfo($access_token,$openid){
    $result = $this->httpsRequest('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN');
    if ($result)
        {
            $json = json_decode($result,true);
            if (!empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
      return false;
    
  }

   /**
     * 发送模板消息
     * @param array $data 消息结构
     * ｛
            "touser":"OPENID",
            "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
            "url":"http://weixin.qq.com/download",
            "topcolor":"#FF0000",
            "data":{
                "参数名1": {
                    "value":"参数",
                    "color":"#173177"    //参数颜色
                    },
                "Date":{
                    "value":"06月07日 19时24分",
                    "color":"#173177"
                    },
                "CardNumber":{
                    "value":"0426",
                    "color":"#173177"
                    },
                "Type":{
                    "value":"消费",
                    "color":"#173177"
                    }
            }
        }
     * @return boolean|array
     */
    public function sendTemplateMessage($data){
        if (!$this->access_token) return false;
        $result = $this->httpsRequest('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->access_token,self::json_encode($data));
        if($result){
            $json = json_decode($result,true);
            if (!empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    } 

    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     */
    static function json_encode($arr) {
        if (count($arr) == 0) return "[]";
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                $str .= 'false'; //The booleans
                elseif ($value === true)
                $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }
/**
   * 创建二维码ticket
   * @param int|string $scene_id 自定义追踪id,临时二维码只能用数值型
   * @param int $type 0:临时二维码；1:数值型永久二维码(此时expire参数无效)；2:字符串型永久二维码(此时expire参数无效)
   * @param int $expire 临时二维码有效期，最大为604800秒
   * @return array('ticket'=>'qrcode字串','expire_seconds'=>604800,'url'=>'二维码图片解析后的地址')
   */
  public function getQRCode($scene_id,$type=0,$expire=604800){
     file_put_contents('ll.log',$this->access_token);
    if (!$this->access_token || !isset($scene_id)) return false;
    switch ($type) {
      case '0':
        if (!is_numeric($scene_id))
          return false;
        $action_name = 'QR_SCENE';
        $action_info = array('scene'=>(array('scene_id'=>$scene_id)));
        break;

      case '1':
        if (!is_numeric($scene_id))
          return false;
        $action_name = 'QR_LIMIT_SCENE';
        $action_info = array('scene'=>(array('scene_id'=>$scene_id)));
        break;

      case '2':
        if (!is_string($scene_id))
          return false;
        $action_name = 'QR_LIMIT_STR_SCENE';
        $action_info = array('scene'=>(array('scene_str'=>$scene_id)));
        break;

      default:
        return false;
    }

    $data = array(
      'action_name'    => $action_name,
      'expire_seconds' => $expire,
      'action_info'    => $action_info
    );
    if ($type) {
      unset($data['expire_seconds']);
    }

    $result = $this->httpsRequest('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->access_token,self::json_encode($data));
    file_put_contents('ll.log',$result);
    if ($result) {
      $json = json_decode($result,true);
      if (!empty($json['errcode'])) {
        $this->errCode = $json['errcode'];
        $this->errMsg = $json['errmsg'];
        return false;
      }
      return $json;
    }
    return false;
  }
/**
   * 获取二维码图片
   * @param string $ticket 传入由getQRCode方法生成的ticket参数
   * @return string url 返回http地址
   */
  public function getQRUrl($ticket) {
    return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
  }

  /**
   * 长链接转短链接接口
   * @param string $long_url 传入要转换的长url
   * @return boolean|string url 成功则返回转换后的短url
   */
  public function getShortUrl($long_url){
      if (!$this->access_token && !$this->checkAuth()) return false;
      $data = array(
            'action'=>'long2short',
            'long_url'=>$long_url
      );
      $result = $this->httpsRequest(self::API_URL_PREFIX.self::SHORT_URL.'access_token='.$this->access_token,self::json_encode($data));
      if ($result)
      {
          $json = json_decode($result,true);
          if (!empty($json['errcode'])) {
              $this->errCode = $json['errcode'];
              $this->errMsg = $json['errmsg'];
              return false;
          }
          return $json['short_url'];
      }
      return false;
  }
/*
获得模板
type 模板类型 text、news
 */
    public function getTpl($type,$data=''){
    switch ($type) {
      case 'text':
        $tpl="<xml>
              <ToUserName><![CDATA[%s]]></ToUserName>
              <FromUserName><![CDATA[%s]]></FromUserName>
              <CreateTime>%s</CreateTime>
              <MsgType><![CDATA[%s]]></MsgType>
              <Content><![CDATA[%s]]></Content>
              </xml>";
        break;
      case 'news':
        $tpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <ArticleCount>".count($data)."</ArticleCount>
            <Articles>";
      foreach($data as $k=>$v){
        $tpl .="<item>
              <Title><![CDATA[".$v['title']."]]></Title> 
              <Description><![CDATA[".$v['description']."]]></Description>
              <PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>
              <Url><![CDATA[".$v['url']."]]></Url>
              </item>";
      }
      
      $tpl .="</Articles>
            </xml> ";;
        break;
      default:
         $tpl="<xml>
              <ToUserName><![CDATA[%s]]></ToUserName>
              <FromUserName><![CDATA[%s]]></FromUserName>
              <CreateTime>%s</CreateTime>
              <MsgType><![CDATA[%s]]></MsgType>
              <Content><![CDATA[%s]]></Content>
              </xml>";
        break;
    }
   
        return $tpl;
  }
 /**
     * 获取url返回信息
     * @param string url api地址
     * @param string data 传送的数据
     * @return string
     */
    public function httpsRequest($url, $data = null)
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

}