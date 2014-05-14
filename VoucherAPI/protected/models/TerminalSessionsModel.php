<?php


class TerminalSessionsModel extends CFormModel {
   
    
    public function getLastSessionDetails($terminalID){
        
        $connection = Yii::app()->db2;
        
        $sql = "SELECT LoyaltyCardNumber, MID, UserMode, UBServiceLogin, UBServicePassword, 
                ServiceID, UBHashedServicePassword, LastBalance FROM terminalsessions
                WHERE TerminalID = :terminal_id";        
        $command = $connection->createCommand($sql);
        $command->bindValue(":terminal_id", $terminalID);
        $result = $command->queryRow();

        return $result;
    }
    
}

