<?php
/**
 * will be used by autoemail reports 
 * such as big reload, big winnings,
 * daily total buy in and BCF less
 * than 20%
 * by: Lea Tuazon
 * Date : November 3, 2011
 */

class Autoemail {
    
    private $_connectionString;
    private $_stmt;
    public $_dbh;
    public $_mbdbh;
    
    public function __construct( $connectionString )
    {
         $this->_connectionString = explode( ",", $connectionString );

    }
    
    public function open()
    {
            $connectionstring1 = $this->_connectionString[0];
            $connectionstring2 = $this->_connectionString[1];
            $connectionstring3 = $this->_connectionString[2];  
            $this->_dbh = new PDO( $connectionstring1, $connectionstring2, $connectionstring3);
            if($this->_dbh)
               return true;
            else
                return false;
    }
    
    public function close()
    {
        if ($this->_dbh)
            $this->_dbh= NULL;
    }
    //get last sched when the cron job executed
    public function getcronsched($field)
    {
        
        $stmt = "SELECT ".$field." FROM autoemailschedule";
        $sth = $this->_dbh->prepare($stmt);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
        
    }
    // get the biggest reload amount from transactiondetails table - step 1
    public function getbigreload($cdateforprocess)
    {
        $stmt = "SELECT MAX(td.Amount) as Amount,now_usec() as querytime FROM transactiondetails td 
            INNER JOIN sites s ON s.SiteID = td.SiteID WHERE td.TransactionType = 'R' 
            AND td.DateCreated > ? AND s.isTestSite = 0";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1,$cdateforprocess);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;                 
    }
    
    // get the biggest reload amount from transactiondetails table - step 2
    public function getallbigreload($cdateforprocess,$camount)
    {
        //query to get big reload within specified date and time
        
//        $stmt= "SELECT tr.TransactionRequestLogID, td.SiteID, td.TerminalID, 
//            td.TransactionType,td.Amount as BigReload, 
//            IF(ISNULL(s.POSAccountNo), '0000000000', s.POSAccountNo) as POS,
//            s.SiteName,a.Name,t.TerminalCode,tr.StartDate,tr.EndDate,td.ServiceID,
//            rs.ServiceName FROM transactiondetails td INNER JOIN transactionrequestlogs tr 
//            ON td.TransactionReferenceId = tr.TransactionReferenceId INNER JOIN sites s 
//            ON s.SiteID = td.SiteID INNER JOIN terminals t 
//            ON t.TerminalID = td.TerminalID INNER JOIN accountdetails a 
//            ON a.AID = s.OwnerAID  INNER JOIN ref_services rs 
//            ON rs.ServiceID = td.ServiceID  
//            WHERE td.TransactionType = 'R' 
//            AND td.DateCreated >  ? 
//            AND s.isTestSite = 0 and td.Amount = ?";
        
        //modified on 04/19/12 15:30
        $stmt= "SELECT tr.TransactionRequestLogID, td.SiteID, td.TerminalID, 
                td.TransactionType,td.Amount as BigReload, 
                IF(ISNULL(s.POSAccountNo), '0000000000', s.POSAccountNo) as POS,
                s.SiteName,a.Name,t.TerminalCode,tr.StartDate,tr.EndDate,td.ServiceID,
                rs.ServiceName FROM transactiondetails td USE INDEX (IX_transactiondetails_DateCreated)
                INNER JOIN transactionrequestlogs tr ON td.TransactionReferenceId = tr.TransactionReferenceId 
                INNER JOIN sites s ON s.SiteID = td.SiteID 
                INNER JOIN terminals t ON t.TerminalID = td.TerminalID 
                INNER JOIN accountdetails a ON a.AID = s.OwnerAID  
                INNER JOIN ref_services rs ON rs.ServiceID = td.ServiceID  
                WHERE td.TransactionType = 'R' AND td.DateCreated > '".$cdateforprocess."%'
                 AND s.isTestSite = 0 and td.Amount = ?";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1,$camount);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);  
        return $result;  
    }
    
    //insert getbigreload() result to ....    
    public function insertbigreload($cstart,$cend, $csiteID, $csitename, $cterminalID, $cterminalcode, $cdepositamt, $cwithdrawamt, $cnetwin, $cserviceID, $ctranstype)
    {
        $this->_dbh->beginTransaction();
        $sth = $this->_dbh->prepare("INSERT INTO autoemail(TimeIn, TimeOut, SiteID, 
            SiteName, TerminalID, TerminalCode, TotalDeposit, WithdrawAmount, 
            NetWinnings, ServiceID, TransactionType) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
        $sth->bindParam(1, $cstart);
        $sth->bindParam(2, $cend);
        $sth->bindParam(3, $csiteID);
        $sth->bindParam(4, $csitename);
        $sth->bindParam(5, $cterminalID);
        $sth->bindParam(6, $cterminalcode);
        $sth->bindParam(7, $cdepositamt);
        $sth->bindParam(8, $cwithdrawamt);
        $sth->bindParam(9, $cnetwin);
        $sth->bindParam(10, $cserviceID);
        $sth->bindParam(11, $ctranstype);
        if($sth->execute())
        {
            $this->_dbh->commit();
            return 1;
        }
        else
        {
            $this->_dbh->rollback();
            return 0;
        }
    }
        
