<?php
/**
 * Model for egmsessions
 * date created 10/12/12
 * @author elperez
 */
class GamingSessionsModel{
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new GamingSessionsModel();
        return self::$_instance;
    }
    
    public function getActiveSession($token){
        $stmt = "SELECT DateStarted, LastBalance, TerminalID, LastTransactionDate, isVIP FROM egmsessions WHERE TokenID = :tokenID";
        $params = array(':tokenID'=>$token);
        $command = $this->_connection->createCommand($stmt);
        $row = $command->queryRow(true, $params);
        return $row;
    }
    
    public function getTerminalId($machineID){
        $stmt = "SELECT mac.EGMMachineInfoId_PK, mac.TerminalID, mac.TerminalIDVIP, 
                 mac.CreatedByAID, t.SiteID FROM egmmachineinfo mac
                 INNER JOIN terminals t ON mac.TerminalID = t.TerminalID
                 WHERE mac.Machine_Id = :machineId AND mac.IsActive = 1";
        $params = array(':machineId'=>$machineID);
        $command = $this->_connection->createCommand($stmt);
        return $command->queryRow(true, $params);
    }
    
    public function chkActiveSession($terminalID){
        $stmt = "SELECT TokenID FROM egmsessions WHERE TerminalID = :terminalID";
        $params = array(':terminalID'=>$terminalID);
        $command = $this->_connection->createCommand($stmt);
        $row = $command->queryRow(true, $params);
        return $row['TokenID'];
    }
    
    public function insertGamingSession($terminalID, $serviceID, $amount, $trans_summaryID, $tokenID, $machineInfoId, $isVip){
        try{
            $sql = 'INSERT INTO egmsessions (TerminalID, ServiceID, DateStarted, LastBalance, LastTransactionDate, ' .
                'TransactionSummaryID, TokenID, EGMMachineInfoId_PK, isVIP) VALUES (:terminal_id, :service_id, NOW(6), :amount, NOW(6), '.
                ':trans_summary_id, :token_id, :mac_info_id, :is_vip)';
            $command = $this->_connection->createCommand($sql);
            $params = array(':terminal_id'=>$terminalID, ':service_id'=>$serviceID,':amount'=>$amount, 
                            ':trans_summary_id'=>$trans_summaryID, ':token_id'=>$tokenID,"mac_info_id"=>$machineInfoId,
                            ':is_vip'=>$isVip);
            $isRecorded = $command->execute($params);
            if(!$isRecorded){
                $this->log($command->getText().$command->getBound());
            }
            return $isRecorded;
        } catch (Exception $e){
            $this->log($e->getMessage());
            return false;
        }
    }
    
    public function updateLastTransDate($tokenID){
        $sql = "UPDATE egmsessions SET LastTransactionDate = NOW(6) WHERE TokenID = :token_id";
        $params = array(":token_id"=>$tokenID);
        $command = $this->_connection->createCommand($sql);
        $isUpdated = $command->execute($params);
        if(!$isUpdated){
            $this->log($command->getText().$command->getBound());
        }
        return $isUpdated;
    }
    
    public function deleteGamingSessions($terminal_id, $stackerbatchID){
        $stackerSummaryModel = new StackerSummaryModel();
        $siteaccounts        = new SiteAccountsModel();
        
        $beginTrans = $this->_connection->beginTransaction();
        
        //get cashier user
        $user = $siteaccounts->getAIDByAccountTypeIDAndTerminalID(15, $terminal_id);
        if (!is_null($stackerbatchID))
        {
            $updatestat = $stackerSummaryModel->updateStackerSummaryStatus($stackerbatchID, StackerSummaryModel::STATUS_WITHDRAW, $user);
        }
        try
        {
            $sql = 'DELETE FROM egmsessions WHERE TerminalID = :terminal_id';
            $param = array(':terminal_id' => $terminal_id);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try{
                $beginTrans->commit();
                $bool = true;
            }
            catch(Exception $e){
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                $bool = false;
            }

        }catch(Exception $e){
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            $bool = false;
        }
        return $bool;
    }
    
    public function updateGamingSessionById($serviceID, $terminalBalance, $terminalID){
        
         $beginTrans = $this->_connection->beginTransaction();
         
         try 
         {
             $sql = 'UPDATE egmsessions SET ServiceID = :service_id, LastTransactionDate = NOW(6), LastBalance = :terminal_balance'.
                    ' WHERE TerminalID = :terminal_id';

             $param = array(':service_id'=>$serviceID,
                            ':terminal_balance'=>$terminalBalance,
                            ':terminal_id'=>$terminalID);

             $command = $this->_connection->createCommand($sql);

             $command->bindValues($param);

             $command->execute();

             try {
                 $beginTrans->commit();

                 return true;

             } catch (Exception $e){
                 $beginTrans->rollback();
                 Utilities::log($e->getMessage());
                 return false;
             }
         }
         catch(Exception $e)
         {
             $beginTrans->rollback();
             Utilities::log($e->getMessage());
             return false;
         }
    }
    
    public function showTerminalIdByToken($token){
        $stmt = "SELECT TerminalID FROM egmsessions WHERE TokenID = :tokenID";
        $params = array(':tokenID'=>$token);
        $command = $this->_connection->createCommand($stmt);
        $row = $command->queryRow(true, $params);
        return $row['TerminalID'];
    }
    
    protected function log($message) 
    {
        Yii::log($message, 'error', 'egm.models.GamingSessionsModel');
    }
    
    public function getEGMMachineInfoID($token)
    {
        $sql = "SELECT EGMMachineInfoId_PK FROM egmsessions WHERE TokenID = :tokenid";
        $params = array(':tokenid'=>$token);
        $command = $this->_connection->createCommand($sql);
        $row = $command->queryRow(true, $params);
        return $row['EGMMachineInfoId_PK'];
    }
    
    public function verifyToken($token)
    {
        $sql = "SELECT COUNT(EGMMachineInfoId_PK) as ctrtoken FROM egmsessions WHERE TokenID = :tokenid;";
        $params = array(':tokenid'=>$token);
        $command = $this->_connection->createCommand($sql);
        $row = $command->queryRow(true, $params);
        return $row;
    }
    
    public function insertEgmSession($mid, $terminalID, $serviceID, $aid){
       
            $sql = 'INSERT INTO egmsessions (MID, TerminalID, ServiceID, DateCreated, CreatedByAID) VALUES (:mid, :terminal_id, :service_id, NOW(6), :aid)';
            $command = $this->_connection->createCommand($sql);
            $params = array(':mid'=>$mid, ':terminal_id'=>$terminalID, ':service_id'=>$serviceID, ':aid'=>$aid);
            $command->execute($params);
            $egmsession_id = $this->_connection->getLastInsertID();
            return $egmsession_id;
            
    }
    
    public function getlastinsertedegmsession($egmsessionID){
       
            $sql = "SELECT TerminalID, ServiceID, DateCreated FROM egmsessions WHERE EGMSessionID = :egmsessionID";
            $params = array(':egmsessionID'=>$egmsessionID);
            $command = $this->_connection->createCommand($sql);
            $row = $command->queryRow(true, $params);
            return $row;
            
    }
    
    
    public function chkActiveEgmSession($terminalID){
        $stmt = "SELECT EGMSessionID, StackerBatchID FROM egmsessions WHERE TerminalID = :terminalID";
        $params = array(':terminalID'=>$terminalID);
        $command = $this->_connection->createCommand($stmt);
        $row = $command->queryRow(true, $params);
        return $row;
    }
    
    public function chkActiveEgmSessionByMID($MID){
        $stmt = "SELECT EGMSessionID, StackerBatchID FROM egmsessions WHERE MID = :mid";
        $params = array(':mid'=>$MID);
        $command = $this->_connection->createCommand($stmt);
        $row = $command->queryRow(true, $params);
        return $row;
    }
    public function chkIfStackerBatchIDExist($stackerbatchID)
    {
        $sql = "SELECT COUNT(EGMSessionID) as Count FROM egmsessions 
                WHERE StackerBatchID = :stackerbatchID";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":stackerbatchID", $stackerbatchID);
        $result = $command->queryRow();
        
        return $result;
    }
    /**
     * Check if has Active EGM Session by both checking of TerminalID and MID
     * @param type $terminalID
     * @param type $MID
     * @return array Count
     * @author Mark Kenneth Esguerra
     * @date June 5, 2014
     */
    public function checkEgmSessionBoth($terminalID, $MID)
    {
        $sql = "SELECT COUNT(EGMSessionID) as Count, EGMSessionID FROM egmsessions 
                WHERE TerminalID = :terminalID AND MID = :MID";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":terminalID", $terminalID);
        $command->bindValue(":MID", $MID);
        $result = $command->queryRow();
        
        return $result;
    }
    /**
     * Get StackerBatchID of the EGM Session
     * @param type $terminalID
     * @param type $MID
     * @return array Stacker Batch ID
     * @author Mark Kenneth Esguerra
     * @date June 5, 2014
     */
    public function getStackerBatchID($egmsessionID)
    {
        $sql = "SELECT StackerBatchID FROM egmsessions 
                WHERE EGMSessionID = :egmsessionID";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":egmsessionID", $egmsessionID);
        $result = $command->queryRow();
        
        if ($result['StackerBatchID'] != "")
        {
            return $result['StackerBatchID'];
        }
        else
        {
            return null;
        }
    }
}

?>
