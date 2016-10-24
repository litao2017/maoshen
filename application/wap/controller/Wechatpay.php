<?php
namespace app\wap\controller;
use think\Request;
use app\wap\controller\Wap;
use wxpay\JsApi_pub;
use wxpay\WxPayConf_pub;
use wxpay\UnifiedOrder_pub;
use think\Db;
class Wechatpay extends Wap
{
    public function index()
    {
            
    $url_parameter="";
    $order_sn=input("order_sn");
    $order_name=input("order_name");
    if($order_name && $order_sn){
        $url_parameter="?order_sn=".$order_sn."&order_name=".$order_name;
  
        $out_trade_no=$order_sn;
       $order_name=$order_name;
    }

    if( empty($out_trade_no) || empty($order_name)){
      
      
        $this->error('参数错误');
            exit;
    }
        include_once("vendor/WxPayPubHelper/WxPayPubHelper.php");
        
        //使用jsapi接口
    $jsApi = new JsApi_pub();

        //=========步骤1：网页授权获取用户openid============
        //通过code获得openid
        $code=input('code');
        if (empty($code))
        {   $call_url=WxPayConf_pub::JS_API_CALL_URL;
            $call_url.=$url_parameter;
            $call_url=urlencode($call_url);
            //echo $call_url;exit;
            //触发微信返回code码
            $url = $jsApi->createOauthUrlForCode($call_url);
            //$new_url=str_replace('#wechat_redirect', $url_parameter, $url);
            //echo $url.'   '.$new_url;exit;
            Header("Location: $url"); 
        }else
        {
            //获取code码，以获取openid
            $jsApi->setCode($code);
            $openid = $jsApi->getOpenId();
        }
        //echo $openid;exit;
        //=========步骤2：使用统一支付接口，获取prepay_id============
        //使用统一支付接口
        $unifiedOrder = new UnifiedOrder_pub();
         if(strstr($out_trade_no,'test')){
          $order=db("order_test")->where("order_sn='".$out_trade_no."' and status=0")->find();
         }else{
          $order=db("order")->where("order_sn='".$out_trade_no."' and status=0")->find();
         }
        
        $address=db("address")->find($order['address_id']);
        if(!$order){
              $this->error('没有此订单');
            exit;
        }
        $unifiedOrder->setParameter("openid","$openid");//商品描述
        $unifiedOrder->setParameter("body",trim($order_name));//商品描述
        //自定义订单号，此处仅作举例
        //$timeStamp = time();
        //$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
        $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号 
        $unifiedOrder->setParameter("total_fee",floatval($order['amount'])*100);//以分为单位，1就是1分钱，总金额
        $unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址 
        $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型

        $prepay_id = $unifiedOrder->getPrepayId();

        //=========步骤3：使用jsapi调起支付============
        $jsApi->setPrepayId($prepay_id);

        $jsApiParameters = $jsApi->getParameters();

        $this->assign("jsApiParameters",$jsApiParameters);
        $order['order_name']=$order_name;
        $this->assign("order",$order);
        $this->assign("address",$address);
        return $this->fetch();
        }
public function notify(){

    
    include_once("vendor/WxPayPubHelper/WxPayPubHelper.php");

    //使用通用通知接口
    $notify = new \wxpay\Notify_pub();

    //存储微信的回调
    $xml = $GLOBALS['HTTP_RAW_POST_DATA'];  
    $notify->saveData($xml);
    
    //验证签名，并回应微信。
    //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
    //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
    //尽可能提高通知的成功率，但微信不保证通知最终能成功。
    if($notify->checkSign() == FALSE){
        $notify->setReturnParameter("return_code","FAIL");//返回状态码
        $notify->setReturnParameter("return_msg","签名失败");//返回信息
    }else{
        $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
    }
    $returnXml = $notify->returnXml();
    //echo $returnXml;
    
    //==商户根据实际情况设置相应的处理流程，此处仅作举例=======
    
    //以log文件形式记录回调信息

    $log_name="./vendor/WxPayPubHelper/log/notify_url.log";//log文件路径
    //$this->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");

    if($notify->checkSign() == TRUE)
    {
        if ($notify->data["return_code"] == "FAIL") {
            //此处应该更新一下订单状态，商户自行增删操作
            $this->log_result($log_name,"【通信出错】:\n".$xml."\n");
        }
        elseif($notify->data["result_code"] == "FAIL"){
            //此处应该更新一下订单状态，商户自行增删操作
            $this->log_result($log_name,"【业务出错】:\n".$xml."\n");
        }
        else{
            //此处应该更新一下订单状态，商户自行增删操作
          $this->log_result($log_name,"【支付成功】:\n".$xml."\n");
            //更新订单状态
            $out_trade_no=$notify->data['out_trade_no'];
            if(strstr($out_trade_no,'test')){
              $data['status']=1;
              $data['pay_time']=time();
              db("order_test")->where("order_sn='".$out_trade_no."'")->update($data);
              exit;
            }
            $orderWapModel=new \app\wap\model\Order();
            $order=db("order")->where("order_sn='".$out_trade_no."'")->find();
            //lt:获得商品信息
            $goods_info=db("order_goods")->where("order_id=".$order['order_id'])->find();//只获得一款产品
            $goods_name=$goods_info['goods_name'];
            $goods_id=$goods_info['goods_id'];
            $buy_number=$goods_info['buy_number'];
           /* foreach ($goods_list as $goods) {
              if($goods_str){
                
              }else{
                $goods_str.=$goods['goods_name'].' × '.$goods['buy_number'];
              }
              
            }*/
            //end
            if($order && $order['status']<1){
                  $orderModel=new \app\admin\model\Order();
                  //如果用户等级小于1，为初始用户等级，则升级
                  $user_level=$orderModel->get_user_level($order['user_id']);
                   if($user_level<1){
                      $orderModel->level_up($order['user_id'],1);
                    }
                  $orderModel->give_parents_nosure_fencheng($order);
                    $data['status']=1;
                    $data['pay_time']=time();
                    db("order")->where("order_sn='".$out_trade_no."'")->update($data);//更新订单
                    //更新库存和销量
                    $prefix=config("database.prefix");
                     Db::execute("update {$prefix}goods set goods_number=goods_number-{$buy_number} , sales=sales+{$buy_number}  where goods_id=".$goods_id);
                     //end
            //lt:发送模版消息
                 $WechatApi=new \think\WechatApi(["access_token"=>$this->access_token]);
                 
                
                 $tpl_orderpay=$this->shop_config['tpl_orderpay'];
                 $amount=round($order['amount'],2).'元';
                //客户自己
                $user_info=$orderWapModel->get_user_info($order['user_id']);
                 $data=array(
                      "touser"=>$user_info['openid'],
                  "template_id"=>$tpl_orderpay,

                  "data"=>array(
                    "first"=>array("value"=>"订单支付成功！感谢您的支持！","color"=>"#173177"),
                   // "first"=>array("value"=>"恭喜注册成功","color"=>"#173177"),
                    "orderMoneySum"=>array("value"=>$amount,"color"=>"#173177"),
                    "orderProductName"=>array("value"=>$goods_name,"color"=>"#173177"),
                        )
                      );
                  $WechatApi->sendTemplateMessage($data);
                 //上级
                 if($order['parent_id']){
                     $openid=$orderWapModel->get_openid_by_userid($order['parent_id']);
                     $data=array(
                          "touser"=>$openid,
                      "template_id"=>$tpl_orderpay,

                      "data"=>array(
                        "first"=>array("value"=>"您的一级[".$user_info['nickname']."]订单支付成功！","color"=>"#173177"),
                       // "first"=>array("value"=>"恭喜注册成功","color"=>"#173177"),
                        "orderMoneySum"=>array("value"=>$amount,"color"=>"#173177"),
                        "orderProductName"=>array("value"=>$goods_name,"color"=>"#173177"),
                            )
                          );
                      $WechatApi->sendTemplateMessage($data);
                 }
                 //上上级
                 if($order['parent2_id']){
                     $openid=$orderWapModel->get_openid_by_userid($order['parent2_id']);
                        $data=array(
                          "touser"=>$openid,
                      "template_id"=>$tpl_orderpay,

                      "data"=>array(
                        "first"=>array("value"=>"您的二级[".$user_info['nickname']."]订单支付成功！","color"=>"#173177"),
                       // "first"=>array("value"=>"恭喜注册成功","color"=>"#173177"),
                        "orderMoneySum"=>array("value"=>$amount,"color"=>"#173177"),
                        "orderProductName"=>array("value"=>$goods_name,"color"=>"#173177"),
                            )
                          );
                      $WechatApi->sendTemplateMessage($data);
                 }
                 //上上上级
                 if($order['parent3_id']){
                     $openid=$orderWapModel->get_openid_by_userid($order['parent3_id']);
                        $data=array(
                          "touser"=>$openid,
                      "template_id"=>$tpl_orderpay,

                      "data"=>array(
                        "first"=>array("value"=>"您的三级[".$user_info['nickname']."]订单支付成功！","color"=>"#173177"),
                       // "first"=>array("value"=>"恭喜注册成功","color"=>"#173177"),
                        "orderMoneySum"=>array("value"=>$amount,"color"=>"#173177"),
                        "orderProductName"=>array("value"=>$goods_name,"color"=>"#173177"),
                            )
                          );
                      $WechatApi->sendTemplateMessage($data);
                 }
                 //end
            }
        
       
            
         }
    }
}