    //get big winnings 
    public function getdeposits($csummaryid)
    {
       $stmt = "SELECT Deposit,DateStarted FROM transactionsummary 
           WHERE TransactionsSummaryID = ?";    
       $sth = $this->_dbh->prepare($stmt);
       $sth->bindParam(1,$csummaryid);       
       $sth->execute();
       $result = $sth->fetch(PDO::FETCH_LAZY);         
       return $result;
    }
    
    //for big winnings
    public function getreload($csummaryid)
    {
      
      $stmt = "SELECT Reload FROM transactionsummary WHERE TransactionsSummaryID = ?";
       $sth = $this->_dbh->prepare($stmt);
       $sth->bindParam(1,$csummaryid);
       $sth->execute();
       $result = $sth->fetch(PDO::FETCH_LAZY);   
       return $result;           
    }
    
    //for Big Winnings step 1
    public function getredeem($cdateforprocess)
    { 
        $stmt = "SELECT MAX(td.Amount) as Amount, now_usec() as querytime 
            FROM transactiondetails td 
            INNER JOIN sites s ON s.SiteID = td.SiteID 
            WHERE td.TransactionType = 'W' 
            AND td.DateCreated > ?
            AND s.isTestSite = 0";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1,$cdateforprocess);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;    
    }
 
   // for Big winnings step 2
    public function getallbigredeem($cdateforprocess,$camount)
    {
      $stmt = "SELECT tr.TransactionRequestLogID, td.SiteID, td.TerminalID, tr.MID, 
          td.TransactionType,td.Amount as Redeem, 
          IF(ISNULL(s.POSAccountNo), '0000000000', s.POSAccountNo) as POS,
            s.SiteName,a.Name,t.TerminalCode,tr.StartDate,tr.EndDate,td.ServiceID,
            td.DateCreated as EndDate,td.TransactionSummaryID,rs.ServiceName,rs.Code, tr.UserMode 
            FROM transactiondetails td 
            INNER JOIN transactionrequestlogs tr 
            ON td.TransactionReferenceId = tr.TransactionReferenceId 
            INNER JOIN sites s 
            ON s.SiteID = td.SiteID 
            INNER JOIN terminals t 
            ON t.TerminalID = td.TerminalID 
            INNER JOIN accountdetails a 
            ON a.AID = s.OwnerAID  
            INNER JOIN ref_services rs 
            ON rs.ServiceID = td.ServiceID  
            WHERE td.TransactionType = 'W' 
            AND td.DateCreated >  ? 
            AND s.isTestSite = 0 AND td.Amount = ?";      
               
      $sth = $this->_dbh->prepare($stmt);
      $sth->bindParam(1, $cdateforprocess);
      $sth->bindParam(2, $camount);
      $sth->execute();
      $result = $sth->fetchAll(PDO::FETCH_ASSOC);          
      return $result;
 
    }
        
    //for daily buy-in
    public function getdailybuyin($cdatefrom, $cdateto)
    {
        $stmt = "SELECT tr.SiteID,tr.TransactionSummaryID,tr.DateCreated, tr.TerminalID,
                    t.TerminalCode as TerminalCode, tr.TransactionType, tr.Amount,
                    s.SiteName,s.POSAccountNo,s.OwnerAID,a.Name FROM transactiondetails tr                     
                    INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                    INNER JOIN sites s ON s.SiteID = tr.SiteID
                    INNER JOIN accountdetails a ON a.AID = s.OwnerAID  
                    WHERE tr.DateCreated >= ? AND tr.DateCreated <  ? AND tr.Status IN(1,4) order by tr.SiteID";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1, $cdatefrom);
        $sth->bindParam(2, $cdateto);   
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC); 
       
