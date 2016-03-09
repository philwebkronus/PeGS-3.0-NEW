<?php

/**
 * Created By: Edson L. Perez
 * Purpose: CLASS For PAGCOR Access
 * Created On: January 02, 2012
 */

include "DbHandler.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);

class PagcorManagement extends DBHandler
{
    public $page;
    public $total;
    public $records;
    public $rows = array();
   
    public function __construct($connectionString) 
    {
        parent::__construct($connectionString);
    }
    
    //E-City Transaction Tracking: get transactiondetails data
    function gettransactiondetails($zsiteID,$zterminalID, $zdatefrom, $zdateto, $zsummaryID, $ztranstype, $zstart, $zlimit, $zsort, $zdirection)
    {
        //check if request is excel / pdf export
        if($zstart == null && $zlimit == null)
        {
            //check if specific transaction type(D,R,W) was SELECTed
            if($ztranstype <> 'All')
            {
                //check if site was SELECTed all
                if($zsiteID > 0)
                {
                    $stmt = "SELECT tr.TransactionReferenceID, tr.TransactionSummaryID, tr.SiteID, tr.TerminalID, tr.Option2 AS LoyaltyCard,
                             tr.TransactionType, tr.Amount, tr.DateCreated, tr.ServiceID, r.ServiceName, tr.Status, a.UserName 
                             FROM transactiondetails tr 
                             INNER JOIN accounts a on tr.CreatedByAID = a.AID
                             INNER JOIN ref_services r on tr.ServiceID = r.ServiceID
                             WHERE tr.SiteID = ? AND tr.DateCreated >= ? 
                             AND tr.DateCreated < ? AND TransactionType = ? ";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    //$this->bindparameter(2, $zterminalID);
                    $this->bindparameter(2, $zdatefrom);
                    $this->bindparameter(3, $zdateto);
                    $this->bindparameter(4, $ztranstype);
                }
            }
            else
            {
                //check if site was SELECTed all
                if($zsiteID > 0)
                {
                    $stmt = "SELECT tr.TransactionReferenceID, tr.TransactionSummaryID, tr.SiteID, tr.TerminalID, tr.Option2 AS LoyaltyCard,
                             tr.TransactionType, tr.Amount, tr.DateCreated, tr.ServiceID, r.ServiceName, tr.Status, a.UserName 
                             FROM transactiondetails tr 
                             INNER JOIN accounts a on tr.CreatedByAID = a.AID
                             INNER JOIN ref_services r on tr.ServiceID = r.ServiceID 
                             WHERE tr.SiteID = ? AND tr.DateCreated >= ? AND tr.DateCreated < ?";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    //$this->bindparameter(2, $zterminalID);
                    $this->bindparameter(2, $zdatefrom);
                    $this->bindparameter(3, $zdateto);
                }
            }
        }
        //jqgrid pagination
        else
        {
            //check if specific transaction type(D,R,W) was SELECTed
            if($ztranstype <> 'All')
            {
                //if summary ID was SELECTed on the grid, execute;
                if($zsummaryID > 0)
                {
                    $stmt = "SELECT tr.TransactionReferenceID, tr.TransactionSummaryID, tr.SiteID, tr.TerminalID, tr.Option2 AS LoyaltyCard,
                         tr.TransactionType, tr.Amount, tr.DateCreated, tr.ServiceID, r.ServiceName,a.UserName, tr.Status 
                         FROM transactiondetails tr 
                         INNER JOIN ref_services r ON r.ServiceID = tr.ServiceID 
                         INNER JOIN accounts a ON tr.CreatedByAID = a.AID 
                         WHERE tr.SiteID = ? AND tr.TerminalID = ? 
                         AND tr.DateCreated >= ? AND tr.DateCreated < ? AND tr.TransactionSummaryID = ? AND TransactionType = ? 
                         ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $zterminalID);
                    $this->bindparameter(3, $zdatefrom);
                    $this->bindparameter(4, $zdateto);
                    $this->bindparameter(5, $zsummaryID);
                    $this->bindparameter(6, $ztranstype);
                }
                else
                {
                    $stmt = "SELECT tr.TransactionReferenceID, tr.TransactionSummaryID, tr.SiteID, tr.TerminalID, tr.Option2 AS LoyaltyCard,
                         tr.TransactionType, tr.Amount, tr.DateCreated, tr.ServiceID, r.ServiceName, a.UserName, tr.Status 
                         FROM transactiondetails tr 
                         INNER JOIN ref_services r ON r.ServiceID = tr.ServiceID 
                         INNER JOIN accounts a on tr.CreatedByAID = a.AID 
                         WHERE tr.SiteID = ? AND tr.TerminalID = ? 
                         AND tr.DateCreated >= ? AND tr.DateCreated < ? AND TransactionType = ? 
                         ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $zterminalID);
                    $this->bindparameter(3, $zdatefrom);
                    $this->bindparameter(4, $zdateto);
                    $this->bindparameter(5, $ztranstype);
                }
            }
            else
            {
                //if summary ID was SELECTed on the grid, execute;
                if($zsummaryID > 0)
                {
                    $stmt = "SELECT tr.TransactionReferenceID, tr.TransactionSummaryID, tr.SiteID, tr.TerminalID, tr.Option2 AS LoyaltyCard,
                         tr.TransactionType, tr.Amount, tr.DateCreated, tr.ServiceID, r.ServiceName,a.UserName, tr.Status 
                         FROM transactiondetails tr 
                         INNER JOIN ref_services r ON r.ServiceID = tr.ServiceID 
                         INNER JOIN accounts a ON tr.CreatedByAID = a.AID 
                         WHERE tr.SiteID = ? AND tr.TerminalID = ? 
                         AND tr.DateCreated >= ? AND tr.DateCreated < ? AND tr.TransactionSummaryID = ? 
                         ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $zterminalID);
                    $this->bindparameter(3, $zdatefrom);
                    $this->bindparameter(4, $zdateto);
                    $this->bindparameter(5, $zsummaryID);
                }
                else
                {
                    $stmt = "SELECT tr.TransactionReferenceID, tr.TransactionSummaryID, tr.SiteID, tr.TerminalID, tr.Option2 AS LoyaltyCard,
                         tr.TransactionType, tr.Amount, tr.DateCreated, tr.ServiceID, r.ServiceName, a.UserName, tr.Status 
                         FROM transactiondetails tr 
                         INNER JOIN ref_services r ON r.ServiceID = tr.ServiceID 
                         INNER JOIN accounts a ON tr.CreatedByAID = a.AID 
                         WHERE tr.SiteID = ? AND tr.TerminalID = ? 
                         AND tr.DateCreated >= ? AND tr.DateCreated < ? 
                         ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $zterminalID);
                    $this->bindparameter(3, $zdatefrom);
                    $this->bindparameter(4, $zdateto);
                }
            }
        }
        
        $this->execute();
        return $this->fetchAllData();
    }
    
    //E-City Transaction Tracking: count transaction details
    function counttransdetails($zsiteID,$zterminalID, $zdatefrom, $zdateto, $zsummaryID, $ztranstype)
    {
        //check if specific transaction type(D,R,W) was SELECTed
        if($ztranstype <> 'All')
        {
            //if summary ID was SELECTed on the grid, execute;
            if($zsummaryID > 0)
            {
                $stmt = "SELECT COUNT(TransactionDetailsID) ctrtdetails 
                     FROM transactiondetails WHERE SiteID = ? AND TerminalID = ? 
                     AND DateCreated >= ? AND DateCreated < ? 
                     AND TransactionSummaryID = ? AND TransactionType = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
                $this->bindparameter(5, $zsummaryID);
                $this->bindparameter(6, $ztranstype);
            }
            else
            {
                $stmt = "SELECT COUNT(TransactionDetailsID) ctrtdetails 
                     FROM transactiondetails WHERE SiteID = ? AND TerminalID = ? 
                     AND DateCreated >= ? AND DateCreated < ? AND TransactionType = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
                $this->bindparameter(5, $ztranstype);
            } 
        }
        else
        {
            //if summary ID was SELECTed on the grid, execute;
            if($zsummaryID > 0)
            {
                $stmt = "SELECT COUNT(TransactionDetailsID) ctrtdetails 
                     FROM transactiondetails WHERE SiteID = ? AND TerminalID = ? 
                     AND DateCreated >= ? AND DateCreated < ? AND TransactionSummaryID = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
                $this->bindparameter(5, $zsummaryID);
            }
            else
            {
                $stmt = "SELECT COUNT(TransactionDetailsID) ctrtdetails 
                     FROM transactiondetails WHERE SiteID = ? AND TerminalID = ? 
                     AND DateCreated >= ? AND DateCreated < ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
            }
        }
 
        $this->execute();
        return $this->fetchData();
    }
    
    //E-City Transaction Summary, get details
    function gettransactionsummary($zsiteID, $zterminalID, $zdatefrom, $zdateto, $ztranstype)
    {
        //check if transaction type was selected All
        if($ztranstype == "All")
        {
            //check if site was SELECTed all
            if($zsiteID > 0)
            {
                if($zterminalID > 0)
                {
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID, tm.TerminalCode,tr.SiteID, tr.Option2 AS LoyaltyCard,
                        tr.TransactionType, tr.ServiceID, sum(tr.Amount) AS amount, tr.TransactionType, acc.UserName  
                        FROM transactiondetails tr 
                        INNER JOIN terminals tm ON tm.TerminalID = tr.TerminalID
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN accounts acc ON acc.AID = tr.CreatedByAID
                        WHERE tr.SiteID = ? 
                        AND tr.TerminalID = ?
                        AND tr.DateCreated >= ? 
                        AND tr.DateCreated <  ?
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID 
                        ORDER BY tm.TerminalCode, ts.DateStarted Asc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $zterminalID);
                    $this->bindparameter(3, $zdatefrom);
                    $this->bindparameter(4, $zdateto);
                }else{
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID, tm.TerminalCode,tr.SiteID, tr.Option2 AS LoyaltyCard,
                        tr.TransactionType, tr.ServiceID, sum(tr.Amount) AS amount, tr.TransactionType, acc.UserName 
                        FROM transactiondetails tr 
                        INNER JOIN terminals tm ON tm.TerminalID = tr.TerminalID
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN accounts acc ON acc.AID = tr.CreatedByAID
                        WHERE tr.SiteID = ?
                        AND tr.DateCreated >= ? 
                        AND tr.DateCreated <  ?
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID 
			ORDER BY tm.TerminalCode, ts.DateStarted Asc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    //$this->bindparameter(2, $zterminalID);
                    $this->bindparameter(2, $zdatefrom);
                    $this->bindparameter(3, $zdateto);
                }
                
            }
        }
        else
        {
            //check if site was SELECTed all
            if($zsiteID > 0)
            {
                if($zterminalID > 0)
                {
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID, tm.TerminalCode,tr.SiteID, tr.Option2 AS LoyaltyCard,
                        tr.TransactionType, tr.ServiceID, sum(tr.Amount) AS amount, tr.TransactionType, acc.UserName 
                        FROM transactiondetails tr 
                        INNER JOIN terminals tm ON tm.TerminalID = tr.TerminalID
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN accounts acc ON acc.AID = tr.CreatedByAID
                        WHERE tr.SiteID = ? 
                        AND tr.TerminalID = ?
                        AND tr.DateCreated >= ? 
                        AND tr.DateCreated <  ?
                        AND tr.TransactionType = ? 
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID 
			ORDER BY tm.TerminalCode, ts.DateStarted Asc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $zterminalID);
                    $this->bindparameter(3, $zdatefrom);
                    $this->bindparameter(4, $zdateto);
                    $this->bindparameter(5, $ztranstype);
                }else{
                    $stmt = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID, tm.TerminalCode,tr.SiteID, tr.Option2 AS LoyaltyCard,
                        tr.TransactionType, tr.ServiceID, sum(tr.Amount) AS amount, tr.TransactionType, acc.UserName 
                        FROM transactiondetails tr 
                        INNER JOIN terminals tm ON tm.TerminalID = tr.TerminalID
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                        INNER JOIN accounts acc ON acc.AID = tr.CreatedByAID
                        WHERE tr.SiteID = ? 
                        AND tr.DateCreated >= ? 
                        AND tr.DateCreated <  ?
                        AND tr.TransactionType = ? 
                        GROUP BY tr.TransactionType,tr.TransactionSummaryID 
			ORDER BY tm.TerminalCode, ts.DateStarted Asc";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    //$this->bindparameter(2, $zterminalID);
                    $this->bindparameter(2, $zdatefrom);
                    $this->bindparameter(3, $zdateto);
                    $this->bindparameter(4, $ztranstype);
                }
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
    
    //E-City Transaction Summary, count transactions summary
    function counttranssummary($zsiteID, $zterminalID, $zdatefrom, $zdateto)
    {
        if($zsiteID > 0)
        {
            $stmt = "SELECT COUNT(ts.TransactionsSummaryID) ctrtsum
                 FROM transactionsummary ts 
                 INNER JOIN accounts acc ON ts.CreatedByAID = acc.AID
                 WHERE SiteID = ? AND TerminalID = ? AND ts.DateStarted >= ?
                 AND ts.DateStarted < ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zsiteID);
            $this->bindparameter(2, $zterminalID);
            $this->bindparameter(3, $zdatefrom);
            $this->bindparameter(4, $zdateto);
        }
        $this->execute();
        return $this->fetchData();
    }
    
    //E-City Transaction Request LOgs ON LP, get details
    function gettranslogslp($zsiteID, $zterminalID, $zdatefrom, $zdateto, $ztranstype, $zsummaryID, $zstart, $zlimit, $zsort, $zdirection)
    {
        //check if request is excel / pdf export
        if($zstart == null && $zlimit == null)
        {
            //check if specific transaction type(D,R,W) was SELECTed
            if($ztranstype <> 'All')
            {
                //check if site is SELECTed all
                if($zsiteID > 0)
                {
                    $stmt = "SELECT t.TransactionRequestLogLPID, t.TransactionSummaryID, t.TransactionReferenceID, t.Amount, t.StartDate, 
                             t.EndDate, t.TransactionType, t.TerminalID, t.Status, t.SiteID, t.ServiceTransactionID, t.Option1 AS LoyaltyCard,
                             t.ServiceStatus, t.ServiceTransferHistoryID, t.ServiceID, r.ServiceName FROM transactionrequestlogslp t
                             INNER JOIN ref_services r ON t.ServiceID = r.ServiceID
                             WHERE t.SiteID = ? AND t.StartDate >= ? AND t.StartDate < ? AND t.TransactionType = ?";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    //$this->bindparameter(2, $zterminalID);
                    $this->bindparameter(2, $zdatefrom);
                    $this->bindparameter(3, $zdateto);
                    $this->bindparameter(4, $ztranstype);
                }
            }
            else
            {
                //check if site was SELECTed all
                if($zsiteID > 0)
                {
                    $stmt = "SELECT t.TransactionRequestLogLPID, t.TransactionSummaryID, t.TransactionReferenceID, t.Amount, t.StartDate, 
                             t.EndDate, t.TransactionType, t.TerminalID, t.Status, t.SiteID, t.ServiceTransactionID, t.Option1 AS LoyaltyCard,
                             t.ServiceStatus, t.ServiceTransferHistoryID, t.ServiceID, r.ServiceName FROM transactionrequestlogslp t
                             INNER JOIN ref_services r ON t.ServiceID = r.ServiceID
                             WHERE t.SiteID = ? AND t.StartDate >= ? AND t.StartDate < ?";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    //$this->bindparameter(2, $zterminalID);
                    $this->bindparameter(2, $zdatefrom);
                    $this->bindparameter(3, $zdateto);
                }
            }
        }
        //jqgrid pagination
        else
        {
            //check if specific transaction type(D,R,W) was SELECTed
            if($ztranstype <> 'All')
            {
                //check if specific summary ID was SELECTed
                if($zsummaryID > 0)
                {
                    $stmt = "SELECT lp.TransactionRequestLogLPID, lp.TransactionSummaryID, lp.TransactionReferenceID, lp.Amount, lp.StartDate, 
                         lp.EndDate, lp.TransactionType, lp.TerminalID, lp.Status, lp.SiteID, lp.ServiceTransactionID, lp.Option1 AS LoyaltyCard,
                         lp.ServiceStatus, lp.ServiceTransferHistoryID, lp.ServiceID, r.ServiceName 
                         FROM transactionrequestlogslp lp
                         INNER JOIN ref_services r ON r.ServiceID = lp.ServiceID
                         WHERE SiteID = ? 
                         AND TerminalID = ? 
                         AND StartDate >= ? 
                         AND StartDate < ? 
                         AND TransactionType = ? 
                         AND TransactionSummaryID = ?
                         ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $zterminalID);
                    $this->bindparameter(3, $zdatefrom);
                    $this->bindparameter(4, $zdateto);
                    $this->bindparameter(5, $ztranstype);
                    $this->bindparameter(6, $zsummaryID);
                }
                else
                {
                    $stmt = "SELECT lp.TransactionRequestLogLPID, lp.TransactionSummaryID, lp.TransactionReferenceID, lp.Amount, lp.StartDate, 
                         lp.EndDate, lp.TransactionType, lp.TerminalID, lp.Status, lp.SiteID, lp.ServiceTransactionID, lp.Option1 AS LoyaltyCard,
                         lp.ServiceStatus, lp.ServiceTransferHistoryID, lp.ServiceID, r.ServiceName
                         FROM transactionrequestlogslp lp
                         INNER JOIN ref_services r ON r.ServiceID = lp.ServiceID
                         WHERE SiteID = ? 
                         AND TerminalID = ? 
                         AND StartDate >= ? 
                         AND t.StartDate < ? 
                         AND TransactionType = ?
                         ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $zterminalID);
                    $this->bindparameter(3, $zdatefrom);
                    $this->bindparameter(4, $zdateto);
                    $this->bindparameter(5, $ztranstype);
                }
            }
            else
            {
                //check if summary ID was SELECTed
                if($zsummaryID > 0)
                {
                    $stmt = "SELECT lp.TransactionRequestLogLPID, lp.TransactionSummaryID, lp.TransactionReferenceID, lp.Amount, lp.StartDate, 
                         lp.EndDate, lp.TransactionType, lp.TerminalID, lp.Status, lp.SiteID, lp.ServiceTransactionID, lp.Option1 AS LoyaltyCard,
                         lp.ServiceStatus, lp.ServiceTransferHistoryID, lp.ServiceID, r.ServiceName
                         FROM transactionrequestlogslp lp
                         INNER JOIN ref_services r ON r.ServiceID = lp.ServiceID
                         WHERE SiteID = ? 
                         AND TerminalID = ? 
                         AND StartDate >= ? 
                         AND StartDate < ? 
                         AND TransactionSummaryID = ?
                         ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $zterminalID);
                    $this->bindparameter(3, $zdatefrom);
                    $this->bindparameter(4, $zdateto);
                    $this->bindparameter(5, $zsummaryID);
                }
                else
                {
                    $stmt = "SELECT lp.TransactionRequestLogLPID, lp.TransactionSummaryID, lp.TransactionReferenceID, lp.Amount, lp.StartDate, 
                         lp.EndDate, lp.TransactionType, lp.TerminalID, lp.Status, lp.SiteID, lp.ServiceTransactionID, lp.Option1 AS LoyaltyCard,
                         lp.ServiceStatus, lp.ServiceTransferHistoryID, lp.ServiceID, r.ServiceName
                         FROM transactionrequestlogslp lp 
                         INNER JOIN ref_services r ON r.ServiceID = lp.ServiceID
                         WHERE SiteID = ? 
                         AND TerminalID = ? 
                         AND StartDate >= ? 
                         AND StartDate < ? 
                         ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $zterminalID);
                    $this->bindparameter(3, $zdatefrom);
                    $this->bindparameter(4, $zdateto);
                }
            }
        }
        $this->execute();
        return $this->fetchAllData();
    }
    
    //E-City Transaction Request Logs, count
    function counttranslogslp($zsiteID, $zterminalID, $zdatefrom, $zdateto, $ztranstype, $zsummaryID)
    {
        //check if specific transaction type(D,R,W) was SELECTed
        if($ztranstype <> 'All')
        {
            //if summary ID was SELECTed
            if($zsummaryID > 0)
            {
                $stmt = "SELECT COUNT(TransactionRequestLogLPID) ctrlogs FROM transactionrequestlogslp 
                         WHERE SiteID = ? AND TerminalID = ? AND StartDate >= ? 
                         AND StartDate < ? AND TransactionType = ? AND TransactionSummaryID = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
                $this->bindparameter(5, $ztranstype);
                $this->bindparameter(6, $zsummaryID);
            }
            else
            {
                $stmt = "SELECT COUNT(TransactionRequestLogLPID) ctrlogs FROM transactionrequestlogslp 
                         WHERE SiteID = ? AND TerminalID = ? AND StartDate >= ? 
                         AND StartDate < ? AND TransactionType = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
                $this->bindparameter(5, $ztranstype);
            }
        }
        else
        {
            if($zsummaryID > 0)
            {
                $stmt = "SELECT COUNT(TransactionRequestLogLPID) ctrlogs FROM transactionrequestlogslp 
                         WHERE SiteID = ? AND TerminalID = ? AND StartDate >= ? AND StartDate < ? AND TransactionSummaryID = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
                $this->bindparameter(5, $zsummaryID);
            }
            else
            {
                $stmt = "SELECT COUNT(TransactionRequestLogLPID) ctrlogs FROM transactionrequestlogslp 
                         WHERE SiteID = ? AND TerminalID = ? AND DATE(StartDate) >= ? AND DATE(StartDate) <= ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
            }
        }
        $this->execute();
        return $this->fetchData();
    }
    
    //get all services
    function getallservices($sort)
    {
        $stmt = "SELECT * FROM ref_services WHERE Status = 1 ORDER BY $sort";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }
    
    //SELECT all terminal based from sites to populate combo box
