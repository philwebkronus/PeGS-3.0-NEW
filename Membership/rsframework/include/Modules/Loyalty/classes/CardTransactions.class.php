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
    
    public function getLastTransaction( $cardnumber )
    {
        $query = "SELECT ct.*
                    FROM
                      cardtransactions ct
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
        
    public function getTransactions($cardnumber, $start, $end)
    {
        
        $query = "SELECT ct.SiteID, ct.CardID, ct.Amount, ct.TransactionType, ct.TerminalLogin, 
                   DATE_FORMAT(ct.TransactionDate, '%M %d, %Y %H:%i:%s.%f') AS TransactionDate
                    FROM
                      cardtransactions ct
                    INNER JOIN membercards mc
                    ON ct.CardID = mc.CardID
                    WHERE
                      mc.CardNumber = '$cardnumber'
                    ORDER BY ct.CardTransactionID DESC
                    LIMIT $start, $end;";

        return parent::RunQuery($query);
    }
        
    public function getTransactionCount($cardnumber)
    {
        $query = "SELECT count(ct.CardTransactionID) as Count
                    FROM
                      cardtransactions ct
                    INNER JOIN membercards mc
                    ON ct.CardID = mc.CardID
                    WHERE
                      mc.CardNumber = '$cardnumber'";
        $result = parent::RunQuery($query);
        return $result[0]['Count'];
    }
}
?>
