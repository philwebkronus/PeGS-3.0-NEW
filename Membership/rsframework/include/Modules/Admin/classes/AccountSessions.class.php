<?php

/*
 * @author : owliber
 * @date : 2013-06-14
 */

class AccountSessions extends BaseEntity
{
    public function AccountSessions()
    {
        $this->ConnString = "membership";
        $this->TableName = "accountsessions";
        $this->Identity = "AccountSessionID";
    }
    
}
?>
