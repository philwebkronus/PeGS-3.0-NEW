<?php

/*
 * @author : owliber
 * @date : 2013-06-04
 */

class AccountTypes extends BaseEntity
{
    const MEMBER = 'Member';
        
    public function AccountTypes()
    {
        $this->ConnString = "membership";
        $this->TableName = "ref_accounttypes";
        $this->Identity = "AccountTypeID";
    }
    
    public function GetAccountTypeNameByID($accounttypeid)
    {
        $where = " WHERE AccountTypeID = '$accounttypeid'";
        $result = parent::SelectByWhere($where);
        return $result[0]['Name'];
    }
    
    public function GetAccountTypeIDByName($accountName)
    {
        $where = " WHERE Name = '$accountName'";
        $result = parent::SelectByWhere($where);
        return $result[0]['AccountTypeID'];
    }
}
?>
