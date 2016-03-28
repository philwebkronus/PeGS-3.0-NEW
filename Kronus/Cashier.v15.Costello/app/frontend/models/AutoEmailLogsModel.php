<?php

class AutoEmailLogsModel extends MI_Model {

    public function getTransactionDetails($trans_summary_id) {
        $sql = "SELECT 
                ts.TransactionsSummaryID, ts.TerminalID , ts.SiteID, ts.MID, ts.LoyaltyCardNumber, ts.Deposit, ts.Reload, ts.Withdrawal, ts.DateStarted, ts.DateEnded , 
                s.SiteName, s.SiteCode,s.POSAccountNo,
                t.TerminalCode,t.TerminalName,
                a.Name
                FROM transactionsummary ts
		INNER JOIN sites s 
                ON s.SiteID = ts.SiteID 
                INNER JOIN terminals t 
                ON t.TerminalID = ts.TerminalID 
                INNER JOIN accountdetails a 
                ON a.AID = s.OwnerAID  
                WHERE ts.TransactionsSummaryID = :trans_id;";

        $param = array(
            ':trans_id' => $trans_summary_id,
        );
        $this->exec($sql, $param);
        $result = $this->find();
        
        if($result){
             return $result;           
        }
        else{
              return 0;    
        }

    }

    public function insert($service_id, $service_name, $totalLoads, $widtdrawnAmount, $netwins, $terminalCode, $siteName, $POSaccount, $login, $accountName, $timeIn, $timeOut, $lastGamePlayed, $refID) {
        try {

            $sql = 'INSERT INTO autoemaillogs (ReportType, MethodType, TemplateID, Status, ServiceID, ServiceName, TotalLoads,WithdrawnAmount, 
                            Netwins, TerminalCode, SiteName, POSAccountNo, LoginUsername, AccountName, TimeIn, TimeOut, DateCreated, TransDateTime, ReferenceID) 
                            VALUES (4 , 1 , 4, 0, :service_id, :service_name, :total_loads, :widthdrawn_amount, :netwins, :terminal_code,
                            :site_name, :pos, :login, :account_name, :time_in, :time_out, NOW(6), :trans_date_time, :refID)';

            $param = array(':service_id' => $service_id,
                ':service_name' => $service_name,
                ':total_loads' => $totalLoads,
                ':widthdrawn_amount' => $widtdrawnAmount,
                ':netwins' => $netwins,
                ':terminal_code' => $terminalCode,
                ':site_name' => $siteName,
                ':pos' => $POSaccount,
                ':login' => $login,
                ':account_name' => $accountName,
                ':time_in' => $timeIn,
                ':time_out' => $timeOut,
                ':refID' => $refID,
                ':trans_date_time' => $timeOut 
            );


            $result = $this->exec($sql, $param);
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

}

?>