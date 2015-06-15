<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 */

class MemberServicesQA extends BaseEntity
{
    public function MemberServicesQA()
    {
        $this->ConnString = 'membershipqa';
        $this->TableName='memberservices';
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
                    Status
                  FROM memberservices
                  WHERE MID = $MID;";
        
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
    
  
}
?>
