<?php

/*
* Description: Class for BanningHistory table.
* @author: aqdepliyan
* DateCreated: 2013-06-18 06:59:58PM
*/

class BanningHistory extends BaseEntity
{
    public function BanningHistory()
    {
        $this->TableName = "banninghistory";
        $this->Identity = "BanningID";
        $this->ConnString = "membership";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    public function getRemarks($MID,$status)
    {
        $query = "SELECT Remarks FROM membership.banninghistory
                        WHERE MID = ".$MID." AND Status = ".$status." 
                        ORDER BY DateCreated desc LIMIT 1";
        return parent::RunQuery($query);
    }
    
    public function getMaxBannedDate($MID)
    {
        $query = "SELECT DateCreated FROM $this->TableName
                        WHERE MID = ".$MID." AND Status = 1 
                        ORDER BY DateCreated desc LIMIT 1";
        $result = parent::RunQuery($query);
        if(!empty($result)){
         $res = $result[0]['DateCreated'];
        } else {
         $res = '';
        }
        return $res;
    }
    
    public function getBanningHistoryUsingMemCardID($MemCardID)
    {
        $query = "SELECT MemberCardID, MID, Status, DateCreated, Remarks
                            FROM membership.banninghistory
                            WHERE MemberCardID =".$MemCardID." ORDER BY DateCreated ASC";
        return parent::RunQuery($query);
    }
    
}
?>
