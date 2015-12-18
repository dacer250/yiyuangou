<?php
/**
 * 一元购系统---用户类
 * @authors 凌翔 (553299576@qq.com)
 * @date    2015-11-24 14:58:35
 * @version $Id$
 */

namespace AppMain\controller\Api;
use \System\BaseClass;

class UserController extends Baseclass {
    /**
     * checklogin
     */
    public function checklogin(){
            if (empty($_SESSION['userInfo']['openid'])) {
                $this->R('','90005');//跳//getOpenID
            }else{
                $this->R(['user_id'=>$_SESSION['userInfo']['userid']]);//进网站,返回user_id
            }
    }
    /**
     * 授权
     */
    public function getOpenID(){
        $weObj = new \System\lib\Wechat\Wechat($this->config("WEIXIN_CONFIG"));
        $this->weObj = $weObj;
        if (empty($_GET['code']) && empty($_GET['state'])) {
            $callback = getHostUrl();
            $reurl = $weObj->getOauthRedirect($callback, "1");
            redirect($reurl, 0, '正在发送验证中...');
            exit(); 
        } elseif (intval($_GET['state']) == 1) {
                $accessToken = $weObj->getOauthAccessToken();
                 
                // 是否有用户记录
                $isUser = $this->table('user')->where(["openid" => $accessToken['openid'],'is_on'=>1])->get(null, true);
                /*var_dump($isUser);exit();*/
                
                if ($isUser==null) {
                    //没有此用户跳转至输入注册的页面
                    header("LOCATION:".getHost()."/register.html");
                }else{
                $userID=$isUser['id'];
                
                $updateUser = $this->table('user')->where(['id'=>$userID])->update(['last_login'=>time(),'last_ip'=>ip2long(getClientIp())]);
                $_SESSION['userInfo']=[
                    'openid'=>$isUser['openid'],
                    'userid'=>$isUser['id'],
                    'nickname'=>$isUser['nickname'],
                    'user_img'=>$isUser['user_img'],
                ];

                //var_dump($_SESSION['userInfo']['openid']);exit();
                header("LOCATION:http://onebuy.ping-qu.com");//进入网站成功
            //用户取消授权
            //
            //$this->R('','90006');
        }
       
    }
}
    /**
     * 新用户从微信注册
     */
    public function getNewOpenID(){
        $weObj = new \System\lib\Wechat\Wechat($this->config("WEIXIN_CONFIG"));
        $this->weObj = $weObj;
        if (empty($_GET['code']) && empty($_GET['state'])) {
            $callback = getHostUrl();
            $reurl = $weObj->getOauthRedirect($callback, "1");
            redirect($reurl, 0, '正在发送验证中...');
            exit(); 
        } elseif (intval($_GET['state']) == 1) {
                $accessToken = $weObj->getOauthAccessToken();
                
                $mobile = $_GET['phone'];
                
                    //用户信息
                    $userInfo=$this->getUserInfo($accessToken);
                    $saveUser=$this->saveUser($userInfo,$mobile);//插入新会员数据
                    if (!$saveUser) {
                            $this->R('','40001');
                    }
                
                header("LOCATION:http://onebuy.ping-qu.com");
        } else {
            //用户取消授权
            $this->R('','90006');
        }
    }
    
    /**
     * 获取用户信息
     */
    private function getUserInfo($user){

        $user_info = $this->weObj->getOauthUserinfo($user['access_token'], $user['openid']);

        if (!$user_info){
            die("系统错误，请稍后再试！");
        }

        //是否关注
        $isFollow=$this->weObj->getUserInfo($user['openid']);
        if ($isFollow['subscribe']==1){
        	$user_info['is_follow']=1;
        }
        else{
        	$user_info['is_follow']=0;
        }

        return $user_info;
    }
    /**
     * 保存用户
     */
    private function saveUser($user_info,$mobile){

        $data = array(
            'openid' => $user_info['openid'],
            'phone' =>$mobile,
            'user_img' => $user_info['headimgurl'],
            'nickname' => $user_info['nickname'],
            'is_follow'=>$user_info['is_follow'],
            'add_time' => time()
        );
        $result=$this->table('user')->save($data);
        if (!$result){
            die("系统错误，请稍后再试！");
        }

        return $data;
    }
    
    /**
    *查看个人信息__一条数据
     */
    public function userOneAllDetail(){
    
        $this->V(['user_id'=>['egNum',null,true]]);

        $id = intval($_POST['user_id']);

            $user = $this->table('user')->where(['is_on'=>1,'id'=>$id])->get(['id','user_img','phone'],true);
            if(!$user){
                $this->R('',70009);
            }

            $this->R(['user'=>$user]);
    }
    /**
     * 修改个人信息
     */
    public function userOneEdit(){

       $rule = [
            'id'          =>['egNum'],
            'phone'       =>['mobile'],
        ];
        $this->V($rule);
        $id = intval($rule['id']);

        $user = $this->table('user')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
        if(!$user){
            $this->R('',70009);
        }

        unset($rule['id']);
        foreach ($rule as $k=>$v){
            if(isset($_POST[$k])){
                $data[$k] = $_POST[$k];
            }
        }

        $user['update_time'] = time();

        $user = $this->table('user')->where(['id'=>$id])->update($data);
        if(!$user){
            $this->R('',40001);
        }
        $this->R();
    }
    
