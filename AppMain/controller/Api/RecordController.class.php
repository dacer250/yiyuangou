<?php
/**
 * 一元购系统---记录类
 * @authors 凌翔 (553299576@qq.com)
 * @date    2015-11-29 16:08:22
 * @version $Id$
 */

namespace AppMain\controller\Api;
use \System\BaseClass;

class RecordController extends BaseClass {
	/**
	 * 商品参与记录
	 * goods_id
	 * */
    public function buyGoodsRecordList(){

    $this->V(['goods_id'=>['egNum',null,true]]);
    $id = intval($_POST['goods_id']);
    $pageInfo = $this->P();
    $file = ['id','user_id','num','add_time'];

    $class = $this->table('record')->where(['is_on'=>1,'goods_id'=>$id])->order('add_time desc');

    //查询并分页
    $recordpage = $this->getOnePageData($pageInfo,$class,'get','getListLength',[$file],false);
    if($recordpage ){
        foreach ($recordpage  as $k=>$v){
            $recordpage [$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $status = $this->table('user')->where(['is_on'=>1,'id'=>$v['user_id']])->get(['nickname','user_img'],true);
            $recordpage [$k]['nickname'] = $status['nickname'];
            $recordpage [$k]['user_img'] = $status['user_img'];
            $status = $this->table('purchase')->where(['is_on'=>1,'user_id'=>$v['user_id'],'goods_id'=>$id])->get(['code'],false);
            $count = count($status);
            for ($i=0; $i < $count; $i++) { 
            	$recordpage [$k]['code'.$i] = $status[$i]['code'];
            }
        }
    }else{
        $recordpage  = null;
    }
    //返回数据，参见System/BaseClass.class.php方法
    $this->R(['recordpage'=>$recordpage,'pageInfo'=>$pageInfo]);
    }
    /**
     *往期获奖商品情况
     *传入专题id
     */
    public function beforeRecordList(){

    $this->V(['id'=>['egNum',null,true]]);
    $id = intval($_POST['id']);
    $pageInfo = $this->P();
    $file = ['id','goods_id','user_id','code','add_time'];

    $class = $this->table('bill')->where(['is_on'=>1,'thematic_id'=>$id])->order('add_time desc');

    //查询并分页
    $before = $this->getOnePageData($pageInfo,$class,'get','getListLength',[$file],false);
    if($before){
        foreach ($before  as $k=>$v){
            $status = $this->table('goods')->where(['is_on'=>1,'id'=>$v['goods_id']])->get(['goods_name','price'],true);
            $before[$k]['goods_name'] = $status['goods_name'];
            $before[$k]['price'] = $status['price'];
            $status = $this->table('user')->where(['is_on'=>1,'id'=>$v['user_id']])->get(['nickname','user_img'],true);
            $before[$k]['nickname'] = $status['nickname'];
            $before[$k]['user_img'] = $status['user_img'];
            $before[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
           
        }
    }else{
        $before  = null;
    }
    //返回数据，参见System/BaseClass.class.php方法
    $this->R(['before'=>$before,'pageInfo'=>$pageInfo]);
    }
    /**
     * 获得商品的商品详情
     * goods_id
     */
    public function luckyOneDetail(){

        $rule = [
                    'goods_id'   =>['egNum'],
                ];
                $this->V($rule); 

                foreach ($rule as $k => $v) {
                        $data[$k] = $_POST[$k];
                }
        //查询一条数据
            $dataClass = $this->H('BillDetail');
            $where='A.is_on=1 and A.is_confirm=1 and A.goods_id='.$data['goods_id'];
            $detail = $dataClass->getBillDetail($where);

            if(!$detail){
                $this->R('',70009);
            }
            foreach ($detail as $k => $v) {
            
           
            $detail [$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $status = $this->table('logistics')->where(['is_on'=>1,'bill_id'=>$v['id']])->get(['logistics_number'],true);
                $detail [$k]['logistics_number'] = $status['logistics_number'];
            }
            $this->R(['detail'=>$detail]);
    }
}