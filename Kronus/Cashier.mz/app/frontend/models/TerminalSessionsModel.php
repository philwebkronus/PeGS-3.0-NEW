<?php

/**
 * Date Created 11 7, 11 11:38:11 AM <pre />
 * Description of TerminalSessionsModel
 * @author Bryan Salazar
 */
class TerminalSessionsModel extends MI_Model 
{
    
    public function getServiceId($terminal_id) 
    {
        $sql = 'SELECT ServiceID FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $result = $this->find();
        if(!isset($result['ServiceID']))
            return false;
        return $result['ServiceID'];
    }
    
    public function getDataById($terminal_id) 
    {
        $sql = 'SELECT TIMESTAMPDIFF(MINUTE,LastTransactionDate,NOW()) as minutes,ServiceID,DateStarted, LastTransactionDate FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $result = $this->find();
        return $result;
    }

    public function deleteTerminalSessionById($terminal_id) 
    {
        $sql = 'DELETE FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id' => $terminal_id);
        return $this->exec($sql,$param);
    }
    
    public function updateTerminalSessionById($terminal_id,$service_id,$terminal_balance) 
    {
        $sql = 'UPDATE terminalsessions SET ServiceID = :service_id, LastBalance = :terminal_balance, ' . 
                'LastTransactionDate = now(6) WHERE TerminalID = :terminal_id';
        $param = array(
            ':service_id'=>$service_id,
            ':terminal_balance'=>$terminal_balance,
            ':terminal_id'=>$terminal_id);
        return $this->exec($sql,$param);
    }
    
    public function isSessionActive($terminal_id) 
    {
        $sql = 'SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE TerminalID = :terminal_id AND DateEnded = 0';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $result = $this->find();
        if(!isset($result['Cnt']))
            return false;
        return $result['Cnt'];
    }
    
    public function checkActiveSession($terminalcode) 
    {
        $terminalcodevip = $terminalcode."VIP";
        $sql = 'SELECT COUNT(ts.TerminalID) AS Cnt FROM terminalsessions ts 
                    INNER JOIN terminals t ON ts.TerminalID = t.TerminalID
                    WHERE t.TerminalCode IN (:terminalcode, :terminalcodevip)';
        $param = array(':terminalcode'=>$terminalcode,':terminalcodevip'=>$terminalcodevip);
        $this->exec($sql,$param);
        $result = $this->find();
        if(!isset($result['Cnt']))
            return false;
        return $result['Cnt'];
    }
    
    /**
     * Inserts record in terminalsessions table
     * handles the ff. rules / constraints : 
     * ( 1. same card number and casino)
     * ( 2. same card number and terminal)
     * @modified elperez, 03-22-13
     * @version modified to support Kronus UB
     * @param int $terminal_id
     * @param int $service_id
     * @param str $amount
     * @param int $trans_summary_id
     * @param str $loyalty_card
     * @param int $mid
     * @param int $user_mode
     * @return type
     */
    public function insert($terminal_id,$service_id,$amount,$trans_summary_id, $loyalty_card, $mid, $user_mode, 
            $casino_login = '', $casino_pwd = '', $casinohashed_pwd = '') 
            //$casino_login = '', $casino_pwd = '', $casinohashed_pwd = '', $vip_type = 0) // CCT Added vip_type VIP
    {

        if($loyalty_card == '')
        {
            $loyalty_card = $terminal_id;
        }
        try 
        {
            $this->beginTransaction();
            //$sql = 'INSERT INTO terminalsessions (TerminalID, ServiceID, DateStarted, LastBalance, LastTransactionDate, 
            //    TransactionSummaryID, LoyaltyCardNumber, MID, UserMode, UBServiceLogin, UBServicePassword, 
            //    UBHashedServicePassword, OptionID1) 
            //    VALUES (:terminal_id, :service_id, now(6), :amount, now(6), :trans_summary_id, :loyalty_card, :mid, 
            //    :user_mode, :casino_login, :casino_pwd, :casinohashed_pwd, :vip_type)'; // CCT added vip_type and OptionID1

            $sql = 'INSERT INTO terminalsessions (TerminalID, ServiceID, DateStarted, LastBalance, LastTransactionDate, 
                TransactionSummaryID, LoyaltyCardNumber, MID, UserMode, UBServiceLogin, UBServicePassword, 
                UBHashedServicePassword) 
                VALUES (:terminal_id, :service_id, now(6), :amount, now(6), :trans_summary_id, :loyalty_card, :mid, 
                :user_mode, :casino_login, :casino_pwd, :casinohashed_pwd)';

            $stmt = $this->dbh->prepare($sql);

            $stmt->bindValue(':terminal_id', $terminal_id);
            $stmt->bindValue(':service_id', $service_id);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':trans_summary_id', $trans_summary_id);
            $stmt->bindValue(':loyalty_card', $loyalty_card);
            $stmt->bindValue(':mid', $mid);
            $stmt->bindValue(':user_mode', $user_mode);
            $stmt->bindValue(':casino_login', $casino_login);
            $stmt->bindValue(':casino_pwd', $casino_pwd);
            $stmt->bindValue(':casinohashed_pwd', $casinohashed_pwd);
            //CCT BEGIN added VIP
            //$stmt->bindValue(':vip_type', $vip_type);
            //CCT END added VIP
            if($stmt->execute())
            {
                try 
                {
                    $this->dbh->commit();
                    return true;
                } 
                catch(Exception $e) 
                {
                    $this->dbh->rollBack();
                    return false;
                }
            } 
            else 
                {
                $this->dbh->rollBack();
                return false;
            }
        } 
        catch (Exception $e) 
        {
            $this->dbh->rollBack();
            return false;
        }
    }
    
    public function getTransDateTime() 
    {
        $time =microtime(true);         
        $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);   
        $rawdate = new DateTime( date('Y-m-d H:i:s.'.$micro_time, $time) );    
        return $rawdate->format("Y-m-d H:i:s.u");
    }
    
    /**
     * Get Last Transaction Summary ID in terminal sessions table
     * instead of getting the max summary ID in transaction summary table
     */
    public function getLastSessSummaryID($terminalID)
    {
        $sql = 'SELECT TransactionSummaryID FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminalID);
        $this->exec($sql, $param);
        $result = $this->find();
        if(!isset($result['TransactionSummaryID']))
            return false;
        return $result['TransactionSummaryID'];
    }
    
    /**
     * Get terminal session details either UB or TB
     * @param int $terminalID
     * @return obj
     */
    public function getLastSessionDetails($terminalID)
    {
        $sql = "SELECT LoyaltyCardNumber, MID, UserMode, UBServiceLogin, UBServicePassword, 
                ServiceID, UBHashedServicePassword FROM terminalsessions
                WHERE TerminalID = :terminal_id";
        $param = array(":terminal_id"=>$terminalID);
        $this->exec($sql, $param);
        $result =  $this->findAll();
        return $result;
    }
    
    public function checkSession($ubcard, $serviceid)
    {
        $sql = "SELECT COUNT(TerminalID) AS Count FROM terminalsessions
                WHERE LoyaltyCardNumber = :loyaltycard AND ServiceID = :service_id";
        $param = array(":loyaltycard"=>$ubcard, ":service_id"=>$serviceid);
        $this->exec($sql, $param);
        $result = $this->find();
        if(!isset($result['Count']))
            return false;
        return $result['Count'];
    }
    
    public function checkCardSession($ubcard)
    {
        $sql = "SELECT COUNT(TerminalID) AS Count FROM terminalsessions
                WHERE LoyaltyCardNumber = :loyaltycard ";
        $param = array(":loyaltycard"=>$ubcard);
        $this->exec($sql, $param);
        $result = $this->find();
        if(!isset($result['Count']))
            return false;
        return $result['Count'];
    }
    
    public function getCardNumberByTerminalID($terminalID)
    {
        $sql = "SELECT LoyaltyCardNumber FROM terminalsessions WHERE TerminalID=:terminalID LIMIT 1";
        $param = array(":terminalID"=>$terminalID);
        $this->exec($sql, $param);
        $result =  $this->find();
        return $result['LoyaltyCardNumber'];
    }
    
    public function getUBServiceLoginByTerminalID($terminalID)
    {
        $sql = "SELECT UBServiceLogin FROM terminalsessions WHERE TerminalID = :terminal_id";
        $param = array(":terminal_id"=>$terminalID);
        $this->exec($sql, $param);
        $result =  $this->find();
        return isset($result['UBServiceLogin'])?$result['UBServiceLogin']:false;
    }
    
    public function isEWalletSessionByCardNumber($cardNumber)
    {
//        $sql = "SELECT COUNT(ts.LoyaltyCardNumber) as IsEwallet FROM terminalsessions ts INNER JOIN membership.members m ON ts.MID=m.MID WHERE ts.LoyaltyCardNumber=:cardNumber AND ts.ServiceID=19 AND m.IsEwallet=1";
        $sql = "SELECT COUNT(LoyaltyCardNumber) as IsEwallet FROM terminalsessions ts
                INNER JOIN membership.members m ON ts.MID=m.MID WHERE ts.LoyaltyCardNumber=:cardNumber AND ts.UserMode = 1 AND m.IsEwallet=1";
        $param = array(":cardNumber"=>$cardNumber);
        $this->exec($sql, $param);
        $result =  $this->find();
        return isset($result['IsEwallet'])?$result['IsEwallet']:false;
    }

    public function getUBCardNumberByTerminalID($terminalID)
    {
//        $sql = "SELECT LoyaltyCardNumber FROM terminalsessions WHERE TerminalID=:terminalID AND ServiceID=19 LIMIT 1";
        $sql = "SELECT LoyaltyCardNumber FROM terminalsessions WHERE TerminalID=:terminalID LIMIT 1";
        $param = array(":terminalID"=>$terminalID);
        $this->exec($sql, $param);
        $result =  $this->find();
        return $result['LoyaltyCardNumber'];
    }
    
    public function checkNumberOfServiceID($terminalID)
    {
        $checksql = "SELECT COUNT(DISTINCT ServiceID) as cnt FROM terminalsessions WHERE TerminalID=:terminalID LIMIT 1";
        $param1 = array(":terminalID"=>$terminalID);
        $this->exec($checksql, $param1);
        $result1 =  $this->find();
        return $result1['cnt'];
    }
    
    public function getEwalletTerminal($siteID)
    {
        $sql = "SELECT t.TerminalID, t.TerminalCode, ts.LoyaltyCardNumber FROM terminalsessions ts INNER JOIN membership.members m ON ts.MID=m.MID INNER JOIN terminals t ON t.TerminalID=ts.TerminalID WHERE ts.Usermode=1 AND m.IsEwallet=1 AND t.SiteID=:siteID ORDER BY t.TerminalCode";
        $param = array(':siteID'=>$siteID);
        $this->exec($sql, $param);
        $result =  $this->findAll();
        return $result;
    }
    
    public function getTerminalName($terminalID) 
    {
        $sql = "SELECT TerminalName FROM terminals WHERE TerminalID = :terminal_id";
        $command = $this->connection->createCommand($sql);
        $command->bindValues(array(':terminal_id' => $terminalID));
        $result = $command->queryRow();
        foreach ($result as $row) 
        {
            $TerminalName = $row['TerminalName'];
        }
        return $TerminalName;
    }
    
    public function checkeSAFECardSession($ubcard, $serviceid)
    {
        $sql = "SELECT TerminalID FROM terminalsessions WHERE LoyaltyCardNumber = :loyaltycard AND ServiceID=:serviceid LIMIT 1";
        $param = array(":loyaltycard"=>$ubcard, ":serviceid"=>$serviceid);
        $this->exec($sql, $param);
        $result =  $this->find();
        return $result['TerminalID'];
    }
	
	
    public function getLastBalanceBingo($terminal_id) {
        $sql = "SELECT LastBalance FROM terminalsessions WHERE TerminalID = :terminalid";
        $param = array(":terminalid" => $terminal_id);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['LastBalance'];
    }

    public function updateActiveServiceIDByTerminalID($terminal_id, $service_id, $status) {
        $sql = 'UPDATE terminalsessions SET ActiveServiceStatus =  :active_service_status, OldActiveServiceStatus = :old_active_service_status,  ActiveLastTransdateUpd = NOW(6) WHERE TerminalID = :terminal_id AND ServiceID = :service_id';
        
        $param = array(
            ':service_id' => $service_id,
            ':active_service_status' => $status,
            ':old_active_service_status' => $status,
            ':terminal_id' => $terminal_id);
        return $this->exec($sql, $param);
    }

    public function getActiveServiceStatusByTerminalID($terminal_id) {
        $sql = "SELECT ActiveServiceStatus FROM terminalsessions WHERE TerminalID = :terminalid";
        $param = array(":terminalid" => $terminal_id);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['ActiveServiceStatus'];
    }

}
