<?php

/* Created by : 
 * 
 * 
 * 
 */
//include "DbHandler.class.php";
//ini_set('display_errors',true);
//ini_set('log_errors',true);
//
//class Reports extends DBHandler
//{
//      public function __construct($sconectionstring)
//      {
//          parent::__construct($sconectionstring);
//      }

//      //get BCF of site
//      function getSiteBCF($zsiteid)
//      {
//          $stmt = "SELECT s.SiteID,s.SiteName,IF(s.Status=1,'Active','Inactive') as Status,IF(sb.Balance IS NULL,0,sb.Balance) as Balance
//                    FROM sites s
//                    LEFT JOIN sitebalance sb ON sb.SiteID=s.SiteID
//                    WHERE s.SiteID='" . $zsiteid . "'";
//          $this->executeQuery($stmt);
//          return $this->fetchAllData();
//      }

      //get BCF of all sites
//      function getAllSitesBCF($zaid)
//      {
//          $stmt = "SELECT s.SiteID,s.SiteName,IF(s.Status=1,'Active','Inactive') as Status,IF(sb.Balance IS NULL,0,sb.Balance) as Balance
//                    FROM siteaccounts sa
//                    LEFT JOIN sites s ON s.SiteID=sa.SiteID
//                    LEFT JOIN sitebalance sb ON sb.SiteID=s.SiteID
//                    LEFT JOIN accounts a ON a.AID=sa.AID
//                    WHERE sa.AID='" . $zaid . "'" ;
//          $this->executeQuery($stmt);
//          return $this->fetchAllData();
//      }

      //get all sites under account id
//      function getAccountSites($zaid = NULL)
//      {
//          $stmt = "SELECT s.SiteID,s.SiteName,s.Status
//                    FROM siteaccounts sa
//                    LEFT JOIN sites s ON s.SiteID=sa.SiteID
//                    where AID = '" . $zaid ."'";
//          $this->executeQuery($stmt);
//          return $this->fetchAllData();
//      }


      //get all sites under account id
//      function getActiveSites()
//      {
//          $stmt = "SELECT s.SiteID,s.SiteName,s.Status
//                    FROM sites s
//                    WHERE Status=1";
//          $this->executeQuery($stmt);
//          return $this->fetchAllData();
//      }

      //get all transactions of Site
//      function getSiteTransactions($zsiteid,$zdate,$zlimit = NULL)
//      {
//          if (!empty($zlimit))
//          {
//              $limitshow = "LIMIT " . $zlimit;
//          }
//          else
//          {
//              $limitshow = "";
//          }
//          
//          $stmt = "SELECT td.DateCreated,t.TerminalName,rs.ServiceName,
//                    IF(td.TransactionType='D','Deposit',IF(td.TransactionType='R','Reload','Withdraw')) as TransType,
//                    td.Amount
//                    FROM transactiondetails td
//                    LEFT JOIN sites s ON s.SiteID=td.SiteID
//                    LEFT JOIN ref_services rs ON rs.ServiceID=td.ServiceID
//                    LEFT JOIN terminals t ON t.TerminalID=td.TerminalID
//                    WHERE td.SiteID='" . $zsiteid . "' AND (td.DateCreated > '" . $zdate . " 06:00:00' AND td.DateCreated < '2011-06-30 06:00:00')
//                    ORDER BY td.DateCreated " . $limitshow;
//          
//          $this->executeQuery($stmt);
//          return $this->fetchAllData();
//      }

      //get GH of Site
//      function getGH($zsiteid)
//      {
//          $stmt = "";
//          $this->executeQuery($stmt);
//          return $this->fetchAllData();
//      }

      //get all 
//      function getSiteTopupHistory($zdatefrom,$zdateto,$zsiteid = NULL,$ztype = NULL)
//      {         
//          if($ztype > -1)
//          {
//              $wheretype = "AND sbl.TopupType = " . $ztype;
//          }
//          else
//          {
//              $wheretype = "";
//          }
//              
//          
//          if ($zsiteid > 0)
//          {
//              $stmt = "SELECT s.SiteName,sbl.DateCreated,sbl.PrevBalance,sbl.Amount,sbl.NewBalance,IF(sbl.TopupType=1,'Auto','Manual') as TopupType
//                        FROM sitebalancelogs sbl
//                        LEFT JOIN sites s ON s.SiteID=sbl.SiteID
//                        WHERE sbl.SiteID='" . $zsiteid . "' AND sbl.DateCreated BETWEEN '" . $zdatefrom . "' AND '" . $zdateto . "'
//                        $wheretype
//                        ORDER BY s.SiteName,sbl.DateCreated
//                    ";
//          }
//          else
//          {
//              $stmt = "SELECT s.SiteName,sbl.DateCreated,sbl.PrevBalance,sbl.Amount,sbl.NewBalance,IF(sbl.TopupType=1,'Auto','Manual') as TopupType
//                        FROM sitebalancelogs sbl
//                        LEFT JOIN sites s ON s.SiteID=sbl.SiteID
//                        WHERE sbl.DateCreated BETWEEN '" . $zdatefrom . "' AND '" . $zdateto . "'
//                        $wheretype
//                        ORDER BY s.SiteName,sbl.DateCreated
//                    ";
//          }
//              
//              
//          $this->executeQuery($stmt);
//          return $this->fetchAllData();
//      }


      
      
      //get all Bank Remittances of Site