    /**
     * 用户的购买记录(分页)     
     * user_id
     * 缩略图，商品标题，价格(总须人次)，已购买人次
     */
    public function purchaseList(){

        $this->V(['user_id'=>['egNum',null,true]]);
        $id = intval($_POST['user_id']);
        $pageInfo = $this->P();
        $file = ['id','goods_id','num'];

        $class = $this->table('record')->where(['is_on'=>1,'user_id'=>$id])->order('add_time desc');

        //查询并分页
        $detailpage = $this->getOnePageData($pageInfo,$class,'get','getListLength',[$file],false);
        if($detailpage ){
            foreach ($detailpage  as $k=>$v){
                $status = $this->table('goods')->where(['is_on'=>1,'id'=>$v['goods_id']])->get(['goods_title','price','goods_thumb'],true);
                $detailpage [$k]['goods_title'] = $status['goods_title'];
                $detailpage [$k]['total_num'] = $status['price'];
                $detailpage [$k]['goods_thumb'] = $status['goods_thumb'];
                $status = $this->table('purchase')->where(['is_on'=>1,'goods_id'=>$v['goods_id']])->get(['id'],false);
                $count = count($status);
                $detailpage [$k]['purchase_num'] = $count;
                $detailpage [$k]['last_num'] =$detailpage [$k]['total_num']-$count;
                if ($detailpage [$k]['last_num']<1) {
                     $status = $this->table('bill')->where(['is_on'=>1,'goods_id'=>$v['goods_id']])->get(['user_id','code','add_time'],true);
                     $detailpage[$k]['lucky_time'] = $status['add_time'];
                     $detailpage[$k]['code'] = $status['code'];
                     $status = $this->table('user')->where(['is_on'=>1,'id'=>$status['user_id']])->get(['nickname','user_img'],true);
                     $detailpage[$k]['nickname'] = $status['nickname'];
                     $detailpage[$k]['user_img'] = $status['user_img'];
                 } 
            }
        }else{
            $detailpage  = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['detailpage'=>$detailpage,'pageInfo'=>$pageInfo]);
    }
    /**
     * 购买记录中的商品详情
     * goods_id
     */
    public function purchaseOneDetail(){

        $rule = [
                    'goods_id'    =>['egNum'],
                    'user_id'     =>['egNum'],
                ];
                $this->V($rule); 

                foreach ($rule as $k => $v) {
                        $data[$k] = $_POST[$k];
                }

        $pageInfo = $this->P();
        $file = ['id','num','add_time'];

        $class = $this->table('record')->where(['is_on'=>1,'goods_id'=>$data['goods_id'],'user_id'=>
            $data['user_id']])->order('add_time desc');

        //查询并分页
        $detailpage = $this->getOnePageData($pageInfo,$class,'get','getListLength',[$file],false);
        if($detailpage ){
            foreach ($detailpage  as $k=>$v){
                $detailpage [$k]['add_time'] = $v['add_time'];
                $status = $this->table('purchase')->where(['is_on'=>1,'goods_id'=>$data['goods_id'],'user_id'=>
                    $data['user_id']])->get(['code'],false);
                $count = count($status);
                 for ($i=0; $i < $count; $i++) { 
                $v = implode(",",$status[$i]); //可以用implode将一维数组转换为用逗号连接的字符串
                $temp[] = $v;
            }
                $detailpage[$k]['code']=$temp;
                $status = $this->table('goods')->where(['is_on'=>1,'id'=>$data['goods_id']])->get(['goods_title','goods_thumb'],true);
                $detailpage [$k]['goods_title'] = $status['goods_title'];
                $detailpage [$k]['goods_thumb'] = $status['goods_thumb'];
                $status = $this->table('bill')->where(['is_on'=>1,'goods_id'=>$data['goods_id']])->get(['code','add_time'],true);
                $detailpage [$k]['lucky_code'] = $status['code'];
                $detailpage [$k]['lucky_time'] = $status['add_time'];
                $status = $this->table('user')->where(['is_on'=>1,'id'=>$data['user_id']])->get(['nickname'],true);
                $detailpage [$k]['nickname'] = $status['nickname'];
            }
        }else{
            $detailpage  = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['detailpage'=>$detailpage,'pageInfo'=>$pageInfo]);
    }
    /**
     * 获得商品记录列表(分页)
     */
    public function luckyList(){

        $this->V(['user_id'=>['egNum',null,true]]);
        $id = intval($_POST['user_id']);
        $pageInfo = $this->P();
        $file = ['id','goods_id','code','add_time'];

        $class = $this->table('bill')->where(['is_on'=>1,'user_id'=>$id,'is_confirm'=>1])->order('add_time desc');

        //查询并分页
        $luckypage = $this->getOnePageData($pageInfo,$class,'get','getListLength',[$file],false);
        if($luckypage ){
            foreach ($luckypage  as $k=>$v){
                $luckypage [$k]['lucky_time'] = $v['add_time'];
                $status = $this->table('goods')->where(['is_on'=>1,'id'=>$v['goods_id']])->get(['goods_title','price','goods_thumb'],true);
                $luckypage [$k]['goods_title'] = $status['goods_title'];
                $luckypage [$k]['total_num'] = $status['price'];
                $luckypage [$k]['goods_thumb'] = $status['goods_thumb'];
                $status = $this->table('record')->where(['is_on'=>1,'goods_id'=>$v['goods_id'],'user_id'=>$id])->get(['num'],true);
                $luckypage [$k]['num'] = $status['num'];
                $status = $this->table('logistics')->where(['is_on'=>1,'bill_id'=>$v['id']])->get(['logistics_number'],true);
                $luckypage [$k]['logistics_number'] = $status['logistics_number'];
                unset($luckypage[$k]['add_time']);

            }
        }else{
            $luckypage  = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['obtained_goods'=>$luckypage,'pageInfo'=>$pageInfo]);
    }

    public function getExpress(){
        $this->V(['logistics_number'=>['egNum',null,true]]);
        $id = $_POST['logistics_number'];
        $express = new \System\lib\Express\Express();
        $expressdetail = $express->getorder($id);
        $this->R(['expressdetail'=>$expressdetail]);
    }
    
}