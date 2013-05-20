<?php

/**
 * Description of LPTerminals
 * @package application.modules.launchpad.models
 * @author Bryan Salazar, elperez
 */
class LPTerminals extends LPModel
{
    private static $_instance;
    
    private function __construct() 
    {
        $this->_connection = LPDB::app();
    }
    
    /**
     * Get instance of LPTerminals
     * @return LPTerminals 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPTerminals();
        return self::$_instance;
    }
    
    /**
     * Get TerminalID by TerminalCode
     * @param type $terminalCode
     * @return bool|array false if no row affected
     */
    public function getTerminalID($terminalCode)
    {
        $query = "SELECT TerminalID FROM terminals WHERE TerminalCode = :terminalCode";
        $command=$this->_connection->createCommand($query);
        $row=$command->queryRow(true, array(':terminalCode'=>$terminalCode));
        if(!$row) {
            $this->log($command->getText().$command->getBound(), 'launchpad.models.LPTerminals');
            throw new CHttpException(404,"Can't get terminal ID");
        }
            
        return $row;
    }
}