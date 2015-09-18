<?php

/* Created by : Lea Tuazon
 * Date Created : Jun 8, 2011
 * Modified By: Edson L. Perez
 */

include "DbHandler.class.php";

class ApplicationSupport extends DBHandler
{
      public function __construct($sconectionstring)
      {
          parent::__construct($sconectionstring);
      }
      
      
      /**
      * @author Gerardo V. Jagolino Jr.
      * @param $cardnumber, $ztransstatus, $ztranstype, $zFrom,$zTo, $zStart, $zLimit
      * @return array
      * for selecting manualredemptions of a certain loyaltycard number
      */
      function selectmanualredemptionsub($cardnumber, $ztransstatus, $zFrom,$zTo, $zStart, $zLimit)
      {
          
          //validate if combo boxes of transaction status and transaction type are selected ALL 
          if($ztransstatus == 'All')
          {
              $stmt = "SELECT mr.ManualRedemptionsID, s.SiteCode, tm.TerminalCode, rf.ServiceName, mr.TransactionID, mr.TransactionDate, mr.ReportedAmount, mr.Status 
                    FROM manualredemptions mr
                    INNER JOIN sites s ON mr.SiteID = s.SiteID
                    INNER JOIN terminals tm ON mr.TerminalID = tm.TerminalID
                    LEFT JOIN ref_services rf ON mr.ServiceID = rf.ServiceID
                    WHERE mr.LoyaltyCardNumber = ? AND mr.TransactionDate >=? 
                    AND mr.TransactionDate < ? ORDER BY mr.TransactionDate LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$zFrom);
              $this->bindparameter(3,$zTo);   
          }
          //then if Transaction Status was selected any of its choices (Success, Failed) AND Transaction Type was selected all
          elseif($ztransstatus <> 'All')
          {
              $stmt = "SELECT mr.ManualRedemptionsID, s.SiteCode, tm.TerminalCode, rf.ServiceName, mr.TransactionID, mr.TransactionDate, mr.ReportedAmount, mr.Status 
                    FROM manualredemptions mr
                    INNER JOIN sites s ON mr.SiteID = s.SiteID
                    INNER JOIN terminals tm ON mr.TerminalID = tm.TerminalID
                    LEFT JOIN ref_services rf ON mr.ServiceID = rf.ServiceID
                    WHERE mr.LoyaltyCardNumber = ? AND mr.Status = ? AND mr.TransactionDate >=? 
                    AND mr.TransactionDate < ? ORDER BY mr.TransactionDate LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztransstatus);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo); 
          }
          
          try {
            $this->execute();
          } catch(PDOException $e) {
              var_dump($e->getMessage()); exit;
          }
          return $this->fetchAllData();
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
              $stmt = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.SiteID, tm.TerminalCode, td.TerminalID, td.TransactionType, td.Amount, td.Option2 AS LoyaltyCard, rf.ServiceName,
                  td.DateCreated, td.Status, trl.ServiceTransactionID, a.Name FROM transactiondetails td 
                  INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                  INNER JOIN accountdetails a ON td.CreatedByAID = a.AID
                  INNER JOIN terminals tm ON td.TerminalID = tm.TerminalID
                  INNER JOIN ref_services rf ON rf.ServiceID = td.ServiceID
                  WHERE td.SiteID =? AND td.TerminalID =? AND Date(td.DateCreated) >=? 
                  AND Date(td.DateCreated) < ? ORDER BY td.DateCreated LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$zSiteID);
              $this->bindparameter(2,$zTerminalID);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo);   
          }
          //then if Transaction Status was selected any of its choices (Success, Failed) AND Transaction Type was selected all
          elseif($ztransstatus[0] <> 'All' && $ztranstype == 'All')
          {
              $stmt = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.SiteID, tm.TerminalCode, td.TerminalID, td.TransactionType, td.Amount, td.Option2 AS LoyaltyCard, rf.ServiceName,
                  td.DateCreated, td.Status, trl.ServiceTransactionID, a.Name FROM transactiondetails td 
                  INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                  INNER JOIN accountdetails a ON td.CreatedByAID = a.AID
                  INNER JOIN terminals tm ON td.TerminalID = tm.TerminalID
                  INNER JOIN ref_services rf ON rf.ServiceID = td.ServiceID
                  WHERE td.SiteID =? AND td.TerminalID =? AND td.Status IN (".$status.") AND Date(td.DateCreated) >=? 
                  AND Date(td.DateCreated) < ? ORDER BY td.DateCreated LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$zSiteID);
              $this->bindparameter(2,$zTerminalID);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo); 
          }
          //then if Transaction Status was selected all AND Transaction Type was selected ano of its choices (Deposit, Reload, Withdraw)
          elseif($ztransstatus[0] == 'All' && $ztranstype <> 'All')
          {
              $stmt = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.SiteID, tm.TerminalCode, td.TerminalID, td.TransactionType, td.Amount, td.Option2 AS LoyaltyCard, rf.ServiceName,
                  td.DateCreated, td.Status, trl.ServiceTransactionID, a.Name FROM transactiondetails td 
                  INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                  INNER JOIN accountdetails a ON td.CreatedByAID = a.AID
                  INNER JOIN terminals tm ON td.TerminalID = tm.TerminalID
                  INNER JOIN ref_services rf ON rf.ServiceID = td.ServiceID
                  WHERE td.SiteID =? AND td.TerminalID =? AND td.TransactionType = ? AND Date(td.DateCreated) >=? 
                  AND Date(td.DateCreated) < ? ORDER BY td.DateCreated LIMIT ".$zStart.", ".$zLimit."";
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
              $stmt = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.SiteID, tm.TerminalCode, td.TerminalID, td.TransactionType, td.Amount, td.Option2 AS LoyaltyCard, rf.ServiceName,
                  td.DateCreated, td.Status, trl.ServiceTransactionID, a.Name FROM transactiondetails td 
                  INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID 
                  INNER JOIN accountdetails a ON td.CreatedByAID = a.AID
                  INNER JOIN terminals tm ON td.TerminalID = tm.TerminalID
                  INNER JOIN ref_services rf ON rf.ServiceID = td.ServiceID
                  WHERE td.SiteID =? AND td.TerminalID =? AND td.Status IN (".$status.") AND td.TransactionType = ? AND Date(td.DateCreated) >=? 
                  AND Date(td.DateCreated) < ? ORDER BY td.DateCreated LIMIT ".$zStart.", ".$zLimit."";
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
      /**
       * Select e-SAFE Transaction Details for Manual e-SAFE Casino Fulfillment
       * @param type $vtransstatus
       * @param type $vFrom
       * @param type $vTo
       * @param type $start
       * @param type $limit
       * @param type $cardNumber
       * @param type $site
       * @return type
       * @author Mark Kenneth Esguerra
       */
      function selectewallettransdtls($vtransstatus, $vFrom,$vTo, $start = null, $limit = null, $cardNumber = null, $site = null)
      {
          //check if status is selected or specific
          if ($vtransstatus == "All")
          {
              if (!is_null($cardNumber))
              {
                  $stmt = "SELECT ewl.EwalletTransID, ewl.SiteID, ewl.TerminalID, ewl.TransType, 
                      rs.ServiceName, s.SiteCode, ewl.SiteID, ewl.StartDate, ewl.UserMode, ewl.ServiceTransactionID, 
                      ewl.Amount, ewl.Status, ewl.TransType, ewl.UpdatedByAID  
                      FROM npos.ewallettrans ewl 
                      INNER JOIN npos.sites s ON s.SiteID = ewl.SiteID 
                      INNER JOIN npos.ref_services rs ON ewl.ServiceID = rs.ServiceID 
                      WHERE ewl.LoyaltyCardNumber = ? 
                      AND ewl.Status IN (3, 4) AND ewl.StartDate >= ?
                      AND ewl.StartDate < ?
                      ORDER BY ewl.StartDate";
              }
              else
              {
                  $stmt = "SELECT ewl.EwalletTransID, ewl.SiteID, ewl.TerminalID, ewl.TransType, 
                      rs.ServiceName, s.SiteCode, ewl.SiteID, ewl.StartDate, ewl.UserMode, ewl.ServiceTransactionID, 
                      ewl.Amount, ewl.Status, ewl.TransType, ewl.UpdatedByAID  
                      FROM npos.ewallettrans ewl 
                      INNER JOIN npos.sites s ON s.SiteID = ewl.SiteID 
                      INNER JOIN npos.ref_services rs ON ewl.ServiceID = rs.ServiceID 
                      WHERE ewl.SiteID = ? 
                      AND ewl.Status IN (3, 4) AND ewl.StartDate >= ?
                      AND ewl.StartDate < ?
                      ORDER BY ewl.StartDate";
              }
              $this->prepare($stmt);
              (!is_null($cardNumber)) ? $this->bindparameter(1,$cardNumber) : $this->bindparameter(1, $site);
              $this->bindparameter(2, $vFrom);
              $this->bindparameter(3, $vTo);
          }
          else //selected status
          {
              if (!is_null($cardNumber))
              {
                  $stmt = "SELECT ewl.EwalletTransID, ewl.SiteID, ewl.TerminalID, ewl.TransType, 
                      rs.ServiceName, s.SiteCode, ewl.StartDate, ewl.UserMode, ewl.ServiceTransactionID, 
                      ewl.Amount, ewl.Status, ewl.TransType, ewl.UpdatedByAID  
                      FROM npos.ewallettrans ewl 
                      INNER JOIN npos.sites s ON s.SiteID = ewl.SiteID 
                      INNER JOIN npos.ref_services rs ON ewl.ServiceID = rs.ServiceID 
                      WHERE ewl.LoyaltyCardNumber = ? 
                      AND ewl.Status = ? AND ewl.StartDate >= ?
                      AND ewl.StartDate < ?
                      ORDER BY ewl.StartDate";
              }
              else
              {
                  $stmt = "SELECT ewl.EwalletTransID, ewl.SiteID, ewl.TerminalID, ewl.TransType, 
                      rs.ServiceName, s.SiteCode, ewl.StartDate, ewl.UserMode, ewl.ServiceTransactionID, 
                      ewl.Amount, ewl.Status, ewl.TransType, ewl.UpdatedByAID  
                      FROM npos.ewallettrans ewl 
                      INNER JOIN npos.sites s ON s.SiteID = ewl.SiteID 
                      INNER JOIN npos.ref_services rs ON ewl.ServiceID = rs.ServiceID 
                      WHERE ewl.SiteID = ? 
                      AND ewl.Status = ? AND ewl.StartDate >= ?
                      AND ewl.StartDate < ?
                      ORDER BY ewl.StartDate";
              }
              
              $this->prepare($stmt);
              (!is_null($cardNumber)) ? $this->bindparameter(1,$cardNumber) : $this->bindparameter(1, $site);
              $this->bindparameter(2,$vtransstatus);
              $this->bindparameter(3,$vFrom);
              $this->bindparameter(4,$vTo); 
          }
          try {
            $this->execute();
          } catch(PDOException $e) {
              var_dump($e->getMessage()); exit;
          }
          return $this->fetchAllData();
      }
      /**
       * Count all e-SAFE trans for Manual e-SAFE Casino fulfillment
       * @param type $zSiteID
       * @param type $zTerminalID
       * @param type $ztransstatus
       * @param type $ztranstype
       * @param type $zFrom
       * @param type $zTo
       * @param type $zStart
       * @param type $zLimit
       * @return type
       */
      function countselectewallettrans($vtransstatus, $vFrom, $vTo, $cardNumber = null, $site = null)
      {
          //check if status is selected or specific
          if ($vtransstatus == "All")
          {
              //check what to look up to
              if (!is_null($cardNumber))
              {
                  $stmt = "SELECT COUNT(ewl.EwalletTransID) AS count 
                           FROM npos.ewallettrans ewl 
                           INNER JOIN npos.sites s ON s.SiteID = ewl.SiteID 
                           INNER JOIN npos.ref_services rs ON ewl.ServiceID = rs.ServiceID 
                           WHERE ewl.LoyaltyCardNumber = ? 
                           AND ewl.Status IN (3, 4) AND ewl.StartDate >= ? 
                           AND ewl.StartDate < ? 
                           ORDER BY ewl.StartDate";
              }
              else //SITE
              {
                  $stmt = "SELECT COUNT(ewl.EwalletTransID) AS count 
                           FROM npos.ewallettrans ewl 
                           INNER JOIN npos.sites s ON s.SiteID = ewl.SiteID 
                           INNER JOIN npos.ref_services rs ON ewl.ServiceID = rs.ServiceID 
                           WHERE ewl.SiteID = ? 
                           AND ewl.Status IN (3, 4) AND ewl.StartDate >= ? 
                           AND ewl.StartDate < ? 
                           ORDER BY ewl.StartDate";
              }
              $this->prepare($stmt);
              (!is_null($cardNumber)) ? $this->bindparameter(1, $cardNumber) : $this->bindparameter(1, $site);
              $this->bindparameter(2, $vFrom);
              $this->bindparameter(3, $vTo);
          }
          else //selected status
          {
              if (!is_null($cardNumber))
              {
                  $stmt = "SELECT COUNT(ewl.EwalletTransID) AS count 
                      FROM npos.ewallettrans ewl 
                      INNER JOIN npos.sites s ON s.SiteID = ewl.SiteID 
                      INNER JOIN npos.ref_services rs ON ewl.ServiceID = rs.ServiceID 
                      WHERE ewl.LoyaltyCardNumber = ? 
                      AND ewl.Status = ? AND ewl.StartDate >= ? 
                      AND ewl.StartDate < ? 
                      ORDER BY ewl.StartDate";
              }
              else
              {
                  $stmt = "SELECT COUNT(ewl.EwalletTransID) AS count 
                      FROM npos.ewallettrans ewl 
                      INNER JOIN npos.sites s ON s.SiteID = ewl.SiteID 
                      INNER JOIN npos.ref_services rs ON ewl.ServiceID = rs.ServiceID 
                      WHERE ewl.SiteID = ? 
                      AND ewl.Status = ? AND ewl.StartDate >= ? 
                      AND ewl.StartDate < ? 
                      ORDER BY ewl.StartDate";
              }
              $this->prepare($stmt);
              (!is_null($cardNumber)) ? $this->bindparameter(1,$cardNumber) : $this->bindparameter(1, $site); 
              $this->bindparameter(2,$vtransstatus);
              $this->bindparameter(3,$vFrom);
              $this->bindparameter(4,$vTo); 
          }
          try {
            $this->execute();
          } catch(PDOException $e) {
              var_dump($e->getMessage()); exit;
          }
          return $this->fetchData();
      }
      //select all terminal based from sites to populate combo box
      function viewterminals($zsiteID)
      {
          if($zsiteID > 0)
          {
              $stmt = "SELECT DISTINCT a.TerminalID, b.TerminalCode FROM transactiondetails
                  a INNER JOIN terminals b ON a.TerminalID = b.TerminalID
                  WHERE a.SiteID = '".$zsiteID."' ORDER BY TerminalID ASC";
          }
          else
          {
              $stmt = "SELECT DISTINCT a.TerminalID, b.TerminalCode FROM transactiondetails a 
                  INNER JOIN terminals b ON a.TerminalID = b.TerminalID ORDER BY TerminalID ASC";
          }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
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
                  WHERE td.SiteID =? AND td.TerminalID =? AND Date(td.DateCreated) >=? 
                  AND Date(td.DateCreated) < ?";
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
                  WHERE td.SiteID =? AND td.TerminalID =? AND td.Status IN (".$status.") AND Date(td.DateCreated) >=? 
                  AND Date(td.DateCreated) < ?";
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
                  WHERE td.SiteID =? AND td.TerminalID =? AND td.TransactionType = ? AND Date(td.DateCreated) >=? 
                  AND Date(td.DateCreated) < ?";
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
                  WHERE td.SiteID =? AND td.TerminalID =? AND td.Status IN (".$status.") AND td.TransactionType = ? AND Date(td.DateCreated) >=? 
                  AND Date(td.DateCreated) < ?";
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
      
      
      /**
      * @author Gerardo V. Jagolino Jr.
      * @param $cardnumber, $ztransstatus, $ztranstype, $zFrom,$zTo
      * @return array
      * count all manualredemptions to paginate, validate if status and transtype was selected
      */
      function countmanualredemptionsub($cardnumber, $ztransstatus, $zFrom,$zTo)
      {           
          
          //validate if combo boxes of transaction status and transaction type are selected ALL 
          if($ztransstatus == 'All')
          {
              $stmt = "SELECT COUNT(mr.ManualRedemptionsID) as count FROM manualredemptions mr  
                    WHERE mr.LoyaltyCardNumber = ? AND mr.TransactionDate >= ?
                    AND mr.TransactionDate < ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$zFrom);
              $this->bindparameter(3,$zTo); 
          }
          //then if Transaction Status was selected any of its choices (Success, Failed) AND Transaction Type was selected all
          elseif($ztransstatus <> 'All')
          {
              $stmt = "SELECT COUNT(mr.ManualRedemptionsID) as count FROM manualredemptions mr 
                    WHERE mr.LoyaltyCardNumber = ? AND mr.Status = ? AND mr.TransactionDate >= ?
                    AND mr.TransactionDate < ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztransstatus);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo); 
          }
                
          $this->execute();
          return $this->fetchData();
      }
      
      
      function getcashierpersite($zsiteID)
      {
          $stmt = "SELECT a.AID,b.UserName from siteaccounts a 
                   INNER JOIN accounts b on a.AID = b.AID WHERE a.SiteID = ? AND b.AccountTypeID = 4 AND b.Status = 1";
          $this->prepare($stmt);
          $this->bindparameter(1,$zsiteID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      function getcashierpasskey($zcashierid)
      {
          $stmt = "SELECT WithPasskey from  accounts WHERE AID = ? ";
          $this->prepare($stmt);
          $this->bindparameter(1,$zcashierid);
          $this->execute();
          return $this->fetchData();          
      }
      
      function updatecashierpasskey($zcashierid,$zpasskey, $zgenpasskey = '', $zpasskeyexpire = '')
      {
          if($zgenpasskey != '' && $zpasskeyexpire != ''){
            $stmt ="UPDATE accounts SET WithPasskey = ?, Passkey = ?, DatePasskeyExpires = ? WHERE AID =? ";
            $this->prepare($stmt);
            $this->bindparameter(1,$zpasskey);
            $this->bindparameter(2,$zgenpasskey);
            $this->bindparameter(3,$zpasskeyexpire);
            $this->bindparameter(4,$zcashierid);
          } else {
            $stmt ="UPDATE accounts SET WithPasskey = ? WHERE AID =? ";
            $this->prepare($stmt);
            $this->bindparameter(1,$zpasskey);
            $this->bindparameter(2,$zcashierid);
          }
           
           $this->execute();
           return $this->rowCount();
      }
      
      public function checkpasskeydetails($zcashierid){
          $stmt = "SELECT Passkey, DatePasskeyExpires from  accounts WHERE AID = ? ";
          $this->prepare($stmt);
          $this->bindparameter(1,$zcashierid);
          $this->execute();
          return $this->fetchData();    
      }
      
      function getterminalname($zterminalID)
      {
          $stmt = "SELECT TerminalName FROM terminals WHERE TerminalID = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $zterminalID);
          $this->execute();
          return $this->fetchData();
      }
      
      //disable cashier terminal
      function disableterminal($zcshmacID, $zremarks)
      {
          $stmt = "UPDATE cashiermachineinfo SET IsActive = 0, Remarks = ?  where CashierMachineInfoId_PK = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $zremarks);
          $this->bindparameter(2, $zcshmacID);
          $this->execute();
          return $this->rowCount();
      }
      
      //check first if site terminal is registered or not
      function chkdisableterm($zcomputername, $zdisableterm)
      {
          if($zdisableterm == 1)
          {
              $stmt = "SELECT COUNT(*) as ctrterminal FROM cashiermachineinfo WHERE MAC_Address = ?";
          }
          else 
          {
              $stmt = "SELECT COUNT(*) as ctrterminal FROM cashiermachineinfo where ComputerName = ?";
          }
          $this->prepare($stmt);
          $this->bindparameter(1,$zcomputername);
          $this->execute();
          return $this->fetchData();
      }
      
      
      //call current provider of a terminal
      function getterminalprovider($zterminalID, $zserviceID)
      {
          if($zterminalID > 0){
              $stmt = "SELECT ts.ServiceID, serv.ServiceName FROM terminalservices ts 
                   INNER JOIN ref_services serv ON ts.ServiceID = serv.ServiceID 
                   WHERE ts.TerminalID = ? AND ts.Status = 1";
              $this->prepare($stmt);
              $this->bindparameter(1, $zterminalID);
          } else {
              $stmt = "SELECT a.ServiceGroupID, a.ServiceName FROM ref_services a WHERE a.ServiceID = ?";
              $this->prepare($stmt);
              $this->bindparameter(1, $zserviceID);
          }
          
          $this->execute();
          return $this->fetchAllData();
      }
      
      //get all services
      function getallservices($sort)
      {
          $stmt = "SELECT * FROM ref_services WHERE Status = 1 ORDER BY ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $sort);
          $this->execute();
          return $this->fetchAllData();
      }
      
        /**
       * @author Gerardo V. Jagolino Jr.
       * @param int $services
       * @return array 
       * get services for casino services dropdown
       */
      function getServices($services)
      {
          $stmt = "SELECT ServiceID, ServiceName FROM ref_services WHERE ServiceID IN (".$services.") ORDER BY ServiceGroupID";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();        
      }
      
       
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index, $search
     * @return array 
     * get Find a certain service in Casino Array
     */
      function loopAndFindCasinoService($array, $index, $search){
        $returnArray = array();
            foreach($array as $k=>$v){
                  if($v[$index] == $search){   
                       $returnArray[] = $v;
                  }
            }
      return $returnArray;
      }
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index
     * @return array 
     * get Find ServiceID in Casino Array
     */
      function loopAndFindService($array, $index){
        $returnArray = array();
            foreach($array as $k=>$v){
                  if($v[$index] == 15 ||$v[$index] == 16||$v[$index] == 17||$v[$index] == 8){   
                       $returnArray[] = $v[$index];
                  }
            }
        return $returnArray;
      }
      //change currently assigned RTG Server to another when selected all(batch)
      /**
       *
       * @param type $zarrbatch
       * @return type; $return case 1: inserted,  2 : updated 
       */
      function reassignbatchserver($zarrbatch)
      {
          $this->begintrans();
          try
          {
              $return = 0;
              foreach ($zarrbatch as $row)
              {
                  $zterminalID = $row['TerminalID'];
                  $zoldserviceID = $row['OldServiceID'];
                  $znewserviceID = $row['NewServiceID'];
                  $zremarks = $row['Remarks'];
                  $zplainpassword = $row['PlainPassword'];
                  $zhashedpwd = $row['HashedPassword'];
                  $this->prepare("SELECT COUNT(*) FROM terminalservices WHERE TerminalID = ?  AND ServiceID = ?");
                  $this->bindparameter(1, $zterminalID);
                  $this->bindparameter(2, $znewserviceID);
                  $this->execute();
                  
                  //check if terminals are not created in kronus
                  if($this->hasRows() == 0)
                  {
                      if($zplainpassword <> null){
                          $this->prepare("INSERT INTO terminalservices 
                                          (Status, TerminalID, isCreated, ServiceID, Remarks, ServicePassword, HashedServicePassword)
                                          VALUES (1, ?, 1, ?, ?, ?, ?)");
                          $this->bindparameter(1, $zterminalID);
                          $this->bindparameter(2, $znewserviceID);
                          $this->bindparameter(3, $zremarks);
                          $this->bindparameter(4, $zplainpassword);
                          $this->bindparameter(5, $zhashedpwd);
                          if($this->execute())
                          {
                              $this->prepare("UPDATE terminalservices SET Status = 0, Remarks = ? WHERE TerminalID = ? AND ServiceID = ?");
                              $this->bindparameter(1, $zremarks);
                              $this->bindparameter(2, $zterminalID);
                              $this->bindparameter(3, $zoldserviceID);
                              try
                              {
                                  $this->execute();
                                  $return = 1;
                              }
                              catch (PDOException $e)
                              {
                                  $this->rollbacktrans();
                                  return 0;
                              }
                          }
                          else
                          {
                              $this->rollbacktrans();
                              return 0;
                          }
                      }
                      else{
                          $this->rollbacktrans();
                          return 0;
                      }
                  }
                  else
                  {
                      $this->prepare("UPDATE terminalservices SET Status = 0, Remarks = ? WHERE TerminalID = ? AND ServiceID = ?");
                      $this->bindparameter(1, $zremarks);
                      $this->bindparameter(2, $zterminalID);
                      $this->bindparameter(3, $zoldserviceID);
                      if($this->execute())
                      {
                          $this->prepare("UPDATE terminalservices SET Status = 1, Remarks = ?,
                                          ServicePassword = ?, HashedServicePassword = ?
                                          WHERE TerminalID = ? AND ServiceID = ?");
                          $this->bindparameter(1, $zremarks);
                          $this->bindparameter(2, $zplainpassword);
                          $this->bindparameter(3, $zhashedpwd);
                          $this->bindparameter(4, $zterminalID);
                          $this->bindparameter(5, $znewserviceID);
                          try 
                          {
                              $this->execute();
                              $return = 2;
                          } 
                          catch (PDOException $e) 
                          {
                              $this->rollbacktrans();
                              return 0;
                          }
                      }
                      else
                      {
                          $this->rollbacktrans();
                          return 0;
                      }
                  }
              }
              try{
                  $this->committrans();    
                  return $return;
              }catch(PDOException $e) {
                  $this->rollbacktrans();
                  return 0;
              }
          }
          catch (PDOException $e)
          {
              $this->rollbacktrans();
              return 0;
          }
      }
      
      //remove server from a terminal (regular and vip)
      function removeservice($zterminalID, $zserviceID, $zremarks)
      {
          $listterminals = array();
          foreach($zterminalID as $val1)
          {
              foreach ($val1 as $row)
              {
                array_push($listterminals, "'".$row."'");   
              }
          }
          $terminals = implode(',',$listterminals);

          $this->begintrans();
          $this->prepare("UPDATE terminalservices SET Status = 0, Remarks = ? WHERE TerminalID IN (".$terminals.") AND ServiceID = ?");
          //$this->bindparameter(1, $zterminalID);
          $this->bindparameter(1, $zremarks);
          $this->bindparameter(2, $zserviceID);
          if($this->execute())
          {
              $this->committrans();
              unset($listterminals);
              return 1;
          }
          else
          {
              $this->rollbacktrans();
              return 0;
          }
      }
      
      //select all terminal based from sites to populate combo box
      function getterminals($zsiteID)
      {
          if($zsiteID > 0)
          {
              $stmt = "Select TerminalID, TerminalCode from terminals where SiteID = '".$zsiteID."' AND Status = 1 AND isVIP = 0 ORDER BY TerminalID ASC";
          }
          else
	  {
              $stmt = "Select TerminalID, TerminalCode from terminals WHERE Status = 1 AND isVIP = 0 ORDER BY TerminalID ASC";
          }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
     }
     
     function getterminals2($zsiteID)
      {
          if($zsiteID > 0)
          {
              $stmt = "Select DISTINCT(t.TerminalID), t.TerminalCode from terminals t INNER JOIN terminalservices ts ON  t.TerminalID = ts.TerminalID INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID where t.SiteID = '".$zsiteID."' AND t.Status = 1 AND t.isVIP = 0 AND rs.UserMode = 0 ORDER BY TerminalID ASC";
          }
          else
	  {
              $stmt = "Select DISTINCT(t.TerminalID), t.TerminalCode from terminals t INNER JOIN terminalservices ts ON  t.TerminalID = ts.TerminalID INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID WHERE t.Status = 1 AND t.isVIP = 0 AND rs.UserMode = 0 ORDER BY TerminalID ASC";
          }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
     }
      
     //get terminals by server ID (RTG Alpha, Gamma, ECF, MG) --> Switching of Servers
     function getterminalbyserverID($zsiteID, $zserviceID)
     {
           $stmt = "SELECT ts.TerminalID, t.TerminalCode FROM terminalservices ts
                    INNER JOIN terminals t ON ts.TerminalID = t.TerminalID
                    WHERE ts.ServiceID = ? AND t.SiteID = ? AND t.isVIP = 0 AND ts.Status = 1 ORDER BY ts.TerminalID ASC";
           $this->prepare($stmt);
           $this->bindparameter(1, $zserviceID);
           $this->bindparameter(2, $zsiteID);
           $this->execute();
           return $this->fetchAllData();
     }
     
    /**
    * @author Gerardo V. Jagolino Jr.
    * @param $zsiteID
    * @return string
    * get spyder status if enable or disable
    */ 
     function getSpyder($zsiteID)
     {
           $stmt = "SELECT Spyder FROM sites WHERE SiteID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $zsiteID);
           $this->execute($stmt);
           return $this->fetchData();
     }
     
     function getCashierTab($siteID)
     {
           $stmt = "SELECT TMTab,SRRTab FROM sites WHERE SiteID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $siteID);
           $this->execute($stmt);
           return $this->fetchData();
     }
     
     /**
    * @author Gerardo V. Jagolino Jr.
    * @param $zsiteID
    * @return int
    * get Cashier version of a certain site
    */ 
     function getCashierVersion($zsiteID)
     {
           $stmt = "SELECT CashierVersion FROM sites WHERE SiteID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $zsiteID);
           $this->execute($stmt);
           return $this->fetchData();
     }
     
    /**
    * @author Gerardo V. Jagolino Jr.
    * @param $zsiteID
    * @return int
    * check the number of cashier sessions enable in a certain site
    */ 
     function checkAccountSessions($zsiteID)
     {
           $stmt = "SELECT COUNT(AtS.SessionID) count FROM accountsessions AtS
                INNER JOIN siteaccounts SA ON AtS.AID = SA.AID
                INNER JOIN accounts AC ON AC.AID = AtS.AID 
                WHERE SA.SiteID = ? AND AC.Status = 1";
           $this->prepare($stmt);
           $this->bindparameter(1, $zsiteID);
           $this->execute($stmt);
           $count =  $this->fetchData();
           return $count['count'];
     }
     
    /**
    * @author Gerardo V. Jagolino Jr.
    * @param $spyder, $zsiteID
    * @return int
    * update spyder status
    */ 
     function updateSpyder($spyder, $zsiteID)
     {
           $stmt = "UPDATE sites SET Spyder = ? WHERE SiteID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $spyder);
           $this->bindparameter(2, $zsiteID);
           $this->execute($stmt);
           return $this->rowCount();
     }
     
     function updateCashierMenuTab($siteID,$tmTab,$srrTab)
     {
         
           $stmt = "UPDATE sites SET TMTab = ?,SRRTab = ? WHERE SiteID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $tmTab);
           $this->bindparameter(2, $srrTab);
           $this->bindparameter(3, $siteID);
           $this->execute($stmt);
           return $this->rowCount();
         
     }
     
     
     /**
    * @author Gerardo V. Jagolino Jr.
    * @param $spyder, $zsiteID
    * @return int
    * update spyder status
    */ 
     function updateCashierVersion($cversion, $zsiteID)
     {
           $stmt = "UPDATE sites SET CashierVersion = ? WHERE SiteID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $cversion);
           $this->bindparameter(2, $zsiteID);
           $this->execute($stmt);
           return $this->rowCount();
     }
     
     
     //get terminalID of regular and vip by terminalcode
     function getterminalID($zterminalcode, $zsiteID, $zsitecode)
     {
           $terminal = array();
           foreach ($zterminalcode as $terminals)
           {
               $terminalcode = $zsitecode.$terminals;
               $vipterminal = $terminalcode."VIP";
               //$stmt = "SELECT TerminalID, TerminalCode FROM terminals where TerminalCode LIKE '".$terminalcode."%' AND SiteID = ?";
               $stmt = "SELECT TerminalID, TerminalCode FROM terminals WHERE TerminalCode IN('$terminalcode','$vipterminal') AND SiteID = ?";
               $this->prepare($stmt);
               $this->bindparameter(1, $zsiteID);
               $this->execute();
               $rterminals = $this->fetchAllData();
               array_push($terminal, $rterminals);
           }
           return $terminal;
    }
    
    //get terminalID of regular and vip by terminalcode
     function getterminalacct($zterminalcode, $zsiteID, $zsitecode, $oldserviceid = '')
     {
           $terminal = array();
           foreach ($zterminalcode as $terminals)
           {
               $terminalcode = $zsitecode.$terminals;
               $vipterminal = $terminalcode."VIP";
               //$stmt = "SELECT TerminalID, TerminalCode FROM terminals where TerminalCode LIKE '".$terminalcode."%' AND SiteID = ?";
               if(is_null($oldserviceid) || $oldserviceid == ''){
                    $stmt = "SELECT t.TerminalID, t.TerminalCode, ts.ServiceID, ts.ServicePassword, 
                        ts.HashedServicePassword, rs.ServiceName, rs.ServiceGroupID FROM terminals t
                        INNER JOIN terminalservices ts ON t.TerminalID = ts.TerminalID
                        INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                        WHERE t.TerminalCode IN('$terminalcode','$vipterminal') AND t.SiteID = ? AND ts.Status IN (1,9) AND t.Status = 1";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
               }
               else{
                    $stmt = "SELECT t.TerminalID, t.TerminalCode, ts.ServiceID, ts.ServicePassword, 
                        ts.HashedServicePassword, rs.ServiceName, rs.ServiceGroupID FROM terminals t
                        INNER JOIN terminalservices ts ON t.TerminalID = ts.TerminalID
                        INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                        WHERE t.TerminalCode IN('$terminalcode','$vipterminal') AND t.SiteID = ? AND ts.Status IN (1,9) AND t.Status = 1 AND ts.ServiceID = ?";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsiteID);
                    $this->bindparameter(2, $oldserviceid);
               }
               $this->execute();
               $rterminals = $this->fetchAllData();
               array_push($terminal, $rterminals);
           }
           
           return $terminal;
    }
    
    //E-City Transaction Tracking: get transactiondetails data
    function gettransactiondetails($zsiteID,$zterminalID, $zdatefrom, $zdateto, $zsummaryID, $zstart, $zlimit, $zsort, $zdirection)
    {
        //if summary ID was selected on the grid, execute;
        if($zsummaryID > 0)
        {
            $stmt = "SELECT tr.TransactionReferenceID, st.POSAccountNo, tr.TransactionSummaryID, tr.SiteID, tm.TerminalCode, tr.TerminalID, tr.Option2 AS LoyaltyCard,
                 tr.TransactionType, tr.Amount, tr.DateCreated, tr.ServiceID,ad.Name, tr.Status, rs.ServiceName 
                 FROM transactiondetails tr inner join accountdetails ad on tr.CreatedByAID = ad.AID
                  INNER JOIN sites st ON tr.SiteID = st.SiteID
                  INNER JOIN ref_services rs ON rs.ServiceID = tr.ServiceID 
                  INNER JOIN terminals tm ON tr.TerminalID = tm.TerminalID WHERE tr.SiteID = ? AND tr.TerminalID = ? 
                 AND tr.DateCreated >= ? AND tr.DateCreated < ? AND tr.TransactionSummaryID = ? ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
            $this->prepare($stmt);
            $this->bindparameter(1, $zsiteID);
            $this->bindparameter(2, $zterminalID);
            $this->bindparameter(3, $zdatefrom);
            $this->bindparameter(4, $zdateto);
            $this->bindparameter(5, $zsummaryID);
        }
        else
        {
            $stmt = "SELECT tr.TransactionReferenceID, st.POSAccountNo, tr.TransactionSummaryID, tr.SiteID, tm.TerminalCode, tr.TerminalID, tr.Option2 AS LoyaltyCard,
                 tr.TransactionType, tr.Amount, tr.DateCreated, tr.ServiceID,ad.Name, tr.Status, rs.ServiceName 
                 FROM transactiondetails tr inner join accountdetails ad on tr.CreatedByAID = ad.AID
                  INNER JOIN sites st ON tr.SiteID = st.SiteID
                  INNER JOIN ref_services rs ON rs.ServiceID = tr.ServiceID
                  INNER JOIN terminals tm ON tr.TerminalID = tm.TerminalID WHERE tr.SiteID = ? AND tr.TerminalID = ? 
                 AND tr.DateCreated >= ? AND tr.DateCreated < ? ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
            $this->prepare($stmt);
            $this->bindparameter(1, $zsiteID);
            $this->bindparameter(2, $zterminalID);
            $this->bindparameter(3, $zdatefrom);
            $this->bindparameter(4, $zdateto);
        }
        
        $this->execute();
        
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
            $stmt = "SELECT COUNT(*) ctrtdetails 
                 FROM transactiondetails WHERE SiteID = ? AND TerminalID = ? 
                 AND DateCreated >= ? AND DateCreated < ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zsiteID);
            $this->bindparameter(2, $zterminalID);
            $this->bindparameter(3, $zdatefrom);
            $this->bindparameter(4, $zdateto);
        }
 
        $this->execute();
        return $this->fetchData();
    }
    
    //E-City Transaction Summary, get details
    function gettransactionsummary($zsiteID, $zterminalID, $zdatefrom, $zdateto, $zstart, $zlimit, $zsort, $zdirection)
    {
        $stmt = "SELECT ts.TransactionsSummaryID, ts.SiteID, st.POSAccountNo, ts.TerminalID, t.TerminalCode, ts.Deposit, ts.Reload, ts.Option1 AS LoyaltyCard,
                 ts.Withdrawal, ts.DateStarted, ts.DateEnded, ad.Name 
                 FROM transactionsummary ts
                 INNER JOIN accounts acc ON ts.CreatedByAID = acc.AID
                 INNER JOIN accountdetails ad ON acc.AID = ad.AID
                 INNER JOIN sites st ON ts.SiteID = st.SiteID
                 INNER JOIN terminals t ON ts.TerminalID = t.TerminalID
                 WHERE ts.SiteID = ? AND ts.TerminalID = ? AND ts.DateStarted >= ?
                 AND ts.DateStarted < ? ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->bindparameter(2, $zterminalID);
        $this->bindparameter(3, $zdatefrom);
        $this->bindparameter(4, $zdateto);
        $this->execute();
        return $this->fetchAllData();
    }
    
    //E-City Transaction Summary, count transactions summary
    function counttranssummary($zsiteID, $zterminalID, $zdatefrom, $zdateto)
    {
        $stmt = "SELECT COUNT(*) ctrtsum
                 FROM transactionsummary ts 
                 INNER JOIN accounts acc ON ts.CreatedByAID = acc.AID
                 WHERE SiteID = ? AND TerminalID = ? AND DateStarted >= ?
                 AND DateStarted < ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->bindparameter(2, $zterminalID);
        $this->bindparameter(3, $zdatefrom);
        $this->bindparameter(4, $zdateto);
        $this->execute();
        return $this->fetchData();
    }
    
    //E-City Transaction Request LOgs ON LP, get details
    function gettranslogslp($zsiteID, $zterminalID, $zdatefrom, $zdateto, $zsummaryID, $zstart, $zlimit, $zsort, $zdirection)
    {
        //if summary ID was selected
        if($zsummaryID > 0)
        {
            $stmt = "SELECT trl.TransactionRequestLogLPID, trl.TransactionReferenceID, trl.Amount, trl.StartDate, st.POSAccountNo, rs.ServiceName, t.TerminalCode,
                 trl.EndDate, trl.TransactionType, trl.TerminalID, trl.Status, trl.SiteID, trl.ServiceTransactionID, 
                 trl.ServiceStatus, trl.ServiceTransferHistoryID, trl.ServiceID FROM transactionrequestlogslp trl
                 INNER JOIN sites st ON st.SiteID = trl.SiteID
                 INNER JOIN terminals t ON t.TerminalID = trl.TerminalID
		         INNER JOIN ref_services rs ON rs.ServiceID = trl.ServiceID  
                 WHERE trl.SiteID = ? AND trl.TerminalID = ? AND trl.StartDate >= ? AND trl.EndDate < ? 
                 AND trl.TransactionSummaryID = ? ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
            $this->prepare($stmt);
            $this->bindparameter(1, $zsiteID);
            $this->bindparameter(2, $zterminalID);
            $this->bindparameter(3, $zdatefrom);
            $this->bindparameter(4, $zdateto);
            $this->bindparameter(5, $zsummaryID);
        }
        else
        {
            $stmt = "SELECT trl.TransactionRequestLogLPID, trl.TransactionReferenceID, trl.Amount, trl.StartDate, st.POSAccountNo, rs.ServiceName, t.TerminalCode,
                 trl.EndDate, trl.TransactionType, trl.TerminalID, trl.Status, trl.SiteID, trl.ServiceTransactionID, 
                 trl.ServiceStatus, trl.ServiceTransferHistoryID, trl.ServiceID FROM transactionrequestlogslp trl
                 INNER JOIN sites st ON st.SiteID = trl.SiteID
                 INNER JOIN terminals t ON t.TerminalID = trl.TerminalID
		         INNER JOIN ref_services rs ON rs.ServiceID = trl.ServiceID 
                 WHERE trl.SiteID = ? AND trl.TerminalID = ? AND trl.StartDate >= ? AND trl.EndDate < ? 
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
    
    //E-City Transaction Request Logs, count
    function counttranslogslp($zsiteID, $zterminalID, $zdatefrom, $zdateto, $zsummaryID)
    {
        //if summaryID was selected 
        if($zsummaryID > 0)
        {
            $stmt = "SELECT COUNT(*) ctrlogs FROM transactionrequestlogslp 
                     WHERE SiteID = ? AND TerminalID = ? AND StartDate >= ? 
                     AND EndDate < ? AND TransactionSummaryID = ?";
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
                     WHERE SiteID = ? AND TerminalID = ? AND DATE(StartDate) >= ? 
                     AND DATE(EndDate) < ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zsiteID);
            $this->bindparameter(2, $zterminalID);
            $this->bindparameter(3, $zdatefrom);
            $this->bindparameter(4, $zdateto);
        }
        
        $this->execute();
        return $this->fetchData();
    }
    
    //E-City Service Transfer History; get details by service history ID
    function gethistorydetails($zsthistoryID)
    {
        $stmt = "SELECT ServiceTransferHistoryID, TerminalID, Amount, FromServiceID, 
                 ToServiceID, Status FROM servicetransferhistory WHERE ServiceTransferHistoryID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsthistoryID);
        $this->execute();        
        return $this->fetchAllData();
    }
    
    //Logs Monitoring: get log file contents
    function getfilecontents($zfile)
    {   
        
        $exists = $this->remoteFileExists($zfile);
        $arrdisplay = array();
        if(file_exists($zfile)){
            $file = fopen($zfile, "r");  
            while (!feof($file))   
            {
               $display = fgets($file, strlen(file_get_contents($zfile)));
               
               
               if($display <> false)
               {
                   if(trim($display)!=""){
                   $arrdisplay[] = array("ERROR"=>trim($display));  
                   } 
               }
            }  
            fclose($file); 
            return $arrdisplay;
        }else if($exists){
            //getting file from a remotesever not on local
            $data = file_get_contents($zfile);
            $convert = explode("\n", $data);
            for ($i=0;$i<count($convert);$i++)  
            {
               if(trim($convert[$i])!=""){
               $arrdisplay[] = array("ERROR"=>trim($convert[$i])); 
               }
            }
            return $arrdisplay;
        }else{
            return false;
        }
        
    }
    
    //Logs Monitoring: get cashier's logs path
    function getlogspath($cashierlogpath)
    {
//        $zdirectory = dirname( __FILE__ ) . '/';
//        $zroot = realpath($zdirectory . '../../../../../' ) . '/';
//        $zrealfolder = $zroot . $cashierlogpath;  
        return $cashierlogpath; //modified 02/23/11, get the realpath from web.config.php
    }
    
        //Admin Logs Monitoring: get admin's logs path
    function getadminlogspath($adminlogpath)
    {
        return $adminlogpath; //modified 05/31/13, get the realpath from web.config.php
    }
    
    //for switching of servers
    function getsitecredentials($zsiteID)
    {
        $stmt = "SELECT s.SiteName, s.SiteCode, sd.PassCode FROM sites s
                 INNER JOIN sitedetails sd ON s.SiteID = sd.SiteID WHERE s.SiteID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchAllData();
    }
    
    /**
     * counts cashier machine per site
     * @param int site ID
     * @return int cashier machine count
     */
    function countcashiermachine($zsiteID)
    {
        $stmt = "SELECT CashierMachineCount FROM cashiermachinecounts WHERE SiteID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchData();
    }
    
    /**
     * updates the allowed number of cashier machine per site
     * @param int added cashier count
     * @param int site ID 
     * @param int account ID
     * @return boolean success / error
     */
    function updatecashiercount($zcashiercount,$zsiteID, $zaid)
    {
        $this->begintrans();
        $this->prepare("UPDATE cashiermachinecounts SET CashierMachineCount = ?, DateUpdated = now_usec(), UpdatedByAID = ? WHERE SiteID = ?");
        $this->bindparameter(1, $zcashiercount);
        $this->bindparameter(2, $zaid);
        $this->bindparameter(3, $zsiteID);
        if($this->execute())
        {
            $this->committrans();
            return 1;
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
    }
    
    /**
     *counts details on cashier machine info (Disableing of cashier Terminal)
     * @return array | object 
     */
    function countcashiermachineinfo($zsiteID)
    {
        $stmt = "SELECT COUNT(cmi.CashierMachineInfoId_PK) as ctrmachine FROM cashiermachineinfo cmi WHERE cmi.IsActive = 1 AND cmi.POSAccountNo = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchData();
    }
    
    /**
     * gets all the cashier machine info details to be dispalyed on the grid (Disableing of cashier Terminal)
     * @return array | object 
     */
    function getcashiermachineinfo($zstart, $zlimit, $zsiteID)
    {
        $stmt = "SELECT cmi.CashierMachineInfoId_PK, cmi.ComputerName, cmi.CPU_Id, cmi.BIOS_SerialNumber, cmi.MAC_Address, 
                 cmi.Motherboard_SerialNumber, cmi.OS_Id, cmi.IPAddress, s.SiteCode FROM cashiermachineinfo cmi
                 INNER JOIN sites s ON s.SiteID = cmi.POSAccountNo
                 WHERE cmi.IsActive = 1 AND cmi.POSAccountNo = ? LIMIT ".$zstart.", ".$zlimit."";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchAllData();
    }
    
    /**
     * insert machine count if here was no existing record
     * @param int $zsiteid
     * @param int $zaid
     * @return boolean | int 
     */
    function insertmachinecount($zsiteid, $zaid)
    {
        $this->begintrans();
        $this->prepare("INSERT INTO cashiermachinecounts(SiteID, CashierMachineCount, DateCreated, CreatedByAID) VALUES (?,1,now_usec(),?)");
        $this->bindparameter(1, $zsiteid);
        $this->bindparameter(2, $zaid);
        if($this->execute())
        {
            $this->committrans();
            return 1;
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
    }
    
    /**
     *
     * @param type $zsiteID
     * @param type $zterminalID
     * @return type 
     */
    function updateterminalpwd($zbatch)
    {
        $this->begintrans();
        $isupdated = 0;
        try{
            foreach($zbatch as $val){
                $zterminalID = $val['TerminalID'];
                $zserviceID = $val['ServiceID'];
                $znewpassword = $val['PlainPassword'];
                $zhashedpwd = $val['HashedPassword'];
                $this->prepare("SELECT COUNT(*) FROM terminalservices WHERE TerminalID = ?  AND ServiceID = ?");
                $this->bindparameter(1, $zterminalID);
                $this->bindparameter(2, $zserviceID);
                $this->execute();

                //check if existing on table
                if($this->hasRows() == 0)
                {
                    $stmt = "INSERT INTO terminalservices (ServicePassword, HashedServicePassword, Status, isCreated, TerminalID, ServiceID) 
                             VALUES(?, ?, 1, 1, ?, ?)";
                }
                else{
                    $stmt = "UPDATE terminalservices SET ServicePassword = ?, HashedServicePassword = ?, Status = 1, isCreated = 1
                             WHERE TerminalID = ? AND ServiceID = ?";
                }

                $this->prepare($stmt);
                $this->bindparameter(1, $znewpassword);
                $this->bindparameter(2, $zhashedpwd);
                $this->bindparameter(3, $zterminalID);
                $this->bindparameter(4, $zserviceID);
                $this->execute();
                $isupdated = $isupdated + $this->rowCount();
            }
            
            if($isupdated > 0)
            { 
                $this->committrans();  
                return 1;
            }
            else
            {
                $this->rollbacktrans();
                return 0;
            }
        } catch(PDOException $e) {
            $this->rollbacktrans();
            return 0;
        }
    }
    
    /**
     * get ServicePassword of a certain terminal, status must be active
     * @param type $zterminalID
     * @param type $zserviceID
     * @return type 
     */
    function getterminalcredentials($zterminalID, $zserviceID)
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
    
    /**
     * Get ServicePassword of a certain terminal regardless of its status
     * @param type $zterminalID
     * @param type $zserviceID
     * @return type 
     */
    function getterminalcredentials2($zterminalID, $zserviceID)
    {
        $stmt = "SELECT ServicePassword FROM terminalservices 
                     WHERE ServiceID = ? AND TerminalID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zserviceID);
        $this->bindparameter(2, $zterminalID);
        $this->execute();
        return $this->fetchData();
    }
    
    function viewTerminalID($zterminalcode)
    {
        $stmt = "SELECT TerminalID FROM terminals WHERE TerminalCode = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zterminalcode);
        $this->execute();
        return $this->fetchData();
    }
    
    /**
     * Activates / Deactivate the status of sites in generatedpasswordbatch table
     * @param int $zsiteID
     * @param int $zgenpwdid
     * @return boolean true | false 
     */
    function updateGenPwdBatch($zsiteID, $zgenpwdid, $lpdeployment)
    {
        $this->begintrans();
        $result = $this->chkoldsite($zsiteID);
        if($result){
            $zgenpwdbatchID = $result['GeneratedPasswordBatchID'];
            if($zgenpwdbatchID > 0)
            {
                if($lpdeployment > 0){
                    return 1;
                    $this->rollbacktrans();
                }
                else{
                    try{
                        $stmt = "UPDATE generatedpasswordbatch SET Status = 2 WHERE SiteID = ? AND GeneratedPasswordBatchID = ? AND Status = 1";
                        $this->prepare($stmt);
                        $this->bindparameter(1, $zsiteID);
                        $this->bindparameter(2, $zgenpwdbatchID);
                        $this->execute();
                        $isupdated1 = $this->rowCount(); 
                        try{
                            $stmt = "UPDATE generatedpasswordbatch SET Status = 1, DateUsed = now_usec(), SiteID = ? 
                                     WHERE Status = 0 AND SiteID IS NULL AND DateUsed IS NULL AND GeneratedPasswordBatchID = ?";
                            $this->prepare($stmt);
                            $this->bindparameter(1, $zsiteID);
                            $this->bindparameter(2, $zgenpwdid);
                            $this->execute();
                            $isupdated2 = $this->rowCount();
                            try{
                                if($isupdated1 > 0 && $isupdated2 > 0){
                                    $this->committrans();
                                    return 1;
                                }
                                else 
                                   return 0;
                            }catch(PDOException $e){
                                $this->rollbacktrans();
                                return 0;
                            }
                        } catch(PDOException $e){
                            $this->rollbacktrans();
                            return 0;
                        }
                    } catch(PDOException $e){
                        $this->rollbacktrans();
                        return 0;
                    }
                }
            }
        }
        else
        {
            try{
                $stmt = "UPDATE generatedpasswordbatch SET Status = 1, DateUsed = now_usec(), SiteID = ? 
                             WHERE Status = 0 AND SiteID IS NULL AND DateUsed IS NULL AND GeneratedPasswordBatchID = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zsiteID);
                $this->bindparameter(2, $zgenpwdid);
                $this->execute();
                $isupdated = $this->rowCount();
                try{
                    if($isupdated > 0)
                    {
                        $this->committrans();
                        return 1;
                    }                           
                    else 
                       return 0;
                }catch(PDOException $e){
                    $this->rollbacktrans();
                    return 0;
                }
            }catch(PDOException $e) {
                $this->rollbacktrans();
                return 0;
            }
        }
    }
    
   /**
    * Creates a log file that contains success/failure on updating terminal accounts
    * @param array $arrsuccess
    * @param array $arrerror
    * @param file $txtfile 
    */
   function createTerminalPwdLogs($arrsuccess, $arrerror, $txtfile)
   {
        $handle = fopen($txtfile, 'w+') or die("Cannot open file");
        fwrite($handle, "Success on changing terminal password : "."\n");
        
        foreach($arrsuccess as $val){
            fwrite($handle, $this->getDate()."\t");
            fwrite($handle, $val['TerminalCode']."\t");
            fwrite($handle, $val['Casino']."\n");
        }
        
        fwrite($handle, "Error on changing terminal password : "."\n");
        
        foreach($arrerror as $val){
            fwrite($handle, $this->getDate()."\t");
            fwrite($handle, $val['TerminalCode']."\t");
            fwrite($handle, $val['Casino']."\n");
        }
        
       fclose($handle);
       
       unset($arrsuccess, $arrerror);
   }
   
   function logTerminalsCreated($arrsuccess, $txtfile){
        $handle = fopen($txtfile, 'w+') or die("Cannot open file");
        fwrite($handle, "Created Terminal accounts : "."\n");
        
        foreach($arrsuccess as $val){
            fwrite($handle, $this->getDate()."\t");
            fwrite($handle, $val['TerminalCode']."\t");
            fwrite($handle, $val['Casino']."\n");
        }
       
        unset($arrsuccess);
   }
   
   /**
    * Checks if a terminal has session 
    * @param array $zterminalID
    * @return object 
    */
   function chkTerminalSession($zterminalID){
       $listterminals = array();
       foreach($zterminalID as $val1)
       {
          foreach ($val1 as $row) 
          {
            array_push($listterminals, "'" . $row . "'");
          }
        }
        $terminals = implode(',', $listterminals);
        
        $stmt = "SELECT COUNT(TerminalID) AS ctrsession FROM terminalsessions WHERE TerminalID IN (".$terminals.")";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchData();
   }
   
   //select all terminal accounts
  function getsiteterminals($zsiteID)
  {
      $stmt = "SELECT TerminalID, TerminalName, TerminalCode FROM terminals WHERE SiteID = ? AND Status = 1
               ORDER BY TerminalCode";
      $this->prepare($stmt);
      $this->bindparameter(1, $zsiteID);
      $this->execute();
      return $this->fetchAllData();
  }
  
   /**
    * @author Gerardo V. Jagolino Jr.
    * @param $cardnumber, $ztransstatus, $ztranstype, $zFrom,$zTo, $zStart, $zLimit
    * @return array
    * for selecting transactionrequestlogs of a certain loyaltycard number and StartDate
    */ 
   function getcashierTranslogs($cardnumber, $ztransstatus, $ztranstype, $zFrom,$zTo, $zStart, $zLimit)
   {

          
          //validate if combo boxes of transaction status and transaction type are selected ALL 
          if($ztransstatus == 'All' && $ztranstype == 'All')
          {
              $stmt = "SELECT trl.TransactionReferenceID, trl.SiteID, s.SiteCode, tm.TerminalCode, 
                    trl.TerminalID, trl.TransactionType, trl.Amount, trl.LoyaltyCardNumber, rf.ServiceName, 
                    trl.StartDate, trl.EndDate, trl.Status, trl.ServiceTransactionID FROM transactionrequestlogs trl 
                    INNER JOIN terminals tm ON trl.TerminalID = tm.TerminalID
                    INNER JOIN ref_services rf ON rf.ServiceID = trl.ServiceID
                    INNER JOIN sites s ON s.SiteID = trl.SiteID
                    WHERE trl.LoyaltyCardNumber = ? AND trl.StartDate >= ? AND trl.StartDate < ? 
                    ORDER BY trl.StartDate LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$zFrom);
              $this->bindparameter(3,$zTo);   
          }
          //then if Transaction Status was selected any of its choices (Success, Failed) AND Transaction Type was selected all
          elseif($ztransstatus <> 'All' && $ztranstype == 'All')
          {
              $stmt = "SELECT trl.TransactionReferenceID, trl.SiteID, s.SiteCode, tm.TerminalCode, 
                  trl.TerminalID, trl.TransactionType, trl.Amount, trl.LoyaltyCardNumber, rf.ServiceName, 
                  trl.StartDate, trl.EndDate, trl.Status, trl.ServiceTransactionID FROM transactionrequestlogs trl 
                    INNER JOIN terminals tm ON trl.TerminalID = tm.TerminalID
                    INNER JOIN ref_services rf ON rf.ServiceID = trl.ServiceID
                    INNER JOIN sites s ON s.SiteID = trl.SiteID
                    WHERE trl.LoyaltyCardNumber = ? AND trl.Status = ?  AND trl.StartDate >= ? 
                    AND trl.StartDate < ? ORDER BY trl.StartDate LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztransstatus);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo); 
          }
          //then if Transaction Status was selected all AND Transaction Type was selected ano of its choices (Deposit, Reload, Withdraw)
          elseif($ztransstatus == 'All' && $ztranstype <> 'All')
          {
              $stmt = "SELECT trl.TransactionReferenceID, trl.SiteID, s.SiteCode, tm.TerminalCode, 
                  trl.TerminalID, trl.TransactionType, trl.Amount, trl.LoyaltyCardNumber, rf.ServiceName, 
                  trl.StartDate, trl.EndDate, trl.Status, trl.ServiceTransactionID FROM transactionrequestlogs trl 
                    INNER JOIN terminals tm ON trl.TerminalID = tm.TerminalID
                    INNER JOIN ref_services rf ON rf.ServiceID = trl.ServiceID
                    INNER JOIN sites s ON s.SiteID = trl.SiteID
                    WHERE trl.LoyaltyCardNumber = ? AND trl.TransactionType = ? AND trl.StartDate >= ? 
                    AND trl.StartDate < ? ORDER BY trl.StartDate LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztranstype);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo); 
          }
          //then if both Transaction Status and Transaction type was selected of its choices, execute:
          else
          {
              $stmt = "SELECT trl.TransactionReferenceID, trl.SiteID, s.SiteCode, tm.TerminalCode, 
                  trl.TerminalID, trl.TransactionType, trl.Amount, trl.LoyaltyCardNumber, rf.ServiceName, 
                  trl.StartDate, trl.EndDate, trl.Status, trl.ServiceTransactionID FROM transactionrequestlogs trl 
                    INNER JOIN terminals tm ON trl.TerminalID = tm.TerminalID
                    INNER JOIN ref_services rf ON rf.ServiceID = trl.ServiceID
                    INNER JOIN sites s ON s.SiteID = trl.SiteID
                    WHERE trl.LoyaltyCardNumber = ? AND trl.Status  = ? 
                        AND trl.TransactionType = ? AND trl.StartDate >= ? 
                    AND trl.StartDate < ? ORDER BY trl.StartDate LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztransstatus);
              $this->bindparameter(3,$ztranstype);
              $this->bindparameter(4,$zFrom);
              $this->bindparameter(5,$zTo);   
          }
          
          try {
            $this->execute();
          } catch(PDOException $e) {
              var_dump($e->getMessage()); exit;
          }
          return $this->fetchAllData();
   
      }
      
        /**
        * @author Gerardo V. Jagolino Jr.
        * @param $cardnumber, $ztransstatus, $ztranstype, $zFrom,$zTo
        * @return array
        * count all transactions to paginate, validate if status and transtype was selected
        */
      function countcashierTranslogs($cardnumber, $ztransstatus, $ztranstype, $zFrom,$zTo)
      {           
          
          //validate if combo boxes of transaction status and transaction type are selected ALL 
          if($ztransstatus == 'All' && $ztranstype == 'All')
          {
              $stmt = "SELECT COUNT(trl.TransactionRequestLogID) as count FROM transactionrequestlogs trl 
                    WHERE trl.LoyaltyCardNumber = ? AND trl.StartDate >= ? AND trl.StartDate < ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$zFrom);
              $this->bindparameter(3,$zTo);  
          }
          //then if Transaction Status was selected any of its choices (Success, Failed) AND Transaction Type was selected all
          elseif($ztransstatus <> 'All' && $ztranstype == 'All')
          {
              $stmt = "SELECT COUNT(trl.TransactionRequestLogID) as count FROM transactionrequestlogs trl 
                    WHERE trl.LoyaltyCardNumber = ? AND trl.Status = ? AND trl.StartDate >= ? 
                    AND trl.StartDate < ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztransstatus);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo);
          }
          //then if Transaction Status was selected all AND Transaction Type was selected ano of its choices (Deposit, Reload, Withdraw)
          elseif($ztransstatus == 'All' && $ztranstype <> 'All')
          {
              $stmt = "SELECT COUNT(trl.TransactionRequestLogID) as count FROM transactionrequestlogs trl 
                    WHERE trl.LoyaltyCardNumber = ? AND trl.TransactionType = ? AND trl.StartDate >= ? 
                    AND trl.StartDate < ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztranstype);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo);
          }
          //then if both Transaction Status and Transaction type was selected of its choices, execute:
          else
          {
              $stmt = "SELECT COUNT(trl.TransactionRequestLogID) as count FROM transactionrequestlogs trl 
                    WHERE trl.LoyaltyCardNumber = ? AND trl.Status = ?
                    AND trl.TransactionType = ? AND trl.StartDate >= ? 
                    AND trl.StartDate < ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztransstatus);
              $this->bindparameter(3,$ztranstype);
              $this->bindparameter(4,$zFrom);
              $this->bindparameter(5,$zTo); 
          }
                
          $this->execute();
          return $this->fetchData();
      }
      
      
      /**
        * @author Gerardo V. Jagolino Jr.
        * @param $zFrom, $zTo, $transRefID, $cardnumber
        * @return array
        * get cashier username that is responsible for the transactions made
        */
      function getCashierUsername($zFrom, $zTo, $transRefID, $cardnumber){
           $stmt = "SELECT a.Name FROM npos.transactiondetails td USE INDEX (IX_transactiondetails_DateCreated)
                    INNER JOIN npos.accountdetails a ON td.CreatedByAID = a.AID
                    WHERE td.DateCreated >= ? AND td.DateCreated < ? AND 
                    TransactionReferenceID = ? AND LoyaltyCardNumber = ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$zFrom);
              $this->bindparameter(2,$zTo);
              $this->bindparameter(3,$transRefID);
              $this->bindparameter(4,$cardnumber);
              
              try {
            $this->execute();
          } catch(PDOException $e) {
              var_dump($e->getMessage()); exit;
          }
          $username = $this->fetchData();
          return $username['Name'];
      }
      
      /**
        * @author Gerardo V. Jagolino Jr.
        * @param $cardnumber, $ztransstatus, $ztranstype, $zFrom,$zTo, $zStart, $zLimit
        * @return array
        * get launchpad transaction logs
        */
      function getlptranslogsLP($cardnumber, $ztransstatus, $ztranstype, $zFrom,$zTo, $zStart, $zLimit)
      {
          
          //validate if combo boxes of transaction status and transaction type are selected ALL 
          if($ztransstatus == 'All' && $ztranstype == 'All')
          {
              $stmt = "SELECT trlp.TransactionReferenceID, trlp.SiteID, s.SiteCode, tm.TerminalCode, trlp.TerminalID, 
                  trlp.TransactionType, trlp.Amount, trlp.LoyaltyCardNumber, rf.ServiceName, trlp.StartDate, trlp.EndDate,
                  trlp.Status, trlp.ServiceTransactionID FROM transactionrequestlogslp trlp 
                    INNER JOIN terminals tm ON trlp.TerminalID = tm.TerminalID
                    INNER JOIN ref_services rf ON rf.ServiceID = trlp.ServiceID
                    INNER JOIN sites s ON s.SiteID = trlp.SiteID
                    WHERE trlp.LoyaltyCardNumber = ? AND trlp.StartDate >= ? 
                    AND trlp.StartDate < ? ORDER BY trlp.StartDate LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$zFrom);
              $this->bindparameter(3,$zTo);   
          }
          //then if Transaction Status was selected any of its choices (Success, Failed) AND Transaction Type was selected all
          elseif($ztransstatus <> 'All' && $ztranstype == 'All')
          {
              $stmt = "SELECT trlp.TransactionReferenceID, trlp.SiteID, s.SiteCode, tm.TerminalCode, trlp.TerminalID, 
                  trlp.TransactionType, trlp.Amount, trlp.LoyaltyCardNumber, rf.ServiceName, trlp.StartDate, trlp.EndDate,
                  trlp.Status, trlp.ServiceTransactionID FROM transactionrequestlogslp trlp 
                    INNER JOIN terminals tm ON trlp.TerminalID = tm.TerminalID
                    INNER JOIN ref_services rf ON rf.ServiceID = trlp.ServiceID
                    INNER JOIN sites s ON s.SiteID = trlp.SiteID
                    WHERE trlp.LoyaltyCardNumber = ? AND trlp.Status = ? AND trlp.StartDate >= ? 
                    AND trlp.StartDate < ? ORDER BY trlp.StartDate LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztransstatus);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo); 
          }
          //then if Transaction Status was selected all AND Transaction Type was selected ano of its choices (Deposit, Reload, Withdraw)
          elseif($ztransstatus == 'All' && $ztranstype <> 'All')
          {
              $stmt = "SELECT trlp.TransactionReferenceID, trlp.SiteID, s.SiteCode, tm.TerminalCode, trlp.TerminalID, 
                  trlp.TransactionType, trlp.Amount, trlp.LoyaltyCardNumber, rf.ServiceName, trlp.StartDate, trlp.EndDate, 
                  trlp.Status, trlp.ServiceTransactionID FROM transactionrequestlogslp trlp 
                    INNER JOIN terminals tm ON trlp.TerminalID = tm.TerminalID
                    INNER JOIN ref_services rf ON rf.ServiceID = trlp.ServiceID
                    INNER JOIN sites s ON s.SiteID = trlp.SiteID
                    WHERE trlp.LoyaltyCardNumber = ? AND trlp.TransactionType = ? AND trlp.StartDate >= ? 
                    AND trlp.StartDate < ? ORDER BY trlp.StartDate LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztranstype);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo); 
          }
          //then if both Transaction Status and Transaction type was selected of its choices, execute:
          else
          {
              $stmt = "SELECT trlp.TransactionReferenceID, trlp.SiteID, s.SiteCode, tm.TerminalCode, trlp.TerminalID, 
                  trlp.TransactionType, trlp.Amount, trlp.LoyaltyCardNumber, rf.ServiceName, trlp.StartDate, trlp.EndDate, 
                  trlp.Status, trlp.ServiceTransactionID FROM transactionrequestlogslp trlp 
                    INNER JOIN terminals tm ON trlp.TerminalID = tm.TerminalID
                    INNER JOIN ref_services rf ON rf.ServiceID = trlp.ServiceID
                    INNER JOIN sites s ON s.SiteID = trlp.SiteID
                    WHERE trlp.LoyaltyCardNumber = ? AND trlp.Status = ? AND trlp.TransactionType = ? AND trlp.StartDate >= ? 
                    AND trlp.StartDate < ? ORDER BY trlp.StartDate LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztransstatus);
              $this->bindparameter(3,$ztranstype);
              $this->bindparameter(4,$zFrom);
              $this->bindparameter(5,$zTo);   
          }
          
          try {
            $this->execute();
          } catch(PDOException $e) {
              var_dump($e->getMessage()); exit;
          }
          return $this->fetchAllData();
      }
      
      /**
        * @author Gerardo V. Jagolino Jr.
        * @param $cardnumber, $ztransstatus, $ztranstype, $zFrom,$zTo, $zStart, $zLimit
        * @return array
        * count launchpad transaction logs
        */
      function countlptranslogsLP($cardnumber, $ztransstatus, $ztranstype, $zFrom,$zTo)
      {           
          
          //validate if combo boxes of transaction status and transaction type are selected ALL 
          if($ztransstatus == 'All' && $ztranstype == 'All')
          {
              $stmt = "SELECT COUNT(trlp.TransactionRequestLogLPID) as count FROM transactionrequestlogslp trlp 
                    WHERE trlp.LoyaltyCardNumber = ? AND trlp.StartDate >= ? 
                    AND trlp.StartDate < ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$zFrom);
              $this->bindparameter(3,$zTo); 
          }
          //then if Transaction Status was selected any of its choices (Success, Failed) AND Transaction Type was selected all
          elseif($ztransstatus <> 'All' && $ztranstype == 'All')
          {
              $stmt = "SELECT COUNT(trlp.TransactionRequestLogLPID) as count FROM transactionrequestlogslp trlp 
                    WHERE trlp.LoyaltyCardNumber = ? AND trlp.Status = ? AND trlp.StartDate >= ? 
                    AND trlp.StartDate < ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztransstatus);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo); 
          }
          //then if Transaction Status was selected all AND Transaction Type was selected ano of its choices (Deposit, Reload, Withdraw)
          elseif($ztransstatus == 'All' && $ztranstype <> 'All')
          {
              $stmt = "SELECT COUNT(trlp.TransactionRequestLogLPID) as count FROM transactionrequestlogslp trlp 
                    WHERE trlp.LoyaltyCardNumber = ? AND trlp.TransactionType = ? AND trlp.StartDate >= ? 
                    AND trlp.StartDate < ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztranstype);
              $this->bindparameter(3,$zFrom);
              $this->bindparameter(4,$zTo); 
          }
          //then if both Transaction Status and Transaction type was selected of its choices, execute:
          else
          {
              $stmt = "SELECT COUNT(trlp.TransactionRequestLogLPID) as count FROM transactionrequestlogslp trlp 
                    WHERE trlp.LoyaltyCardNumber = ? AND trlp.Status = ? AND trlp.TransactionType = ? AND trlp.StartDate >= ? 
                    AND trlp.StartDate < ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$ztransstatus);
              $this->bindparameter(3,$ztranstype);
              $this->bindparameter(4,$zFrom);
              $this->bindparameter(5,$zTo); 
          }
                
          $this->execute();
          return $this->fetchData();
      }

      /**
        * @author gvjagolino
        * @param $terminalID
        * @return string
        * check terminal sessions
        */
      function checkTerminalSessions($terminalID,$vipterminalID)
     {
           $stmt = "SELECT COUNT(TerminalID) count FROM terminalsessions 
                WHERE TerminalID IN (?,?) ";
           $this->prepare($stmt);
           $this->bindparameter(1, $terminalID);
           $this->bindparameter(2, $vipterminalID);
           $this->execute($stmt);
           $count =  $this->fetchData();
           return $count['count'];
     }
     
     /**
        * @author Gerardo V. Jagolino Jr.
        * @param $terminalID
        * @return int
        * delete egm sessions from egmsessions table
        */
     function deleteEGMSessions($terminalID,$vipterminalID)
     {
        $this->begintrans();
        $this->prepare("DELETE FROM egmsessions WHERE TerminalID IN (?,?)");
        $this->bindparameter(1, $terminalID);
        $this->bindparameter(2, $vipterminalID);
        if($this->execute())
        {
            $this->committrans();
            return 1;
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
     }
     
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $terminalID
     * @return string
     * check egm sessions of a specific terminal
     */
     function checkEGMSessions($terminalID,$vipterminalID)
     {
           $stmt = "SELECT COUNT(EGMSessionID) egmcount FROM egmsessions 
                WHERE TerminalID IN (?,?) ";
           $this->prepare($stmt);
           $this->bindparameter(1, $terminalID);
           $this->bindparameter(2, $vipterminalID);
           $this->execute($stmt);
           $count =  $this->fetchData();
           return $count['egmcount'];
     }
     
     
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $terminalID
     * @return string
     * check egm sessions of a specific terminal
     */
     function getStackerBatchID($terminalID,$vipterminalID)
     {
           $stmt = "SELECT StackerBatchID FROM egmsessions 
                WHERE TerminalID IN (?,?) ";
           $this->prepare($stmt);
           $this->bindparameter(1, $terminalID);
           $this->bindparameter(2, $vipterminalID);
           $this->execute($stmt);
           $count =  $this->fetchData();
           return $count['StackerBatchID'];
     }
     
     function checkdeposit($stackerbatchid){
            $stmt = "SELECT Deposit FROM stackermanagement.stackersummary 
                WHERE StackerSummaryID = ? AND Status = 0";
           $this->prepare($stmt);
           $this->bindparameter(1, $stackerbatchid);
           $this->execute($stmt);
           $count =  $this->fetchData();
           return $count['Deposit'];
     }


     /**
     *counts details on cashier machine info (Disableing of cashier Terminal)
     * @return array | object 
     */
    function getTerminalCode($terminalid)
    {
        $stmt = "SELECT SiteID, TerminalCode FROM terminals WHERE TerminalID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $terminalid);
        $this->execute();
        return $this->fetchData();
    }
    
    
    /**
     *counts details on cashier machine info (Disableing of cashier Terminal)
     * @return array | object 
     */
    function getTerminalIDs($terminalcode)
    {
        $stmt = "SELECT TerminalID FROM terminals WHERE TerminalCode = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $terminalcode);
        $this->execute();
        return $this->fetchData();
    }
     
     
     function getServiceUserMode($serviceID)
     {
           $stmt = "SELECT UserMode FROM ref_services 
                WHERE ServiceID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $serviceID);
           $this->execute($stmt);
           $result =  $this->fetchData();
           return $result['UserMode'];
     }
     
     
     function getTerminalServicePassword($terminalid, $serviceID)
     {
           $stmt = "SELECT ServicePassword FROM terminalservices 
                WHERE TerminalID = ? AND ServiceID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $terminalid);
           $this->bindparameter(2, $serviceID);
           $this->execute($stmt);
           $result =  $this->fetchData();
           return $result['ServicePassword'];
     }
     
     function checkTerminalServices($terminalid, $serviceID)
     {
           $stmt = "SELECT * FROM terminalservices 
                WHERE TerminalID = ? AND ServiceID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $terminalid);
           $this->bindparameter(2, $serviceID);
           $this->execute($stmt);
           $result =  $this->fetchData();
           return $result;
     }
     
     
     public function getServiceGrpNameById($service_id){
        $sql = 'SELECT rsg.ServiceGroupName FROM ref_services rs
                INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                WHERE rs.ServiceID = ?';
        $this->prepare($sql);
        $this->bindparameter(1, $service_id);
        $this->execute($sql);
        $result =  $this->fetchData();
        if(!isset($result['ServiceGroupName']))
            return false;
        return $result['ServiceGroupName'];
    }
    
    
    public function getServiceGrpIDById($service_id){
        $sql = 'SELECT rsg.ServiceGroupID FROM ref_services rs
                INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                WHERE rs.ServiceID = ?';
        $this->prepare($sql);
        $this->bindparameter(1, $service_id);
        $this->execute($sql);
        $result =  $this->fetchData();
        if(!isset($result['ServiceGroupID']))
            return false;
        return $result['ServiceGroupID'];
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
    
    public function updateSSStatus($aid,$stackerbatchid, $status){
         $this->prepare("UPDATE stackersummary SET Status = ?, UpdatedByAID = ?, DateUpdated = now_usec() WHERE Status = 0 AND StackerSummaryID = ?");
         $this->bindparameter(1, $status);
         $this->bindparameter(2, $aid);
         $this->bindparameter(3, $stackerbatchid);
         $this->execute();
         return $this->rowCount();
    }
    
    public function getNamebyAid($aid){
        $sql = "SELECT Name FROM accountdetails WHERE AID = ?";
        $this->prepare($sql);
        $this->bindparameter(1, $aid);
        $this->execute();
        $Name = $this->fetchData();
        $Name = $Name['Name'];
        return $Name;
    }
    
    public function countfulfillmenthistroy($SiteID,$TerminalID,$transstatus, $From,$To){
        //validate if combo boxes of transaction status and transaction type are selected ALL 
          if($transstatus == 'All')
          {
              $stmt = "SELECT COUNT(trl.TransactionRequestLogID) AS Count FROM transactionrequestlogs trl LEFT JOIN transactiondetails td 
                          ON td.TransactionReferenceID = trl.TransactionReferenceID INNER JOIN terminals t ON t.TerminalID = trl.TerminalID 
                          INNER JOIN sites s ON s.SiteID = trl.SiteID INNER JOIN ref_services rs ON rs.ServiceID = trl.ServiceID
                          WHERE trl.SiteID =? AND trl.TerminalID =? AND trl.StartDate >= ? 
                          AND trl.StartDate < ? AND trl.Status IN (3,4)";
              $this->prepare($stmt);
              $this->bindparameter(1,$SiteID);
              $this->bindparameter(2,$TerminalID);
              $this->bindparameter(3,$From);
              $this->bindparameter(4,$To);   
          }
          else {
              $stmt = "SELECT COUNT(trl.TransactionRequestLogID) AS Count FROM transactionrequestlogs trl LEFT JOIN transactiondetails td 
                          ON td.TransactionReferenceID = trl.TransactionReferenceID INNER JOIN terminals t ON t.TerminalID = trl.TerminalID 
                          INNER JOIN sites s ON s.SiteID = trl.SiteID INNER JOIN ref_services rs ON rs.ServiceID = trl.ServiceID
                          WHERE trl.SiteID =? AND trl.TerminalID =? AND trl.StartDate >= ? 
                          AND trl.StartDate < ? AND trl.Status = ?";
              $this->prepare($stmt);
              $this->bindparameter(1,$SiteID);
              $this->bindparameter(2,$TerminalID);
              $this->bindparameter(3,$From);
              $this->bindparameter(4,$To);  
              $this->bindparameter(5,$transstatus);  
          }
          
          try {
            $this->execute();
          } catch(PDOException $e) {
              var_dump($e->getMessage()); exit;
          }
          return $this->fetchData();
    }
    
    public function getfulfillmenthistroy($SiteID,$TerminalID,$transstatus, $From,$To,$start, $limit){
        //validate if combo boxes of transaction status and transaction type are selected ALL 
          if($transstatus == 'All')
          {
              $stmt = "SELECT trl.TransactionRequestLogID, s.SiteCode, t.TerminalCode, trl.TransactionType, trl.Amount, rs.ServiceName, rs.UserMode, 
			  td.LoyaltyCardNumber, td.CreatedByAID, trl.TransactionDate, trl.Status FROM transactionrequestlogs trl LEFT JOIN transactiondetails td 
                          ON td.TransactionReferenceID = trl.TransactionReferenceID INNER JOIN terminals t ON t.TerminalID = trl.TerminalID 
                          INNER JOIN sites s ON s.SiteID = trl.SiteID INNER JOIN ref_services rs ON rs.ServiceID = trl.ServiceID
                          WHERE trl.SiteID =? AND trl.TerminalID =? AND trl.StartDate >= ? 
                          AND trl.StartDate < ? AND trl.Status IN (3,4) ORDER BY trl.StartDate LIMIT ".$start.", ".$limit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$SiteID);
              $this->bindparameter(2,$TerminalID);
              $this->bindparameter(3,$From);
              $this->bindparameter(4,$To);   
          }
          else {
              $stmt = "SELECT trl.TransactionRequestLogID, s.SiteCode, t.TerminalCode, trl.TransactionType, trl.Amount, rs.ServiceName, rs.UserMode, 
			  td.LoyaltyCardNumber, td.CreatedByAID, trl.TransactionDate, trl.Status FROM transactionrequestlogs trl LEFT JOIN transactiondetails td 
                          ON td.TransactionReferenceID = trl.TransactionReferenceID INNER JOIN terminals t ON t.TerminalID = trl.TerminalID 
                          INNER JOIN sites s ON s.SiteID = trl.SiteID INNER JOIN ref_services rs ON rs.ServiceID = trl.ServiceID
                          WHERE trl.SiteID =? AND trl.TerminalID =? AND trl.StartDate >= ? 
                          AND trl.StartDate < ? AND trl.Status = ? ORDER BY trl.StartDate LIMIT ".$start.", ".$limit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$SiteID);
              $this->bindparameter(2,$TerminalID);
              $this->bindparameter(3,$From);
              $this->bindparameter(4,$To);  
              $this->bindparameter(5,$transstatus);  
          }
          
          try {
            $this->execute();
          } catch(PDOException $e) {
              var_dump($e->getMessage()); exit;
          }
          return $this->fetchAllData();
    }
    
    
    public function exportfulfillmenthistroy($SiteID,$TerminalID,$transstatus, $From,$To){
        //validate if combo boxes of transaction status and transaction type are selected ALL 
          if($transstatus == 'All')
          {
              $stmt = "SELECT trl.TransactionRequestLogID, s.SiteCode, t.TerminalCode, trl.TransactionType, trl.Amount, rs.ServiceName, rs.UserMode, 
			  td.LoyaltyCardNumber, td.CreatedByAID, trl.TransactionDate, trl.Status FROM transactionrequestlogs trl LEFT JOIN transactiondetails td 
                          ON td.TransactionReferenceID = trl.TransactionReferenceID INNER JOIN terminals t ON t.TerminalID = trl.TerminalID 
                          INNER JOIN sites s ON s.SiteID = trl.SiteID INNER JOIN ref_services rs ON rs.ServiceID = trl.ServiceID
                          WHERE trl.SiteID =? AND trl.TerminalID =? AND trl.StartDate >= ? 
                          AND trl.StartDate < ? AND trl.Status IN (3,4) ORDER BY trl.StartDate";
              $this->prepare($stmt);
              $this->bindparameter(1,$SiteID);
              $this->bindparameter(2,$TerminalID);
              $this->bindparameter(3,$From);
              $this->bindparameter(4,$To);   
          }
          else {
              $stmt = "SELECT trl.TransactionRequestLogID, s.SiteCode, t.TerminalCode, trl.TransactionType, trl.Amount, rs.ServiceName, rs.UserMode, 
			  td.LoyaltyCardNumber, td.CreatedByAID, trl.TransactionDate, trl.Status FROM transactionrequestlogs trl LEFT JOIN transactiondetails td 
                          ON td.TransactionReferenceID = trl.TransactionReferenceID INNER JOIN terminals t ON t.TerminalID = trl.TerminalID 
                          INNER JOIN sites s ON s.SiteID = trl.SiteID INNER JOIN ref_services rs ON rs.ServiceID = trl.ServiceID
                          WHERE trl.SiteID =? AND trl.TerminalID =? AND trl.StartDate >= ? 
                          AND trl.StartDate < ? AND trl.Status = ? ORDER BY trl.StartDate";
              $this->prepare($stmt);
              $this->bindparameter(1,$SiteID);
              $this->bindparameter(2,$TerminalID);
              $this->bindparameter(3,$From);
              $this->bindparameter(4,$To);  
              $this->bindparameter(5,$transstatus);  
          }
          
          try {
            $this->execute();
          } catch(PDOException $e) {
              var_dump($e->getMessage()); exit;
          }
          return $this->fetchAllData();
    }
    public function remoteFileExists($url) {
     //check if file/ip in the given url is existing  
    return(bool)preg_match('~HTTP/1\.\d\s+200\s+OK~',@current(get_headers($url)));

    }
    /**
     * Remove Terminal Session
     * @param type $terminalid
     * @param type $vipterminalid
     * @return boolean
     * @author Mark Kenneth Esguerra
     * @date Febraury 10, 2015
     */
    public function removeTerminalSession($terminalid, $vipterminalid)
    {
        try
        {
            $stmt = "DELETE FROM terminalsessions 
                     WHERE TerminalID IN (?, ?)";
            $this->prepare($stmt);
            $this->bindparameter(1, $terminalid);
            $this->bindparameter(2, $vipterminalid);
            if ($this->execute())
            {
                try
                {
                    $this->committrans();
                    return true;
                }
                catch (PDOException $e)
                {
                    $this->rollbacktrans();
                    return false;
                }
            }
        }
        catch (PDOException $e)
        {
            $this->rollbacktrans();
            return false;
        }
        
    }
    /**
     * Get Terminal Session Details via TerminalID 
     * @param type $terminalid
     * @return type
     */
    public function getTerminalSessionDetails($terminalid)
    {
        $stmt = "SELECT TerminalID, 
                        LoyaltyCardNumber, 
                        MID, 
                        ServiceID, 
                        UBServiceLogin 
                 FROM terminalsessions 
                 WHERE TerminalID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $terminalid);
        $this->execute();
        $result = $this->fetchData();
        
        return $result;
    }
    /**
     * Check if player has terminal session
     * @param type $MID
     * @return type
     * @author ken ken
     */
    public function checkIfHasTerminalSession($cardnumber)
    {
        $stmt = "SELECT TerminalID, UserMode, UBServiceLogin, ServiceID    
                 FROM terminalsessions 
                 WHERE LoyaltyCardNumber = ? AND UserMode = 1";
        $this->prepare($stmt);
        $this->bindparameter(1, $cardnumber);
        $this->execute();
        $result = $this->fetchData();
        
        return $result;
    }
    public function getSiteCode ($siteID)
    {
        $stmt = "SELECT SiteCode 
                 FROM sites 
                 WHERE SiteID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $siteID);
        $this->execute();
        $result = $this->fetchData();
        
        return $result;
    }
    /**
     * Get MID by UB Card
     * @param type $ubcard
     * @return type
     * @author ken ken 
     * @date 2/26/2015
     */
    public function getMIDByUBCard ($ubcard)
    {
        $stmt = "SELECT MID 
                 FROM loyaltydb.membercards 
                 WHERE CardNumber = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $ubcard);
        $this->execute();
        $result = $this->fetchData();
        
        return $result;
    }
    /**
     * Check If Player started in EGM Session
     * @param type $MID
     * @return type
     */
    public function checkIfInEGMSession($MID)
    {
        $stmt = "SELECT COUNT(EGMSessionID) AS Count 
                 FROM egmsessions 
                 WHERE MID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $MID);
        $this->execute();
        $result = $this->fetchData();
        
        return $result;
    }
    /**
     * Check If has terminal session by MID. 
     * Fetch ALL
     * @param type $MID
     * @return type array(Terminal, UserMode, UBServiceLogin, ServiceID)
     * @author ken ken
     * @date 2/26/2015
     */
    public function checkIfHasTerminalSessionByMID($MID)
    {
        $stmt = "SELECT ts.TerminalID, 
                        ts.UserMode, 
                        ts.UBServiceLogin,
                        ts.ServiceID, 
                        ts.DateStarted, 
                        ts.LoyaltyCardNumber, 
                        s.SiteName, 
                        t.TerminalCode 
                 FROM terminalsessions ts 
                 INNER JOIN terminals t ON t.TerminalID = ts.TerminalID 
                 INNER JOIN sites s ON t.SiteID = s.SiteID 
                 WHERE ts.MID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $MID);
        $this->execute();
        $result = $this->fetchAllData();
        
        return $result;
    }
    /**
     * Check if Ewallet
     * @param type $MID
     * @return type
     * @author kenken
     * @date 02/27/2015
     */
    public function isEwallet($MID)
    {
        $stmt = "SELECT IsEwallet FROM 
                 membership.members WHERE MID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $MID);
        $this->execute();
        $result = $this->fetchData();
        
        return $result['IsEwallet'];
    }
    public function getServiceName($serviceID)
    {
        $stmt = "SELECT ServiceName 
                 FROM ref_services 
                 WHERE ServiceID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $serviceID);
        $this->execute();
        $result = $this->fetchData();
                
        return $result;
    }
    
    /**
      * @author Joene E. Floresca.
      * @param $cardnumber, $zFrom,$zTo
      * @return array
      * count all ub transaction ewallet to paginate
      */
      function countUBTransactionEwallet($cardnumber, $zFrom, $zTo)
      {     
              $stmt = "SELECT COUNT(DISTINCT(ts.TransactionsSummaryID)) as count
                                FROM transactionsummary ts 
                                LEFT JOIN npos.transactiondetails tdls ON ts.TransactionsSummaryID = tdls.TransactionSummaryID
                                LEFT JOIN npos.ref_services rs ON tdls.ServiceID = rs.ServiceID
                                LEFT JOIN npos.terminals t ON ts.TerminalID = t.TerminalID
                                LEFT JOIN npos.sites s ON s.SiteID = ts.SiteID 
                                WHERE ts.LoyaltyCardNumber = ? AND ts.DateStarted >= ? AND ts.DateStarted < ?
                                ORDER BY ts.DateStarted";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$zFrom);
              $this->bindparameter(3,$zTo); 
                
          $this->execute();
          return $this->fetchData();
      }
      
      function selectUBTransactionEwallet($cardnumber,  $zFrom,$zTo, $zStart, $zLimit)
      { 
              $stmt = "SELECT ts.TransactionsSummaryID, t.TerminalCode, s.SiteCode, rs.ServiceName, ts.StartBalance as StartingBalance, 
                            ts.WalletReloads as TotalEwalletload, ts.EndBalance as EndingBalance, ts.DateStarted as StartDate, ts.DateEnded as EndDate
                            FROM transactionsummary ts 
                            LEFT JOIN npos.transactiondetails tdls ON ts.TransactionsSummaryID = tdls.TransactionSummaryID
                            LEFT JOIN npos.ref_services rs ON tdls.ServiceID = rs.ServiceID
                            LEFT JOIN npos.terminals t ON ts.TerminalID = t.TerminalID
                            LEFT JOIN npos.sites s ON s.SiteID = ts.SiteID 
                            WHERE ts.LoyaltyCardNumber = ? AND ts.DateStarted >= ? AND ts.DateStarted < ?
                            GROUP BY ts.TransactionsSummaryID
                            ORDER BY ts.DateStarted LIMIT ".$zStart.", ".$zLimit."";
              $this->prepare($stmt);
              $this->bindparameter(1,$cardnumber);
              $this->bindparameter(2,$zFrom);
              $this->bindparameter(3,$zTo);   
          
          try {
            $this->execute();
          } catch(PDOException $e) {
              var_dump($e->getMessage()); exit;
          }
          return $this->fetchAllData();
      }
      /**
       * Get Transaction Summary ID via login.
       * @param type $login
       */
      public function getTransactionSummaryViaLogin($login) {
          $stmt = "SELECT TransactionSummaryID 
                   FROM terminalsessions 
                   WHERE UBServiceLogin = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $login);
          $this->execute();
          $result = $this->fetchData();
          
          return $result['TransactionSummaryID'];
      }
      /**
       * 
       * @param type $siteID
       * @param type $vctype
       */
      public function checkIfHasVirtualCashier($siteID, $vctype) {
          if ($vctype == 1) {//genesis virtual cashier 
            $stmt = "SELECT COUNT(sa.AID) as Count FROM siteaccounts sa 
                     INNER JOIN accounts a ON a.AID = sa.AID
                     WHERE sa.SiteID = ? AND a.AccountTypeID = 15";
          }
          else {
             $stmt = "SELECT COUNT(sa.AID)as Count FROM siteaccounts sa 
                      INNER JOIN accounts a ON a.AID = sa.AID
                      WHERE sa.SiteID = ? AND a.AccountTypeID = 17";  
          }
          $this->prepare($stmt);
          $this->bindparameter(1, $siteID);
          $this->execute();
          $result = $this->fetchData();
          
          return $result['Count'];
      }
      // account creation: insert record in account,accountdetails,siteaccounts table
      function insertaccount($zUserName,$zPassword,$zAccountTypeID,$zPasskey,$zStatus,$zAccountGroupID,$zDateLastLogin,$zLoginAttempts,
            $zSessionNoExpire,$zDateCreated,$zCreatedByAID,$zForChangePassword, $zWithPasskey,$vAID,$vName,$vAddress ,
              $vEmail,$vLandline,$vMobileNumber,$vOption1,$vOption2, $zdesignationID, $zSiteID, $zdateissued, $zdateexpires)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO accounts(UserName,Password,AccountTypeID,Passkey,Status,AccountGroupID,DateLastLogin,LoginAttempts,
              SessionNoExpire,DateCreated,CreatedByAID,ForChangePassword,WithPasskey, DatePasskeyIssued, DatePasskeyExpires) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
          $this->bindparameter(1, $zUserName);
          $this->bindparameter(2, $zPassword);
          $this->bindparameter(3, $zAccountTypeID);
          $this->bindparameter(4, $zPasskey);
          $this->bindparameter(5, $zStatus);
          $this->bindparameter(6, $zAccountGroupID);
          $this->bindparameter(7, $zDateLastLogin);
          $this->bindparameter(8, $zLoginAttempts);
          $this->bindparameter(9, $zSessionNoExpire);
          $this->bindparameter(10, $zDateCreated);
          $this->bindparameter(11, $zCreatedByAID);
          $this->bindparameter(12, $zForChangePassword);
          $this->bindparameter(13, $zWithPasskey);
          $this->bindparameter(14, $zdateissued);
          $this->bindparameter(15, $zdateexpires);
          if($this->execute())
          {
              $accountid = $this->insertedid();

              $this->prepare("INSERT INTO accountdetails(AID,Name,Address,Email,Landline,MobileNumber,Option1,Option2, DesignationID)
                  VALUES(?,?,?,?,?,?,?,?,?)");
              $this->bindparameter(1, $accountid);
              $this->bindparameter(2, $vName);
              $this->bindparameter(3, $vAddress);
              $this->bindparameter(4, $vEmail);
              $this->bindparameter(5, $vLandline);
              $this->bindparameter(6, $vMobileNumber);
              $this->bindparameter(7, $vOption1);
              $this->bindparameter(8, $vOption2);
              $this->bindparameter(9, $zdesignationID);
              if($this->execute())
              {
                  $accountdetailsid = $this->insertedid();
                  $this->prepare("INSERT INTO siteaccounts(SiteID,AID,Status) VALUES (?,?,?)");
                  $this->bindparameter(1,$zSiteID);
                  $this->bindparameter(2,$accountid);
                  $this->bindparameter(3,$zStatus);

                  if($this->execute())
                  {
                     $this->committrans();
                     return $accountid;
                  }
                  else
                  {
                     $this->rollbacktrans ();
                     return 0;
                  }
              }
              else
              {
                $this->rollbacktrans ();
                return 0;
              }
          }
          else
          {
              $this->rollbacktrans();
              return 0;
          }
      }
      public function getDate()
      {
         $time =microtime(true);
         $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);
         $rawdate = new DateTime( date('Y-m-d H:i:s.'.$micro_time, $time) );
         $date = $rawdate->format("Y-m-d H:i:s.u");
         return $date;
      }
}
?>