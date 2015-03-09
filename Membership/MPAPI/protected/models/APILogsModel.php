<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of APILogsModel
 * @date 07-18-2014
 * @author fdlsison
 */
class APILogsModel
{
    CONST API_LOGIN = 1;
    CONST API_FORGOT_PASSWORD = 2;
    CONST API_UPDATE_PROFILE = 3;
    CONST API_CHECK_POINTS = 4;
    CONST API_LIST_ITEMS = 5;
    CONST API_REDEEM_ITEMS = 6;
    CONST API_GET_PROFILE = 7;
    CONST API_REGISTER = 8;
    CONST API_GET_GENDER = 9;
    CONST API_GET_ID_PRESENTED = 10;
    CONST API_GET_NATIONALITY = 11;
    CONST API_GET_OCCUPATION = 12;
    CONST API_IS_SMOKER = 13;
    CONST API_REGISTER_MEMBER_BT = 18;
    CONST API_CHANGE_PASSWORD = 20;
    
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new APILogsModel();
        return self::$_instance;
    }
    
    
    public function insertAPIlogs($apiMethodID, $refID, $transDetails, $trackingID, $status) {
     
        
        $remoteIP = $_SERVER['REMOTE_ADDR'];
        $method = '';
        switch($apiMethodID) {
            case 1:
                $method = self::API_LOGIN;
                break;
            case 2:
                $method = self::API_FORGOT_PASSWORD;
                break;
            case 3: 
                $method = self::API_UPDATE_PROFILE;
                break;
            case 4:
                $method = self::API_CHECK_POINTS;
                break;
            case 5:
                $method = self::API_LIST_ITEMS;
                break;
            case 6:
                $method = self::API_REDEEM_ITEMS;
                break;
            case 7:
                $method = self::API_GET_PROFILE;
                break;
            case 8:
                $method = self::API_REGISTER;
                break;
            case 9:
                $method = self::API_GET_GENDER;
                break;
            case 10: 
                $method = self::API_GET_ID_PRESENTED;
                break;
            case 11:
                $method = self::API_GET_NATIONALITY;
                break;
            case 12:
                $method = self::API_GET_OCCUPATION;
                break;
            case 13:
                $method = self::API_IS_SMOKER;
                break;
            case 18:
                $method = self::API_REGISTER_MEMBER_BT;
                break;
            case 20:
                $method = self::API_CHANGE_PASSWORD;
                break;
        }
        
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'INSERT INTO apilogs(APIMethodID, ReferenceID, TransDetails, DateLastUpdated, TrackingID, RemoteIP, Status)
                    VALUES(:apiMethodID, :refID, :transDetails, NOW(6), :trackingID, :remoteIP, :status)';
            $param = array(':apiMethodID' => $apiMethodID, 
                           ':refID' => $refID,
                           ':transDetails' => $transDetails,
                           ':trackingID' => $trackingID,
                           ':remoteIP' => $remoteIP,
                           ':status' => $status);
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