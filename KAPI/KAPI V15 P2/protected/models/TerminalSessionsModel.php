<?php

/**
 * Date Created 11 7, 11 11:38:11 AM <pre />
 * Date Modified 10/12/12
 * Description of TerminalSessionsModel
 * @author Bryan Salazar
 * @author Edson Perez
 */

class TerminalSessionsModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new TerminalSessionsModel();
        return self::$_instance;
    }
    
    public function getServiceId($terminal_id) {
        $sql = 'SELECT ServiceID FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['ServiceID']))
            return false;
        return $result['ServiceID'];
    }
    
    public function getServiceUserName($terminal_id) {
        $sql = 'SELECT UBServiceLogin FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['UBServiceLogin']))
            return false;
        return $result['UBServiceLogin'];
    }

    public function deleteTerminalSessionById($terminal_id) {
        $beginTrans = $this->_connection->beginTransaction();
        
        try{
            $sql = 'DELETE FROM terminalsessions WHERE TerminalID = :terminal_id';
            $param = array(':terminal_id' => $terminal_id);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            
            try{
                $beginTrans->commit();
                return true;
            } catch(PDOException $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        }catch(CDbException $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
        
    }
    
    public function updateTerminalSessionById($terminal_id,$service_id,$terminal_balance) {
        $beginTrans = $this->_connection->beginTransaction();
        
        try{
            
             $sql = 'UPDATE terminalsessions SET ServiceID = :service_id, LastBalance = :terminal_balance, ' . 
                    'LastTransactionDate = NOW(6) WHERE TerminalID = :terminal_id';
             
             $param = array(':service_id'=>$service_id,
                            ':terminal_balance'=>$terminal_balance,
                            ':terminal_id'=>$terminal_id);
             
             $command = $this->_connection->createCommand($sql);
             
             $command->bindValues($param);
             
             $command->execute();
             
             try {
                 $beginTrans->commit();

                 return true;

             } catch (PDOException $e){
                 $beginTrans->rollback();
                 Utilities::log($e->getMessage());
                 return false;
             }
             
        }catch(CDbException $e){
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    public function isSessionActive($terminal_id) {
        $sql = 'SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE TerminalID = :terminal_id AND DateEnded = 0';
        $param = array(':terminal_id'=>$terminal_id);
        $command = Yii::app()->db->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['Cnt']))
            return false;
        return $result['Cnt'];
    }
    
    public function insert($terminal_id,$service_id,$amount,$trans_summary_id) {
        
        $sql = 'INSERT INTO terminalsessions (TerminalID, ServiceID, DateStarted, LastBalance, LastTransactionDate, ' .
                'TransactionSummaryID) VALUES (:terminal_id, :service_id, NOW(6), :amount, NOW(6), :trans_summary_id)';
        
        $param = array(
            ':terminal_id'=>$terminal_id,
            ':service_id'=>$service_id,
            ':amount'=>$amount,
            ':trans_summary_id'=>$trans_summary_id);
        
        $command = $this->_connection->createCommand($sql);
        return $command->execute($param);
    }
    
    
    public function insert2($terminal_id,$service_id,$amount,$trans_summary_id,
            $loyalty_card, $mid, $user_mode, $casino_login = '', $casino_pwd = '', $casinohashed_pwd = '') {
        
        $beginTrans = $this->_connection->beginTransaction();
        try {
            $stmt = $this->_connection->createCommand('INSERT INTO terminalsessions (TerminalID, ServiceID, DateStarted, 
                    LastBalance, LastTransactionDate, TransactionSummaryID, LoyaltyCardNumber, 
                    MID, UserMode, UBServiceLogin, UBServicePassword, UBHashedServicePassword) 
                    VALUES (:terminal_id, :service_id, NOW(6), :amount, NOW(6), 
                    :trans_summary_id, :loyalty_card, :mid, :user_mode, :casino_login, :casino_pwd, :casinohashed_pwd)');

            $stmt->bindValue(':terminal_id',$terminal_id);
            $stmt->bindValue(':service_id',$service_id);
            $stmt->bindValue(':amount',$amount);
            $stmt->bindValue(':trans_summary_id',$trans_summary_id);
            $stmt->bindValue(':loyalty_card',$loyalty_card);
            $stmt->bindValue(':mid',$mid);
            $stmt->bindValue(':user_mode',$user_mode);
            $stmt->bindValue(':casino_login',$casino_login);
            $stmt->bindValue(':casino_pwd',$casino_pwd);
            $stmt->bindValue(':casinohashed_pwd',$casinohashed_pwd);
            
            $result = $stmt->execute();
            
            try {
                $beginTrans->commit();
                return $result;
            } catch(PDOException $e) {
                $beginTrans->rollback();
                return false;
            }
        } catch (CDbException $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    public function getTransDateTime() {
        $time =microtime(true);         
        $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);   
        $rawdate = new DateTime( date('Y-m-d H:i:s.'.$micro_time, $time) );    
        return $rawdate->format("Y-m-d H:i:s.u");
    }
    
    /**
     * Get Active Terminal depending if it is a reg or vip terminal
     * @param str $terminalCode
     * @return obj
     */
    public function getActiveTerminal($terminalCode){
        $terminalCodeVip = $terminalCode."VIP";
        $sql = "SELECT ts.TerminalID, t.Status, t.isVIP FROM terminals t
                INNER JOIN terminalsessions ts ON t.TerminalID = ts.TerminalID
                WHERE t.TerminalCode IN (:terminal_code, :terminal_code_vip)";
        $param = array(':terminal_code'=>$terminalCode,
                       ':terminal_code_vip'=>$terminalCodeVip);
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $param);
    }
    
    /**
     * Get current balance from a terminal to withdraw
     * @param int $terminalID
     * @param int $serviceID
     * @return float
     */
    public function getCurrentBalance($terminalID, $serviceID) {
        $sql = 'SELECT LastBalance FROM terminalsessions WHERE TerminalID = :terminal_id AND ServiceID = :service_id';
        $param = array(':terminal_id'=>$terminalID,':service_id'=>$serviceID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['LastBalance'];
    }
    
    //@author JunJun S. Hernandez
    
    public function getPlayerMode($terminalID) {
        $sql = 'SELECT UserMode FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['UserMode'];
    }
    
    public function getStartDateTime($terminalID) {
        $sql = 'SELECT DateStarted FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['DateStarted'];
    }
    
    public function getLoyaltyCardNumber($terminalID) {
        $sql = 'SELECT LoyaltyCardNumber FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['LoyaltyCardNumber'];
    }
    
    public function getCurrentCasino($terminalID) {
        $sql = 'SELECT ServiceID FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['ServiceID'];
    }
    
    public function getCardNumber($terminalID) {
        $sql = 'SELECT LoyaltyCardNumber FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['LoyaltyCardNumber'];
    }
    
    public function getMatchedTerminalIDAndCardNumber($terminalID, $cardnumber) {
        $sql = 'SELECT TerminalID FROM terminalsessions WHERE TerminalID = :terminal_id AND LoyaltyCardNumber = :card_number';
        $param = array(':terminal_id'=>$terminalID, ':card_number'=>$cardnumber);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['TerminalID'];
    }
    
    public function getMatchedTerminalAndServiceID($TerminalID, $ServiceID){
        $sql = "SELECT TerminalID FROM terminalsessions WHERE TerminalID = :terminal_id AND ServiceID = :service_id";
        $param = array(':terminal_id'=>$TerminalID, ':service_id'=>$ServiceID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        if($result['TerminalID']=="")
            return 0;
        else
            return count($result['TerminalID']);
    }
    
    public function getLastSessionDetails($terminalID, $terminalIDVIP){
        $sql = "SELECT TerminalID, LoyaltyCardNumber, MID, UserMode, UBServiceLogin, UBServicePassword, 
                ServiceID, UBHashedServicePassword, LastBalance, TransactionSummaryID FROM terminalsessions
                WHERE TerminalID IN (:terminal_id, :terminal_id_vip)";
        $param = array(':terminal_id'=>$terminalID,':terminal_id_vip'=>$terminalIDVIP);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(empty($result))
            return false;
        return $result;
    }
    
    
    public function getLastSessSummaryID($terminalID){
        $sql = 'SELECT TransactionSummaryID FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['TransactionSummaryID']))
            return false;
        return $result['TransactionSummaryID'];
    }
    /**
     * Update Terminal Balance
     * @author Mark Kenneth Esguerra
     * @date June 16, 2014
     * 
     */
    public function updateTerminalBalance($terminalID, $amount)
    {
        $pdo = $this->_connection->beginTransaction();
        
        try
        {
            $sql = "UPDATE terminalsessions SET LastBalance = :lastbalance 
                    WHERE TerminalID = :terminalID";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":lastbalance", $amount);
            $command->bindValue(":terminalID", $terminalID);
            $result = $command->execute();
            
            if ($result > 0)
            {
                try
                {
                    $pdo->commit();
                    return array('TransCode' => 0, 
                                 'TransMsg' => 'Successfully updated.');
                }
                catch (CDbException $e)
                {
                    $pdo->rollback();
                    return array('TransCode' => 1, 
                                 'TransMsg' => 'An error occured while updating records on database.');
                }
            }
            else
            {
                $pdo->rollback();
                return array('TransCode' => 1, 
                             'TransMsg' => 'Nothing to update.');
            }
        }
        catch (CDbException $e)
        {
            $pdo->rollback();
            return array('TransCode' => 1, 
                         'TransMsg' => 'An error occured while updating records on database.');
        }
    }
    /**
     * Checks if the player has Active Terminal Session
     * @param type $MID MembeID of the Player/Member
     * @param type $serviceID Casino ID 
     * @return boolean/int Return 1 if has session, FALSE if none
     * @author Mark Kenneth Esguerra
     * @date August 14, 2014
     */
    public function checkIfHasTerminalSession($MID, $serviceID)
    {
        $sql = "SELECT MID 
                FROM terminalsessions 
                WHERE MID = :mid 
                AND ServiceID = :serviceID";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":mid", $MID);
        $command->bindValue(":serviceID", $serviceID);
        $result = $command->queryRow();
        
        return $result;
    }
    /**
     * Check if terminal (regular or vip) has on-going 
     * terminal session
     * @author Mark Kenneth Esguerra
     * @date April 6, 2015
     */
    public function isTerminalActive ($terminal_reg, $terminal_vip) {
        $sql = "SELECT COUNT(TerminalID) as Count 
                FROM terminalsessions 
                WHERE TerminalID IN (:terminal_reg, :terminal_vip)";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":terminal_reg", $terminal_reg);
        $command->bindValue(":terminal_vip", $terminal_vip);
        $result = $command->queryRow();
        
        return $result['Count'];
    }
    
    /**
     * Checks if the player has Active Terminal Session
     * @param type $MID MembeID of the Player/Member
     * @param type $serviceID Casino ID 
     * @return boolean/int Return 1 if has session, FALSE if none
     * @author Ralph Sison
     * @date Dec. 28, 2015
     */
    public function checkIfHasTerminalSession2($MID, $serviceID)
    {
        $sql = "SELECT TerminalID 
                FROM terminalsessions 
                WHERE MID = :mid 
                AND ServiceID = :serviceID";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":mid", $MID);
        $command->bindValue(":serviceID", $serviceID);
        $result = $command->queryRow();
        
        return $result;
    }
        public function getTransSummaryID($mid, $serviceid) {
        $sql = "SELECT TransactionSummaryID FROM terminalsessions WHERE MID = :mid AND ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':mid', $mid);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();

        return $result;
    }
}

