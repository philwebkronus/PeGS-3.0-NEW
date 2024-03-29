<?php
/**
 * queries for finance report; (transaction tracking)
 * Created By: Edson L. Perez
 * Created On : February 11, 2012
 */
include "DbHandler.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);

class RptFinance extends DBHandler{
    public function __construct($connectionString) {
        parent::__construct($connectionString);
    }
    
    //get all services
    function getallservices($sort)
    {
        $stmt = "SELECT ServiceName, ServiceID FROM ref_services WHERE Status = 1 ORDER BY $sort";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }
    
    //get transaction types
    function gettranstypes()
    {
        $stmt = "SELECT TransactionTypeID, TransactionTypeCode, Description FROM ref_transactiontype";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchAllData();
    }
    
    //SELECT all terminal based from sites to populate combo box
    function viewterminals($zsiteID)
    {
        if($zsiteID > 0)
        {
            $stmt = "SELECT TerminalID, TerminalCode FROM terminals
                     WHERE SiteID = '".$zsiteID."' ORDER BY TerminalCode ASC";
        }
        else
        {
            $stmt = "SELECT DISTINCT TerminalID, TerminalCode FROM terminals 
                     ORDER BY TerminalCode ASC";
        }
      $this->executeQuery($stmt);
      return $this->fetchAllData();
    }
    
