<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 * 
 * @Updated by: Joene Floresca
 * @Date: September 3, 2014
 */

class MemberServices extends BaseEntity
{
    public function MemberServices()
    {
        $this->ConnString = 'membership';
        $this->TableName = 'membership.memberservices';
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    public function CreateCasinoAccount ($arrMemberServices)
    {
        $this->StartTransaction();
        
        try{
            $this->InsertMultiple($arrMemberServices);
            
            if(!App::HasError())
            {
               $this->CommitTransaction();
            }else{
               $this->RollBackTransaction();
            }
        }
        catch(Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
        

    }
    
    /**
     * 
     * @param int $MID - Member ID
     * @return string array of Casino services
     * @modified 09-04-2014
     * @purpose added VIPLevel column
     */
    public function getCasinoAccounts( $MID )
    {
        $query = "SELECT
                    ServiceUsername,
                    ServicePassword,
                    HashedServicePassword,
                    ServiceID,
                    UserMode,
                    isVIP,
                    VIPLevel,
                    Status
                  FROM memberservices
                  WHERE MID = $MID;";
        
        $result = parent::RunQuery($query);
                
        return $result;
    }
    
    /**
     * 
     * @param int $MID - Member ID
     * @param int $ServiceID - Service ID
     * @return string array of Casino services
     * @created 10/10/2014
     * 
     */
    public function getCasinoAccountsByMIDAndServiceID($MID, $ServiceID){
        $query = "SELECT
                    ServiceUsername,
                    ServicePassword,
                    HashedServicePassword,
                    ServiceID,
                    UserMode,
                    isVIP,
                    VIPLevel,
                    Status
                  FROM memberservices
                  WHERE MID = '$MID' AND ServiceID='$ServiceID';";
        
        $result = parent::RunQuery($query);

        return $result;
    }
    
    public function getUserBasedMemberServices( $MID )
    {
        // 0 - Terminal Based; 1 - User Based
        $query = "SELECT *
                  FROM memberservices
                  WHERE UserMode = 1 AND Status = 1
                    AND MID = $MID";
        
        $result = parent::RunQuery($query);
                
        return $result;
    }
    
    /**
     * This function is use to update the player classification.
     * @author Noel Antonio 11-25-2013
     * @param tinyint $isVip (0 - Regular, 1 - VIP)
     * @param int $mid Member Card ID
     * @return boolean
     * 
     * Updated by Joene Floresca 09-01-2014
     */
    public function changeIsVipByMid($isVip, $vipLevel, $mid)
    {
        $query = "UPDATE $this->TableName SET IsVIP = '$isVip', VIPLevel = '$vipLevel' WHERE MID = '$mid'";
        return parent::ExecuteQuery($query);
    }
    
    
    public function getMemberServiceByMID( $MID, $serviceID )
    {
        // 0 - Terminal Based; 1 - User Based
        $query = "SELECT ServiceUsername, ServicePassword, HashedServicePassword, PlayerCode 
                  FROM memberservices
                  WHERE MID = $MID AND ServiceID = $serviceID";
        
        $result = parent::RunQuery($query);
                
        return $result;
    }
    
    public function AddMemberServices($serviceid, $MID, $ServiceUsername, $ServicePassword, $HashedServicePassword,
            $UserMode, $DateCreated, $isVIP, $VIPLevel, $PlayerCode, $Status) {
        $query = "INSERT INTO memberservices (ServiceID, MID, ServiceUsername, ServicePassword, 
            HashedServicePassword, UserMode, DateCreated, isVIP, VIPLevel, PlayerCode, Status) 
            VALUES ($serviceid, $MID, '$ServiceUsername', '$ServicePassword', '$HashedServicePassword', $UserMode,
                '$DateCreated', $isVIP, $VIPLevel, '$PlayerCode', $Status)";
        $this->ExecuteQuery($query);
        return $this->AffectedRows;
    }
    
    public function UpdateMemberServices($PlayerCode, $vipLevel, $MID, $serviceID) {
        $query = "UPDATE memberservices SET VIPLevel = $vipLevel, PlayerCode = '$PlayerCode' WHERE MID = $MID AND ServiceID = $serviceID";
        parent::ExecuteQuery($query);
        return $this->AffectedRows;
    }
    
    public function UpdateMemberServicesPassword($ServicePassword, $HashedServicePassword, $PlayerCode, $MID, $serviceID) {
        $query = "UPDATE memberservices SET  ServicePassword = '$ServicePassword',
                HashedServicePassword = '$HashedServicePassword', PlayerCode = '$PlayerCode' WHERE MID = $MID AND ServiceID = $serviceID";
        parent::ExecuteQuery($query);
        return $this->AffectedRows;
    }
    
    public function CheckMemberService($MID, $ServiceID) {
        $query = "SELECT * FROM memberservices WHERE MID = $MID AND ServiceID = $ServiceID";
        $result = parent::RunQuery($query);
                
        return $result;
    }

    /**
     * @author Joene Floresca 
     * @param type int $mid
     * @return array
     * @desc Update VIPLevel
     */
    public function getVIPLevel($mid){
        $query = "SELECT VIPLevel, ServiceID FROM $this->TableName WHERE MID = '$mid'";       
        $result = parent::RunQuery($query);
        return $result;
    }
    
    /**
     * @author Joene Floresca 
     * @param type int $mid
     * @return array
     * @desc Check if VIPLevel Exist
     */
    public function checkVIPLevel($vipLevel){
        $query = "SELECT VIPLeveL FROM $this->TableName WHERE ServiceID = 19 AND VIPLevel = '$vipLevel';";       
        $result = parent::RunQuery($query);
        return $result;
    }

    
    //@author fdlsison
    //@date 09-01-2014
    //@purpose update player classification
    public function updatePlayerClassificationByMIDAndServiceID($isVIP, $vipLevel, $MID, $serviceID) {
        $query = "UPDATE memberservices
                  SET IsVIP = $isVIP, VIPLevel = $vipLevel
                  WHERE MID = $MID AND ServiceID = $serviceID";
        parent::ExecuteQuery($query);
        return $this->AffectedRows;
    }
}
?>
