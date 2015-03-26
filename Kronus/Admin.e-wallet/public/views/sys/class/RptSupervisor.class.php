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
        $query1 = "SELECT tr.StackerSummaryID, tr.DateCreated, tr.TerminalID,tr.SiteID, tr.CreatedByAID, 
                     tr.TransactionType, tr.Amount,a.UserName, ad.Name FROM npos.transactiondetails tr 
                     INNER JOIN npos.accounts a ON a.AID = tr.CreatedByAID
                     INNER JOIN npos.accountdetails ad ON ad.AID = tr.CreatedByAID
                     WHERE tr.SiteID IN (".$zsiteID.") AND 
                     tr.DateCreated >= ? AND tr.DateCreated < ? AND tr.Status IN (1,4)
                     ORDER BY tr.CreatedByAID ASC";
        
        $query2 = "SELECT 

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

                                ts.DateStarted, ts.DateEnded, tr.SiteID
                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                INNER JOIN npos.sites s ON tr.SiteID = s.SiteID
                                WHERE tr.SiteID IN (".$zsiteID.")
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY s.POSAccountNo"; 
        
        $query3 = "SELECT tr.SiteID, IFNULL(SUM(stckr.Withdrawal), 0) AS PrintedTickets FROM npos.transactiondetails tr  -- Printed Tickets through W
                                INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                WHERE tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                  AND tr.SiteID IN (".$zsiteID.")
                                  AND tr.TransactionType = 'W'
                                  AND tr.StackerSummaryID IS NOT NULL
                                  GROUP BY tr.SiteID";

        $query4 = "SELECT tckt.SiteID, IFNULL(SUM(tckt.Amount), 0) AS EncashedTickets FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                                WHERE tckt.DateEncashed >= ? AND tckt.DateEncashed < ?
                                AND tckt.SiteID IN (".$zsiteID.")
                                GROUP BY tckt.SiteID";
        
        $query5 = "SELECT trl.SiteID, IFNULL(SUM(trl.Amount), 0) AS NonEwalletBancnet FROM npos.transactionrequestlogs trl -- Non-Ewallet Bancnet
                                INNER JOIN npos.banktransactionlogs btl ON trl.TransactionRequestLogID = btl.TransactionRequestLogID
                                WHERE trl.StartDate > = ? AND trl.EndDate < ?
                                AND trl.SiteID IN (".$zsiteID.")
                                AND trl.TransactionType = 'D'
                                GROUP BY trl.SiteID";
        
        $query6 = "SELECT ewt.SiteID, IFNULL(SUM(ewt.Amount), 0) AS EwalletBancnet FROM npos.ewallettrans ewt -- Ewallet Bancnet
                                WHERE ewt.StartDate > = ? AND ewt.EndDate < ?
                                AND ewt.SiteID IN (".$zsiteID.")
                                AND ewt.TransType = 'D'
                                AND ewt.TraceNumber IS NOT NULL AND ewt.ReferenceNumber IS NOT NULL
                                GROUP BY ewt.SiteID";
        
        $this->prepare($query1);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows1 = $this->fetchAllData();
        $qr1 = array();
        foreach($rows1 as $row1) {
            $qr1[] = array('SiteID'=>$row1['SiteID'],'TerminalID'=>$row1['TerminalID'],'DateCreated'=>$row1['DateCreated'], 'StackerSummaryID' => $row1['StackerSummaryID'],
                    'CreatedByAID' => $row1['CreatedByAID'],'TransactionType' => $row1['TransactionType'],'Amount'=>$row1['Amount'], 
                    'UserName'=>$row1['UserName'],'Name'=>$row1['Name'], 'PrintedTickets' => '0.00', 'EncashedTickets' => '0.00',
                    'LoadCash' => '0.00', 'LoadTicket' => '0.00', 'Bancnet' => '0.00'
                );
        }
        
        $this->prepare($query2);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows2 = $this->fetchAllData();
        foreach($rows2 as $row2) {
            foreach ($qr1 as $keys => $value2) {
                if($row2["SiteID"] == $value2["SiteID"]){
                    if($row2["DepositCash"] != '0.00')
                        $qr1[$keys]["LoadCash"] = (float)$qr1[$keys]["LoadCash"] + (float)$row2["DepositCash"];
                    if($row2["ReloadCash"] != '0.00')
                        $qr1[$keys]["LoadCash"] = (float)$qr1[$keys]["LoadCash"] + (float)$row2["ReloadCash"];
                    if($row2["DepositTicket"] != '0.00')
                        $qr1[$keys]["LoadTicket"] = (float)$qr1[$keys]["LoadTicket"] + (float)$row2["DepositTicket"];
                    if($row2["ReloadTicket"] != '0.00')
                        $qr1[$keys]["LoadTicket"] = (float)$qr1[$keys]["LoadTicket"] + (float)$row2["ReloadTicket"];
                    break;
                }
            } 
        }
        
        $this->prepare($query3);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows3 = $this->fetchAllData();
        foreach($rows3 as $row3) {
            foreach ($qr1 as $keys => $value2) {
                if($row3["SiteID"] == $value2["SiteID"]){
                    if($row3["PrintedTickets"] != '0.00')
                        $qr1[$keys]["PrintedTickets"] = (float)$qr1[$keys]["PrintedTickets"] + (float)$row3["PrintedTickets"];
                    break;
                }
            } 
        }
        
        $this->prepare($query4);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows4 = $this->fetchAllData();
        foreach($rows4 as $row4) {
            foreach ($qr1 as $keys => $value2) {
                if($row4["SiteID"] == $value2["SiteID"]){
                    if($row4["EncashedTickets"] != '0.00')
                        $qr1[$keys]["EncashedTickets"] = (float)$qr1[$keys]["EncashedTickets"] + (float)$row4["EncashedTickets"];
                    break;
                }
            } 
        }
        
        $this->prepare($query5);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows5 = $this->fetchAllData();
        foreach($rows5 as $row5) {
            foreach ($qr1 as $keys => $value2) {
                if($row5["SiteID"] == $value2["SiteID"]){
                    if($row5["NonEwalletBancnet"] != '0.00')
                        $qr1[$keys]["Bancnet"] = (float)$qr1[$keys]["Bancnet"] + (float)$row5["NonEwalletBancnet"];
                    break;
                }
            } 
        }
        
        $this->prepare($query6);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows6 = $this->fetchAllData();
        foreach($rows6 as $row6) {
            foreach ($qr1 as $keys => $value2) {
                if($row6["SiteID"] == $value2["SiteID"]){
                    if($row6["EwalletBancnet"] != '0.00')
                        $qr1[$keys]["Bancnet"] = (float)$qr1[$keys]["Bancnet"] + (float)$row6["EwalletBancnet"];
                    break;
                }
            } 
        }
        
        //$qr1[$keys]["Bancnet"] = (float)$qr1[$keys]["EwalletBancnet"] + (float)$qr1[$keys]["NonEwalletBancnet"];
        
        return $qr1;
    }
    
    /**
     * @Description: Get the Details for Supervisor Gross hold
     * @Author: aqdepliyan
     * @DateCreated: 2014-04-29
     */
    function getdetails($zdatefrom, $zdateto, $zsiteID)
    {
        
        $query1 = "SELECT s.SiteID, IFNULL(SUM(mr.ActualAmount), 0) AS ManualRedemption,
                                CASE sd.RegionID WHEN 17 THEN 'Metro Manila' ELSE 'Provincial' END AS Location,
                                sb.MinBalance
                                FROM sites s 
                                LEFT JOIN  sitebalance sb ON s.SiteID = sb.SiteID
                                LEFT JOIN  sitedetails sd ON s.SiteID = sd.SiteID
                                LEFT JOIN manualredemptions mr FORCE INDEX(IX_manualredemptions_TransactionDate) ON s.SiteID = mr.SiteID
                                  AND mr.TransactionDate >= ? AND mr.TransactionDate < ?
                                WHERE s.SiteID NOT IN (1, 235)
                                AND s.SiteID IN (".$zsiteID.")
                                ORDER BY s.SiteCode";
        
//        $query2 = "SELECT 
//
//                                -- DEPOSIT CASH --
//                                CASE tr.TransactionType
//                                  WHEN 'D' THEN
//                                        CASE tr.PaymentType
//                                          WHEN 2 THEN 0 -- Coupon
//                                          ELSE -- Not Coupon
//                                                CASE IFNULL(tr.StackerSummaryID, '')
//                                                  WHEN '' THEN SUM(tr.Amount) -- Cash
//                                                  ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
//                                                        (SELECT IFNULL(SUM(Amount), 0)
//                                                        FROM stackermanagement.stackerdetails sdtls
//                                                        WHERE sdtls.stackersummaryID = tr.StackerSummaryID
//                                                                  AND sdtls.TransactionType = 1
//                                                                  AND sdtls.PaymentType = 0)  -- Deposit, Cash
//                                                END
//                                        END
//                                  ELSE 0 -- Not Deposit
//                                END As DepositCash,
//
//                                -- DEPOSIT TICKET --
//                                CASE tr.TransactionType
//                                  WHEN 'D' THEN
//                                    CASE tr.PaymentType
//                                      WHEN 2 THEN 0 -- Coupon
//                                      ELSE -- Not Coupon
//                                        CASE IFNULL(tr.StackerSummaryID, '')
//                                          WHEN '' THEN 0 -- Cash
//                                          ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
//                                            (SELECT IFNULL(SUM(Amount), 0)
//                                            FROM stackermanagement.stackerdetails sdtls
//                                            WHERE sdtls.stackersummaryID = tr.StackerSummaryID
//                                                  AND sdtls.TransactionType = 1
//                                                  AND sdtls.PaymentType = 2)  -- Deposit, Ticket
//                                        END
//                                    END
//                                  ELSE 0 -- Not Deposit
//                                END As DepositTicket,
//
//                                -- RELOAD CASH --
//                                CASE tr.TransactionType
//                                  WHEN 'R' THEN
//                                        CASE tr.PaymentType
//                                          WHEN 2 THEN 0 -- Coupon
//                                          ELSE -- Not Coupon
//                                                CASE IFNULL(tr.StackerSummaryID, '')
//                                                  WHEN '' THEN SUM(tr.Amount) -- Cash
//                                                  ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
//                                                        (SELECT IFNULL(SUM(Amount), 0)
//                                                        FROM stackermanagement.stackerdetails sdtls
//                                                        WHERE sdtls.stackersummaryID = tr.StackerSummaryID
//                                                                  AND sdtls.TransactionType = 2
//                                                                  AND sdtls.PaymentType = 0)  -- Reload, Cash
//                                                END
//                                        END
//                                  ELSE 0 -- Not Reload
//                                END As ReloadCash,
//                                
//                                -- RELOAD TICKET --
//                                CASE tr.TransactionType
//                                  WHEN 'R' THEN
//                                    CASE tr.PaymentType
//                                      WHEN 2 THEN 0 -- Coupon
//                                      ELSE -- Not Coupon
//                                        CASE IFNULL(tr.StackerSummaryID, '')
//                                          WHEN '' THEN 0 -- Cash
//                                          ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
//                                            (SELECT IFNULL(SUM(Amount), 0)
//                                            FROM stackermanagement.stackerdetails sdtls
//                                            WHERE sdtls.stackersummaryID = tr.StackerSummaryID
//                                                  AND sdtls.TransactionType = 2
//                                                  AND sdtls.PaymentType = 2)  -- Reload, Ticket
//                                        END
//                                    END
//                                  ELSE 0 -- Not Reload
//                                END As ReloadTicket,
//                                
//                                -- REDEMPTION CASHIER --
//                                CASE tr.TransactionType
//                                  WHEN 'W' THEN
//                                        CASE a.AccountTypeID
//                                          WHEN 4 THEN SUM(tr.Amount) -- Cashier
//                                          ELSE 0
//                                        END -- Genesis
//                                  ELSE 0 --  Not Redemption
//                                END As RedemptionCashier,
//
//                                ts.DateStarted, ts.DateEnded, tr.SiteID
//                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
//                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
//                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
//                                INNER JOIN npos.sites s ON tr.SiteID = s.SiteID
//                                WHERE tr.SiteID IN (".$zsiteID.")
//                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
//                                  AND tr.Status IN(1,4)
//                                GROUP By tr.TransactionType, tr.TransactionSummaryID
//                                ORDER BY s.POSAccountNo"; 
        
        $query2 = "SELECT tr.TransactionSummaryID AS TransSummID, SUBSTR(t.TerminalCode,11) AS TerminalCode, tr.TransactionType AS TransType,

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

                                ts.DateStarted, ts.DateEnded, tr.SiteID
                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                WHERE tr.SiteID IN (".$zsiteID.")
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY tr.TerminalID, tr.DateCreated DESC";
        
        $query3 = "SELECT tr.SiteID, IFNULL(SUM(stckr.Withdrawal), 0) AS PrintedTickets FROM npos.transactiondetails tr  -- Printed Tickets through W
                                INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                WHERE tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                  AND tr.SiteID IN (".$zsiteID.")
                                  AND tr.TransactionType = 'W'
                                  AND tr.StackerSummaryID IS NOT NULL
                                  GROUP BY tr.SiteID";

        $query4 = "SELECT tckt.SiteID, IFNULL(SUM(tckt.Amount), 0) AS EncashedTickets FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                                WHERE tckt.DateEncashed >= ? AND tckt.DateEncashed < ?
                                AND tckt.SiteID IN (".$zsiteID.")
                                GROUP BY tckt.SiteID";
        
        $query5 = "SELECT trl.SiteID, IFNULL(SUM(trl.Amount), 0) AS NonEwalletBancnet FROM npos.transactionrequestlogs trl -- Non-Ewallet Bancnet
                                INNER JOIN npos.banktransactionlogs btl ON trl.TransactionRequestLogID = btl.TransactionRequestLogID
                                WHERE trl.StartDate > = ? AND trl.EndDate < ?
                                AND trl.SiteID IN (".$zsiteID.")
                                AND trl.TransactionType = 'D'
                                GROUP BY trl.SiteID";
        
        $query6 = "SELECT ewt.SiteID, IFNULL(SUM(ewt.Amount), 0) AS EwalletBancnet FROM npos.ewallettrans ewt -- Ewallet Bancnet
                                WHERE ewt.StartDate > = ? AND ewt.EndDate < ?
                                AND ewt.SiteID IN (".$zsiteID.")
                                AND ewt.TransType = 'D'
                                AND ewt.TraceNumber IS NOT NULL AND ewt.ReferenceNumber IS NOT NULL
                                GROUP BY ewt.SiteID";
        
        $this->prepare($query1);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute(); 
        $rows1 = $this->fetchAllData();        
        $qr1 = array();
        foreach($rows1 as $row1) {
            $qr1[] = array('SiteID'=>$row1['SiteID'], 'ManualRedemption' => $row1['ManualRedemption'],'PrintedTickets' => '0.00', 'EncashedTickets' => '0.00',
                                        'LoadCash' => '0.00', 'LoadTicket' => '0.00', 'LoadCoupon' => '0.00', 'RedemptionCashier' => '0.00', 'Bancnet' => '0.00');
        }
        
        $this->prepare($query2);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows2 = $this->fetchAllData();
        foreach($rows2 as $row2) {
            foreach ($qr1 as $keys => $value2) {
                if($row2["SiteID"] == $value2["SiteID"]){
                    if($row2["DepositCash"] != '0.00')
                        $qr1[$keys]["LoadCash"] = (float)$qr1[$keys]["LoadCash"] + (float)$row2["DepositCash"];
                    if($row2["ReloadCash"] != '0.00')
                        $qr1[$keys]["LoadCash"] = (float)$qr1[$keys]["LoadCash"] + (float)$row2["ReloadCash"];
                    if($row2["DepositTicket"] != '0.00')
                        $qr1[$keys]["LoadTicket"] = (float)$qr1[$keys]["LoadTicket"] + (float)$row2["DepositTicket"];
                    if($row2["ReloadTicket"] != '0.00')
                        $qr1[$keys]["LoadTicket"] = (float)$qr1[$keys]["LoadTicket"] + (float)$row2["ReloadTicket"];
                    if($row2["DepositCoupon"] != '0.00')
                        $qr1[$keys]["LoadCoupon"] = (float)$qr1[$keys]["LoadCoupon"] + (float)$row2["DepositCoupon"];
                    if($row2["ReloadCoupon"] != '0.00')
                        $qr1[$keys]["LoadCoupon"] = (float)$qr1[$keys]["LoadCoupon"] + (float)$row2["ReloadCoupon"];
                    if($row2["RedemptionCashier"] != '0.00')
                        $qr1[$keys]["RedemptionCashier"] = (float)$qr1[$keys]["RedemptionCashier"] + (float)$row2["RedemptionCashier"];
                    break;
                }
            } 
        }
        
        $this->prepare($query3);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows3 = $this->fetchAllData();
        foreach($rows3 as $row3) {
            foreach ($qr1 as $keys => $value2) {
                if($row3["SiteID"] == $value2["SiteID"]){
                    if($row3["PrintedTickets"] != '0.00')
                        $qr1[$keys]["PrintedTickets"] = (float)$qr1[$keys]["PrintedTickets"] + (float)$row3["PrintedTickets"];
                    break;
                }
            } 
        }
        
        $this->prepare($query4);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows4 = $this->fetchAllData();
        foreach($rows4 as $row4) {
            foreach ($qr1 as $keys => $value2) {
                if($row4["SiteID"] == $value2["SiteID"]){
                    if($row4["EncashedTickets"] != '0.00')
                        $qr1[$keys]["EncashedTickets"] = (float)$qr1[$keys]["EncashedTickets"] + (float)$row4["EncashedTickets"];
                    break;
                }
            } 
        }
        
        $this->prepare($query5);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows5 = $this->fetchAllData();
        foreach($rows5 as $row5) {
            foreach ($qr1 as $keys => $value2) {
                if($row5["SiteID"] == $value2["SiteID"]){
                    if($row5["NonEwalletBancnet"] != '0.00')
                        $qr1[$keys]["Bancnet"] = (float)$qr1[$keys]["Bancnet"] + (float)$row5["NonEwalletBancnet"];
                    break;
                }
            } 
        }
        
        $this->prepare($query6);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows6 = $this->fetchAllData();
        foreach($rows6 as $row6) {
            foreach ($qr1 as $keys => $value2) {
                if($row6["SiteID"] == $value2["SiteID"]){
                    if($row6["EwalletBancnet"] != '0.00')
                        $qr1[$keys]["Bancnet"] = (float)$qr1[$keys]["Bancnet"] + (float)$row6["EwalletBancnet"];
                    break;
                }
            } 
        }
        
        //$qr1[$keys]["Bancnet"] = (float)$qr1[$keys]["EwalletBancnet"] + (float)$qr1[$keys]["NonEwalletBancnet"];
        
        return $qr1;
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
