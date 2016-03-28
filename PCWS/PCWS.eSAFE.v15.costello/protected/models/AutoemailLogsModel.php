<?php

class AutoemailLogsModel extends CFormModel
{
   
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }

    public function getdetails($mid, $transtype) {
        
        $sql = "SELECT e.LoyaltyCardNumber,e.StartDate,e.EwalletTransID,t.TerminalCode, s.SiteName,s.POSAccountNo,a.Name, 
                    e.StartDate, e.EndDate ,rf.ServiceName, ts.TransactionsSummaryID,ts.DateStarted
                    FROM ewallettrans e                     
                    INNER JOIN sites s on e.SiteID = s.SiteID
                    INNER JOIN accountdetails a on a.AID = s.OwnerAID
                    INNER JOIN ref_services rf on rf.ServiceID = e.ServiceID		
                    LEFT JOIN terminals t on e.TerminalID = t.terminalid
                    LEFT JOIN transactionsummary ts on e.TransactionSummaryID = ts.TransactionsSummaryID
                    where e.MID = :mid AND e.Transtype = :transtype
                    order by e.EndDate desc limit 1";
        $command = $this->connection->createCommand($sql);
        $command->bindValues(array(':mid'=>$mid, ':transtype'=>$transtype));
        $result = $command->queryRow();
        return $result;
    
    }
    

    public function getValues($transsumid, $serviceid ){
        $sql = "SELECT ts.TransactionsSummaryID, ts.DateStarted, ts.DateEnded, ts.StartBalance,
                ts.EndBalance, ts.WalletReloads, tsl.GenesisWithdrawal, t.TerminalCode,
                s.SiteName, s.POSAccountNo, a.Name,rf.ServiceName
                FROM transactionsummary ts
                INNER JOIN terminals t on ts.TerminalID = t.terminalid
                INNER JOIN sites s on ts.SiteID = s.SiteID
                INNER JOIN accountdetails a on a.AID = s.OwnerAID
                INNER JOIN ref_services rf on rf.ServiceID = :serviceid
                LEFT JOIN transactionsummarylogs tsl on ts.TransactionsSummaryID = tsl.TransactionSummaryID
                WHERE TransactionsSummaryID =:transsumid";
        $command = $this->connection->createCommand($sql);
        $command->bindValues(array(':serviceid'=>$serviceid,':transsumid'=>$transsumid));
        $result = $command->queryRow();       
        return $result;
    }

    

    public function insert ($reportype,$methodtype,$templateid, $serviceid, $startbalance, $endbalance, $totalLoads, $widtdrawnAmount, $reloads, 
                        $genesiswithdrawals ,$netwins, $sitename, $terminalcode, $POSaccount, $login, $accountName,  
                        $servicename, $refID,$timeIn,$timeOut,$transdatetime)
      {
        $startTrans = $this->connection->beginTransaction();
        
            
            try {
                $sql = "INSERT INTO autoemaillogs (ReportType, MethodType, TemplateID, ServiceID, StartBalance, EndBalance, TotalLoads, WithdrawnAmount, ReloadedAmount, 
                    GenesisWithdrawals, NetWins, SiteName, TerminalCode, POSAccountNo, LoginUsername, AccountName, ServiceName, ReferenceID , 
                    TimeIn, TimeOut,TransDateTime, DateCreated) 
                    VALUES (:reporttype , :methodtype , :templateid,:serviceID, :startbalance, :endbalance, :totalloads, :withdrawnamount, :reloadedamount,
                    :genesiswithdrawals, :netwins, :sitename, :terminalcode, :pos, :login, :accountname,  :servicename, 
                    :referenceid,:timein, :timeout, :transdatetime,NOW(6))";
                $param = array(
                    ':reporttype'=>$reportype,':methodtype'=>$methodtype,':templateid'=>$templateid,
                    ':serviceID'=>$serviceid,':startbalance'=>$startbalance,
                    ':endbalance'=>$endbalance,':totalloads'=>$totalLoads,
                    ':withdrawnamount'=>$widtdrawnAmount,':reloadedamount'=>$reloads,
                    ':genesiswithdrawals'=>$genesiswithdrawals,':netwins'=>$netwins,
                    ':sitename'=>$sitename,':terminalcode'=>$terminalcode,
                    ':pos'=>$POSaccount,':login'=>$login,
                    ':accountname'=>$accountName,
                    ':servicename'=>$servicename,':referenceid'=>$refID,
                    ':timein'=>$timeIn,':timeout'=>$timeOut,
                    ':transdatetime'=>$transdatetime                  
                    );
                $command = $this->connection->createCommand($sql);
                $command->bindValues($param);
                $command->execute();
                
                try {

                    $startTrans->commit();
                    return 1;
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
                
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        
    }

}
?>