    /**
     * Finance Transaction tracking
     */
    function showtranstracking($ztype,$zsiteID, $zterminalID, $ztranstype, $zdatefrom, $zdateto)
    {
        //if exported to excel or pdf
        if($ztype == "export")
        {
            //if terminal was selected all
            if($zterminalID == "All")
            {
                //if transaction type was selected all
                if($ztranstype == "All")
                {
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.Option2 AS LoyaltyCard,
                        t.TerminalCode as TerminalCode, tr.TransactionType, tr.ServiceID, sum(tr.Amount) AS amount, 
                        a.UserName, r.ServiceName FROM transactiondetails tr 
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN ref_services r on tr.ServiceID = r.ServiceID
                        INNER JOIN accounts a ON a.AID = tr.CreatedByAID
                        WHERE tr.SiteID IN(".$zsiteID.") AND 
                        tr.DateCreated >= ? AND tr.DateCreated <  ? AND tr.Status IN(1,4)
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID ORDER BY t.TerminalCode,tr.DateCreated Desc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                }
                else
                {
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.Option2 AS LoyaltyCard,
                        t.TerminalCode as TerminalCode, tr.TransactionType, tr.ServiceID, sum(tr.Amount) AS amount, 
                        a.UserName, r.ServiceName FROM transactiondetails tr 
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN ref_services r on tr.ServiceID = r.ServiceID
                        INNER JOIN accounts a ON a.AID = tr.CreatedByAID
                        WHERE tr.SiteID IN(".$zsiteID.") AND 
                        tr.DateCreated >= ? AND tr.DateCreated <  ? AND tr.TransactionType = ? AND tr.Status IN(1,4)
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID ORDER BY t.TerminalCode,tr.DateCreated Desc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                    $this->bindparameter(3, $ztranstype);
                }
            }
            else
            {
                if($ztranstype == "All")
                {
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID, tr.Option2 AS LoyaltyCard,
                        tr.SiteID, t.TerminalCode as TerminalCode, tr.TransactionType, tr.ServiceID, 
                        sum(tr.Amount) AS amount, a.UserName, r.ServiceName FROM transactiondetails tr 
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN accounts a ON a.AID = tr.CreatedByAID
                        INNER JOIN ref_services r on tr.ServiceID = r.ServiceID
                        WHERE tr.SiteID IN(".$zsiteID.") AND 
                        tr.DateCreated >= ? AND tr.DateCreated <  ? AND tr.TerminalID = ? AND tr.Status IN(1,4)
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID ORDER BY t.TerminalCode,tr.DateCreated Desc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                    $this->bindparameter(3, $zterminalID);
                }
                else
                {
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted, tr.ServiceID, ts.DateEnded,tr.DateCreated,tr.Option2 AS LoyaltyCard,
                        tr.TerminalID, tr.SiteID, t.TerminalCode as TerminalCode, tr.TransactionType, 
                        sum(tr.Amount) AS amount,a.UserName, r.ServiceName FROM transactiondetails tr 
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN accounts a ON a.AID = tr.CreatedByAID
                        INNER JOIN ref_services r on tr.ServiceID = r.ServiceID
                        WHERE tr.SiteID IN(".$zsiteID.") AND 
                        tr.DateCreated >= ? AND tr.DateCreated <  ? AND tr.TerminalID = ? AND tr.TransactionType = ? AND tr.Status IN(1,4)
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID ORDER BY t.TerminalCode,tr.DateCreated Desc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                    $this->bindparameter(3, $zterminalID);
                    $this->bindparameter(4, $ztranstype);
                }
            }
        }
        //for jqgrid pagination
        else
        {
            //check if terminal was selected all
            if($zterminalID == "All")
            {
                //check if transaction type was selected all
                if($ztranstype == "All")
                {
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.Option2 AS LoyaltyCard,
                        t.TerminalCode as TerminalCode, tr.TransactionType, tr.ServiceID, r.ServiceName, sum(tr.Amount) AS amount,a.UserName FROM transactiondetails tr 
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN accounts a ON a.AID = tr.CreatedByAID
                        INNER JOIN ref_services r on tr.ServiceID = r.ServiceID
                        WHERE tr.SiteID IN(".$zsiteID.") AND 
                        tr.DateCreated >= ? AND tr.DateCreated <  ? AND tr.Status IN(1,4)
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID ORDER BY t.TerminalCode,tr.DateCreated Desc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                }
                else
                {
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.Option2 AS LoyaltyCard,
                        t.TerminalCode as TerminalCode, tr.TransactionType, tr.ServiceID, r.ServiceName, sum(tr.Amount) AS amount,a.UserName FROM transactiondetails tr 
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN accounts a ON a.AID = tr.CreatedByAID
                        INNER JOIN ref_services r on tr.ServiceID = r.ServiceID
                        WHERE tr.SiteID IN(".$zsiteID.") AND 
                        tr.DateCreated >= ? AND tr.DateCreated <  ? AND tr.TransactionType = ? AND tr.Status IN(1,4)
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID ORDER BY t.TerminalCode,tr.DateCreated Desc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                    $this->bindparameter(3, $ztranstype);
                }
            }
            else
            {
                if($ztranstype == "All")
                {
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.Option2 AS LoyaltyCard,
                        t.TerminalCode as TerminalCode, tr.TransactionType, tr.ServiceID, r.ServiceName, sum(tr.Amount) AS amount,a.UserName FROM transactiondetails tr 
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN accounts a ON a.AID = tr.CreatedByAID
                        INNER JOIN ref_services r on tr.ServiceID = r.ServiceID
                        WHERE tr.SiteID IN(".$zsiteID.") AND 
                        tr.DateCreated >= ? AND tr.DateCreated <  ? AND tr.TerminalID = ? AND tr.Status IN(1,4)
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID ORDER BY t.TerminalCode,tr.DateCreated Desc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                    $this->bindparameter(3, $zterminalID);
                }
                else
                {
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted, tr.ServiceID, r.ServiceName, ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.Option2 AS LoyaltyCard,
                        t.TerminalCode as TerminalCode, tr.TransactionType, sum(tr.Amount) AS amount,a.UserName FROM transactiondetails tr 
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN accounts a ON a.AID = tr.CreatedByAID
                        INNER JOIN ref_services r on tr.ServiceID = r.ServiceID
                        WHERE tr.SiteID IN(".$zsiteID.") AND 
                        tr.DateCreated >= ? AND tr.DateCreated <  ? AND tr.TerminalID = ? AND tr.TransactionType = ? AND tr.Status IN(1,4)
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID ORDER BY t.TerminalCode,tr.DateCreated Desc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                    $this->bindparameter(3, $zterminalID);
                    $this->bindparameter(4, $ztranstype);
                }
            }
        }
        
        
        $this->execute();
        return $this->fetchAllData();
    }
    
