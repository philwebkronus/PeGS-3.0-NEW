<?php
/**
 * EGM Sessions
 * @author Joene Floresca
 * @date September 04, 2014
 * @copyright (c) 2013, Philweb Corporation
 */
class EgmSessions extends BaseEntity
{
    function EgmSessions()
    {
        $this->TableName = "egmsessions";
        $this->ConnString = "kronus";
        $this->Identity = "EGMSessionID";
    }
    /**
     * @author Joene Floresca 
     * @param type int $mid
     * @return array
     * @desc Check if Card has an existing egm session
     */
    public function checkEgmSession($mid)
    {
        $query = "SELECT MID FROM $this->TableName
                  WHERE MID = '$mid'";
        return parent::RunQuery($query);
    }
    
    //@author fdlsison
    //@date 09-05-2014
    //@purpose check if card has an existing egm session
    public function checkForEGMSession($MID) {
        $query = "SELECT COUNT(MID) ctrEGMSessions
                  FROM $this->TableName
                  WHERE MID = '$MID'";
        return parent::RunQuery($query);
    }
}

?>
