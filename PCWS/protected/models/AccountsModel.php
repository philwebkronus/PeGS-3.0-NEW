<?php

class AccountsModel extends CFormModel
{
   
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function getAIDBySiteID($siteID)
    {
        $sql = "SELECT a.AID FROM accounts a INNER JOIN siteaccounts sa ON sa.AID = a.AID WHERE sa.SiteID = :siteID AND a.AccountTypeID = 17";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":siteID", $siteID);
        $result = $command->queryRow();
        
        return $result;
    }
    
    
}
?>
