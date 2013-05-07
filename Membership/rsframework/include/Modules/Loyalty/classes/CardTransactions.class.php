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
                      ct.TransactionDate DESC
                    LIMIT
                      1;";
        
        return parent::RunQuery($query);
    }
}
?>
