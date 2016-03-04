<?php

class MemberCardsModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db3;
    }
    /* public function getCardNUmber($mid)
    {
        $sql = "SELECT CardNumber FROM membercards WHERE MID = :mid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
    */
    public function getMID($cardnumber)
    {
        $sql = "SELECT MID FROM membercards WHERE CardNumber = :cardnumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getStatusByCardNumber($cardnumber)
    {
        $sql = "SELECT Status FROM membercards WHERE CardNumber = :cardnumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getCardPoints($cardnumber)
    {
        $sql = "SELECT CurrentPoints, LifetimePoints, RedeemedPoints, BonusPoints FROM membercards WHERE CardNumber = :cardnumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getCardStatus($cardnumber) {
        $sql = "SELECT Status FROM membercards WHERE CardNumber = :cardnumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function insertcardtrans($serviceid, $siteid, $cardid, $mid, $transtype, $serviceusername, $transdate){
        
        $startTrans = $this->connection->beginTransaction();
   
        try {

            $sql = "INSERT INTO cardtransactions (ServiceID, SiteID, CardID, MID, TransactionType, TerminalLogin, TransactionDate, DateCreated) "
                ."VALUES (:serviceid, :siteid, :cardid, :mid, :transtype, :serviceusername, :transdate, NOW(6))";
            $param = array(
                                    ':serviceid'=>$serviceid,
                                    ':siteid'=>$siteid,
                                    ':cardid'=>$cardid, 
                                    ':mid'=>$mid,
                                    ':transtype'=>$transtype,
                                    ':serviceusername'=>$serviceusername, 
                                    ':transdate'=>$transdate
                                );

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

        } catch (PDOException $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
             
    }
    
    public function getCardID($cardnumber) {
        $sql = "SELECT CardID FROM cards WHERE CardNumber = :cardnumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getDateUpdated($cardnumber)
    {
        $sql = "SELECT DateUpdated FROM membercards WHERE CardNumber = :cardnumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
        /**
     * Mark Kenneth Esguerra
     * @date July 14, 2015
     * @param type $cardnumber
     * @return type
     */
    public function updateLifetimePoints($cardnumber, $lifetimepoints) {
         $pdo = $this->connection->beginTransaction();
         try {
             $sql = "UPDATE membercards 
                     SET LifetimePoints = :LTpoints, 
                         DateUpdated = NOW(6) 
                     WHERE CardNumber = :cardnumber";
             $command = $this->connection->createCommand($sql);
             $command->bindValue(":cardnumber", $cardnumber);
             $command->bindValue(":LTpoints", $lifetimepoints); 
             $result = $command->execute();
             if ($result > 0) {
                 try {
                    $pdo->commit();
                    return array('TransCode' => 0, 
                                 'TransMsg' => 'Lifetime points updated.');
                 }
                 catch (CDbException $e) {
                     $pdo->rollback();
                     return array('TransCode' => 1, 
                                  'TransMsg' => 'Transaction failed.');
                 }
             }
             else {
                 $pdo->rollback();
                 return array('TransCode' => 1, 
                              'TransMsg' => 'Transaction failed.');
             }
         }
         catch (CDbException $e) {
             $pdo->rollback();
             return array('TransCode' => 1, 
                          'TransMsg' => 'Transaction failed.');
         }
    }
}
?>
