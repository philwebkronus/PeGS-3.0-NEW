<?php

/**
 * Description of RptSupervisor
 *
 * Created By: Edson L. Perez
 * Date Created: October 13, 2011
 * 
 */

include 'DbHandler.class.php';
ini_set('display_errors',true);
ini_set('log_errors',true);

class RptSupervisor extends DBHandler
{
    public function __construct($connectionString) 
    {
        parent::__construct($connectionString);
    }
    
    //view all accounts --> 
    function viewsitebyowner($zaid)
    {
        $stmt = "SELECT DISTINCT b.SiteID FROM accounts AS a INNER JOIN siteaccounts AS b 
                 ON a.AID = b.AID WHERE a.AID = '".$zaid."'";
        $this->executeQuery($stmt); 
        return $this->fetchData();
    }
    
    function getsitecashier($zsiteID)
    {
        $stmt = "SELECT sa.AID FROM siteaccounts sa
                 INNER JOIN accounts acct ON sa.AID = acct.AID
                 WHERE sa.SiteID = ? AND acct.AccountTypeID = 4";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchAllData();
    }
    
    function viewgrosshold($zdatefrom, $zdateto, $zsiteID)
    {
        $stmt = "select tr.DateCreated, tr.TerminalID,tr.SiteID, tr.CreatedByAID, 
                     tr.TransactionType, tr.Amount,a.UserName, ad.Name from transactiondetails tr
                     FORCE INDEX(IX_transactiondetails_DateCreated)
                     inner join accounts a on a.AID = tr.CreatedByAID
                     inner join accountdetails ad on ad.AID = tr.CreatedByAID
                     where tr.SiteID IN(".$zsiteID.") AND 
                     tr.DateCreated >= ? and tr.DateCreated <  ? and tr.Status IN(1,4)
                     order by tr.CreatedByAID ASC";
        
        $this->prepare($stmt);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        return $this->fetchAllData();
    }
    
    //paginate site transactions
    function paginatetransaction($zdetails, $zstart, $zlimit)
    {
        $res = array();
        foreach($zdetails as $value) 
        {
           $res[] = $value;
        }
        $res = array_slice($res, $zstart, $zlimit);
        return $res;
    }
}

?>