        return $result;     
    }
    
    public function getsitebcf()
    {
        $stmt = "SELECT sb.SiteID,acc.UserName, if(isnull(s.POSAccountNo), '0000000000', s.POSAccountNo) as POS, 
            sb.Balance, s.SiteName, det.Name, sb.MinBalance * .20 as reqbal 
            FROM sitebalance sb
                 INNER JOIN sites s ON sb.SiteID = s.SiteID
                 INNER JOIN accounts acc ON s.OwnerAID = acc.AID
                 INNER JOIN accountdetails det ON s.OwnerAID = det.AID WHERE s.isTestSite = 0
                 AND sb.WillEmailAlert = 0";
        $sth = $this->_dbh->prepare($stmt);
        $sth->execute();
        $result = $sth->fetchAll();
        return $result;
    }
    
    public function updatesiteemailalert($zsiteid)
    {
        $this->_dbh->beginTransaction();
        $sth = $this->_dbh->prepare("UPDATE  sitebalance SET WillEmailAlert = 1 WHERE SiteID = ? ");
        $sth->bindParam(1,$zsiteid);
        
        if($sth->execute())
        {
            $this->_dbh->commit();
            return 1;
        }
        else
        {
            $this->_dbh->rollback();
            return 0;
        }
        
    }
    
    //basis of cron sched
    public function updatetime($ctime,$field)
    {
        $this->_dbh->beginTransaction();
        $sth = $this->_dbh->prepare("UPDATE autoemailschedule SET ".$field."= ?");
        $sth->bindParam(1, $ctime);        
        if($sth->execute())
        {
            $this->_dbh->commit();
            return 1;
        }
        else
        {
            $this->_dbh->rollback();
            return 0;
        }
    }
    
    // being used in MGAgentBalance.php
    public function getAllAgent()
    {
        $stmt ="SELECT ServiceAgentID,ServiceAgentSessionID FROM serviceagentsessions";
        $sth = $this->_dbh->prepare($stmt);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;        
    }
    
    // being used in MGAgentBalance.php
    public function getAgentSite($cServiceAgentID)
    {
        $stmt = "SELECT SiteID FROM serviceagents WHERE ServiceAgentID = ?";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1, $cServiceAgentID); 
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }
    // being used in MGAgentBalance.php
    public function getSiteInfo($cSiteID)
    {
        $stmt = "SELECT SiteCode,POSAccountNo FROM sites WHERE SiteID = ?";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1, $cSiteID); 
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }
    
    //being used in playingbalance.php
    public function getTerminalSessionsRTG()
    {
        $stmt = "SELECT ts.TerminalID,ts.ServiceID FROM terminalsessions ts 
            INNER JOIN ref_services rs on rs.ServiceID = ts.ServiceID
            WHERE rs.ServiceName LIKE 'RTG%' ORDER BY ts.ServiceID";
        $sth = $this->_dbh->prepare($stmt);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;    
    }
    
    //being used in playingbalance.php
    public function getTerminalSessionsMG()
    {
        $stmt = "SELECT ts.TerminalID,ts.ServiceID FROM terminalsessions ts 
            INNER JOIN ref_services rs on rs.ServiceID = ts.ServiceID
            WHERE rs.ServiceName LIKE 'MG%' ORDER BY ts.ServiceID";
        $sth = $this->_dbh->prepare($stmt);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;    
    }
    
    //being used in playingbalance.php
    public function getServiceName($cserviceid)
    {
        $stmt = "SELECT ServiceName,Code FROM ref_services WHERE ServiceID = ?";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1,$cserviceid);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }
    
    public function getTerminalCode($cterminalid)
    {
        $stmt = "SELECT t.TerminalCode,s.SiteCode,s.SiteID FROM terminals t 
            INNER JOIN sites s on s.SiteID = t.SiteID WHERE t.TerminalID = ?";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1,$cterminalid);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }
    
    public function getAgentID($csiteid)
    {
        $stmt = "SELECT ServiceAgentID FROM serviceagents WHERE SiteID = ?";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1,$csiteid);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }
    
    public function getAgentSession($cagentid)
    {
        $stmt = "SELECT ServiceAgentSessionID FROM serviceagentsessions WHERE ServiceAgentID = ?";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1,$cagentid);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }
    
    public function getOCAccount($cterminalid)
    {
        $stmt = "SELECT tm.ServiceTerminalID, st.ServiceTerminalAccount FROM terminalmapping tm 
            INNER JOIN serviceterminals st on st.ServiceTerminalID = tm.ServiceTerminalID
            WHERE tm.TerminalID = ?";
        $sth= $this->_dbh->prepare($stmt);
        $sth->bindParam(1,$cterminalid);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }
    
    /*
     * Get Site BCFs for comparison on MGAgentBalance Cron
     */
    public function getsitethreshold($csiteID)
    {
        $stmt = "SELECT MinBalance FROM sitebalance WHERE SiteID = ?";
        $sth = $this->_dbh->prepare($stmt);
        $sth->bindParam(1, $csiteID);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_LAZY);
        return $result;
    }

}

?>
