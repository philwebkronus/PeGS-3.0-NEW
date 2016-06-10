<?php

class MemberServicesModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db2;
    }
    
    public function getCasinoCredentials($mid,$serviceid)
    {
        $sql = "SELECT ServiceUsername, ServicePassword FROM memberservices WHERE MID = :mid AND ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    
    public function getCasinoCredentialsAbbott($mid,$serviceid)
    {
        $sql = "SELECT ServiceUsername, ServicePassword, HashedServicePassword FROM memberservices WHERE MID = :mid AND ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getCasinoCredentialsCostelloAbbott($mid,$serviceid)
    {
        $sql = "SELECT ServiceUsername, ServicePassword, HashedServicePassword FROM memberservices WHERE MID = :mid AND ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function UpdateBalances($currentbalance, $lasttransaction, $mid, $serviceid){
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "UPDATE memberservices SET CurrentBalance = :curbalance, LastTransaction = :lasttransaction, CurrentBalanceLastUpdate = NOW(6) WHERE MID = :mid AND ServiceID = :serviceid";
            $param = array(':curbalance' => $currentbalance, ':lasttransaction' => $lasttransaction, ':mid' => $mid, ':serviceid' => $serviceid);
            $command = $this->connection->createCommand($sql);
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
    
    //added for changepassword module
        public function getMIDbyLogin($login,$serviceid)
    {
        $sql = "SELECT MID FROM memberservices WHERE ServiceUsername = :login AND ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":login", $login);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();
        
        return $result;
    }


        
    public function CheckMemberService($MID, $ServiceID) {
        $sql = "SELECT ServiceUsername,ServicePassword FROM memberservices WHERE MID = $MID AND ServiceID = $ServiceID";
       $command = $this->connection->createCommand($sql);
       $result = $command->queryRow();
       return $result;
    }
    
    public function updateMemberServicesUBPassword($servicePassword, $hashedServicePassword, $MID, $serviceID, $genpassbatchid) {
       
        $startTrans = $this->connection->beginTransaction();

        try {
            $query = "UPDATE membership.memberservices SET  ServicePassword = :ServicePassword,
                    HashedServicePassword = :HashedServicePassword WHERE MID = :MID AND ServiceID = :ServiceID";

            $param = array(':HashedServicePassword' => $hashedServicePassword,
                ':ServicePassword' => $servicePassword, ':MID' => $MID, ':ServiceID' => $serviceID);
            $command = $this->connection->createCommand($query);
            $command->bindValues($param);
            $result = $command->execute();
            if ($result) {
                $query2 = "UPDATE generatedpasswordbatch SET DateUsed = NOW(6), MID = :MID, Status = 1 WHERE GeneratedPasswordBatchID = :GeneratedPasswordBatchID";
                $param = array(':GeneratedPasswordBatchID' => $genpassbatchid, ':MID' => $MID);
                $command2 = $this->connection->createCommand($query2);
                $command2->bindValues($param);
                $result2 = $command2->execute();

                if ($result2) {
                    if ($result == true && $result2 == true) {
                        $startTrans->commit();
                        return 1;
                    } else {
                        return 0;
                    }
                } else {
                    $startTrans->rollback();
                    return false;
                }
            } else {
                $startTrans->rollback();
                return false;
            }
        } catch (PDOException $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
        }
    }

}

?>
