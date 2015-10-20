<?php

/*
 * @author: Joene Floresca
 * @date: 2014-08-29
 */
?>
<?php

class RefVip extends BaseEntity
{
    function RefVip()
    {
        $this->TableName = "ref_viplevel";
        $this->ConnString = "membership";
        $this->Identity = "VIPLevelID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    function getVipName()
    {
        $query = "SELECT VIPLevelID, Name FROM $this->TableName  WHERE status = 1 ORDER BY ServiceID ASC";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    function getVipNameByServiceId($serviceid)
    {
        $query = "SELECT VIPLevelID, Name FROM $this->TableName  WHERE status = 1 AND ServiceID = '$serviceid'";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    function getAllColumn()
    {
//        $query = "SELECT VIPLevelID, Name, ServiceID, Status FROM $this->TableName";
        $query = "SELECT mr.VIPLevelID, mr.Name, mr.ServiceID, mr.Status, nr.ServiceName FROM membership.ref_viplevel mr INNER JOIN npos.ref_services nr ON mr.ServiceID = nr.ServiceID;";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    function countRecords()
    {
        $query = "SELECT COUNT(*) FROM membership.ref_viplevel";
        $result = parent::RunQuery($query);
        return $result; 
    }
    
    function checkIfExist($id, $description,  $serviceID)
    {
        $query = "SELECT * FROM $this->TableName WHERE VIPLevelID = '$id' AND Name = '$description' AND ServiceID = '$serviceID'";
        $result = parent::RunQuery($query);
        return $result;   
    }
    
    function checkIfVipNameExist($description,  $serviceID)
    {
        $query = "SELECT * FROM $this->TableName WHERE Name = '$description' AND ServiceID = '$serviceID'";
        $result = parent::RunQuery($query);
        return $result;   
    }
    
    function addVipLevel($VIPLevelID, $Name, $serviceID)
    {
        App::LoadModuleClass("Membership","AuditTrail");
        App::LoadModuleClass("Membership","AuditFunctions");
        $_Log = new AuditTrail();

        $AID = $_SESSION['userinfo']['AID'];
        $sessionID = $_SESSION['userinfo']['SessionID'];
        $this->StartTransaction();
        
        $query = "INSERT INTO $this->TableName (VIPLevelID, Name, ServiceID, Status)"
                ."VALUES ('$VIPLevelID','$Name',$serviceID,1)";
        $result = parent::ExecuteQuery($query);
        
        if ($result)
        {  
            try
            {
                $this->CommitTransaction();
                //$_Log->logEvent(AuditFunctions::ADD_VIP_LEVEL, $VIPLevelID." ".$Name." :successful", array('ID'=>$AID, 'SessionID'=>$sessionID));
                return array('TransCode' => 1,
                             'TransMsg' => 'The new VIP Level was successfully added.');
            }
            catch (Exception $e)
            {
                $this->RollBackTransaction();
                return array('TransCode' => 0,
                             'TransMsg' => $e->getMessage());
            }
        }
        else
        {
            $this->RollBackTransaction();
            return array('TransCode' => 0, 
                         'TransMsg' => 'An error occured while inserting to database');
        }
    }
    
    function updateVipStatus($vipLevelID,$name,$serviceID,$status)
    {
        $query = "UPDATE $this->TableName SET Status = '$status' WHERE VIPLevelID = '$vipLevelID' AND Name = '$name' AND ServiceID = '$serviceID'";
        parent::ExecuteQuery($query);
        return $this->AffectedRows;
    }
}
?>
