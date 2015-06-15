<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AuthenticateSessionModel
 *
 * @author jdlachica
 * @date 07/23/2014
 */
class AuthenticateSessionModel {

    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }

    public function authenticateCredentials($Username, $Password){
        $sql = 'SELECT COUNT(`AID`) as Count,`AID`, `Username`, `Password`, `Status` FROM `accounts` WHERE `Username`=:Username AND `Password`=:Password AND `Status`=:Status AND `UserAccessTypeID` = 1 LIMIT 1';
        $param = array(':Username'=>$Username,':Password'=>$Password, ':Status'=>1);

        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }

     public function authenticateUserNameCredentials($Username){
        $sql = 'SELECT COUNT(`AID`) as Count FROM `accounts` WHERE `Username`=:Username AND `Status`=:Status LIMIT 1';
        $param = array(':Username'=>$Username,':Status'=>1);

        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }

    public function authenticateSession($AID){
        $sql = 'SELECT COUNT(`AID`) as Count, `AID`, `SessionID` FROM accountsessions WHERE `AID`=:AID';
        $param = array(':AID'=>$AID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }

    public function getTPSessionID($AID){
        $sql = 'SELECT COUNT(`AID`) as Count,`SessionID` FROM accountsessions WHERE `AID`=:AID';
        $param = array(':AID'=>$AID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }



    public function insertTPSessionID($AID, $SessionID){

        $startTrans = $this->_connection->beginTransaction();
        try {
            $sql = "INSERT INTO accountsessions(AID,SessionID,DateCreated) VALUES (:AID,:SessionID, NOW(6))";
            $param = array(':AID'=>$AID, ':SessionID'=>$SessionID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    public function updateTPSessionID($AID, $SessionID){

        $startTrans = $this->_connection->beginTransaction();

        try {
            $sql = 'UPDATE accountsessions SET `SessionID`=:SessionID,`DateCreated`=NOW(6) WHERE `AID`=:AID';
            $param = array(':SessionID'=>$SessionID,':AID'=>$AID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }

        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

}
