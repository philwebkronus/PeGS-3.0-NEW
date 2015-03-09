<?php

/**
 * @date 6-30-2014
 * 
 * @author fdlsison
 */

class BlackListsModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model() {
        if(self::$_instance == null)
            self::$_instance = new BlackListsModel();
        return self::$_instance;
    }
    
    //@purpose check if member is blacklisted
    public function checkIfBlackListed($firstname, $lastname, $birthdate, $process, $blacklistID = NULL) {
        if($process == 1) { //Add
            $sql = 'SELECT Status, BlackListedID
                    FROM blacklists 
                    WHERE LastName = :LastName AND FirstName = :FirstName AND 
                          BirthDate = :Birthdate';
            $param = array(':LastName' => $lastname, ':FirstName' => $firstname, ':Birthdate' => $birthdate);
        }
        else if($process == 2) {
            $sql = 'SELECT Status, BlackListedID
                    FROM blacklists 
                    WHERE LastName = :LastName AND FirstName = :FirstName AND 
                          BirthDate = :Birthdate AND BlackListedID <> :BlackListID';
            $param = array(':LastName' => $lastname, ':FirstName' => $firstname, ':Birthdate' => $birthdate, ':BlackListID' => $blacklistID);
        }
        else if($process == 3) { //Checking in registration
            $sql = 'SELECT COUNT(BlackListedID) as Count
                    FROM blacklists
                    WHERE LastName = :LastName AND FirstName = :FirstName AND 
                          BirthDate = :Birthdate AND Status = 1';
            $param = array(':LastName' => $lastname, ':FirstName' => $firstname, ':Birthdate' => $birthdate);
        }
        
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;    
    }   
}