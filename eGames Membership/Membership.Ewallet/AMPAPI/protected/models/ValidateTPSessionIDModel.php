<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ValidateTPSessionIDModel
 *
 * @author jdlachica
 * @date 07/28/2014
 */
class ValidateTPSessionIDModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }
    
    public function validateTPSessionID($TPSessionID){
        $sql = 'SELECT COUNT(acs.AID) as Count,a.UserName,acs.AID, acs.DateCreated FROM accounts a, accountsessions acs WHERE acs.SessionID=:TPSessionID AND acs.AID=a.AID AND a.UserAccessTypeID = 1 LIMIT 1';
        $param = array(':TPSessionID'=>$TPSessionID);
        
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }
    
    public function updateSessionDateCreated($TPSessionID, $AID){
       
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'UPDATE accountsessions SET DateCreated=NOW(6) WHERE SessionID=:TPSessionID AND AID=:AID';
            $param = array(':TPSessionID'=>$TPSessionID,':AID'=>$AID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            
            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
            
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
        
    }
}
