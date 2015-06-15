<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 */

class TempMemberServices extends BaseEntity
{
    public function TempMemberServices()
    {
        $this->ConnString = 'tempmembership';
        $this->TableName = 'memberservices';
    }
    
    public function getTempCasinoServices( $MID )
    {
        $query = "SELECT ServiceID,
                         MID,
                         ServiceUsername,
                         ServicePassword,
                         HashedServicePassword,
                         UserMode,
                         isVIP,
                         Status
                    FROM memberservices
                    WHERE MID = $MID";
        
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
}
?>
