<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ServicesModel
 *
 * @author jdlachica
 */
class GeneratePasswordUBModel extends CFormModel{
    public $connection;
     
    public function __construct() 
    {
        $this->connection = Yii::app()->db2;
    }

        public function getInactivePasswordBatchInfo()
    {
        $sql = "SELECT gpb.GeneratedPasswordBatchID, gpp.PlainPassword, gpp.EncryptedPassword FROM generatedpasswordbatch gpb 
            INNER JOIN generatedpasswordpool gpp ON gpp.GeneratedPasswordBatchID = gpb.GeneratedPasswordBatchID 
            WHERE gpb.Status = 0 AND gpb.PlainPassword = gpp.PlainPassword LIMIT 1";
       $command = $this->connection->createCommand($sql);
       $result = $command->queryRow();
       return $result;
    }
  
    
}
?>