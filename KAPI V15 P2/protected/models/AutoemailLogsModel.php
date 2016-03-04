<?php

class AutoemailLogsModel extends CFormModel
{
   
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    /**
     * Get AID of the <b>e-SAFE</b> Virtual Cashier in a selected site.
     * @param type $siteID
     * @return type
     */
    public function getdetails($mid, $transtype) {
        
        $sql = 'Select e.LoyaltyCardNumber,t.TerminalCode, s.SiteName,s.POSAccountNo,a.Name, 
                    e.StartDate, e.EndDate, ts.DateStarted from ewallettrans e 
                    INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = e.TransactionSummaryID 
                    INNER JOIN terminals t on e.TerminalID = t.terminalid
                    INNER JOIN sites s on e.SiteID = s.SiteID
                    INNER JOIN accountdetails a on a.AID = s.OwnerAID
                    where e.MID = :mid AND e.Transtype = :transtype order by e.EndDate desc limit 1';
        $command = $this->connection->createCommand($sql);
        $command->bindValues(array(':mid'=>$mid, ':transtype'=>$transtype));
        $result = $command->queryRow();
        
        return $result;
    
    }

    
    //---------------
    public function insert ($reportype,$methodtype,$templateid, $serviceid, $startbalance, $endbalance, $totalLoads, $widtdrawnAmount, $reloads, 
                        $genesiswithdrawals ,$netwins, $sitename, $terminalcode, $POSaccount, $login, $accountName,  
                        $servicename, $refID,$timeIn, $timeOut, $TransDateTime)
      {
        $startTrans = $this->connection->beginTransaction();
        
            
            try {
                $sql = "INSERT INTO autoemaillogs (ReportType, MethodType, TemplateID, ServiceID, StartBalance, EndBalance, TotalLoads, WithdrawnAmount, ReloadedAmount, 
                    GenesisWithdrawals, NetWins, SiteName, TerminalCode, POSAccountNo, LoginUsername, AccountName, ServiceName, ReferenceID , 
                    RunDateTime, TimeIn, TimeOut,  TransDateTime, DateCreated) 
                        VALUES (:reporttype , :methodtype , :templateid,:serviceID, :startbalance, :endbalance, :totalloads, :withdrawnamount, :reloadedamount,
                    :genesiswithdrawals, :netwins, :sitename, :terminalcode, :pos, :login, :accountname,  :servicename, 
                    :referenceid,NOW(6),:timein,:timeout,:transDateTime,NOW(6))";
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
                    ':transDateTime'=>$TransDateTime
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
