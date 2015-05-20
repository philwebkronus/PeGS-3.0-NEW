<?php

include "DbHandler.class.php";
include "AppendArray.class.php";


class TopUp2 extends DBHandler{
    
   public $cut_off = CUT_OFF;
    
    public function __construct($sconectionstring)
    {
        parent::__construct($sconectionstring);
    } 
    
    
    public function grossHoldMonitoring($sort,$dir,$startdate,$enddate) {

          if(isset($_GET['siteid']) && $_GET['siteid'] != '') {
                $query = "SELECT s.SiteID, s.POSAccountNo , s.SiteName, IFNULL(sb.Balance, 0) AS BCF,
                                    IFNULL(SUM(mr.ActualAmount), 0) AS ManualRedemption,
                                    CASE sd.RegionID WHEN 17 THEN 'Metro Manila' ELSE 'Provincial' END AS Location,
                                    sb.MinBalance
                                    FROM sites s 
                                    LEFT JOIN  sitebalance sb ON s.SiteID = sb.SiteID
                                    LEFT JOIN  sitedetails sd ON s.SiteID = sd.SiteID
                                    LEFT JOIN manualredemptions mr FORCE INDEX(IX_manualredemptions_TransactionDate) ON s.SiteID = mr.SiteID
                                      AND mr.TransactionDate >= ? AND mr.TransactionDate < ?
                                    WHERE s.SiteID NOT IN (1, 235)
                                    AND s.SiteID = ?
                                    ORDER BY s.$sort $dir";
              
          } else {
                $query = "SELECT s.SiteID, s.POSAccountNo , s.SiteName, IFNULL(sb.Balance, 0) AS BCF,
                                    IFNULL(SUM(mr.ActualAmount), 0) AS ManualRedemption,
                                    CASE sd.RegionID WHEN 17 THEN 'Metro Manila' ELSE 'Provincial' END AS Location,
                                    sb.MinBalance
                                    FROM sites s 
                                    LEFT JOIN  sitebalance sb ON s.SiteID = sb.SiteID
                                    LEFT JOIN  sitedetails sd ON s.SiteID = sd.SiteID
                                    LEFT JOIN manualredemptions mr FORCE INDEX(IX_manualredemptions_TransactionDate) ON s.SiteID = mr.SiteID
                                      AND mr.TransactionDate >= ? AND mr.TransactionDate < ?
                                    WHERE s.SiteID NOT IN (1, 235)
                                    GROUP By s.SiteID
                                    ORDER BY s.$sort $dir";
              
          }
          $this->prepare($query);
          $this->bindparameter(1, $startdate);
          $this->bindparameter(2, $enddate);
          if(isset($_GET['siteid']) && $_GET['siteid']) {
            $this->bindparameter(3, $_GET['siteid']);
          }    
        
          $this->execute();        
          $rows1 =  $this->fetchAllData();

          $varrmerge = array();
          $vtotprintedtickets = array();
          foreach($rows1 as $itr => $value) 
          {                
                 $varrmerge[$itr] = array(
                    'SiteID'=>$value['SiteID'],
                     'POSAccountNo'=>$value['POSAccountNo'],
                     'SiteName'=>$value['SiteName'],
                    'BCF'=>$value['BCF'],
                    'ActualAmount'=>$value['ManualRedemption'],
                    'Location'=>$value['Location'],
                     'MinBalance' =>$value['MinBalance'],
                    'Deposit'=>"0.00",
                    'EwalletLoads'=>"0.00", 
                    'EwalletCashLoads'=>"0.00", 
                    'Reload'=>"0.00",
                    'Redemption'=>"0.00",
                    'EwalletWithdrawal'=>"0.00", 
                    'PrintedTickets'=>"0.00",
                    'UnusedTickets'=>"0.00",
                    'RunningActiveTickets'=>"0.00",
                    'EncashedTickets'=>"0.00",
                    'DepositCash'=>"0.00",
                    'ReloadCash'=>"0.00",
                    'RedemptionCashier'=>"0.00",
                    'Coupon'=>"0.00"
                 ); 
          }
          
          foreach($varrmerge as $key => $trans) {
                $vsiteID[$key] = $trans['SiteID'];
          }
          
          
          
          $sites = implode(",", $vsiteID);
          
            $querydeposit = "SELECT SUM(Amount) AS Deposit, SiteID FROM npos.transactiondetails 
              FORCE INDEX(IX_transactiondetails_DateCreated) WHERE TransactionType = 'D' AND DateCreated >= ? 
              AND DateCreated < ? AND Status In (1,4) AND SiteID IN ($sites) GROUP BY SiteID";
          
            $this->prepare($querydeposit);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
            $this->execute();  
            $rowsdeposit =  $this->fetchAllData();
            
            foreach ($rowsdeposit as $valuez1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($valuez1["SiteID"] == $value2["SiteID"]){
                        if($valuez1["Deposit"] != '0.00'){
                            $varrmerge[$keys]["Deposit"] = (float)$varrmerge[$keys]["Deposit"] + (float)$valuez1["Deposit"];
                        }
                    }
                }  
            }
            
