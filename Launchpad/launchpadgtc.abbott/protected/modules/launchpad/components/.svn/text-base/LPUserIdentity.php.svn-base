<?php

/**
 * User identity for Launchpad
 * @package application.modules.launchpad.components
 * @author Bryan Salazar
 */
class LPUserIdentity extends CUserIdentity
{
    public $terminalCode = null;
    public $terminalID = null;
    public $serviceID = null;
    public $siteID = null;
    public $transSummaryID = null;
    public $terminalPassword = null;
    private static $_instance = null;
    
    public function __construct() {}
    
    /**
     *
     * @return LPUserIdentity 
     */
    public static function app()
    {
        if(self::$_instance == null)
            self::$_instance = new LPUserIdentity();
        return self::$_instance;
    }

    /**
     * 
     * @return bool 
     */
    public function authenticate() 
    {
        if(!isset($_GET['terminalCode']))
            return false;
        
        $this->terminalCode = $this->username = $this->password = $_GET['terminalCode'];
        
        if(($rows=LPTerminalSessions::model()->isLogin($this->terminalCode))) {
            $this->serviceID = $rows['ServiceID'];
            $this->terminalID = $rows['TerminalID'];
            $this->siteID = $rows['SiteID'];
            $this->transSummaryID = $rows['TransactionSummaryID'];
            $this->errorCode = self::ERROR_NONE;
        } else {
            $this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
        }
        return !$this->errorCode;
    }
}
