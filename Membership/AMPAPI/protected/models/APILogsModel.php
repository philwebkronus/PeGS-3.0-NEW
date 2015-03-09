<?php
/**
 * Description of APILogsModel
 * @date 07-18-2014
 * @author fdlsison
 */
class APILogsModel
{
    CONST API_LOGIN = 1;
    CONST API_FORGOT_PASSWORD = 2;
    CONST API_REGISTER_MEMBER = 3;
    CONST API_UPDATE_PROFILE = 4;
    CONST API_GET_PROFILE = 5;
    CONST API_CHECK_POINTS = 6;
    CONST API_LIST_ITEMS = 7;
    CONST API_REDEEM_ITEMS = 8;
    CONST API_AUTHENTICATE_SESSION = 9;
    CONST API_GET_ACTIVE_SESSION= 10;
    CONST API_GET_GENDER = 11;
    CONST API_GET_ID_PRESENTED = 12;
    CONST API_GET_NATIONALITY = 13;
    CONST API_GET_OCCUPATION = 14;
    CONST API_GET_IS_SMOKER = 15;
    CONST API_LOGOUT = 16;
    CONST API_GET_REFERRER = 17;
    CONST API_GET_REGION = 18;
    CONST API_GET_CITY = 19;
    CONST API_CHANGE_PASSWORD = 20;

    
    
    
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new APILogsModel();
        return self::$_instance;
    }
    
    
    public function insertAPIlogs($apiMethodID, $refID, $transDetails, $trackingID, $status) {
        $startTrans = $this->_connection->beginTransaction();
        
        $remoteIP = $_SERVER['REMOTE_ADDR'];
       
        try {
            $sql = 'INSERT INTO apilogs(APIMethodID, ReferenceID, Transdetails, DateLastUpdated, TrackingID, RemoteIP, Status)
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