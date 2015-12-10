<?php

/**
 * @author taalcatara
 * 
 * @date 08-12-2015
 */
class GeneratedPasswordBatchModel
{

    public static $_instance = null;
    public $_connection;

    public function __construct()
    {
        $this->_connection = Yii::app()->db;
    }

    public static function model()
    {
        if (self::$_instance == null)
            self::$_instance = new GeneratedPasswordBatchModel();
        return self::$_instance;
    }
    
    public function getInactivePasswordBatch()
    {
        $query = "SELECT gpb.GeneratedPasswordBatchID FROM generatedpasswordbatch gpb
                  WHERE gpb.Status = 0 LIMIT 1";
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow();
        return $result['GeneratedPasswordBatchID'];
    }
    
    public function getInactivePasswordBatchDetails()
    {
        $query = "SELECT gpb.GeneratedPasswordBatchID, gpp.PlainPassword, gpp.EncryptedPassword FROM generatedpasswordbatch gpb 
            INNER JOIN generatedpasswordpool gpp ON gpp.GeneratedPasswordBatchID = gpb.GeneratedPasswordBatchID 
            WHERE gpb.Status = 0 LIMIT 1";
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow();
        return $result;
    }
    
    public function getPasswordByCasino($batchID, $serviceGrpID){
        $query = "SELECT gpp.PlainPassword, gpp.EncryptedPassword FROM generatedpasswordpool gpp
                  WHERE gpp.GeneratedPasswordBatchID = :BatchID AND ServiceGroupID = :ServiceGrpID";
        $param = array(':BatchID' => $batchID, ':ServiceGrpID' => $serviceGrpID);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        return $result;
    }
    
    public function getExistingPasswordBatch($MID)
    {
        $query = "SELECT gpb.GeneratedPasswordBatchID FROM generatedpasswordbatch gpb 
                    WHERE gpb.MID = :MID;";
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        
        $genPasswordBatchId = '';
        if(isset($result['GeneratedPasswordBatchID']))
            $genPasswordBatchId = $result['GeneratedPasswordBatchID'];
        
        return $genPasswordBatchId;
    }
    
    
    public function updatePasswordBatch($MID, $genpassbacthID)
    {
        $query = "UPDATE generatedpasswordbatch SET DateUsed = NOW(6), MID = :MID, Status = 1 WHERE GeneratedPasswordBatchID = :GenPassBatchID";
        $param = array(':MID' => $MID, ':GenPassBatchID' => $genpassbacthID);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    
    public function checkGenPassBatch($MID)
    {
        $query = "SELECT COUNT(GeneratedPasswordBatchID) AS Count FROM generatedpasswordbatch WHERE MID = :MID";
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        return $result['Count'];
    }
    
    public function getGenPassBatchPassword($MID, $servicegroupID)
    {
        $query = "SELECT gpb.GeneratedPasswordBatchID, gpb.PlainPassword, gpp.EncryptedPassword FROM generatedpasswordbatch gpb 
            INNER JOIN generatedpasswordpool gpp ON gpb.GeneratedPasswordBatchID = gpp.GeneratedPasswordBatchID WHERE gpb.MID = :MID AND gpp.ServiceGroupID = :ServiceGroupID";
        $param = array(':MID' => $MID, ':ServiceGroupID' => $servicegroupID);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        return $result;
    }
    
    /**
     * @author: Ralph Sison
     * @dateadded: Oct. 19, 2015
     */
    public function getInactivePasswordBatchInfo()
    {
        $query = "SELECT gpb.GeneratedPasswordBatchID, gpp.PlainPassword, gpp.EncryptedPassword FROM generatedpasswordbatch gpb 
            INNER JOIN generatedpasswordpool gpp ON gpp.GeneratedPasswordBatchID = gpb.GeneratedPasswordBatchID 
            WHERE gpb.Status = 0 AND gpb.PlainPassword = gpp.PlainPassword LIMIT 1";
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow();
        return $result;
    }

}