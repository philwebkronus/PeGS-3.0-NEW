<?php
/**
 * @Description: Class for manipulating data in Membership Database
 * @DateCreated: 2014-02-12
 * @Author: aqdepliyan
 */

class Membership {
    
    private $_connectionString;
    private $_stmt;
    public $_dbh;
    
    public function __construct( $connectionString )
    {
         $this->_connectionString = explode( ",", $connectionString );

    }
    
    public function open()
    {
            $connectionstring1 = $this->_connectionString[0];
            $connectionstring2 = $this->_connectionString[1];
            $connectionstring3 = $this->_connectionString[2];  
            $this->_dbh = new PDO( $connectionstring1, $connectionstring2, $connectionstring3);
            if($this->_dbh)
               return true;
            else
                return false;
    }
    
    public function close()
    {
        if ($this->_dbh)
            $this->_dbh= NULL;
    }

    /**
     * @Description: Get UB Credentials for RTG Abbott
     * @param int $serviceid
     * @param int $mid
     * @return array
     */
    public function getUBCredentials($serviceid, $mid){
        
        $query = "SELECT ms.ServiceUsername, mi.FirstName, mi.LastName 
                            FROM membership.memberservices ms
                            INNER JOIN membership.memberinfo mi ON ms.MID = mi.MID
                            WHERE ms.ServiceID = :serviceid 
                            AND ms.MID = :mid ";
        
        $sth = $this->_dbh->prepare($query);
        $sth->bindParam(":serviceid", $serviceid);
        $sth->bindParam(":mid", $mid);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }
    public function checkLockedPINs($max_attempts)
    {
        $query = "SELECT MID, DatePINLocked 
                  FROM membership.members 
                  WHERE PINLoginAttemps >= :max_attempts";
        $sth = $this->_dbh->prepare($query);   
        $sth->bindParam(":max_attempts", $max_attempts);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        return $result;
    }
    /**
     * Reset PIN Login Attempts per MID
     * @param type $MID
     * @return type
     * @author Mark Kenneth Esguerra
     * @date Febraury 13, 2015
     */
    public function resetPINLoginAttempts($MID)
    {
        $this->_dbh->beginTransaction();
        
        try
        {
            $query = "UPDATE membership.members SET PINLoginAttemps = 0 
                      WHERE MID = :mid";
            $sth = $this->_dbh->prepare($query);
            $sth->bindParam(":mid", $MID);
            if ($sth->execute())
            {
                try
                {
                    $this->_dbh->commit();
                    return array('ErrorCode' => 0);
                }
                catch (PDOException $e)
                {
                    $this->_dbh->rollBack();
                    return array('ErrorCode' => 1, 'ErrorMsg' => $e->getMessage());
                }
            }
            else
            {
                $this->_dbh->rollBack();
                return array('ErrorCode' => 1);
            }
        }
        catch (PDOException $e)
        {
            $this->_dbh->rollBack();
            return array('ErrorCode' => 1, 'ErrorMsg' => $e->getMessage());
        }
    }
    /**
     * Get player name SP
     * @param type $MID
     * @return type
     * @author mge
     */
    public function getPlayerName($MID) {
        $query = "CALL membership.sp_select_data(1, 1, 0, $MID, 'FirstName,MiddleName,LastName,MID', @ResultCode, @ResultMsg, @ResultField)";
        $sth = $this->_dbh->prepare($query);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        $exp = explode(";", $result['OUTfldListRet']);
        return array(0 => array('FirstName' => $exp[0], 
                                'MiddleName' => $exp[1], 
                                'LastName' => $exp[2], 
                                'MID' => $exp[3]));
    }
}

?>
