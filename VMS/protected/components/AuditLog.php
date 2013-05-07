<?php

/**
 * @author owliber
 * @date Oct 2, 2012
 * @filename AuditLog.php
 * 
 */

class AuditLog extends CFormModel
{
    CONST API_VERIFY_VOUCHER = 'verifyVoucher';
    CONST API_USE_VOUCHER = 'useVoucher';
    CONST API_GENERATE_VOUCHER = 'generateVoucher';
    CONST API_LOG_STACKER = 'logStacker';
    CONST API_VERIFY_STACKER = 'verifyStacker';
    CONST API_VERIFY_TRACKINGID = 'verifyTrackingID';
    CONST API_STACKER_SESSION = 'stackerSession';
    
    /**
     * 
     * @param int $AID
     * @param int $auditFunctionID
     * @param string $transDetails
     */
    public static function logTransactions($auditFunctionID,$transDetails=NULL)
    {
        $conn = Yii::app()->db;
            
        $remoteIP = $_SERVER['REMOTE_ADDR'];
        
        //Replace with now_usec in production
        //$dateCreated = date('Y-m-d H:i:s')  . substr((string)microtime(), 1, 7);
        
        $AID = Yii::app()->user->getId();
        
        $transMsg = AuditLog::logMessage($auditFunctionID) . " " . $transDetails;
        $query = "INSERT INTO audittrail (AID,AuditTrailFunctionID,TransDetails,TransDateTime,RemoteIP)
                  VALUE (:AID,:auditFunctionID,:transMsg,now_usec(),:remoteIP)";

        $sql = $conn->createCommand($query);  
        $sql->bindValues(array(
                    ":AID"=>$AID,
                    ":auditFunctionID"=>$auditFunctionID,
                    ":transMsg"=>$transMsg,
                    ":remoteIP"=>$remoteIP,
        ));
        $sql->execute();
       
    }
    
    public static function logMessage($auditFunctionID)
    {
        
        $conn = Yii::app()->db;
        
        $query = "SELECT AuditFunctionName FROM ref_auditfunctions
                  WHERE AuditTrailFunctionID =:auditFunctionID";
        
        $sql = $conn->createCommand($query);
        $sql->bindParam(":auditFunctionID", $auditFunctionID);
        $result = $sql->queryRow();
        
        return $result["AuditFunctionName"];
    }
    
    public static function logAPITransactions($APIMethod,$source,$transDetails,$referenceID,$trackingID,$status)
    {
        $conn = Yii::app()->db;
            
        switch ($APIMethod)
        {
            case 1:
                $method = self::API_VERIFY_VOUCHER;
                break;
            case 2:
                $method = self::API_USE_VOUCHER;
                break;
            case 3: 
                $method = self::API_GENERATE_VOUCHER;
                break;
            case 4:
                $method = self::API_LOG_STACKER;
                break;
            case 5:
                $method = self::API_VERIFY_STACKER;
                break;
            case 6:
                $method = self::API_STACKER_SESSION;
                break;
            case 7:
                $method = self::API_VERIFY_TRACKINGID;
                break;
        }
        
        $remoteIP = $_SERVER['REMOTE_ADDR'];
                       
        $query = "INSERT INTO apilogs (APIMethod,Source,TransDetails,TransDateTime,ReferenceID,TrackingID,RemoteIP,Status)
                  VALUE (:APIMethod,:source,:transDetails,now_usec(),:referenceID,:trackingID,:remoteIP,:status)";

        $sql = $conn->createCommand($query);    
        $sql->bindValues(array(
                        ":APIMethod"=>$method,
                        ":source"=>$source,
                        ":transDetails"=>$transDetails,
                        ":referenceID"=>$referenceID,
                        ":trackingID"=>$trackingID,
                        ":remoteIP"=>$remoteIP,
                        ":status"=>$status));
        
        $sql->execute();
        
    }
    
}
?>
