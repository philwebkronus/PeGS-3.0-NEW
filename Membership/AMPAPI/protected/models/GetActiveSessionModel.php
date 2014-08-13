<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GetActiveSessionModel
 *
 * @author jdlachica
 * @Date 07/24/2014
 */
class GetActiveSessionModel {
    //put your code here
    
    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }
    
    public function getActiveSession($TPSessionID, $Username){
        $sql = 'SELECT COUNT(accs.AID) as Count,accs.AID, accs.SessionID, a.Username, accs.DateCreated FROM accountsessions accs, accounts a WHERE a.AID = accs.AID AND accs.SessionID=:SessionID AND a.Username=:Username AND a.Status=:Status';

        $param = array(':SessionID'=>$TPSessionID,':Username'=>$Username,':Status'=>'1');
        
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }
    
    
}
