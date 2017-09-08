<?php

/**
 * Date Created 11 7, 11 3:55:18 PM <pre />
 * Description of TransactionSummaryModel
 * @author Bryan Salazar
 */
class TransactionSummaryModel extends MI_Model
{
    public function insert($site_id,$terminal_id,$amount,$acctid)
    //public function insert($site_id,$terminal_id,$amount,$acctid, $vip_type = 0) // CCT Added vip_type VIP
    {
        //$sql = 'INSERT INTO transactionsummary (SiteID, TerminalID, Deposit, DateStarted, DateEnded, CreatedByAID, OptionID1) ' .
        //        'VALUES (:site_id, :terminal_id, :amount, now(6), \'0\', :acctid, :vip_type)'; // CCT added vip_type and OptionID1

        $sql = 'INSERT INTO transactionsummary (SiteID, TerminalID, Deposit, DateStarted, DateEnded, CreatedByAID) ' .
                'VALUES (:site_id, :terminal_id, :amount, now(6), \'0\', :acctid)'; 

        //$param = array(':site_id'=>$site_id,':terminal_id'=>$terminal_id,':amount'=>$amount,':acctid'=>$acctid, ':vip_type'=>vip_type);
        $param = array(':site_id'=>$site_id,':terminal_id'=>$terminal_id,':amount'=>$amount,':acctid'=>$acctid);
        return $this->exec($sql,$param);
    }
    
