<?php 
	namespace AppMain\controller\Admin;
	use \System\BaseClass;
	/**
     * 订单class
     */
	class BillController extends BaseClass {
		/**
         * 订单列表
         */
		public function billList(){
	        $where=['is_on'=>1];
	        $pageInfo = $this->P();
	        $field = ['id','user_id','thematic_id','goods_id','code','status','is_confirm','is_post','is_cancel','add_time','update_time'];
	        $class = $this->table('bill')->where($where)->order('add_time desc');
	        //查询并分页
	        $billlist = $this->getOnePageData($pageInfo,$class,'get','getListLength',[$field],false);
	        if($billlist){
	            foreach ($billlist as $k=>$v){
	                $billlist[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
	                $billlist[$k]['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
	            }
	        }else{
	            $billlist = null;
	        }
	        $this->R(['billlist'=>$billlist,'pageInfo'=>$pageInfo]);
		}

		/**
         * 查询中奖订单明细
         */
		public function billOneList(){
			$where=['is_on'=>1];
	        $pageInfo = $this->P();
	        //调用Helper类
	        $dataClass = $this->H('BillDetail');
	        $order = "A.id asc";
	        //$field = ['id','bill_sn','code','goods_sn','goods_title','price','thematic_name','nickname','phone','add_time'];
	        $billList=$dataClass->getbill($where,null,null,false,$order);
            $bill=$this->getOnePageData($pageInfo, $dataClass, 'getbill','getbillListLength',[$where,null,null,false,$order],true);
            if($bill){
                foreach ($bill as $k=>$v){
                    $bill[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                }
            }else{
                $bill = null;
            } 
            $this->R(['bill'=>$bill,'pageInfo'=>$pageInfo]);
		}
		/**
         * 查询一条订单记录
         */
		public function billOneDetail(){
			$this->V(['id'=>['egNum',null,true]]);
            $id = intval($_POST['id']);
            //查询一条数据
            $bill = $this->table('bill')->where(['is_on'=>1,'id'=>$id])->get(null,true);
            if(!$bill){
                $this->R('',70009);
            }
            $bill['update_time'] = date('Y-m-d H:i:s',$bill['update_time']);
            $bill['add_time'] = date('Y-m-d H:i:s',$bill['add_time']);
            $this->R(['bill'=>$bill]);
		}

		/**
	     *删除一条订单数据（设置数据库字段为0，相当于回收站）
	     */
	    public function billDelete(){
	    
	        $this->V(['id'=>['egNum',null,true]]);
	        $id = intval($_POST['id']);
	         
	        $bill = $this->table('bill')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
	    
	        if(!$bill){
	            $this->R('',70009);
	        }
	    
	        $bill = $this->table('bill')->where(['id'=>$id])->update(['is_on'=>0]);
	        if(!$bill){
	            $this->R('',40001);
	        }
	        $this->R();
	    }

	    /**
	     *删除一条订单数据（清除数据）
	     */
	    public function billDeleteConfirm(){
	    
	        $this->V(['id'=>['egNum',null,true]]);
	        $id = intval($_POST['id']);
	         
	        $bill = $this->table('bill')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
	    
	        if(!$bill){
	            $this->R('',70009);
	        }
	        $bill = $this->table('bill')->where(['id'=>$id])->delete();
	        if(!$bill){
	            $this->R('',40001);
	        }
	        $this->R();
	    }

	    /**
         * 确认订单
         */
	    public function billConfirm(){
	    
	        $this->V(['id'=>['egNum',null,true]]);
	        $id = intval($_POST['id']);
	         
	        $bill = $this->table('bill')->where(['id'=>$id,'is_on'=>1,'is_confirm'=>0])->get(['id'],true);
	    
	        if(!$bill){
	            $this->R('',70009);
	        }
	    
	        $bill = $this->table('bill')->where(['id'=>$id])->update(['is_confirm'=>1]);
	        if(!$bill){
	            $this->R('',40001);
	        }
	        $bill = $this->table('bill')->where(['id'=>$id])->get(['id,user_id'],true);
	        if(!$bill){
	            $this->R('',40001);
	        }
	        $this->R(['bill'=>$bill]);
	    }

	    /**
         * 取消订单
         */
	    public function billCancel(){
	    
	        $this->V(['id'=>['egNum',null,true]]);
	        $id = intval($_POST['id']);
	         
	        $bill = $this->table('bill')->where(['id'=>$id,'is_on'=>1,'is_cancel'=>0])->get(['id'],true);
	    
	        if(!$bill){
	            $this->R('',70009);
	        }
	    
	        $bill = $this->table('bill')->where(['id'=>$id])->update(['is_cancel'=>1]);
	        if(!$bill){
	            $this->R('',40001);
	        }
	        $this->R();
	    }
	    public function outputExcel(){
	    	$data = array(
	    		'title' =>"一元购中奖订单明细",
	    		'sn'    =>"订单编号",
	    		'object_name' =>"商品名",
	    		'code'  =>"中奖认购码",
	    		'time'  =>"时间",
	    		);
	    	$list = array( 0 =>array(
	    		'sn'    =>"20151201882281",
	    		'object_name' =>"锤子手机",
	    		'code'  =>"HE09VIFUH86RF3W",
	    		'time'  =>"2015.12.01",)
	    		);
	    	$outputExcel = $this->H('Excel')->PHPExcel($list,$data);
	    	if (!$outputExcel) {
	    		$this->R('',40001);
	    	}
	    }

	}

 ?>