    function showtranstrackingUB($ztype,$zsiteID, $zterminalID, $zdatefrom, $zdateto)
    {
        //if exported to excel or pdf
        if($ztype == "export")
        {
            //if terminal was selected all
            if($zterminalID == "All")
            {
                    $stmt = "SELECT ts.TransactionsSummaryID, s.SiteCode, t.TerminalCode, rs.ServiceName, ts.StartBalance, ts.EndBalance, ts.WalletReloads, IFNULL(tl.GenesisWithdrawal,0) as GenesisWithdrawal, ts.DateStarted, ts.DateEnded
                                FROM transactionsummary ts
                                    INNER JOIN sites s ON s.SiteID=ts.SiteID
                                    INNER JOIN terminals t ON t.TerminalID=ts.TerminalID
                                    INNER JOIN terminalservices tss ON tss.TerminalID=ts.TerminalID
                                    INNER JOIN ref_services rs ON rs.ServiceID=tss.ServiceID
                                    LEFT JOIN transactionsummarylogs tl ON tl.TransactionSummaryID = ts.TransactionsSummaryID
                                WHERE ts.SiteID IN(".$zsiteID.")
                                    AND ts.DateEnded >= ? 
                                    AND ts.DateEnded < ?
                                    AND tss.Status=1
                                ";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
            }
            else
            {
                    $stmt = "SELECT ts.TransactionsSummaryID, s.SiteCode, t.TerminalCode, rs.ServiceName, ts.StartBalance, ts.EndBalance, ts.WalletReloads, IFNULL(tl.GenesisWithdrawal,0) as GenesisWithdrawal, ts.DateStarted, ts.DateEnded
                                FROM transactionsummary ts
                                    INNER JOIN sites s ON s.SiteID=ts.SiteID
                                    INNER JOIN terminals t ON t.TerminalID=ts.TerminalID
                                    INNER JOIN terminalservices tss ON tss.TerminalID=ts.TerminalID
                                    INNER JOIN ref_services rs ON rs.ServiceID=tss.ServiceID 
                                    LEFT JOIN transactionsummarylogs tl ON tl.TransactionSummaryID = ts.TransactionsSummaryID
                                WHERE ts.SiteID IN(".$zsiteID.") 
                                    AND ts.DateEnded >= ?
                                    AND ts.DateEnded < ?
                                    AND ts.TerminalID = ?
                                    AND tss.Status=1
                                ";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                    $this->bindparameter(3, $zterminalID);
                }
 }
        //for jqgrid pagination
        else
        {
            //check if terminal was selected all
            if($zterminalID == "All")
            {
                   $stmt = "SELECT ts.TransactionsSummaryID, s.SiteCode, t.TerminalCode, rs.ServiceName, ts.StartBalance, ts.EndBalance, ts.WalletReloads, IFNULL(tl.GenesisWithdrawal,0) as GenesisWithdrawal, ts.DateStarted, ts.DateEnded
                                FROM transactionsummary ts
                                    INNER JOIN sites s ON s.SiteID=ts.SiteID
                                    INNER JOIN terminals t ON t.TerminalID=ts.TerminalID
                                    INNER JOIN terminalservices tss ON tss.TerminalID=ts.TerminalID
                                    INNER JOIN ref_services rs ON rs.ServiceID=tss.ServiceID
                                    LEFT JOIN transactionsummarylogs tl ON tl.TransactionSummaryID = ts.TransactionsSummaryID
                                WHERE ts.SiteID IN(".$zsiteID.")
                                    AND ts.DateEnded >= ? 
                                    AND ts.DateEnded < ?
                                    AND tss.Status=1
                                ";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                
            }
            else
            {
                    $stmt = "SELECT ts.TransactionsSummaryID, s.SiteCode, t.TerminalCode, rs.ServiceName, ts.StartBalance, ts.EndBalance, ts.WalletReloads, IFNULL(tl.GenesisWithdrawal,0) as GenesisWithdrawal, ts.DateStarted, ts.DateEnded
                                FROM transactionsummary ts
                                    INNER JOIN sites s ON s.SiteID=ts.SiteID
                                    INNER JOIN terminals t ON t.TerminalID=ts.TerminalID
                                    INNER JOIN terminalservices tss ON tss.TerminalID=ts.TerminalID
                                    INNER JOIN ref_services rs ON rs.ServiceID=tss.ServiceID 
                                    LEFT JOIN transactionsummarylogs tl ON tl.TransactionSummaryID = ts.TransactionsSummaryID
                                WHERE ts.SiteID IN(".$zsiteID.") 
                                    AND ts.DateEnded >= ?
                                    AND ts.DateEnded < ?
                                    AND ts.TerminalID = ?
                                    AND tss.Status=1
                                ";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zdatefrom);
                    $this->bindparameter(2, $zdateto);
                    $this->bindparameter(3, $zterminalID);
            }
        }
        
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