    /**
     * Description: get last
     * @param type $site_id
     * @param type $terminal_id
     * @return type 
     */
    public function getTransactionSummaryDetail($site_id,$terminal_id) 
    {
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
   
    public function getAllTransactionSummary($site_id,$site_code,$date,$enddate) 
    {
        $len = strlen($site_code) + 1;
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, " . 
                "SUBSTR(t.TerminalCode,$len) as TerminalCode,if(ts.DateStarted < '$date $cutoff_time', ts.Deposit = 0,ts.Deposit) as Deposit," . 
                "ts.Reload,ts.Withdrawal,tr.DateCreated from transactiondetails tr left join transactionsummary ts " . 
                "on ts.TransactionsSummaryID = tr.TransactionSummaryID inner join terminals t on t.TerminalID = tr.TerminalID " . 
                "where tr.SiteID = :site_id AND tr.DateCreated >= :start_date and tr.DateCreated < :end_date AND tr.Status IN (1,4) " . 
                "group by ts.TransactionsSummaryID order by tr.TerminalID,tr.DateCreated Desc";
        $param = array(':site_id'=>$site_id,
                ':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,);
        $this->exec($sql, $param);
        return $this->findAll();
    }
    
    public function getTransSummaryPaging($site_id,$site_code,$date,$enddate,$start,$limit) 
    {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT tr.TransactionSummaryID, SUBSTR(t.TerminalCode,$len) AS TerminalCode, tr.TransactionType, t.TerminalType,

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

            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN accounts a ON ts.CreatedByAID = a.AID
            WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date
              AND tr.SiteID = :site_id
              AND tr.Status IN(1,4)
            GROUP By tr.TransactionType, tr.TransactionSummaryID
            ORDER BY tr.TerminalID, tr.DateCreated DESC;";
        $param = array(':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,
                ':site_id'=>$site_id,);
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
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
            switch ($value['TransactionType']) 
            {
                case 'W':
                    if($value['RedemptionCashier'] > 0)
                    {
                        $merge_array = array('WCashier'=>$value['RedemptionCashier']);
                    }
                    
                    if($value['RedemptionGenesis'] > 0)
                    {
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
        foreach($new_result as $value) 
        {
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
//        $param = array(':site_id'=>$site_id,
//                    ':start_date'=>$date . ' ' . $cutoff_time,
//                    ':end_date'=>$enddate . ' ' . $cutoff_time,
//                    ':start'=>$start,
//                    ':limit'=>$limit,);
//        $this->exec($sql, $param);
//        $result = $this->findAll();
//        MI_Logger::log($result, E_ERROR);
//        return $result;
    }
    
    public function _getTransSummaryPaging($site_id,$site_code,$date,$enddate,$start,$limit) 
    {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT tr.TerminalID, tr.TransactionSummaryID, SUBSTR(t.TerminalCode,$len) AS TerminalCode, tr.TransactionType, t.TerminalType,
 
            -- TOTAL DEPOSIT --
            CASE tr.TransactionType
              WHEN 'D' THEN SUM(tr.Amount)
              ELSE 0
            END As TotalDeposit,
            
            -- TOTAL RELOAD --
            CASE tr.TransactionType
              WHEN 'R' THEN SUM(tr.Amount)
              ELSE 0 -- Not Reload
            END As TotalReload,

            -- TOTAL REDEMPTION --
            CASE tr.TransactionType
              WHEN 'W' THEN SUM(tr.Amount)
              ELSE 0
            END As TotalRedemption,
            ts.DateStarted, ts.DateEnded, ts.StartBalance, ts.EndBalance, ts.WalletReloads, a.AccountTypeID

            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN accounts a ON ts.CreatedByAID = a.AID
            WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date
              AND tr.SiteID = :site_id
              AND tr.Status IN(1,4)
            GROUP By tr.TransactionType, tr.TransactionSummaryID
            ORDER BY tr.TerminalID, tr.DateCreated DESC;";
        $param = array( ':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,
                ':site_id'=>$site_id, );
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            
            if($value['AccountTypeID'] == 17)
            {
                $IseSAFETrans = 1;
            } 
            else 
            { 
                $IseSAFETrans = 0; 
            }

            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'TerminalCode'=>$terminalCode,
                    'IseSAFETrans'=>$IseSAFETrans,
                    'TerminalID'=>$value['TerminalID'],
                    'TotalTransDeposit'=>$value['TotalDeposit'],
                    'TotalTransReload'=>$value['TotalReload'],
                    'TotalTransRedemption'=>$value['TotalRedemption'],
                    'StartBalance'=>$value['StartBalance'],
                    'EndBalance'=>$value['EndBalance'],
                    'WalletReloads'=>$value['WalletReloads'],
                    'AccountTypeID'=>$value['AccountTypeID'],
                );
            } 
            else 
            {
                $new_result[$value['TransactionSummaryID']]['TotalTransDeposit'] +=$value['TotalDeposit'];
                $new_result[$value['TransactionSummaryID']]['TotalTransReload'] +=$value['TotalReload'];
                $new_result[$value['TransactionSummaryID']]['TotalTransRedemption'] +=$value['TotalRedemption'];
            }
        }
        $res = array();
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        return $res;
    }
    
    public function getTransSummaryPagingWithTerminalID($site_id,$site_code,$terminal_id,$trans_sum_id,$date,$enddate,$start,$limit) 
    {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT tr.TransactionSummaryID, SUBSTR(t.TerminalCode,$len) AS TerminalCode, tr.TransactionType, t.TerminalType,

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
            ts.DateStarted, ts.DateEnded, ts.StartBalance, ts.EndBalance, ts.WalletReloads, a.AccountTypeID

            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN accounts a ON ts.CreatedByAID = a.AID
            WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date
              AND tr.SiteID = :site_id
              AND tr.Status IN(1,4)
              AND tr.TerminalID = :terminal_id
              AND tr.TransactionSummaryID = :trans_sum_id
            GROUP By tr.TransactionType, tr.TransactionSummaryID
            ORDER BY tr.TerminalID, tr.DateCreated DESC;";
        $param = array(':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,
                ':site_id'=>$site_id,
                ':terminal_id'=>$terminal_id,
                ':trans_sum_id'=>$trans_sum_id,);
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            
            if($value['AccountTypeID'] == 17)
            {
                $IseSAFETrans = 1;
            } 
            else 
            { 
                $IseSAFETrans = 0; 
            }
            
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'TerminalCode'=>$terminalCode,
                    'IseSAFETrans'=>$IseSAFETrans,
                    'DCash'=>'0.00',
                    'DTicket'=>'0.00',
                    'DCoupon'=>'0.00',
                    'RCash'=>'0.00',
                    'RTicket'=>'0.00',
                    'RCoupon'=>'0.00',
                    'WCashier'=>'0.00',
                    'WGenesis'=>'0.00',
                    'StartBalance'=>$value['StartBalance'],
                    'EndBalance'=>$value['EndBalance'],
                    'WalletReloads'=>$value['WalletReloads'],
                    'AccountTypeID'=>$value['AccountTypeID'],
                );
            }
            $merge_array = array();
            switch ($value['TransactionType']) 
            {
                case 'W':
                    if($value['RedemptionCashier'] > 0)
                    {
                        $merge_array = array('WCashier'=>$value['RedemptionCashier']);
                    }
                    
                    if($value['RedemptionGenesis'] > 0)
                    {
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
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        return $res;
    }
    
    public function getTransSummaryTotalsPerCG($site_id,$site_code,$date,$enddate,$start,$limit) 
    {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
//        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.StackerSummaryID, tr.PaymentType, t.TerminalType,
//            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  from transactiondetails  tr FORCE INDEX(IX_transactiondetails_DateCreated) 
//            inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
//            inner join terminals t on t.TerminalID = tr.TerminalID  where tr.SiteID = :site_id AND 
//            tr.DateCreated >= :start_date and tr.DateCreated < :end_date and tr.Status IN(1,4) 
//            group by tr.TransactionType,tr.TransactionSummaryID,tr.PaymentType order by tr.TerminalID,tr.DateCreated Desc";
        $sql = "SELECT a.AccountTypeID, ew.Status as eSAFEStatus, IFNULL(tr.StackerSummaryID,IFNULL(ew.StackerSummaryID,'')) AS StackerSumm, t.TerminalType,
                            ts.StartBalance, ts.EndBalance, ts.WalletReloads, ew.StackerSummaryID as eSAFEStackerSumm, tr.StackerSummaryID, tr.TransactionSummaryID, 
                            ts.DateStarted, ts.DateEnded, tr.DateCreated, tr.TerminalID, tr.SiteID, tr.PaymentType, t.TerminalType, SUBSTR(t.TerminalCode,$len) AS TerminalCode,
                            tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1 
                    FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
                    INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
                    INNER JOIN terminals t ON t.TerminalID = tr.TerminalID 
                    INNER JOIN accounts a ON ts.CreatedByAID = a.AID
                    LEFT JOIN ewallettrans ew ON ew.TransactionSummaryID = ts.TransactionsSummaryID
                    WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date 
                    AND tr.SiteID = :site_id AND tr.Status IN(1,4) 
                    GROUP BY tr.TransactionType,tr.TransactionSummaryID,tr.PaymentType 
                    ORDER BY tr.TerminalID,tr.DateCreated DESC";
        $param = array(':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,
                ':site_id'=>$site_id,);
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            
            if($value['AccountTypeID'] == 17)
            {
                $IseSAFETrans = 1;
            } else { $IseSAFETrans = 0; }
            
            /*if($value['StackerSumm'] != ''){
                $IsEGM = 1;
            } else { $IsEGM = 0; }*/
            if($value['TerminalType'] == 1)
            {
                $IsEGM = 1;
            } 
            else 
            {   
                $IsEGM = 0; 
            }
            
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'DateCreated'=>$value['DateCreated'],
                    'TerminalID'=>$value['TerminalID'],
                    'SiteID'=>$value['SiteID'],
                    'TerminalCode'=>$terminalCode,
                    'IsEGM'=>$IsEGM,
                    'IseSAFETrans'=>$IseSAFETrans,
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
                    'WGenesis'=>'0.00',
                    'StartBalance'=>$value['StartBalance'],
                    'EndBalance'=>$value['EndBalance'],
                    'WalletReloads'=>$value['WalletReloads']
                );
            }
            $merge_array = array();
            if($value['amount'] == NULL)
            {
                $value['amount'] = '0.00';
            }
            if($value['StackerSumm'] == null || $value['StackerSumm'] == '')
            {
                if($value['PaymentType'] == 1)
                {
                    switch ($value['TransactionType']) 
                    {
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
                else
                {
                    switch ($value['TransactionType']) 
                    {
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
            else
            {
                if($value['eSAFEStatus'] != NULL )
                {
                    if($value['eSAFEStatus'] != 1)
                    {
                        if($value['eSAFEStatus'] != 3)
                        {
                            continue;
                        }
                    }
                }
                
                if($value['TransactionType'] == 'W')
                {
                    $merge_arrays = array('WGenesis'=>$value['amount']);
                    $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_arrays);
                }
                elseif($value['TransactionType'] == 'D')
                {
                    $sql2 = "SELECT DISTINCT(PaymentType) FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype;";
                    $param2 = array(':stackersummaryid'=>$value['StackerSumm'], 
                        ':transtype'=>1,);
                    $this->exec2($sql2,$param2);
                    $result2 = $this->findAll2();
                    
                    foreach ($result2 as $value2) 
                    {
                        $pymnttype = $value2['PaymentType'];
                        if($pymnttype == '0')
                        {
                            $sql3 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param3 = array(':stackersummaryid'=>$value['StackerSumm'],
                                    ':transtype'=>1,
                                    ':paymenttype'=>0);
                            $this->exec2($sql3,$param3);
                            $result3 = $this->findAll2();
                            
                            foreach ($result3 as $value3) 
                            {
                                $amt = $value3['Amount'];
                            }
                            $merge_arrays = array('GenDCash'=>$amt);
                        }
                        else
                        {
                            $sql4 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param4 = array(':stackersummaryid'=>$value['StackerSumm'],
                                    ':transtype'=>1,
                                    ':paymenttype'=>2);
                            $this->exec2($sql4,$param4);
                            $result4 = $this->findAll2();
                            
                            foreach ($result4 as $value4) 
                            {
                                $amt1 = $value4['Amount'];
                            }
                            $merge_arrays = array('GenDTicket'=>$amt1);
                        }
                        $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_arrays); 
                    }
                }
                elseif($value['TransactionType'] == 'R')
                {
                    $sql2 = "SELECT DISTINCT(PaymentType) FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype;";
                    $param2 = array(':stackersummaryid'=>$value['StackerSumm'],
                            ':transtype'=>2, );
                    $this->exec2($sql2,$param2);
                    $result2 = $this->findAll2();
                    
                    foreach ($result2 as $value2) 
                    {
                        if($value2['PaymentType'] == '0')
                        {
                            $sql3 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param3 = array(':stackersummaryid'=>$value['StackerSumm'],
                                    ':transtype'=>2,
                                    ':paymenttype'=>0 );
                            $this->exec2($sql3,$param3);
                            $result3 = $this->findAll2();
                            
                            foreach ($result3 as $value3) 
                            {
                                $amt2 = $value3['Amount'];
                            }
                            $merge_arrays = array('GenRCash'=>$amt2);
                        }
                        else
                        {
                            $sql4 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param4 = array(':stackersummaryid'=>$value['StackerSumm'],
                                    ':transtype'=>2,
                                    ':paymenttype'=>2);
                            $this->exec2($sql4,$param4);
                            $result4 = $this->findAll2();
                            
                            foreach ($result4 as $value4) 
                            {
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
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        return $res;
    }
        
    public function _getTransSummaryTotalsPerCG($site_id,$site_code,$date,$enddate,$start,$limit) 
    {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.StackerSummaryID, tr.PaymentType, t.TerminalType,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  FROM transactiondetails  tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID  WHERE tr.DateCreated >= :start_date 
            AND tr.DateCreated < :end_date AND tr.SiteID = :site_id AND tr.Status IN(1,4) 
            GROUP BY tr.TransactionType,tr.TransactionSummaryID,tr.PaymentType ORDER BY tr.TerminalID,tr.DateCreated DESC";
        $param = array(':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,
                ':site_id'=>$site_id,);
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $res = array();
        $res[0]['TotalTransDeposit'] = 0;
        $res[0]['TotalTransReload'] = 0;
        $res[0]['TotalTransRedemption'] = 0;
        
        foreach($result as $value) 
        {
            switch ($value['TransactionType']) 
            {
                case 'D':
                    $res[0]['TotalTransDeposit'] += $value['amount'];
                    break;
                case 'R':
                    $res[0]['TotalTransReload'] += $value['amount'];
                    break;
                case 'W':
                    $res[0]['TotalTransRedemption'] += $value['amount'];
                    break;
            }
        }
        return $res;
    }
    
    public function getTransSummaryTotalsPerCGWithTerminalID($site_id,$site_code,$terminal_id,$trans_sum_id,$date,$enddate,$start,$limit) 
    {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.StackerSummaryID, tr.PaymentType, t.TerminalType,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  FROM transactiondetails  tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID  WHERE tr.DateCreated >= :start_date 
            AND tr.DateCreated < :end_date AND tr.SiteID = :site_id AND tr.Status IN(1,4) AND tr.TerminalID = :terminal_id AND tr.TransactionSummaryID = :trans_sum_id
            GROUP BY tr.TransactionType,tr.TransactionSummaryID,tr.PaymentType ORDER BY tr.TerminalID,tr.DateCreated DESC";
        $param = array(':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,
                ':site_id'=>$site_id,
                ':terminal_id'=>$terminal_id,
                ':trans_sum_id'=>$trans_sum_id,);
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
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
            if($value['amount'] == NULL)
            {
                $value['amount'] = '0.00';
            }
            if($value['StackerSummaryID'] == null || $value['StackerSummaryID'] == '')
            {
                if($value['PaymentType'] == 1)
                {
                    switch ($value['TransactionType']) 
                    {
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
                else
                {
                    switch ($value['TransactionType']) 
                    {
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
            else
            {
                if($value['TransactionType'] == 'W')
                {
                    $merge_arrays = array('WGenesis'=>$value['amount']);
                    $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_arrays);
                }
                elseif($value['TransactionType'] == 'D')
                {
                    $sql2 = "SELECT DISTINCT(PaymentType) FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype;";
                    $param2 = array(':stackersummaryid'=>$value['StackerSummaryID'],
                            ':transtype'=>1,);
                    $this->exec2($sql2,$param2);
                    $result2 = $this->findAll2();
                    
                    foreach ($result2 as $value2) 
                    {
                        $pymnttype = $value2['PaymentType'];
                        if($pymnttype == '0')
                        {
                            $sql3 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param3 = array(':stackersummaryid'=>$value['StackerSummaryID'],
                                    ':transtype'=>1,
                                    ':paymenttype'=>0);
                            $this->exec2($sql3,$param3);
                            $result3 = $this->findAll2();
                            
                            foreach ($result3 as $value3) 
                            {
                                $amt = $value3['Amount'];
                            }
                            $merge_arrays = array('GenDCash'=>$amt);
                        }
                        else
                        {
                            $sql4 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param4 = array(':stackersummaryid'=>$value['StackerSummaryID'],
                                    ':transtype'=>1,
                                    ':paymenttype'=>2);
                            $this->exec2($sql4,$param4);
                            $result4 = $this->findAll2();
                            
                            foreach ($result4 as $value4) 
                            {
                                $amt1 = $value4['Amount'];
                            }
                            $merge_arrays = array('GenDTicket'=>$amt1);
                        }
                        $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_arrays); 
                    }
                }
                elseif($value['TransactionType'] == 'R')
                {
                    $sql2 = "SELECT DISTINCT(PaymentType) FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype;";
                    $param2 = array(':stackersummaryid'=>$value['StackerSummaryID'],
                            ':transtype'=>2,);
                    $this->exec2($sql2,$param2);
                    $result2 = $this->findAll2();
                    
                    foreach ($result2 as $value2) 
                    {
                        if($value2['PaymentType'] == '0')
                        {
                            $sql3 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param3 = array(':stackersummaryid'=>$value['StackerSummaryID'],
                                    ':transtype'=>2,
                                    ':paymenttype'=>0);
                            $this->exec2($sql3,$param3);
                            $result3 = $this->findAll2();
                            
                            foreach ($result3 as $value3) 
                            {
                                $amt2 = $value3['Amount'];
                            }
                            $merge_arrays = array('GenRCash'=>$amt2);
                        }
                        else
                        {
                            $sql4 = "SELECT SUM(Amount) AS Amount FROM stackermanagement.stackerdetails WHERE StackerSummaryID = :stackersummaryid AND TransactionType = :transtype AND PaymentType = :paymenttype;";
                            $param4 = array(':stackersummaryid'=>$value['StackerSummaryID'],
                                    ':transtype'=>2,
                                    ':paymenttype'=>2);
                            $this->exec2($sql4,$param4);
                            $result4 = $this->findAll2();
                            
                            foreach ($result4 as $value4) 
                            {
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
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        return $res;
    }
    
    public function getTicketList($site_id, $date, $end_date)
    {
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT (SELECT IFNULL(SUM(stckr.Withdrawal), 0) FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN accounts a ON tr.CreatedByAID = a.AID
            LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
            WHERE tr.DateCreated >= :start_date1 AND tr.DateCreated < :end_date1 
              AND tr.SiteID = :site_id0 
              AND tr.Status IN(1,4)
              AND tr.TransactionType = 'W'
              AND tr.StackerSummaryID IS NOT NULL
              AND stckr.CreatedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 15
              AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :site_id1))) AS PrintedRedemptionTickets,

            (SELECT IFNULL(SUM(Amount), 0) AS Amount FROM
              (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
              INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
              INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
              INNER JOIN accounts a ON tr.CreatedByAID = a.AID
              LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
              WHERE tr.DateCreated >= :start_date2 AND tr.DateCreated < :end_date2 
                AND tr.SiteID = :site_id2 
                AND tr.Status IN(1,4)
                AND tr.TransactionType = 'W'
                AND tr.StackerSummaryID IS NOT NULL
                AND stckr.CreatedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 15
                AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :site_id3))
              UNION ALL
              (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
              WHERE stckr.Status IN (1, 2)
              AND stckr.DateCancelledOn >= :start_date3 AND stckr.DateCancelledOn < :end_date3
              AND stckr.CreatedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID IN (4, 15)
              AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :site_id4))
               )
            )AS UnusedTicketsTbl
            WHERE TicketCode NOT IN (SELECT tckt.TicketCode FROM vouchermanagement.tickets tckt -- Less: Encashed Tickets
                                            WHERE tckt.DateEncashed >= :start_date4 AND tckt.DateEncashed < :end_date4
                                              AND tckt.EncashedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 4
                                              AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :site_id5)))
                AND TicketCode NOT IN  -- Less: Used in Deposit and Reload Genesis Transactions
                  (SELECT stckrdtls.VoucherCode
                  FROM stackermanagement.stackersummary stckr
                  INNER JOIN stackermanagement.stackerdetails stckrdtls ON stckr.StackerSummaryID = stckrdtls.StackerSummaryID
                  WHERE stckrdtls.PaymentType = 2
                    AND stckrdtls.StackerSummaryID IN
                      (SELECT tr.StackerSummaryID
                        FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                        INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN accounts a ON tr.CreatedByAID = a.AID
                        LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                        WHERE tr.DateCreated >= :start_date5 AND tr.DateCreated < :end_date5 
                          AND tr.SiteID = :site_id6 
                          AND tr.Status IN(1,4)
                          AND tr.TransactionType In ('D', 'R')
                            AND tr.StackerSummaryID IS NOT NULL
                          AND stckr.CreatedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 15
                          AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :site_id7))
                      )
                )) As UnusedTickets,

            (SELECT IFNULL(SUM(stckr.Withdrawal), 0) FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
            WHERE stckr.Status IN (1, 2)
              AND stckr.DateCancelledOn >= :start_date6 AND stckr.DateCancelledOn < :end_date6
              AND stckr.CreatedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID IN (4, 15)
              AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :site_id8))) AS CancelledTickets,

            (SELECT IFNULL(SUM(tckt.Amount), 0) FROM vouchermanagement.tickets tckt  -- Encashed Tickets
            WHERE tckt.DateEncashed >= :start_date7 AND tckt.DateEncashed < :end_date7
            AND tckt.EncashedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 4
            AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :site_id9))) AS EncashedTickets;";
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
                ':end_date7'=>$end_date . ' ' . $cutoff_time
            );
        $this->exec($sql,$param);
        $result = $this->findAll();
        return $result;
    }
    
    public function getActiveTicketsForTheDay($site_id, $date, $end_date)
    {
        $cutoff_time = Mirage::app()->param['cut_off'];
        $totalactiveticketsfortheday = 0;
        $getprintedtickets = "SELECT Amount, TicketCode FROM vouchermanagement.tickets WHERE DateCreated >= :start_date               -- Get Printed Tickets for the day 
                                                AND DateCreated < :end_date AND SiteID = :siteid";
        $getcancelledtickets = "SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker 
                                                    INNER JOIN accounts acct ON stckr.CreatedByAID = acct.AID
                                                    INNER JOIN siteaccounts sa ON acct.AID = sa.AID
                                                    WHERE stckr.DateCancelledOn >= :start_date AND stckr.DateCancelledOn < :end_date
                                                    AND acct.AccountTypeID IN (4, 15)
                                                    AND sa.SiteID = :siteid";
        $getusedtickets = "SELECT Amount,TicketCode FROM vouchermanagement.tickets WHERE DateUpdated >= :start_date 
                                            AND DateUpdated < :end_date AND DateEncashed IS NULL AND SiteID = :siteid";
        $getencashedtickets = "SELECT Amount,TicketCode FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                                            WHERE tckt.DateEncashed >= :start_date AND tckt.DateEncashed < :end_date 
                                            AND tckt.EncashedByAID IN (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 4
                                            AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :siteid))";
        $param = array(
                ':siteid'=>$site_id,
                ':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$end_date . ' ' . $cutoff_time );
        $this->exec($getprintedtickets,$param);
        $printedTicketsresults = $this->findAll();
        
        $this->exec($getcancelledtickets,$param);
        $cancelledTicketsresults = $this->findAll();
        
        $this->exec($getusedtickets,$param);
        $usedTicketsresults = $this->findAll();
        
        $this->exec($getencashedtickets,$param);
        $encashedTicketsresults = $this->findAll();
        
        foreach ($printedTicketsresults as $key => $value1) 
        {
            foreach ($cancelledTicketsresults as $value2) 
            {
                if($value1['TicketCode'] == $value2['TicketCode'])
                {
                    unset($printedTicketsresults[$key]);
                }
            }
            foreach ($usedTicketsresults as $value3) 
            {
                if($value1['TicketCode'] == $value3['TicketCode'])
                {
                    unset($printedTicketsresults[$key]);
                }
            }
            foreach ($encashedTicketsresults as $value4) 
            {
                if($value1['TicketCode'] == $value4['TicketCode'])
                {
                    unset($printedTicketsresults[$key]);
                }
            }
        }
        
        foreach ($printedTicketsresults as $value) 
        {
            $totalactiveticketsfortheday += $value['Amount'];
        }
        return $totalactiveticketsfortheday;
    }
    
    public function getTicketListperCashier($site_id, $date, $end_date, $aid)
    {
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT (SELECT IFNULL(SUM(stckr.Withdrawal), 0) FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN accounts a ON tr.CreatedByAID = a.AID
            LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
            WHERE tr.DateCreated >= :start_date1 AND tr.DateCreated < :end_date1 
              AND tr.SiteID = :site_id0 
              AND tr.Status IN(1,4)
              AND tr.TransactionType = 'W'
              AND tr.StackerSummaryID IS NOT NULL
              AND stckr.CreatedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 15
              AND acct.AID = :aid1)) AS PrintedRedemptionTickets,

            (SELECT IFNULL(SUM(Amount), 0) AS Amount FROM
              (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
              INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
              INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
              INNER JOIN accounts a ON tr.CreatedByAID = a.AID
              LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
              WHERE tr.DateCreated >= :start_date2 AND tr.DateCreated < :end_date2 
                AND tr.SiteID = :site_id1 
                AND tr.Status IN(1,4)
                AND tr.TransactionType = 'W'
                AND tr.StackerSummaryID IS NOT NULL
                AND stckr.CreatedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 15
                AND acct.AID = :aid2)
              UNION ALL
              (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
              WHERE stckr.Status IN (1, 2)
              AND stckr.DateCancelledOn >= :start_date3 AND stckr.DateCancelledOn < :end_date3
              AND stckr.CreatedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID IN (4, 15)
              AND acct.AID = :aid3)
               )
            )AS UnusedTicketsTbl
            WHERE TicketCode NOT IN (SELECT tckt.TicketCode FROM vouchermanagement.tickets tckt -- Less: Encashed Tickets
                                            WHERE tckt.DateEncashed >= :start_date4 AND tckt.DateEncashed < :end_date4
                                              AND tckt.EncashedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 4))
                AND TicketCode NOT IN  -- Less: Used in Deposit and Reload Genesis Transactions
                  (SELECT stckrdtls.VoucherCode
                  FROM stackermanagement.stackersummary stckr
                  INNER JOIN stackermanagement.stackerdetails stckrdtls ON stckr.StackerSummaryID = stckrdtls.StackerSummaryID
                  WHERE stckrdtls.PaymentType = 2
                    AND stckrdtls.StackerSummaryID IN
                      (SELECT tr.StackerSummaryID
                        FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
                        INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                        INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                        INNER JOIN accounts a ON tr.CreatedByAID = a.AID
                        LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                        WHERE tr.DateCreated >= :start_date5 AND tr.DateCreated < :end_date5 
                          AND tr.SiteID = :site_id2 
                          AND tr.Status IN(1,4)
                          AND tr.TransactionType In ('D', 'R')
                            AND tr.StackerSummaryID IS NOT NULL
                          AND stckr.CreatedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 15
                          AND acct.AID = :aid4)
                      )
                )) As UnusedTickets,

            (SELECT IFNULL(SUM(stckr.Withdrawal), 0) FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
            WHERE stckr.Status IN (1, 2)
              AND stckr.DateCancelledOn >= :start_date6 AND stckr.DateCancelledOn < :end_date6
              AND stckr.CreatedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID IN (4, 15)
              AND acct.AID = :aid5)) AS CancelledTickets,

            (SELECT IFNULL(SUM(tckt.Amount), 0) FROM vouchermanagement.tickets tckt  -- Encashed Tickets
            WHERE tckt.DateEncashed >= :start_date7 AND tckt.DateEncashed < :end_date7
            AND tckt.EncashedByAID In (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 4
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
    
    /**
     * @Description: Get the total printed tickets with an active status for the day per site.
     * @DateCreated: 2015-10-26
     * @Author: aqdepliyan
     * @param int $site_id
     * @param string $date
     * @param string $end_date
     * @param int aid
     * @return float
     */
    public function getActiveTicketsForTheDayPerCashier($site_id, $date, $end_date, $aid)
    {
        $cutoff_time = Mirage::app()->param['cut_off'];
        $totalactiveticketsfortheday = 0;
        $getprintedtickets = "SELECT Amount, TicketCode FROM vouchermanagement.tickets WHERE DateCreated >= :start_date               -- Get Printed Tickets for the day 
                                                AND DateCreated < :end_date AND SiteID = :siteid AND CreatedByAID = :aid";
        $getcancelledtickets = "SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker 
                                                    INNER JOIN accounts acct ON stckr.CreatedByAID = acct.AID
                                                    INNER JOIN siteaccounts sa ON acct.AID = sa.AID
                                                    WHERE stckr.Status IN (1, 2)
                                                    AND stckr.DateCancelledOn >= :start_date AND stckr.DateCancelledOn < :end_date
                                                    AND acct.AccountTypeID IN (4, 15)
                                                    AND acct.AID = :aid
                                                    AND sa.SiteID = :siteid";
        $getusedtickets = "SELECT Amount,TicketCode FROM vouchermanagement.tickets WHERE DateCreated >= :start_date 
                                            AND DateCreated < :end_date AND CreatedByAID = :aid AND Status = 3 AND DateEncashed IS NULL AND SiteID = :siteid";
        $param = array(':siteid'=>$site_id,
                ':aid'=>$aid,
                ':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$end_date . ' ' . $cutoff_time );
        $this->exec($getprintedtickets,$param);
        $printedTicketsresults = $this->findAll();
        
        $this->exec($getcancelledtickets,$param);
        $cancelledTicketsresults = $this->findAll();
        
        $this->exec($getusedtickets,$param);
        $usedTicketsresults = $this->findAll();
        
        foreach ($printedTicketsresults as $key => $value1) 
        {
            foreach ($cancelledTicketsresults as $value2) 
            {
                if($value1['TicketCode'] == $value2['TicketCode'])
                {
                    unset($printedTicketsresults[$key]);
                }
            }
            foreach ($usedTicketsresults as $value3) 
            {
                if($value1['TicketCode'] == $value3['TicketCode'])
                {
                    unset($printedTicketsresults[$key]);
                }
            }
        }
        
        foreach ($printedTicketsresults as $value) 
        {
            $totalactiveticketsfortheday += (float)$value['Amount'];
        }
        return $totalactiveticketsfortheday;
    }
    
    /**
     * @Description: For Site Cash On Hand Reports in Cashier. Function to get encashed tickets per site per cutoff
     * @DateCreated: 2015-10-28
     * @Author: aqdepliyan
     * @param string $startdate
     * @param string $enddate
     * @param int $siteid
     * @return array
     */
    public function getEncashedTickets($startdate,$enddate,$siteid)
    {
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT IFNULL(SUM(tckt.Amount), 0) as EncashedTickets FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                    WHERE tckt.DateEncashed >= :startdate AND tckt.DateEncashed < :enddate 
                    AND tckt.EncashedByAID IN (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 4
                    AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :siteid1))
                    AND tckt.TicketCode NOT IN (SELECT IFNULL(stsum.TicketCode,'') FROM stackermanagement.stackersummary stsum 
                    WHERE stsum.UpdatedByAID IN (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID IN (15,17)
                    AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :siteid2)) AND stsum.EwalletTransID IS NOT NULL)";
        $param = array(':startdate'=>$startdate.' '.$cutoff_time,
            ':enddate'=>$enddate.' '.$cutoff_time,
            ':siteid1'=>$siteid,
            ':siteid2'=>$siteid);
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['EncashedTickets'])?$result['EncashedTickets']:0;
    }
    
    /**
     * @Description: For Transaction History per Cashier. Function to get encashed tickets per site per cutoff
     * @DateCreated: 2015-10-28
     * @Author: aqdepliyan
     * @param string $startdate
     * @param string $enddate
     * @param int $siteid
     * @return array
     */
    public function getEncashedTicketsPerCashier($startdate,$enddate,$siteid,$aid)
    {
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT IFNULL(SUM(tckt.Amount), 0) as EncashedTickets FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                    WHERE tckt.DateEncashed >= :startdate AND tckt.DateEncashed < :enddate 
                    AND tckt.EncashedByAID = :aid
                    AND tckt.EncashedByAID IN (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID = 4
                    AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :siteid1))
                    AND tckt.TicketCode NOT IN (SELECT IFNULL(stsum.TicketCode,'') FROM stackermanagement.stackersummary stsum 
                    WHERE stsum.UpdatedByAID IN (SELECT acct.AID FROM accounts acct WHERE acct.AccountTypeID IN (15,17)
                    AND acct.AID IN (SELECT sacct.AID FROM siteaccounts sacct WHERE sacct.SiteID = :siteid2)) AND stsum.EwalletTransID IS NOT NULL)";
        $param = array(':startdate'=>$startdate.' '.$cutoff_time,
            ':enddate'=>$enddate.' '.$cutoff_time,
            ':aid'=>$aid,
            ':siteid1'=>$siteid,
            ':siteid2'=>$siteid);
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['EncashedTickets'])?$result['EncashedTickets']:0;
    }

    /**
     * @Description: For Site Cash On Hand Reports in Cashier. Function to get transactions grouped into Load Cash( Deposit,Reload), Load Coupon( Deposit,Reload), Load Bancnet( Deposit,Reload),
     * Load Ticket( Deposit,Reload), WCash(Cashier Redemption) and WTicket (Genesis Redemption).
     * @DateCreated: 2015-10-28
     * @Author: aqdepliyan
     * @param string $startdate
     * @param string $enddate
     * @param int $siteid
     * @return array
     */
    public function getTransactionDetailsForCOH($startdate,$enddate,$siteid)
    {
        $cutoff_time = Mirage::app()->param['cut_off'];
        $result = array();
        $sql = "SELECT tdtls.ServiceID,

                -- LOAD CASH --
                SUM(CASE tdtls.TransactionType
                    WHEN 'D' THEN
                            CASE tdtls.PaymentType
                               WHEN 2 THEN 0 -- Coupon
                               ELSE -- Not Coupon
                                     CASE IFNULL(tdtls.StackerSummaryID, '')
                                       WHEN '' THEN 
                                            CASE -- Check if bancnet transaction
                                                    WHEN (SELECT COUNT(BankTransactionLogID) FROM banktransactionlogs btls
                                                            WHERE btls.TransactionRequestLogID = trl.TransactionRequestLogID) > 0
                                                    THEN 0 ELSE tdtls.Amount -- Cash
                                            END
                                       ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                             (SELECT IFNULL(SUM(sdtls.Amount), 0)
                                             FROM stackermanagement.stackerdetails sdtls
                                             WHERE sdtls.stackersummaryID = tdtls.StackerSummaryID
                                                       AND sdtls.TransactionType = 1
                                                       AND sdtls.PaymentType = 0)  -- Cash

                                     END
                            END
                    WHEN 'R' THEN
                            CASE tdtls.PaymentType
                               WHEN 2 THEN 0 -- Coupon
                               ELSE -- Not Coupon
                                     CASE IFNULL(tdtls.StackerSummaryID, '')
                                       WHEN '' THEN 
                                            CASE -- Check if bancnet transaction
                                                    WHEN (SELECT COUNT(BankTransactionLogID) FROM banktransactionlogs btls
                                                            WHERE btls.TransactionRequestLogID = trl.TransactionRequestLogID) > 0
                                                    THEN 0 ELSE tdtls.Amount -- Cash
                                            END
                                       ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                             (SELECT IFNULL(SUM(sdtls.Amount), 0)
                                             FROM stackermanagement.stackerdetails sdtls
                                             WHERE sdtls.stackersummaryID = tdtls.StackerSummaryID
                                                       AND sdtls.TransactionDetailsID = tdtls.TransactionDetailsID
                                                       AND sdtls.TransactionType = 2
                                                       AND sdtls.PaymentType = 0)  -- Cash
                                     END
                            END
                    ELSE 0
                END) As LoadCash,

                -- LOAD COUPON --
                SUM(CASE tdtls.TransactionType
                    WHEN 'D' THEN
                            CASE tdtls.PaymentType
                               WHEN 2 THEN tdtls.Amount -- Coupon
                               ELSE 0 -- Not Coupon
                            END
                    WHEN 'R' THEN
                            CASE tdtls.PaymentType
                               WHEN 2 THEN tdtls.Amount -- Coupon
                               ELSE 0 -- Not Coupon
                            END
                ELSE 0
                END) As LoadCoupon,

                -- LOAD BANCNET --
                SUM(CASE tdtls.TransactionType
                    WHEN 'D' THEN
                            CASE tdtls.PaymentType
                               WHEN 2 THEN 0 -- Coupon
                               ELSE -- Not Coupon
                                     CASE IFNULL(tdtls.StackerSummaryID, '')
                                       WHEN '' THEN 
                                            CASE -- Check if bancnet transaction
                                                    WHEN (SELECT COUNT(BankTransactionLogID) FROM banktransactionlogs btls
                                                            WHERE btls.TransactionRequestLogID = trl.TransactionRequestLogID) > 0
                                                    THEN tdtls.Amount -- Bancnet
                                                    ELSE 0 -- Not Bancnet
                                            END
                                       ELSE 0 -- Not Bancnet
                                     END
                            END
                    WHEN 'R' THEN
                            CASE tdtls.PaymentType
                               WHEN 2 THEN 0 -- Coupon
                               ELSE -- Not Coupon
                                     CASE IFNULL(tdtls.StackerSummaryID, '')
                                       WHEN '' THEN 
                                            CASE -- Check if bancnet transaction
                                                    WHEN (SELECT COUNT(BankTransactionLogID) FROM banktransactionlogs btls
                                                            WHERE btls.TransactionRequestLogID = trl.TransactionRequestLogID) > 0
                                                    THEN tdtls.Amount -- Bancnet
                                                    ELSE 0 -- Not Bancnet
                                            END
                                       ELSE 0 -- Not Bancnet
                                     END
                            END
                    ELSE 0
                END) As LoadBancnet,

                -- LOAD TICKET --
                SUM(CASE tdtls.TransactionType
                    WHEN 'D' THEN
                            CASE tdtls.PaymentType
                              WHEN 2 THEN 0 -- Coupon
                              ELSE -- Not Coupon
                                    CASE IFNULL(tdtls.StackerSummaryID, '')
                                      WHEN '' THEN 0 -- Cash
                                      ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                            (SELECT IFNULL(SUM(sdtls.Amount), 0)
                                            FROM stackermanagement.stackerdetails sdtls
                                            WHERE sdtls.stackersummaryID = tdtls.StackerSummaryID
                                                      AND sdtls.TransactionDetailsID = tdtls.TransactionDetailsID
                                                      AND sdtls.TransactionType = 1
                                                      AND sdtls.PaymentType = 2)  -- Deposit, Ticket
                                    END
                            END
                    WHEN 'R' THEN
                            CASE tdtls.PaymentType
                              WHEN 2 THEN 0 -- Coupon
                              ELSE -- Not Coupon
                                    CASE IFNULL(tdtls.StackerSummaryID, '')
                                      WHEN '' THEN 0 -- Cash
                                      ELSE  -- Check transtype in stackermanagement to find out if ticket or cash, from EGM
                                            (SELECT IFNULL(SUM(sdtls.Amount), 0)
                                            FROM stackermanagement.stackerdetails sdtls
                                            WHERE sdtls.stackersummaryID = tdtls.StackerSummaryID
                                                      AND sdtls.TransactionDetailsID = tdtls.TransactionDetailsID
                                                      AND sdtls.TransactionType = 2
                                                      AND sdtls.PaymentType = 2)  -- Deposit, Ticket
                                    END
                            END
                    ELSE 0 
                END) As LoadTicket,

                -- REDEMPTION CASHIER --
                SUM(CASE tdtls.TransactionType
                  WHEN 'W' THEN
                        CASE a.AccountTypeID
                          WHEN 4 THEN tdtls.Amount -- Cashier
                          ELSE 0
                        END -- Genesis
                  ELSE 0 --  Not Redemption
                END) As WCash,

                -- REDEMPTION GENESIS --
                SUM(CASE tdtls.TransactionType
                  WHEN 'W' THEN
                        CASE a.AccountTypeID
                          WHEN 15 THEN tdtls.Amount -- Genesis
                          WHEN 17 THEN tdtls.Amount -- Genesis
                          ELSE 0
                        END -- Cashier
                  ELSE 0 -- Not Redemption
                END) As WTicket
        FROM transactiondetails tdtls  FORCE INDEX(IX_transactiondetails_DateCreated) 
        INNER JOIN transactionrequestlogs trl ON tdtls.TransactionReferenceID = trl.TransactionReferenceID
        INNER JOIN accounts a ON tdtls.CreatedByAID = a.AID
        WHERE tdtls.DateCreated >= :startdate AND tdtls.DateCreated < :enddate
        AND tdtls.Status IN (1,4) AND tdtls.SiteID = :siteid 
        AND trl.MID = tdtls.MID
	AND trl.TerminalID = tdtls.TerminalID
	AND trl.Amount = tdtls.Amount
	AND trl.TransactionType = tdtls.TransactionType
	AND trl.ServiceID = tdtls.ServiceID
        GROUP BY tdtls.ServiceID";
        
        $param = array(':startdate'=>$startdate . ' ' . $cutoff_time,
                ':enddate'=>$enddate . ' ' . $cutoff_time,
                ':siteid'=>$siteid);
        $this->exec($sql,$param);
        $transdetails = $this->findAll();
        
        foreach($transdetails as $value)
        {
            if(!isset($result['LoadCash']))
            {
                $result['LoadCash'] = (float)$value['LoadCash'];
            }
            else
            { 
                $result['LoadCash'] += (float)$value['LoadCash']; 
            }
            if(!isset($result['LoadCoupon']))
            {
                $result['LoadCoupon'] = (float)$value['LoadCoupon'];
            }
            else
            {   
                $result['LoadCoupon'] += (float)$value['LoadCoupon']; 
            }
            if(!isset($result['LoadTicket']))
            {
                $result['LoadTicket'] = (float)$value['LoadTicket'];
            }
            else
            { 
                $result['LoadTicket'] += (float)$value['LoadTicket']; 
            }
            if(!isset($result['LoadBancnet']))
            {
                $result['LoadBancnet'] = (float)$value['LoadBancnet'];
            }
            else
            { 
                $result['LoadBancnet'] += (float)$value['LoadBancnet']; 
            }
            if(!isset($result['WCash']))
            {
                $result['WCash'] = (float)$value['WCash'];
            }
            else
            { 
                $result['WCash'] += (float)$value['WCash']; 
            }
            if(!isset($result['WTicket']))
            {
                $result['WTicket'] = (float)$value['WTicket'];
            }
            else
            { 
                $result['WTicket'] += (float)$value['WTicket']; 
            }
        }
        return $result;
    }

    public function getTransSummaryStackersummaryW($site_id,$site_code,$date,$enddate,$start,$limit) 
    {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.StackerSummaryID, tr.PaymentType, t.TerminalType,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  from transactiondetails  tr 
            inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
            inner join terminals t on t.TerminalID = tr.TerminalID  where tr.SiteID = :site_id AND tr.TransactionType = 'W' AND tr.StackerSummaryID IS NOT NULL AND tr.StackerSummaryID != '' AND
            tr.DateCreated >= :start_date and tr.DateCreated < :end_date and tr.Status IN(1,4) 
            group by tr.TransactionType,tr.TransactionSummaryID order by tr.TerminalID,tr.DateCreated Desc";
        $param = array(':site_id'=>$site_id,
                ':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,);
        $this->exec($sql,$param);
        $result = $this->findAll();
        return $result;
    }    
    // SUM of depost, reload and withdrawal with no limit
    public function getTransSummaryTotals($site_id,$site_code,$date,$enddate) 
    {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount  from transactiondetails  tr 
            inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
            inner join terminals t on t.TerminalID = tr.TerminalID  where tr.SiteID = :site_id AND 
            tr.DateCreated >= :start_date and tr.DateCreated < :end_date and tr.Status IN(1,4) 
            group by tr.TransactionType,tr.TransactionSummaryID order by tr.TerminalID,tr.DateCreated Desc";
        $param = array(':site_id'=>$site_id,
                ':start_date'=>$date . ' ' . $cutoff_time,
                ':end_date'=>$enddate . ' ' . $cutoff_time,);
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        $total_deposit = 0;
        $total_reload = 0;
        $total_withdraw = 0;        
        foreach($result as $value) 
        {
            switch ($value['TransactionType']) 
            {
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
//    public function getTransactionSummaryperCashierCount($account_id,$start_date,$end_date) {
//        $cutoff_time = Mirage::app()->param['cut_off'];
//        $sql = "SELECT COUNT(*) AS cnt FROM (select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.TerminalID,tr.SiteID, " . 
//                 "if(ts.DateStarted < '$start_date $cutoff_time', ts.Deposit = 0,ts.Deposit) as Deposit," . 
//                 "ts.Reload,ts.Withdrawal,tr.DateCreated from transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) left join transactionsummary ts " . 
//                 "on ts.TransactionsSummaryID = tr.TransactionSummaryID inner join terminals t on t.TerminalID = tr.TerminalID " . 
//                 "WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date AND tr.CreatedByAID = :account_id AND tr.Status IN (1,4) " . 
//                 "GROUP BY ts.TransactionsSummaryID ORDER BY tr.TerminalID,tr.DateCreated DESC) AS total";
//        $param = array(':account_id'=>$account_id,
//            ':start_date'=>$start_date . ' ' . Mirage::app()->param['cut_off'],
//            ':end_date'=>$end_date . ' ' . Mirage::app()->param['cut_off']);
//        $this->exec($sql, $param);
//        $result = $this->find();
//        return $result['cnt'];
//    }
    
//    public function getAllTransactionPerCashier($account_id,$site_code,$start_date,$end_date) {
//        $len = strlen($site_code) + 1;
//        $cutoff_time = Mirage::app()->param['cut_off'];
//        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, " . 
//                 "SUBSTR(t.TerminalCode,$len) as TerminalCode,if(ts.DateStarted < '$start_date $cutoff_time', ts.Deposit = 0,ts.Deposit) as Deposit," . 
//                 "ts.Reload,ts.Withdrawal,tr.DateCreated from transactiondetails tr left join transactionsummary ts " . 
//                 "on ts.TransactionsSummaryID = tr.TransactionSummaryID inner join terminals t on t.TerminalID = tr.TerminalID " . 
//                 "INNER JOIN siteaccounts sa ON sa.SiteID = ts.SiteID " .   
//                 "where sa.AID = :account_id AND tr.DateCreated >= :start_date and tr.DateCreated < :end_date AND tr.Status IN (1,4) " . 
//                 "group by ts.TransactionsSummaryID order by tr.TerminalID,tr.DateCreated Desc";
//        $param = array(':account_id'=>$account_id,
//            ':start_date'=>$start_date . ' ' . Mirage::app()->param['cut_off'],
//            ':end_date'=>$end_date . ' ' . Mirage::app()->param['cut_off'],);
//        $this->exec($sql, $param);
//        return $this->findAll();        
//    }
    
    public function getTransactionSummaryPerCashier($site_id,$account_id,$site_code,$start_date,$end_date,$start,$limit) 
    {
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

            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN accounts a ON ts.CreatedByAID = a.AID
            WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date AND tr.SiteID = :site_id
              AND tr.CreatedByAID = :account_id 
              AND tr.Status IN(1,4)
            GROUP By tr.TransactionType, tr.TransactionSummaryID
            ORDER BY tr.TerminalID, tr.DateCreated DESC;";
        //sa.AID tr.CreatedByAID
        $param = array(':account_id'=>$account_id,
            ':site_id'=>$site_id,
            ':start_date'=>$start_date . ' ' . Mirage::app()->param['cut_off'],
            ':end_date'=>$end_date . ' ' . Mirage::app()->param['cut_off'],);

        $this->exec($sql, $param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
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
            switch ($value['TransactionType']) 
            {
                case 'W':
                    if($value['RedemptionCashier'] > 0)
                    {
                        $merge_array = array('WCashier'=>$value['RedemptionCashier']);
                    }
                    
                    if($value['RedemptionGenesis'] > 0)
                    {
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
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        //$res = array_slice($res, $start, $limit);
        return $res;
    }    
    
    public function _getTransactionSummaryPerCashier($site_id,$account_id,$site_code,$start_date,$end_date,$start,$limit) 
    {
        Mirage::loadModels(array('EWalletTransModel'));
        $eWalletTransModel = new EWalletTransModel();

        $len = strlen($site_code) + 1;
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $sql = "SELECT tr.TerminalID, tr.TransactionSummaryID, SUBSTR(t.TerminalCode,$len) AS TerminalCode, tr.TransactionType, t.TerminalType,
 
            -- TOTAL DEPOSIT --
            CASE tr.TransactionType
              WHEN 'D' THEN SUM(tr.Amount)
              ELSE 0
            END As TotalDeposit,

            -- TOTAL RELOAD --
            CASE tr.TransactionType
              WHEN 'R' THEN SUM(tr.Amount)
              ELSE 0 -- Not Reload
            END As TotalReload,

            -- TOTAL REDEMPTION --
            CASE tr.TransactionType
              WHEN 'W' THEN SUM(tr.Amount)
              ELSE 0
            END As TotalRedemption,
            ts.DateStarted, ts.DateEnded, ts.DateEnded, ts.StartBalance, ts.EndBalance, ts.WalletReloads, a.AccountTypeID

            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN accounts a ON ts.CreatedByAID = a.AID
            WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date AND tr.SiteID = :site_id
              AND tr.CreatedByAID = :account_id 
              AND tr.Status IN(1,4)
            GROUP By tr.TransactionType, tr.TransactionSummaryID
            ORDER BY tr.TerminalID, tr.DateCreated DESC;";
        //sa.AID tr.CreatedByAID
        $param = array(':account_id'=>$account_id,
            ':site_id'=>$site_id,
            ':start_date'=>$start_date . ' ' .$cutoff_time,
            ':end_date'=>$end_date . ' ' .$cutoff_time,);

        $this->exec($sql, $param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            
            if($value['AccountTypeID'] == 17)
            {
                $IseSAFETrans = 1;
            } 
            else 
            { 
                $IseSAFETrans = 0; 
            }
            
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
                $new_result[$value['TransactionSummaryID']] = array
                    ('TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'TerminalCode'=>$terminalCode,
                    'IseSAFETrans'=>$IseSAFETrans,
                    'TerminalID'=>$value['TerminalID'],
                    'TotalCTransDeposit'=>'0.00',
                    'TotalCTransReload'=>'0.00',
                    'TotalCTransRedemption'=>'0.00',
                    'StartBalance'=>'0.00',
                    'EndBalance'=>'0.00',
                    'WalletReloads'=>'0.00',
                );
            }
            $merge_array = array();
            switch ($value['TransactionType']) 
            {
                case 'W':
                    if($value['TotalRedemption'] > 0)
                    {
                        $merge_array = array('TotalCTransRedemption'=>$value['TotalRedemption']);
                    }
                    break;
                case 'D':
                    $merge_array = array('TotalCTransDeposit'=>$value['TotalDeposit'], 'StartBalance' =>$value['StartBalance'],
                                   'EndBalance' =>$value['EndBalance'], 'WalletReloads' =>$value['WalletReloads'] );
                    break;
                case 'R':
                    $merge_array = array('TotalCTransReload'=>$value['TotalReload']);
                    break;
            }
            $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
        }
        $res = array();
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        //$res = array_slice($res, $start, $limit);
        return $res;
    }    
    
    public function _getTransactionSummaryPerVCashier($site_id,$account_id,$site_code,$start_date,$end_date,$start,$limit) 
    {
        Mirage::loadModels(array('EWalletTransModel'));
        $eWalletTransModel = new EWalletTransModel();
        
        $len = strlen($site_code) + 1;
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $sql = "SELECT tr.TerminalID, tr.TransactionSummaryID, SUBSTR(t.TerminalCode,$len) AS TerminalCode, tr.TransactionType, t.TerminalType,
 
            -- TOTAL DEPOSIT --
            CASE tr.TransactionType
              WHEN 'D' THEN SUM(tr.Amount)
              ELSE 0
            END As TotalDeposit,

            -- TOTAL RELOAD --
            CASE tr.TransactionType
              WHEN 'R' THEN SUM(tr.Amount)
              ELSE 0 -- Not Reload
            END As TotalReload,

            -- TOTAL REDEMPTION --
            CASE tr.TransactionType
              WHEN 'W' THEN SUM(tr.Amount)
              ELSE 0
            END As TotalRedemption,
            ts.DateStarted, ts.DateEnded, ts.StartBalance, ts.EndBalance, ts.WalletReloads, a.AccountTypeID

            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN accounts a ON ts.CreatedByAID = a.AID
            WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date AND tr.SiteID = :site_id
              AND tr.CreatedByAID = :account_id 
              AND tr.Status IN(1,4)
            GROUP By tr.TransactionType, tr.TransactionSummaryID
            ORDER BY tr.TerminalID, tr.DateCreated DESC;";
        //sa.AID tr.CreatedByAID
        $param = array(':account_id'=>$account_id,
            ':site_id'=>$site_id,
            ':start_date'=>$start_date . ' ' . Mirage::app()->param['cut_off'],
            ':end_date'=>$end_date . ' ' . Mirage::app()->param['cut_off'],);

        $this->exec($sql, $param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            
            if($value['AccountTypeID'] == 17)
            {
                $IseSAFETrans = 1;
            } 
            else 
            {   
                $IseSAFETrans = 0; 
            }
            
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'TerminalCode'=>$terminalCode,
                    'TerminalID'=>$value['TerminalID'],
                    'IseSAFETrans'=>$IseSAFETrans,
                    'TotalCTransDeposit'=>'0.00',
                    'TotalCTransReload'=>'0.00',
                    'TotalCTransRedemption'=>'0.00',
                    'eWalletDeposits'=>'0.00',
                    'eWalletWithdrawals'=>'0.00',
                    'StartBalance'=>'0.00',
                    'EndBalance'=>'0.00',
                    'WalletReloads'=>'0.00',
                );
            }
            $merge_array = array();
            switch ($value['TransactionType']) 
            {
                case 'W':
                    if($value['TotalRedemption'] > 0)
                    {
                        $merge_array = array('TotalCTransRedemption'=>$value['TotalRedemption']);
                    }
                    break;
                case 'D':
                    $merge_array = array('TotalCTransDeposit'=>$value['TotalDeposit'], 'StartBalance' =>$value['StartBalance'],
                                       'EndBalance' =>$value['EndBalance'], 'WalletReloads' =>$value['WalletReloads']);
                    break;
                case 'R':
                    $merge_array = array('TotalCTransReload'=>$value['TotalReload']);
                    break;
            }
            $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
        }
        
        foreach ($new_result as $key => $value) 
        {
            $eWalletDeposits = $eWalletTransModel->getDepositSumPerVCashierPerTerminal($start_date, $end_date, $site_id, $account_id,$value['TransactionSummaryID'],$value['TerminalID']);
            $eWalletWithdrawals = $eWalletTransModel->getWithdrawalSumPerVCashierPerTerminal($start_date,$end_date, $site_id, $account_id,$value['TransactionSummaryID'],$value['TerminalID']);    
            $new_result[$key]['eWalletDeposits'] = $eWalletDeposits; 
            $new_result[$key]['eWalletWithdrawals'] = $eWalletWithdrawals; 
        }
        $res = array();
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        //$res = array_slice($res, $start, $limit);
        return $res;
    }   
    
    public function getTransactionSummaryPerCashierWithTerminalID($site_id,$account_id,$site_code,$terminal_id,$trans_sum_id,$start_date,$end_date,$start,$limit) 
    {
        $len = strlen($site_code) + 1;
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT tr.TransactionSummaryID, SUBSTR(t.TerminalCode,$len) AS TerminalCode, tr.TransactionType, t.TerminalType,
 
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
            
            ts.DateStarted, ts.DateEnded, ts.StartBalance, ts.WalletReloads, ts.EndBalance

            FROM transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
            INNER JOIN accounts a ON ts.CreatedByAID = a.AID
            WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date 
              AND tr.CreatedByAID = :account_id AND tr.SiteID = :site_id 
              AND tr.Status IN(1,4) AND tr.TerminalID=:terminal_id AND tr.TransactionSummaryID=:trans_sum_id
            GROUP By tr.TransactionType, tr.TransactionSummaryID
            ORDER BY tr.TerminalID, tr.DateCreated DESC;";
        //sa.AID tr.CreatedByAID
        $param = array(':account_id'=>$account_id,
            ':site_id'=>$site_id,
            ':start_date'=>$start_date . ' ' . Mirage::app()->param['cut_off'],
            ':end_date'=>$end_date . ' ' . Mirage::app()->param['cut_off'],
            ':terminal_id'=>$terminal_id,
            ':trans_sum_id'=>$trans_sum_id);

        $this->exec($sql, $param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
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
                    'WGenesis'=>'0.00',
                    'StartBalance'=>'0.00',
                    'WalletReloads'=>'0.00',
                    'EndBalance'=>'0.00',
                );
            }
            $merge_array = array();
            switch ($value['TransactionType']) 
            {
                case 'W':
                    if($value['RedemptionCashier'] > 0)
                    {
                        $merge_array = array('WCashier'=>$value['RedemptionCashier']);
                    }
                    if($value['RedemptionGenesis'] > 0)
                    {
                        $merge_array = array('WGenesis'=>$value['RedemptionGenesis']);
                    }
                    break;
                case 'D':
                    $merge_array = array('DCash'=>$value['DepositCash'], 'DTicket'=>$value['DepositTicket'],'DCoupon'=>$value['DepositCoupon'],
                           'StartBalance' => $value['StartBalance'], 'WalletReloads' => $value['WalletReloads'], 'EndBalance' => $value['EndBalance']);
                     break;
                case 'R':
                    $merge_array = array('RCash'=>$value['ReloadCash'],'RTicket'=>$value['ReloadTicket'],'RCoupon'=>$value['ReloadCoupon']);
                     break;
            }
            $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
        }
        $res = array();
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        //$res = array_slice($res, $start, $limit);
        return $res;
    }    
    
    public function getTransactionSummaryPerCashierTotals($site_id,$site_code,$account_id,$start_date,$end_date) 
    {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.StackerSummaryID, tr.PaymentType, t.TerminalType,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  from transactiondetails  tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID  WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date AND 
            tr.SiteID = :site_id AND tr.CreatedByAID = :account_id AND tr.Status IN(1,4) 
            group by tr.TransactionType,tr.TransactionSummaryID order by tr.TerminalID,tr.DateCreated DESC";
        $param = array(':account_id'=>$account_id,
                ':site_id'=>$site_id,
                ':start_date'=>$start_date . ' ' . $cutoff_time,
                ':end_date'=>$end_date . ' ' . $cutoff_time,);
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
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
            
            if($value['StackerSummaryID'] == null || $value['StackerSummaryID'] == '')
            {
                if($value['PaymentType'] == 1)
                {
                    switch ($value['TransactionType']) 
                    {
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
                else
                {
                    switch ($value['TransactionType']) 
                    {
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
            else
            {
                $sql2 = "SELECT Deposit, Reload, TicketDeposit, TicketReload, Withdrawal FROM stackermanagement.stackersummary WHERE StackerSummaryID = :stackersummaryid;";
                $param2 = array(':stackersummaryid'=>$value['StackerSummaryID']);
                $this->exec2($sql2,$param2);
                $result2 = $this->findAll2();
                
                foreach ($result2 as $value2) 
                {
                    $ticketreload = $value2['TicketReload'];
                    $ticketdeposit = $value2['TicketDeposit'];
                    $reload = $value2['Reload'];
                    $deposit = $value2['Deposit'];
                    $ticketwithdraw = $value2['Withdrawal'];
                }
                
                if($value['TransactionType'] == 'W')
                {
                    $merge_arrays = array('WGenesis'=>$value['amount']);
                }
                else  if($value['TransactionType'] == 'R' && $ticketreload > 0)
                {
                    $merge_arrays = array('GenRTicket'=>$ticketreload);
                }
                else if($value['TransactionType'] == 'D' && $ticketdeposit > 0)
                {
                    $merge_arrays = array('GenDTicket'=>$ticketdeposit);
                }
                else
                {
                    if($deposit > 0 && $ticketdeposit <= 0 && $value['TransactionType'] == 'D')
                    {
                        $merge_arrays = array('GenDCash'=>$deposit);
                    }
                    else if($reload > 0 && $ticketreload <= 0 && $value['TransactionType'] == 'R')
                    {
                        $merge_arrays = array('GenRCash'=>$reload);
                    }
                    else
                    {
                        if($value['PaymentType'] == 1)
                        {
                            switch ($value['TransactionType']) 
                            {
                                case 'D':
                                    $merge_arrays = array('RegDCash'=>$value['amount']);
                                    break;
                                case 'R':
                                    $merge_arrays = array('RegRCash'=>$value['amount']);
                                    break;
                            }
                        }
                        else
                        {
                            switch ($value['TransactionType']) 
                            {
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
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        return $res;
    }
    
    public function _getTransactionSummaryPerCashierTotals($site_id,$site_code,$account_id,$start_date,$end_date) 
    {
        Mirage::loadModels(array('EWalletTransModel'));
        $eWalletTransModel = new EWalletTransModel();
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded, ts.StartBalance, ts.WalletReloads, ts.EndBalance, 
            tr.DateCreated, tr.TerminalID,tr.SiteID, tr.StackerSummaryID, tr.PaymentType, t.TerminalType,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType, SUM(tr.Amount) AS amount, ts.Option1 
            FROM transactiondetails  tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID 
            WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date AND 
            tr.SiteID = :site_id AND tr.CreatedByAID = :account_id AND tr.Status IN(1,4) 
            GROUP BY tr.PaymentType, tr.TransactionType,tr.TransactionSummaryID ORDER BY tr.TerminalID,tr.DateCreated DESC";
        $param = array(':account_id'=>$account_id,
                ':site_id'=>$site_id,
                ':start_date'=>$start_date . ' ' . $cutoff_time,
                ':end_date'=>$end_date . ' ' . $cutoff_time,);
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            
            $IseSAFETrans = $eWalletTransModel->CheckIfeSAFETrans($value['TransactionSummaryID']);
            
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'DateCreated'=>$value['DateCreated'],
                    'TerminalID'=>$value['TerminalID'],
                    'SiteID'=>$value['SiteID'],
                    'TerminalCode'=>$terminalCode,
                    'IseSAFETrans'=>$IseSAFETrans,
                    'LoyaltyCard'=>$value['Option1'],
                    'DCash'=>'0.00',
                    'DTicket'=>'0.00',
                    'DCoupon'=>'0.00',
                    'RCash'=>'0.00',
                    'RTicket'=>'0.00',
                    'RCoupon'=>'0.00',
                    'WCashier'=>'0.00',
                    'WGenesis'=>'0.00',
                    'StartBalance'=>'0.00',
                    'WalletReloads'=>'0.00',
                    'EndBalance'=>'0.00',
                );
            }
            $merge_array = array();
            
            if($value['StackerSummaryID'] == null || $value['StackerSummaryID'] == '')
            {
                if($value['PaymentType'] == 1)
                {
                    switch ($value['TransactionType']) 
                    {
                        case 'W':
                            $merge_array = array('WCashier'=>$value['amount']);
                            break;
                        case 'D':
                            $merge_array = array('DCash'=>$value['amount']);
                            break;
                        case 'R':
                            $merge_array = array('RCash'=>$value['amount']);
                            break;
                    }
                }
                else
                {
                    switch ($value['TransactionType']) 
                    {
                        case 'W':
                            $merge_array = array('WCashier'=>$value['amount']);
                            break;
                        case 'D':
                            $merge_array = array('DCoupon'=>$value['amount']);
                            break;
                        case 'R':
                            $merge_array = array('RCoupon'=>$value['amount']);
                            break;
                    }
                }
                switch ($value['TransactionType']) 
                {
                    case 'D':
                        $merge_array['StartBalance'] = $value['StartBalance'];
                        $merge_array['WalletReloads'] = $value['WalletReloads'];
                        $merge_array['EndBalance'] = $value['EndBalance'];
                        break;
                }
                $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
            }
            else
            {
                $sql2 = "SELECT Deposit, Reload, TicketDeposit, TicketReload, Withdrawal FROM stackermanagement.stackersummary WHERE StackerSummaryID = :stackersummaryid;";
                $param2 = array(':stackersummaryid'=>$value['StackerSummaryID']);
                $this->exec2($sql2,$param2);
                $result2 = $this->findAll2();
                
                foreach ($result2 as $value2) 
                {
                    $ticketreload = $value2['TicketReload'];
                    $ticketdeposit = $value2['TicketDeposit'];
                    $reload = $value2['Reload'];
                    $deposit = $value2['Deposit'];
                    $ticketwithdraw = $value2['Withdrawal'];
                }
                
                if($value['TransactionType'] == 'W')
                {
                    $merge_arrays = array('WGenesis'=>$value['amount']);
                }
                else  if($value['TransactionType'] == 'R' && $ticketreload > 0)
                {
                    $merge_arrays = array('RTicket'=>$ticketreload);
                }
                else if($value['TransactionType'] == 'D' && $ticketdeposit > 0)
                {
                    $merge_arrays = array('DTicket'=>$ticketdeposit);
                }
                else
                {
                    if($deposit > 0 && $ticketdeposit <= 0 && $value['TransactionType'] == 'D')
                    {
                        $merge_arrays = array('DCash'=>$deposit);
                    }
                    else if($reload > 0 && $ticketreload <= 0 && $value['TransactionType'] == 'R')
                    {
                        $merge_arrays = array('RCash'=>$reload);
                    }
                    else
                    {
                        if($value['PaymentType'] == 1)
                        {
                            switch ($value['TransactionType']) 
                            {
                                case 'D':
                                    $merge_arrays = array('DCash'=>$value['amount']);
                                    break;
                                case 'R':
                                    $merge_arrays = array('RCash'=>$value['amount']);
                                    break;
                            }
                        }
                        else
                        {
                            switch ($value['TransactionType']) 
                            {
                                case 'D':
                                    $merge_arrays = array('DCoupon'=>$value['amount']);
                                    break;
                                case 'R':
                                    $merge_arrays = array('RCoupon'=>$value['amount']);
                                    break;
                            }
                        }
                    }
                }
                $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_arrays);   
            }
        }
        $res = array();
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        return $res;
    }
    
    public function getTransactionSummaryPerCashierWithTerminalIDTotals($site_id,$site_code,$account_id,$terminal_id,$trans_sum_id,$start_date,$end_date) 
    {
        $len = strlen($site_code) + 1;

        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, tr.StackerSummaryID, tr.PaymentType, t.TerminalType,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  FROM transactiondetails  tr FORCE INDEX(IX_transactiondetails_DateCreated) 
            INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID 
            INNER JOIN terminals t ON t.TerminalID = tr.TerminalID  WHERE tr.DateCreated >= :start_date AND tr.DateCreated < :end_date AND 
            tr.SiteID = :site_id AND tr.CreatedByAID = :account_id AND tr.Status IN(1,4) AND tr.TerminalID=:terminal_id AND tr.TransactionSummaryID=:trans_sum_id 
            GROUP BY tr.TransactionType,tr.TransactionSummaryID ORDER BY tr.TerminalID,tr.DateCreated DESC";
        $param = array(':account_id'=>$account_id,
                ':site_id'=>$site_id,
                ':start_date'=>$start_date . ' ' . $cutoff_time,
                ':end_date'=>$end_date . ' ' . $cutoff_time,
                ':terminal_id'=>$terminal_id,
                ':trans_sum_id'=>$trans_sum_id);
        $this->exec($sql,$param);
        $result = $this->findAll();
        
        $new_result = array();
        foreach($result as $value) 
        {
            if($value['TerminalType'] == 1)
            {
                $terminalCode = 'G'.$value['TerminalCode'];
            }
            else
            {
                $terminalCode = $value['TerminalCode']; 
            }
            if(!isset($new_result[$value['TransactionSummaryID']])) 
            {
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
            
            if($value['StackerSummaryID'] == null || $value['StackerSummaryID'] == '')
            {
                if($value['PaymentType'] == 1)
                {
                    switch ($value['TransactionType']) 
                    {
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
                else
                {
                    switch ($value['TransactionType']) 
                    {
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
            else
            {
                $sql2 = "SELECT Deposit, Reload, TicketDeposit, TicketReload, Withdrawal FROM stackermanagement.stackersummary WHERE StackerSummaryID = :stackersummaryid;";
                $param2 = array(':stackersummaryid'=>$value['StackerSummaryID']);
                $this->exec2($sql2,$param2);
                $result2 = $this->findAll2();
                
                foreach ($result2 as $value2) 
                {
                    $ticketreload = $value2['TicketReload'];
                    $ticketdeposit = $value2['TicketDeposit'];
                    $reload = $value2['Reload'];
                    $deposit = $value2['Deposit'];
                    $ticketwithdraw = $value2['Withdrawal'];
                }
                
                if($value['TransactionType'] == 'W')
                {
                    $merge_arrays = array('WGenesis'=>$value['amount']);
                }
                else  if($value['TransactionType'] == 'R' && $ticketreload > 0)
                {
                    $merge_arrays = array('GenRTicket'=>$ticketreload);
                }
                else if($value['TransactionType'] == 'D' && $ticketdeposit > 0)
                {
                    $merge_arrays = array('GenDTicket'=>$ticketdeposit);
                }
                else
                {
                    if($deposit > 0 && $ticketdeposit <= 0 && $value['TransactionType'] == 'D')
                    {
                        $merge_arrays = array('GenDCash'=>$deposit);
                    }
                    else if($reload > 0 && $ticketreload <= 0 && $value['TransactionType'] == 'R')
                    {
                        $merge_arrays = array('GenRCash'=>$reload);
                    }
                    else
                    {
                        if($value['PaymentType'] == 1)
                        {
                            switch ($value['TransactionType']) 
                            {
                                case 'D':
                                    $merge_arrays = array('RegDCash'=>$value['amount']);
                                    break;
                                case 'R':
                                    $merge_arrays = array('RegRCash'=>$value['amount']);
                                    break;
                            }
                        }
                        else
                        {
                            switch ($value['TransactionType']) 
                            {
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
        foreach($new_result as $value) 
        {
            $res[] = $value;
        }
        return $res;
    }
}