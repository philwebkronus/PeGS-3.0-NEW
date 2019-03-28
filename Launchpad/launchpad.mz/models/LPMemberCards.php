<?php

/**
 * Description of LPMemberCards
 * @package application.modules.launchpad.models
 * @author aqdepliyan
 */

require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPMemberCards extends LPModel
{
    /**
     *
     * @var LPMemberCards
     */
    private static $_instance;
    public $_pdoconn;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db3"]["connectionString"];
        $username = LPConfig::app()->params["db3"]["username"];
        $password = LPConfig::app()->params["db3"]["password"];
        $this->_pdoconn = $this->setpdoconn($connstring,$username,$password);
    }
    
    /**
     * Get instance of LPMemberCards
     * @return LPMemberCards
     * 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPMemberCards();
        return self::$_instance;
    }
    
    public function getCardStatus($CardNumber){
        
        $query = "SELECT MID,Status "
                . " FROM membercards "
                . " WHERE CardNumber='$CardNumber'";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPMemberCards, Message: Can't get UB Card MID and Status");
        }
        return $result[0];
    }

}

?>