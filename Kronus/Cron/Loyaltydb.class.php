<?php
/**
 * @Description: Class for manipulating data in Loyaltydb Database
 * @DateCreated: 2014-01-12
 * @Author: aqdepliyan
 */

class Loyaltydb {
    
    private $_connectionString;
    private $_stmt;
    public $_dbh;
    
    public function __construct( $connectionString )
    {
         $this->_connectionString = explode( ",", $connectionString );

    }
    
    public function open()
    {
            $connectionstring1 = $this->_connectionString[0];
            $connectionstring2 = $this->_connectionString[1];
            $connectionstring3 = $this->_connectionString[2];  
            $this->_dbh = new PDO( $connectionstring1, $connectionstring2, $connectionstring3);
            if($this->_dbh)
               return true;
            else
                return false;
    }
    
    public function close()
    {
        if ($this->_dbh)
            $this->_dbh= NULL;
    }

    /**
     * @Description: Get Card Number for User Based RTG Abbott Casino
     * @param int $mid
     * @return array
     */
    public function getCardNumber($mid){
        
        $query = "SELECT CardNumber FROM membercards
                            WHERE MID = :mid ";
        
        $sth = $this->_dbh->prepare($query);
        $sth->bindParam(":mid", $mid);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }

}

?>
