<?php

/**
 * Date Created 11 7, 11 3:55:18 PM <pre />
 * Description of TransactionSummaryModel
 * @author Bryan Salazar
 */
class TransactionSummaryModel extends MI_Model{
    
    public function insert($site_id,$terminal_id,$amount,$acctid) {
        $sql = 'INSERT INTO transactionsummary (SiteID, TerminalID, Deposit, DateStarted, DateEnded, CreatedByAID) VALUES ' . 
                '(:site_id, :terminal_id, :amount, now(6), \'0\', :acctid)';
        $param = array(':site_id'=>$site_id,':terminal_id'=>$terminal_id,':amount'=>$amount,':acctid'=>$acctid);
        return $this->exec($sql,$param);
    }
    
    /**
     * Description: get last
     * @param type $site_id
     * @param type $terminal_id
     * @return type 
     */
    public function getTransactionSummaryDetail($site_id,$terminal_id) {
        $sql = 'SELECT TransactionsSummaryID, Reload, Withdrawal FROM transactionsummary WHERE SiteID = :site_id AND TerminalID = :terminal_id AND DateEnded = \'0\' ORDER BY TransactionsSummaryID DESC LIMIT 1';
        $param = array(':site_id'=>$site_id,':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        return $this->find();
    }
    
    public function updateRedeem($trans_summary_id,$amount) {
        $sql = 'UPDATE transactionsummary SET Withdrawal = :amount,DateEnded = now(6)  WHERE TransactionsSummaryID = :trans_summary_id';
        $param = array(':amount'=>$amount,':trans_summary_id'=>$trans_summary_id);
        return $this->exec($sql,$param);
    }
    
    public function updateReload($trans_summary_id,$amount) {
        $sql = 'UPDATE transactionsummary SET Reload = :amount WHERE TransactionsSummaryID = :trans_summary_id';
        $param = array(':amount'=>$amount,':trans_summary_id'=>$trans_summary_id);
        return $this->exec($sql,$param);
    }
    
    public function getLastTransSummaryId($terminal_id,$site_id) {
        $sql = 'SELECT TransactionsSummaryID FROM transactionsummary WHERE SiteID = :site_id AND TerminalID = :terminal_id ORDER BY DateStarted DESC LIMIT 1';
        $param = array(':terminal_id'=>$terminal_id,':site_id'=>$site_id);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['TransactionsSummaryID'];
    }
    
    /************************** TRANSACTION HISTORY ***************************/
    // Total row count with no limit
    public function getCountTransSummary($date,$enddate,$site_id) {
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT COUNT(*) AS cnt FROM (select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded, tr.TerminalID,tr.SiteID, " . 
                 "if(ts.DateStarted < '$date $cutoff_time', ts.Deposit = 0,ts.Deposit) as Deposit," . 
                 "ts.Reload,ts.Withdrawal from transactiondetails tr left join transactionsummary ts " . 
                 "on ts.TransactionsSummaryID = tr.TransactionSummaryID inner join terminals t on t.TerminalID = tr.TerminalID " . 
                 "where tr.SiteID = :site_id AND tr.DateCreated >= :start_date and tr.DateCreated < :end_date AND tr.Status IN (1,4) " . 
                 "group by ts.TransactionsSummaryID order by tr.TerminalID,tr.DateCreated) as total";
        
        $param = array(
                    ':site_id'=>$site_id,
                    ':start_date'=>$date . ' ' . $cutoff_time,
                    ':end_date'=>$enddate . ' ' . $cutoff_time,
                );
        
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['cnt'];
    }
    
    public function getAllTransactionSummary($site_id,$site_code,$date,$enddate) {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, " . 
                 "SUBSTR(t.TerminalCode,$len) as TerminalCode,if(ts.DateStarted < '$date $cutoff_time', ts.Deposit = 0,ts.Deposit) as Deposit," . 
                 "ts.Reload,ts.Withdrawal,tr.DateCreated from transactiondetails tr left join transactionsummary ts " . 
                 "on ts.TransactionsSummaryID = tr.TransactionSummaryID inner join terminals t on t.TerminalID = tr.TerminalID " . 
                 "where tr.SiteID = :site_id AND tr.DateCreated >= :start_date and tr.DateCreated < :end_date AND tr.Status IN (1,4) " . 
                 "group by ts.TransactionsSummaryID order by tr.TerminalID,tr.DateCreated Desc";
        
        $param = array(
                    ':site_id'=>$site_id,
                    ':start_date'=>$date . ' ' . $cutoff_time,
                    ':end_date'=>$enddate . ' ' . $cutoff_time,
                );
        $this->exec($sql, $param);
        return $this->findAll();
    }
    
    public function getTransSummaryPaging($site_id,$site_code,$date,$enddate,$start,$limit) {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT tr.TransactionSummaryID, SUBSTR(t.TerminalCode,$len) AS TerminalCode, tr.TransactionType, t.TerminalType,
 
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
            ts.DateStarted, ts.DateEnded

            FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
            WHERE tr.SiteID = :site_id
              AND tr.DateCreated >= :start_date AND tr.DateCreated < :end_date
              AND tr.Status IN(1,4)
            GROUP By tr.TransactionType, tr.TransactionSummaryID
            ORDER BY tr.TerminalID, tr.DateCreated DESC;";
        $param = array(
                ':site_id'=>$site_id,
                ':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,
            );
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) {
            if($value['TerminalType'] == 1){
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else{
                $terminalCode = $value['TerminalCode']; 
            }
            if(!isset($new_result[$value['TransactionSummaryID']])) {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'TerminalCode'=>$terminalCode,
                    'DCash'=>'0.00',
                    'DTicket'=>'0.00',
                    'DCoupon'=>'0.00',
                    'RCash'=>'0.00',
                    'RTicket'=>'0.00',
                    'RCoupon'=>'0.00',
                    'WCashier'=>'0.00',
                    'WGenesis'=>'0.00'
                );
            }
            $merge_array = array();
            switch ($value['TransactionType']) {
                case 'W':
                    if($value['RedemptionCashier'] > 0){
                        $merge_array = array('WCashier'=>$value['RedemptionCashier']);
                    }
                    
                    if($value['RedemptionGenesis'] > 0){
                        $merge_array = array('WGenesis'=>$value['RedemptionGenesis']);
                    }
                    break;
                case 'D':
                        $merge_array = array('DCash'=>$value['DepositCash'], 'DTicket'=>$value['DepositTicket'],'DCoupon'=>$value['DepositCoupon'] );
                    
                    break;
                case 'R':
                        $merge_array = array('RCash'=>$value['ReloadCash'],'RTicket'=>$value['ReloadTicket'],'RCoupon'=>$value['ReloadCoupon']);
                    
                    break;
            }
            $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
            
            
           
        }
        $res = array();
        foreach($new_result as $value) {
            $res[] = $value;
        }
        //$res = array_slice($res, $start, $limit);
        return $res;
        
        
//        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, " . 
//                 "SUBSTR(t.TerminalCode,$len) as TerminalCode,if(ts.DateStarted < '$date $cutoff_time', ts.Deposit = 0,ts.Deposit) as Deposit," . 
//                 "ts.Reload,ts.Withdrawal,tr.DateCreated from transactiondetails tr left join transactionsummary ts " . 
//                 "on ts.TransactionsSummaryID = tr.TransactionSummaryID inner join terminals t on t.TerminalID = tr.TerminalID " . 
//                 "where tr.SiteID = :site_id AND tr.DateCreated > :start_date and tr.DateCreated <= :end_date " . 
//                 "group by ts.TransactionsSummaryID order by tr.TerminalID,tr.DateCreated Desc LIMIT :start, :limit";
//        
//        $param = array(
//                    ':site_id'=>$site_id,
//                    ':start_date'=>$date . ' ' . $cutoff_time,
//                    ':end_date'=>$enddate . ' ' . $cutoff_time,
//                    ':start'=>$start,
//                    ':limit'=>$limit,
//                );
//        $this->exec($sql, $param);
//        $result = $this->findAll();
//        MI_Logger::log($result, E_ERROR);
//        
//        return $result;
    }
    
    
    public function getTransSummaryTotalsPerCG($site_id,$site_code,$date,$enddate,$start,$limit) {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.StackerSummaryID, tr.PaymentType, t.TerminalType,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  from transactiondetails  tr 
            inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
            inner join terminals t on t.TerminalID = tr.TerminalID  where tr.SiteID = :site_id AND 
            tr.DateCreated >= :start_date and tr.DateCreated < :end_date and tr.Status IN(1,4) 
            group by tr.TransactionType,tr.TransactionSummaryID,tr.PaymentType order by tr.TerminalID,tr.DateCreated Desc";
        $param = array(
                ':site_id'=>$site_id,
                ':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,
            );
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) {
            if($value['TerminalType'] == 1){
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else{
                $terminalCode = $value['TerminalCode']; 
            }
            if(!isset($new_result[$value['TransactionSummaryID']])) {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'DateCreated'=>$value['DateCreated'],
                    'TerminalID'=>$value['TerminalID'],
                    'SiteID'=>$value['SiteID'],
                    'TerminalCode'=>$terminalCode,
                    'LoyaltyCard'=>$value['Option1'],
                    'RegDCash'=>'0.00',
                    'RegDTicket'=>'0.00',
                    'RegDCoupon'=>'0.00',
                    'RegRCash'=>'0.00',
                    'RegRTicket'=>'0.00',
                    'RegRCoupon'=>'0.00',
                    'GenDCash'=>'0.00',
                    'GenDTicket'=>'0.00',
                    'GenDCoupon'=>'0.00',
                    'GenRCash'=>'0.00',
                    'GenRTicket'=>'0.00',
                    'GenRCoupon'=>'0.00',
                    'WCashier'=>'0.00',
                    'WGenesis'=>'0.00'
                );
            }
            $merge_array = array();
            
            if($value['StackerSummaryID'] == null || $value['StackerSummaryID'] == ''){
                
                
                if($value['PaymentType'] == 1){
                    switch ($value['TransactionType']) {
                        case 'W':
                            $merge_array = array('WCashier'=>$value['amount']);
                            break;
                        case 'D':
                            $merge_array = array('RegDCash'=>$value['amount']);
                            break;
                        case 'R':
                            $merge_array = array('RegRCash'=>$value['amount']);
                            break;
                    }
                }
                else{
                    switch ($value['TransactionType']) {
                        case 'W':
                            $merge_array = array('WCashier'=>$value['amount']);
                            break;
                        case 'D':
                            $merge_array = array('RegDCoupon'=>$value['amount']);
                            break;
                        case 'R':
                            $merge_array = array('RegRCoupon'=>$value['amount']);
                            break;
                    }
                }
               
                $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
                
            }
            else{
                
                
                if($value['TransactionType'] == 'W'){
                    $merge_arrays = array('WGenesis'=>$value['amount']);
                    
                    $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_arrays);
                }
                elseif($value['TransactionType'] == 'D'){
                    $sql2 = "SELECT DISTINCT(PaymentType) FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype;";
                    $param2 = array(
                            ':stackersummaryid'=>$value['StackerSummaryID'],
                            ':transtype'=>1,
                        );
                    $this->exec2($sql2,$param2);
                    $result2 = $this->findAll2();
                    
                    foreach ($result2 as $value2) {
                        $pymnttype = $value2['PaymentType'];
                        if($pymnttype == '0'){
                            $sql3 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param3 = array(
                                    ':stackersummaryid'=>$value['StackerSummaryID'],
                                    ':transtype'=>1,
                                    ':paymenttype'=>0
                                );
                            $this->exec2($sql3,$param3);
                            $result3 = $this->findAll2();
                            
                            foreach ($result3 as $value3) {
                                $amt = $value3['Amount'];
                            }
                            
                            
                            $merge_arrays = array('GenDCash'=>$amt);
                            
                        }
                        else{
                            $sql4 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param4 = array(
                                    ':stackersummaryid'=>$value['StackerSummaryID'],
                                    ':transtype'=>1,
                                    ':paymenttype'=>2
                                );
                            $this->exec2($sql4,$param4);
                            $result4 = $this->findAll2();
                            
                            foreach ($result4 as $value4) {
                                $amt1 = $value4['Amount'];
                            }
                            
                            $merge_arrays = array('GenDTicket'=>$amt1);
                        }
                        
                        $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_arrays); 
                        
                    }
                    
                }
                elseif($value['TransactionType'] == 'R'){
                    $sql2 = "SELECT DISTINCT(PaymentType) FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype;";
                    $param2 = array(
                            ':stackersummaryid'=>$value['StackerSummaryID'],
                            ':transtype'=>2,
                        );
                    $this->exec2($sql2,$param2);
                    $result2 = $this->findAll2();
                    
                    foreach ($result2 as $value2) {
                        if($value2['PaymentType'] == '0'){
                            $sql3 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param3 = array(
                                    ':stackersummaryid'=>$value['StackerSummaryID'],
                                    ':transtype'=>2,
                                    ':paymenttype'=>0
                                );
                            $this->exec2($sql3,$param3);
                            $result3 = $this->findAll2();
                            
                            foreach ($result3 as $value3) {
                                $amt2 = $value3['Amount'];
                            }
                            
                            $merge_arrays = array('GenRCash'=>$amt2);
                        }
                        else{
                            $sql4 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param4 = array(
                                    ':stackersummaryid'=>$value['StackerSummaryID'],
                                    ':transtype'=>2,
                                    ':paymenttype'=>2
                                );
                            $this->exec2($sql4,$param4);
                            $result4 = $this->findAll2();
                            
                            foreach ($result4 as $value4) {
                                $amt3 = $value4['Amount'];
                            }
                            
                            $merge_arrays = array('GenRTicket'=>$amt3);
                        }
                        
                        $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_arrays); 
                    }
                }
               
            }
        }
        
        $res = array();
        foreach($new_result as $value) {
            $res[] = $value;
        }
        
        return $res;
    }
    
    
    public function getTicketList($site_id, $date, $end_date){

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT (SELECT IFNULL(SUM(stckr.Withdrawal), 0) FROM npos.transactiondetails tr  -- Printed Tickets through W
            INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
            LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
            WHERE tr.SiteID = :site_id0
              AND tr.DateCreated >= :start_date1 AND tr.DateCreated < :end_date1
              AND tr.Status IN(1,4)
              AND tr.TransactionType = 'W'
              AND tr.StackerSummaryID IS NOT NULL
              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID = 15
              AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID = :site_id1))) AS PrintedRedemptionTickets,


            (SELECT IFNULL(SUM(Amount), 0) AS Amount FROM
              (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode FROM npos.transactiondetails tr  -- Printed Tickets through W
              INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
              INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
              INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
              LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
              WHERE tr.SiteID = :site_id2
                AND tr.DateCreated >= :start_date2 AND tr.DateCreated < :end_date2
                AND tr.Status IN(1,4)
                AND tr.TransactionType = 'W'
                AND tr.StackerSummaryID IS NOT NULL
                AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID = 15
                AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID = :site_id3))
              UNION ALL
              (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
              WHERE stckr.Status IN (1, 2)
              AND stckr.DateCancelledOn >= :start_date3 AND stckr.DateCancelledOn < :end_date3
              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4, 15)
              AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID = :site_id4))
               )
            )AS UnusedTicketsTbl
            WHERE TicketCode NOT IN (SELECT tckt.TicketCode FROM vouchermanagement.tickets tckt -- Less: Encashed Tickets
                                            WHERE tckt.DateEncashed >= :start_date4 AND tckt.DateEncashed < :end_date4
                                              AND tckt.EncashedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID = 4
                                              AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID = :site_id5)))
                AND TicketCode NOT IN  -- Less: Used in Deposit and Reload Genesis Transactions
                  (SELECT stckrdtls.VoucherCode
                  FROM stackermanagement.stackersummary stckr
                  INNER JOIN stackermanagement.stackerdetails stckrdtls ON stckr.StackerSummaryID = stckrdtls.StackerSummaryID
                  WHERE stckrdtls.PaymentType = 2
                    AND stckrdtls.StackerSummaryID IN
                      (SELECT tr.StackerSummaryID
                        FROM npos.transactiondetails tr
                        INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                        INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                        LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                        WHERE tr.SiteID = :site_id6
                          AND tr.DateCreated >= :start_date5 AND tr.DateCreated < :end_date5
                          AND tr.Status IN(1,4)
                          AND tr.TransactionType In ('D', 'R')
                            AND tr.StackerSummaryID IS NOT NULL
                          AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID = 15
                          AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID = :site_id7))
                      )
                )) As UnusedTickets,

            (SELECT IFNULL(SUM(stckr.Withdrawal), 0) FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
            WHERE stckr.Status IN (1, 2)
              AND stckr.DateCancelledOn >= :start_date6 AND stckr.DateCancelledOn < :end_date6
              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4, 15)
              AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID = :site_id8))) AS CancelledTickets,

            (SELECT IFNULL(SUM(tckt.Amount), 0) FROM vouchermanagement.tickets tckt  -- Encashed Tickets
            WHERE tckt.DateEncashed >= :start_date7 AND tckt.DateEncashed < :end_date7
            AND tckt.EncashedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID = 4
            AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID = :site_id9))) AS EncashedTickets;";
        $param = array(
                ':site_id0'=>$site_id,
                ':site_id1'=>$site_id,
                ':site_id2'=>$site_id,
                ':site_id3'=>$site_id,
                ':site_id4'=>$site_id,
                ':site_id5'=>$site_id,
                ':site_id6'=>$site_id,
                ':site_id7'=>$site_id,
                ':site_id8'=>$site_id,
                ':site_id9'=>$site_id,
                ':start_date1'=>$date . ' ' . $cutoff_time,
                ':start_date2'=>$date . ' ' . $cutoff_time,
                ':start_date3'=>$date . ' ' . $cutoff_time,
                ':start_date4'=>$date . ' ' . $cutoff_time,
                ':start_date5'=>$date . ' ' . $cutoff_time,
                ':start_date6'=>$date . ' ' . $cutoff_time,
                ':start_date7'=>$date . ' ' . $cutoff_time,
                ':end_date1'=>$end_date . ' ' . $cutoff_time,
                ':end_date2'=>$end_date . ' ' . $cutoff_time,
                ':end_date3'=>$end_date . ' ' . $cutoff_time,
                ':end_date4'=>$end_date . ' ' . $cutoff_time,
                ':end_date5'=>$end_date . ' ' . $cutoff_time,
                ':end_date6'=>$end_date . ' ' . $cutoff_time,
                ':end_date7'=>$end_date . ' ' . $cutoff_time,
            );
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        return $result;
    }
    
    public function getTicketListperCashier($site_id, $date, $end_date, $aid){
        
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT (SELECT IFNULL(SUM(stckr.Withdrawal), 0) FROM npos.transactiondetails tr  -- Printed Tickets through W
            INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
            LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
            WHERE tr.SiteID = :site_id0
              AND tr.DateCreated >= :start_date1 AND tr.DateCreated < :end_date1
              AND tr.Status IN(1,4)
              AND tr.TransactionType = 'W'
              AND tr.StackerSummaryID IS NOT NULL
              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID = 15
              AND acct.AID = :aid1)) AS PrintedRedemptionTickets,


            (SELECT IFNULL(SUM(Amount), 0) AS Amount FROM
              (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode FROM npos.transactiondetails tr  -- Printed Tickets through W
              INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
              INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
              INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
              LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
              WHERE tr.SiteID = :site_id1
                AND tr.DateCreated >= :start_date2 AND tr.DateCreated < :end_date2
                AND tr.Status IN(1,4)
                AND tr.TransactionType = 'W'
                AND tr.StackerSummaryID IS NOT NULL
                AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID = 15
                AND acct.AID = :aid2)
              UNION ALL
              (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
              WHERE stckr.Status IN (1, 2)
              AND stckr.DateCancelledOn >= :start_date3 AND stckr.DateCancelledOn < :end_date3
              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4, 15)
              AND acct.AID = :aid3)
               )
            )AS UnusedTicketsTbl
            WHERE TicketCode NOT IN (SELECT tckt.TicketCode FROM vouchermanagement.tickets tckt -- Less: Encashed Tickets
                                            WHERE tckt.DateEncashed >= :start_date4 AND tckt.DateEncashed < :end_date4
                                              AND tckt.EncashedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID = 4))
                AND TicketCode NOT IN  -- Less: Used in Deposit and Reload Genesis Transactions
                  (SELECT stckrdtls.VoucherCode
                  FROM stackermanagement.stackersummary stckr
                  INNER JOIN stackermanagement.stackerdetails stckrdtls ON stckr.StackerSummaryID = stckrdtls.StackerSummaryID
                  WHERE stckrdtls.PaymentType = 2
                    AND stckrdtls.StackerSummaryID IN
                      (SELECT tr.StackerSummaryID
                        FROM npos.transactiondetails tr
                        INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                        INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                        LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                        WHERE tr.SiteID = :site_id2
                          AND tr.DateCreated >= :start_date5 AND tr.DateCreated < :end_date5
                          AND tr.Status IN(1,4)
                          AND tr.TransactionType In ('D', 'R')
                            AND tr.StackerSummaryID IS NOT NULL
                          AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID = 15
                          AND acct.AID = :aid4)
                      )
                )) As UnusedTickets,

            (SELECT IFNULL(SUM(stckr.Withdrawal), 0) FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
            WHERE stckr.Status IN (1, 2)
              AND stckr.DateCancelledOn >= :start_date6 AND stckr.DateCancelledOn < :end_date6
              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4, 15)
              AND acct.AID = :aid5)) AS CancelledTickets,

            (SELECT IFNULL(SUM(tckt.Amount), 0) FROM vouchermanagement.tickets tckt  -- Encashed Tickets
            WHERE tckt.DateEncashed >= :start_date7 AND tckt.DateEncashed < :end_date7
            AND tckt.EncashedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID = 4
            AND acct.AID = :aid6)) AS EncashedTickets;";
        $param = array(
                ':site_id0'=>$site_id,
                ':site_id1'=>$site_id,
                ':site_id2'=>$site_id,
                ':aid1'=>$aid,
                ':aid2'=>$aid,
                ':aid3'=>$aid,
                ':aid4'=>$aid,
                ':aid5'=>$aid,
                ':aid6'=>$aid,
                ':start_date1'=>$date . ' ' . $cutoff_time,
                ':start_date2'=>$date . ' ' . $cutoff_time,
                ':start_date3'=>$date . ' ' . $cutoff_time,
                ':start_date4'=>$date . ' ' . $cutoff_time,
                ':start_date5'=>$date . ' ' . $cutoff_time,
                ':start_date6'=>$date . ' ' . $cutoff_time,
                ':start_date7'=>$date . ' ' . $cutoff_time,
                ':end_date1'=>$end_date . ' ' . $cutoff_time,
                ':end_date2'=>$end_date . ' ' . $cutoff_time,
                ':end_date3'=>$end_date . ' ' . $cutoff_time,
                ':end_date4'=>$end_date . ' ' . $cutoff_time,
                ':end_date5'=>$end_date . ' ' . $cutoff_time,
                ':end_date6'=>$end_date . ' ' . $cutoff_time,
                ':end_date7'=>$end_date . ' ' . $cutoff_time,
            );
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        return $result;
    }


    
    public function getTransSummaryStackersummaryW($site_id,$site_code,$date,$enddate,$start,$limit) {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.StackerSummaryID, tr.PaymentType, t.TerminalType,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  from transactiondetails  tr 
            inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
            inner join terminals t on t.TerminalID = tr.TerminalID  where tr.SiteID = :site_id AND tr.TransactionType = 'W' AND tr.StackerSummaryID IS NOT NULL AND tr.StackerSummaryID != '' AND
            tr.DateCreated >= :start_date and tr.DateCreated < :end_date and tr.Status IN(1,4) 
            group by tr.TransactionType,tr.TransactionSummaryID order by tr.TerminalID,tr.DateCreated Desc";
        $param = array(
                ':site_id'=>$site_id,
                ':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,
            );
        $this->exec($sql,$param);
        $result = $this->findAll();
        return $result;
    }    
    // SUM of depost, reload and withdrawal with no limit
    public function getTransSummaryTotals($site_id,$site_code,$date,$enddate) {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount  from transactiondetails  tr 
            inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
            inner join terminals t on t.TerminalID = tr.TerminalID  where tr.SiteID = :site_id AND 
            tr.DateCreated >= :start_date and tr.DateCreated < :end_date and tr.Status IN(1,4) 
            group by tr.TransactionType,tr.TransactionSummaryID order by tr.TerminalID,tr.DateCreated Desc";
        $param = array(
                ':site_id'=>$site_id,
                ':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,
            );
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        $total_deposit = 0;
        $total_reload = 0;
        $total_withdraw = 0;        
        foreach($result as $value) {
            switch ($value['TransactionType']) {
                case 'W':
                    $total_withdraw += $value['amount'];
                    break;
                case 'D':
                    $total_deposit += $value['amount'];
                    break;
                case 'R':
                    $total_reload += $value['amount'];
                    break;
            }
//            $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
           
        }
        
        
//        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, " . 
//                 "SUBSTR(t.TerminalCode,$len) as TerminalCode,if(ts.DateStarted < '$date $cutoff_time', ts.Deposit = 0,ts.Deposit) as Deposit," . 
//                 "ts.Reload,ts.Withdrawal,tr.DateCreated from transactiondetails tr left join transactionsummary ts " . 
//                 "on ts.TransactionsSummaryID = tr.TransactionSummaryID inner join terminals t on t.TerminalID = tr.TerminalID " . 
//                 "where tr.SiteID = :site_id AND tr.DateCreated > :start_date and tr.DateCreated <= :end_date " . 
//                 "group by ts.TransactionsSummaryID order by tr.TerminalID,tr.DateCreated Desc";
        
//        $param = array(
//                    ':site_id'=>$site_id,
//                    ':start_date'=>$date . ' ' . $cutoff_time,
//                    ':end_date'=>$enddate . ' ' . $cutoff_time,
//                );
//        echo($sql);
//        echo '<br />';
//        debug($param); exit;
//        $this->exec($sql, $param);
//        $result =  $this->findAll();
//        $total_deposit = 0;
//        $total_reload = 0;
//        $total_withdraw = 0;
//        foreach($result as $row) {
//            $total_deposit += $row['Deposit'];
//            $total_reload += $row['Reload'];
//            $total_withdraw += $row['Withdrawal'];
//        }
        
        return array('totaldeposit'=>$total_deposit,'totalreload'=>$total_reload,'totalwithdrawal'=>$total_withdraw);
//        $sql = 'SELECT SUM(ts.Deposit) AS totaldeposit, SUM(ts.Reload) AS totalreload, SUM(ts.Withdrawal) AS totalwithdrawal FROM transactionsummary ts ' . 
//                'JOIN terminals tr ON ts.TerminalID = tr.TerminalID JOIN sites st ON ts.SiteID = st.SiteID ' . 
//                'WHERE ts.SiteID = :site_id AND DATE_FORMAT(ts.DateStarted,\'%Y-%m-%d %H:%i:%s\') > DATE_FORMAT(:start_date,\'%Y-%m-%d %H:%i:%s\') AND ' . 
//                'DATE_FORMAT(ts.DateStarted,\'%Y-%m-%d %H:%i:%s\') <= DATE_FORMAT(:end_date,\'%Y-%m-%d %H:%i:%s\')';
//        $param = array(
//                ':start_date'=>$date . ' ' . Mirage::app()->param['cut_off'],
//                ':end_date'=>$enddate . ' ' . Mirage::app()->param['cut_off'],
//                ':site_id'=>$site_id
//            );
//        $this->exec($sql, $param);
//        return $this->find();
    }
    /************************ END TRANSACTION HISTORY *************************/
    
    
    /********************** TRANSACTION SUMMARY PER CASHIER *******************/
    public function getTransactionSummaryperCashierCount($account_id,$start_date,$end_date) {
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT COUNT(*) AS cnt FROM (select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.TerminalID,tr.SiteID, " . 
                 "if(ts.DateStarted < '$start_date $cutoff_time', ts.Deposit = 0,ts.Deposit) as Deposit," . 
                 "ts.Reload,ts.Withdrawal,tr.DateCreated from transactiondetails tr left join transactionsummary ts " . 
                 "on ts.TransactionsSummaryID = tr.TransactionSummaryID inner join terminals t on t.TerminalID = tr.TerminalID " . 
                 "where tr.CreatedByAID = :account_id AND tr.DateCreated >= :start_date and tr.DateCreated < :end_date AND tr.Status IN (1,4) " . 
                 "group by ts.TransactionsSummaryID order by tr.TerminalID,tr.DateCreated Desc) AS total";
        
        $param = array(
            ':account_id'=>$account_id,
            ':start_date'=>$start_date . ' ' . Mirage::app()->param['cut_off'],
            ':end_date'=>$end_date . ' ' . Mirage::app()->param['cut_off']
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['cnt'];
    }
    
    public function getAllTransactionPerCashier($account_id,$site_code,$start_date,$end_date) {
        $len = strlen($site_code) + 1;
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, " . 
                 "SUBSTR(t.TerminalCode,$len) as TerminalCode,if(ts.DateStarted < '$start_date $cutoff_time', ts.Deposit = 0,ts.Deposit) as Deposit," . 
                 "ts.Reload,ts.Withdrawal,tr.DateCreated from transactiondetails tr left join transactionsummary ts " . 
                 "on ts.TransactionsSummaryID = tr.TransactionSummaryID inner join terminals t on t.TerminalID = tr.TerminalID " . 
                 "INNER JOIN siteaccounts sa ON sa.SiteID = ts.SiteID " .   
                 "where sa.AID = :account_id AND tr.DateCreated >= :start_date and tr.DateCreated < :end_date AND tr.Status IN (1,4) " . 
                 "group by ts.TransactionsSummaryID order by tr.TerminalID,tr.DateCreated Desc";
        
        $param = array(
            ':account_id'=>$account_id,
            ':start_date'=>$start_date . ' ' . Mirage::app()->param['cut_off'],
            ':end_date'=>$end_date . ' ' . Mirage::app()->param['cut_off'],
        );
        $this->exec($sql, $param);
        return $this->findAll();        
    }
    
    public function getTransactionSummaryPerCashier($site_id,$account_id,$site_code,$start_date,$end_date,$start,$limit) {
        $len = strlen($site_code) + 1;
        $cutoff_time = Mirage::app()->param['cut_off'];
//        $sql = "SELECT SUBSTR(t.TerminalCode,$len) AS tc,SUBSTR(t.TerminalCode,$len) as TerminalCode, td.TransactionDetailsID,td.SiteID,td.TerminalID,td.TransactionType,SUM(td.Amount) as totalamount,td.DateCreated from transactiondetails td " .
//                "INNER JOIN terminals t ON t.TerminalID = td.TerminalID " .
//                "where td.CreatedByAID = :account_id and td.DateCreated > DATE_FORMAT(:start_date,'%Y-%m-%d %H:%i:%s') and " . 
//                "td.DateCreated <= DATE_FORMAT(:end_date,'%Y-%m-%d %H:%i:%s') GROUP BY td.TransactionType,td.TerminalID order by tc ASC, td.DateCreated DESC " . 
//                "LIMIT :start, :limit"; 
        
        $sql = "SELECT tr.TransactionSummaryID, SUBSTR(t.TerminalCode,$len) AS TerminalCode, tr.TransactionType, t.TerminalType,
 
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
            ts.DateStarted, ts.DateEnded

            FROM npos.transactiondetails tr INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
            WHERE  tr.CreatedByAID = :account_id AND tr.SiteID = :site_id
              AND tr.DateCreated >= :start_date AND tr.DateCreated < :end_date
              AND tr.Status IN(1,4)
            GROUP By tr.TransactionType, tr.TransactionSummaryID
            ORDER BY tr.TerminalID, tr.DateCreated DESC;";
        //sa.AID tr.CreatedByAID
        $param = array(
            ':account_id'=>$account_id,
            ':site_id'=>$site_id,
            ':start_date'=>$start_date . ' ' . Mirage::app()->param['cut_off'],
            ':end_date'=>$end_date . ' ' . Mirage::app()->param['cut_off'],
        );

        $this->exec($sql, $param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) {
            if($value['TerminalType'] == 1){
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else{
                $terminalCode = $value['TerminalCode']; 
            }
            if(!isset($new_result[$value['TransactionSummaryID']])) {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'TerminalCode'=>$terminalCode,
                    'DCash'=>'0.00',
                    'DTicket'=>'0.00',
                    'DCoupon'=>'0.00',
                    'RCash'=>'0.00',
                    'RTicket'=>'0.00',
                    'RCoupon'=>'0.00',
                    'WCashier'=>'0.00',
                    'WGenesis'=>'0.00'
                );
            }
            $merge_array = array();
            switch ($value['TransactionType']) {
                case 'W':
                    if($value['RedemptionCashier'] > 0){
                        $merge_array = array('WCashier'=>$value['RedemptionCashier']);
                    }
                    
                    if($value['RedemptionGenesis'] > 0){
                        $merge_array = array('WGenesis'=>$value['RedemptionGenesis']);
                    }
                    break;
                case 'D':
                        $merge_array = array('DCash'=>$value['DepositCash'], 'DTicket'=>$value['DepositTicket'],'DCoupon'=>$value['DepositCoupon'] );
                    
                    break;
                case 'R':
                        $merge_array = array('RCash'=>$value['ReloadCash'],'RTicket'=>$value['ReloadTicket'],'RCoupon'=>$value['ReloadCoupon']);
                    
                    break;
            }
            $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
            
            
           
        }
        $res = array();
        foreach($new_result as $value) {
            $res[] = $value;
        }
        //$res = array_slice($res, $start, $limit);
        return $res;
        
    }    
    
    public function getTransactionSummaryPerCashierTotals($site_id,$site_code,$account_id,$start_date,$end_date) {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.StackerSummaryID, tr.PaymentType, t.TerminalType,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  from transactiondetails  tr 
            inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
            inner join terminals t on t.TerminalID = tr.TerminalID  where tr.SiteID = :site_id AND tr.CreatedByAID = :account_id AND
            tr.DateCreated >= :start_date and tr.DateCreated < :end_date and tr.Status IN(1,4) 
            group by tr.TransactionType,tr.TransactionSummaryID order by tr.TerminalID,tr.DateCreated Desc";
        $param = array(
                ':account_id'=>$account_id,
                ':site_id'=>$site_id,
                ':start_date'=>$start_date . ' ' . $cutoff_time,
                ':end_date'=>$end_date . ' ' . $cutoff_time,
            );
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) {
            if($value['TerminalType'] == 1){
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else{
                $terminalCode = $value['TerminalCode']; 
            }
            if(!isset($new_result[$value['TransactionSummaryID']])) {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'DateCreated'=>$value['DateCreated'],
                    'TerminalID'=>$value['TerminalID'],
                    'SiteID'=>$value['SiteID'],
                    'TerminalCode'=>$terminalCode,
                    'LoyaltyCard'=>$value['Option1'],
                    'RegDCash'=>'0.00',
                    'RegDTicket'=>'0.00',
                    'RegDCoupon'=>'0.00',
                    'RegRCash'=>'0.00',
                    'RegRTicket'=>'0.00',
                    'RegRCoupon'=>'0.00',
                    'GenDCash'=>'0.00',
                    'GenDTicket'=>'0.00',
                    'GenDCoupon'=>'0.00',
                    'GenRCash'=>'0.00',
                    'GenRTicket'=>'0.00',
                    'GenRCoupon'=>'0.00',
                    'WCashier'=>'0.00',
                    'WGenesis'=>'0.00'
                );
            }
            $merge_array = array();
            
            if($value['StackerSummaryID'] == null || $value['StackerSummaryID'] == ''){
                
                
                if($value['PaymentType'] == 1){
                    switch ($value['TransactionType']) {
                        case 'W':
                            $merge_array = array('WCashier'=>$value['amount']);
                            break;
                        case 'D':
                            $merge_array = array('RegDCash'=>$value['amount']);
                            break;
                        case 'R':
                            $merge_array = array('RegRCash'=>$value['amount']);
                            break;
                    }
                }
                else{
                    switch ($value['TransactionType']) {
                        case 'W':
                            $merge_array = array('WCashier'=>$value['amount']);
                            break;
                        case 'D':
                            $merge_array = array('RegDCoupon'=>$value['amount']);
                            break;
                        case 'R':
                            $merge_array = array('RegRCoupon'=>$value['amount']);
                            break;
                    }
                }
               
                $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
                
            }
            else{
                
                $sql2 = "SELECT Deposit, Reload, TicketDeposit, TicketReload, Withdrawal FROM stackermanagement.stackersummary WHERE StackerSummaryID = :stackersummaryid;";
                $param2 = array(
                        ':stackersummaryid'=>$value['StackerSummaryID']
                    );
                $this->exec2($sql2,$param2);
                $result2 = $this->findAll2();
                
                foreach ($result2 as $value2) {
                    $ticketreload = $value2['TicketReload'];
                    $ticketdeposit = $value2['TicketDeposit'];
                    $reload = $value2['Reload'];
                    $deposit = $value2['Deposit'];
                    $ticketwithdraw = $value2['Withdrawal'];
                }
                
                if($value['TransactionType'] == 'W'){
                    $merge_arrays = array('WGenesis'=>$value['amount']);
                }
                
                else  if($value['TransactionType'] == 'R' && $ticketreload > 0){
                    $merge_arrays = array('GenRTicket'=>$ticketreload);
                }
                
                else if($value['TransactionType'] == 'D' && $ticketdeposit > 0){
                    $merge_arrays = array('GenDTicket'=>$ticketdeposit);
                }
                
                else{
                    if($deposit > 0 && $ticketdeposit <= 0 && $value['TransactionType'] == 'D'){
                        $merge_arrays = array('GenDCash'=>$deposit);
                    }
                    else if($reload > 0 && $ticketreload <= 0 && $value['TransactionType'] == 'R'){
                        $merge_arrays = array('GenRCash'=>$reload);
                    }
                    else{
                        if($value['PaymentType'] == 1){
                            switch ($value['TransactionType']) {
                                case 'D':
                                    $merge_arrays = array('RegDCash'=>$value['amount']);
                                    break;
                                case 'R':
                                    $merge_arrays = array('RegRCash'=>$value['amount']);
                                    break;
                            }
                        }
                        else{
                            switch ($value['TransactionType']) {
                                case 'D':
                                    $merge_arrays = array('RegDCoupon'=>$value['amount']);
                                    break;
                                case 'R':
                                    $merge_arrays = array('RegRCoupon'=>$value['amount']);
                                    break;
                            }
                        }
                    }
                }
                $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_arrays);   
            }
        }
        
        $res = array();
        foreach($new_result as $value) {
            $res[] = $value;
        }
        
        return $res;
    }
}