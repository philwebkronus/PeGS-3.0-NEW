<?php

/**
 * Date Created 11 7, 11 3:55:18 PM <pre />
 * Description of TransactionSummaryModel
 * @author Bryan Salazar
 */
class TransactionSummaryModel extends MI_Model{
    
    public function insert($site_id,$terminal_id,$amount,$acctid) {
        $sql = 'INSERT INTO transactionsummary (SiteID, TerminalID, Deposit, DateStarted, DateEnded, CreatedByAID) VALUES ' . 
                '(:site_id, :terminal_id, :amount, now_usec(), \'0\', :acctid)';
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
        $sql = 'UPDATE transactionsummary SET Withdrawal = :amount,DateEnded = now_usec()  WHERE TransactionsSummaryID = :trans_summary_id';
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
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  from transactiondetails  tr 
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
        foreach($result as $value) {
            if(!isset($new_result[$value['TransactionSummaryID']])) {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'DateCreated'=>$value['DateCreated'],
                    'TerminalID'=>$value['TerminalID'],
                    'SiteID'=>$value['SiteID'],
                    'TerminalCode'=>$value['TerminalCode'],
                    'LoyaltyCard'=>$value['Option1'],
                    'Withdrawal'=>'0.00',
                    'Deposit'=>'0.00',
                    'Reload'=>'0.00'
                );
            }
            $merge_array = array();
            switch ($value['TransactionType']) {
                case 'W':
                    $merge_array = array('Withdrawal'=>$value['amount']);
                    break;
                case 'D':
                    $merge_array = array('Deposit'=>$value['amount']);
                    break;
                case 'R':
                    $merge_array = array('Reload'=>$value['amount']);
                    break;
            }
            $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
           
        }
        $res = array();
        foreach($new_result as $value) {
            $res[] = $value;
        }
        $res = array_slice($res, $start, $limit);
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
        
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID,
            SUBSTR(t.TerminalCode,$len) as TerminalCode,tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  from transactiondetails  tr 
            inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
            inner join terminals t on t.TerminalID = tr.TerminalID 
            where tr.CreatedByAID = :account_id AND tr.SiteID = :site_id AND tr.DateCreated >= :start_date and tr.DateCreated < :end_date and tr.Status IN (1,4)
            group by tr.TransactionType,tr.TransactionSummaryID order by tr.TerminalID,tr.DateCreated Desc";
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
            if(!isset($new_result[$value['TransactionSummaryID']])) {
                $new_result[$value['TransactionSummaryID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'DateStarted'=>$value['DateStarted'],
                    'DateEnded'=>$value['DateEnded'],
                    'DateCreated'=>$value['DateCreated'],
                    'TerminalID'=>$value['TerminalID'],
                    'SiteID'=>$value['SiteID'],
                    'TerminalCode'=>$value['TerminalCode'],
                    'LoyaltyCard'=>$value['Option1'],
                    'Withdrawal'=>'0.00',
                    'Deposit'=>'0.00',
                    'Reload'=>'0.00'
                );
            }
            $merge_array = array();
            switch ($value['TransactionType']) {
                case 'W':
                    $merge_array = array('Withdrawal'=>$value['amount']);
                    break;
                case 'D':
                    $merge_array = array('Deposit'=>$value['amount']);
                    break;
                case 'R':
                    $merge_array = array('Reload'=>$value['amount']);
                    break;
            }
            $new_result[$value['TransactionSummaryID']] = array_merge($new_result[$value['TransactionSummaryID']],$merge_array);
           
        }
        $res = array();
        foreach($new_result as $value) {
            $res[] = $value;
        }
        $res = array_slice($res, $start, $limit);
        return $res;
        
    }    
    
    public function getTransactionSummaryPerCashierTotals($site_id,$account_id,$start_date,$end_date) {
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID,
            tr.TransactionType,sum(tr.Amount) AS amount, ts.Option1  from transactiondetails  tr 
            inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
            inner join terminals t on t.TerminalID = tr.TerminalID 
            where tr.CreatedByAID = :account_id AND tr.SiteID = :site_id AND tr.DateCreated >= :start_date and tr.DateCreated < :end_date and tr.Status IN (1,4) 
            group by tr.TransactionType,tr.TransactionSummaryID order by tr.TerminalID,tr.DateCreated Desc";
        
        $param = array(
            ':account_id'=>$account_id,
            ':site_id'=>$site_id,
            ':start_date'=>$start_date . ' ' . Mirage::app()->param['cut_off'],
            ':end_date'=>$end_date . ' ' . Mirage::app()->param['cut_off'],
        );
        $this->exec($sql, $param);
        $result = $this->findAll();
//        echo $sql;
//        debug($param); exit;
        $new_result = array();
        $total_deposit = 0;
        $total_reload = 0;
        $total_withdraw = 0;        
        foreach($result as $value) {
            switch ($value['TransactionType']) {
                case 'W':
                    $total_withdraw+=$value['amount'];
                    break;
                case 'D':
                    $total_deposit+=$value['amount'];
                    break;
                case 'R':
                    $total_reload+=$value['amount'];
                    break;
            }
           
        }
        
//        $sql = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID, " . 
//                 "if(ts.DateStarted < '$start_date $cutoff_time', ts.Deposit = 0,ts.Deposit) as Deposit," . 
//                 "ts.Reload,ts.Withdrawal,tr.DateCreated from transactiondetails tr left join transactionsummary ts " . 
//                 "on ts.TransactionsSummaryID = tr.TransactionSummaryID inner join terminals t on t.TerminalID = tr.TerminalID " . 
//                 "INNER JOIN siteaccounts sa ON sa.SiteID = ts.SiteID " .   
//                 "where sa.AID = :account_id AND tr.DateCreated > :start_date and tr.DateCreated <= :end_date " . 
//                 "group by ts.TransactionsSummaryID order by tr.TerminalID,tr.DateCreated Desc";
//        
//        $param = array(
//            ':account_id'=>$account_id,
//            ':start_date'=>$start_date . ' ' . Mirage::app()->param['cut_off'],
//            ':end_date'=>$end_date . ' ' . Mirage::app()->param['cut_off'],
//        );
//        $this->exec($sql, $param);
//        $result = $this->findAll();
//        $total_deposit = 0;
//        $total_reload = 0;
//        $total_withdraw = 0;
//        foreach($result as $row) {
//            $total_deposit+=$row['Deposit'];
//            $total_reload+=$row['Reload'];
//            $total_withdraw+=$row['Withdrawal'];
//        }
        return array('totaldeposit'=>$total_deposit,'totalreload'=>$total_reload,'totalwithdrawal'=>$total_withdraw);
//        $sql = 'SELECT SUM(ts.Deposit) AS totaldeposit, SUM(ts.Reload) AS totalreload, SUM(ts.Withdrawal) AS totalwithdrawal FROM transactionsummary ts ' . 
//                'JOIN terminals tr ON ts.TerminalID = tr.TerminalID ' . 
//                'INNER JOIN siteaccounts sa ON sa.SiteID = ts.SiteID '.
//                'JOIN sites st ON ts.SiteID = st.SiteID ' . 
//                'WHERE sa.AID = :account_id AND DATE_FORMAT(ts.DateStarted,\'%Y-%m-%d %H:%i:%s\') > DATE_FORMAT(:start_date,\'%Y-%m-%d %H:%i:%s\') AND ' . 
//                'DATE_FORMAT(ts.DateStarted,\'%Y-%m-%d %H:%i:%s\') <= DATE_FORMAT(:end_date,\'%Y-%m-%d %H:%i:%s\')';        
//        
//        $param = array(
//            ':account_id'=>$account_id,
//            ':start_date'=>$start_date,
//            ':end_date'=>$end_date
//        );
//        $this->exec($sql, $param);
//        return $this->find();
    }
}