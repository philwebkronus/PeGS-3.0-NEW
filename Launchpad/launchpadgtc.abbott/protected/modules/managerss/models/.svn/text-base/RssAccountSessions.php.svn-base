<?php

/**
 * Model for accountsessions table
 * @package application.modules.managerss.models
 * @author Bryan Salazar
 */
class RssAccountSessions extends RssModel
{
    /**
     * @var RssAccountSessions 
     */
    private static $_instance = null;
    
    /**
     * Constructor is set to private. User model() to get instance of RssAccountSessions
     */
    private function __construct() 
    {
        $this->_connection = RssDB::app();
    }    
    
    /**
     * Get instance of RssAccountSessions
     * @return RssAccountSessions 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new RssAccountSessions();
        return self::$_instance;
    }      
    
    /**
     * Get session id by AID
     * @param int $aid
     * @return type 
     */
    public function getSessionIDByAID($aid)
    {
        $query = 'SELECT SessionID FROM accountsessions ' . 
            'WHERE AID = :aid';
        $command = $this->_connection->createCommand($query);
        $row = $command->queryRow(true,array(':aid'=>$aid));
        if(!isset($row['SessionID']) || !$row || !$row['SessionID'])
            return false;
        return $row;
    }
    
    /**
     *
     * @param int $aid
     * @param string $sessionID
     * @return boolean|int false if failed or int number of row affected 
     */
    public function insert($aid,$sessionID)
    {
        $query = 'INSERT INTO accountsessions (AID,SessionID) VALUES (:aid,:sessionID)';
        $data = array(
            ':aid'=>$aid,
            ':sessionID'=>$sessionID
        );
        $command = $this->_connection->createCommand($query);
        $n = $command->execute($data);
        if(!$n) {
            $this->log($command->getText().$command->getBound()." failed to insert to accountsessions", 'managerss.models.RssAccountSessions');
        }
        return $n;
    }
    
    /**
     *
     * @param int $aid
     * @param string $sessionID
     * @return boolean|int false if failed or int number of row affected  
     */
    public function updateSessionIDByAID($aid,$sessionID)
    {
        $query = 'UPDATE accountsessions SET SessionID = :sessionID ' . 
            'WHERE AID = :aid';
        $data = array(
            ':aid'=>$aid,
            ':sessionID'=>$sessionID
        );
        $command = $this->_connection->createCommand($query);
        $n = $command->execute($data);
        if(!$n) {
            $this->log($command->getText().$command->getBound()." failed to update to SessionID", 'managerss.models.RssAccountSessions');
        }
        return $n;
    }
    
    public function login($aid)
    {
        $row = $this->getSessionIDByAID($aid);
        $sessionID = $row['SessionID'];
        // check if session id not exist
        if(!$sessionID) {
            $this->insert($aid, Yii::app()->session->getSessionID());
            
        // check if current session id is not equal in database    
        } elseif($sessionID) {
            $this->delete($aid);
            $this->insert($aid, Yii::app()->session->getSessionID());
        }
    }
    
    /**
     * @param int $aid
     * @return boolean|int false if failed or int number of row affected 
     */
    public function delete($aid)
    {
        $query = 'DELETE FROM accountsessions WHERE AID = :aid';
        $command = $this->_connection->createCommand($query);
        $n = $command->execute(array(':aid'=>$aid));
        if(!$n) {
            $this->log($command->getText().$command->getBound()." failed to delete in accountsessions", 'managerss.models.RssAccountSessions');
        }
        return $n;
    }
}
