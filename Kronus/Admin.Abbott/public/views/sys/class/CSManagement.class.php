<?php

/*
 * Created By: Edson L. Perez
 * Date Created: July 6, 2011
 * Purpose: class For Manual Redemption
 */

include "DbHandler.class.php";

class CSManagement extends DBHandler{
    
      public function __construct($sconectionstring)
      {
          parent::__construct($sconectionstring);
      }
      
      //select all terminal based from sites to populate combo box
      function viewterminals($zsiteID)
      {
          if($zsiteID > 0)
          {
              $stmt = "SELECT TerminalID, TerminalCode FROM terminals where SiteID = '".$zsiteID."' AND (Status = 0 OR 1) ORDER BY TerminalID ASC";
          }
          else
	  {
              $stmt = "SELECT TerminalID, TerminalCode FROM terminals WHERE (Status = 0 OR 1) ORDER BY TerminalID ASC";
          }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
      //get all sites
      function getsites()
      {
          return $this->getallsites();
      }
      
      //get all available services on all terminals
      function viewservices($zTerminalID)
      {
          if($zTerminalID > 0)
          {
              $stmt = "SELECT DISTINCT a.ServiceID, b.ServiceName FROM terminalservices AS a 
                  INNER JOIN ref_services AS b ON a.ServiceID = b.ServiceID 
                  WHERE a.TerminalID = '".$zTerminalID."' AND a.Status = 1 AND b.UserMode = 0
                  ORDER BY ServiceName ASC";
          }
          else
          {
              $stmt = "SELECT DISTINCT a.ServiceID, b.ServiceName FROM terminalservices AS a 
                       INNER JOIN ref_services AS b ON a.ServiceID = b.ServiceID 
                       WHERE b.UserMode = 0 ORDER BY ServiceName ASC";
          }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
      //get service terminals mapped (for MG)
      function getmglogin($zTerminalID){
          $stmt = "SELECT ServiceAgentID, ServiceTerminalAccount FROM serviceterminals AS a 
              INNER JOIN terminalmapping AS b ON a.ServiceTerminalID = b.ServiceTerminalID 
              WHERE TerminalID = '".$zTerminalID."'";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
      //get agent session ID based from agentid (for MG)
      function getagentsession($zAgentID)
      {
          $stmt = "SELECT ServiceAgentSessionID from serviceagentsessions where ServiceAgentID = '".$zAgentID."'";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
   
      //get terminal name and terminal code
      function getterminalvalues($zterminalID)
      {
          $stmt = "SELECT TerminalName, TerminalCode FROM terminals WHERE TerminalID = ? ORDER BY TerminalCode ASC";
          $this->prepare($stmt);
          $this->bindparameter(1, $zterminalID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      //get cashier passkey information
      function getcashierpasskey($zcashierID)
      {
          $stmt = "SELECT acc.Passkey, acc.DatePasskeyIssued, acc.DatePasskeyExpires, det.Email
                   FROM accounts acc INNER JOIN accountdetails det ON acc.AID = det.AID WHERE acc.AID = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $zcashierID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      function getcashierpersite($zsiteID)
      {
          $stmt = "SELECT a.AID,b.UserName from siteaccounts a 
                   INNER JOIN accounts b on a.AID = b.AID 
                   WHERE a.SiteID = ? AND b.AccountTypeID = 4 AND b.Status = 1";
          $this->prepare($stmt);
          $this->bindparameter(1,$zsiteID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function getterminalcredentials($zterminalID, $zserviceID)
      {
                $stmt = "SELECT ServicePassword FROM terminalservices 
                             WHERE ServiceID = ? AND TerminalID = ? 
                             AND Status = 1 AND isCreated = 1";
            $this->prepare($stmt);
            $this->bindparameter(1, $zserviceID);
            $this->bindparameter(2, $zterminalID);
            $this->execute();
            return $this->fetchData();
      }
      
       public function viewTerminalID($zterminalcode)
        {
            $stmt = "SELECT TerminalID FROM terminals WHERE TerminalCode = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zterminalcode);
            $this->execute();
            return $this->fetchData();
        }
        
        /**
         * temporary
         * @return type 
         */
        public function getLastInsertedID()
        {
            $stmt = "SELECT MAX(ManualRedemptionsID) AS manualredeem FROM manualredemptions";
            $this->prepare($stmt);
            $this->execute();
            return $this->fetchData();
        }
        
        /**
         * @author Gerardo V. Jagolino Jr.
         * @param int $serviceid
         * @return array 
         * get service name and status of a certain service provider using its id
         */
        public function getCasinoName($serviceid)
        {
            $stmt = "SELECT ServiceName, Status FROM ref_services WHERE ServiceID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $serviceid);
            $this->execute();
            return $this->fetchAllData();
        }
        
        
         /**
         * @author Gerardo V. Jagolino Jr.
         * @param int $serviceid
         * @return array 
         * get service name and status of a certain service provider using its id
         */
        public function getTransSummary($terminalid)
        {
            $stmt = "SELECT max(TransactionsSummaryID) as summaryID, MAX(LoyaltyCardNumber) as loyaltyCard FROM transactionsummary
                WHERE TerminalID = ? AND DateEnded <> 0";
            $this->prepare($stmt);
            $this->bindparameter(1, $terminalid);
            $this->execute();
            return $this->fetchAllData();
        }
        
        /**
         *ADDED FUNCTIONS FROM ApplicationSupport.class.php 
         * as of June 6, 2012
         * 
         */
        
        //get all services
        function getallservices($sort)
        {
            /**
             *Deprecated as of June 13, 2012 
             */
            /*$stmt = "SELECT * FROM ref_services WHERE Status = 1 ORDER BY $sort";
            $this->prepare($stmt);
            $this->execute();
            return $this->fetchAllData();*/
            
            /**
             *Modified as of June 13, 2012
             * @author Marx Lenin Topico
             *  
             */
            $stmt = "SELECT * FROM ref_services WHERE Status = 1 ORDER BY $sort";
            $this->prepare($stmt);
            $this->execute();
            $record = $this->fetchAllData();
            $data = array();
            foreach($record as $r) {
                
                $data[$r["ServiceID"]] = $r;
                
            }
            
            return $data;
            
        }
        
        //Separated function for E-City Tracking in CS & AS for fetching all casino services disregarded whether the casino is already inactive.
        function getallservicesecitytrack($sort)
        {
            /**
             *Deprecated as of June 13, 2012 
             */
            /*$stmt = "SELECT * FROM ref_services WHERE Status = 1 ORDER BY $sort";
            $this->prepare($stmt);
            $this->execute();
            return $this->fetchAllData();*/
            
            /**
             *Modified as of June 13, 2012
             * @author Marx Lenin Topico
             *  
             */
            $stmt = "SELECT * FROM ref_services ORDER BY $sort";
            $this->prepare($stmt);
            $this->execute();
            $record = $this->fetchAllData();
            $data = array();
            foreach($record as $r) {
                
                $data[$r["ServiceID"]] = $r;
                
            }
            
            return $data;
            
        }
        
        //count all transactions to paginate, validate if status and transtype was selected
        function counttransactiondetails($zSiteID,$zTerminalID,$ztransstatus, $ztranstype, $zFrom,$zTo)
        {           
            $liststatus = array();
            foreach ($ztransstatus as $row)
            {
                $rstatus = $row;
                array_push($liststatus, $rstatus);
            }
            $status = implode(',', $liststatus);

            //validate if combo boxes of transaction status and transaction type are selected ALL 
            if($ztransstatus[0] == 'All' && $ztranstype == 'All')
            {
                $stmt = "SELECT COUNT(*) as count FROM transactiondetails td 
                    INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                    WHERE td.SiteID =? AND td.TerminalID =? AND td.DateCreated >=? 
                    AND td.DateCreated < ?";
                $this->prepare($stmt);
                $this->bindparameter(1,$zSiteID);
                $this->bindparameter(2,$zTerminalID);
                $this->bindparameter(3,$zFrom);
                $this->bindparameter(4,$zTo); 
            }
            //then if Transaction Status was selected any of its choices (Success, Failed) AND Transaction Type was selected all
            elseif($ztransstatus[0] <> 'All' && $ztranstype == 'All')
            {
                $stmt = "SELECT COUNT(*) as count FROM transactiondetails td 
                    INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                    WHERE td.SiteID =? AND td.TerminalID =? AND td.Status IN (".$status.") AND td.DateCreated >=? 
                    AND td.DateCreated < ?";
                $this->prepare($stmt);
                $this->bindparameter(1,$zSiteID);
                $this->bindparameter(2,$zTerminalID);
                $this->bindparameter(3,$zFrom);
                $this->bindparameter(4,$zTo); 
            }
            //then if Transaction Status was selected all AND Transaction Type was selected ano of its choices (Deposit, Reload, Withdraw)
            elseif($ztransstatus[0] == 'All' && $ztranstype <> 'All')
            {
                $stmt = "SELECT COUNT(*) as count FROM transactiondetails td 
                    INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                    WHERE td.SiteID =? AND td.TerminalID =? AND td.TransactionType = ? AND td.DateCreated >=? 
                    AND td.DateCreated < ?";
                $this->prepare($stmt);
                $this->bindparameter(1,$zSiteID);
                $this->bindparameter(2,$zTerminalID);
                $this->bindparameter(3,$ztranstype);
                $this->bindparameter(4,$zFrom);
                $this->bindparameter(5,$zTo); 
            }
            //then if both Transaction Status and Transaction type was selected of its choices, execute:
            else
            {
                $stmt = "SELECT COUNT(*) as count FROM transactiondetails td 
                    INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                    WHERE td.SiteID =? AND td.TerminalID =? AND td.Status IN (".$status.") AND td.TransactionType = ? AND td.DateCreated >=? 
                    AND td.DateCreated < ?";
                $this->prepare($stmt);
                $this->bindparameter(1,$zSiteID);
                $this->bindparameter(2,$zTerminalID);
                $this->bindparameter(3,$ztranstype);
                $this->bindparameter(4,$zFrom);
                $this->bindparameter(5,$zTo); 
            }

            $this->execute();
            unset($liststatus);
            return $this->fetchData();
        }
        
        //select all records based on parameters, validate if status and transtype was selected
        function selecttransactiondetails($zSiteID,$zTerminalID,$ztransstatus, $ztranstype, $zFrom,$zTo, $zStart, $zLimit)
        {
            $liststatus = array();
            foreach ($ztransstatus as $row)
            {
                $rstatus = $row;
                array_push($liststatus, $rstatus);
            }
            $status = implode(',', $liststatus); 

            //validate if combo boxes of transaction status and transaction type are selected ALL 
            if($ztransstatus[0] == 'All' && $ztranstype == 'All')
            {
                $stmt = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.SiteID, td.TerminalID, td.TransactionType, td.Amount,
                    td.DateCreated, td.Status, trl.ServiceTransactionID, a.UserName FROM transactiondetails td 
                    INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                    INNER JOIN accounts a ON td.CreatedByAID = a.AID
                    WHERE td.SiteID =? AND td.TerminalID =? AND td.DateCreated >= ? 
                    AND td.DateCreated <= ? ORDER BY td.DateCreated LIMIT ".$zStart.", ".$zLimit."";
                $this->prepare($stmt);
                $this->bindparameter(1,$zSiteID);
                $this->bindparameter(2,$zTerminalID);
                $this->bindparameter(3,$zFrom);
                $this->bindparameter(4,$zTo);   
            }
            //then if Transaction Status was selected any of its choices (Success, Failed) AND Transaction Type was selected all
            elseif($ztransstatus[0] <> 'All' && $ztranstype == 'All')
            {
                $stmt = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.SiteID, td.TerminalID, td.TransactionType, td.Amount,
                    td.DateCreated, td.Status, trl.ServiceTransactionID, a.UserName FROM transactiondetails td 
                    INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                    INNER JOIN accounts a ON td.CreatedByAID = a.AID
                    WHERE td.SiteID =? AND td.TerminalID =? AND td.Status IN (".$status.") AND td.DateCreated >= ? 
                    AND td.DateCreated <= ? ORDER BY td.DateCreated LIMIT ".$zStart.", ".$zLimit."";
                $this->prepare($stmt);
                $this->bindparameter(1,$zSiteID);
                $this->bindparameter(2,$zTerminalID);
                $this->bindparameter(3,$zFrom);
                $this->bindparameter(4,$zTo); 
            }
            //then if Transaction Status was selected all AND Transaction Type was selected ano of its choices (Deposit, Reload, Withdraw)
            elseif($ztransstatus[0] == 'All' && $ztranstype <> 'All')
            {
                $stmt = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.SiteID, td.TerminalID, td.TransactionType, td.Amount,
                    td.DateCreated, td.Status, trl.ServiceTransactionID, a.UserName FROM transactiondetails td 
                    INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                    INNER JOIN accounts a ON td.CreatedByAID = a.AID
                    WHERE td.SiteID =? AND td.TerminalID =? AND td.TransactionType = ? AND td.DateCreated >=? 
                    AND td.DateCreated <= ? ORDER BY td.DateCreated LIMIT ".$zStart.", ".$zLimit."";
                $this->prepare($stmt);
                $this->bindparameter(1,$zSiteID);
                $this->bindparameter(2,$zTerminalID);
                $this->bindparameter(3,$ztranstype);
                $this->bindparameter(4,$zFrom);
                $this->bindparameter(5,$zTo); 
            }
            //then if both Transaction Status and Transaction type was selected of its choices, execute:
            else
            {
                $stmt = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.SiteID, td.TerminalID, td.TransactionType, td.Amount,
                    td.DateCreated, td.Status, trl.ServiceTransactionID, a.UserName FROM transactiondetails td 
                    INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                    INNER JOIN accounts a ON td.CreatedByAID = a.AID
                    WHERE td.SiteID =? AND td.TerminalID =? AND td.Status IN (".$status.") AND td.TransactionType = ? AND td.DateCreated >=? 
                    AND td.DateCreated <= ? ORDER BY td.DateCreated LIMIT ".$zStart.", ".$zLimit."";
                $this->prepare($stmt);
                $this->bindparameter(1,$zSiteID);
                $this->bindparameter(2,$zTerminalID);
                $this->bindparameter(3,$ztranstype);
                $this->bindparameter(4,$zFrom);
                $this->bindparameter(5,$zTo);   
            }

            try {
                $this->execute();
            } catch(PDOException $e) {
                var_dump($e->getMessage()); exit;
            }
            unset($liststatus);
            return $this->fetchAllData();
        }
        
        //E-City Transaction Tracking: count transaction details
        function counttransdetails($zsiteID,$zterminalID, $zdatefrom, $zdateto, $zsummaryID)
        {
            //if summary ID was selected on the grid, execute;
            if($zsummaryID > 0)
            {
                $stmt = "SELECT COUNT(*) ctrtdetails 
                    FROM transactiondetails WHERE SiteID = ? AND TerminalID = ? 
                    AND DateCreated >= ? AND DateCreated <= ? AND TransactionSummaryID = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
                $this->bindparameter(5, $zsummaryID);
            }
            else
            {
                $stmt = "SELECT COUNT(*) ctrtdetails 
                    FROM transactiondetails WHERE SiteID = ? AND TerminalID = ? 
                    AND DateCreated >= ? AND DateCreated <= ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
            }

            $this->execute();
            return $this->fetchData();
        }
        
        //E-City Transaction Tracking: get transactiondetails data
        function gettransactiondetails($zsiteID,$zterminalID, $zdatefrom, $zdateto, $zsummaryID, $zstart, $zlimit, $zsort, $zdirection)
        {
            
            //if summary ID was selected on the grid, execute;
            if($zsummaryID > 0)
            {
                $stmt = "SELECT tr.TransactionReferenceID, tr.TransactionSummaryID, tr.SiteID, tr.TerminalID, 
                    tr.TransactionType, tr.Amount, tr.DateCreated, tr.ServiceID,a.UserName, ad.Name, tr.Status 
                    FROM transactiondetails tr inner join accounts a on tr.CreatedByAID = a.AID 
                    inner join accountdetails ad on a.AID = ad.AID
                    WHERE tr.SiteID = ? AND tr.TerminalID = ? 
                    AND tr.DateCreated >= ? AND tr.DateCreated <= ? AND tr.TransactionSummaryID = ? ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
                $this->bindparameter(5, $zsummaryID);
            }
            else
            {
                $stmt = "SELECT tr.TransactionReferenceID, tr.TransactionSummaryID, tr.SiteID, tr.TerminalID, 
                    tr.TransactionType, tr.Amount, tr.DateCreated, tr.ServiceID, a.UserName, ad.Name, tr.Status 
                    FROM transactiondetails tr inner join accounts a on tr.CreatedByAID = a.AID 
                    inner join accountdetails ad on a.AID = ad.AID
                    WHERE tr.SiteID = ? AND tr.TerminalID = ? 
                    AND tr.DateCreated >= ? AND tr.DateCreated <= ? ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
            }

            $this->execute();

            return $this->fetchAllData();
        }
        
        //E-City Transaction Summary, count transactions summary
        function counttranssummary($zsiteID, $zterminalID, $zdatefrom, $zdateto)
        {
            $stmt = "SELECT COUNT(*) ctrtsum
                    FROM transactionsummary ts 
                    INNER JOIN accounts acc ON ts.CreatedByAID = acc.AID
                    WHERE ts.SiteID = ? AND ts.TerminalID = ? AND ts.DateStarted >= ?
                    AND ts.DateStarted <= ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zsiteID);
            $this->bindparameter(2, $zterminalID);
            $this->bindparameter(3, $zdatefrom);
            $this->bindparameter(4, $zdateto);
            $this->execute();
            return $this->fetchData();
        }
        
        //E-City Transaction Summary, get details
        function gettransactionsummary($zsiteID, $zterminalID, $zdatefrom, $zdateto, $zstart, $zlimit, $zsort, $zdirection)
        {
            $stmt = "SELECT ts.TransactionsSummaryID, ts.SiteID, ts.TerminalID, tm.TerminalCode, ts.Deposit, ts.Reload,
                    ts.Withdrawal, ts.DateStarted, ts.DateEnded, acc.Name, s.POSAccountNo, s.SiteCode
                    FROM transactionsummary ts
                    INNER JOIN accountdetails acc ON ts.CreatedByAID = acc.AID
                    INNER JOIN terminals tm ON ts.TerminalID = tm.TerminalID
                    INNER JOIN sites s ON ts.SiteID = s.SiteID
                    WHERE ts.SiteID = ? AND ts.TerminalID = ? AND ts.DateStarted >= ?
                    AND ts.DateStarted <= ? ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
            $this->prepare($stmt);
            $this->bindparameter(1, $zsiteID);
            $this->bindparameter(2, $zterminalID);
            $this->bindparameter(3, $zdatefrom);
            $this->bindparameter(4, $zdateto);
            $this->execute();
            return $this->fetchAllData();
        }
        
        //E-City Transaction Request Logs, count
        function counttranslogslp($zsiteID, $zterminalID, $zdatefrom, $zdateto, $zsummaryID)
        {
            //if summaryID was selected 
            if($zsummaryID > 0)
            {
                $stmt = "SELECT COUNT(*) ctrlogs FROM transactionrequestlogslp 
                        WHERE SiteID = ? AND TerminalID = ? AND StartDate >= ? 
                        AND EndDate <= ? AND TransactionSummaryID = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
                $this->bindparameter(5, $zsummaryID);
            }
            else
            {
                $stmt = "SELECT COUNT(*) ctrlogs FROM transactionrequestlogslp 
                        WHERE SiteID = ? AND TerminalID = ? AND StartDate >= ? 
                        AND EndDate <= ?";
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
        function gettranslogslp($zsiteID, $zterminalID, $zdatefrom, $zdateto, $zsummaryID, $zstart, $zlimit, $zsort, $zdirection)
        {
            //if summary ID was selected
            if($zsummaryID > 0)
            {
                $stmt = "SELECT TransactionRequestLogLPID, TransactionReferenceID, Amount, StartDate, 
                    EndDate, TransactionType, TerminalID, Status, SiteID, ServiceTransactionID, 
                    ServiceStatus, ServiceTransferHistoryID, ServiceID FROM transactionrequestlogslp 
                    WHERE SiteID = ? AND TerminalID = ? AND StartDate >= ? AND EndDate <= ? 
                    AND TransactionSummaryID = ? ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
                $this->bindparameter(5, $zsummaryID);
            }
            else
            {
                $stmt = "SELECT TransactionRequestLogLPID, TransactionReferenceID, Amount, StartDate, 
                    EndDate, TransactionType, TerminalID, Status, SiteID, ServiceTransactionID, 
                    ServiceStatus, ServiceTransferHistoryID, ServiceID FROM transactionrequestlogslp 
                    WHERE SiteID = ? AND TerminalID = ? AND StartDate >= ? AND EndDate <= ? 
                    ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zterminalID);
                $this->bindparameter(3, $zdatefrom);
                $this->bindparameter(4, $zdateto);
            }

            $this->execute();
            return $this->fetchAllData();
        }
        
        public function getServiceGrpName($casino){
        $sql = "SELECT ServiceGroupName FROM ref_services rs 
            INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID 
            WHERE ServiceID = ?";
        $this->prepare($sql);
        $this->bindparameter(1, $casino);
        $this->execute();
        $serviceName = $this->fetchData();
        $serviceName = $serviceName['ServiceGroupName'];
        return $serviceName;
     }
}

?>

    