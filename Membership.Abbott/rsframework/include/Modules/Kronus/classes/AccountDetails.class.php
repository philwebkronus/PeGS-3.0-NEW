<?php
/**
 * Account Details Module
 * @author Mark Kenneth Esguerra
 * @date November 13, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
class AccountDetails extends BaseEntity
{
    function AccountDetails()
    {
        $this->TableName = "accountdetails";
        $this->ConnString = "kronus";
        $this->Identity = "AID";
    }
    public function selectNameByAID ($AID)
    {
        $query = "SELECT Name FROM $this->TableName
                  WHERE AID = $AID";
        return parent::RunQuery($query);
    }
}
?>
