<?php

class TransactionSummary extends BaseEntity
{

    public function TransactionSummary()
    {
        $this->ConnString = "kronus";
        $this->TableName = "transactionsummary";
    }

    //@author fdlsison
    //@date 04/30/2015
    public function getTransSummaryPerSiteByMID($MID)
    {
        $query = "SELECT a.TransactionsSummaryID, a.DateEnded, a.DateStarted,
                         TIMEDIFF(a.DateEnded, a.DateStarted) AS PlayingTime,
                         a.SiteID,a.LoyaltyCardNumber, ROUND(SUM(a.Deposit),2) AS TotalDeposit, ROUND(SUM(a.Reload),2) AS TotalReload,
                         ROUND(SUM(a.Withdrawal),2) AS TotalWithdrawal, a.DateStarted, a.DateEnded
                    FROM $this->TableName a INNER JOIN membership.memberinfo b ON a.MID = b.MID WHERE b.SFID = '$MID' GROUP BY SiteID";

        $result = parent::RunQuery($query);
        return $result;
    }

}

?>
