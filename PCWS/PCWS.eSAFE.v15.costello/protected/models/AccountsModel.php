<?php

class AccountsModel extends CFormModel
{
   
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    /**
     * Get AID of the <b>e-SAFE</b> Virtual Cashier in a selected site.
     * @param type $siteID
     * @return type
     */
    public function getAIDBySiteID($siteID)
    {
        $sql = "SELECT a.AID FROM accounts a INNER JOIN siteaccounts sa ON sa.AID = a.AID WHERE sa.SiteID = :siteID AND a.AccountTypeID = 17 and sa.Status = 1";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":siteID", $siteID);
        $result = $command->queryRow();
        
        return $result;
    }
    /**
     * Get AID of the <b>Genesis</b> Virtual Cashier in a selected site.
     * @param type $siteID
     * @return type
     */
    public function getAIDBySiteIDGenesis($siteID) {
        $sql = "SELECT a.AID FROM accounts a INNER JOIN siteaccounts sa ON sa.AID = a.AID WHERE sa.SiteID = :siteID AND a.AccountTypeID = 15 and sa.Status = 1";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":siteID", $siteID);
        $result = $command->queryRow();
        
        return $result;
    }
}
?>
