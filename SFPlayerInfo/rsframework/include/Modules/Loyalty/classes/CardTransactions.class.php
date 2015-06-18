<?php

/*
 * @author : owliber
 * @date : 2013-04-30
 */

class CardTransactions extends BaseEntity
{

    public function CardTransactions()
    {
        $this->ConnString = "loyalty";
        $this->TableName = "cardtransactions";
    }

    public function getLastTransaction($cardnumber)
    {
        $query = "SELECT ct.*
                    FROM
                      $this->TableName ct
                    INNER JOIN membercards mc
                    ON ct.CardID = mc.CardID
                    WHERE
                      mc.CardNumber = '$cardnumber'
                    ORDER BY
                      ct.CardTransactionID DESC
                    LIMIT
                      1;";

        return parent::RunQuery($query);
    }

    //@author fdlsison
    //@date 04/30/2015
    public function getLastReloadTransaction($cardnumber)
    {
        $query = "SELECT ct.*
                    FROM
                      $this->TableName ct
                    INNER JOIN membercards mc
                    ON ct.CardID = mc.CardID
                    WHERE
                      mc.CardNumber = '$cardnumber' AND ct.TransactionType = 'R'
                    ORDER BY
                      ct.CardTransactionID DESC
                    LIMIT
                      1;";

        return parent::RunQuery($query);
    }

}

?>
