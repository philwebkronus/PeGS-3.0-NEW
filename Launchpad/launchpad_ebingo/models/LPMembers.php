<?php

/**
 * Description of LPMembers
 * @package application.modules.launchpad.models
 * @author jefloresca
 */

require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPMembers extends LPModel
{
    /**
     *
     * @var LPMembers
     */
    private static $_instance;
    public $_pdoconn;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db2"]["connectionString"];
        $username = LPConfig::app()->params["db2"]["username"];
        $password = LPConfig::app()->params["db2"]["password"];
        $this->_pdoconn = $this->setpdoconn($connstring,$username,$password);
    }
    
    /**
     * Get instance of LPMembers
     * @return LPMembers
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPMembers();
        return self::$_instance;
    }
    
    
    /**
     * @Description: Get UB Card PIN
     * @param int $mid
     * @param int $serviceid
     * @return int $PIN
     * 
     */
    public function getPIN($MID) 
    {
        $query = 'SELECT PIN,DatePINLastChange,PINLoginAttemps FROM members WHERE MID = :MID';
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':MID',$MID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPMembers, Message: Can't get PIN");
        }
            
        return $result[0];
    }

    public function checkUBCard($MID){
        
        $query = "SELECT m.IsEwallet "
                . " FROM members m"
                . " WHERE m.MID='$MID'";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
         if(!$result) {
            $this->logerror("File: launchpad.models.LPMembers, Message: Can't check UB Card");
        }
        
        return $result[0];
    }
    
    public function checkPassword($mid,$pass)
    {
        $query = "SELECT COUNT(*) as Count"
                . " FROM members"
                . " WHERE MID=$mid"
                . " AND Password = '$pass'";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPMembers, Message: Can't get and match password");
        }
        
        $result = $result[0]["Count"];
        
        return $result;
    }
    
    public function checkPin($mid){
        
        $MID = $mid;
      
        $query = "SELECT PIN "
                . "FROM members "
                . "WHERE MID=$MID";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':MID',$MID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        if(!$result) {
            $this->logerror("File: launchpad.models.LPMembers, Message: Can't check PIN");
        }
        
        return $result; 
    }
    
    public function updatePIN($mid,$newP){
        
        $query = 'UPDATE members SET PIN=:NEWPIN,DatePINLastChange=now(6)'
                . 'WHERE MID=:MID';
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':NEWPIN',$newP);
        $rqst->bindParam(':MID',$mid);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        if(!$result) {
            $this->logerror("File: launchpad.models.LPMembers, Message: Update Pin Failed");
            return $result;
        }else{
            
        return $result;
        }
    }
    
    public function updateAttempts($attempts,$mid){
        
        $query = 'UPDATE members SET PINLoginAttemps=:ATTEMPTS '
                . 'WHERE MID='.$mid;
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':ATTEMPTS',$attempts);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        if(!$result) {
            $this->logerror("File: launchpad.models.LPMembers, Message: Update Pin Attempts Failed");
        }else{
            
        $result="Update Successful";
            
        return $result;
        }
        
        
    }
    
     public function getPinMaxVal($mid){
        
        $MID = $mid;
      
        
        $query = "SELECT DatePINLastChange "
                . "FROM members "
                . "WHERE MID=$MID";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':MID',$MID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        if(!$result) {
            $this->logerror("File: launchpad.models.LPMembers, Message: Can't get date of last PIN update");
        }
        
        return $result; 
        
    }
    
    public function isEwallet($mid)
    {
        $query = "SELECT IsEwallet "
                . "FROM members "
                . "WHERE MID=:mid";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':mid',$mid);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        if(!$result) {
            $this->logerror("File: launchpad.models.LPMembers, Message: Can't get IsEwallet");
        }
        
        return $result[0]; 
    }
    
    public function tagEwallet($mid,$pin)
    {
        $query = "UPDATE members "
                . "SET IsEwallet = 1,PIN =:pin,DatePINLastChange=now(6),DateMigrated=now(6)"
                . "WHERE MID=:mid";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':mid',$mid);
        $rqst->bindParam(':pin',$pin);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        if(!$result) {
            $this->logerror("File: launchpad.models.LPMembers, Message: Failed to update IsEwallet");
        }
        
        return $result[0]; 
        
    }
    
}

?>