            $querydepositcash = "SELECT tr.SiteID, SUM(CASE tr.TransactionType
                                   WHEN 'D' THEN
                                     CASE tr.PaymentType
                                       WHEN 2 THEN 0 -- Coupon
                                       ELSE -- Not Coupon
                                         CASE IFNULL(tr.StackerSummaryID, '')
                                           WHEN '' THEN tr.Amount -- Cash
                                           ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                             (SELECT IFNULL(SUM(Amount), 0)
                                             FROM stackermanagement.stackerdetails sdtls
                                             WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                   AND sdtls.TransactionType = 1
                                                   AND sdtls.PaymentType = 0)  -- Deposit, Cash
                                         END
                                    END
                                   ELSE 0 -- Not Deposit
                                END) As DepositCash 
            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  WHERE tr.SiteID IN ($sites)
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4) GROUP BY SiteID;";
          
            $this->prepare($querydepositcash);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
            $this->execute();  
            $rowsdepositcash =  $this->fetchAllData();
            
            foreach ($rowsdepositcash as $valuez2) {
                foreach ($varrmerge as $keys => $value2) {
                    if($valuez2["SiteID"] == $value2["SiteID"]){
                        if($valuez2["DepositCash"] != '0.00'){
                            $varrmerge[$keys]["DepositCash"] = (float)$varrmerge[$keys]["DepositCash"] + (float)$valuez2["DepositCash"];
                        }
                    }
                }  
            }
            
            
            $queryreload = "SELECT SUM(Amount) AS Reload, SiteID FROM npos.transactiondetails FORCE INDEX(IX_transactiondetails_DateCreated) 
                WHERE TransactionType = 'R' AND DateCreated >= ? AND DateCreated < ? 
                AND Status In (1,4) AND SiteID IN ($sites) GROUP BY SiteID";
          
            $this->prepare($queryreload);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
            $this->execute();  
            $rowsreload =  $this->fetchAllData();
            
            foreach ($rowsreload as $valuez3) {
                foreach ($varrmerge as $keys => $value2) {
                    if($valuez3["SiteID"] == $value2["SiteID"]){
                        if($valuez3["Reload"] != '0.00'){
                            $varrmerge[$keys]["Reload"] = (float)$varrmerge[$keys]["Reload"] + (float)$valuez3["Reload"];
                        }
                    }
                }  
            }
            
            $querydepositcoupon = "SELECT tr.SiteID, SUM(CASE tr.TransactionType
                                  WHEN 'D' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN tr.Amount
                                      ELSE 0
                                     END
                                  ELSE 0 END) As DepositCoupon
            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID WHERE tr.SiteID IN ($sites)
                AND tr.DateCreated >= ? AND tr.DateCreated < ? AND tr.Status IN(1,4) Group BY SiteID";
          
            $this->prepare($querydepositcoupon);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
            $this->execute();  
            $rowsdepositcoupon =  $this->fetchAllData();
            
            foreach ($rowsdepositcoupon as $valuezdc) {
                foreach ($varrmerge as $keys => $value2) {
                    if($valuezdc["SiteID"] == $value2["SiteID"]){
                        if($valuezdc["DepositCoupon"] != '0.00'){
                            $varrmerge[$keys]["Coupon"] = (float)$varrmerge[$keys]["Coupon"] + (float)$valuezdc["DepositCoupon"];
                        }
                    }
                }  
            }
            
            
            $queryreloadcoupon = "SELECT tr.SiteID, SUM(CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN tr.Amount
                                      ELSE 0
                                     END
                                  ELSE 0 END) As ReloadCoupon
            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID WHERE tr.SiteID IN ($sites)
                AND tr.DateCreated >= ? AND tr.DateCreated < ? AND tr.Status IN(1,4) Group BY SiteID";
          
            $this->prepare($queryreloadcoupon);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
            $this->execute();  
            $rowsreloadcoupon =  $this->fetchAllData();
            
            foreach ($rowsreloadcoupon as $valuezdc) {
                foreach ($varrmerge as $keys => $value2) {
                    if($valuezdc["SiteID"] == $value2["SiteID"]){
                        if($valuezdc["ReloadCoupon"] != '0.00'){
                            $varrmerge[$keys]["Coupon"] = (float)$varrmerge[$keys]["Coupon"] + (float)$valuezdc["ReloadCoupon"];
                        }
                    }
                }  
            }
            
            
            $queryreloadcash = "SELECT tr.SiteID, SUM(CASE tr.TransactionType
                                   WHEN 'R' THEN
                                     CASE tr.PaymentType
                                       WHEN 2 THEN 0 -- Coupon
                                       ELSE -- Not Coupon
                                         CASE IFNULL(tr.StackerSummaryID, '')
                                           WHEN '' THEN tr.Amount -- Cash
                                           ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                              (SELECT IFNULL(SUM(Amount), 0)
                                --              (SELECT IFNULL(Amount, 0)
                                             FROM stackermanagement.stackerdetails sdtls
                                             WHERE sdtls.stackersummaryID = tr.StackerSummaryID
                                                   AND tr.TransactionDetailsID = sdtls.TransactionDetailsID
                                                   AND sdtls.TransactionType = 2
                                                   AND sdtls.PaymentType = 0)  -- Reload, Cash
                                         END
                                     END
                                   ELSE 0 -- Not Reload
                                END) As ReloadCash
            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) WHERE tr.SiteID IN ($sites)
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4) GROUP BY SiteID";
          
            $this->prepare($queryreloadcash);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
            $this->execute();  
            $rowsreloadcash =  $this->fetchAllData();
            
            foreach ($rowsreloadcash as $valuez4) {
                foreach ($varrmerge as $keys => $value2) {
                    if($valuez4["SiteID"] == $value2["SiteID"]){
                        if($valuez4["ReloadCash"] != '0.00'){
                            $varrmerge[$keys]["ReloadCash"] = (float)$varrmerge[$keys]["ReloadCash"] + (float)$valuez4["ReloadCash"];
                        }
                    }
                }  
            }
            
            
            $queryredemptions = "SELECT SUM(Amount) AS Redemption, SiteID FROM npos.transactiondetails FORCE INDEX(IX_transactiondetails_DateCreated) 
                WHERE TransactionType = 'W' AND DateCreated >= ? AND DateCreated < ? 
                AND Status In (1,4) AND SiteID IN ($sites) GROUP BY SiteID;";
          
            $this->prepare($queryredemptions);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
            $this->execute();  
            $rowsredemptions =  $this->fetchAllData();
            
            foreach ($rowsredemptions as $valuez5) {
                foreach ($varrmerge as $keys => $value2) {
                    if($valuez5["SiteID"] == $value2["SiteID"]){
                        if($valuez5["Redemption"] != '0.00'){
                            $varrmerge[$keys]["Redemption"] = (float)$varrmerge[$keys]["Redemption"] + (float)$valuez5["Redemption"];
                        }
                    }
                }  
            }
            
            
            $queryredemptionscashier = "SELECT SUM(Amount) AS RedemptionCashier, tr.SiteID FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
                INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                WHERE tr.TransactionType = 'W' AND tr.DateCreated >= ? AND tr.DateCreated < ? 
                AND tr.Status In (1,4) AND tr.SiteID IN ($sites) AND a.AccountTypeID NOT IN (15, 16) Group By SiteID";
          
            $this->prepare($queryredemptionscashier);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
            $this->execute();  
            $rowsredemptionscashier =  $this->fetchAllData();
            
            foreach ($rowsredemptionscashier as $valuez6) {
                foreach ($varrmerge as $keys => $value2) {
                    if($valuez6["SiteID"] == $value2["SiteID"]){
                        if($valuez6["RedemptionCashier"] != '0.00'){
                            $varrmerge[$keys]["RedemptionCashier"] = (float)$varrmerge[$keys]["RedemptionCashier"] + (float)$valuez6["RedemptionCashier"];
                        }
                    }
                }  
            }

            
            $query4 = "SELECT tr.SiteID, IFNULL(SUM(stckr.Withdrawal), 0) AS PrintedTickets FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
                            INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                            INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                            INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                            LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                            WHERE tr.SiteID IN ($sites)
                              AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                              AND tr.Status IN(1,4)
                              AND tr.TransactionType = 'W'
                              AND tr.StackerSummaryID IS NOT NULL
                              GROUP BY tr.SiteID";
            
            //Get the total Printed Tickets per site
            $this->prepare($query4);
            $this->bindparameter(":startdate", $startdate);
            $this->bindparameter(":enddate", $enddate);
            $this->execute();  
            $rows4 =  $this->fetchAllData();

            foreach ($rows4 as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                        $varrmerge[$keys]["PrintedTickets"] = (float)$value1["PrintedTickets"];
                        break;
                    }
                }  
            }
            
            //Format the pick date into Year-Month-Day
            $fdate = new DateTime($startdate);
            $formatteddate = $fdate->format('Y-m-d');

            //Set the Date Today less 1 day for comparison
            $cdate = new DateTime(date('Y-m-d'));
            $cdate->sub(date_interval_create_from_date_string('1 day'));
            $comparedate = $cdate->format('Y-m-d');
            
            $query5 = "SELECT SiteID, IFNULL(SUM(Amount), 0) AS UnusedTickets FROM
                                        ((SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode, tr.SiteID FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
                                          INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                          INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                          INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                          LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                          WHERE tr.SiteID IN ($sites)
                                            AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                                            AND tr.Status IN(1,4)
                                            AND tr.TransactionType = 'W'
                                            AND tr.StackerSummaryID IS NOT NULL
                                            AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4,15)
                                            AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID IN ($sites)))
                                                )
                                        UNION ALL
                                        (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode, sa.SiteID FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
                                          INNER JOIN npos.siteaccounts sa ON stckr.CreatedByAID = sa.AID
                                          WHERE stckr.Status IN (1, 2)
                                          AND stckr.DateCancelledOn >= :startdate AND stckr.DateCancelledOn < :enddate
                                          AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4, 15))
                                          AND sa.SiteID IN ($sites)
                                          )) AS UnionPrintedTickets
                                        WHERE TicketCode NOT IN  
                                                (SELECT tckt.TicketCode FROM vouchermanagement.tickets tckt -- Less: Encashed Tickets
                                                        INNER JOIN npos.accounts acct ON  tckt.EncashedByAID = acct.AID
                                                        INNER JOIN npos.siteaccounts sa ON tckt.EncashedByAID = sa.AID
                                                        WHERE tckt.DateEncashed >= :startdate AND tckt.DateEncashed < :enddate
                                                          AND acct.AccountTypeID = 4 AND sa.SiteID IN ($sites)
                                                        UNION ALL
                                                        (SELECT stckrdtls.VoucherCode AS TicketCode
                                                          FROM stackermanagement.stackersummary stckr
                                                          INNER JOIN stackermanagement.stackerdetails stckrdtls ON stckr.StackerSummaryID = stckrdtls.StackerSummaryID
                                                          WHERE stckrdtls.PaymentType = 2
                                                                AND stckrdtls.StackerSummaryID IN
                                                                  (SELECT tr.StackerSummaryID
                                                                        FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)
                                                                        INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                                                        INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                                                        INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                                                        LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                                                        WHERE tr.SiteID IN ($sites)
                                                                          AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                                                                          AND tr.Status IN(1,4)
                                                                          AND tr.TransactionType In ('D', 'R')
                                                                                AND tr.StackerSummaryID IS NOT NULL
                                                                          AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4,15)
                                                                          AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID IN ($sites))))
                                                        )
                                                )
                                GROUP BY SiteID";
            
            //Get the total Unused Tickets per site
            $this->prepare($query5);
            $this->bindparameter(":startdate", $startdate);
            $this->bindparameter(":enddate", $enddate);
            $this->execute();  
            $rows5 =  $this->fetchAllData();

            foreach ($rows5 as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                        $varrmerge[$keys]["UnusedTickets"] = (float)$value1["UnusedTickets"];
                        break;
                    }
                }  
            }

            $query6 = "SELECT tckt.SiteID, IFNULL(SUM(tckt.Amount), 0) AS EncashedTickets FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                            WHERE tckt.DateEncashed >= ? AND tckt.DateEncashed < ?
                            AND tckt.SiteID IN ($sites)
                            GROUP BY tckt.SiteID";
        
        //Get the total Encashed Tickets per site
        $this->prepare($query6);
        $this->bindparameter(1, $startdate);
        $this->bindparameter(2, $enddate);
        $this->execute();  
        $rows6 =  $this->fetchAllData();
        foreach ($rows6 as $value1) {
            foreach ($varrmerge as $keys => $value2) {
                if($value1["SiteID"] == $value2["SiteID"]){
                    $varrmerge[$keys]["EncashedTickets"] = (float)$value1["EncashedTickets"];
                    break;
                }
            }  
        }

        $query7 = "SELECT SiteID, IFNULL(RunningActiveTickets, 0) AS RunningActiveTickets
                                FROM sitegrossholdcutoff 
                                WHERE SiteID IN ($sites)
                                AND DateCutOff = :cutoffdate ";
        
        
        $query8 = "SELECT SiteID, IFNULL(SUM(Amount), 0) AS ExpiredTickets FROM vouchermanagement.tickets
                                WHERE SiteID IN ($sites) 
                                 AND (ValidToDate >= :startlimitdate AND ValidToDate <= :endlimitdate) AND ValidToDate <= now(6)
                                AND Status IN (1,2,7)
                                AND DateEncashed IS NULL 
                                GROUP BY SiteID ORDER BY SiteID";
        
        
        $query9 = "SELECT SiteID, IFNULL(SUM(Amount), 0) AS LessTickets FROM vouchermanagement.tickets
                                WHERE SiteID IN ($sites) 
                                AND (DateUpdated >= :startlimitdate AND DateUpdated <= :endlimitdate)
                                AND (Status IN (4,3)
                                    OR DateEncashed IS NOT NULL)
                                ORDER BY SiteID";
        
        $query10 = "SELECT et.SiteID, et.CreatedByAID, ad.Name,

                                -- Total e-wallet Withdrawal
                                SUM(CASE et.TransType
                                        WHEN 'W' THEN et.Amount -- if redemption
                                        ELSE 0 -- if not redemption
                                END) AS EwalletRedemption,
                                
                                SUM(CASE IFNULL(et.TraceNumber,'')
                                        WHEN '' THEN  
                                                CASE IFNULL(et.ReferenceNumber, '')
                                                WHEN '' THEN -- if not bancnet
                                                        CASE et.TransType
                                                        WHEN 'D' THEN -- if deposit
                                                                CASE et.PaymentType 
                                                                WHEN 1 THEN et.Amount -- if Cash
                                                                ELSE 0 -- if not Cash
                                                                END
                                                        ELSE 0 -- if not deposit
                                                        END
                                                ELSE 0 -- if bancnet
                                                END
                                        ELSE 0
                                END) AS EwalletCashDeposit,
                                
                                SUM(CASE IFNULL(et.TraceNumber,'')
                                        WHEN '' THEN 0
                                        ELSE CASE IFNULL(et.ReferenceNumber, '')
                                                WHEN '' THEN 0 -- if not bancnet
                                                ELSE CASE et.TransType -- if bancnet
                                                        WHEN 'D' THEN et.Amount -- if deposit
                                                        ELSE 0 -- if not deposit
                                                        END
                                                END
                                END) AS EwalletBancnetDeposit,
                                
                                SUM(CASE et.TransType
                                        WHEN 'D' THEN -- if deposit
                                                CASE et.PaymentType
                                                WHEN 2 THEN et.Amount -- if voucher
                                                ELSE 0 -- if not voucher
                                                END
                                        ELSE 0 -- if not deposit
                                END) AS EwalletVoucherDeposit

                            FROM npos.ewallettrans et
                            LEFT JOIN npos.accountdetails ad ON et.CreatedByAID = ad.AID
                            WHERE et.StartDate >= :startlimitdate AND et.StartDate <= :endlimitdate
                            AND et.SiteID IN (".$sites.") AND et.Status IN (1,3)
                            GROUP BY et.CreatedByAID";
        
        if($formatteddate == $comparedate) { //Date Started is less than 1 day of the date today
            
            $firstdate = new DateTime($comparedate);
            $firstdate->sub(date_interval_create_from_date_string('1 day'));
            $date1 = $firstdate->format('Y-m-d')." 06:00:00";
            $date2 = $comparedate." 06:00:00";
            
            //Get the Running Active Tickets of the date less than 2 days of the date today if the pick date is less than 1 day of the date today
            //ex: Current Date = June 1, Pick Date = May 31: Get the Active tickets for May 30
            $this->prepare($query7);
            $this->bindparameter(':cutoffdate', $date2);
            $this->execute();  
            $rows7 =  $this->fetchAllData();

            foreach ($rows7 as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                        if($varrmerge[$keys]["RunningActiveTickets"] == "0.00"){
                            $varrmerge[$keys]["RunningActiveTickets"] = (float)$value1["RunningActiveTickets"];
                        } else {
                            $varrmerge[$keys]["RunningActiveTickets"] = $varrmerge[$keys]["RunningActiveTickets"] + (float)$value1["RunningActiveTickets"];
                        }
                        break;
                    }
                }  
            }
            
            //Date to use for Expired Ticket Query
             $sldate = new DateTime($startdate);
            $startlimitdate = $sldate->format('Y-m-d')." 00:00:00.000000";
            $eldate = new DateTime($startdate);
            $endlimitdate = $eldate->format('Y-m-d')." 23:59:59.000000";
            
            //Get the Expired Tickets per site
            $this->prepare($query8);
            $this->bindparameter(':startlimitdate', $startlimitdate);
            $this->bindparameter(':endlimitdate', $endlimitdate);
            $this->execute();  
            $rows8 =  $this->fetchAllData();
            
            //Less the Expired Tickets to Total Unused Tickets
            foreach ($rows8 as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                            $varrmerge[$keys]["RunningActiveTickets"] = (float)$varrmerge[$keys]["RunningActiveTickets"]  - (float)$value1["ExpiredTickets"];
                        break;
                    }
                }  
            }
            
            //Date to use for Ticket Query To be less in active running tickets
             $sldate = new DateTime($startdate);
            $startlimitdate = $sldate->format('Y-m-d')." 06:00:00.000000";
            $endlimitdate = date('Y-m-d')." 06:00:00.000000";
            
            //Get the Tickets to be less in active running tickets per site
            $this->prepare($query9);
            $this->bindparameter(':startlimitdate', $startlimitdate);
            $this->bindparameter(':endlimitdate', $endlimitdate);
            $this->execute();  
            $rows9 =  $this->fetchAllData();
            
           //Less the tickets used/encashed for the recalculated dates
            foreach ($varrmerge as $keys => $value2) {
                foreach ($rows9 as $value1) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                            $vaddtorunningtickets = (float)$varrmerge[$keys]["PrintedTickets"]  - (float)$value1["LessTickets"];
                            $varrmerge[$keys]["RunningActiveTickets"] = (float)$varrmerge[$keys]["RunningActiveTickets"]  + (float)$vaddtorunningtickets;
                        break;
                    } else if($value2["PrintedTickets"] != "0.00" && $value1["SiteID"] != $value2["SiteID"]) {
                        $varrmerge[$keys]["RunningActiveTickets"] = (float)$varrmerge[$keys]["RunningActiveTickets"]  + (float)$varrmerge[$keys]["PrintedTickets"] ;
                    }
                }  
            }
            
        } else if($formatteddate != date('Y-m-d') && $formatteddate != $comparedate){ //Date Started is not less than 1 day nor equal to the date today

            //Get the Running Active Tickets for Pick Date, if the Pick Date is not less than 1 day nor equal to the date today
            //ex: Current Date = June 4, Pick Date = June 2: Get the Active tickets from sitegrosshold for June 2
            $this->prepare($query7);
            $this->bindparameter(':cutoffdate', $enddate);
            $this->execute();  
            $rows7 =  $this->fetchAllData();

            foreach ($rows7 as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                        if($varrmerge[$keys]["RunningActiveTickets"] == "0.00"){
                            $varrmerge[$keys]["RunningActiveTickets"] = (float)$value1["RunningActiveTickets"];
                        } else {
                            $varrmerge[$keys]["RunningActiveTickets"] = $varrmerge[$keys]["RunningActiveTickets"] + (float)$value1["RunningActiveTickets"];
                        }
                        break;
                    }
                }  
            }
        } else if($formatteddate == date('Y-m-d')){ //Date Started/Pick Date is equal to the date today
            
            //Set the Date Range in getting the Unused Ticket for Pick Date less 1 Day Cutoff
            $firstdate = new DateTime($formatteddate);
            $firstdate->sub(date_interval_create_from_date_string('1 day'));
            $date1 = $firstdate->format('Y-m-d')." 06:00:00";
            $date2 = $formatteddate." 06:00:00";
            
            //Set the Date Range in getting the Running Active Tickets for Pick Date less 2 Days Cutoff
            $seconddate = new DateTime($date1);
            $seconddate->sub(date_interval_create_from_date_string('1 day'));
            
            //Get the Running Active Tickets of the date less than 2 days of the date today if the pick date is equal to the date today
            //ex: Current Date = June 4, Pick Date = June 4: Get the Active tickets from sitegrosshold for June 2
            $this->prepare($query7);
            $this->bindparameter(':cutoffdate', $date1);
            $this->execute();  
            $rows7 =  $this->fetchAllData();

            foreach ($rows7 as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                        if($varrmerge[$keys]["RunningActiveTickets"] == "0.0"){
                            $varrmerge[$keys]["RunningActiveTickets"] = (float)$value1["RunningActiveTickets"];
                        } else {
                            $varrmerge[$keys]["RunningActiveTickets"] = $varrmerge[$keys]["RunningActiveTickets"] + (float)$value1["RunningActiveTickets"];
                        }
                        break;
                    }
                }
            }
            
            //Date to use for Expired Ticket Query for the Date Today
            $sldate = new DateTime($startdate);
            $sldate->sub(date_interval_create_from_date_string('1 day'));
            $startlimitdate = $sldate->format('Y-m-d')." 00:00:00.000000";
            $eldate = new DateTime($startdate);
            $endlimitdate = $eldate->format('Y-m-d')." 23:59:59.000000";
            
            //Get the Expired Tickets per site
            $this->prepare($query8);
            $this->bindparameter(':startlimitdate', $startlimitdate);
            $this->bindparameter(':endlimitdate', $endlimitdate);
            $this->execute();  
            $rows8 =  $this->fetchAllData();

            //Less the Expired Tickets to Total Unused Tickets
            foreach ($rows8 as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                            $varrmerge[$keys]["RunningActiveTickets"] = (float)$varrmerge[$keys]["RunningActiveTickets"]  - (float)$value1["ExpiredTickets"];
                        break;
                    }
                }  
            }
            
            
            //Date to use for Ticket Query To be less in active running tickets
            $sldate = new DateTime($startdate);
            $sldate->sub(date_interval_create_from_date_string('1 day'));
            $startlimitdate = $sldate->format('Y-m-d')." 06:00:00.000000";
            $eldate = new DateTime($startdate);
            $eldate->add(date_interval_create_from_date_string('1 day'));
            $endlimitdate = $eldate->format('Y-m-d')." 06:00:00.000000";

            //Get the total Printed Tickets per site for 2 days
            //ex: Current Date = June 4, Pick Date = June 4: Get the total printed tickets for June 4 and June3 Cutoff
            $this->prepare($query4);
            $this->bindparameter(":startdate", $startlimitdate);
            $this->bindparameter(":enddate", $endlimitdate);
            $this->execute();  
            $rows4 =  $this->fetchAllData();

            foreach($rows4 as $itr => $value) 
            {                
                   $vtotprintedtickets[$itr] = array(
                      'SiteID'=>$value['SiteID'],
                       'PrintedTickets'=>$value['PrintedTickets']); 
            }

            //Get the Tickets to be less in active running tickets per site
            $this->prepare($query9);
            $this->bindparameter(':startlimitdate', $startlimitdate);
            $this->bindparameter(':endlimitdate', $endlimitdate);
            $this->execute();  
            $rows9 =  $this->fetchAllData();

            //Less the tickets used/encashed for the recalculated dates
            foreach ($rows9 as $value1) {
                foreach ($vtotprintedtickets as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                            $vtotprintedtickets[$keys]["PrintedTickets"] = (float)$vtotprintedtickets[$keys]["PrintedTickets"]  - (float)$value1["LessTickets"];
                        break;
                    }
                }  
            }
            
            //Less the tickets used/encashed for the recalculated dates
            foreach ($vtotprintedtickets as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                            $varrmerge[$keys]["RunningActiveTickets"] = (float)$varrmerge[$keys]["RunningActiveTickets"]  + (float)$value1["PrintedTickets"];
                        break;
                    }
                }  
            }
            
        }

