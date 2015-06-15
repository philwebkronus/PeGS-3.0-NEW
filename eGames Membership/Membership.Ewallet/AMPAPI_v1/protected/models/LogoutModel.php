<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogoutModel
 *
 * @author jdlachica
 * @date Aug06,2014
 */
class LogoutModel {
    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }
    
    public function logout($AID, $TPSessionID){
        
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'DELETE FROM accountsessions WHERE AID=:AID AND SessionID = :TPSessionID';
            $param = array(':AID' => $AID, ':TPSessionID' => $TPSessionID);
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
