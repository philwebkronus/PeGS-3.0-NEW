<?php

/*
 * Created by: Lea Tuazon
 * Modified By: Edson L. Perez
 * Date Created : June 7, 2011
 */

include "DbHandler.class.php";
include "AppendArray.class.php";

class TopUp extends DBHandler
{
    public $cut_off = CUT_OFF;
    
    public function __construct($sconectionstring)
    {
        parent::__construct($sconectionstring);
    }
    
    /*
     * Get old gross hold balance if queried date is not today
     */
    public function getoldGHBalance($sort, $dir, $startdate,$enddate,$zsiteid)
    {       
       switch ($zsiteid)
       {
           case 'All':
               //Query for the generated site gross hold per cutoff (this is only up to the last Cut off)
                $query1 = "SELECT sgc.SiteID, sgc.BeginningBalance, ad.Name, sd.SiteDescription, sgc.Coupon,
                            s.SiteCode, s.POSAccountNo,sgc.ReportDate,
                            sgc.DateCutOff,sgc.Deposit AS InitialDeposit, sgc.Reload AS Reload , sgc.Withdrawal AS Redemption, sgc.EwalletDeposits,  sgc.EwalletWithdrawals
                            FROM sitegrossholdcutoff sgc
                            INNER JOIN sites s ON s.SiteID = sgc.SiteID
                            INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID
                            INNER JOIN sitedetails sd ON sd.SiteID = sgc.SiteID
                            WHERE sgc.DateCutOff > ? AND sgc.DateCutOff <= ?
                            ORDER BY s.SiteCode, sgc.DateCutOff";          

               //Query for Replenishments
                $query2 = "SELECT SiteID, Amount, DateCreated FROM replenishments
                                    WHERE DateCreated >= ? AND DateCreated < ? ";

                //Query for Collection
                $query3 = "SELECT SiteID, Amount, DateCreated FROM siteremittance
                                    WHERE Status = 3 AND DateCreated >= ? AND DateCreated < ? ";
                
                //Query for Manual Redemption (per site/per cut off)
                $query5 = "SELECT SiteID, ActualAmount AS ActualAmount,TransactionDate FROM manualredemptions " . 
                        "WHERE TransactionDate >= ? AND TransactionDate < ? ";   

                //Query for Deposit (Cash,Coupon,Ticket),  Reload (Cash,Coupon,Ticket) and Redemption (Cashier,Genesis)
                $query6 = "SELECT  tr.TransactionType AS TransType,

                                -- TOTAL DEPOSIT --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN SUM(tr.Amount)
                                  ELSE 0
                                END As TotalDeposit,

                                -- DEPOSIT COUPON --
                                SUM(CASE tr.TransactionType
                                  WHEN 'D' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN tr.Amount
                                      ELSE 0
                                     END
                                  ELSE 0 END) As DepositCoupon,

                                -- DEPOSIT CASH --
                                SUM(CASE tr.TransactionType
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
                                END) As DepositCash,

                                -- DEPOSIT TICKET --
                                SUM(CASE tr.TransactionType
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
                                END) As DepositTicket,

                                -- TOTAL RELOAD --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN SUM(tr.Amount)
                                  ELSE 0 -- Not Reload
                                END As TotalReload,

                                -- RELOAD COUPON --
                                SUM(CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN tr.Amount
                                      ELSE 0
                                     END
                                  ELSE 0 END) As ReloadCoupon,

                                -- RELOAD CASH --
                                SUM(CASE tr.TransactionType
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
                                END) As ReloadCash,

                                -- RELOAD TICKET --
                                SUM(CASE tr.TransactionType
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
                                                   AND tr.TransactionDetailsID = sdtls.TransactionDetailsID
                                                  AND sdtls.TransactionType = 2
                                                  AND sdtls.PaymentType = 2)  -- Reload, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Reload
                                END) As ReloadTicket,

                                -- TOTAL REDEMPTION --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN SUM(tr.Amount)
                                  ELSE 0
                                END As TotalRedemption,

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

                                tr.DateCreated, tr.SiteID
                                FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)
                                INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                WHERE tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY tr.TerminalID, tr.DateCreated DESC"; 
                
