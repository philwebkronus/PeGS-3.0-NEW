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
class GeneratePasswordTBModel extends CFormModel{
    public $connection;
     
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
        
    public function chkoldsite($zsiteID){
        $sql = "SELECT GeneratedPasswordBatchID FROM generatedpasswordbatch 
        WHERE SiteID = :siteid AND Status = 1 AND DateUsed IS NOT NULL LIMIT 1";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":siteid", $zsiteID);
        $result = $command->queryRow();
        
        return $result;
        
    

           }
           
         public function chkpwdbatch(){ 
        $sql = "SELECT GeneratedPasswordBatchID FROM generatedpasswordbatch 
                     WHERE SiteID IS NULL AND DateUsed IS NULL AND Status = 0 
                     LIMIT 1";
        $command = $this->connection->createCommand($sql);
        $result = $command->queryRow();
        
        return $result;
        }
        
        
        
        public function getgeneratedpassword($zgenpwdid, $zservicegrpid){
        $sql = "SELECT PlainPassword, EncryptedPassword FROM generatedpasswordpool 
                     WHERE GeneratedPasswordBatchID = :genpwid AND ServiceGroupID = :servicegrpid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":genpwid", $zgenpwdid);
        $command->bindValue(":servicegrpid", $zservicegrpid);
        $result = $command->queryRow();
        
        return $result;
        }
        
        
        public function updateGenPwdBatch($zsiteID, $zgenpwdid)
    {
        $startTrans = $this->connection->beginTransaction();
        $result = $this->chkoldsite($zsiteID);
        if ($result) {
            //$zgenpwdbatchID = $result['GeneratedPasswordBatchID'];

            if ($zgenpwdid) {
                try {
//                        $isupdated1 = 0;
//                        $stmt = "UPDATE generatedpasswordbatch SET Status = 2 WHERE SiteID = :siteid AND GeneratedPasswordBatchID = :genpwid AND Status = 1";
//                        
//                        $param = array(':siteid' => $zsiteID, ':genpwid' => $zgenpwdid); 
//                        $command = $this->connection->createCommand($stmt);
//                        $command->bindValues($param);
//                        $result = $command->execute();
//                        if ($result){
//                        $isupdated1 = 1;
//                        
//                        }
//                        try{
                    $stmt2 = "UPDATE generatedpasswordbatch SET Status = 1, DateUsed = NOW(6), SiteID = :siteid 
                                     WHERE Status = 0 AND SiteID IS NULL AND DateUsed IS NULL AND GeneratedPasswordBatchID = :genpwid";
                    $param = array(':siteid' => $zsiteID, ':genpwid' => $zgenpwdid);
                    $command2 = $this->connection->createCommand($stmt2);
                    $command2->bindValues($param);
                    $result2 = $command2->execute();
                    if ($result2) {
                        try {
                                $startTrans->commit();
                                return 1;
                        } catch (PDOException $e) {
                            $startTrans->rollback();
                            return 0;
                        }
                    }
                    else{
                        return 0;
                    }
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    return 0;
                }
//                    } catch(PDOException $e){
//                        $startTrans->rollback();
//                        return 0;
//                    }
            }
        } else {
            try {
                $stmt = "UPDATE generatedpasswordbatch SET Status = 1, DateUsed = now_usec(), SiteID = :siteid 
                             WHERE Status = 0 AND SiteID IS NULL AND DateUsed IS NULL AND GeneratedPasswordBatchID = :genpwid";

                $param = array(':siteid' => $zsiteID, ':genpwid' => $zgenpwdid);
                $command = $this->connection->createCommand($stmt);
                $command->bindValues($param);
                $result = $command->execute();
                try {
                    if ($result) {
                        $startTrans->commit();
                        return 1;
                    } else
                        return 0;
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    return 0;
                }
            } catch (PDOException $e) {
                $startTrans->rollback();
                ;
                return 0;
            }
        }
    }
}
?>