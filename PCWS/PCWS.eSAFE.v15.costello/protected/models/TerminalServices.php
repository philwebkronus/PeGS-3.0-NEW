<?php

class TerminalServices extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
      public function updateterminalpassword($password, $hashedpassword,$terminalid, $terminalidvip,$serviceid) {
        $startTrans = $this->connection->beginTransaction();
        try {
            $sql = "UPDATE terminalservices SET ServicePassword = :password, HashedServicePassword = :hashed, Status = 1, isCreated = 1
                             WHERE TerminalID IN (:terminalid,:terminalidvip) AND ServiceID = :serviceid";
            $param = array(':password' => $password, ':hashed' => $hashedpassword, 
                        ':terminalid'=>$terminalid, ':terminalidvip'=>$terminalidvip,':serviceid'=>$serviceid);
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
    
    public function getterminalpassword($temrinalid,$serviceid)
    {
        $sql = "SELECT ServicePassword FROM terminalservices WHERE TerminalID = :terminalid AND ServiceID = :serviceid;";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":terminalid", $temrinalid);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();
        
        return $result;
        
    }
   
}
?>
