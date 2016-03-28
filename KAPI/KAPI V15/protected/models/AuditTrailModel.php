<?php
/**
 * Audit trail Model
 * @author Mark Kenneth Esguerra
 * @date June 5, 2014
 */
class AuditTrailModel extends CFormModel
{
    public $connection;
    
    public function __construct()
    {
        $this->connection = Yii::app()->db;
    }
    
    public function logToAuditTrail($aid, $transdetails)
    {
        $pdo = $this->connection->beginTransaction();
        
        $auditfunction  = RefAuditFunctionsModel::REMOVE_EGM_SESSION; //audit function
        $remoteIP       = $_SERVER['REMOTE_ADDR']; //remote IP
        try
        {
            $sql = "INSERT INTO audittrail (
                            AID, 
                            TransDetails, 
                            TransDateTime, 
                            DateCreated, 
                            RemoteIP, 
                            AuditTrailFunctionID
                    ) VALUES (
                            :aid, 
                            :transdetails, 
                            NOW(), 
                            :datecreated, 
                            :remoteIP, 
                            :auditfunction
                    )";
            $command = $this->connection->createCommand($sql);
            $command->bindValues(array(":aid" => $aid, 
                                       ":transdetails" => $transdetails, 
                                       ":datecreated" => date('Y-m-d'), 
                                       ":remoteIP" => $remoteIP, 
                                       ":auditfunction" => $auditfunction));
            $result = $command->execute();
            if ($result > 0)
            {
                try
                {
                    $pdo->commit();
                    return array('TransCode' => 0, 'TransMsg' => 'Successfully Logged to Audit Trail');
                }
                catch(CDbException $e)
                {
                    $pdo->rollback();
                    return array('TransCode' => 0, 'TransMsg' => 'Failed to Logged in Audit Trail');
                }
            }
            else
            {
                $pdo->rollback();
                return array('TransCode' => 0, 'TransMsg' => 'Failed to Logged in Audit Trail');
            }
        }
        catch (CDbException $e)
        {
            $pdo->rollback();
            return array('TransCode' => 0, 'TransMsg' => 'Failed to Logged in Audit Trail');  
        }
    }
}
?>
