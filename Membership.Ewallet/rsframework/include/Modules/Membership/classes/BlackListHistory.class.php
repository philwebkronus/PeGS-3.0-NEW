<?php
/**
 * BlackList History Module
 * @author Mark Kenneth Esguerra
 * @date November 12, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
class BlackListHistory extends BaseEntity
{
    function BlackListHistory()
    {
        $this->TableName = "blacklisthistory";
        $this->Identity = "BlackListHistoryID";
        $this->ConnString = "membership";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    /**
     * Get the latest Remarks of the blacklisted player 
     * @param int $blacklistID ID of the blacklisted player 
     * @param string Remarks
     * @author Mark Kenneth Esguerra
     * @date November 12, 2013
     */
    public function getRemarks($blacklistID)
    {
        $query = "SELECT Remarks FROM $this->TableName 
                  WHERE BlackListedID = $blacklistID AND Status = 1 
                  ORDER BY DateCreated DESC LIMIT 1";
        $result = parent::RunQuery($query);
        
        return $result[0]['Remarks'];
    }
    /**
     * Get the blacklisted's entire history
     * @param int $blacklistID ID of the blacklisted player
     * @return array Array of recorded history
     */
    public function getAllBlackListedHist($blacklistID)
    {
        $query = "SELECT BlackListHistoryID, DATE_FORMAT(DateCreated, '%Y-%m-%d %h:%i:%s') as DateCreated, 
                  Remarks, CreatedByAID FROM $this->TableName 
                  WHERE BlackListedID = $blacklistID 
                  ORDER BY DateCreated DESC";
        $result = parent::RunQuery($query);
        
        return $result;
    }
}
?>