        // 打印log
    function  log_result($file,$word) 
    {
        $fp = fopen($file,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y-%m-%d-%H：%M：%S",time())."\n".$word."\n\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    function hongbao_pay(){
    //lt:如果不是管理员没有权限发送红包
         $admin_id=cookie('admin_id');

         if(empty($admin_id)){
            echo '{"error":1,"msg":"您没有权限"}';
            //$this->error('没有访问权限');
            exit;
        }
        //end
        //$price=Request::instance()->post("price",0,"floatval");
         $money_send_id=Request::instance()->post("money_send_id",0,"intval");
         
         $money_send=db("money_send")->alias('m')->join('user u','m.user_id = u.user_id')->join('wechat_user w','u.wechat_user_id = w.wechat_user_id')->where("m.status=0 and m.money_send_id=".$money_send_id)->find();
        //  file_put_contents('ttt1.log',json_encode($money_send));
         if(!$money_send){
             //$this->error('没有要发送的红包');
              echo '{"error":1,"msg":"没有要发送的红包"}';
              exit;
         }

        $price=$money_send['money'];
        $total_amount=$price*100;
        $nick_name=Request::instance()->post("nick_name",'系统发送',"addslashes");
        $send_name=Request::instance()->post("send_name",'系统发送',"addslashes");
        $openid=$money_send['openid'];
        $act_name=Request::instance()->post("act_name",'',"addslashes");
        $wishing=Request::instance()->post("wishing",'恭喜发财',"addslashes");
        $remark=Request::instance()->post("remark",'',"addslashes");
        $mch_billno=date('YmdHis').rand(1000, 9999);
        include_once("vendor/WxPayPubHelper/WxHongBaoHelper.php");
        $wxHongBaoHelper = new \wxpay\WxHongBaoHelper();

        $wxHongBaoHelper->setParameter("nonce_str", create_rond_str(30));//随机字符串，不长于 32 位
        $wxHongBaoHelper->setParameter("mch_billno",$mch_billno );//订单号
        $wxHongBaoHelper->setParameter("mch_id", WxPayConf_pub::MCHID);//商户号
        $wxHongBaoHelper->setParameter("wxappid", WxPayConf_pub::APPID);
        $wxHongBaoHelper->setParameter("nick_name", $nick_name);//提供方名称
        $wxHongBaoHelper->setParameter("send_name", $send_name);//红包发送者名称
        $wxHongBaoHelper->setParameter("re_openid", $openid);//相对于医脉互通的openid
        $wxHongBaoHelper->setParameter("total_amount", $total_amount);//付款金额，单位分
        $wxHongBaoHelper->setParameter("min_value", 100);//最小红包金额，单位分
        $wxHongBaoHelper->setParameter("max_value", 20000);//最大红包金额，单位分
        $wxHongBaoHelper->setParameter("total_num", 1);//红包収放总人数
        $wxHongBaoHelper->setParameter("wishing", $wishing);//红包祝福诧
        $wxHongBaoHelper->setParameter("client_ip", '127.0.0.1');//调用接口的机器 Ip 地址
        $wxHongBaoHelper->setParameter("act_name", $act_name);//活劢名称
        $wxHongBaoHelper->setParameter("remark", $remark);//备注信息
       
        $postXml = $wxHongBaoHelper->create_hongbao_xml();
        //var_dump($postXml);exit;
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $responseXml = $wxHongBaoHelper->curl_post_ssl($url, $postXml);
       
       
        //var_dump($responseXml);
        $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $result_code=$responseObj->result_code;
        if($responseObj->result_code=='SUCCESS'){
            //发送成功更新状态
            $data['money_send_id']=$money_send_id;
            $data['status']=1;
            $data['pay_time']=time();
            $data['mch_billno']=$mch_billno; //商户订单号,可用于查询红包状态
            $data['note']=$remark;
            db("money_send")->update($data);
        }
       // pp($responseObj);
        $return_arr=['error'=>0,'result_code'=>$result_code,"total_amount"=>$responseObj->total_amount,"mch_billno"=>$responseObj->mch_billno];
        echo json_encode($return_arr);
        exit;
    }


    function hongbao_view(){
          //lt:如果不是管理员没有权限发送红包
         $admin_id=cookie('admin_id');
         if(empty($admin_id)){
            //echo '{"error":1,"msg":"您没有权限"}';
            $this->error('没有访问权限');
            exit;
        }
        //end
        $mch_billno=input('mch_billno');
         if(empty($mch_billno)){
            //echo '{"error":1,"msg":"您没有权限"}';
            $this->error('没有此订单');
            exit;
        }
        include_once("vendor/WxPayPubHelper/WxHongBaoHelper.php");
        $wxHongBaoHelper = new \wxpay\WxHongBaoHelper();
        $wxHongBaoHelper->setParameter("nonce_str", create_rond_str(30));//随机字符串，不长于 32 位
        $wxHongBaoHelper->setParameter("mch_billno",$mch_billno );//订单号
        $wxHongBaoHelper->setParameter("mch_id", WxPayConf_pub::MCHID);//商户号
        $wxHongBaoHelper->setParameter("appid", WxPayConf_pub::APPID);
        $wxHongBaoHelper->setParameter("bill_type", 'MCHT');//MCHT:通过商户订单号获取红包信息。 
      
         $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo';
         $postXml = $wxHongBaoHelper->create_hongbao_xml(2);
         $responseXml = $wxHongBaoHelper->curl_post_ssl($url, $postXml);
          $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
          $status_arr=["SENDING"=>"发放中","SENT"=>"已发放待领取","FAILED"=>"发放失败","RECEIVED"=>"已领取","REFUND"=>"已退款"];
          $status=trim($responseObj->status);
          echo '<br/>';
          echo "订单号：$mch_billno <br/>";
          echo "金额：".(($responseObj->total_amount)/100)." <br/>";
          echo "状态：".$status_arr[$status]." <br/>";
          
       //echo $responseXml;
    }
}
