<?php
/**
 * Terminal Sessions Module
 * @author JunJun S. Hernandez
 * @date March 03, 2014
 * @copyright (c) 2014, Philweb Corporation
 */
class TerminalSessions extends BaseEntity
{
    function TerminalSessions()
    {
        $this->TableName = "terminalsessions";
        $this->ConnString = "kronus";
        $this->Identity = "AID";
    }
    public function isSessionExists($MID)
    {
        $query = "SELECT COUNT(TerminalID) ctrTerminalID FROM $this->TableName
                  WHERE MID = $MID";
        return parent::RunQuery($query);
    }
}
?>
