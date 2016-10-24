<?php
namespace app\wap\controller;
use app\wap\controller\Wechat;
use think\Request;
class Money extends Wechat
{
    public $Model;
   
    public function _initialize(){
        parent::_initialize();
        $this->Model=new \app\wap\model\Money();
      
    }
    //首页
    public function index()
    {
      $user_id=$this->user_id;
      //s:用来验证是否是通过自己的表单提交的
            $postcode=mt_rand(1000,9999);
            cookie("postcode_sendmoney",$postcode);
            $this->assign("postcode",$postcode);
            //end
       //得到提现额
       $sure_fencheng=$this->Model->get_sure_fencheng($user_id);
       $this->assign("sure_fencheng",$sure_fencheng);
        $this->assign("shop_config",$this->shop_config);
        return $this->fetch();
    }
    //粉丝界面
  public function add_sendmoney()
    {
      $user_id=$this->user_id;

      $postcode=Request::instance()->post("postcode",'',"addslashes");
        if(cookie("postcode_sendmoney")!=$postcode){
            //cookie验证提交是否为正常提交，防止恶意提交
            $this->error('提交失败');
        }
      $sure_fencheng=$this->Model->get_sure_fencheng($user_id);
      $data['money']=Request::instance()->post("pay",'',"float");
       if($data['money']>$sure_fencheng){
          js_alert('提现金额大于可提现金额');
       
         }
         
         
         if($data['money']<$this->shop_config['min_money']){
          js_alert('提现金额最小为'.$this->shop_config['min_money']);
         
         }
         $data['pay_type']=Request::instance()->post("pay_type",'',"intval");
    if($data['money']>200 && $data['pay_type']==0){
      //如果是微信提现，则不能大于200
      js_alert('微信提现不能超过200');
    }
         
         if($data['pay_type']==1){
            $tx_info['tel']=Request::instance()->post("tel",'',"addslashes");
                $tx_info['bank_card']=Request::instance()->post("bank_card",'',"addslashes");
                $tx_info['bank_user']=Request::instance()->post("bank_user",'',"addslashes");
                $tx_info['bank_name']=Request::instance()->post("bank_name",'',"addslashes");
                if(empty($tx_info['bank_card']) || empty($tx_info['bank_user']) || empty($tx_info['bank_name'])){
                  js_alert('保存提现信息失败');
                }else{
                  $data['tx_info_id']=$this->Model->add_tx_info($tx_info);
                }
                

         }
         $data['user_id']=$user_id;
         $data['create_time']=time();
         $money_send_id=$this->Model->add_money_send($data);     
         if($money_send_id){
            if($data['pay_type']==0){
              js_alert('申请提现成功！','/wap/money/sendmoney_list');
            }else{
              js_alert('申请提现成功！');
            }
       
         }  else{
          js_alert('申请提现失败');
         }
      
    }

    public function sendmoney_list(){
      $user_id=$this->user_id;
      $sendmoney_status=['待审核','成功','失败'];
      $sendmoney_type=['微信','银行卡','支付宝'];
      $sendmoney_list=$this->Model->get_sendmoney_list($user_id);
      $this->assign("sendmoney_list",$sendmoney_list);
      $this->assign("sendmoney_status",$sendmoney_status);
      $this->assign("sendmoney_type",$sendmoney_type);
       return $this->fetch();
    }
   
}