                //Query for Unused or Active Tickets of the Pick Date (per site/per cutoff)
                $query7 = "SELECT SiteID, IFNULL(SUM(Amount), 0) AS UnusedTickets, DateCreated FROM
                                            ((SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode, tr.SiteID, tr.DateCreated FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
                                              INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                              INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                              INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                              LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                              WHERE tr.SiteID = :siteid
                                                AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                                                AND tr.Status IN(1,4)
                                                AND tr.TransactionType = 'W'
                                                AND tr.StackerSummaryID IS NOT NULL
                                                AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4,15)
                                                AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID =  :siteid))
                                                    )
                                            UNION ALL
                                            (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode, sa.SiteID, stckr.DateCancelledOn as DateCreated FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
                                              INNER JOIN npos.siteaccounts sa ON stckr.CreatedByAID = sa.AID
                                              WHERE stckr.Status IN (1, 2)
                                              AND stckr.DateCancelledOn >= :startdate AND stckr.DateCancelledOn < :enddate
                                              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4, 15))
                                              AND sa.SiteID =  :siteid
                                              )) AS UnionPrintedTickets
                                            WHERE TicketCode NOT IN  
                                                    (SELECT tckt.TicketCode FROM vouchermanagement.tickets tckt -- Less: Encashed Tickets
                                                            INNER JOIN npos.accounts acct ON  tckt.EncashedByAID = acct.AID
                                                            INNER JOIN npos.siteaccounts sa ON tckt.EncashedByAID = sa.AID
                                                            WHERE tckt.DateEncashed >=  :startdate AND tckt.DateEncashed < :enddate
                                                              AND acct.AccountTypeID = 4 AND sa.SiteID = :siteid
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
                                                                            WHERE tr.SiteID = :siteid
                                                                              AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                                                                              AND tr.Status IN(1,4)
                                                                              AND tr.TransactionType In ('D', 'R')
                                                                                    AND tr.StackerSummaryID IS NOT NULL
                                                                              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4,15)
                                                                              AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID =  :siteid)))
                                                            )
                                                    ) 
                                        GROUP BY SiteID";
                
                //Query for Printed Tickets of the pick date (per site/per cutoff)
                $query8 = "SELECT tr.SiteID, IFNULL(SUM(stckr.Withdrawal), 0) AS PrintedTickets, tr.DateCreated FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
                                        INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                        INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                        INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                        LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                        WHERE tr.DateCreated >= ? AND tr.DateCreated < ?
                                          AND tr.Status IN(1,4)
                                          AND tr.SiteID = ?
                                          AND tr.TransactionType = 'W'
                                          AND tr.StackerSummaryID IS NOT NULL
                                          GROUP BY tr.SiteID";
                
                //Query for Encashed Tickets of the pick date (per site/per cutoff)
                $query9 = "SELECT tckt.SiteID, IFNULL(SUM(tckt.Amount), 0) AS EncashedTickets, tckt.DateEncashed as DateCreated FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                                        WHERE tckt.DateEncashed >= ? AND tckt.DateEncashed < ?
                                        AND tckt.SiteID = ?
                                        GROUP BY tckt.SiteID";
                
                //Query for Encashed Tickets of the pick date (per site/per cutoff)
                $query10 = "SELECT SiteID,
                                        CASE
                                          WHEN (substr(StartDate, 12, 2) < '06') THEN substr(date_add(StartDate, INTERVAL -1 DAY), 1, 10)
                                          ELSE substr(StartDate, 1, 10)
                                        END AS ReportDate,

                                        SUM(CASE PaymentType -- Coupon
                                            WHEN 2 THEN Amount
                                            ELSE 0 END)
                                        AS EwalletVoucherDeposit,

                                        SUM(CASE PaymentType -- Bancnet
                                            WHEN 1 THEN
                                              CASE IFNULL(TraceNumber, '')
                                                 WHEN '' THEN 0
                                                  ELSE Amount -- Bancnet
                                                    END
                                            ELSE 0 END)
                                        AS EwalletBancnetDeposit,

                                        SUM(CASE PaymentType -- Cash
                                            WHEN 1 THEN
                                              CASE IFNULL(TraceNumber, '')
                                              WHEN '' THEN Amount -- Cash
                                              ELSE 0 END
                                            ELSE 0 END)
                                        AS EwalletCashDeposit
                                  FROM npos.ewallettrans
                                  WHERE TransType = 'D' AND Status IN (1,3)
                                  AND StartDate >= ? AND StartDate < ?
                                  GROUP BY SiteID, ReportDate";

                // to get beginning balance, sitecode, sitename
                $this->prepare($query1);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->execute(); 
                $rows1 = $this->fetchAllData();        
                $qr1 = array();
                foreach($rows1 as $row1) {
                    $qr1[] = array('SiteID'=>$row1['SiteID'],'BegBal'=>$row1['BeginningBalance'],
                        'POSAccountNo' => $row1['POSAccountNo'],'SiteName' => $row1['Name'],'SiteCode'=>$row1['SiteCode'], 
                        'InitialDeposit'=>'0.00','Reload'=>'0.00','Redemption'=> '0.00',
                        'ReportDate'=>$row1['ReportDate'],'CutOff'=>$row1['DateCutOff'],'ManualRedemption'=>0,'Coupon'=>'0.00',
                        'PrintedTickets'=>'0.00','EncashedTickets'=>'0.00', 'RedemptionCashier'=>'0.00',
                        'RedemptionGenesis'=>'0.00','DepositCash'=>'0.00','ReloadCash'=>'0.00','UnusedTickets'=>'0.00','DepositTicket'=>'0.00',
                        'ReloadTicket'=>'0.00','DepositCoupon'=>'0.00','ReloadCoupon'=>'0.00', 'Replenishment'=>0,'Collection'=>0,
                        'EwalletDeposits' => $row1['EwalletDeposits'], 'EwalletWithdrawals' => $row1['EwalletWithdrawals'], 'EwalletCashLoads' => 0
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
                    $qr2[] = array('SiteID'=>$row2['SiteID'],'DateCreated'=>$row2['DateCreated'],
                        'Amount'=>$row2['Amount']);
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
                    $qr3[] = array('SiteID'=>$row3['SiteID'],'DateCreated'=>$row3['DateCreated'],
                        'Amount'=>$row3['Amount']);
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
                    $qr5[] = array('SiteID'=>$row5['SiteID'],'ManualRedemption'=>$row5['ActualAmount'],'MRTransDate'=>$row5['TransactionDate']);
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
                        if($row6["SiteID"] == $value1["SiteID"]){
                            if(($row6['DateCreated'] >= $value1['ReportDate']." ".BaseProcess::$cutoff) && ($row6['DateCreated'] < $value1['CutOff'])){
                                if($row6["DepositCash"] != '0.00'){
                                    $qr1[$keys]["DepositCash"] = (float)$qr1[$keys]["DepositCash"] + (float)$row6["DepositCash"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositCash"];
                                }
                                if($row6["ReloadCash"] != '0.00'){
                                    $qr1[$keys]["ReloadCash"] = (float)$qr1[$keys]["ReloadCash"] + (float)$row6["ReloadCash"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadCash"];
                                }
                                if($row6["RedemptionCashier"] != '0.00'){
                                    $qr1[$keys]["RedemptionCashier"] = (float)$qr1[$keys]["RedemptionCashier"] + (float)$row6["RedemptionCashier"];
                                    $qr1[$keys]["Redemption"] = (float)$qr1[$keys]["Redemption"] + (float)$row6["RedemptionCashier"];
                                }
                                if($row6["RedemptionGenesis"] != '0.00'){
                                    $qr1[$keys]["RedemptionGenesis"] = (float)$qr1[$keys]["RedemptionGenesis"] + (float)$row6["RedemptionGenesis"];
                                    $qr1[$keys]["Redemption"] = (float)$qr1[$keys]["Redemption"] + (float)$row6["RedemptionGenesis"];
                                }
                                if($row6["DepositCoupon"] != '0.00'){
                                    $qr1[$keys]["DepositCoupon"] = (float)$qr1[$keys]["DepositCoupon"] + (float)$row6["DepositCoupon"];
                                    $qr1[$keys]["Coupon"] = (float)$qr1[$keys]["Coupon"] + (float)$row6["DepositCoupon"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositCoupon"];
                                }
                                if($row6["ReloadCoupon"] != '0.00'){
                                    $qr1[$keys]["ReloadCoupon"] = (float)$qr1[$keys]["ReloadCoupon"] + (float)$row6["ReloadCoupon"];
                                    $qr1[$keys]["Coupon"] = (float)$qr1[$keys]["Coupon"] + (float)$row6["ReloadCoupon"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadCoupon"];
                                }
                                if($row6["DepositTicket"] != '0.00'){
                                    $qr1[$keys]["DepositTicket"] = (float)$qr1[$keys]["DepositTicket"] + (float)$row6["DepositTicket"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositTicket"];
                                }
                                if($row6["ReloadTicket"] != '0.00'){
                                    $qr1[$keys]["ReloadTicket"] = (float)$qr1[$keys]["ReloadTicket"] + (float)$row6["ReloadTicket"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadTicket"];
                                }
                            }
                        }
                    }     
                }
                
                foreach ($qr1 as $keys => $value2) {
                    //Get the total Unused Tickets per site
                    $this->prepare($query7);
                    $this->bindparameter(":siteid", $value2["SiteID"]);
                    $this->bindparameter(":startdate", $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(":enddate", $value2["CutOff"]);
                    $this->execute();  
                    $rows7 =  $this->fetchAllData();
                    foreach ($rows7 as $row7) {
                        if($row7["SiteID"] == $value2["SiteID"]){
                            if(($row7['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row7['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["UnusedTickets"] = (float)$row7["UnusedTickets"];
                            }
                        }
                    }
                    
                    //Get the total Printed Tickets per site
                    $this->prepare($query8);
                    $this->bindparameter(1, $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(2, $value2["CutOff"]);
                    $this->bindparameter(3, $value2["SiteID"]);
                    $this->execute();  
                    $rows8 =  $this->fetchAllData();
                    
                    foreach ($rows8 as $row8) {
                        if($row8["SiteID"] == $value2["SiteID"]){
                            if(($row8['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row8['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["PrintedTickets"] = (float)$row8["PrintedTickets"];
                            }
                            break;
                        }
                    }
                    
                    //Get the total Encashed Tickets per site
                    $this->prepare($query9);
                    $this->bindparameter(1, $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(2, $value2["CutOff"]);
                    $this->bindparameter(3, $value2["SiteID"]);
                    $this->execute();  
                    $rows9 =  $this->fetchAllData();
                    
                    foreach ($rows9 as $row9) {
                        if($row9["SiteID"] == $value2["SiteID"]){
                            if(($row9['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row9['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["EncashedTickets"] = (float)$row9["EncashedTickets"];
                            }
                            break;
                        }
                    }  
                } 
                
                //Get the total Encashed Tickets per site
                $this->prepare($query10);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->execute();  
                $rows10 =  $this->fetchAllData();

                foreach ($rows10 as $value1) {
                    foreach ($qr1 as $keys => $value2) {
                        if($value1["SiteID"] == $value2["SiteID"]){
                                if($value1['ReportDate'] == $value2['ReportDate']){
                                    $qr1[$keys]["EwalletCashLoads"] += (float)$value1["EwalletCashDeposit"];
                                    $qr1[$keys]["EwalletCashLoads"] += (float)$value1["EwalletBancnetDeposit"];
                                    $qr1[$keys]["Coupon"] += (float)$value1["EwalletVoucherDeposit"];
                                }
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
                        if($qr1[$ctr]['SiteID'] == $qr5[$ctr2]['SiteID'])
                        {
                            $amount = 0;
                            if(($qr5[$ctr2]['MRTransDate'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr5[$ctr2]['MRTransDate'] < $qr1[$ctr]['CutOff']))
                            {              
                                 if($qr1[$ctr]['ManualRedemption'] == 0) 
                                     $qr1[$ctr]['ManualRedemption'] = $qr5[$ctr2]['ManualRedemption'];
                                 else
                                 {
                                     $amount = $qr1[$ctr]['ManualRedemption'];
                                     $qr1[$ctr]['ManualRedemption'] = $amount + $qr5[$ctr2]['ManualRedemption'];
                                 }
                            }
                        }
                        $ctr2 = $ctr2 + 1;
                    }           

                    $ctr3 = 0; //counter for grossholdconfirmation array
                    while($ctr3 < count($qr2))
                    {
                        if($qr1[$ctr]['SiteID'] == $qr2[$ctr3]['SiteID'])
                        {
                            $amount = 0;
                            if(($qr2[$ctr3]['DateCreated'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr2[$ctr3]['DateCreated'] < $qr1[$ctr]['CutOff']))
                            {
                                if($qr1[$ctr]['Replenishment'] == 0) 
                                    $qr1[$ctr]['Replenishment'] = $qr2[$ctr3]['Amount'];
                                else
                                {
                                    $amount = $qr1[$ctr]['Replenishment'];
                                    $qr1[$ctr]['Replenishment'] = $amount + $qr2[$ctr3]['Amount'];
                                }                         
                            }                   
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr4 = 0; //counter for collection array
                    while($ctr4 < count($qr3))
                    {
                        if($qr1[$ctr]['SiteID'] == $qr3[$ctr4]['SiteID'])
                        {         
                            $amount = 0;
                            if(($qr3[$ctr4]['DateCreated'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr3[$ctr4]['DateCreated'] < $qr1[$ctr]['CutOff']))
                            {
                                if($qr1[$ctr]['Collection'] == 0) 
                                {
                                    $qr1[$ctr]['Collection'] = $qr3[$ctr4]['Amount'];         
                                }      
                                else
                                {
                                    $amount = $qr1[$ctr]['Collection'];
                                    $qr1[$ctr]['Collection'] = $amount + $qr3[$ctr4]['Amount'];                                
                                }                           
                            }                    
                        }
                        $ctr4 = $ctr4 + 1;
                    }            
                    $ctr = $ctr + 1;
                }
               break;
           case $zsiteid > 0 :
               //Query for the generated site gross hold per cutoff (this is only up to the last Cut off)
                $query1 = "SELECT sgc.SiteID, sgc.BeginningBalance, ad.Name, sgc.Coupon,
                            s.SiteCode, s.POSAccountNo,sgc.ReportDate,
                            sgc.DateCutOff,sgc.Deposit AS InitialDeposit, sgc.Reload AS Reload , sgc.Withdrawal AS Redemption, sgc.EwalletDeposits,  sgc.EwalletWithdrawals
                            FROM sitegrossholdcutoff sgc
                            INNER JOIN sites s ON s.SiteID = sgc.SiteID
                            INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID
                            INNER JOIN sitedetails sd ON sd.SiteID = sgc.SiteID
                            WHERE sgc.DateCutOff > ?
                            AND sgc.DateCutOff <= ? AND sgc.SiteID = ?
                            ORDER BY s.SiteCode, sgc.DateCutOff";          

               //Query for Replenishments
                $query2 = "SELECT SiteID, Amount, DateCreated FROM replenishments
                                    WHERE DateCreated >= ? AND DateCreated < ? AND SiteID = ?";

                //Query for Collection
                $query3 = "SELECT SiteID, Amount, DateCreated FROM siteremittance
                                    WHERE Status = 3 AND DateCreated >= ? AND DateCreated < ? AND SiteID = ? ";

                //Query for Manual Redemption (per site/per cut off)
                $query5 = "SELECT SiteID, ActualAmount AS ActualAmount, TransactionDate FROM manualredemptions " . 
                        "WHERE TransactionDate >= ? AND TransactionDate < ? AND SiteID = ? ";  
                
                //Query for Deposit (Cash,Coupon,Ticket),  Reload (Cash,Coupon,Ticket) and Redemption (Cashier,Genesis)
                $query6 = "SELECT tr.TransactionType AS TransType,

                                -- TOTAL DEPOSIT --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN SUM(tr.Amount)
                                  ELSE 0
                                END As TotalDeposit,

                                -- DEPOSIT COUPON --
                                SUM(CASE tr.TransactionType
                                  WHEN 'D' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN tr.Amount
                                      ELSE 0
                                     END
                                  ELSE 0 END) As DepositCoupon,

                                -- DEPOSIT CASH --
                                SUM(CASE tr.TransactionType
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
                                END) As DepositCash,

                                -- DEPOSIT TICKET --
                                SUM(CASE tr.TransactionType
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
                                END) As DepositTicket,

                                -- TOTAL RELOAD --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN SUM(tr.Amount)
                                  ELSE 0 -- Not Reload
                                END As TotalReload,

                                -- RELOAD COUPON --
                                SUM(CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN tr.Amount
                                      ELSE 0
                                     END
                                  ELSE 0 END) As ReloadCoupon,

                                -- RELOAD CASH --
                                SUM(CASE tr.TransactionType
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
                                END) As ReloadCash,

                                -- RELOAD TICKET --
                                SUM(CASE tr.TransactionType
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
                                                   AND tr.TransactionDetailsID = sdtls.TransactionDetailsID
                                                  AND sdtls.TransactionType = 2
                                                  AND sdtls.PaymentType = 2)  -- Reload, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Reload
                                END) As ReloadTicket,

                                -- TOTAL REDEMPTION --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN SUM(tr.Amount)
                                  ELSE 0
                                END As TotalRedemption,

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

                                tr.DateCreated,  tr.SiteID
                                FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)
                                INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                WHERE tr.SiteID = ?
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY tr.TerminalID, tr.DateCreated DESC"; 
                
                //Query for Unused or Active Tickets of the Pick Date (per site/per cutoff)
                $query7 = "SELECT SiteID, IFNULL(SUM(Amount), 0) AS UnusedTickets, DateCreated FROM
                                            ((SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode, tr.SiteID, tr.DateCreated FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
                                              INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                              INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                              INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                              LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                              WHERE tr.SiteID = :siteid
                                                AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                                                AND tr.Status IN(1,4)
                                                AND tr.TransactionType = 'W'
                                                AND tr.StackerSummaryID IS NOT NULL
                                                AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4,15)
                                                AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID =  :siteid))
                                                    )
                                            UNION ALL
                                            (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode, sa.SiteID, stckr.DateCancelledOn as DateCreated FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
                                              INNER JOIN npos.siteaccounts sa ON stckr.CreatedByAID = sa.AID
                                              WHERE stckr.Status IN (1, 2)
                                              AND stckr.DateCancelledOn >= :startdate AND stckr.DateCancelledOn < :enddate
                                              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4, 15))
                                              AND sa.SiteID =  :siteid
                                              )) AS UnionPrintedTickets
                                            WHERE TicketCode NOT IN  
                                                    (SELECT tckt.TicketCode FROM vouchermanagement.tickets tckt -- Less: Encashed Tickets
                                                            INNER JOIN npos.accounts acct ON  tckt.EncashedByAID = acct.AID
                                                            INNER JOIN npos.siteaccounts sa ON tckt.EncashedByAID = sa.AID
                                                            WHERE tckt.DateEncashed >=  :startdate AND tckt.DateEncashed < :enddate
                                                              AND acct.AccountTypeID = 4 AND sa.SiteID = :siteid
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
                                                                            WHERE tr.SiteID = :siteid
                                                                              AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                                                                              AND tr.Status IN(1,4)
                                                                              AND tr.TransactionType In ('D', 'R')
                                                                                    AND tr.StackerSummaryID IS NOT NULL
                                                                              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4,15)
                                                                              AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID =  :siteid)))
                                                            )
                                                    ) 
                                        GROUP BY SiteID";
                
                //Query for Printed Tickets of the pick date (per site/per cutoff)
                $query8 = "SELECT tr.SiteID, IFNULL(SUM(stckr.Withdrawal), 0) AS PrintedTickets, tr.DateCreated FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
                                        INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                        INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                        INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                        LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                        WHERE tr.DateCreated >= ? AND tr.DateCreated < ?
                                          AND tr.Status IN(1,4)
                                          AND tr.SiteID = ?
                                          AND tr.TransactionType = 'W'
                                          AND tr.StackerSummaryID IS NOT NULL
                                          GROUP BY tr.SiteID";
                
                //Query for Encashed Tickets of the pick date (per site/per cutoff)
                $query9 = "SELECT tckt.SiteID, IFNULL(SUM(tckt.Amount), 0) AS EncashedTickets, tckt.DateEncashed as DateCreated FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                                        WHERE tckt.DateEncashed >= ? AND tckt.DateEncashed < ?
                                        AND tckt.SiteID = ?
                                        GROUP BY tckt.SiteID";
                
                //Query for Encashed Tickets of the pick date (per site/per cutoff)
                $query10 = "SELECT SiteID,
                                        CASE
                                          WHEN (substr(StartDate, 12, 2) < '06') THEN substr(date_add(StartDate, INTERVAL -1 DAY), 1, 10)
                                          ELSE substr(StartDate, 1, 10)
                                        END AS ReportDate,

                                        SUM(CASE PaymentType -- Coupon
                                            WHEN 2 THEN Amount
                                            ELSE 0 END)
                                        AS EwalletVoucherDeposit,

                                        SUM(CASE PaymentType -- Bancnet
                                            WHEN 1 THEN
                                              CASE IFNULL(TraceNumber, '')
                                                 WHEN '' THEN 0
                                                  ELSE Amount -- Bancnet
                                                    END
                                            ELSE 0 END)
                                        AS EwalletBancnetDeposit,

                                        SUM(CASE PaymentType -- Cash
                                            WHEN 1 THEN
                                              CASE IFNULL(TraceNumber, '')
                                              WHEN '' THEN Amount -- Cash
                                              ELSE 0 END
                                            ELSE 0 END)
                                        AS EwalletCashDeposit
                                  FROM npos.ewallettrans
                                  WHERE TransType = 'D' AND Status IN (1,3)
                                  AND StartDate >= ? AND StartDate < ?
                                  AND SiteID = ?
                                  GROUP BY SiteID, ReportDate";

                // to get beginning balance, sitecode, sitename
                $this->prepare($query1);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                
                $this->execute(); 
                $rows1 = $this->fetchAllData();        
                $qr1 = array();
                foreach($rows1 as $row1) {
                    $qr1[] = array('SiteID'=>$row1['SiteID'],'BegBal'=>$row1['BeginningBalance'],
                            'POSAccountNo' => $row1['POSAccountNo'],'SiteName' => $row1['Name'],'SiteCode'=>$row1['SiteCode'], 
                            'InitialDeposit'=>'0.00','Reload'=>'0.00','Redemption'=>'0.00',
                            'ReportDate'=>$row1['ReportDate'],'CutOff'=>$row1['DateCutOff'],'ManualRedemption'=>0,'Coupon'=>'0.00',
                            'PrintedTickets'=>'0.00','EncashedTickets'=>'0.00', 'RedemptionCashier'=>'0.00',
                            'RedemptionGenesis'=>'0.00','DepositCash'=>'0.00','ReloadCash'=>'0.00','UnusedTickets'=>'0.00','DepositTicket'=>'0.00',
                            'ReloadTicket'=>'0.00','DepositCoupon'=>'0.00','ReloadCoupon'=>'0.00', 'Replenishment'=>0,'Collection'=>0,
                            'EwalletDeposits' => $row1['EwalletDeposits'], 'EwalletWithdrawals' => $row1['EwalletWithdrawals'], 'EwalletCashLoads' => 0
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
                    $qr2[] = array('SiteID'=>$row2['SiteID'],'DateCreated'=>$row2['DateCreated'],
                        'Amount'=>$row2['Amount']);
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
                    $qr3[] = array('SiteID'=>$row3['SiteID'],'DateCreated'=>$row3['DateCreated'],
                        'Amount'=>$row3['Amount']);
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
                    $qr5[] = array('SiteID'=>$row5['SiteID'],'ManualRedemption'=>$row5['ActualAmount'],'MRTransDate'=>$row5['TransactionDate']);
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
                        if($row6["SiteID"] == $value1["SiteID"]){
                            if(($row6['DateCreated'] >= $value1['ReportDate']." ".BaseProcess::$cutoff) && ($row6['DateCreated'] < $value1['CutOff'])){
                                if($row6["DepositCash"] != '0.00'){
                                    $qr1[$keys]["DepositCash"] = (float)$qr1[$keys]["DepositCash"] + (float)$row6["DepositCash"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositCash"];
                                }
                                if($row6["ReloadCash"] != '0.00'){
                                    $qr1[$keys]["ReloadCash"] = (float)$qr1[$keys]["ReloadCash"] + (float)$row6["ReloadCash"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadCash"];
                                }
                                if($row6["RedemptionCashier"] != '0.00'){
                                    $qr1[$keys]["RedemptionCashier"] = (float)$qr1[$keys]["RedemptionCashier"] + (float)$row6["RedemptionCashier"];
                                    $qr1[$keys]["Redemption"] = (float)$qr1[$keys]["Redemption"] + (float)$row6["RedemptionCashier"];
                                    }
                                if($row6["RedemptionGenesis"] != '0.00'){
                                    $qr1[$keys]["RedemptionGenesis"] = (float)$qr1[$keys]["RedemptionGenesis"] + (float)$row6["RedemptionGenesis"];
                                    $qr1[$keys]["Redemption"] = (float)$qr1[$keys]["Redemption"] + (float)$row6["RedemptionGenesis"];
                                    }
                                if($row6["DepositCoupon"] != '0.00'){
                                    $qr1[$keys]["DepositCoupon"] = (float)$qr1[$keys]["DepositCoupon"] + (float)$row6["DepositCoupon"];
                                    $qr1[$keys]["Coupon"] = (float)$qr1[$keys]["Coupon"] + (float)$row6["DepositCoupon"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositCoupon"];
                                }
                                if($row6["ReloadCoupon"] != '0.00'){
                                    $qr1[$keys]["ReloadCoupon"] = (float)$qr1[$keys]["ReloadCoupon"] + (float)$row6["ReloadCoupon"];
                                    $qr1[$keys]["Coupon"] = (float)$qr1[$keys]["Coupon"] + (float)$row6["ReloadCoupon"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadCoupon"];
                                }
                                if($row6["DepositTicket"] != '0.00'){
                                    $qr1[$keys]["DepositTicket"] = (float)$qr1[$keys]["DepositTicket"] + (float)$row6["DepositTicket"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositTicket"];
                                }
                                if($row6["ReloadTicket"] != '0.00'){
                                    $qr1[$keys]["ReloadTicket"] = (float)$qr1[$keys]["ReloadTicket"] + (float)$row6["ReloadTicket"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadTicket"];
                                }
                            }
                        }
                    }     
                }

                foreach ($qr1 as $keys => $value2) {
                    //Get the total Unused Tickets per site
                    $this->prepare($query7);
                    $this->bindparameter(":siteid", $value2["SiteID"]);
                    $this->bindparameter(":startdate", $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(":enddate", $value2["CutOff"]);
                    $this->execute();  
                    $rows7 =  $this->fetchAllData();
                    foreach ($rows7 as $row7) {
                        if($row7["SiteID"] == $value2["SiteID"]){
                            if(($row7['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row7['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["UnusedTickets"] = (float)$row7["UnusedTickets"];
                            }
                        }
                    }
                    
                    //Get the total Printed Tickets per site
                    $this->prepare($query8);
                    $this->bindparameter(1, $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(2, $value2["CutOff"]);
                    $this->bindparameter(3, $value2["SiteID"]);
                    $this->execute();  
                    $rows8 =  $this->fetchAllData();
                    
                    foreach ($rows8 as $row8) {
                        if($row8["SiteID"] == $value2["SiteID"]){
                            if(($row8['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row8['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["PrintedTickets"] = (float)$row8["PrintedTickets"];
                            }
                            break;
                        }
                    }
                    
                    //Get the total Encashed Tickets per site
                    $this->prepare($query9);
                    $this->bindparameter(1, $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(2, $value2["CutOff"]);
                    $this->bindparameter(3, $value2["SiteID"]);
                    $this->execute();  
                    $rows9 =  $this->fetchAllData();
                    
                    foreach ($rows9 as $row9) {
                        if($row9["SiteID"] == $value2["SiteID"]){
                            if(($row9['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row9['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["EncashedTickets"] = (float)$row9["EncashedTickets"];
                            }
                            break;
                        }
                    }
                    
                }  
                
                //Get the total Encashed Tickets per site
                $this->prepare($query10);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->bindparameter(3, $zsiteid);
                $this->execute();  
                $rows10 =  $this->fetchAllData();

                foreach ($rows10 as $value1) {
                    foreach ($qr1 as $keys => $value2) {
                        if($value1["SiteID"] == $value2["SiteID"]){
                                if($value1['ReportDate'] == $value2['ReportDate']){
                                    $qr1[$keys]["EwalletCashLoads"] += (float)$value1["EwalletCashDeposit"];
                                    $qr1[$keys]["EwalletCashLoads"] += (float)$value1["EwalletBancnetDeposit"];
                                    $qr1[$keys]["Coupon"] += (float)$value1["EwalletVoucherDeposit"];
                                }
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
                        if($qr1[$ctr]['SiteID'] == $qr5[$ctr2]['SiteID'])
                        {
                            $amount = 0;
                            if(($qr5[$ctr2]['MRTransDate'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr5[$ctr2]['MRTransDate'] < $qr1[$ctr]['CutOff']))
                            {              
                                 if($qr1[$ctr]['ManualRedemption'] == 0) 
                                     $qr1[$ctr]['ManualRedemption'] = $qr5[$ctr2]['ManualRedemption'];
                                 else
                                 {
                                     $amount = $qr1[$ctr]['ManualRedemption'];
                                     $qr1[$ctr]['ManualRedemption'] = $amount + $qr5[$ctr2]['ManualRedemption'];
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
                        if($qr1[$ctr]['SiteID'] == $qr2[$ctr3]['SiteID'])
                        {
                            $amount = 0;
                            if(($qr2[$ctr3]['DateCreated'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr2[$ctr3]['DateCreated'] < $qr1[$ctr]['CutOff']))
                            {
                                if($qr1[$ctr]['Replenishment'] == 0) 
                                    $qr1[$ctr]['Replenishment'] = $qr2[$ctr3]['Amount'];
                                else
                                {
                                    $amount = $qr1[$ctr]['Replenishment'];
                                    $qr1[$ctr]['Replenishment'] = $amount + $qr2[$ctr3]['Amount'];
                                }                         
                            }                   
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr4 = 0; //counter for collection array
                    while($ctr4 < count($qr3))
                    {
                        if($qr1[$ctr]['SiteID'] == $qr3[$ctr4]['SiteID'])
                        {         
                            $amount = 0;
                            if(($qr3[$ctr4]['DateCreated'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr3[$ctr4]['DateCreated'] < $qr1[$ctr]['CutOff']))
                            {
                                if($qr1[$ctr]['Collection'] == 0) 
                                {
                                    $qr1[$ctr]['Collection'] = $qr3[$ctr4]['Amount'];         
                                }      
                                else
                                {
                                    $amount = $qr1[$ctr]['Collection'];
                                    $qr1[$ctr]['Collection'] = $amount + $qr3[$ctr4]['Amount'];                                
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
     * Get gross hold balance based on previous cutoff
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
        
        unset($query1, $query2, $query3, $query4, $query5, $rows1, $qr1, $rows2, 
              $qr2, $rows3, $qr3, $rows3, $qr4, $rows4, $qr5, $rows5);
        return $consolidate;
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
        unset($zdetails);
        return $res;
    }
    
    public function getConfirmation($sort, $dir, $start, $limit,$startdate,$enddate) {
        $query = "SELECT ghc.GrossHoldConfirmationID, a.UserName, s.SiteCode, ghc.DateCreated, ghc.DateCredited, ghc.SiteRepresentative, ghc.AmountConfirmed,s.POSAccountNo " . 
                "FROM grossholdconfirmation ghc INNER JOIN accounts a ON ghc.PostedByAID = a.AID " . 
                "INNER JOIN sites s ON ghc.SiteID = s.SiteID WHERE ghc.DateCreated BETWEEN ? AND ? ORDER BY $sort $dir LIMIT $start,$limit";
          $this->prepare($query);
          $this->bindparameter(1, $startdate);
          $this->bindparameter(2, $enddate);
          $this->execute();
          return $this->fetchAllData();
    }
    
    public function getConfirmationTotal($startdate,$enddate) {
        $query = "SELECT COUNT(ghc.GrossHoldConfirmationID ) as totalrow " . 
                "FROM grossholdconfirmation ghc INNER JOIN accounts a ON ghc.PostedBYAID = a.AID " . 
                "INNER JOIN sites s ON ghc.SiteID = s.SiteID WHERE ghc.DateCreated BETWEEN ? AND ? ";
        $this->prepare($query);
        $this->bindparameter(1, $startdate);
        $this->bindparameter(2, $enddate);
        $this->execute();
        $row = $this->fetchAllData(); 
        $total_row = 0;
        if(isset($row[0]['totalrow'])) {
            $total_row = $row[0]['totalrow'];
        }
        return $total_row;           
    }
    
    //@date modified 03-03-2015
    public function getReplenishment($sort, $dir, $start, $limit,$startdate,$enddate) {
        $query = "SELECT r.ReplenishmentID, s.SiteCode, r.Amount, r.DateCreated, a.UserName,s.POSAccountNo, r.ReferenceNumber, ad.Name, ref.ReplenishmentName FROM replenishments r " . 
                "INNER JOIN sites s ON s.SiteID = r.SiteID " .
                "INNER JOIN ref_replenishmenttype ref ON r.ReplenishmentTypeID = ref.ReplenishmentTypeID " .
                "INNER JOIN accounts a ON a.AID = r.CreatedByAID " .
                "INNER JOIN accountdetails ad ON a.AID = ad.AID WHERE r.DateCreated BETWEEN ? AND ? ORDER BY $sort $dir LIMIT $start,$limit";
          $this->prepare($query);
          $this->bindparameter(1, $startdate);
          $this->bindparameter(2, $enddate);
          $this->execute();
          return $this->fetchAllData();
    }
    
    public function getReplenishmentTotal($startdate,$enddate) {
        $query = "SELECT COUNT(r.ReplenishmentID) AS totalrow FROM replenishments r " . 
                "INNER JOIN sites s ON s.SiteID = r.SiteID " .
                "INNER JOIN ref_replenishmenttype ref ON r.ReplenishmentTypeID = ref.ReplenishmentTypeID " .
                "INNER JOIN accounts a ON a.AID = r.CreatedByAID " .
                "INNER JOIN accountdetails ad ON a.AID = ad.AID WHERE r.DateCreated BETWEEN ? AND ?";
        $this->prepare($query);
        $this->bindparameter(1, $startdate);
        $this->bindparameter(2, $enddate);
        $this->execute();
        $row =  $this->fetchAllData(); 
        $total_row = 0;
        if(isset($row[0]['totalrow'])) {
            $total_row = $row[0]['totalrow'];
        }
        return $total_row;        
    }
    
    //get all bcf
    function getallbcf($zSiteID)
    {
        //if site was selected
        if($zSiteID > 0)
        {
            $stmt = "SELECT Balance,MinBalance,MaxBalance,TopUpType, PickUpTag  FROM sitebalance WHERE SiteID ='".$zSiteID."'";
            $this->executeQuery($stmt);
            return $this->fetchAllData();
        }
        else
        {
            $stmt = "SELECT Balance,MinBalance,MaxBalance,TopUpType,PickUpTag FROM sitebalance";
            $this->executeQuery($stmt);
            return $this->fetchData();
        }
    }
    //posting of manual topup:insert record in sitebalance,sitebalancelogs and transaction history tables
    function insertsitebalance($zSiteID,$zBalance,$zMinBalance,$zMaxBalance,$zLastTransactionDate,$zLastTransactionDescription,$zTopUpType,$zPickUpTag,
              $zAmount,$zPrevBalance,$zNewBalance,$zCreatedByAID,$zDateCreated,$zStartBalance,$zEndBalance,$zToupAmount,$zTotalTopupAmount,
              $zTopupCount,$zRemarks,$zAutoTopUpEnabled ,$zAutoTopUpAmount,$zTopupTransactionType,$zStatus)
    {
        $this->prepare("SELECT COUNT(*) FROM sitebalance WHERE SiteID =?");
        $this->bindparameter(1, $zSiteID);
        $this->execute();
        if($this->hasRows() == 0) 
        {
             $this->begintrans();
             try
             {
                 $this->prepare("INSERT INTO sitebalance(SiteID,Balance,MinBalance,MaxBalance,LastTransactionDate,LastTransactionDescription,TopUpType,AutoTopupEnabled,PickUpTag) VALUES (?,?,?,?,?,?,?,?,?)");
                 $this->bindparameter(1,$zSiteID);
                 $this->bindparameter(2,$zBalance);
                 $this->bindparameter(3,$zMinBalance);
                 $this->bindparameter(4,$zMaxBalance);
                 $this->bindparameter(5,$zLastTransactionDate);
                 $this->bindparameter(6,$zLastTransactionDescription);
                 $this->bindparameter(7,$zTopUpType);
                 $this->bindparameter(8,$zAutoTopUpEnabled);
                 $this->bindparameter(9,$zPickUpTag);
                 $this->execute();             
                 $sitebalance = $this->insertedid();
                 try
                 {
                     $this->prepare("INSERT  INTO sitebalancelogs(SiteID,Amount,PrevBalance,NewBalance,TopupType,CreatedByAID,DateCreated) VALUES (?,?,?,?,?,?,?)");
                     $this->bindparameter(1,$zSiteID);
                     $this->bindparameter(2,$zAmount);
                     $this->bindparameter(3,$zPrevBalance);
                     $this->bindparameter(4,$zNewBalance);
                     $this->bindparameter(5,$zTopUpType);
                     $this->bindparameter(6,$zCreatedByAID);
                     $this->bindparameter(7,$zDateCreated);
                     $this->execute();
                     $sitebalancelogs = $this->insertedid();    
                     try
                     {
                         $this->prepare("INSERT INTO topuptransactionhistory(SiteID,StartBalance,EndBalance,MinBalance,MaxBalance,TopupAmount,TotalTopupAmount,TopupType,TopupCount,TopupTransactionType,
                           DateCreated,Status,Remarks,CreatedByAID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                         $this->bindparameter(1,$zSiteID);
                         $this->bindparameter(2,$zStartBalance);
                         $this->bindparameter(3,$zEndBalance);
                         $this->bindparameter(4,$zMinBalance);
                         $this->bindparameter(5,$zMaxBalance);
                         $this->bindparameter(6,$zToupAmount);
                         $this->bindparameter(7,$zTotalTopupAmount);
                         $this->bindparameter(8,$zTopUpType);
                         $this->bindparameter(9,$zTopupCount);
                         $this->bindparameter(10,$zTopupTransactionType);
                         $this->bindparameter(11,$zDateCreated);
                         $this->bindparameter(12,$zStatus);
                         $this->bindparameter(13,$zRemarks);
                         $this->bindparameter(14, $zCreatedByAID);
                         if($this->execute())
                        {
                           $topuptransactionhistory = $this->insertedid();
                           $this->committrans();
                           return 1;
                        }
                        else
                        {
                           $this->rollbacktrans();
                           return 0;
                        }                         
                     }
                     catch (PDOException $e){
                        $this->rollbacktrans();
                        return 0;
                     }                      
                 }
                 catch (PDOException $e){
                    $this->rollbacktrans();
                    return 0;
                 }                 
            }
            catch (PDOException $e){
              $this->rollbacktrans();
              return 0;
            }
        }
        else
        {
            return 0;
        }
    }

   
    //posting of manual topup: update sitebalance
    function updatebalance($zAmount,$zSiteID,$zPrevBalance,$zNewBalance,$zCreatedByAID,$zDateCreated,
            $zTopUpType,$zMinBalance,$zMaxBalance,$zPickUpTag , $zTopUpCount,$zStatus,$zRemarks,
            $zTopupTransactionType)
    {
        $this->begintrans();
        
         try{
                $this->prepare("UPDATE sitebalance SET Balance =?, WillEmailAlert = 0  WHERE SiteID =?");
                $this->bindparameter(1,$zNewBalance);
                $this->bindparameter(2,$zSiteID);
                $this->execute();
                try 
                {
                    $this->prepare("INSERT  INTO sitebalancelogs(SiteID,Amount,PrevBalance,NewBalance,TopupType,
                        CreatedByAID,DateCreated) VALUES (?,?,?,?,?,?,?)");
                    $this->bindparameter(1,$zSiteID);
                    $this->bindparameter(2,$zAmount);
                    $this->bindparameter(3,$zPrevBalance);
                    $this->bindparameter(4,$zNewBalance);
                    $this->bindparameter(5,$zTopUpType);
                    $this->bindparameter(6,$zCreatedByAID);
                    $this->bindparameter(7,$zDateCreated);       
                    $this->execute();
                    try
                    {
                       $this->prepare("INSERT INTO topuptransactionhistory(SiteID,StartBalance,EndBalance,MinBalance,
                            MaxBalance,TopupAmount,TotalTopupAmount,TopupType,TopupCount,TopupTransactionType,
                               DateCreated,Status,Remarks,CreatedByAID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                       $this->bindparameter(1,$zSiteID);
                       $this->bindparameter(2,$zPrevBalance);
                       $this->bindparameter(3,$zNewBalance);
                       $this->bindparameter(4,$zMinBalance);
                       $this->bindparameter(5,$zMaxBalance);        
                       $this->bindparameter(6,$zAmount);        
                       $this->bindparameter(7,$zAmount);        
                       $this->bindparameter(8,$zTopUpType);
                       $this->bindparameter(9,$zTopUpCount);
                       $this->bindparameter(10,$zTopupTransactionType);        
                       $this->bindparameter(11,$zDateCreated);
                       $this->bindparameter(12,$zStatus);
                       $this->bindparameter(13,$zRemarks);        
                       $this->bindparameter(14, $zCreatedByAID);
                        if($this->execute())
                        {
                            $this->committrans();
                            return 1; // replace $insertedid , always return 0 even if updated was successful
                        }
                        else
                        {
                           $this->rollbacktrans();
                           return 0;
                        }
                    }
                    catch (PDOException $e){
                          $this->rollbacktrans();
                          return 0;
                    }

                }
                catch (PDOException $e){
                      $this->rollbacktrans();
                      return 0;
                }              
              
          }catch (PDOException $e){
              $this->rollbacktrans();
              return 0;
          }
    }

    //posting of manual topup: update sitebalance
    function updatesiteparam($zSiteID,$zMinimumBalance,$zMaximumBalance,$zTopUpType,$zPickUpTag)   
    {
        $this->prepare("UPDATE sitebalance SET MinBalance =?,MaxBalance =?,TopUpType =?, PickUpTag=?  WHERE SiteID =?");
        $this->bindparameter(1,$zMinimumBalance);
        $this->bindparameter(2,$zMaximumBalance);
        $this->bindparameter(3,$zTopUpType);        
        $this->bindparameter(4,$zPickUpTag);        
        $this->bindparameter(5,$zSiteID);
        $this->execute();
        return $this->rowCount();
    }

    //reversal of deposits : update status in siteremittance from 0 to 1
    //reversal of deposits : update status in siteremittance from 2 to 0    
    //reversal of deposits : update status in siteremittance from 2 to 3 , meaning verified  
    function updatesiteremittancestatus($zSiteRemittanceID,$zaid,$zvdate)
    {
        $this->prepare("UPDATE siteremittance SET Status =3, VerifiedBy = ?, StatusUpdateDate = ?  WHERE SiteRemittanceID =?");
        $this->bindparameter(1,$zaid);
        $this->bindparameter(2,$zvdate);
        $this->bindparameter(3,$zSiteRemittanceID);
        $this->execute();
        return $this->rowCount();
    }
    
    //update verified site remittance, change 3 to 0 or 1
    function updateverifiedsiteremittance($zSiteRemittanceID,$zVerifiedRemitStat,$zaid,$zdate)
    {
        $this->prepare("UPDATE siteremittance SET Status =? , AID = ? , StatusUpdateDate = ?  WHERE SiteRemittanceID =?");
        $this->bindparameter(1,$zVerifiedRemitStat);
        $this->bindparameter(2,$zaid); 
        $this->bindparameter(3,$zdate); 
        $this->bindparameter(4,$zSiteRemittanceID);
        $this->execute();
        return $this->rowCount();
    }

    //reversal of manual topup
    function updatereversal($zAmount, $zSiteID, $zPrevBalance,$zNewBalance,$zTopUpType,$zCreatedByAID,
            $zDateCreated,$zTopUpType, $zMinBalance,$zMaxBalance,$zPickUpTag ,$zTopUpCount,$zStatus,
            $zRemarks,$zTopupTransactionType)
    {
        $this->begintrans();
        try
        {
            $this->prepare("UPDATE sitebalance SET Balance =? WHERE SiteID =?");
            $this->bindparameter(1,$zNewBalance);
            $this->bindparameter(2,$zSiteID);
            $this->execute();
            $this->prepare("INSERT  INTO sitebalancelogs(SiteID,Amount,PrevBalance,NewBalance,TopupType,CreatedByAID,DateCreated) VALUES (?,?,?,?,?,?,?)");
            $this->bindparameter(1,$zSiteID);
            $this->bindparameter(2,$zAmount);
            $this->bindparameter(3,$zPrevBalance);
            $this->bindparameter(4,$zNewBalance);
            $this->bindparameter(5,$zTopUpType);
            $this->bindparameter(6,$zCreatedByAID);
            $this->bindparameter(7,$zDateCreated);
            $this->execute(); 
            try
            {
                $this->prepare("INSERT INTO topuptransactionhistory(SiteID,StartBalance,EndBalance,MinBalance,MaxBalance,TopupAmount,TotalTopupAmount,TopupType,TopupCount,TopupTransactionType,
                       DateCreated,Status,Remarks,CreatedByAID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $this->bindparameter(1,$zSiteID);
                $this->bindparameter(2,$zPrevBalance);
                $this->bindparameter(3,$zNewBalance);
                $this->bindparameter(4,$zMinBalance);
                $this->bindparameter(5,$zMaxBalance);
                $this->bindparameter(6,$zAmount);
                $this->bindparameter(7,$zAmount);
                $this->bindparameter(8,$zTopUpType);
                $this->bindparameter(9,$zTopUpCount);
                $this->bindparameter(10,$zTopupTransactionType);
                $this->bindparameter(11,$zDateCreated);
                $this->bindparameter(12,$zStatus);
                $this->bindparameter(13,$zRemarks);
                $this->bindparameter(14, $zCreatedByAID);
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
            catch (PDOException $e){
              $this->rollbacktrans();
              return 0;
            }
        }
        catch (PDOException $e){
              $this->rollbacktrans();
              return 0;
        }
        
    }

    //view individual site remittances via remittance id
    function viewsiteremittance($zsiteremit)
    {
        $stmt = "SELECT  a.RemittanceTypeID,a.BankID,a.Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a 			INNER JOIN sites b ON  a.SiteID = b.SiteID  
		INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID WHERE a.SiteRemittanceID = '".$zsiteremit."'";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }
    
    //view verified individual  site remittance via remittance id
    function viewverifiedsiteremittance($zsiteremit)
    {
        $stmt = "SELECT  a.SiteRemittanceID,a.RemittanceTypeID,a.BankID,Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a INNER JOIN sites b
		ON a.SiteID = b.SiteID INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID WHERE a.SiteRemittanceID = '".$zsiteremit."'";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }

    //get site remittance ID where status = 0, select all SiteRemittances on combo box base on site ID
    //get site remittance ID where status = 2, select all SiteRemittances on combo box base on site ID
    function getsiteremittanceid($zsiteID)
    {
        $stmt = "Select SiteRemittanceID from siteremittance where SiteID = '".$zsiteID."' and Status = 2";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }
    
    function getsiteremittanceid2($zsiteID)
    {
        $stmt = "Select SiteRemittanceID from siteremittance where SiteID = '".$zsiteID."' and Status = 3";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }

    //get all sites
    function getsites()
    {
        return $this->getallsites();
    }
    
     //for pagination
     //count site remmitance details (for pagination)
    function countrevdeposits($zsiteID)
    {
          $stmt = "Select COUNT(*) as count from siteremittance where SiteID = '".$zsiteID."' AND Status = 2";
          $this->executeQuery($stmt);
          $this->_row = $this->fetchData();
          return $this->_row;
      }
    
     //get all verified deposits per site
    function countrevdeposits2($zsiteID)
    {
          $stmt = "Select COUNT(*) as count from siteremittance where SiteID = '".$zsiteID."' AND Status = 3";
          $this->executeQuery($stmt);
          $this->_row = $this->fetchData();
          return $this->_row;
      }

     //view all site remittances to reverse (for pagination)
     function viewreversalpage($zsiteID, $zstart, $zlimit)
      {
         if($zsiteID > 0)
         {
          $stmt = "SELECT a.SiteRemittanceID, a.SiteRemittanceID, a.RemittanceTypeID,a.BankID,a.Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a INNER JOIN sites b
		ON a.SiteID = b.SiteID INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID WHERE a.SiteID = '".$zsiteID."' AND a.Status = 2 LIMIT $zstart, $zlimit";
         }
         else
         {
          $stmt = "SELECT a.SiteRemittanceID, a.RemittanceTypeID,a.BankID,a.Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a INNER JOIN sites b
		ON  a.SiteID = b.SiteID INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID LIMIT $zstart, $zlimit";
         }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
     //view all site remittances to reverse (for pagination)
     function viewreversalpage2($zsiteID, $zstart, $zlimit)
      {
         if($zsiteID > 0)
         {
          $stmt = "SELECT a.SiteRemittanceID, a.SiteRemittanceID, a.RemittanceTypeID,a.BankID,Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a INNER JOIN sites b
		ON a.SiteID = b.SiteID INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID WHERE a.SiteID = '".$zsiteID."' AND a.Status = 3 LIMIT $zstart, $zlimit";
         }
         else
         {
          $stmt = "SELECT a.SiteRemittanceID, a.RemittanceTypeID,a.BankID,Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a INNER JOIN sites b
		ON a.SiteID = b.SiteID INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID WHERE a.Status = 3 LIMIT $zstart, $zlimit";
         }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }      
      
      //insert in siteremittance, for posting of deposit(cashierdeposit.php)
      function insertdepositposting($zremittancetypeID, $zbankID, $zbranch, $zamount, $zbanktransID, $zbanktransdate, $zcheckno, $zaid, $zparticulars, $zsiteID, $zstatus, $zdatecreated, $zsitedate)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO siteremittance (RemittanceTypeID, BankID, Branch, Amount, BankTransactionID, BankTransactionDate, ChequeNumber, CreatedByAID, Particulars, SiteID, Status, DateCreated, StatusUpdateDate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
          $this->bindparameter(1, $zremittancetypeID);
          $this->bindparameter(2, $zbankID);
          $this->bindparameter(3, $zbranch);
          $this->bindparameter(4, $zamount);
          $this->bindparameter(5, $zbanktransID);
          $this->bindparameter(6, $zbanktransdate);
          $this->bindparameter(7, $zcheckno);
          $this->bindparameter(8, $zaid);
          $this->bindparameter(9, $zparticulars);
          $this->bindparameter(10, $zsiteID);
          $this->bindparameter(11, $zstatus);
          $this->bindparameter(12, $zdatecreated);
          $this->bindparameter(13, $zsitedate);
          if($this->execute())
          {
              $lastinsertid = $this->insertedid();
              $this->committrans();
              return $lastinsertid;
          }
          else
          {
              $this->rollbacktrans();
              return 0;
          }
      }
      
      //get bankcode, and bankID
      function getbanknames()
      {
          $stmt = "SELECT BankID, BankName, BankCode FROM ref_banks WHERE Status = 1 ORDER BY BankName ASC";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();
      }
      
      //get owner per site
      function getoperator($zsiteID)
      {
          $stmt = "select a.AID,a.Username,a.AccountTypeID,a.Status, b.SiteID from accounts a inner join siteaccounts b on b.AID = a.AID where b.SiteID = ? and a.AccountTypeID = 2 and a.Status = 1";      
          $this->prepare($stmt);
          $this->bindparameter(1, $zsiteID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      //insert pegs/ grosshold confirmation
      function insertconfirmation($zsiteID, $zdatecredited, $zsiterep, $zamount, $zaid, $zdatecreated)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO grossholdconfirmation(SiteID,DateCredited,SiteRepresentative,AmountConfirmed,PostedbyAID,DateCreated) values(?,?,?,?,?, now_usec())");
          $this->bindparameter(1, $zsiteID);
          $this->bindparameter(2, $zdatecredited);
          $this->bindparameter(3, $zsiterep);
          $this->bindparameter(4, $zamount);
          $this->bindparameter(5, $zaid);
          
          if($this->execute())
          {
              $confirmationID = $this->insertedid();
              try
              {
                  $this->committrans();
                  return $confirmationID;
              }
              catch(PDOException $e)
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
       
      public function getSiteCodeList() {
         $query = "SELECT SiteCode,SiteName, SiteID, POSAccountNo FROM sites WHERE SiteID <> 1 AND Status = 1 ORDER BY SiteCode";   
         $this->prepare($query);
         $this->execute();
         return $this->fetchAllData();         
      }
      
      public function grossHoldMonitoringTotal($startdate,$enddate) {
         $yesterday = date('Y-m-d',strtotime(date("Y-m-d", strtotime(date('Y-m-d'))) . " -1 day"));
         $search = '';
         $field = '';
         $field2 = '';         
         if(isset($_GET['siteid']) && $_GET['siteid'] != '') {
             $search .= " AND srb.SiteID = '".$_GET['siteid']."'";
             $field = 'and SiteID = '.$_GET['siteid'];
             $field2 = 'and t.SiteID = '.$_GET['siteid'];
         }
         $total_row = 0; 
         
         // with site id
         if(isset($_GET['siteid']) && $_GET['siteid']) {
             $query = "SELECT td.TransactionDetailsID, td.TransactionReferenceID,td.TransactionSummaryID, " . 
                "td.SiteID,s.SiteCode,s.SiteName,td.TerminalID,s.POSAccountNo,td.TransactionType,COALESCE(td.Amount,0) AS " . 
                "Amount, td.DateCreated,td.ServiceID,td.CreatedByAID, a.UserName " . 
                "FROM transactiondetails td " . 
                "INNER JOIN accounts a ON a.AID = td.CreatedByAID " . 
                "INNER JOIN sites s ON s.SiteID = td.SiteID " . 
                "WHERE td.DateCreated >= ? AND td.DateCreated < ? AND td.Status IN (1,4) AND td.SiteID = ?";
         } else {
             $query = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.TransactionSummaryID, " . 
                "td.SiteID, s.SiteCode, s.SiteName, td.TerminalID, s.POSAccountNo, td.TransactionType, " . 
                "COALESCE(td.Amount,0) AS Amount, td.DateCreated, td.ServiceID, td.CreateBYAID, a.UserName " . 
                "FROM transactiondetails td " . 
                "INNER JOIN accounts a ON a.AID = td.CreatedByAID " . 
                "INNER JOIN sites s ON s.SiteID = td.SiteID " . 
                "WHERE td.DateCreated >= ? AND td.DateCreated < ? AND td.Status IN (1,4) ORDER BY s.SiteCode";
         }
         $this->prepare($query);
         $this->bindparameter(1, $startdate);
         $this->bindparameter(2, $enddate);
         if(isset($_GET['siteid']) && $_GET['siteid']) {
             $this->bindparameter(3, $_GET['siteid']);
         }
         
         $this->execute();
         $row = $this->fetchAllData();
         if(isset($row[0]['totalrow']))
             $total_row = $row[0]['totalrow'];
         unset($yesterday, $search, $field, $field2, $total_row, $query, $row);
         return $total_row;
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
                
                //Query for Replenishments
                $replenish = "SELECT SiteID, Amount, DateCreated FROM replenishments
                                    WHERE DateCreated >= ? AND DateCreated < ? AND SiteID = ?";

                //Query for Collection
                $collection = "SELECT SiteID, Amount, DateCreated FROM siteremittance
                                    WHERE Status = 3 AND DateCreated >= ? AND DateCreated < ? AND SiteID = ? ";
              
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
                
                //Query for Replenishments
                $replenish = "SELECT SiteID, Amount, DateCreated FROM replenishments
                                    WHERE DateCreated >= ? AND DateCreated < ?";

                //Query for Collection
                $collection = "SELECT SiteID, Amount, DateCreated FROM siteremittance
                                    WHERE Status = 3 AND DateCreated >= ? AND DateCreated < ?";
              
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
                    'Coupon'=>"0.00",
                    'Replenishment'=>"0.00",
                    'Collection'=>"0.00"
                 ); 
          }
          
          $this->prepare($replenish);
          $this->bindparameter(1, $startdate);
          $this->bindparameter(2, $enddate);
          if(isset($_GET['siteid']) && $_GET['siteid']) { $this->bindparameter(3, $_GET['siteid']); }    
          $this->execute();        
          $replenishdata =  $this->fetchAllData();
          
          //Get the replenishment total amount per site
          foreach ($replenishdata as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                        if($varrmerge[$keys]["Replenishment"] == "0.00"){
                            $varrmerge[$keys]["Replenishment"] = (float)$value1["Amount"];
                        } else {
                            $varrmerge[$keys]["Replenishment"] += (float)$value1["Amount"];
                        }
                        break;
                    }
                }  
          }
          
          $this->prepare($collection);
          $this->bindparameter(1, $startdate);
          $this->bindparameter(2, $enddate);
          if(isset($_GET['siteid']) && $_GET['siteid']) { $this->bindparameter(3, $_GET['siteid']); }    
          $this->execute();        
          $collectiondata =  $this->fetchAllData();
          
          //Get the collection total amount per site
          foreach ($collectiondata as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                        if($varrmerge[$keys]["Collection"] == "0.00"){
                            $varrmerge[$keys]["Collection"] = (float)$value1["Amount"];
                        } else {
                            $varrmerge[$keys]["Collection"] += (float)$value1["Amount"];
                        }
                        break;
                    }
                }  
          }

          foreach($varrmerge as $key => $trans) {
                $vsiteID[$key] = $trans['SiteID'];
          }
          
          $sites = implode(",", $vsiteID);
          $query2 = "SELECT ts.DateStarted, ts.DateEnded, tr.SiteID,
                                IFNULL(SUM(CASE tr.TransactionType WHEN 'D' THEN tr.Amount ELSE 0 END), 0) AS TotalDeposit,                     -- TOTAL DEPOSITS
                                IFNULL(SUM(CASE tr.TransactionType WHEN 'R' THEN tr.Amount ELSE 0 END), 0) AS TotalReload,                      -- TOTAL RELOAD
                                IFNULL(SUM(CASE tr.TransactionType WHEN 'W' THEN tr.Amount ELSE 0 END), 0) AS TotalRedemption           -- TOTAL REDEMPTION
                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                INNER JOIN npos.sites s ON tr.SiteID = s.SiteID
                                WHERE tr.SiteID IN ($sites)
                                    AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                    AND tr.Status IN(1,4)
                                GROUP By tr.SiteID
                                ORDER BY s.$sort $dir"; 
        
          $query3 = "SELECT tr.TransactionSummaryID AS TransSummID, SUBSTR(t.TerminalCode,11) AS TerminalCode, tr.TransactionType AS TransType,

                                -- TOTAL DEPOSIT --
                                CASE tr.TransactionType
                                  WHEN 'D' THEN SUM(tr.Amount)
                                  ELSE 0
                                END As TotalDeposit,

                                -- DEPOSIT COUPON --
                                SUM(CASE tr.TransactionType
                                  WHEN 'D' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN tr.Amount
                                      ELSE 0
                                     END
                                  ELSE 0 END) As DepositCoupon,

                                -- DEPOSIT CASH --
                                SUM(CASE tr.TransactionType
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
                                END) As DepositCash,
                                
                                -- DEPOSIT TICKET --
                                SUM(CASE tr.TransactionType
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
                                END) As DepositTicket,

                                -- TOTAL RELOAD --
                                CASE tr.TransactionType
                                  WHEN 'R' THEN SUM(tr.Amount)
                                  ELSE 0 -- Not Reload
                                END As TotalReload,

                                -- RELOAD COUPON --
                                SUM(CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN tr.Amount
                                      ELSE 0
                                     END
                                  ELSE 0 END) As ReloadCoupon,

                                -- RELOAD CASH --
                                SUM(CASE tr.TransactionType
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
                                END) As ReloadCash,
                                
                                -- RELOAD TICKET --
                                SUM(CASE tr.TransactionType
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
                                                   AND tr.TransactionDetailsID = sdtls.TransactionDetailsID
                                                  AND sdtls.TransactionType = 2
                                                  AND sdtls.PaymentType = 2)  -- Reload, Ticket
                                        END
                                    END
                                  ELSE 0 -- Not Reload
                                END) As ReloadTicket,

                                -- TOTAL REDEMPTION --
                                CASE tr.TransactionType
                                  WHEN 'W' THEN SUM(tr.Amount)
                                  ELSE 0
                                END As TotalRedemption,

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

                                tr.DateCreated, tr.SiteID
                                FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)
                                INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                WHERE tr.SiteID IN ($sites)
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY tr.TerminalID, tr.DateCreated DESC"; 
        
            //Total the Deposit and Reload Cash, Deposit and Reload Coupons
            //Total Redemption made by the cashier and the EGM
            $this->prepare($query3);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
            $this->execute();  
            $rows3 =  $this->fetchAllData();
            foreach ($rows3 as $value1) {
                foreach ($varrmerge as $keys => $value2) {
                    if($value1["SiteID"] == $value2["SiteID"]){
                        if($value1["DepositCash"] != '0.00'){
                            $varrmerge[$keys]["DepositCash"] = (float)$varrmerge[$keys]["DepositCash"] + (float)$value1["DepositCash"];
                            $varrmerge[$keys]["Deposit"] = (float)$varrmerge[$keys]["Deposit"] + (float)$value1["DepositCash"];
                        }
                        if($value1["ReloadCash"] != '0.00'){
                            $varrmerge[$keys]["ReloadCash"] = (float)$varrmerge[$keys]["ReloadCash"] + (float)$value1["ReloadCash"];
                            $varrmerge[$keys]["Reload"] = (float)$varrmerge[$keys]["Reload"] + (float)$value1["ReloadCash"];
                        }
                        if($value1["RedemptionCashier"] != '0.00'){
                            $varrmerge[$keys]["RedemptionCashier"] = (float)$varrmerge[$keys]["RedemptionCashier"] + (float)$value1["RedemptionCashier"];
                            $varrmerge[$keys]["Redemption"] = (float)$varrmerge[$keys]["Redemption"] + (float)$value1["RedemptionCashier"];
                        }
                        if($value1["RedemptionGenesis"] != '0.00'){
                            $varrmerge[$keys]["Redemption"] = (float)$varrmerge[$keys]["Redemption"] + (float)$value1["RedemptionGenesis"];
                        }
                        if($value1["DepositCoupon"] != '0.00'){
                            $varrmerge[$keys]["Coupon"] = (float)$varrmerge[$keys]["Coupon"] + (float)$value1["DepositCoupon"];
                            $varrmerge[$keys]["Deposit"] = (float)$varrmerge[$keys]["Deposit"] + (float)$value1["DepositCoupon"];
                        }
                        if($value1["ReloadCoupon"] != '0.00'){
                            $varrmerge[$keys]["Coupon"] = (float)$varrmerge[$keys]["Coupon"] + (float)$value1["ReloadCoupon"];
                            $varrmerge[$keys]["Reload"] = (float)$varrmerge[$keys]["Reload"] + (float)$value1["ReloadCoupon"];
                        }
                        if($value1["DepositTicket"] != '0.00'){
                            $varrmerge[$keys]["Deposit"] = (float)$varrmerge[$keys]["Deposit"] + (float)$value1["DepositTicket"];
                        }
                        if($value1["ReloadTicket"] != '0.00'){
                            $varrmerge[$keys]["Reload"] = (float)$varrmerge[$keys]["Reload"] + (float)$value1["ReloadTicket"];
                        }
                        break;
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
                                -- Total e-SAFE Deposits
                                SUM(CASE et.TransType
                                        WHEN 'D' THEN et.Amount -- if deposit
                                        ELSE 0 -- if not deposit
                                END) AS EwalletDeposits,

                                -- Total e-SAFE Withdrawal
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

          unset($query,$query2,$query3, $sort, $dir, $rows1);   
          return $varrmerge;
      }
      
      public function getBankDepositHistoryTotal($startdate,$enddate) {
          $total_row = 0;
          $query = "SELECT count(sr.SiteRemittanceID) AS totalrow " .
                "FROM siteremittance sr " .
                "LEFT JOIN sites st ON sr.SiteID = st.SiteID " .
                "LEFT JOIN accounts at ON sr.CreatedByAID = at.CreatedByAID " .
                "LEFT JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID " .
                "LEFT JOIN ref_banks bk ON sr.BankID = bk.BankID " .
                "LEFT JOIN accounts ats ON sr.VerifiedBy = ats.CreatedByAID " .
                "WHERE sr.DateCreated BETWEEN '$startdate' AND '$enddate' AND sr.Status = 3 ";
         $this->prepare($query);
         $this->execute();
         $row =  $this->fetchAllData();
         if(isset($row[0]['totalrow']))
             $total_row = $row[0]['totalrow'];
         unset($row, $query);
         return $total_row;
      }
      
      public function getBankDepositHistory($sort, $dir, $start, $limit,$startdate,$enddate) {
          $query = "SELECT sr.SiteRemittanceID,
                sr.RemittanceTypeID,
                sr.BankID,
                sr.Branch,
                sr.Amount,
                sr.BankTransactionID,
                sr.BankTransactionDate,
                sr.ChequeNumber,
                sr.Particulars,
                sr.Status,
                sr.SiteID,
                ad.Name as name,
                st.SiteName as siteName,
                sr.DateCreated as DateCreated,
                DATE_FORMAT(sr.StatusUpdateDate,'%Y-%m-%d %h:%i:%s %p') DateUpdated,
                bk.BankName as bankname,
                rt.RemittanceName as remittancename,
                ats.Username as PostedBy,
                st.POSAccountNo
                FROM siteremittance sr 
                LEFT JOIN sites st ON sr.SiteID = st.SiteID 
                LEFT JOIN accountdetails ad ON sr.CreatedByAID = ad.AID 
                LEFT JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID 
                LEFT JOIN ref_banks bk ON sr.BankID = bk.BankID 
                LEFT JOIN accounts ats ON sr.VerifiedBy = ats.CreatedByAID 
                WHERE sr.DateCreated BETWEEN '$startdate' AND '$enddate' AND sr.Status = 3 
                ORDER BY $sort $dir LIMIT $start,$limit";
         $this->prepare($query);
         $this->execute();
         return $this->fetchAllData();
      }
      
      public function getCohAdjustmentHistory($sort, $dir, $start, $limit,$startdate,$enddate) {
          $query = "SELECT b.SiteName, b.POSAccountNo, 
                    a.Amount, a.Reason, d.Name as ApprovedBy, 
                    c.Name AS CreatedBy, a.DateCreated
                    FROM npos.cohadjustment a
                    LEFT JOIN npos.sites b ON a.SiteID = b.SiteID
                    LEFT JOIN npos.accountdetails c ON a.CreatedByAID = c.AID
                    LEFT JOIN npos.accountdetails d ON a.ApprovedByAID = d.AID
                WHERE a.DateCreated BETWEEN '$startdate' AND '$enddate'
                ORDER BY $sort $dir, a.DateCreated ASC LIMIT $start,$limit";
          
         $this->prepare($query);
         $this->execute();
         return $this->fetchAllData();
      }
      
      public function getCohAdjustmentHistoryTotal($startdate,$enddate) {
          $total_row = 0;
          $query = "SELECT count( a.COHAdjustmentID) AS totalrow
                    FROM npos.cohadjustment a
                    LEFT JOIN npos.sites b ON a.SiteID = b.SiteID
                    LEFT JOIN npos.accountdetails c ON a.CreatedByAID = c.AID
                    LEFT JOIN npos.accountdetails d ON a.ApprovedByAID = d.AID" .
                " WHERE a.DateCreated BETWEEN '$startdate' AND '$enddate'";
         $this->prepare($query);
         $this->execute();
         $row =  $this->fetchAllData();
         if(isset($row[0]['totalrow']))
             $total_row = $row[0]['totalrow'];
         unset($row, $query);
        return $total_row;
      }
      
      public function getTopUpHistoryTotal($startdate,$enddate,$type,$site_code) {
          //if site was selected All
          if($site_code == '')
          {
                //if top-up type was selected All
                if($type == '')
                {
                    $stmt = "SELECT count(tuth.TopupHistoryID) AS totalrow
                             FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                             WHERE tuth.DateCreated >= ?
                             AND tuth.DateCreated < ?
                             AND tuth.TopupTransactionType IN(0,1)";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                }
                else
                {
                    $stmt = "SELECT count(tuth.TopupHistoryID) AS totalrow
                             FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                             WHERE tuth.DateCreated >= ?
                             AND tuth.DateCreated < ?
                             AND tuth.TopupTransactionType = ?";      
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                    $this->bindparameter(3, $type);
                }
            }
            else
            {
                //if top-up type was selected All
                if($type == '')
                {
                    $stmt = "SELECT count(tuth.TopupHistoryID) AS totalrow
                             FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                             WHERE tuth.DateCreated >= ?
                             AND tuth.DateCreated < ? AND tuth.SiteID = ? 
                             AND tuth.TopupTransactionType IN(0,1)";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                    $this->bindparameter(3, $site_code);
                }
                else
                {
                    $stmt = "SELECT count(tuth.TopupHistoryID) AS totalrow
                             FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                             WHERE tuth.DateCreated >= ?
                             AND tuth.DateCreated < ?
                             AND tuth.TopupTransactionType = ? AND tuth.SiteID = ?";      
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                    $this->bindparameter(3, $type);
                    $this->bindparameter(4, $site_code);
                }
          }
          $this->execute();
          return $this->fetchData();
      }
      
      public function getTopUpHistory($sort, $dir, $start, $limit,$startdate,$enddate,$type,$site_code) {
          //if site was selected All
          if($site_code == '')
          {
                //if top-up type was selected All
                if($type == '')
                {
                    $stmt = "SELECT tuth.TopupHistoryID,tuth.SiteID,tuth.StartBalance,
                               tuth.EndBalance,tuth.MinBalance,tuth.MaxBalance, 
                               tuth.TopupAmount,tuth.TotalTopupAmount,tuth.TopupType, 
                               tuth.TopupTransactionType,tuth.DateCreated,tuth.Remarks, 
                               tuth.TopupCount,tuth.CreatedByAID, st.SiteName,st.SiteCode,st.POSAccountNo
                               FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                               WHERE tuth.DateCreated >= ?
                               AND tuth.DateCreated < ? AND tuth.TopupTransactionType IN(0,1)
                               ORDER BY $sort $dir LIMIT $start,$limit";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                }
                else
                {
                    $stmt = "SELECT tuth.TopupHistoryID,tuth.SiteID,tuth.StartBalance,
                               tuth.EndBalance,tuth.MinBalance,tuth.MaxBalance, 
                               tuth.TopupAmount,tuth.TotalTopupAmount,tuth.TopupType, 
                               tuth.TopupTransactionType,tuth.DateCreated,tuth.Remarks, 
                               tuth.TopupCount,tuth.CreatedByAID, st.SiteName,st.SiteCode,st.POSAccountNo
                               FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                               WHERE tuth.DateCreated >= ?
                               AND tuth.DateCreated < ?
                               AND tuth.TopupTransactionType = ? 
                               ORDER BY $sort $dir LIMIT $start,$limit";      
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                    $this->bindparameter(3, $type);
                }
            }
            else
            {
                //if top-up type was selected All
                if($type == '')
                {
                    $stmt = "SELECT tuth.TopupHistoryID,tuth.SiteID,tuth.StartBalance,
                               tuth.EndBalance,tuth.MinBalance,tuth.MaxBalance, 
                               tuth.TopupAmount,tuth.TotalTopupAmount,tuth.TopupType, 
                               tuth.TopupTransactionType,tuth.DateCreated,tuth.Remarks, 
                               tuth.TopupCount,tuth.CreatedByAID, st.SiteName,st.SiteCode,st.POSAccountNo
                               FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                               WHERE tuth.DateCreated >= ?
                               AND tuth.DateCreated < ? AND tuth.TopupTransactionType IN(0,1)
                               AND tuth.SiteID = ? ORDER BY $sort $dir LIMIT $start,$limit";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                    $this->bindparameter(3, $site_code);
                }
                else
                {
                    $stmt = "SELECT tuth.TopupHistoryID,tuth.SiteID,tuth.StartBalance,
                               tuth.EndBalance,tuth.MinBalance,tuth.MaxBalance, 
                               tuth.TopupAmount,tuth.TotalTopupAmount,tuth.TopupType, 
                               tuth.TopupTransactionType,tuth.DateCreated,tuth.Remarks, 
                               tuth.TopupCount,tuth.CreatedByAID, st.SiteName,st.SiteCode,st.POSAccountNo
                               FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                               WHERE tuth.DateCreated >= ?
                               AND tuth.DateCreated < ?
                               AND tuth.TopupTransactionType = ? AND tuth.SiteID = ? 
                               ORDER BY $sort $dir LIMIT $start,$limit";      
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                    $this->bindparameter(3, $type);
                    $this->bindparameter(4, $site_code);
                }
          }
          
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function getReversalManualTotal($startdate,$enddate) {
          $total_row = 0;
          $query = "SELECT count(th.TopupHistoryID) AS totalrow " .
              "FROM topuptransactionhistory as th " .
              "inner join accounts as acc on acc.AID = th.CreatedByAID " .
              "inner join sites on sites.SiteID = th.SiteID " . 
              "where th.DateCreated >= '$startdate' and th.DateCreated < '$enddate' and th.TopupTransactionType = 2 " . 
              "ORDER BY sites.SiteCode ASC";
          $this->prepare($query);
          $this->execute();
          $row =  $this->fetchAllData();
          if(isset($row[0]['totalrow']))
              $total_row = $row[0]['totalrow'];
          unset($query, $row);
          return $total_row;
      }     
      
      public function getReversalManual($sort, $dir, $start, $limit,$startdate,$enddate) {
          $query = "SELECT th.TopupHistoryID,th.SiteID,sites.SiteName as SiteName,sites.SiteCode as SiteCode,
              th.StartBalance,th.EndBalance,th.TopupAmount as ReversedAmount,
              th.DateCreated as TransDate,th.CreatedByAID,acc.Username as ReversedBy,sites.POSAccountNo " .
              "FROM topuptransactionhistory as th " .
              "inner join accounts as acc on acc.AID = th.CreatedByAID " .
              "inner join sites on sites.SiteID = th.SiteID " . 
              "where th.DateCreated Between '$startdate' and '$enddate' and th.TopupTransactionType = 2 " . 
              "ORDER BY $sort $dir LIMIT $start,$limit";
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function getManualRedemptionTotal($startdate,$enddate) {
            $total_row = 0;
            $query = "SELECT count(mr.ManualRedemptionsID) AS totalrow 
                      FROM manualredemptions mr 
                      JOIN sites st ON mr.SiteID = st.SiteID
                      JOIN terminals tm ON mr.TerminalID = tm.TerminalID
                      JOIN accounts at ON mr.ProcessedByAID = at.AID 
                      WHERE mr.TransactionDate >= '$startdate 06:00:00' AND mr.TransactionDate < '$enddate 06:00:00'";
            $this->prepare($query);
            $this->execute();
            
            $rows = $this->fetchAllData(); 
            if(isset($rows[0]['totalrow'])) {
                $total_row = $rows[0]['totalrow'];
            }
            unset($query, $rows);
            return $total_row;
      }
      
      public function getManualRedemption($sort, $dir, $start, $limit,$startdate,$enddate) {
            $query = "SELECT mr.ManualRedemptionsID,
                mr.ReportedAmount,
                mr.ActualAmount,
                mr.Remarks,
                mr.Status,
                mr.TransactionDate as TransDate,
                mr.TicketID,
                mr.TransactionID,
                st.SiteName,
                st.SiteCode,
                tm.TerminalCode,
                st.POSAccountNo,
                at.UserName,
                rs.ServiceName
                FROM manualredemptions mr 
                INNER JOIN sites st ON mr.SiteID = st.SiteID 
                INNER JOIN terminals tm ON mr.TerminalID = tm.TerminalID
                INNER JOIN accounts at ON mr.ProcessedByAID = at.AID 
                LEFT JOIN ref_services rs ON mr.ServiceID = rs.ServiceID
                WHERE mr.TransactionDate >= '$startdate' AND mr.TransactionDate < '$enddate' 
                ORDER BY $sort $dir LIMIT $start,$limit";
            $this->prepare($query);
            $this->execute();
            return $this->fetchAllData();      
      }
      
      /**
      * @author Gerardo V. Jagolino Jr.
      * @return array
      * get siteID using gicen sitecode
      */
      public final function getSiteID ($siteID) {
       
        $query = "
                  SELECT  
                    SiteID
                  FROM 
                    sites
                  WHERE
                    SiteCODE LIKE :siteID";
        
        $this->prepare($query);
        
        $this->bindParam(":siteID", $siteID);
        
        $this->execute();
        
        $record = $this->fetchData();
        
        return $record["SiteID"];
        
    }
      
    /**
      * @author Gerardo V. Jagolino Jr.
      * @modifiedby April Rose Q. Depliyan
      * @return array
      * get active session count using siteid or cardnumber
      */
      public final function getActiveSessionCount ($siteID, $cardnumber, $terminalID = 'all') {
        if($cardnumber == ''){
            if($siteID == 'all') {
                $query = "SELECT count(t.TerminalID) as ActiveSession
                                    FROM terminalsessions as ts, terminals as t
                                    WHERE t.TerminalID = ts.TerminalID";
        
                $this->prepare($query);
            } else {
                $terminalID != "all" ? $additioncond = "AND ts.TerminalID = :terminalID":$additioncond = "";
                $query = " SELECT count(t.TerminalID) as ActiveSession
                                    FROM terminalsessions as ts, terminals as t
                                    WHERE t.TerminalID = ts.TerminalID AND t.SiteID = :siteID ".$additioncond;
        
                $this->prepare($query);
                $this->bindParam(":siteID", $siteID);
                $terminalID != "all" ? $this->bindParam(":terminalID", $terminalID):"";
            }
            
        } else {
            $query = "SELECT count(t.TerminalID) as ActiveSession
                                FROM terminalsessions as ts, terminals as t
                                WHERE t.TerminalID = ts.TerminalID
                                AND ts.LoyaltyCardNumber = :cardnumber";
        
                $this->prepare($query);
                $this->bindParam(":cardnumber", $cardnumber);
        }

        $this->execute();
        $record = $this->fetchAllData();
        return $record[0]["ActiveSession"];
    }
    
 
    
    /**
      * @author Gerardo V. Jagolino Jr.
      * @modifiedby April Rose Q. Depliyan
      * @return array
      * get active session count using siteid with user mode
      */
    public final function getActiveSessionCountMod ($siteID, $cardnumber, $usermode, $terminalID = 'all') {
        if($cardnumber == '') {
            if($siteID == 'all') {
                $query = "SELECT count(t.TerminalID) as ActiveSession
                                    FROM terminalsessions as ts, terminals as t
                                    WHERE t.TerminalID = ts.TerminalID
                                    AND ts.UserMode = :usermode";
        
                $this->prepare($query);
                $this->bindParam(":usermode", $usermode); 
            } else {
                $terminalID != "all" ? $additioncond = "AND ts.TerminalID = :terminalID":$additioncond = "";
                $query = "SELECT count(t.TerminalID) as ActiveSession
                                    FROM terminalsessions as ts, terminals as t
                                    WHERE t.TerminalID = ts.TerminalID
                                    AND t.SiteID = :siteID
                                    AND ts.UserMode = :usermode ".$additioncond;
        
                $this->prepare($query);
                $this->bindParam(":siteID", $siteID);
                $this->bindParam(":usermode", $usermode); 
                $terminalID != "all" ? $this->bindParam(":terminalID", $terminalID):"";
            }
        } else {
            $query = "SELECT count(t.TerminalID) as ActiveSession
                                FROM terminalsessions as ts, terminals as t
                                WHERE t.TerminalID = ts.TerminalID
                                AND ts.LoyaltyCardNumber = :cardnumber
                                AND ts.UserMode = :usermode";
        
                $this->prepare($query);
                $this->bindParam(":cardnumber", $cardnumber);
                $this->bindParam(":usermode", $usermode);
        }
        
        
        $this->execute();
        
        $record = $this->fetchAllData();
        
        return $record[0]["ActiveSession"];
        
    }
    
      
      
      public function getAllSiteCode() {
            $query = "SELECT SiteID, SiteName, SiteCode, POSAccountNo from sites WHERE Status = 1 AND SiteID <> 1 ORDER BY SiteCode ASC";
            $this->prepare($query);
            $this->execute();
            return $this->fetchAllData();
      }
      
      public function getSitesDetails($owner_id) {
            $and = '';
            if($owner_id != 'All') {
                $and = " AND OwnerAID = '" . $owner_id . "' ";
            }
            $query = "SELECT SiteID, SiteName, SiteCode, POSAccountNo from sites WHERE Status = 1 AND SiteID <> 1 $and ORDER BY SiteCode ASC";
            $this->prepare($query);
            $this->execute();
            return $this->fetchAllData();
      }
      
      public function getActiveTerminalsTotal() {
          $sitecode = $_GET['sitecode'];
          $condition = " WHERE s.SiteCode = '$sitecode' ";
          if($_GET['sitecode'] == 'all') {
              $condition = '';
          }
          $total_row = 0;
          $query = "SELECT count(ts.TerminalID) AS totalrow FROM terminalsessions ts " .
              "left join (terminals as t, sites as s) on (ts.TerminalID = t.terminalID and t.SiteID = s.SiteID) $condition " . 
              "ORDER BY s.SiteCode ASC";
          $this->prepare($query);
          $this->execute();
          $rows = $this->fetchAllData();
          if($rows[0]['totalrow'])
              $total_row = $rows[0]['totalrow'];
          unset($rows, $query, $condition, $sitecode);
          return $total_row;
      }
      
      /**
      * @author Gerardo V. Jagolino Jr.
      * @return array
      * get total count rows by selecting active terminals via card number
      */
      public function getActiveTerminalsTotalub() {
          $cardnumber = $_GET['cardnumber'];
          $condition = " WHERE ts.LoyaltyCardNumber = '$cardnumber' ";
          if($_GET['cardnumber'] == 'all') {
              $condition = '';
          }
          $total_row = 0;
          $query = "SELECT count(ts.TerminalID) AS totalrow FROM terminalsessions ts " .
              "left join (terminals as t, sites as s) on (ts.TerminalID = t.terminalID and t.SiteID = s.SiteID) $condition " . 
              "ORDER BY s.SiteCode ASC";
          $this->prepare($query);
          $this->execute();
          $rows = $this->fetchAllData();
          if($rows[0]['totalrow'])
              $total_row = $rows[0]['totalrow'];
          unset($rows, $query, $condition, $cardnumber);
          return $total_row;
      }
      
      public function getActiveTerminals($sort, $dir, $start, $limit) {
          $sitecode = $_GET['sitecode'];
          $condition = " WHERE s.SiteCode = '$sitecode' ";
          if($_GET['sitecode'] == 'all') {
              $condition = '';
          }
          
          $query = "SELECT ts.TerminalID, t.TerminalName,s.SiteName, s.POSAccountNo, s.SiteCode,ts.ServiceID,
                            t.TerminalCode, rs.ServiceName, ts.UserMode FROM terminalsessions ts
                            INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                            INNER JOIN sites as s ON t.SiteID = s.SiteID 
                            INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                            $condition
                            ORDER BY $sort $dir LIMIT $start,$limit";
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();
      }
      
      
      public function getActiveTerminals2($sitecode, $terminalID, $dir, $start, $limit) {
          
          if($sitecode != "all"){
              $condition = " WHERE s.SiteCode = '$sitecode' ";
          } else {
              $condition = '';
          }
          
          if($terminalID != "all"){
              $condition .= " AND ts.TerminalID = $terminalID ";
          }

          $query = "SELECT ts.TerminalID, t.TerminalName,  CASE t.TerminalType WHEN 0 THEN 'Regular' WHEN 1 THEN 'Genesis' ELSE 'e-SAFE' END AS TerminalType, 
                            s.SiteName, s.POSAccountNo, s.SiteCode,ts.ServiceID,
                            t.TerminalCode, rs.ServiceName, ts.UserMode, ts.LoyaltyCardNumber FROM terminalsessions ts
                            INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                            INNER JOIN sites as s ON t.SiteID = s.SiteID 
                            INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                            $condition
                            ORDER BY ts.TerminalID $dir LIMIT $start,$limit";
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();
      }
      
      
      public function countActiveTerminals2($sitecode,$terminalID) {
          
          if($sitecode != "all"){
              $condition = " WHERE s.SiteCode = '$sitecode' ";
          } else {
              $condition = '';
          }
          
          if($terminalID != "all"){
              $condition .= " AND ts.TerminalID = $terminalID ";
          }
          
          
          $query = "SELECT COUNT(ts.TerminalID) AS rcount FROM terminalsessions ts
                            INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                            INNER JOIN sites as s ON t.SiteID = s.SiteID 
                            INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                            $condition";
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();
      }
      
      
      public function getUBServiceLogin($terminalid) {

          $query = "SELECT UBServiceLogin FROM terminalsessions WHERE TerminalID = ?";
          $this->prepare($query);
          $this->bindparameter(1, $terminalid);
          $this->execute();
          $ublogin = $this->fetchData();
          $ublogin = $ublogin['UBServiceLogin'];
          return $ublogin;
      }
      
    //get service name
    public function getServiceName($serviceID)
    {
        $sql = "SELECT ServiceName FROM ref_services WHERE ServiceID = ?";
        $this->prepare($sql);
        $this->bindparameter(1, $serviceID);
        $this->execute();
        $servicename = $this->fetchData();
        return $servicename = $servicename['ServiceName'];
    }
    
      /**
      * @author Gerardo V. Jagolino Jr.
      * @return array
      *  selecting active terminals via card number
      */
      public function getActiveTerminalsub($sort, $dir, $start, $limit) {
          $cardnumber = $_GET['cardnumber'];
          $acctype = $_SESSION['acctype'];
          $condition = " WHERE ts.LoyaltyCardNumber = '$cardnumber' ";
          if($_GET['cardnumber'] == 'all') {
              $condition = '';
          }
          
          $query = "SELECT ts.TerminalID, t.TerminalName,CASE t.TerminalType WHEN 0 THEN 'Regular' WHEN 1 THEN 'Genesis' ELSE 'e-SAFE' END AS TerminalType,
                            s.SiteName, s.POSAccountNo, s.SiteCode,ts.ServiceID,
                            t.TerminalCode, rs.ServiceName, ts.UserMode FROM terminalsessions ts
                            INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                            INNER JOIN sites as s ON t.SiteID = s.SiteID 
                            INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                            $condition
                            ORDER BY $sort $dir LIMIT $start,$limit";
          
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function getAgentSessionGuid($terminalid) {
            $query = "SELECT C.ServiceAgentSessionID FROM serviceterminals A INNER JOIn terminalmapping B ON A.ServiceTerminalID = B.ServiceTerminalID
                   INNER JOIN serviceagentsessions C ON A.ServiceAgentID = C.ServiceAgentID WHERE B.TerminalID = '" . $terminalid . "';";
            $this->prepare($query);
            $this->execute();
            $rows = $this->fetchAllData();
            if(isset($rows[0]['ServiceAgentSessionID']))
                return $rows[0]['ServiceAgentSessionID'];
            return '';
      }
      
      public function getRefServices() {
          $query = "SELECT ServiceID, Alias, ServiceName FROM ref_services";
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function getRefServicesWithServiceGroup() {
          $query = "SELECT rs.ServiceID, rs.Alias, rs.ServiceName, rsg.ServiceGroupName FROM ref_services rs
                               INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID";
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function getBettingCreditTotal($condition = null,$comp=null,$owner=null,$site_id=null,$report=null) {
            switch ($report)
            {
                case 'critical':
                    switch ($site_id)
                    {
                        case 'All':
                            switch ($owner)
                            {
                                case 'All': // ALL OWNER
                                    $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance
                                        FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance <= sb.MinBalance";
                                    $this->prepare($query);                                    
                                    break;
                                
                                case $owner > 0: //SPECIFIED OWNER
                                    $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance
                                    s.OwnerAID  FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance <= sb.MinBalance AND s.OwnerAID = ?";  
                                    $this->prepare($query);
                                    $this->bindparameter(1,$owner); 
                                    break;
                            }
                            break;
                        case $site_id > 0: // with owner specified
                            $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance,sb.SiteID
                            FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                            WHERE sb.Balance <= sb.MinBalance AND sb.SiteID = ?";                        
                            $this->prepare($query);
                            $this->bindparameter(1,$site_id); 
                            break;
                    }
                    break;
                case 'safe':
                    switch ($site_id)
                    {
                        case 'All':
                            switch ($owner)
                            {
                                case 'All': // ALL OWNER
                                    $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance
                                        FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance > sb.MinBalance";
                                    $this->prepare($query);                                    
                                    break;
                                
                                case $owner > 0: //SPECIFIED OWNER
                                    $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance,
                                    s.OwnerAID  FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance > sb.MinBalance AND s.OwnerAID = ?";  
                                    $this->prepare($query);
                                    $this->bindparameter(1,$owner); 
                                    break;
                            }
                            break;
                        case $site_id > 0: // with owner specified
                            $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance,sb.SiteID
                            FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                            WHERE sb.Balance > sb.MinBalance AND sb.SiteID = ?";                        
                            $this->prepare($query);
                            $this->bindparameter(1,$site_id); 
                            break;
                    }
                    break;
            }
            $this->execute();
            unset($condition,$comp,$owner,$site_id,$report);
            return $this->fetchAllData(); 
      }
      
      public function getBettingCredit($sort, $dir, $start, $limit,$condition = null,$comp=null,$owner=null,$site_id=null, $report = null) {
    
        switch ($report)
        {
            case 'critical':
                switch ($site_id)                
                {                
                    case 'All':
                        switch ($owner)
                        {
                            case 'All': // ALL OWNER
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance <= sb.MinBalance ORDER BY $sort $dir LIMIT $start,$limit";
                                $this->prepare($query);                                
                                break;
                            
                            case $owner > 0: // OWNER AND ALL ASSIGNED SITES
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance <= sb.MinBalance AND s.OwnerAID = ? ORDER BY $sort $dir LIMIT $start,$limit";
                                $this->prepare($query);   
                                $this->bindparameter(1,$owner);                                                               
                                break;
                        }
                        break;
                    case $site_id > 0: // SPECIFIED OWNER AND SITE
                        $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                            sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                            s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                            WHERE sb.Balance <= sb.MinBalance AND sb.SiteID = ? ORDER BY $sort $dir LIMIT $start,$limit";                        
                        $this->prepare($query);
                        $this->bindparameter(1,$site_id); 
                        break;
                }
                break;
            case 'safe':
                switch ($site_id)
                {
                    case 'All':
                        switch ($owner)
                        {
                            case 'All': // ALL OWNER
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance > sb.MinBalance ORDER BY $sort $dir LIMIT $start,$limit";
                                $this->prepare($query);                                
                                break;
                            
                            case $owner > 0: // OWNER AND ALL ASSIGNED SITES
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance > sb.MinBalance AND s.OwnerAID = ? ORDER BY $sort $dir LIMIT $start,$limit";
                                $this->prepare($query);   
                                $this->bindparameter(1,$owner);                                                               
                                break;
                        }
                        break;
                    case $site_id > 0:  // SPECIFIED OWNER AND SITE
                        $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                            sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                            s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                            WHERE sb.Balance > sb.MinBalance AND sb.SiteID = ? ORDER BY $sort $dir LIMIT $start,$limit";                        
                        $this->prepare($query);
                        $this->bindparameter(1,$site_id); 
                        break;
                }
                break;
        }        
        $this->execute();
        unset($sort, $dir, $start, $limit,$condition ,$comp,$owner,$site_id, $report);
        return $this->fetchAllData(); 
      }  
      
      public function getOwner() {
          $query = 'SELECT DISTINCT s.OwnerAID, ad.Name FROM sites s INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID INNER JOIN accounts a ON a.AID = s.OwnerAID WHERE a.AccountTypeID = 2 ' . 
                  'ORDER BY ad.Name';
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function getTerminalMap($terminalid) {
         $query = "SELECT ServiceTerminalAccount FROM serviceterminals A INNER JOIN terminalmapping B ON A.ServiceTerminalID = B.ServiceTerminalID WHERE B.TerminalID = '" . $terminalid . "';";
         $this->prepare($query);
         $this->execute();
         $row =  $this->fetchAllData();
         if(isset($row[0]['ServiceTerminalAccount']))
             return $row[0]['ServiceTerminalAccount'];
         return '';
      }
      
      /***** For Manual Redemption ******/
      
      //get service terminals mapped (for MG)
      function getmglogin($zTerminalID){
          $stmt = "Select ServiceAgentID, ServiceTerminalAccount from serviceterminals as a INNER JOIN terminalmapping as b ON a.ServiceTerminalID = b.ServiceTerminalID where TerminalID = '".$zTerminalID."'";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
      //get agent session ID based from agentid (for MG)
      function getagentsession($zAgentID)
      {
          $stmt = "select ServiceAgentSessionID from serviceagentsessions where ServiceAgentID = '".$zAgentID."'";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
      
      function getterminalvalues($zterminalID)
      {
          $stmt = "SELECT TerminalName, TerminalCode FROM terminals WHERE TerminalID = ? ORDER BY TerminalCode ASC";
          $this->prepare($stmt);
          $this->bindparameter(1, $zterminalID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      /**** End Manual Redemption *****/
      function getremittancetypes()
      {
          $stmt = "SELECT RemittanceTypeID, RemittanceName FROM ref_remittancetype WHERE Status = 1 ORDER BY RemittanceName ASC";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();
      }
      
      function checkaccountdetails($zaccname, $zaccpassword)
      {
          $convertpass= sha1($zaccpassword);
          $stmt = "SELECT COUNT(AID) ctracc FROM accounts WHERE UserName = ? AND Password = ? AND Status = 1 AND AccountTypeID = 5";
          $this->prepare($stmt);
          $this->bindparameter(1, $zaccname);
          $this->bindparameter(2, $convertpass);
          $this->execute();
          return $this->fetchData();
      }
      
      // Get account AID
      public function getAccountAID ($zaccname, $zaccpassword) {
        $convertpass= sha1($zaccpassword);
        $query = "SELECT AID FROM accounts WHERE UserName = ? AND Password = ? AND Status = 1 AND AccountTypeID = 5";
        $this->prepare($query);
        $this->bindparameter(1, $zaccname);
        $this->bindparameter(2, $convertpass);
        $this->execute();
        $record = $this->fetchData();
        return $record["AID"];
        
    }
      
      public function ListPEGSSubject($sort,$dir,$startdate,$enddate) {
          
            $query1 = "SELECT s.POSAccountNo, s.SiteID, a.AID, ad.Name
                            FROM sites s
                            INNER JOIN accounts a ON a.AID = s.OwnerAID
                            INNER JOIN accountdetails ad ON ad.AID = a.AID 
                            ORDER by s.SiteID";
              
             $query2 = "SELECT sb.SiteID,sb.Balance,sb.MaxBalance FROM sitebalance sb";
              
          if(isset($_GET['siteid']) && $_GET['siteid'] != '') {
              $query3 = "SELECT td.Amount, td.SiteID                 
                FROM transactiondetails td                
                WHERE td.DateCreated >= ? AND td.DateCreated < ? AND td.Status IN (1,4) AND td.SiteID = ?
                ORDER BY s.$sort $dir";
              
              
          } else {
               $query3 = "SELECT td.TransactionDetailsID, td.Amount,td.TransactionType, td.SiteID                 
                FROM transactiondetails td                
                WHERE td.DateCreated >= ? AND td.DateCreated < ? -- AND td.Status IN (1,4) 
                ORDER BY td.$sort $dir";
          }
         
          $this->prepare($query3);
          $this->bindparameter(1, $startdate);
          $this->bindparameter(2, $enddate);
          if(isset($_GET['siteid']) && $_GET['siteid']) {
            $this->bindparameter(3, $_GET['siteid']);
          }  
         
          $this->execute(); 
          $rows1 =  $this->fetchAllData();
          
          //all site account with account names
          $this->prepare($query1);
          $this->execute();
          $siteaccount =  $this->fetchAllData();
          
          //all sitebalance resord
          $this->prepare($query2);
          $this->execute();
          $sitebalance =  $this->fetchAllData();          
       
          $trans_details = array();
          $varrmerge = array();
          
          foreach($rows1 as $value) 
          {                
                if(!isset($varrmerge[$value['SiteID']])) 
                {
                     $mergedep = 0;
                     $mergerel = 0;
                     $mergewith = 0; 
                     $varrmerge[$value['SiteID']] = array(                       
                        'SiteID'=>$value['SiteID'],       
                        'Redemption'=>$mergewith,
                        'Deposit'=>$mergedep,
                        'Reload'=>$mergerel
                     ); 
                }
                $trans = array();
                switch ($value['TransactionType']) 
                {
                    case 'W':
                        $mergewith = $mergewith + $value['Amount'];
                        $trans = array('Redemption'=>$mergewith);
                        break;
                    case 'D':
                        $mergedep = $mergedep + $value['Amount'];
                        $trans = array('Deposit'=>$mergedep);
                        break;
                    case 'R':
                        $mergerel = $mergerel + $value['Amount'];
                        $trans = array('Reload'=>$mergerel);
                        break;
                }
                $varrmerge[$value['SiteID']] = array_merge($varrmerge[$value['SiteID']], $trans);
          }
          
          //merge tansactiondetails records to siteaccounts
          $append = new AppendArrays();
          $columnNamesToBind = array("POSAccountNo","Name");
          $mergedColumnNames = array("POSAccountNo","Name");          
          $varrmerge1 = $append->joinArrayByKeys($varrmerge, $siteaccount, 'SiteID', 'SiteID', $mergedColumnNames, $columnNamesToBind, null);

          //merge transactiondeytails with siteaccounts with site balance
          $append1 = new AppendArrays();
          $columnNamesToBind = array("Balance","MaxBalance");
          $mergedColumnNames = array("Balance","MaxBalance");
          $varrmerge2 = $append1->joinArrayByKeys($varrmerge1, $sitebalance, 'SiteID', 'SiteID', $mergedColumnNames, $columnNamesToBind, null);
          $arrResult = array();         
          for($i=0; $i<count($varrmerge2); $i++) 
          {                 
              $gross_hold = (($varrmerge2[$i]['Deposit'] + $varrmerge2[$i]['Reload'] - $varrmerge2[$i]['Redemption']) );
              $allowable_topup = (($varrmerge2[$i]['MaxBalance'] -($gross_hold + $varrmerge2[$i]['Balance'] )));
              if($allowable_topup > 0)

              { 
                  $arrResult[$i]["SiteID"]= $varrmerge2[$i]["SiteID"];
                  $arrResult[$i]["POSAccountNo"]= $varrmerge2[$i]["POSAccountNo"];
                  $arrResult[$i]["Name"]= $varrmerge2[$i]["Name"];
                  $arrResult[$i]["Balance"]= $varrmerge2[$i]["Balance"];
                  $arrResult[$i]["Deposit"]= $varrmerge2[$i]["Deposit"];
                  $arrResult[$i]["Redemption"]= $varrmerge2[$i]["Redemption"];
                  $arrResult[$i]["Reload"]= $varrmerge2[$i]["Reload"];
                  $arrResult[$i]["GrossHold"]= $gross_hold;
                  $arrResult[$i]["Allowable"] =   $allowable_topup;
              }
          }
          unset($query1, $query2, $query3, $rows1, $siteaccount, $sitebalance, 
                $trans_details, $varrmerge, $trans, $columnNamesToBind, $mergedColumnNames, 
                $varrmerge1, $append, $append1, $varrmerge2);
          return $arrResult;        
      } 
      
      function getidbyposacc($zposaccno)
      {
          $stmt = "SELECT SiteID FROM sites WHERE POSAccountNo = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $zposaccno);
          $this->execute();
          return $this->fetchData();
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
        
        //insert into manualredemption
      function insertmanualredemption($zsiteID, $zterminalID, $zreportedAmt, 
              $zactualAmt, $ztransactionDate, $zreqByAID, $zprocByAID, $zremarks, 
              $zdateeff, $zstatus, $ztransactionID, $zsummaryID,$zticketID, $zCmbServerID,
              $ztransStatus)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO manualredemptions(SiteID, TerminalID, ReportedAmount, 
              ActualAmount, TransactionDate, RequestedByAID, ProcessedByAID, Remarks, 
              DateEffective, Status, TransactionID, LastTransactionSummaryID, TicketID, ServiceID,
              TransactionStatus) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
          $this->bindparameter(1, $zsiteID);
          $this->bindparameter(2, $zterminalID);
          $this->bindparameter(3, $zreportedAmt);
          $this->bindparameter(4, $zactualAmt);
          $this->bindparameter(5, $ztransactionDate);
          $this->bindparameter(6, $zreqByAID);
          $this->bindparameter(7, $zprocByAID);
          $this->bindparameter(8, $zremarks);
          $this->bindparameter(9, $zdateeff);
          $this->bindparameter(10, $zstatus);
          $this->bindparameter(11, $ztransactionID);
          $this->bindparameter(12, $zsummaryID);
          $this->bindparameter(13, $zticketID);
          $this->bindparameter(14, $zCmbServerID);
          $this->bindparameter(15, $ztransStatus);
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
         * @param $zsiteID, $zterminalID, $zreportedAmt, 
              $zactualAmt, $ztransactionDate, $zreqByAID, $zprocByAID, $zremarks, 
              $zdateeff, $zstatus, $ztransactionID, $zsummaryID,$zticketID, $zCmbServerID,
              $ztransStatus, $loyaltycardnumber, $mid, $usermode
         * @return integer 
         * insert into manualredemption user based with loyalty card number, memberid and user mode
         */
      function insertmanualredemptionub($zsiteID, $zterminalID, $zreportedAmt, 
              $zactualAmt, $ztransactionDate, $zreqByAID, $zprocByAID, $zremarks, 
              $zdateeff, $zstatus, $ztransactionID, $zsummaryID,$zticketID, $zCmbServerID,
              $ztransStatus, $loyaltycardnumber, $mid, $usermode)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO manualredemptions(SiteID, TerminalID, ReportedAmount, 
              ActualAmount, TransactionDate, RequestedByAID, ProcessedByAID, Remarks, 
              DateEffective, Status, TransactionID, LastTransactionSummaryID, TicketID, ServiceID,
              TransactionStatus, LoyaltyCardNumber, MID, UserMode) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
          $this->bindparameter(1, $zsiteID);
          $this->bindparameter(2, $zterminalID);
          $this->bindparameter(3, $zreportedAmt);
          $this->bindparameter(4, $zactualAmt);
          $this->bindparameter(5, $ztransactionDate);
          $this->bindparameter(6, $zreqByAID);
          $this->bindparameter(7, $zprocByAID);
          $this->bindparameter(8, $zremarks);
          $this->bindparameter(9, $zdateeff);
          $this->bindparameter(10, $zstatus);
          $this->bindparameter(11, $ztransactionID);
          $this->bindparameter(12, $zsummaryID);
          $this->bindparameter(13, $zticketID);
          $this->bindparameter(14, $zCmbServerID);
          $this->bindparameter(15, $ztransStatus);
          $this->bindparameter(16, $loyaltycardnumber);
          $this->bindparameter(17, $mid);
          $this->bindparameter(18, $usermode);
          if($this->execute())
           {
              $lastid = $this->insertedid();
              $this->committrans();
              return $lastid;
           }
          else
           {
               $this->rollbacktrans();
               return 0;
           }
      }
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $status, $actualamount, $transid, $dateff, $transstatus, $manid
     * @return array 
     * update manual redemptions table with status 1
     */
      function updateManualRedemptionub($status, $actualamount, $transid, $dateff, $transstatus, $manid)
      {
        $this->prepare("UPDATE manualredemptions SET Status = ?, ActualAmount = ?, 
                        TransactionID = ?, DateEffective = ?, TransactionStatus = ? 
                        WHERE ManualRedemptionsID = ?");
        $this->bindparameter(1,$status);
        $this->bindparameter(2,$actualamount);
        $this->bindparameter(3,$transid);
        $this->bindparameter(4,$dateff);
        $this->bindparameter(5,$transstatus);
        $this->bindparameter(6,$manid);
        $this->execute();
        return $this->rowCount();
      }
      
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param $status, $actualamount, $transid, $dateff, $transstatus, $manid
     * @return array 
     * update manual redemptions table with status 1
     */
      function updateManualRedemptionFailedub($status, $manid)
      {
        $this->prepare("UPDATE manualredemptions SET Status = ? WHERE ManualRedemptionsID = ?");
        $this->bindparameter(1,$status);
        $this->bindparameter(2,$manid);
        $this->execute();
        return $this->rowCount();
      }
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index, $search
     * @return array 
     * insert service transaction refence
     */
      function insertserviceTransRef($zserviceid, $ztransorigin)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO servicetransactionref (ServiceID, TransactionOrigin, DateCreated) VALUES (?,?,now_usec())");
          $this->bindparameter(1, $zserviceid);
          $this->bindparameter(2, $ztransorigin);
          $this->execute();
          $insertedid = $this->insertedid();
          try{
              $this->committrans();
              return $insertedid;
          }catch (PDOException $e){
              $this->rollbacktrans();
              return 0;
          }
      }
      
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index, $search
     * @return array 
     * get last summary id using certain terminal id
     */
      function getLastSummaryID($zterminalID)
      {
          $stmt ="SELECT max(TransactionsSummaryID) as summaryID 
              FROM transactionsummary 
              WHERE TerminalID = ? and DateEnded <> 0";
          
          $this->prepare($stmt);
          $this->bindparameter(1, $zterminalID);
          $this->execute();
          return $this->fetchData();
      }
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index, $search
     * @return array 
     * get casino array with given casino service
     */
      public function loopAndFind($array, $index, $search){
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
         * @param int $serviceid
         * @return array 
         * get service name and status of a certain service provider using its id
         */
        public function getCasinoName($serviceid)
        {
            $stmt = "SELECT ServiceName, Status, UserMode FROM ref_services WHERE ServiceID = ?";
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
         * @author Gerardo V. Jagolino Jr.
         * @param int $terminalid
         * @return array 
         * get service name and status of a certain service provider using its id
         */
        public function getTCodeSiteID($terminalid)
        {
            $stmt = "SELECT TerminalCode, SiteID FROM terminals WHERE TerminalID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $terminalid);
            $this->execute();
            return $this->fetchAllData();
        }
        
        
        /**
         * @author Gerardo V. Jagolino Jr.
         * @param $loyaltycard, $serviceid
         * @return string 
         * get TransactionRequestLogID of a certain service provider and mermbership card
         */
        public function getMaxTransreqlogid($loyaltycard, $serviceid)
        {
            $stmt = "SELECT MAX(TransactionRequestLogID) AS TransactionRequestLogID FROM transactionrequestlogs 
                     WHERE LoyaltyCardNumber = ? AND ServiceID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $loyaltycard);
            $this->bindparameter(2, $serviceid);
            $this->execute();
            $site =  $this->fetchData();
            return $site['TransactionRequestLogID'];
        }
        
        /**
         * @author Gerardo V. Jagolino Jr.
         * @param $loyaltycard, $serviceid
         * @return string 
         * get SiteID, TerminalID of a certain TransactionRequestLogID
         */
        public function getSiteTer($transid)
        {
            $stmt = "SELECT SiteID, TerminalID FROM transactionrequestlogs WHERE TransactionRequestLogID = ? ";
            $this->prepare($stmt);
            $this->bindparameter(1, $transid);
            $this->execute();
            return $this->fetchAllData();
        }
        
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $casino
     * @return string 
     * get UserMode of a certain casino service
     */
    public function checkUserMode($casino){
        $sql = "SELECT UserMode FROM ref_services WHERE ServiceID = ?";
        $this->prepare($sql);
        $this->bindparameter(1, $casino);
        $this->execute();
        $usermode = $this->fetchData();
        $usermode = $usermode['UserMode'];
        return $usermode;
    }
    
    /**
     * @Description: Get Service Group Name
     * @DateCreated: 2014-02-06
     */
    public function getServiceGrpName($serviceId){
        $sql = "SELECT rsg.ServiceGroupname FROM ref_services rs
                        INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                        WHERE rs.ServiceID = ?";
        $this->prepare($sql);
        $this->bindparameter(1, $serviceId);
        $this->execute();
        $servicegroup = $this->fetchData();
        $servicegroup = $servicegroup['ServiceGroupname'];
        return $servicegroup;
    }
    
    public function getServiceStatus($serviceid) {
          $query = "SELECT Status FROM ref_services WHERE ServiceID = ?";
          $this->prepare($query);
          $this->bindparameter(1, $serviceid);
          $this->execute();
          $service = $this->fetchData();
          return $service["Status"];
    }
      
    public function updateTerminalSessions($balance, $terminalID){
        $this->prepare("UPDATE terminalsessions SET LastBalance = ? WHERE TerminalID = ?");
        $this->bindparameter(1,$balance);
        $this->bindparameter(2,$terminalID);
        $this->execute();
        return $this->rowCount();
    }
    
     function geteWalletTransactionHistory($sort, $dir, $start, $limit,$site,$transType,$transStatus,$startDate,$endDate)
      {
        
         $where="";
         
         if($transType != 'All' && $transStatus != 'All'){
             
          
                $where.="WHERE a.SiteID=$site"
                     ." AND a.TransType='$transType'"
                     ." AND a.Status=$transStatus"
                     ." AND a.StartDate >= '$startDate' "
                     ." AND a.StartDate <  '$endDate' ";
             
         }
         elseif($transType == 'All' && $transStatus <> 'All'){
              
             
                 $where.="WHERE a.SiteID=$site"
                        . " AND a.Status=$transStatus"
                        .  " AND a.StartDate >= '$startDate' "
                        .   " AND a.StartDate < '$endDate' ";
             

         }
         elseif($transType <> 'All' && $transStatus == 'All'){
             $where.="WHERE a.SiteID=$site"
                     ." AND a.TransType='$transType'"
                     . " AND a.StartDate >= '$startDate' "
                     .  " AND a.StartDate < '$endDate' ";
             
         }
         else{
             $where.="WHERE a.SiteID=$site"
                     ." AND a.StartDate >= '$startDate' "
                     . " AND a.StartDate < '$endDate' "; 
         }
         
        
          $stmt = "SELECT a.EwalletTransID,a.LoyaltyCardNumber, a.StartDate ,"
                  ." a.EndDate , a.Amount, a.TransType,a.Status,b.Name"
                  ." FROM npos.ewallettrans a"
                  ." INNER JOIN npos.accountdetails b ON b.AID = a.CreatedByAID ".$where
                  ."ORDER BY $sort $dir LIMIT $start,$limit";     
          
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();
      }
      
          public function getTotaleWalletTransactionHistory($site,$transType,$transStatus,$startDate,$endDate) {
          $where="";
          $total_row=0;
   
         if($transType != 'All' && $transStatus != 'All'){
             
          
                $where.="WHERE a.SiteID=$site"
                     ." AND a.TransType='$transType'"
                     ." AND a.Status=$transStatus"
                     ." AND a.StartDate >= '$startDate' "
                     ." AND a.StartDate <  '$endDate' ";
           
         }
         elseif($transType == 'All' && $transStatus <> 'All'){
              
               
                 $where.="WHERE a.SiteID=$site"
                        . " AND a.Status=$transStatus"
                        .  " AND a.StartDate >= '$startDate' "
                        .   " AND a.StartDate < '$endDate' ";
               

         }
         elseif($transType <> 'All' && $transStatus == 'All'){
             $where.="WHERE a.SiteID=$site"
                     ." AND a.TransType='$transType'"
                     . " AND a.StartDate >= '$startDate' "
                     .  " AND a.StartDate < '$endDate' ";
             
         }
         else{
             $where.="WHERE a.SiteID=$site"
                     ." AND a.StartDate >= '$startDate' "
                     . " AND a.StartDate < '$endDate' "; 
         }
         
        
          $stmt = "SELECT COUNT(*) as totalrow"
                  ." FROM npos.ewallettrans a"
                  ." INNER JOIN npos.accountdetails b ON b.AID = a.CreatedByAID ".$where;     
          
          $this->prepare($stmt);
          $this->execute();
           $rows = $this->fetchAllData(); 
          if(isset($rows[0]['totalrow'])) {
                $total_row = $rows[0]['totalrow'];
            }
            unset($stmt, $rows);
            return $total_row;
      }
      
      function getCardNumberStatus($cardNumber){
          
          $stmt = "SELECT Status"
                  ." FROM loyaltydb.membercards"
                  ." WHERE CardNumber =?";     
          
          $this->prepare($stmt);
          $this->bindparameter(1, $cardNumber);
          $this->execute();
          $cardStatus = $this->fetchData();
          $cardStatus = $cardStatus['Status'];
        
          return $cardStatus;
          
      }
       function geteWalletTransactionCardHistory($sort, $dir, $start, $limit,$cardNum,$transType,$transStatus,$startDate,$endDate)
      {
        
         $where="";
         
         if($transType != 'All' && $transStatus != 'All'){
            
             $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                     ." AND a.TransType='$transType'"
                     . " AND a.Status=$transStatus"
                     .  " AND a.StartDate >= '$startDate' "
                     .   " AND a.StartDate < '$endDate' 
                     ";
             
         }
         elseif($transType == 'All' && $transStatus <> 'All'){
            
                $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                       . " AND a.Status=$transStatus"
                        . " AND a.StartDate >= '$startDate' "
                        .  " AND a.StartDate < '$endDate' ";
          

         }
         elseif($transType <> 'All' && $transStatus == 'All'){
             $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                     ." AND a.TransType='$transType'"
                     . " AND a.StartDate >= '$startDate' "
                     .  " AND a.StartDate < '$endDate' ";
             
         }
         else{
             $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                     ." AND a.StartDate >= '$startDate' "
                     . " AND a.StartDate < '$endDate' "; 
         }
         
        
          $stmt = "SELECT c.SiteCode, a.EwalletTransID,a.LoyaltyCardNumber, a.StartDate ,"
                  ." a.EndDate , a.Amount, a.TransType,a.Status,b.Name"
                  ." FROM npos.ewallettrans a"
                  ." INNER JOIN npos.sites c ON c.SiteID = a.SiteID "
                  ." INNER JOIN npos.accountdetails b ON b.AID = a.CreatedByAID ".$where
                  ."ORDER BY $sort $dir LIMIT $start,$limit";     
          
          
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();
      }
      
          public function getTotaleWalletTransactionCardHistory($cardNum,$transType,$transStatus,$startDate,$endDate) {
          $where="";
          $total_row=0;
          
         if($transType != 'All' && $transStatus != 'All'){
            
             $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                     ." AND a.TransType='$transType'"
                     . " AND a.Status=$transStatus"
                     .  " AND a.StartDate >= '$startDate' "
                     .   " AND a.StartDate < '$endDate' 
                     ";
             
         }
         elseif($transType == 'All' && $transStatus <> 'All'){
            
                $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                        . " AND a.StartDate >= '$startDate' "
                        .  " AND a.StartDate < '$endDate' ";
          

         }
         elseif($transType <> 'All' && $transStatus == 'All'){
             $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                     ." AND a.TransType='$transType'"
                     . " AND a.StartDate >= '$startDate' "
                     .  " AND a.StartDate < '$endDate' ";
             
         }
         else{
             $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                     ." AND a.StartDate >= '$startDate' "
                     . " AND a.StartDate < '$endDate' "; 
         }
         
        
          $stmt = "SELECT COUNT(*) as totalrow"
                  ." FROM npos.ewallettrans a"
                  ." INNER JOIN npos.accountdetails b ON b.AID = a.CreatedByAID ".$where;     
          
          $this->prepare($stmt);
          $this->execute();
           $rows = $this->fetchAllData(); 
          if(isset($rows[0]['totalrow'])) {
                $total_row = $rows[0]['totalrow'];
            }
            unset($stmt, $rows);
            return $total_row;
      }
    
    public function updateMemberServices($balance, $mid, $serviceid, $mrid){
        $this->prepare("UPDATE memberservices SET CurrentBalance = ?,LastTransaction = ?, CurrentBalanceLastUpdate = NOW(6) WHERE MID = ? AND ServiceID = ?");
        $this->bindparameter(1,$balance);
        $this->bindparameter(2,"MR[".$mrid."]");
        $this->bindparameter(3,$mid);
        $this->bindparameter(4,$serviceid);
        $this->execute();
        return $this->rowCount();
    }
    /**
     * Get All Banks
     * @param type $count if <b>TRUE</b> return total count only, <b>FALSE</b> return banks with details 
     * @return type
     * @author Mark Kenneth Esguerra
     * @date Febraury 18, 2015
     */
    public function getAllBanks($count = false)
    {
        if (!$count)
        {
            $stmt = "SELECT * FROM ref_banks ORDER BY BankCode ASC";
            $this->prepare($stmt);
            $this->execute();
            $result = $this->fetchAllData(); 
        }
        else
        {
            $stmt = "SELECT COUNT(BankID) as Count FROM ref_banks";
            $this->prepare($stmt);
            $this->execute();
            $result = $this->fetchData();
        }
        
        return $result;
    }
    /**
     * Insert new bank
     * @param type $bankcode
     * @param type $bankname
     * @param type $isaccredited
     * @param type $status
     * @return boolean
     * @author Mark Kenneth Esguerra
     * @date Febraury 23, 2015
     */
    public function insertBank($bankcode, $bankname, $isaccredited, $status)
    {
        $this->begintrans();
        try
        {
            $sql = "INSERT INTO npos.ref_banks (
                                            BankCode, 
                                            BankName, 
                                            IsAccredited, 
                                            Status 
                                        ) VALUES (
                                            ?, 
                                            ?, 
                                            ?, 
                                            ? 
                                        )";
            $this->prepare($sql);
            $this->bindparameter(1, $bankcode);
            $this->bindparameter(2, $bankname);
            $this->bindparameter(3, $isaccredited);
            $this->bindparameter(4, $status);
            if ($this->execute())
            {
                try
                {
                    $this->committrans();
                    return array('ErrorCode' => 0, 
                                 'Message' => "$bankname has successfully added.");
                }
                catch (PDOException $e)
                {
                    $this->rollbacktrans();
                    return array('ErrorCode' => 1, 
                                 'Message' => 'An error occured while adding the new bank.');
                }
            }
            else
            {
                $this->rollbacktrans();
                return array('ErrorCode' => 1, 
                             'Message' => 'An error occured while adding the new bank.');
            }
        }
        catch (PDOException $e)
        {
            $this->rollbacktrans();
            return array('ErrorCode' => 1, 
                         'Message' => 'An error occured while adding the new bank.');
        }
    }
    /**
     * Check if Bank Code already exist
     * @param type $bankcode
     * @return type array Count
     * @author Mark Kenneth Esguerra
     */
    public function checkIfBankCodeExist($bankcode, $bankID = null)
    {
        if (is_null($bankID))
        {
            $sql = "SELECT COUNT(BankID) AS Count 
                    FROM npos.ref_banks 
                    WHERE BankCode = ?";
            $this->prepare($sql);
            $this->bindparameter(1, $bankcode);
            $this->execute();
            $result = $this->fetchData();   
        }
        else
        {
            $sql = "SELECT COUNT(BankID) AS Count 
                    FROM npos.ref_banks 
                    WHERE BankCode = ? AND BankID <> ?";
            $this->prepare($sql);
            $this->bindparameter(1, $bankcode);
            $this->bindparameter(2, $bankID);
            $this->execute();
            $result = $this->fetchData();
        }
        return $result;
    }
    /**
     * Get Bank Details
     * @param type $bankID
     * @return type array 
     * @author Mark Kenneth Esguerra
     * @date Febraury 23, 2015
     */
    public function getBankDetails($bankID)
    {
        $sql = "SELECT BankCode, BankName, IsAccredited 
                FROM npos.ref_banks 
                WHERE BankID = ?";
        $this->prepare($sql);
        $this->bindparameter(1, $bankID);
        $this->execute();
        $result = $this->fetchData();
        
        return $result;
    }
    /**
     * Update Bank Details
     * @param type $bankID
     * @param type $bankcode
     * @param type $bankname
     * @param type $isaccredited
     * @return boolean
     * @author Mark Kenneth Esguerra
     * @date Febraury 23, 2015
     */
    public function updateBankDetails($bankID, $bankcode, $bankname, $isaccredited)
    {
        $this->begintrans();
        try
        {
            $sql = "UPDATE npos.ref_banks 
                    SET BankCode = ?, 
                        BankName = ?, 
                        IsAccredited = ? 
                    WHERE BankID = ?";
            $this->prepare($sql);
            $this->bindparameter(1, $bankcode);
            $this->bindparameter(2, $bankname);
            $this->bindparameter(3, $isaccredited);
            $this->bindparameter(4, $bankID);
            if ($this->execute())
            {
                try
                {
                    $this->committrans();
                    return array('ErrorCode' => 0, 
                                 'Message' => 'Bank details successfully updated.');
                }
                catch (PDOException $e)
                {
                    $this->rollbacktrans();
                    return array('ErrorCode' => 1, 
                                 'Message' => 'An error occured while updating the bank details.');
                }
            }
            else
            {
                $this->rollbacktrans();
                return array('ErrorCode' => 2, 
                             'Message' => 'Bank details unchanged.');
                
            }
        }
        catch (PDOException $e)
        {
            $this->rollbacktrans();
            return array('ErrorCode' => 1, 
                         'Message' => 'An error occured while updating the bank details.');
        }
    }
    /**
     * Get Bank Status
     * @param type $bankID
     * @return type
     */
    public function getBankStatus($bankID)
    {
        $sql = "SELECT Status FROM npos.ref_banks 
                WHERE BankID = ?";
        $this->prepare($sql);
        $this->bindparameter(1, $bankID);
        $this->execute();
        $result = $this->fetchData();
        
        return $result;
    }
    /**
     * Update bank status.
     * @param type $bankID
     * @param type $status
     * @return type
     * @author Mark Kenneth Esguerra
     * @date Feb 24, 2015
     */
    public function updateBankStatus($bankID, $status)
    {
        $this->begintrans();
        try
        {
            $sql = "UPDATE npos.ref_banks 
                    SET Status = ? 
                    WHERE BankID = ?";
            $this->prepare($sql);
            $this->bindparameter(1, $status);
            $this->bindparameter(2, $bankID);
            if ($this->execute())
            {
                try
                {
                    $this->committrans();
                    return array('ErrorCode' => 0, 
                                 'Message' => 'Bank status successfully updated.');
                }
                catch (PDOException $e)
                {
                    $this->rollbacktrans();
                    return array('ErrorCode' => 1, 
                                 'Message' => 'An error occured while updating records.');
                }
            }
            else
            {
                $this->rollbacktrans();
                return array('ErrorCode' => 2, 
                             'Message' => 'Bank status unchanged.');
            }
        }
        catch (PDOException $e)
        {
            $this->rollbacktrans();
            return array('ErrorCode' => 1, 
                         'Message' => 'An error occured while updating records.');
        }
    }
    /**
     * Update bank status.
     * @param type $bankID
     * @param type $status
     * @return type
     * @author Joene Floresca
     * @date Feb 27, 2015
     */
    public function insertCohAdjustment($siteID, $amount, $reason, $createdBy, $approved, $dateCreated)
    {
        $this->begintrans();
        try
        {
            $sql = "INSERT INTO npos.cohadjustment (SiteID,  Amount, Reason, CreatedByAID, ApprovedByAID, DateCreated ) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $this->prepare($sql);
            $this->bindparameter(1, $siteID);
            $this->bindparameter(2, $amount);
            $this->bindparameter(3, $reason);
            $this->bindparameter(4, $createdBy);
            $this->bindparameter(5, $approved);
            $this->bindparameter(6, $dateCreated);
            if ($this->execute())
            {
                try
                {
                    $lastInsertID = $this->insertedid();
                    $this->committrans();
                    
                    return array('ErrorCode' => 0, 
                                 'Message' => "Cash on hand Adjustment has successfully added.",
                                 'LastInsertID' => $lastInsertID);
                }
                catch (PDOException $e)
                {
                    $this->rollbacktrans();
                    return array('ErrorCode' => 1, 
                                 'Message' => 'An error occured while adding the new Cash on hand Adjustment.');
                }
            }
            else
            {
                $this->rollbacktrans();
                return array('ErrorCode' => 1, 
                             'Message' => 'An error occured while adding the new Cash on hand Adjustment .');
            }
        }
        catch (PDOException $e)
        {
            $this->rollbacktrans();
            return array('ErrorCode' => 1, 
                         'Message' => 'An error occured while adding the new checkaccountdetails.');
        }
    }
    
    /**
     * Get active replenishments from ref_replenishmenttype table.
     * @date Mar 02, 2015
     */
    
    public function getActiveReplenishment()
    {
        
            $sql = "SELECT ReplenishmentTypeID, ReplenishmentName FROM npos.ref_replenishmenttype WHERE Status = 1";
            $this->prepare($sql);
            $this->execute();
            $result = $this->fetchAllData();

            return $result;
            
    }
    
    
    //@date edited Mar 02, 2015
    function insertreplenishment($zsiteID, $zreptype, $zamount, $zrefnum,  $vaid)
    {     
          $this->begintrans();
          
          try {
            $this->prepare("INSERT INTO npos.replenishments (SiteID, ReplenishmentTypeID, Amount, ReferenceNumber, DateCreated, CreatedByAID) VALUES (?,?,?,?,NOW(6),?)");
            $this->bindparameter(1, $zsiteID);
            $this->bindparameter(2, $zreptype);
            $this->bindparameter(3, $zamount);
            $this->bindparameter(4, $zrefnum);
            //$this->bindparameter(3, $zdatecreated);
            $this->bindparameter(5, $vaid);
            //$this->bindparameter(4, $zdatecredited);
            if($this->execute())
            {
                try
                {
                    $replenishmentID = $this->insertedid();
                    $this->committrans();
                    return $replenishmentID;
                }
                catch(PDOException $e)
                {
                    $this->rollbacktrans();
                    return array('ErrorCode' => 1, 
                         'Message' => 'An error occured while adding the new replenishments.');
                }
            }
            else
            {
                $this->rollbacktrans();
                return array('ErrorCode' => 1, 
                         'Message' => 'An error occured while adding the new replenishments.');
            }      
          } catch(PDOException $e) {
            $this->rollbacktrans();
            return array('ErrorCode' => 1, 
                         'Message' => 'An error occured while adding the new replenishments.');
          }
    }
    //get bankcode, and bankID
    public function getAllBankNames()
    {
        $stmt = "SELECT BankID, BankName, BankCode FROM ref_banks ORDER BY BankName ASC";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchAllData();
    }
    
    function getlistofterminals($zsiteID) {
        if($zsiteID > 0) {
            $stmt = "Select SiteID, TerminalID, TerminalCode, TerminalName from terminals where SiteID = '".$zsiteID."' AND Status = 1 AND isVIP = 0 ORDER BY TerminalID ASC";
        } else {
            $stmt = "Select SiteID, TerminalID, TerminalCode, TerminalName from terminals WHERE Status = 1 AND isVIP = 0 ORDER BY TerminalID ASC";
        }
          $this->prepare($stmt);
          $this->execute();
        return $this->fetchAllData();
     }
 }
?>
