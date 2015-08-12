<?php

class EwalletTrans extends BaseEntity
{

    public function EwalletTrans()
    {
        $this->ConnString = "kronus";
        $this->TableName = "ewallettrans";
    }

    public function getLastTransaction($cardNumber)
    {
        $query = "SELECT et.*
                    FROM
                      $this->TableName et
                    WHERE
                      et.LoyaltyCardNumber = '$cardNumber'
                    ORDER BY
                      et.EwalletTransID DESC
                    LIMIT
                      1;";

        return parent::RunQuery($query);
    }
    
    public function getLastReloadTransaction($cardNumber)
    {
        $query = "SELECT et.*
                    FROM
                      $this->TableName et
                    WHERE
                      et.LoyaltyCardNumber = '$cardNumber' AND et.TransType = 'D'
                    ORDER BY
                      et.EwalletTransID DESC
                    LIMIT
                      1;";

        return parent::RunQuery($query);
    }
    
    //@author fdlsison
    //@date 08/10/2015
    public function getEwalletTransPerSiteByMID($MID, $dateConverted)
    {
        $query = "SELECT a.EwalletTransID, a.StartDate, a.EndDate,
                         a.SiteID,a.LoyaltyCardNumber, ROUND(IFNULL(SUM(CASE a.TransType WHEN 'D' THEN a.Amount ELSE 0 END), 0)) AS TotalDeposit,
                         ROUND(IFNULL(SUM(CASE a.TransType WHEN 'W' THEN a.Amount ELSE 0 END), 0)) AS TotalWithdrawal
                    FROM $this->TableName a WHERE a.MID = '$MID' AND a.StartDate >= '$dateConverted' GROUP BY SiteID";

        $result = parent::RunQuery($query);
        return $result;
    }

}

?>