//    function viewterminals($zsiteID)
//    {
//        if($zsiteID > 0)
//        {
//            $stmt = "SELECT DISTINCT a.TerminalID, b.TerminalCode FROM transactiondetails a 
//                INNER JOIN terminals b ON a.TerminalID = b.TerminalID 
//                WHERE a.SiteID = '".$zsiteID."' ORDER BY TerminalID ASC";
//        }
//        $this->executeQuery($stmt);
//        return $this->fetchAllData();
//    }
    
    //get terminal name
    function getterminalname($zterminalID)
    {
        $stmt = "SELECT TerminalName FROM terminals WHERE TerminalID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zterminalID);
        $this->execute();
        return $this->fetchData();
    }
    
    function gettranstypes()
    {
        $stmt = "SELECT TransactionTypeID, TransactionTypeCode, Description FROM ref_transactiontype";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchAllData();
    }
    
    //Extraction of Transaction Details, Note: change site's
//    function getTransDet($cdatefrom,$cdateto,$cserviceid)
//    {
//        $stmt = 'SELECT t.TerminalCode,td.TransactionType,td.DateCreated,td.Amount,td.TransactionReferenceID,
//            a.UserName,concat(tr.TransactionRequestLogID,",",t.TerminalID) as TrackingInfo,tr.ServiceTransactionID,
//            tr.SiteID,tr.TerminalID FROM transactiondetails td        
//            INNER JOIN transactionrequestlogs tr ON tr.TransactionReferenceID = td.TransactionReferenceID     
//            INNER JOIN terminals t ON t.TerminalID = td.TerminalID
//            INNER JOIN accounts a ON a.AID = td.CreatedByAID
//            WHERE td.DateCreated >= ? AND td.DateCreated < ? 
//            AND td.ServiceID IN(?) AND td.SiteID IN(145,146,147) ORDER BY td.DateCreated DESC';
//        $this->prepare($stmt);
//        $this->bindparameter(1,$cdatefrom);
//        $this->bindparameter(2,$cdateto);
//        $this->bindparameter(3,$cserviceid);
//        $this->execute();
//        return $this->fetchAllData();
//    }
    
    //Extraction Transaction Logs of LP only,  Note: change site's
