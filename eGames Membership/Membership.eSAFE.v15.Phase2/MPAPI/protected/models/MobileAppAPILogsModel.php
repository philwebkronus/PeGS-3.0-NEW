<?php

/**
 * Description of MobileAppAPILogsModel
 * @date 08-24-2015
 * @author fdlsison
 */
class MobileAppAPILogsModel
{
    CONST API_LIST_PROMOS = 1;
    CONST API_LIST_LOCATIONS = 2;
       
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db7;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new MobileAppAPILogsModel();
        return self::$_instance;
    }   
    
    public function insertAPIlogs($apiMethodID, $refID, $transDetails, $trackingID, $status) {  
        $remoteIP = $_SERVER['REMOTE_ADDR'];
        $method = '';
        switch($apiMethodID) {
            case 1:
                $method = self::API_LIST_PROMOS;
                break;
            case 2:
                $method = self::API_LIST_LOCATIONS;
                break;
        }
        
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'INSERT INTO tblapilogs(fldAPIMethodID, fldReferenceID, fldTransDetails, fldDateLastUpdated, fldTrackingID, fldRemoteIP, fldStatus)
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
?>