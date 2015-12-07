<?php

namespace AppMain\helper;
use System\BaseHelper;
    /*
    * 订单商品信息联表查询class
    */
    class BillDetailHelper extends BaseHelper{
        /*
        *获取订单商品信息
        * @param unknown $whereStmt
        * @param string $bindParams
        * @param string $bindTypes
        * @param string $getOne
        * @param string $order
        * @param string $sqlFunction
        * @return Ambigous <NULL, unknown, multitype:, \System\database\this>
        */
        public function  getBillDetail($whereStmt, $bindParams = null, $bindTypes = null, $getOne = false, $order = null, $sqlFunction = null){
            $this->BillDetailLinkedTable($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order, $sqlFunction);
            return $this->getMulti($multiSqlStmt, $fieldsName, $getOne);
        }
        public function getBillDetailListLength($whereStmt, $bindParams = null, $bindTypes = null, $getOne = true, $order = null) {
            $this->BillDetailLinkedTable($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order);
            return $this->getMultiLength($multiSqlStmt, $fieldsName, $getOne);
        }
        private function BillDetailLinkedTable(&$fieldsName, &$multiSqlStmt, $whereStmt, $bindParams = null, $bindTypes = null, $orderBy = null, $sqlFunction = null) {            
            $fieldsName = array(
                    'bill as A' => 'goods_id,user_id,thematic_id,status,code,add_time',
                    'goods as B' => 'goods_sn,goods_title,price,goods_thumb,free_post',
                    'thematic as C' => 'thematic_name',
                    'user as D' => 'nickname,user_img,phone'
                    
            );
            $multiSqlStmt = array(
                'joinType' => array('left join','left join','left join'),
                'joinOn' => array('A.goods_id=B.id','A.thematic_id=C.id','A.user_id=D.id'),
                'whereStmt' => $whereStmt,
                'bindParams' => $bindParams,
                'bindTypes' => $bindTypes,
                'orderBy' => $orderBy,
                'sqlFunction' => $sqlFunction
            );
        }
        /*
        *获取中奖订单明细 
        * @param unknown $whereStmt
        * @param string $bindParams
        * @param string $bindTypes
        * @param string $getOne
        * @param string $order
        * @param string $sqlFunction
        * @return Ambigous <NULL, unknown, multitype:, \System\database\this>
        */
        public function  getBill($whereStmt, $bindParams = null, $bindTypes = null, $getOne = false, $order = null, $sqlFunction = null){
            $this->BillLinkedTable($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order, $sqlFunction);
            return $this->getMulti($multiSqlStmt, $fieldsName, $getOne);
        }
        public function getBillListLength($whereStmt, $bindParams = null, $bindTypes = null, $getOne = true, $order = null) {
            $this->BillLinkedTable($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order);
            return $this->getMultiLength($multiSqlStmt, $fieldsName, $getOne);
        }
        private function BillLinkedTable(&$fieldsName, &$multiSqlStmt, $whereStmt, $bindParams = null, $bindTypes = null, $orderBy = null, $sqlFunction = null) {            
            $fieldsName = array(
                    'bill as A' => 'id,bill_sn,code,add_time',
                    'goods as B' => 'goods_sn,goods_title,price',
                    'thematic as C' => 'thematic_name',
                    'user as D' => 'nickname,phone',
            );
            $multiSqlStmt = array(
                'joinType' => array('left join','left join','left join'),
                'joinOn' => array('A.goods_id=B.id','A.thematic_id=C.id','A.user_id=D.id'),
                'whereStmt' => $whereStmt,
                'bindParams' => $bindParams,
                'bindTypes' => $bindTypes,
                'orderBy' => $orderBy,
                'sqlFunction' => $sqlFunction
            );
        }
        
    }