//    function getLPTrans($cdatefrom,$cdateto)
//    {
//        $stmt = "SELECT tlp.TransactionRequestLogLPID,t.TerminalCode ,
//            tlp.TransactionType,tlp.Amount,rs.ServiceDescription ,rs.Code,
//            tlp.StartDate,tlp.ServiceStatus ,
//            tlp.ServiceTransactionID ,tlp.SiteID,tlp.TerminalID  FROM transactionrequestlogslp  tlp
//            INNER JOIN terminals t ON t.TerminalID = tlp.TerminalID
//            INNER JOIN ref_services rs ON rs.ServiceID = tlp.ServiceID
//            WHERE tlp.StartDate >= ? AND tlp.StartDate < ? AND tlp.SiteID IN(145,146,147) 
//            ORDER BY tlp.TransactionRequestLogLPID ";
//        $this->prepare($stmt);
//        $this->bindparameter(1, $cdatefrom);
//        $this->bindparameter(2, $cdateto);
//        $this->execute();
//        return $this->fetchAllData();
//    }

    //method for jqgrid plugin: parameters
    public function getJqgrid($total_row,$default_field) {
        //$jqgrid = new jQGrid();
        $jqgrid->page = $_GET['page']; 
        $limit = (int)$_GET['rows'];
        $start = ((int)$_GET['page'] * $limit) - $limit;
        $dir = $_GET['sord'];
        $sort = $_GET['sidx'];
        if($_GET['sidx'] == '') 
            $sort = $default_field;
        
        $jqgrid->total = ceil($total_row / $limit);
        $jqgrid->records = $total_row;
        return array('jqgrid'=>$jqgrid,'sort'=>$sort,'dir'=>$dir,'start'=>$start,'limit'=>$limit);
    } 
    
    /*
     * Get old gross hold balance if queried date is not today, for gh balance  per cutoff pagination
     */
    public function getoldGHBalance($sort, $dir, $startdate,$enddate,$zsiteid)
    {       
       switch ($zsiteid)
       {
           case '':
                $query1 = "SELECT sgc.SiteID, sgc.BeginningBalance, sgc.EndingBalance, ad.Name, sd.SiteDescription, 
                            s.SiteCode, s.POSAccountNo,sgc.DateFirstTransaction,sgc.DateLastTransaction,sgc.ReportDate,
                            sgc.DateCutOff,sgc.Deposit AS InitialDeposit, sgc.Reload AS Reload , sgc.Withdrawal AS Redemption                       
                            FROM sitegrossholdcutoff sgc
                            INNER JOIN sites s ON s.SiteID = sgc.SiteID
                            INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID
                            INNER JOIN sitedetails sd ON sd.SiteID = sgc.SiteID
                            WHERE sgc.DateCutOff > ? AND sgc.DateCutOff <= ?
                            ORDER BY s.SiteCode, sgc.DateFirstTransaction";          

                $query2 = "SELECT SiteID,DateCredited,AmountConfirmed FROM grossholdconfirmation 
                    WHERE DateCredited  >= ? AND DateCredited < ? ";

                $query3 = "SELECT SiteID,Amount,StatusUpdateDate FROM siteremittance 
                    WHERE StatusUpdateDate  >= ? AND StatusUpdateDate < ? ";

//                $query4 = "SELECT SiteID,Amount,DateCredited FROM ";

                $query5 = "SELECT SiteID, ActualAmount AS ActualAmount,TransactionDate FROM manualredemptions " . 
                        "WHERE TransactionDate >= ? AND TransactionDate < ? ";   
                
                $query6 = "SELECT 

                                -- DEPOSIT CASH --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN
                                        CASE tr.PaymentType
                                          WHEN 2 THEN 0 -- Coupon
                                          ELSE -- Not Coupon
                                                CASE IFNULL(tr.StackerSummaryID, '')
                                                  WHEN '' THEN SUM(tr.Amount) -- Cash
                                                  ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                                        (SELECT IFNULL(SUM(Amount), 0)
                                                        FROM stackermanagement.stackerdetails sdtls
                                                        WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                                  AND sdtls.TransactionType = 1
                                                                  AND sdtls.PaymentType = 0)  -- Deposit, Cash
                                                END
                                        END
                                  ELSE 0 -- Not Deposit
                                END As DepositCash,
                                
                                -- DEPOSIT COUPON --
                                CASE tr.TransactionType
                                    WHEN 'D' THEN
                                      CASE tr.PaymentType
                                        WHEN 2 THEN SUM(tr.Amount) -- Coupon
                                        ELSE 0
                                      END
                                    ELSE 0
                                END As DepositCoupon,
                                
                                -- DEPOSIT TICKET --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN 0 -- Coupon
                                      ELSE -- Not Coupon
                                        CASE IFNULL(tr.StackerSummaryID, '')
                                          WHEN '' THEN 0 -- Cash
                                          ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                            (SELECT IFNULL(SUM(Amount), 0)
                                            FROM stackermanagement.stackerdetails sdtls
                                            WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                  AND sdtls.TransactionType = 1
                                                  AND sdtls.PaymentType = 2)  -- Deposit, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Deposit
                                END As DepositTicket,
                                
                                -- RELOAD COUPON --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN SUM(tr.Amount) -- Coupon
                                      ELSE 0
                                    END
                                  ELSE 0
                                END As ReloadCoupon,

                                -- RELOAD CASH --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                        CASE tr.PaymentType
                                          WHEN 2 THEN 0 -- Coupon
                                          ELSE -- Not Coupon
                                                CASE IFNULL(tr.StackerSummaryID, '')
                                                  WHEN '' THEN SUM(tr.Amount) -- Cash
                                                  ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                                        (SELECT IFNULL(SUM(Amount), 0)
                                                        FROM stackermanagement.stackerdetails sdtls
                                                        WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                                  AND sdtls.TransactionType = 2
                                                                  AND sdtls.PaymentType = 0)  -- Reload, Cash
                                                END
                                        END
                                  ELSE 0 -- Not Reload
                                END As ReloadCash,
                                
                                -- RELOAD TICKET --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN 0 -- Coupon
                                      ELSE -- Not Coupon
                                        CASE IFNULL(tr.StackerSummaryID, '')
                                          WHEN '' THEN 0 -- Cash
                                          ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                            (SELECT IFNULL(SUM(Amount), 0)
                                            FROM stackermanagement.stackerdetails sdtls
                                            WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                  AND sdtls.TransactionType = 2
                                                  AND sdtls.PaymentType = 2)  -- Reload, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Reload
                                END As ReloadTicket,

                                -- REDEMPTION CASHIER --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN
                                        CASE a.AccountTypeID
                                          WHEN 4 THEN SUM(tr.Amount) -- Cashier
                                          ELSE 0
                                        END -- Genesis
                                  ELSE 0 --  Not Redemption
                                END As RedemptionCashier,
                                
                                -- REDEMPTION GENESIS --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN
                                    CASE a.AccountTypeID
                                      WHEN 15 THEN SUM(tr.Amount) -- Genesis
                                      ELSE 0
                                    END -- Cashier
                                  ELSE 0 -- Not Redemption
                                END As RedemptionGenesis,

                                ts.DateStarted, ts.DateEnded, tr.SiteID
                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                INNER JOIN npos.sites s ON tr.SiteID = s.SiteID
                                WHERE tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY s.POSAccountNo ";   

                // to get beginning balance, sitecode, sitename
                $this->prepare($query1);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->execute(); 
                $rows1 = $this->fetchAllData();        
                $qr1 = array();
                foreach($rows1 as $row1) {
                    $qr1[] = array('siteid'=>$row1['SiteID'],'begbal'=>$row1['BeginningBalance'],'endbal'=>$row1['BeginningBalance'],
                        'sitecode'=>$row1['SiteCode'],'sitename'=>($row1['Name'] . ' ' . $row1['SiteDescription']), 'POSAccountNo' => $row1['POSAccountNo'],
                        'initialdep'=>'0.00','reload'=>'0.00','redemption'=>'0.00',
                        'datestart'=>$row1['DateFirstTransaction'],'datelast'=>$row1['DateLastTransaction'],
                        'reportdate'=>$row1['ReportDate'],'cutoff'=>$row1['DateCutOff'],'manualredemption'=>0,
                        'replenishment'=>0,'collection'=>0,'replenishment'=>0
                        );
                }

                // to get confirmation made by cashier from provincial sites
                $this->prepare($query2);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->execute();
                $rows2 = $this->fetchAllData();
                $qr2 = array();
                foreach($rows2 as $row2) 
                {
                    $qr2[] = array('siteid'=>$row2['SiteID'],'datecredit'=>$row2['DateCredited'],
                        'amount'=>$row2['AmountConfirmed']);
                }

                // to get deposits made by cashier from metro manila
                $this->prepare($query3);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->execute();
                $rows3 = $this->fetchAllData();
                $qr3 = array();
                foreach($rows3 as $row3) 
                {
                    $qr3[] = array('siteid'=>$row3['SiteID'],'datecredit'=>$row3['StatusUpdateDate'],
                        'amount'=>$row3['Amount']);
                }  
                // to get manual redemptions based on date range
                $this->prepare($query5);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->execute();
                $rows5 = $this->fetchAllData();
                $qr5 = array();
                foreach($rows5 as $row5)
                {
                    $qr5[] = array('siteid'=>$row5['SiteID'],'manualredemption'=>$row5['ActualAmount'],'mrtransdate'=>$row5['TransactionDate']);
                } 
                
                //Total the Deposit and Reload Cash, Deposit and Reload Coupons, Deposit and Reload Tickets in EGM
                //Total Redemption made by the cashier and the EGM
                $this->prepare($query6);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->execute();  
                $rows6 =  $this->fetchAllData();
                
                foreach ($rows6 as $row6) {
                    foreach ($qr1 as $keys => $value1) {
                        if($row6["SiteID"] == $value1["siteid"]){
                            if($row6["DepositCash"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositCash"];
                            if($row6["ReloadCash"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadCash"];
                            if($row6["RedemptionCashier"] != '0.00')
                                $qr1[$keys]["redemption"] = (float)$qr1[$keys]["redemption"] + (float)$row6["RedemptionCashier"];
                            if($row6["RedemptionGenesis"] != '0.00')
                                $qr1[$keys]["redemption"] = (float)$qr1[$keys]["redemption"] + (float)$row6["RedemptionGenesis"];
                            if($row6["DepositCoupon"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositCoupon"];
                            if($row6["ReloadCoupon"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadCoupon"];
                            if($row6["DepositTicket"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositTicket"];
                            if($row6["ReloadTicket"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadTicket"];
                            break;
                        }
                    }     
                }

                $ctr = 0;
                while($ctr < count($qr1))
                {
                    $ctr2 = 0; // counter for manual redemptions array
                    while($ctr2 < count($qr5))
                    {
                        if($qr1[$ctr]['siteid'] == $qr5[$ctr2]['siteid'])
                        {
                            $amount = 0;
                            if(($qr5[$ctr2]['mrtransdate'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr5[$ctr2]['mrtransdate'] < $qr1[$ctr]['cutoff']))
                            {              
                                 if($qr1[$ctr]['manualredemption'] == 0) 
                                     $qr1[$ctr]['manualredemption'] = $qr5[$ctr2]['manualredemption'];
                                 else
                                 {
                                     $amount = $qr1[$ctr]['manualredemption'];
                                     $qr1[$ctr]['manualredemption'] = $amount + $qr5[$ctr2]['manualredemption'];
                                 }
                            }
                        }
                        $ctr2 = $ctr2 + 1;
                    }            

                    $ctr3 = 0; //counter for grossholdconfirmation array
                    while($ctr3 < count($qr2))
                    {
                        if($qr1[$ctr]['siteid'] == $qr2[$ctr3]['siteid'])
                        {
                            $amount = 0;
                            if(($qr2[$ctr3]['datecredit'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr2[$ctr3]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['replenishment'] == 0) 
                                    $qr1[$ctr]['replenishment'] = $qr2[$ctr3]['amount'];
                                else
                                {
                                    $amount = $qr1[$ctr]['replenishment'];
                                    $qr1[$ctr]['replenishment'] = $amount + $qr2[$ctr3]['amount'];
                                }                         
                            }                   
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr4 = 0; //counter for collection array
                    while($ctr4 < count($qr3))
                    {
                        if($qr1[$ctr]['siteid'] == $qr3[$ctr4]['siteid'])
                        {         
                            $amount = 0;
                            if(($qr3[$ctr4]['datecredit'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr3[$ctr4]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['collection'] == 0) 
                                {
                                    $qr1[$ctr]['collection'] = $qr3[$ctr4]['amount'];         
                                }      
                                else
                                {
                                    $amount = $qr1[$ctr]['collection'];
                                    $qr1[$ctr]['collection'] = $amount + $qr3[$ctr4]['amount'];                                
                                }                           
                            }                    
                        }
                        $ctr4 = $ctr4 + 1;
                    }            
                    $ctr = $ctr + 1;
                }                 
               break;
           case $zsiteid > 0 :
                $query1 = "SELECT sgc.SiteID, sgc.BeginningBalance, sgc.EndingBalance, ad.Name, sd.SiteDescription, 
                            s.SiteCode, s.POSAccountNo,sgc.DateFirstTransaction,sgc.DateLastTransaction,sgc.ReportDate,
                            sgc.DateCutOff,sgc.Deposit AS InitialDeposit, sgc.Reload AS Reload , sgc.Withdrawal AS Redemption                       
                            FROM sitegrossholdcutoff sgc
                            INNER JOIN sites s ON s.SiteID = sgc.SiteID
                            INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID
                            INNER JOIN sitedetails sd ON sd.SiteID = sgc.SiteID
                            WHERE sgc.DateCutOff > ? AND sgc.DateCutOff <= ?
                            AND sgc.SiteID = ?
                            ORDER BY s.SiteCode, sgc.DateFirstTransaction";          

                $query2 = "SELECT SiteID,DateCredited,AmountConfirmed FROM grossholdconfirmation 
                    WHERE DateCredited  >= ? AND DateCredited < ?  AND SiteID = ?";

                $query3 = "SELECT SiteID,Amount,StatusUpdateDate FROM siteremittance 
                    WHERE StatusUpdateDate  >= ? AND StatusUpdateDate < ?  AND SiteID = ?";

                

                $query5 = "SELECT SiteID, ActualAmount AS ActualAmount,TransactionDate FROM manualredemptions " . 
                        "WHERE TransactionDate >= ? AND TransactionDate < ? AND SiteID = ? ";  
                
                $query6 = "SELECT 

                                -- DEPOSIT CASH --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN
                                        CASE tr.PaymentType
                                          WHEN 2 THEN 0 -- Coupon
                                          ELSE -- Not Coupon
                                                CASE IFNULL(tr.StackerSummaryID, '')
                                                  WHEN '' THEN SUM(tr.Amount) -- Cash
                                                  ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                                        (SELECT IFNULL(SUM(Amount), 0)
                                                        FROM stackermanagement.stackerdetails sdtls
                                                        WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                                  AND sdtls.TransactionType = 1
                                                                  AND sdtls.PaymentType = 0)  -- Deposit, Cash
                                                END
                                        END
                                  ELSE 0 -- Not Deposit
                                END As DepositCash,
                                
                                -- DEPOSIT COUPON --
                                CASE tr.TransactionType
                                    WHEN 'D' THEN
                                      CASE tr.PaymentType
                                        WHEN 2 THEN SUM(tr.Amount) -- Coupon
                                        ELSE 0
                                      END
                                    ELSE 0
                                END As DepositCoupon,
                                
                                -- DEPOSIT TICKET --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN 0 -- Coupon
                                      ELSE -- Not Coupon
                                        CASE IFNULL(tr.StackerSummaryID, '')
                                          WHEN '' THEN 0 -- Cash
                                          ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                            (SELECT IFNULL(SUM(Amount), 0)
                                            FROM stackermanagement.stackerdetails sdtls
                                            WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                  AND sdtls.TransactionType = 1
                                                  AND sdtls.PaymentType = 2)  -- Deposit, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Deposit
                                END As DepositTicket,
                                
                                -- RELOAD COUPON --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN SUM(tr.Amount) -- Coupon
                                      ELSE 0
                                    END
                                  ELSE 0
                                END As ReloadCoupon,

                                -- RELOAD CASH --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                        CASE tr.PaymentType
                                          WHEN 2 THEN 0 -- Coupon
                                          ELSE -- Not Coupon
                                                CASE IFNULL(tr.StackerSummaryID, '')
                                                  WHEN '' THEN SUM(tr.Amount) -- Cash
                                                  ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                                        (SELECT IFNULL(SUM(Amount), 0)
                                                        FROM stackermanagement.stackerdetails sdtls
                                                        WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                                  AND sdtls.TransactionType = 2
                                                                  AND sdtls.PaymentType = 0)  -- Reload, Cash
                                                END
                                        END
                                  ELSE 0 -- Not Reload
                                END As ReloadCash,
                                
                                -- RELOAD TICKET --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN 0 -- Coupon
                                      ELSE -- Not Coupon
                                        CASE IFNULL(tr.StackerSummaryID, '')
                                          WHEN '' THEN 0 -- Cash
                                          ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                            (SELECT IFNULL(SUM(Amount), 0)
                                            FROM stackermanagement.stackerdetails sdtls
                                            WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                  AND sdtls.TransactionType = 2
                                                  AND sdtls.PaymentType = 2)  -- Reload, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Reload
                                END As ReloadTicket,

                                -- REDEMPTION CASHIER --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN
                                        CASE a.AccountTypeID
                                          WHEN 4 THEN SUM(tr.Amount) -- Cashier
                                          ELSE 0
                                        END -- Genesis
                                  ELSE 0 --  Not Redemption
                                END As RedemptionCashier,
                                
                                -- REDEMPTION GENESIS --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN
                                    CASE a.AccountTypeID
                                      WHEN 15 THEN SUM(tr.Amount) -- Genesis
                                      ELSE 0
                                    END -- Cashier
                                  ELSE 0 -- Not Redemption
                                END As RedemptionGenesis,

                                ts.DateStarted, ts.DateEnded, tr.SiteID
                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                INNER JOIN npos.sites s ON tr.SiteID = s.SiteID
                                WHERE tr.SiteID = ?
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY s.POSAccountNo";

                // to get beginning balance, sitecode, sitename
                $this->prepare($query1);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                
                $this->execute(); 
                $rows1 = $this->fetchAllData();        
                $qr1 = array();
                foreach($rows1 as $row1) {
                    $qr1[] = array('siteid'=>$row1['SiteID'],'begbal'=>$row1['BeginningBalance'],'endbal'=>$row1['BeginningBalance'],
                        'sitecode'=>$row1['SiteCode'],'sitename'=>($row1['Name'] . ' ' . $row1['SiteDescription']), 'POSAccountNo' => $row1['POSAccountNo'],
                        'initialdep'=>'0.00','reload'=>'0.00','redemption'=>'0.00',
                        'datestart'=>$row1['DateFirstTransaction'],'datelast'=>$row1['DateLastTransaction'],
                        'reportdate'=>$row1['ReportDate'],'cutoff'=>$row1['DateCutOff'],'manualredemption'=>0,
                        'replenishment'=>0,'collection'=>0,'replenishment'=>0
                        );
                }

                // to get confirmation made by cashier from provincial sites
                $this->prepare($query2);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                  
                $this->execute();
                $rows2 = $this->fetchAllData();
                $qr2 = array();
                foreach($rows2 as $row2) 
                {
                    $qr2[] = array('siteid'=>$row2['SiteID'],'datecredit'=>$row2['DateCredited'],
                        'amount'=>$row2['AmountConfirmed']);
                }

                // to get deposits made by cashier from metro manila
                $this->prepare($query3);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                  
                $this->execute();
                $rows3 = $this->fetchAllData();
                $qr3 = array();
                foreach($rows3 as $row3) 
                {
                    $qr3[] = array('siteid'=>$row3['SiteID'],'datecredit'=>$row3['StatusUpdateDate'],
                        'amount'=>$row3['Amount']);
                }  
                // to get manual redemptions based on date range
                $this->prepare($query5);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                  
                $this->execute();
                $rows5 = $this->fetchAllData();
                $qr5 = array();
                foreach($rows5 as $row5)
                {
                    $qr5[] = array('siteid'=>$row5['SiteID'],'manualredemption'=>$row5['ActualAmount'],'mrtransdate'=>$row5['TransactionDate']);
                } 
                
                //Total the Deposit and Reload Cash, Deposit and Reload Coupons, Deposit and Reload Tickets in EGM
                //Total Redemption made by the cashier and the EGM
                $this->prepare($query6);
                $this->bindparameter(1, $zsiteid);
                $this->bindparameter(2, $startdate);
                $this->bindparameter(3, $enddate);
                $this->execute();  
                $rows6 =  $this->fetchAllData();
                
                foreach ($rows6 as $row6) {
                    foreach ($qr1 as $keys => $value1) {
                        if($row6["SiteID"] == $value1["siteid"]){
                            if($row6["DepositCash"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositCash"];
                            if($row6["ReloadCash"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadCash"];
                            if($row6["RedemptionCashier"] != '0.00')
                                $qr1[$keys]["redemption"] = (float)$qr1[$keys]["redemption"] + (float)$row6["RedemptionCashier"];
                            if($row6["RedemptionGenesis"] != '0.00')
                                $qr1[$keys]["redemption"] = (float)$qr1[$keys]["redemption"] + (float)$row6["RedemptionGenesis"];
                            if($row6["DepositCoupon"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositCoupon"];
                            if($row6["ReloadCoupon"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadCoupon"];
                            if($row6["DepositTicket"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositTicket"];
                            if($row6["ReloadTicket"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadTicket"];
                            break;
                        }
                    }     
                }
                
//                print_r($qr1);
//                print_r($qr5);
                $ctr = 0;
                while($ctr < count($qr1))
                {
                    $ctr2 = 0; // counter for manual redemptions array
                    while($ctr2 < count($qr5))
                    {
                        if($qr1[$ctr]['siteid'] == $qr5[$ctr2]['siteid'])
                        {
                            $amount = 0;
                            if(($qr5[$ctr2]['mrtransdate'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr5[$ctr2]['mrtransdate'] < $qr1[$ctr]['cutoff']))
                            {              
                                 if($qr1[$ctr]['manualredemption'] == 0) 
                                     $qr1[$ctr]['manualredemption'] = $qr5[$ctr2]['manualredemption'];
                                 else
                                 {
                                     $amount = $qr1[$ctr]['manualredemption'];
                                     $qr1[$ctr]['manualredemption'] = $amount + $qr5[$ctr2]['manualredemption'];
                                 }
                                 
//                                 echo $ctr."==>".$qr5[$ctr2]['mrtransdate']." >= ".$qr1[$ctr]['reportdate']."==>".
//                                         $qr5[$ctr2]['mrtransdate']." < ".$qr1[$ctr]['cutoff']."==>".$qr1[$ctr]['manualredemption']."<br />";
//                                 
                            }
//                            else {
//                                
//                                echo "NOT IN ".$qr5[$ctr2]['mrtransdate'].">=".$qr1[$ctr]['reportdate']."<br />";
//                                
//                            }
                        }
                        $ctr2 = $ctr2 + 1;
                    }            

                    $ctr3 = 0; //counter for grossholdconfirmation array
                    while($ctr3 < count($qr2))
                    {
                        if($qr1[$ctr]['siteid'] == $qr2[$ctr3]['siteid'])
                        {
                            $amount = 0;
                            if(($qr2[$ctr3]['datecredit'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr2[$ctr3]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['replenishment'] == 0) 
                                    $qr1[$ctr]['replenishment'] = $qr2[$ctr3]['replenishment'];
                                else
                                {
                                    $amount = $qr1[$ctr]['replenishment'];
                                    $qr1[$ctr]['replenishment'] = $amount + $qr2[$ctr3]['replenishment'];
                                }                         
                            }                   
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr4 = 0; //counter for collection array
                    while($ctr4 < count($qr3))
                    {
                        if($qr1[$ctr]['siteid'] == $qr3[$ctr4]['siteid'])
                        {         
                            $amount = 0;
                            if(($qr3[$ctr4]['datecredit'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr3[$ctr4]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['collection'] == 0) 
                                {
                                    $qr1[$ctr]['collection'] = $qr3[$ctr4]['amount'];         
                                }      
                                else
                                {
                                    $amount = $qr1[$ctr]['collection'];
                                    $qr1[$ctr]['collection'] = $amount + $qr3[$ctr4]['amount'];                                
                                }                           
                            }                    
                        }
                        $ctr4 = $ctr4 + 1;
                    }            
                    $ctr = $ctr + 1;
                }                 
               break;
       }
  
        unset($query1,$query2,$query3,$query5, $rows1,$rows2,$rows3,$qr2,
                $qr3,$rows4,$rows5);
        return $qr1;
    }
    
     /*
     * Get gross hold balance if queried date is today, for gh balance  per cutoff pagination
     */
    public function getGrossHoldBalance($sort, $dir, $startdate,$enddate) {
        if(isset($_GET['site']) && $_GET['site'] == '') {
            // to get beginning balance
            $query1 = "SELECT srb.SiteID, srb.PrevBalance, ad.Name, sd.SiteDescription, s.SiteCode, s.POSAccountNo FROM siterunningbalance srb " . 
                    "INNER JOIN sites s ON s.SiteID = srb.SiteID " . 
                    "INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID " .
                    "INNER JOIN sitedetails sd ON sd.SiteID = srb.SiteID  where TransactionDate >= '$startdate' and " . 
                    "TransactionDate < '$enddate' order by srb.TransactionDate";
            
            // to get sum of dep,reload and withdrawal
            $query2 = "SELECT SiteID, COALESCE(sum(Deposit),0) as InitialDeposit,sum(Reload) as Reload,sum(Withdrawal) as Redemption FROM siterunningbalance " . 
                    "where TransactionDate >= '$startdate' and TransactionDate < '$enddate' GROUP BY SiteID";  
            
            // to get collection 
            $query3 = "select SiteID, COALESCE(Sum(Amount),0) as Collection from siteremittance where StatusUpdateDate >= '$startdate' and " . 
                    "StatusUpdateDate < '$enddate' GROUP BY SiteID";
    
            // to get replenishment
            $query4 = "select SiteID, COALESCE(Sum(Amount),0) as Replenishment from replenishments where DateCredited >= '$startdate' and " . 
                    "DateCredited < '$enddate' GROUP BY SiteID";   
            
            //to get manual redemption
            $query5 = "SELECT SiteID, SUM(ActualAmount) AS ActualAmount FROM manualredemptions " . 
                    "WHERE TransactionDate >= '$startdate' AND TransactionDate < '$enddate' GROUP BY SiteID";
            
        } else {
            // to get beginning balance
            $query1 = "SELECT srb.SiteID, srb.PrevBalance, ad.Name, sd.SiteDescription, s.SiteCode, s.POSAccountNo FROM siterunningbalance srb " . 
                    "INNER JOIN sites s ON s.SiteID = srb.SiteID " . 
                    "INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID " .
                    "INNER JOIN sitedetails sd ON sd.SiteID = srb.SiteID  where TransactionDate >= '$startdate' and " . 
                    "TransactionDate < '$enddate' AND srb.SiteID = '" . $_GET['site'] . "'  order by srb.TransactionDate  ";
            
            // to get sum of dep,reload and withdrawal
            $query2 = "SELECT SiteID, COALESCE(sum(Deposit),0) as InitialDeposit,sum(Reload) as Reload,sum(Withdrawal) as Redemption FROM siterunningbalance " . 
                    "where TransactionDate >= '$startdate' and TransactionDate < '$enddate' and SiteID = " . $_GET['site'];

            // to get collection 
            $query3 = "SELECT SiteID, COALESCE(Sum(Amount),0) as Collection from siteremittance where StatusUpdateDate >= '$startdate' and " . 
                    "StatusUpdateDate < '$enddate' and SiteID = " . $_GET['site'];

            // to get replenishment
            $query4 = "SELECT SiteID, COALESCE(Sum(Amount),0) as Replenishment from replenishments where DateCredited >= '$startdate' and " . 
                    "DateCredited < '$enddate' and SiteID = " . $_GET['site'];
            
            //to get manual redemption
            $query5 = "SELECT SiteID, SUM(ActualAmount) AS ActualAmount FROM manualredemptions " . 
                    "WHERE SiteID = '".$_GET['site']."' AND TransactionDate >= '$startdate' AND TransactionDate < '$enddate' GROUP BY SiteID";
        }

        // to get beginning balance, sitecode, sitename
        $this->prepare($query1);
        $this->execute(); 
        $rows1 = $this->fetchAllData();
        
        $qr1 = array();
        foreach($rows1 as $row1) {
            $qr1[$row1['SiteID']] = array('begbal'=>$row1['PrevBalance'],
                'sitecode'=>$row1['SiteCode'],'sitename'=>($row1['Name'] . ' ' . $row1['SiteDescription']), 'POSAccountNo' => $row1['POSAccountNo']);
            break;
        }
        
        // to get sum of dep,reload and withdrawal
        $this->prepare($query2);
        $this->execute();
        $rows2 = $this->fetchAllData();
        $qr2 = array();
        foreach($rows2 as $row2) {
            $qr2[$row2['SiteID']] = array('initialdeposit'=>$row2['InitialDeposit'],'reload'=>$row2['Reload'],'redemption'=>$row2['Redemption']);
        }
        
        // to get collection 
        $this->prepare($query3);
        $this->execute();
        $rows3 = $this->fetchAllData();
        $qr3 = array();
        foreach($rows3 as $row3) {
            $qr3[$row3['SiteID']] = $row3['Collection'];
        }
        
        $this->prepare($query4);
        $this->execute();
        $rows4 = $this->fetchAllData();
        $qr4 = array();
        foreach($rows4 as $row4) {
            $qr4[$row4['SiteID']] = $row4['Replenishment'];
        }
        
        $this->prepare($query5);
        $this->execute();
        $rows5 = $this->fetchAllData();
        $qr5 = array();
        foreach($rows5 as $row5)
        {
            $qr5[$row5['SiteID']] = $row5['ActualAmount'];
        }
        
        $consolidate = array();
        
        foreach($qr1 as $key => $q) {
            $collection = 0;
            if(isset($qr3[$key]))
                $collection = $qr3[$key];
            $replenishment = 0;
            if(isset($qr4[$key]))
                $replenishment = $qr4[$key];
            $vmanualredeem = 0;
            if(isset($qr5[$key]))
                $vmanualredeem = $qr5[$key];    
            $consolidate[] = array('siteid'=>$key,'sitename'=>$q['sitename'],'sitecode'=>$q['sitecode'],
                'begbal'=>$q['begbal'],'initialdep'=>$qr2[$key]['initialdeposit'],
                'reload'=>$qr2[$key]['reload'],'redemption'=>$qr2[$key]['redemption'],
                'collection'=>$collection,'replenishment'=>$replenishment, 'POSAccountNo' => $q['POSAccountNo'],
                'manualredemption'=>$vmanualredeem);
        }
        
        return $consolidate;
    }
    
     //added on 11/18/2011, for gross hold monitoring per cut off, for gh balance  per cutoff (PDF and Excel)
    public function getGrossHoldCutoff($startdate, $enddate, $zsitecode) 
    {
        //if site was selected All
        if($zsitecode == '') {
            // to get beginning balance
            $query1 = "SELECT srb.SiteID, srb.PrevBalance, ad.Name, sd.SiteDescription, s.SiteCode, s.POSAccountNo FROM siterunningbalance srb " . 
                    "INNER JOIN sites s ON s.SiteID = srb.SiteID " . 
                    "INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID " .
                    "INNER JOIN sitedetails sd ON sd.SiteID = srb.SiteID  where TransactionDate >= '$startdate' and " . 
                    "TransactionDate < '$enddate' order by srb.TransactionDate  ";
            
            // to get sum of dep,reload and withdrawal
            $query2 = "SELECT SiteID, COALESCE(sum(Deposit),0) as InitialDeposit,sum(Reload) as Reload,sum(Withdrawal) as Redemption FROM siterunningbalance " . 
                    "where TransactionDate >= '$startdate' and TransactionDate < '$enddate' GROUP BY SiteID";  
            
            // to get collection 
            $query3 = "select SiteID, COALESCE(Sum(Amount),0) as Collection from siteremittance where StatusUpdateDate >= '$startdate' and " . 
                    "StatusUpdateDate < '$enddate' GROUP BY SiteID";
    
            // to get replenishment
            $query4 = "select SiteID, COALESCE(Sum(Amount),0) as Replenishment from replenishments where DateCredited >= '$startdate' and " . 
                    "DateCredited < '$enddate' GROUP BY SiteID";    
            
            //to get manual redemption
            $query5 = "SELECT SiteID, SUM(ActualAmount) AS ActualAmount FROM manualredemptions " . 
                    "WHERE TransactionDate >= '$startdate' AND TransactionDate < '$enddate' GROUP BY SiteID";
            
        } else {
            // to get beginning balance
            $query1 = "SELECT srb.SiteID, srb.PrevBalance, ad.Name, sd.SiteDescription, s.SiteCode, s.POSAccountNo FROM siterunningbalance srb " . 
                    "INNER JOIN sites s ON s.SiteID = srb.SiteID " . 
                    "INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID " .
                    "INNER JOIN sitedetails sd ON sd.SiteID = srb.SiteID  where TransactionDate >= '$startdate' and " . 
                    "TransactionDate < '$enddate' AND srb.SiteID = '" . $zsitecode . "'  order by srb.TransactionDate  ";
            
            // to get sum of dep,reload and withdrawal
            $query2 = "SELECT SiteID, COALESCE(sum(Deposit),0) as InitialDeposit,sum(Reload) as Reload,sum(Withdrawal) as Redemption FROM siterunningbalance " . 
                    "where TransactionDate >= '$startdate' and TransactionDate < '$enddate' and SiteID = " . $zsitecode;

            // to get collection 
            $query3 = "SELECT SiteID, COALESCE(Sum(Amount),0) as Collection from siteremittance where StatusUpdateDate >= '$startdate' and " . 
                    "StatusUpdateDate < '$enddate' and SiteID = " . $zsitecode;

            // to get replenishment
            $query4 = "SELECT SiteID, COALESCE(Sum(Amount),0) as Replenishment from replenishments where DateCredited >= '$startdate' and " . 
                    "DateCredited < '$enddate' and SiteID = " . $zsitecode;
            
             //to get manual redemption
            $query5 = "SELECT SiteID, SUM(ActualAmount) AS ActualAmount FROM manualredemptions " . 
                    "WHERE SiteID = '".$zsitecode."' AND TransactionDate >= '$startdate' AND TransactionDate < '$enddate' GROUP BY SiteID";
        }

        // to get beginning balance, sitecode, sitename
        $this->prepare($query1);
        $this->execute(); 
        $rows1 = $this->fetchAllData();
        $qr1 = array();
        foreach($rows1 as $row1) {
            $qr1[$row1['SiteID']] = array('begbal'=>$row1['PrevBalance'],
                'sitecode'=>$row1['SiteCode'],'sitename'=>($row1['Name'] . ' ' . $row1['SiteDescription']), 'POSAccountNo' => $row1['POSAccountNo']);
            break;
        }
        
        // to get sum of dep,reload and withdrawal
        $this->prepare($query2);
        $this->execute();
        $rows2 = $this->fetchAllData();
        $qr2 = array();
        foreach($rows2 as $row2) {
            $qr2[$row2['SiteID']] = array('initialdeposit'=>$row2['InitialDeposit'],'reload'=>$row2['Reload'],'redemption'=>$row2['Redemption']);
        }
        
        // to get collection 
        $this->prepare($query3);
        $this->execute();
        $rows3 = $this->fetchAllData();
        $qr3 = array();
        foreach($rows3 as $row3) {
            $qr3[$row3['SiteID']] = $row3['Collection'];
        }
        
        $this->prepare($query4);
        $this->execute();
        $rows4 = $this->fetchAllData();
        $qr4 = array();
        foreach($rows4 as $row4) {
            $qr4[$row4['SiteID']] = $row4['Replenishment'];
        }
        
        $this->prepare($query5);
        $this->execute();
        $rows5 = $this->fetchAllData();
        $qr5 = array();
        foreach($rows5 as $row5)
        {
            $qr5[$row5['SiteID']] = $row5['ActualAmount'];
        }
        
        $consolidate = array();
        
        foreach($qr1 as $key => $q) {
            $collection = 0;
            if(isset($qr3[$key]))
                $collection = $qr3[$key];
            $replenishment = 0;
            if(isset($qr4[$key]))
                $replenishment = $qr4[$key];
            $vmanualredeem = 0;
            if(isset($qr5[$key]))
                $vmanualredeem = $qr5[$key];        
            $consolidate[] = array('siteid'=>$key,'sitename'=>$q['sitename'],'sitecode'=>$q['sitecode'],
                'begbal'=>$q['begbal'],'initialdep'=>$qr2[$key]['initialdeposit'],
                'reload'=>$qr2[$key]['reload'],'redemption'=>$qr2[$key]['redemption'],
                'collection'=>$collection,'replenishment'=>$replenishment, 'POSAccountNo' => $q['POSAccountNo'],
                'manualredemption'=>$vmanualredeem);
        }
        return $consolidate;
    }
    
    //previous gh balance  per cutoff (PDF and Excel)
    public function getoldGHCutoff($startdate, $enddate, $zsiteid) 
    {
       switch ($zsiteid)
       {
           //If no selected site
           case '':
                $query1 = "SELECT sgc.SiteID, sgc.BeginningBalance, sgc.EndingBalance, ad.Name, sd.SiteDescription, 
                            s.SiteCode, s.POSAccountNo,sgc.DateFirstTransaction,sgc.DateLastTransaction,sgc.ReportDate,
                            sgc.DateCutOff,sgc.Deposit AS InitialDeposit, sgc.Reload AS Reload , sgc.Withdrawal AS Redemption                       
                            FROM sitegrossholdcutoff sgc
                            INNER JOIN sites s ON s.SiteID = sgc.SiteID
                            INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID
                            INNER JOIN sitedetails sd ON sd.SiteID = sgc.SiteID
                            WHERE sgc.DateCutOff > ? AND sgc.DateCutOff <= ?
                            ORDER BY s.SiteCode, sgc.DateFirstTransaction";          

                $query2 = "SELECT SiteID,DateCredited,AmountConfirmed FROM grossholdconfirmation 
                    WHERE DateCredited  >= ? AND DateCredited < ? ";

                $query3 = "SELECT SiteID,Amount,StatusUpdateDate FROM siteremittance 
                    WHERE StatusUpdateDate  >= ? AND StatusUpdateDate < ? ";

                $query5 = "SELECT SiteID, ActualAmount AS ActualAmount,TransactionDate FROM manualredemptions " . 
                        "WHERE TransactionDate >= ? AND TransactionDate < ? ";   
                
                $query6 = "SELECT 

                                -- DEPOSIT CASH --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN
                                        CASE tr.PaymentType
                                          WHEN 2 THEN 0 -- Coupon
                                          ELSE -- Not Coupon
                                                CASE IFNULL(tr.StackerSummaryID, '')
                                                  WHEN '' THEN SUM(tr.Amount) -- Cash
                                                  ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                                        (SELECT IFNULL(SUM(Amount), 0)
                                                        FROM stackermanagement.stackerdetails sdtls
                                                        WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                                  AND sdtls.TransactionType = 1
                                                                  AND sdtls.PaymentType = 0)  -- Deposit, Cash
                                                END
                                        END
                                  ELSE 0 -- Not Deposit
                                END As DepositCash,
                                
                                -- DEPOSIT COUPON --
                                CASE tr.TransactionType
                                    WHEN 'D' THEN
                                      CASE tr.PaymentType
                                        WHEN 2 THEN SUM(tr.Amount) -- Coupon
                                        ELSE 0
                                      END
                                    ELSE 0
                                END As DepositCoupon,
                                
                                -- DEPOSIT TICKET --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN 0 -- Coupon
                                      ELSE -- Not Coupon
                                        CASE IFNULL(tr.StackerSummaryID, '')
                                          WHEN '' THEN 0 -- Cash
                                          ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                            (SELECT IFNULL(SUM(Amount), 0)
                                            FROM stackermanagement.stackerdetails sdtls
                                            WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                  AND sdtls.TransactionType = 1
                                                  AND sdtls.PaymentType = 2)  -- Deposit, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Deposit
                                END As DepositTicket,
                                
                                -- RELOAD COUPON --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN SUM(tr.Amount) -- Coupon
                                      ELSE 0
                                    END
                                  ELSE 0
                                END As ReloadCoupon,

                                -- RELOAD CASH --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                        CASE tr.PaymentType
                                          WHEN 2 THEN 0 -- Coupon
                                          ELSE -- Not Coupon
                                                CASE IFNULL(tr.StackerSummaryID, '')
                                                  WHEN '' THEN SUM(tr.Amount) -- Cash
                                                  ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                                        (SELECT IFNULL(SUM(Amount), 0)
                                                        FROM stackermanagement.stackerdetails sdtls
                                                        WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                                  AND sdtls.TransactionType = 2
                                                                  AND sdtls.PaymentType = 0)  -- Reload, Cash
                                                END
                                        END
                                  ELSE 0 -- Not Reload
                                END As ReloadCash,
                                
                                -- RELOAD TICKET --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN 0 -- Coupon
                                      ELSE -- Not Coupon
                                        CASE IFNULL(tr.StackerSummaryID, '')
                                          WHEN '' THEN 0 -- Cash
                                          ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                            (SELECT IFNULL(SUM(Amount), 0)
                                            FROM stackermanagement.stackerdetails sdtls
                                            WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                  AND sdtls.TransactionType = 2
                                                  AND sdtls.PaymentType = 2)  -- Reload, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Reload
                                END As ReloadTicket,

                                -- REDEMPTION CASHIER --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN
                                        CASE a.AccountTypeID
                                          WHEN 4 THEN SUM(tr.Amount) -- Cashier
                                          ELSE 0
                                        END -- Genesis
                                  ELSE 0 --  Not Redemption
                                END As RedemptionCashier,
                                
                                -- REDEMPTION GENESIS --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN
                                    CASE a.AccountTypeID
                                      WHEN 15 THEN SUM(tr.Amount) -- Genesis
                                      ELSE 0
                                    END -- Cashier
                                  ELSE 0 -- Not Redemption
                                END As RedemptionGenesis,

                                ts.DateStarted, ts.DateEnded, tr.SiteID
                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                INNER JOIN npos.sites s ON tr.SiteID = s.SiteID
                                WHERE tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY s.POSAccountNo ";

                // to get beginning balance, sitecode, sitename
                $this->prepare($query1);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->execute(); 
                $rows1 = $this->fetchAllData();        
                $qr1 = array();
                foreach($rows1 as $row1) {
                    $qr1[] = array('siteid'=>$row1['SiteID'],'begbal'=>$row1['BeginningBalance'],'endbal'=>$row1['EndingBalance'],
                        'sitecode'=>$row1['SiteCode'],'sitename'=>($row1['Name'] . ' ' . $row1['SiteDescription']), 'POSAccountNo' => $row1['POSAccountNo'],
                        'initialdep'=>'0.00','reload'=>'0.00','redemption'=>'0.00',
                        'datestart'=>$row1['DateFirstTransaction'],'datelast'=>$row1['DateLastTransaction'],
                        'reportdate'=>$row1['ReportDate'],'cutoff'=>$row1['DateCutOff'],'manualredemption'=>0,
                        'replenishment'=>0,'collection'=>0,'replenishment'=>0
                        );
                }

                // to get confirmation made by cashier from provincial sites
                $this->prepare($query2);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->execute();
                $rows2 = $this->fetchAllData();
                $qr2 = array();
                foreach($rows2 as $row2) 
                {
                    $qr2[] = array('siteid'=>$row2['SiteID'],'datecredit'=>$row2['DateCredited'],
                        'amount'=>$row2['AmountConfirmed']);
                }

                // to get deposits made by cashier from metro manila
                $this->prepare($query3);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->execute();
                $rows3 = $this->fetchAllData();
                $qr3 = array();
                foreach($rows3 as $row3) 
                {
                    $qr3[] = array('siteid'=>$row3['SiteID'],'datecredit'=>$row3['StatusUpdateDate'],
                        'amount'=>$row3['Amount']);
                }  
                // to get manual redemptions based on date range
                $this->prepare($query5);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->execute();
                $rows5 = $this->fetchAllData();
                $qr5 = array();
                
                foreach($rows5 as $row5)
                {
                    $qr5[] = array('siteid'=>$row5['SiteID'],'manualredemption'=>$row5['ActualAmount'],'mrtransdate'=>$row5['TransactionDate']);
                } 
                
                //Total the Deposit and Reload Cash, Deposit and Reload Coupons, Deposit and Reload Tickets in EGM
                //Total Redemption made by the cashier and the EGM
                $this->prepare($query6);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->execute();  
                $rows6 =  $this->fetchAllData();
                
                foreach ($rows6 as $row6) {
                    foreach ($qr1 as $keys => $value1) {
                        if($row6["SiteID"] == $value1["siteid"]){
                            if($row6["DepositCash"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositCash"];
                            if($row6["ReloadCash"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadCash"];
                            if($row6["RedemptionCashier"] != '0.00')
                                $qr1[$keys]["redemption"] = (float)$qr1[$keys]["redemption"] + (float)$row6["RedemptionCashier"];
                            if($row6["RedemptionGenesis"] != '0.00')
                                $qr1[$keys]["redemption"] = (float)$qr1[$keys]["redemption"] + (float)$row6["RedemptionGenesis"];
                            if($row6["DepositCoupon"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositCoupon"];
                            if($row6["ReloadCoupon"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadCoupon"];
                            if($row6["DepositTicket"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositTicket"];
                            if($row6["ReloadTicket"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadTicket"];
                            break;
                        }
                    }     
                }

                $ctr = 0;
                
                //prepare array data
                while($ctr < count($qr1))
                {
                    $ctr2 = 0; // counter for manual redemptions array
                    while($ctr2 < count($qr5))
                    {
                        if($qr1[$ctr]['siteid'] == $qr5[$ctr2]['siteid'])
                        {
                            $amount = 0;
                            if(($qr5[$ctr2]['mrtransdate'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr5[$ctr2]['mrtransdate'] < $qr1[$ctr]['cutoff']))
                            {              
                                 if($qr1[$ctr]['manualredemption'] == 0) 
                                     $qr1[$ctr]['manualredemption'] = $qr5[$ctr2]['manualredemption'];
                                 else
                                 {
                                     $amount = $qr1[$ctr]['manualredemption'];
                                     $qr1[$ctr]['manualredemption'] = $amount + $qr5[$ctr2]['manualredemption'];
                                 }
                            }
                        }
                        $ctr2 = $ctr2 + 1;
                    }            

                    $ctr3 = 0; //counter for grossholdconfirmation array
                    while($ctr3 < count($qr2))
                    {
                        if($qr1[$ctr]['siteid'] == $qr2[$ctr3]['siteid'])
                        {
                            $amount = 0;
                            if(($qr2[$ctr3]['datecredit'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr2[$ctr3]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['replenishment'] == 0) 
                                    $qr1[$ctr]['replenishment'] = $qr2[$ctr3]['amount'];
                                else
                                {
                                    $amount = $qr1[$ctr]['replenishment'];
                                    $qr1[$ctr]['replenishment'] = $amount + $qr2[$ctr3]['amount'];
                                }                         
                            }                   
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr4 = 0; //counter for collection array
                    while($ctr4 < count($qr3))
                    {
                        if($qr1[$ctr]['siteid'] == $qr3[$ctr4]['siteid'])
                        {         
                            $amount = 0;
                            if(($qr3[$ctr4]['datecredit'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr3[$ctr4]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['collection'] == 0) 
                                {
                                    $qr1[$ctr]['collection'] = $qr3[$ctr4]['amount'];         
                                }      
                                else
                                {
                                    $amount = $qr1[$ctr]['collection'];
                                    $qr1[$ctr]['collection'] = $amount + $qr3[$ctr4]['amount'];                                
                                }                           
                            }                    
                        }
                        $ctr4 = $ctr4 + 1;
                    }            
                    $ctr = $ctr + 1;
                }                 
               break;
           case $zsiteid > 0 :
                $query1 = "SELECT sgc.SiteID, sgc.BeginningBalance, sgc.EndingBalance, ad.Name, sd.SiteDescription, 
                            s.SiteCode, s.POSAccountNo,sgc.DateFirstTransaction,sgc.DateLastTransaction,sgc.ReportDate,
                            sgc.DateCutOff,sgc.Deposit AS InitialDeposit, sgc.Reload AS Reload , sgc.Withdrawal AS Redemption                       
                            FROM sitegrossholdcutoff sgc
                            INNER JOIN sites s ON s.SiteID = sgc.SiteID
                            INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID
                            INNER JOIN sitedetails sd ON sd.SiteID = sgc.SiteID
                            WHERE sgc.DateCutOff > ? AND sgc.DateCutOff <= ?
                            AND sgc.SiteID = ?
                            ORDER BY s.SiteCode, sgc.DateFirstTransaction";          

                $query2 = "SELECT SiteID,DateCredited,AmountConfirmed FROM grossholdconfirmation 
                    WHERE DateCredited  >= ? AND DateCredited < ?  AND SiteID = ?";

                $query3 = "SELECT SiteID,Amount,StatusUpdateDate FROM siteremittance 
                    WHERE StatusUpdateDate  >= ? AND StatusUpdateDate < ?  AND SiteID = ?";

                

                $query5 = "SELECT SiteID, ActualAmount AS ActualAmount,TransactionDate FROM manualredemptions " . 
                        "WHERE TransactionDate >= ? AND TransactionDate < ? AND SiteID = ? ";   
                
                $query6 = "SELECT 

                                -- DEPOSIT CASH --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN
                                        CASE tr.PaymentType
                                          WHEN 2 THEN 0 -- Coupon
                                          ELSE -- Not Coupon
                                                CASE IFNULL(tr.StackerSummaryID, '')
                                                  WHEN '' THEN SUM(tr.Amount) -- Cash
                                                  ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                                        (SELECT IFNULL(SUM(Amount), 0)
                                                        FROM stackermanagement.stackerdetails sdtls
                                                        WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                                  AND sdtls.TransactionType = 1
                                                                  AND sdtls.PaymentType = 0)  -- Deposit, Cash
                                                END
                                        END
                                  ELSE 0 -- Not Deposit
                                END As DepositCash,
                                
                                -- DEPOSIT COUPON --
                                CASE tr.TransactionType
                                    WHEN 'D' THEN
                                      CASE tr.PaymentType
                                        WHEN 2 THEN SUM(tr.Amount) -- Coupon
                                        ELSE 0
                                      END
                                    ELSE 0
                                END As DepositCoupon,
                                
                                -- DEPOSIT TICKET --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN 0 -- Coupon
                                      ELSE -- Not Coupon
                                        CASE IFNULL(tr.StackerSummaryID, '')
                                          WHEN '' THEN 0 -- Cash
                                          ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                            (SELECT IFNULL(SUM(Amount), 0)
                                            FROM stackermanagement.stackerdetails sdtls
                                            WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                  AND sdtls.TransactionType = 1
                                                  AND sdtls.PaymentType = 2)  -- Deposit, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Deposit
                                END As DepositTicket,
                                
                                -- RELOAD COUPON --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN SUM(tr.Amount) -- Coupon
                                      ELSE 0
                                    END
                                  ELSE 0
                                END As ReloadCoupon,

                                -- RELOAD CASH --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                        CASE tr.PaymentType
                                          WHEN 2 THEN 0 -- Coupon
                                          ELSE -- Not Coupon
                                                CASE IFNULL(tr.StackerSummaryID, '')
                                                  WHEN '' THEN SUM(tr.Amount) -- Cash
                                                  ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                                        (SELECT IFNULL(SUM(Amount), 0)
                                                        FROM stackermanagement.stackerdetails sdtls
                                                        WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                                  AND sdtls.TransactionType = 2
                                                                  AND sdtls.PaymentType = 0)  -- Reload, Cash
                                                END
                                        END
                                  ELSE 0 -- Not Reload
                                END As ReloadCash,
                                
                                -- RELOAD TICKET --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN 0 -- Coupon
                                      ELSE -- Not Coupon
                                        CASE IFNULL(tr.StackerSummaryID, '')
                                          WHEN '' THEN 0 -- Cash
                                          ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                            (SELECT IFNULL(SUM(Amount), 0)
                                            FROM stackermanagement.stackerdetails sdtls
                                            WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                  AND sdtls.TransactionType = 2
                                                  AND sdtls.PaymentType = 2)  -- Reload, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Reload
                                END As ReloadTicket,

                                -- REDEMPTION CASHIER --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN
                                        CASE a.AccountTypeID
                                          WHEN 4 THEN SUM(tr.Amount) -- Cashier
                                          ELSE 0
                                        END -- Genesis
                                  ELSE 0 --  Not Redemption
                                END As RedemptionCashier,
                                
                                -- REDEMPTION GENESIS --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN
                                    CASE a.AccountTypeID
                                      WHEN 15 THEN SUM(tr.Amount) -- Genesis
                                      ELSE 0
                                    END -- Cashier
                                  ELSE 0 -- Not Redemption
                                END As RedemptionGenesis,

                                ts.DateStarted, ts.DateEnded, tr.SiteID
                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                INNER JOIN npos.sites s ON tr.SiteID = s.SiteID
                                WHERE tr.SiteID = ?
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY s.POSAccountNo";

                // to get beginning balance, sitecode, sitename
                $this->prepare($query1);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                
                $this->execute(); 
                $rows1 = $this->fetchAllData();        
                $qr1 = array();
                foreach($rows1 as $row1) {
                    $qr1[] = array('siteid'=>$row1['SiteID'],'begbal'=>$row1['BeginningBalance'],'endbal'=>$row1['EndingBalance'],
                        'sitecode'=>$row1['SiteCode'],'sitename'=>($row1['Name'] . ' ' . $row1['SiteDescription']), 'POSAccountNo' => $row1['POSAccountNo'],
                        'initialdep'=>'0.00','reload'=>'0.00','redemption'=>'0.00',
                        'datestart'=>$row1['DateFirstTransaction'],'datelast'=>$row1['DateLastTransaction'],
                        'reportdate'=>$row1['ReportDate'],'cutoff'=>$row1['DateCutOff'],'manualredemption'=>0,
                        'replenishment'=>0,'collection'=>0,'replenishment'=>0
                        );
                }

                // to get confirmation made by cashier from provincial sites
                $this->prepare($query2);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                  
                $this->execute();
                $rows2 = $this->fetchAllData();
                
                $qr2 = array();
                foreach($rows2 as $row2) 
                {
                    $qr2[] = array('siteid'=>$row2['SiteID'],'datecredit'=>$row2['DateCredited'],
                        'amount'=>$row2['AmountConfirmed']);
                }

                // to get deposits made by cashier from metro manila
                $this->prepare($query3);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                  
                $this->execute();
                $rows3 = $this->fetchAllData();
                
                $qr3 = array();
                foreach($rows3 as $row3) 
                {
                    $qr3[] = array('siteid'=>$row3['SiteID'],'datecredit'=>$row3['StatusUpdateDate'],
                        'amount'=>$row3['Amount']);
                }  
                
                // to get manual redemptions based on date range
                $this->prepare($query5);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                  
                $this->execute();
                $rows5 = $this->fetchAllData();
                
                $qr5 = array();
                foreach($rows5 as $row5)
                {
                    $qr5[] = array('siteid'=>$row5['SiteID'],'manualredemption'=>$row5['ActualAmount'],'mrtransdate'=>$row5['TransactionDate']);
                } 
                
                //Total the Deposit and Reload Cash, Deposit and Reload Coupons, Deposit and Reload Tickets in EGM
                //Total Redemption made by the cashier and the EGM
                $this->prepare($query6);
                $this->bindparameter(1, $zsiteid);
                $this->bindparameter(2, $startdate);
                $this->bindparameter(3, $enddate);
                $this->execute();  
                $rows6 =  $this->fetchAllData();
                
                foreach ($rows6 as $row6) {
                    foreach ($qr1 as $keys => $value1) {
                        if($row6["SiteID"] == $value1["siteid"]){
                            if($row6["DepositCash"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositCash"];
                            if($row6["ReloadCash"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadCash"];
                            if($row6["RedemptionCashier"] != '0.00')
                                $qr1[$keys]["redemption"] = (float)$qr1[$keys]["redemption"] + (float)$row6["RedemptionCashier"];
                            if($row6["RedemptionGenesis"] != '0.00')
                                $qr1[$keys]["redemption"] = (float)$qr1[$keys]["redemption"] + (float)$row6["RedemptionGenesis"];
                            if($row6["DepositCoupon"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositCoupon"];
                            if($row6["ReloadCoupon"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadCoupon"];
                            if($row6["DepositTicket"] != '0.00')
                                $qr1[$keys]["initialdep"] = (float)$qr1[$keys]["initialdep"] + (float)$row6["DepositTicket"];
                            if($row6["ReloadTicket"] != '0.00')
                                $qr1[$keys]["reload"] = (float)$qr1[$keys]["reload"] + (float)$row6["ReloadTicket"];
                            break;
                        }
                    }     
                }
                
                $ctr = 0;
                
                //prepare array data
                while($ctr < count($qr1))
                {
                    $ctr2 = 0; // counter for manual redemptions array
                    while($ctr2 < count($qr5))
                    {
                        if($qr1[$ctr]['siteid'] == $qr5[$ctr2]['siteid'])
                        {
                            $amount = 0;
                            if(($qr5[$ctr2]['mrtransdate'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr5[$ctr2]['mrtransdate'] < $qr1[$ctr]['cutoff']))
                            {              
                                 if($qr1[$ctr]['manualredemption'] == 0) 
                                     $qr1[$ctr]['manualredemption'] = $qr5[$ctr2]['manualredemption'];
                                 else
                                 {
                                     $amount = $qr1[$ctr]['manualredemption'];
                                     $qr1[$ctr]['manualredemption'] = $amount + $qr5[$ctr2]['manualredemption'];
                                 }
                            }
                        }
                        $ctr2 = $ctr2 + 1;
                    }            

                    $ctr3 = 0; //counter for grossholdconfirmation array
                    while($ctr3 < count($qr2))
                    {
                        if($qr1[$ctr]['siteid'] == $qr2[$ctr3]['siteid'])
                        {
                            $amount = 0;
                            if(($qr2[$ctr3]['datecredit'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr2[$ctr3]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['replenishment'] == 0) 
                                    $qr1[$ctr]['replenishment'] = $qr2[$ctr3]['replenishment'];
                                else
                                {
                                    $amount = $qr1[$ctr]['replenishment'];
                                    $qr1[$ctr]['replenishment'] = $amount + $qr2[$ctr3]['replenishment'];
                                }                         
                            }                   
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr4 = 0; //counter for collection array
                    while($ctr4 < count($qr3))
                    {
                        if($qr1[$ctr]['siteid'] == $qr3[$ctr4]['siteid'])
                        {         
                            $amount = 0;
                            if(($qr3[$ctr4]['datecredit'] >= $qr1[$ctr]['reportdate']." 06:00:00") && ($qr3[$ctr4]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['collection'] == 0) 
                                {
                                    $qr1[$ctr]['collection'] = $qr3[$ctr4]['amount'];         
                                }      
                                else
                                {
                                    $amount = $qr1[$ctr]['collection'];
                                    $qr1[$ctr]['collection'] = $amount + $qr3[$ctr4]['amount'];                                
                                }                           
                            }                    
                        }
                        $ctr4 = $ctr4 + 1;
                    }            
                    $ctr = $ctr + 1;
                }                 
               break;
       }
  
        unset($query1,$query2,$query3,$query5, $rows1,$rows2,$rows3,$qr2,
                $qr3,$rows4,$rows5);
        return $qr1;
    }
   
}

?>