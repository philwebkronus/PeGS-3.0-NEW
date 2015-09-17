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
     * @author Mark Kenneth Esguerra
     * @date June 30, 2015
     * @param type $blacklistID
     * @return type
     */
    public function getRemarksSP($blacklistID) {
        $query = "CALL membership.sp_select_data(1, 3, 8, '$blacklistID,1', 'Remarks', @OUTRetCode, @OUTRetMessage, @OUTfldListRet)";
        $result = parent::RunQuery($query);
        $arr_result = array();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $exp = explode(";", $row['OUTfldListRet']);
                $arr_result[] = array('Remarks' => $exp[0]);
            }
        }
        return $arr_result[0]['Remarks'];
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
    public function getAllBlackListedHistSP($blacklistID)
    {
        $query = "CALL sp_select_data_v2(1, 3, 9, $blacklistID, 'BlackListHistoryID,DateCreated,Remarks,CreatedByAID', @OUTRetCode,@OUTRetMessage, @OUTfldListRet);";
        $result = parent::RunQuery($query);
        $arr_result = array();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $exp = explode(";", $row['OUTfldListRet']);
                $arr_result[] = array('BlackListHistoryID' => $exp[0], 
                                      'DateCreated' => $exp[1], 
                                      'Remarks' => $exp[2], 
                                      'CreatedByAID' => $exp[3]);
            }
        }
        return $arr_result;
    }
}
?>