//        Get the Expired Tickets per site
//        $this->prepare($query10);
//        $this->bindparameter(':startlimitdate', $startdate);
//        $this->bindparameter(':endlimitdate', $enddate);
//        $this->execute();  
//        $rows10 =  $this->fetchAllData();
        
        //Get the Expired Tickets per site
        $this->prepare($query10);
        $this->bindparameter(':startlimitdate', $startdate);
        $this->bindparameter(':endlimitdate', $enddate);
        $this->execute();  
        $rows11 =  $this->fetchAllData();

        //Less the Expired Tickets to Total Unused Tickets
        foreach ($rows11 as $value1) {
            foreach ($varrmerge as $keys => $value2) {
                if($value1["SiteID"] == $value2["SiteID"]){
                        $varrmerge[$keys]["EwalletWithdrawal"] += (float)$value1["EwalletRedemption"];
                        $varrmerge[$keys]["EwalletCashLoads"] += (float)$value1["EwalletCashDeposit"];
                        $varrmerge[$keys]["EwalletCashLoads"] += (float)$value1["EwalletBancnetDeposit"];
                        $varrmerge[$keys]["Coupon"] += (float)$value1["EwalletVoucherDeposit"];
                    break;
                }
            }  
        }

          unset($query,$query3, $sort, $dir, $rows1);   
          return $varrmerge;
      }
}
?>
