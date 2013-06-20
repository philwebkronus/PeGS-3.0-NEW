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
    
}
?>