//      function getBankRemittances($zsiteid = NULL,$zdatefrom,$zdateto,$zlimit = NULL)
//      {
//          if (!empty($zlimit))
//          {
//              $limitshow = "LIMIT " . $zlimit;
//          }
//          else
//          {
//              $limitshow = "";
//          }
//          
//          if (!empty($zsiteid))
//          {
//              $stmt = "SELECT s.SiteName,sr.BankTransactionDate,rb.BankName,sr.Branch,rr.RemittanceName,sr.ChequeNumber,sr.Particulars,sr.Amount,rr.RemittanceName,
//                        IF(sr.Status=1,'Valid','Invalid') as Status
//                        FROM siteremittance sr
//                        LEFT JOIN sites s ON s.SiteID=sr.SiteID
//                        LEFT JOIN ref_banks rb ON rb.BankID=sr.BankID
//                        LEFT JOIN ref_remittancetype rr ON rr.RemittanceTypeID=sr.RemittanceTypeID
//                        WHERE sr.SiteID='" . $zsiteid . "' AND sr.BankTransactionDate BETWEEN '" . $zdatefrom . "' AND '" . $zdateto . "'
//                        ORDER BY sr.BankTransactionDate " . $limitshow;
//          }
//          else
//          {
//              $stmt = "SELECT s.SiteName,sr.BankTransactionDate,rb.BankName,sr.Branch,rr.RemittanceName,sr.ChequeNumber,sr.Particulars,sr.Amount,rr.RemittanceName,
//                        IF(sr.Status=1,'Valid','Invalid') as Status
//                        FROM siteremittance sr
//                        LEFT JOIN sites s ON s.SiteID=sr.SiteID
//                        LEFT JOIN ref_banks rb ON rb.BankID=sr.BankID
//                        LEFT JOIN ref_remittancetype rr ON rr.RemittanceTypeID=sr.RemittanceTypeID
//                        WHERE sr.BankTransactionDate BETWEEN '" . $zdatefrom . "' AND '" . $zdateto . "'
//                        ORDER BY s.SiteID,sr.BankTransactionDate " . $limitshow;
//          }
//          
//          $this->executeQuery($stmt);
//          return $this->fetchAllData();
//      }

      //get all 
//      function getSiteGH($zsiteid,$zdatefrom,$zdateto)
//      {
//
//          $stmt = "SELECT TransactionType, SUM(Amount) as TotalAmount
//                    FROM transactiondetails 
//                    WHERE SiteID='" . $zsiteid . "' AND DateCreated  BETWEEN '" . $zdatefrom . "' AND '" . $zdateto . "'
//                    GROUP BY TransactionType  
//                ";
//          $this->executeQuery($stmt);
//          return $this->fetchAllData();
//      }
      
//      //get all Bank Remittances of Site
//      function getAllBankRemittances($zdatefrom,$zdateto,$zlimit = NULL)
//      {
//          if (!empty($zlimit))
//          {
//              $limitshow = "LIMIT " . $zlimit;
//          }
//          else
//          {
//              $limitshow = "";
//          }
//          
//          $stmt = "SELECT s.SiteName,sr.BankTransactionDate,rb.BankName,sr.Branch,rr.RemittanceName,sr.ChequeNumber,sr.Particulars,sr.Amount,rr.RemittanceName,
//                    IF(sr.Status=1,'Valid','Invalid') as Status
//                    FROM siteremittance sr
//                    LEFT JOIN sites s ON s.SiteID=sr.SiteID
//                    LEFT JOIN ref_banks rb ON rb.BankID=sr.BankID
//                    LEFT JOIN ref_remittancetype rr ON rr.RemittanceTypeID=sr.RemittanceTypeID
//                    WHERE sr.BankTransactionDate BETWEEN '" . $zdatefrom . "' AND '" . $zdateto . "'
//                    ORDER BY s.SiteID,sr.BankTransactionDate " . $limitshow;
//          
//          $this->executeQuery($stmt);
//          return $this->fetchAllData();
//      }


//}
?>