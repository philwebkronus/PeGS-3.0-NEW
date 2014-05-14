<?php

/**
 * @description of AccountsModel
 * @author JunJun S. Hernandez <jshernandez@philweb.com.ph>
 * @datecreated 04/23/14
 */
class APILogsModel {

    public static $_instance = null;
    public $_connection;

    //API Methods found in stackermanagement.ref_apimethod declared as constant
    //instead of live fetching from database to avoid data traffic
    CONST API_METHOD_LOGSTACKERSESSION = 1;
    CONST API_METHOD_GETSTACKERBATCHID = 2;
    CONST API_METHOD_LOGSTACKERTRANSACTION = 3;
    CONST API_METHOD_VERIFYLOGSTACKERTRANSACTION = 4;
    CONST API_METHOD_ADDSTACKERINFO = 5;
    CONST API_METHOD_GETSTACKERINFO = 6;
    CONST API_METHOD_CANCELDEPOSIT = 7;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new APILogsModel();
        return self::$_instance;
    }

    public function insertIntoAPILogs($APIMethodID, $transdetails, $trackingID = '') {
        $beginTrans = $this->_connection->beginTransaction();
        $status = 0; //default status upon insert is 0;
        $remoteIP = $_SERVER['REMOTE_ADDR'];
        switch ($APIMethodID) {
            CASE self::API_METHOD_LOGSTACKERSESSION:
                $sql = "INSERT INTO apilogs(APIMethodID, TransDetails, DateLastUpdated, RemoteIP, Status) VALUES (:api_method_id, :trans_details, NOW(6), :remote_ip, :status)";
                $param = array(':api_method_id'=>$APIMethodID, ':trans_details' => $transdetails, ':remote_ip' => $remoteIP, ':status' => $status);
                break;
            CASE self::API_METHOD_GETSTACKERBATCHID:
                $sql = "INSERT INTO apilogs(APIMethodID, TransDetails, DateLastUpdated, RemoteIP, Status) VALUES (:api_method_id, :trans_details, NOW(6), :remote_ip, :status)";
                $param = array(':api_method_id'=>$APIMethodID, ':trans_details' => $transdetails, ':remote_ip' => $remoteIP, ':status' => $status);
                break;
            CASE self::API_METHOD_LOGSTACKERTRANSACTION:
                $sql = "INSERT INTO apilogs(APIMethodID, TransDetails, DateLastUpdated, TrackingID, RemoteIP, Status) VALUES (:api_method_id, :trans_details, NOW(6), :tracking_id, :remote_ip, :status)";
                $param = array(':api_method_id'=>$APIMethodID, ':trans_details' => $transdetails, ':remote_ip' => $remoteIP,  ':tracking_id' => $trackingID,':status' => $status);
                break;
            CASE self::API_METHOD_VERIFYLOGSTACKERTRANSACTION:
                $sql = "INSERT INTO apilogs(APIMethodID, TransDetails, DateLastUpdated, RemoteIP, Status) VALUES (:api_method_id, :trans_details, NOW(6), :remote_ip, :status)";
                $param = array(':api_method_id'=>$APIMethodID, ':trans_details' => $transdetails, ':remote_ip' => $remoteIP, ':status' => $status);
                break;
            CASE self::API_METHOD_ADDSTACKERINFO:
                $sql = "INSERT INTO apilogs(APIMethodID, TransDetails, DateLastUpdated, RemoteIP, Status) VALUES (:api_method_id, :trans_details, NOW(6), :remote_ip, :status)";
                $param = array(':api_method_id'=>$APIMethodID, ':trans_details' => $transdetails, ':remote_ip' => $remoteIP, ':status' => $status);
                break;
            CASE self::API_METHOD_GETSTACKERINFO:
                $sql = "INSERT INTO apilogs(APIMethodID, TransDetails, DateLastUpdated, RemoteIP, Status) VALUES (:api_method_id, :trans_details, NOW(6), :remote_ip, :status)";
                $param = array(':api_method_id'=>$APIMethodID, ':trans_details' => $transdetails, ':remote_ip' => $remoteIP, ':status' => $status);
                break;
            CASE self::API_METHOD_CANCELDEPOSIT:
                $sql = "INSERT INTO apilogs(APIMethodID, TransDetails, DateLastUpdated, TrackingID, RemoteIP, Status) VALUES (:api_method_id, :trans_details, NOW(6), :tracking_id, :remote_ip, :status)";
                $param = array(':api_method_id'=>$APIMethodID, ':trans_details' => $transdetails, ':remote_ip' => $remoteIP,  ':tracking_id' => $trackingID,':status' => $status);
                break;
            default :
                $sql = "INSERT INTO apilogs(APIMethodID, TransDetails, DateLastUpdated, RemoteIP, Status) VALUES (:api_method_id, :trans_details, NOW(6), :remote_ip, :status)";
                $param = array(':api_method_id'=>$APIMethodID, ':trans_details' => $transdetails, ':remote_ip' => $remoteIP, ':status' => $status);
                break;
        }
        $command = $this->_connection->createCommand($sql);
        $command->bindValues($param);
        $command->execute();
        $logID = $this->_connection->getLastInsertID();
        try {
            $beginTrans->commit();
            return $logID;
        } catch (PDOException $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    public function updateAPILogs($APIMethodID, $logID, $status, $referenceID = '') {
        // $referenceID = row id of stackersession (LogStackerSession) [or] row id of stackersummary (LogStackerTransaction) [or] row id of stackerinfo (AddStackerInfo)
        $beginTrans = $this->_connection->beginTransaction();
        switch ($APIMethodID) {
            CASE self::API_METHOD_LOGSTACKERSESSION:
                $sql = "UPDATE apilogs SET DateLastUpdated = NOW(6), Status = :status, ReferenceID = :reference_id WHERE LogID = :log_id";
                $param = array(':status' => $status, ':reference_id' => $referenceID, ':log_id'=>$logID);
                break;
            CASE self::API_METHOD_GETSTACKERBATCHID:
                $sql = "UPDATE apilogs SET DateLastUpdated = NOW(6), Status = :status WHERE LogID = :log_id";
                $param = array(':status' => $status, ':log_id'=>$logID);
                break;
            CASE self::API_METHOD_LOGSTACKERTRANSACTION:
                $sql = "UPDATE apilogs SET DateLastUpdated = NOW(6), Status = :status, ReferenceID = :reference_id WHERE LogID = :log_id";
                $param = array(':status' => $status, ':reference_id' => $referenceID, ':log_id'=>$logID);
                break;
            CASE self::API_METHOD_VERIFYLOGSTACKERTRANSACTION:
                $sql = "UPDATE apilogs SET DateLastUpdated = NOW(6), Status = :status WHERE LogID = :log_id";
                $param = array(':status' => $status, ':log_id'=>$logID);
                break;
            CASE self::API_METHOD_ADDSTACKERINFO:
                $sql = "UPDATE apilogs SET DateLastUpdated = NOW(6), Status = :status, ReferenceID = :reference_id WHERE LogID = :log_id";
                $param = array(':status' => $status, ':reference_id' => $referenceID, ':log_id'=>$logID);
                break;
            CASE self::API_METHOD_GETSTACKERINFO:
                $sql = "UPDATE apilogs SET DateLastUpdated = NOW(6), Status = :status WHERE LogID = :log_id";
                $param = array(':status' => $status, ':log_id'=>$logID);
                break;
            CASE self::API_METHOD_CANCELDEPOSIT:
                $sql = "UPDATE apilogs SET DateLastUpdated = NOW(6), Status = :status WHERE LogID = :log_id";
                $param = array(':status' => $status, ':log_id'=>$logID);
                break;
            default :
                $sql = "UPDATE apilogs SET DateLastUpdated = NOW(6), Status = :status WHERE LogID = :log_id";
                $param = array(':status' => $status, ':log_id'=>$logID);
                break;
        }
        $command = $this->_connection->createCommand($sql);
        $command->bindValues($param);
        $command->execute();
        try {
            $beginTrans->commit();
            return true;
        } catch (PDOException $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

}
