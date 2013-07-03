<?php

class TransactionSummary extends BaseEntity
{
    public function TransactionSummary()
    {
        $this->ConnString = "kronus";
        $this->TableName = "transactionsummary";
    }
    
    /*
     * Description: Get Transaction Summary using site and start to end date
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-07-01
     */
    public function getTransSummary($site, $startdate, $enddate)
    {
        $query = "SELECT TransactionsSummaryID, LoyaltyCardNumber, Deposit, Reload, Withdrawal 
            FROM transactionsummary 
            WHERE SiteID = $site AND DateStarted >= '$startdate' AND DateEnded < '$enddate'";
        
    
        $result = parent::RunQuery($query);
        return $result;
    }
    
    /*
     * Description: Get total no of transaction summary
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-07-01
     */
    public function countTransSummary($site, $startdate, $enddate)
    {
        $query = "SELECT Count(LoyaltyCardNumber) AS count FROM transactionsummary WHERE SiteID = $site 
            AND DateStarted >= '$startdate' AND DateEnded <= '$enddate'";

        $result = parent::RunQuery($query);
        return $result;
    }
    
}
?>
