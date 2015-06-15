<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GetIsSmokerModel
 *
 * @author jdlachica
 * @Date 07/24/2014
 */
class GetIsSmokerModel {
    //put your code here
    public function GetIsSmoker($TPSessionID){
         $sql = 'SELECT COUNT(`AID`) as Count,`AID`, `Username`, `Password`, `Status` FROM `accounts` WHERE `Username`=:Username OR `Password`=:Password AND `Status`=:Status LIMIT 1';
        $param = array(':Username'=>$Username,':Password'=>$Password, ':Status'=>1);
        
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }
}
