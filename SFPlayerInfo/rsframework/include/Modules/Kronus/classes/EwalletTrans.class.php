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

}

?>
