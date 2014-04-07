<?php
/**
 * @Description: Class for manipulating data in Membership Database
 * @DateCreated: 2014-02-12
 * @Author: aqdepliyan
 */

class Membership {
    
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
     * @Description: Get UB Credentials for RTG Abbott
     * @param int $serviceid
     * @param int $mid
     * @return array
     */
    public function getUBCredentials($serviceid, $mid){
        
        $query = "SELECT ms.ServiceUsername, mi.FirstName, mi.LastName 
                            FROM membership.memberservices ms
                            INNER JOIN membership.memberinfo mi ON ms.MID = mi.MID
                            WHERE ms.ServiceID = :serviceid 
                            AND ms.MID = :mid ";
        
        $sth = $this->_dbh->prepare($query);
        $sth->bindParam(":serviceid", $serviceid);
        $sth->bindParam(":mid", $mid);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }

}

?>
