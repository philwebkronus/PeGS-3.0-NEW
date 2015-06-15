<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-06
 * Company: Philweb
 * ***************** */

class SiteAccounts extends BaseEntity
{

    function SiteAccounts()
    {

        $this->ConnString = "kronus";
        $this->TableName = "siteaccounts";
        $this->Identity = "SiteAccountID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }

    function getSiteIDByAID($aid)
    {
        $query = "Select SiteID from $this->TableName where AID = $aid and Status = 1;";
        return parent::RunQuery($query);
    }
}

?>
