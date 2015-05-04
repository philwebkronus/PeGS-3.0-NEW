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
        
//Preparation for bancnet transactions
//        $query2 = "SELECT tr.StackerSummaryID, tr.SiteID, tr.CreatedByAID, a.UserName, ad.Name,
//
//                                -- TOTAL DEPOSIT --
//                                SUM(CASE tr.TransactionType
//                                  WHEN 'D' THEN tr.Amount
//                                  ELSE 0
//                                END) As TotalDeposit,
//
//                                -- TOTAL RELOAD --
//                                SUM(CASE tr.TransactionType
//                                  WHEN 'R' THEN tr.Amount
//                                  ELSE 0 -- Not Reload
//                                END) As TotalReload,
//
//                                 -- TOTAL REDEMPTION --
//                                CASE tr.TransactionType
//                                  WHEN 'W' THEN SUM(tr.Amount)
//                                  ELSE 0
//                                END As TotalRedemption,
//
//                                -- DEPOSIT CASH --
//                                SUM(CASE tr.TransactionType
//                                   WHEN 'D' THEN
//                                     CASE tr.PaymentType
//                                       WHEN 2 THEN 0 -- Coupon
//                                       ELSE -- Not Coupon
//                                         CASE IFNULL(tr.StackerSummaryID, '')
//                                           WHEN '' THEN 
//                                                CASE (SELECT COUNT(*) as IsBancnet FROM npos.banktransactionlogs btl
//                                                            INNER JOIN npos.transactionrequestlogs trl ON btl.TransactionRequestLogID = trl.TransactionRequestLogID
//			WHERE trl.TransactionReferenceID = tr.TransactionReferenceID)
//                                                WHEN 0 THEN tr.Amount -- Cash
//                                                ELSE 0 END 
//                                           ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
//                                             (SELECT IFNULL(SUM(Amount), 0)
//                                             FROM stackermanagement.stackerdetails sdtls
//                                             WHERE sdtls.stackersummaryID = tr.StackerSummaryID
//                                                   AND sdtls.TransactionType = 1
//                                                   AND sdtls.PaymentType = 0)  -- Deposit, Cash
//                                         END
//                                    END
//                                   ELSE 0 -- Not Deposit
//                                END) As DepositCash,
//
//                                -- RELOAD CASH --
//                                SUM(CASE tr.TransactionType
//                                   WHEN 'R' THEN
//                                     CASE tr.PaymentType
//                                       WHEN 2 THEN 0 -- Coupon
//                                       ELSE -- Not Coupon
//                                         CASE IFNULL(tr.StackerSummaryID, '')
//                                           WHEN '' THEN 
//                                                CASE (SELECT COUNT(*) as IsBancnet FROM npos.banktransactionlogs btl
//                                                            INNER JOIN npos.transactionrequestlogs trl ON btl.TransactionRequestLogID = trl.TransactionRequestLogID
//			WHERE trl.TransactionReferenceID = tr.TransactionReferenceID)
//                                                WHEN 0 THEN tr.Amount -- Reload, Cash
//                                                ELSE 0 END 
//                                           ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
//                                              (SELECT IFNULL(SUM(Amount), 0)
//                                --              (SELECT IFNULL(Amount, 0)
//                                             FROM stackermanagement.stackerdetails sdtls
//                                             WHERE sdtls.stackersummaryID = tr.StackerSummaryID
//                                                   AND tr.TransactionDetailsID = sdtls.TransactionDetailsID
//                                                   AND sdtls.TransactionType = 2
//                                                   AND sdtls.PaymentType = 0)  -- Reload, Cash
//                                         END
//                                     END
//                                   ELSE 0 -- Not Reload
//                                END) As ReloadCash,
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
//                                tr.DateCreated
//                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
//                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
//                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
//                                INNER JOIN npos.accountdetails ad ON ad.AID = tr.CreatedByAID
//                                INNER JOIN npos.sites s ON tr.SiteID = s.SiteID
//                                WHERE tr.SiteID IN (".$zsiteID.")
//                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
//                                  AND tr.Status IN(1,4) AND a.AccountTypeID NOT IN (17)
//                                GROUP By tr.TransactionType, tr.TransactionSummaryID
//                                ORDER BY tr.TerminalID"; 
        
        
        $query2 = "SELECT tr.StackerSummaryID, tr.SiteID, tr.CreatedByAID, a.UserName, ad.Name,

                                -- TOTAL DEPOSIT --
                                SUM(CASE tr.TransactionType
                                  WHEN 'D' THEN tr.Amount
                                  ELSE 0
                                END) As TotalDeposit,

                                -- TOTAL RELOAD --
                                SUM(CASE tr.TransactionType
                                  WHEN 'R' THEN tr.Amount
                                  ELSE 0 -- Not Reload
                                END) As TotalReload,

                                 -- TOTAL REDEMPTION --
                                SUM(CASE tr.TransactionType
                                  WHEN 'W' THEN tr.Amount
                                  ELSE 0
                                END) As TotalRedemption,

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

                                -- RELOAD CASH --
                                SUM(CASE tr.TransactionType
                                   WHEN 'R' THEN
                                     CASE tr.PaymentType
                                       WHEN 2 THEN 0 -- Coupon
                                       ELSE -- Not Coupon
                                         CASE IFNULL(tr.StackerSummaryID, '')
                                           WHEN '' THEN tr.Amount -- Reload, Cash
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
                                
                                -- REDEMPTION CASHIER --
                                SUM(CASE tr.TransactionType
                                  WHEN 'W' THEN
                                        CASE a.AccountTypeID
                                          WHEN 4 THEN tr.Amount -- Cashier
                                          ELSE 0
                                        END -- Genesis
                                  ELSE 0 --  Not Redemption
                                END) As RedemptionCashier,

                                tr.DateCreated
                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                INNER JOIN npos.accountdetails ad ON ad.AID = tr.CreatedByAID
                                INNER JOIN npos.sites s ON tr.SiteID = s.SiteID
                                WHERE tr.SiteID IN (".$zsiteID.")
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4) AND a.AccountTypeID NOT IN (17)
                                GROUP By tr.CreatedByAID ORDER BY tr.CreatedByAID"; 
        
        $query3 = "SELECT tr.SiteID, IFNULL(SUM(stckr.Withdrawal), 0) AS PrintedTickets FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
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
        
        $query4 = "SELECT tckt.EncashedByAID as AID, tckt.SiteID, IFNULL(SUM(tckt.Amount), 0) AS EncashedTickets FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                            WHERE tckt.DateEncashed >= ? AND tckt.DateEncashed < ?
                            AND tckt.SiteID IN (".$zsiteID.")
                            GROUP BY AID, tckt.SiteID";

        $query5 = "SELECT et.SiteID, et.CreatedByAID, ad.Name,
                                -- Total e-wallet Deposits
                                SUM(CASE et.TransType
                                        WHEN 'D' THEN et.Amount -- if deposit
                                        ELSE 0 -- if not deposit
                                END) AS EwalletDeposits,

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
                                END) AS EwalletBancnetDeposit

                            FROM npos.ewallettrans et
                            LEFT JOIN npos.accountdetails ad ON et.CreatedByAID = ad.AID
                            WHERE et.StartDate >= ? AND et.StartDate < ?
                            AND et.SiteID IN (".$zsiteID.") AND et.Status IN (1,3)
                            GROUP BY et.CreatedByAID";
        
        $this->prepare($query2);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows1 = $this->fetchAllData();
        $qr1 = array();
        foreach($rows1 as $row1) {
            $qr1[] = array('SiteID'=>$row1['SiteID'],'DateCreated'=>$row1['DateCreated'], 'StackerSummaryID' => $row1['StackerSummaryID'],
                'CreatedByAID' => $row1['CreatedByAID'],'Deposits'=>$row1['TotalDeposit'], 'Reloads'=>$row1['TotalReload'],  'Redemptions'=>$row1['TotalRedemption'], 
                'UserName'=>$row1['UserName'],'Name'=>$row1['Name'], 'PrintedTickets' => '0.00', 'EncashedTickets' => '0.00',
                'LoadCash' => (float)$row1['DepositCash'] + (float)$row1['ReloadCash'], 'RedemptionCashier' => $row1['RedemptionCashier'],
                'EwalletRedemption'=>'0.00'
            );
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
                if($row4["SiteID"] == $value2["SiteID"] && $row4["AID"] == $value2["CreatedByAID"]){
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
            $arrnewrec1 = array('SiteID'=>$row5['SiteID'],'DateCreated'=>'', 'StackerSummaryID' => NULL,
                'CreatedByAID' => $row5['CreatedByAID'],'Deposits'=>$row5['EwalletDeposits'], 'Reloads'=>'',  'Redemptions'=>$row5['EwalletRedemption'], 
                'UserName'=>'','Name'=>$row5['Name'], 'PrintedTickets' => '0.00', 'EncashedTickets' => '0.00', 'LoadCash' => $row5['EwalletCashDeposit'] + $row5['EwalletBancnetDeposit'],
                'RedemptionCashier' => '0.00', 'EwalletRedemption'=>$row5['EwalletRedemption']);
            array_push($qr1, $arrnewrec1);
        }
        
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

        $query2 = "SELECT tr.SiteID,

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
                                           WHEN '' THEN 
                                                CASE (SELECT COUNT(*) as IsBancnet FROM npos.banktransactionlogs btl
                                                            INNER JOIN npos.transactionrequestlogs trl ON btl.TransactionRequestLogID = trl.TransactionRequestLogID
			WHERE trl.TransactionReferenceID = tr.TransactionReferenceID)
                                                WHEN 0 THEN tr.Amount -- Cash
                                                ELSE 0 END 
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
                                
                                -- DEPOSIT Bancnet --
                                SUM(CASE tr.TransactionType
                                   WHEN 'D' THEN
                                     CASE tr.PaymentType
                                       WHEN 2 THEN 0 -- Coupon
                                       ELSE -- Not Coupon
                                         CASE IFNULL(tr.StackerSummaryID, '')
                                            WHEN '' THEN 
                                                CASE (SELECT COUNT(*) as IsBancnet FROM npos.banktransactionlogs btl
                                                            INNER JOIN npos.transactionrequestlogs trl ON btl.TransactionRequestLogID = trl.TransactionRequestLogID
			WHERE trl.TransactionReferenceID = tr.TransactionReferenceID)
                                                WHEN 1 THEN tr.Amount -- Bancnet
                                                ELSE 0 END 
                                            ELSE 0 END
                                    END
                                   ELSE 0 -- Not Deposit
                                END) As DepositBancnet,

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
                                           WHEN '' THEN 
                                                CASE (SELECT COUNT(*) as IsBancnet FROM npos.banktransactionlogs btl
                                                            INNER JOIN npos.transactionrequestlogs trl ON btl.TransactionRequestLogID = trl.TransactionRequestLogID
			WHERE trl.TransactionReferenceID = tr.TransactionReferenceID)
                                                WHEN 0 THEN tr.Amount -- Reload, Cash
                                                ELSE 0 END 
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

                                -- RELOAD BANCNET --
                                SUM(CASE tr.TransactionType
                                   WHEN 'R' THEN
                                     CASE tr.PaymentType
                                       WHEN 2 THEN 0 -- Coupon
                                       ELSE -- Not Coupon
                                         CASE IFNULL(tr.StackerSummaryID, '')
                                            WHEN '' THEN 
                                                CASE (SELECT COUNT(*) as IsBancnet FROM npos.banktransactionlogs btl
                                                            INNER JOIN npos.transactionrequestlogs trl ON btl.TransactionRequestLogID = trl.TransactionRequestLogID
			WHERE trl.TransactionReferenceID = tr.TransactionReferenceID)
                                                WHEN 1 THEN tr.Amount -- Reload, Bancnet
                                                ELSE 0 END 
                                            ELSE 0 END
                                     END
                                   ELSE 0 -- Not Reload
                                END) As ReloadBancnet,

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

                                tr.DateCreated
                                FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                INNER JOIN npos.sites s ON tr.SiteID = s.SiteID
                                WHERE tr.SiteID IN (".$zsiteID.")
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4) AND a.AccountTypeID NOT IN (17)
                                GROUP By tr.CreatedByAID ORDER BY tr.CreatedByAID";
        
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
        
        $query5 ="SELECT et.SiteID, et.CreatedByAID, ad.Name,
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
                                END) AS EwalletVoucherDeposit,
                                
                                SUM(CASE et.TransType
                                        WHEN 'W' THEN et.Amount -- if withdrawal
                                        ELSE 0 -- if not withdrawal
                                END) AS EwalletWithdrawal

                            FROM npos.ewallettrans et
                            LEFT JOIN npos.accountdetails ad ON et.CreatedByAID = ad.AID
                            WHERE et.StartDate >= ? AND et.StartDate < ?
                            AND et.SiteID IN (".$zsiteID.") AND et.Status IN (1,3)
                            GROUP BY et.CreatedByAID";
        
        $this->prepare($query1);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute(); 
        $rows1 = $this->fetchAllData();        
        $qr1 = array();
        foreach($rows1 as $row1) {
            $qr1[] = array('SiteID'=>$row1['SiteID'], 'ManualRedemption' => $row1['ManualRedemption'],'PrintedTickets' => 0.00, 'EncashedTickets' => 0.00,
                                        'LoadCash' => 0.00, 'LoadTicket' => 0.00, 'LoadCoupon' => 0.00, 'RedemptionCashier' => 0.00, 'Bancnet' => 0.00, 'EwalletWithdrawal' => 0.00);
        }
        
        $this->prepare($query2);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows2 = $this->fetchAllData();
        foreach($rows2 as $row2) {
            foreach ($qr1 as $keys => $value2) {
                if($row2["SiteID"] == $value2["SiteID"]){
                    if($row2["DepositCash"] != '0.00'){
                        $qr1[$keys]["LoadCash"] +=  (float)$row2["DepositCash"];}
                    if($row2["ReloadCash"] != '0.00'){
                        $qr1[$keys]["LoadCash"] += (float)$row2["ReloadCash"];}
                    if($row2["DepositTicket"] != '0.00'){
                        $qr1[$keys]["LoadTicket"] += (float)$row2["DepositTicket"];}
                    if($row2["ReloadTicket"] != '0.00'){
                        $qr1[$keys]["LoadTicket"] += (float)$row2["ReloadTicket"];}
                    if($row2["DepositCoupon"] != '0.00'){
                        $qr1[$keys]["LoadCoupon"] += (float)$row2["DepositCoupon"];}
                    if($row2["ReloadCoupon"] != '0.00'){
                        $qr1[$keys]["LoadCoupon"] += (float)$row2["ReloadCoupon"];}
                    if($row2["DepositBancnet"] != '0.00'){
                        $qr1[$keys]["Bancnet"] += (float)$row2["DepositBancnet"];}
                    if($row2["ReloadBancnet"] != '0.00'){
                        $qr1[$keys]["Bancnet"] += (float)$row2["ReloadBancnet"];}
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
                        $qr1[$keys]["PrintedTickets"] += (float)$row3["PrintedTickets"];
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
                        $qr1[$keys]["EncashedTickets"] += (float)$row4["EncashedTickets"];
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
                    if($row5["EwalletBancnetDeposit"] != '0.00')
                        $qr1[$keys]["Bancnet"] += (float)$row5["EwalletBancnetDeposit"];
                    if($row5["EwalletCashDeposit"] != '0.00')
                        $qr1[$keys]["LoadCash"] += (float)$row5["EwalletCashDeposit"];
                    if($row5["EwalletVoucherDeposit"] != '0.00')
                        $qr1[$keys]["LoadCoupon"] += (float)$row5["EwalletVoucherDeposit"];
                    if($row5["EwalletWithdrawal"] != '0.00')
                        $qr1[$keys]["EwalletWithdrawal"] += (float)$row5["EwalletWithdrawal"];
                }
            } 
        }
        
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
