<?php
/**
 * Terminal Sessions Module
 * @author JunJun S. Hernandez
 * @date March 03, 2014
 * @copyright (c) 2014, Philweb Corporation
 * 
 * @Updated by Joene Floresca
 * @date September 03, 2014
 * @copyright (c) 2014, Philweb Corporation
 * @desc Added isSessionExistCardNum Function
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
    
    public function isSessionExistsCard($card)
    {
        $query = "SELECT TerminalID FROM $this->TableName
                  WHERE LoyaltyCardNumber = '$card'";
        return parent::RunQuery($query);
    }
    
    //@author fdlsison
    //@date 09-04-2014
    //@purpose check if card has an existing terminal session
    public function checkForTerminalSession($ubCard) {
        $query = "SELECT COUNT(LoyaltyCardNumber) ctrTerminalSessions
                  FROM $this->TableName
                  WHERE LoyaltyCardNumber = '$ubCard'";
        return parent::RunQuery($query);
    }
}
?>
