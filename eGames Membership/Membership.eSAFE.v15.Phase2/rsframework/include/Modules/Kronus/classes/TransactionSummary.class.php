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
            WHERE SiteID = $site AND DateStarted >= '$startdate' AND DateStarted < '$enddate'";
        
    
        $result = parent::RunQuery($query);
        return $result;
    }
    
    /*
     * Description: Get transactionsummarydetails per card number
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-07-09
     */
    public function getTransSummaryperCard($site, $startdate, $enddate, $loyaltycardnumber)
    {
        $query = "SELECT TransactionsSummaryID, LoyaltyCardNumber, Deposit, Reload, Withdrawal 
            FROM transactionsummary 
            WHERE SiteID = $site AND DateStarted >= '$startdate' AND DateStarted < '$enddate' 
            AND LoyaltyCardNumber = '$loyaltycardnumber'";
        
    
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
        $query = "SELECT COUNT(DISTINCT(LoyaltyCardNumber)) AS count FROM transactionsummary WHERE SiteID = $site 
            AND DateStarted >= '$startdate' AND DateStarted < '$enddate'";

        $result = parent::RunQuery($query);
        return $result;
    }    
    
    /*
     * Description: Get distinct card number
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-07-09
     */
    public function getDistinctCard($site, $start, $end){
        $query = "SELECT DISTINCT(LoyaltyCardNumber)
            FROM transactionsummary 
            WHERE SiteID = $site AND DateStarted >= '$start' 
            AND DateStarted < '$end' AND LoyaltyCardNumber IS NOT NULL";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    /*
     * Description: Get Transaction Summary by Member ID
     * @author: Junjun S. Hernandez
     * result: object array
     * DateCreated: 2013-07-01
     */
    public function getTransSummaryByMID($MID, $startdate, $enddate)
    {
        $query = "SELECT TransactionsSummaryID, DateEnded, DateStarted,
                         TIMEDIFF(DateEnded, DateStarted) AS PlayingTime,
                         SiteID, LoyaltyCardNumber, Deposit, Reload,
                         Withdrawal, DateStarted, DateEnded 
                    FROM transactionsummary WHERE MID = $MID 
            AND DateStarted >= '$startdate' AND DateStarted < '$enddate'";
    
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function getAmountReload($transactionssummaryid)
    {
        $query = "SELECT ts.TransactionsSummaryID, ts.Deposit, td.Amount, ts.Withdrawal, td.TransactionType, td.PaymentType, td.TransactionReferenceID
                         FROM transactionsummary ts
                         INNER JOIN transactiondetails td
                         ON ts.TransactionsSummaryID = td.TransactionSummaryID
                         WHERE  ts.TransactionsSummaryID = $transactionssummaryid";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function getOptions($transactionreferenceid)
    {
        $query = "SELECT Option1 From transactionrequestlogs WHERE TransactionReferenceID = $transactionreferenceid";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    /*
     * Description: Get the number of both Active and Banned Account Status
     * @author: Junjun S. Hernandez
     * DateCreated: June 27, 2013 01:07:35PM
     */
    public function getAllMemberCards()
    {
        
        $query = "SELECT MID, LoyaltyCardNumber FROM transactionsummary ORDER BY LoyaltyCardNumber ASC";
        
        return parent::RunQuery($query);
    }
    
}
?>
