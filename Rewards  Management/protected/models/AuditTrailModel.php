<?php
/**
 * Audit Trail Model
 * @author Mark Kenneth Esguerra
 * @date November 25, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
class AuditTrailModel extends CFormModel
{
    /**
     * Log Event in Audit Trail
     * @param int $auditfunctionID AuditTrailFunctionID
     * @param string $transdetails Transaction details
     * @param array $info SessionID and AID
     * @return array TransCode
     * @author Mark Kenneth Esguerra
     * @date November 25, 2013
     * @DateModified November 26, 2013 (aqdepliyan)
     */
    public function logEvent($auditfunctionID, $transdetails, $info)
    {
        
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        
        //$remoteip = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        
        $remoteip = $this->get_client_ip();
        
        if (is_array($info) && count($info) > 0)
        {
            $aid        = $info['AID'];
            $sessionID  = $info['SessionID'];
        }
        else
        {
            $aid = null;
            $sessionID = null;
        }
        $query = "INSERT INTO audittrail (
                                SessionID, 
                                AID, 
                                TransDetails, 
                                TransDateTime, 
                                RemoteIP, 
                                AuditTrailFunctionID 
                ) VALUES (
                    :sessionID, 
                    :aid, 
                    :transdetails, 
                    NOW_USEC(), 
                    :remoteip, 
                    :auditfunctionID
                )";
        $sql = $connection->createCommand($query);
        $sql->bindParam(":sessionID", $sessionID);
        $sql->bindParam(":aid", $aid);
        $sql->bindParam(":transdetails", $transdetails);
        $sql->bindParam(":remoteip", $remoteip);
        $sql->bindParam(":auditfunctionID", $auditfunctionID);
        
        $result = $sql->execute();
        
        if ($result > 0)
        {
            try
            {
                $pdo->commit();
                return array('TransCode' => 1);
            }
            catch (CDbException $e)
            {
                $pdo->rollback();
                return array('TransCode' => 0, 'TransMsg' => $e->getMessage());
            }
        }
        else
        {
            $pdo->rollback();
            return array('TransCode' => 0, 'TransMsg' => $e->getMessage());
        }
    }
    
    /**
     * Get client IP address
     * @author Edson Perez
     * @date 2013-11-27
     * @link http://stackoverflow.com/questions/15699101/get-client-ip-address-using-php
     * @return string client ip address
     */
    private function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
          $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
          $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
          $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip; 
   }
}
?>
