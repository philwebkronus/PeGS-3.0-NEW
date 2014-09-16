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
        $query = "SELECT VIPLevelID, Name FROM $this->TableName  WHERE status = 1";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    function getAllColumn()
    {
        $query = "SELECT VIPLevelID, Name, Status FROM $this->TableName";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    function countRecords()
    {
        $query = "SELECT COUNT(*) FROM membership.ref_viplevel";
        $result = parent::RunQuery($query);
        return $result; 
    }
    
    function checkIfExist($id, $description)
    {
        $query = "SELECT * FROM $this->TableName WHERE VIPLevelID = '$id' AND Name = '$description'";
        $result = parent::RunQuery($query);
        return $result;   
    }
    
    function addVipLevel($VIPLevelID,$Name)
    {
        App::LoadModuleClass("Membership","AuditTrail");
        App::LoadModuleClass("Membership","AuditFunctions");
        $_Log = new AuditTrail();

        $AID = $_SESSION['userinfo']['AID'];
        $sessionID = $_SESSION['userinfo']['SessionID'];
        $this->StartTransaction();
        
        $query = "INSERT INTO $this->TableName (VIPLevelID, Name, Status)"
                ."VALUES ('$VIPLevelID','$Name',1)";
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
    
    function updateVipStatus($vipLevelID,$name,$status)
    {
        $query = "UPDATE $this->TableName SET Status = '$status' WHERE VIPLevelID = '$vipLevelID' AND Name = '$name'";
        parent::ExecuteQuery($query);
        return $this->AffectedRows;
    }
}
?>
