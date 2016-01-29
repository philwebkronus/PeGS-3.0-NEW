<?php

/**
 * Description of LPRefAllowedEwalletUser
 * @package launchpad.models
 * @author aqdepliyan
 */

require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPRefAllowedEwalletUser extends LPModel
{
    /**
     *
     * @var LPRefAllowedEwalletUser
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
     * Get instance of LPRefAllowedEwalletUser
     * @return LPRefAllowedEwalletUser
     * 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPRefAllowedEwalletUser();
        return self::$_instance;
    }
    
    
    /**
     * @Description: Identify if card is in allowed to convert ewallet list
     * @param int $MID
     * @return int $result[0]
     */
    public function isAllowed($MID)
    {
        
        $query = "SELECT COUNT(*) as Count FROM ref_ewalletalloweduser "
                . "WHERE MID = :MID";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':MID',$MID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        if(!$result) {
            $this->logerror("File: launchpad.models.LPRefAllowedEwalletUser, Message: Can't get list of allowed card to convert in e-wallet");
        }
        
        return $result[0]; 
    }

}

?>