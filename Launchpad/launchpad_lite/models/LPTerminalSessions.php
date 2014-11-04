<?php

/**
 * Description of LPTerminalSessions
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */

require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPTerminalSessions extends LPModel
{
    /**
     *
     * @var LPTerminalSessions 
     */
    private static $_instance = null;
    public $_pdoconn;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db1"]["connectionString"];
        $username = LPConfig::app()->params["db1"]["username"];
        $password = LPConfig::app()->params["db1"]["password"];
        
        $this->_pdoconn = $this->setpdoconn($connstring,$username,$password);
    }
    
    /**
     * Get instance of LPTerminalSessions
     * @return LPTerminalSessions 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPTerminalSessions();
        return self::$_instance;
    }

    /**
     * Check if terminal code has active session vip or not vip
     * @param string $terminalCode
     * @return bool|int false if no row affected else number of row affected
     */
    public function isLogin($terminalCode)
    {
        $vip = 'VIP';
        $tcodevip = $terminalCode.$vip;
        $query = "SELECT t.TerminalID, t.TerminalCode, ts.TransactionSummaryID, t.SiteID, 
                ts.ServiceID, ts.LoyaltyCardNumber, ts.UserMode, ts.UBServiceLogin,
                ts.UBServicePassword, ts.UBHashedServicePassword, rs.Code FROM terminals t 
                INNER JOIN terminalsessions ts ON ts.TerminalID = t.TerminalID
                INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                WHERE t.TerminalCode IN (:terminalCode,:withvip)";
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminalCode',$terminalCode);
        $rqst->bindParam(':withvip',$tcodevip);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $$rqst = $this->_pdoconn->prepare($query);
            
            if(stripos($terminalCode, $vip) === false) { // case-insensitive
                $terminalCode = $terminalCode.$vip;
            } else {
                $terminalCode = preg_replace('/vip/i', '', $terminalCode);
            }
            
            $rqst->bindParam(':terminalCode',$terminalCode);
            $rqst->bindParam(':withvip',$terminalCode);
            $rqst->execute();
            $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        } 

        return $result;
    }
    
    /**
     * Get current casino by terminal code
     * @param string $terminalCode
     * @return bool|array false if no row affected
     */
    public function getCurrentCasino($terminalCode) 
    {
        $tcodevip = $terminalCode."VIP";
        $query = 'SELECT ts.ServiceID FROM terminals t ' . 
            'INNER JOIN terminalsessions ts ON ts.TerminalID = t.TerminalID ' . 
            'WHERE t.TerminalCode IN (:terminalCode,:withvip)';
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminalCode',$terminalCode);
        $rqst->bindParam(':withvip',$tcodevip);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);

        if(!$result) {
            $this->logerror("File: launchpad.models.LPTerminalSessions, Message: Can't get current casino");
        }
            
        return $result[0];
    }
    
    /**
     * Get ServiceID and Alias
     * @param type $terminalID
     * @return array array('Alias'=>'','ServiceID'=>'') 
     */
    public function getCurrentCasinoByTerminalID($terminalID)
    {
        $query = 'SELECT rs.Alias, rs.ServiceID FROM terminalsessions ts ' . 
            'INNER JOIN ref_services rs ON rs.ServiceID = ts.ServiceID ' . 
            'WHERE ts.TerminalID = :terminalID';
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminalID',$terminalID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPTerminalSessions, Message: Can't get current casino");
        }
        return $result;
    }
    
    /**
     *
     * @param type $terminalID
     * @param type $serviceID
     * @return bool|int false if no row affected else number of row affected 
     */
    public function updateCurrentServiceID($terminalID,$serviceID, $password, $hashedpassword, $servicelogin, $usermode) 
    {
        $query = 'UPDATE terminalsessions SET ServiceID = :serviceID, UserMode = :usermode, 
                            UBServicePassword = :servicepassword, UBHashedServicePassword = :hashedservicepassword,
                            UBServiceLogin = :servicelogin
                            WHERE TerminalID = :terminalID';
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminalID',$terminalID);
        $rqst->bindParam(':usermode',$usermode);
        $rqst->bindParam(':servicepassword',$password);
        $rqst->bindParam(':hashedservicepassword',$hashedpassword);
        $rqst->bindParam(':servicelogin',$servicelogin);
        $rqst->bindParam(':serviceID',$serviceID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPTerminalSessions, Message: failed to update servicetransferhistory");
        }
        
        return $result;
    }
    
    public function getTerminalPassword($terminalID, $serviceID)
    {
        $query = "SELECT t.ServicePassword, t.HashedServicePassword FROM terminalservices t 
                  WHERE t.TerminalID = :terminal_id AND ServiceID = :service_id 
                  AND t.Status = 1";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminal_id',$terminalID);
        $rqst->bindParam(':service_id',$serviceID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPTerminalSessions, Message: Can't get casino password");
        }
        return $result;
    }
    
    public function getTransactionSummaryID($terminalID) {
        $query = "SELECT TransactionSummaryID FROM terminalsessions WHERE TerminalID = :terminal_id AND DateEnded = 0";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminal_id',$terminalID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPTerminalSessions, Message: Can't get session ID");
        }
        return $result['TransactionSummaryID'];
    }
    
    public function getTerminalDetails($terminalID) {
        $query = "SELECT LoyaltyCardNumber, MID, UserMode FROM terminalsessions WHERE TerminalID = :terminal_id AND DateEnded = 0";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminal_id',$terminalID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPTerminalSessions, Message: Can't get session ID");
        }

        return $result;
    }
    
    /**
     * Get UB Card ID
     * @param string $terminalCode
     * @return MID
     */
    public function getMID($terminalCode) 
    {
        $query = 'SELECT ts.MID FROM terminals t
                            INNER JOIN terminalsessions ts ON ts.TerminalID = t.TerminalID
                            WHERE t.TerminalCode = :terminalCode';
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminalCode',$terminalCode);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPTerminalSessions, Message: Can't get UB Card ID");
        }
            
        return $result["MID"];
    }
}