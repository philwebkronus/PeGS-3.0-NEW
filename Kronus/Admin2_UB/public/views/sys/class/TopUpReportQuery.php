<?php


/**
 * Date Created 10 3, 11 3:55:17 PM
 * Description of TopUpPDF
 * @author Bryan Salazar
 * modified by: Edson L. Perez
 */
include_once "DbHandler.class.php"; 
include_once "AppendArray.class.php"; 

class TopUpReportQuery extends DBHandler{
    public function __construct($sconectionstring)
    {
        parent::__construct($sconectionstring);
    }
    
    public function confirmation($startdate, $enddate) {
        $query = "SELECT ghc.GrossHoldConfirmationID, a.UserName, s.SiteCode, ghc.DateCreated, ghc.DateCredited, ghc.SiteRepresentative, ghc.AmountConfirmed, s.POSAccountNo " . 
                "FROM grossholdconfirmation ghc INNER JOIN accounts a ON ghc.PostedByAID = a.AID " . 
                "INNER JOIN sites s ON ghc.SiteID = s.SiteID WHERE ghc.DateCreated BETWEEN '$startdate' AND '$enddate' ORDER BY ghc.GrossHoldConfirmationID  ASC ";
        $this->prepare($query);
        $this->execute();
        return $this->fetchAllData();
    }
    
    public function replenish($startdate, $enddate) {
        $query = "SELECT r.ReplenishmentID, s.SiteCode, s.POSAccountNo, r.Amount, r.DateCredited, r.DateCreated, a.UserName FROM replenishments r " . 
                "INNER JOIN sites s ON s.SiteID = r.SiteID " . 
                "INNER JOIN accounts a ON a.AID = r.CreatedByAID WHERE r.DateCreated BETWEEN '$startdate' AND '$enddate' ORDER BY r.ReplenishmentID ASC";
        $this->prepare($query);
        $this->execute();
        return $this->fetchAllData();
    }
    
    public function bettingcredit($zownerAID, $zsiteID, $zreport) 
    {
        switch ($zreport)
        {
            case 'critical':
                switch ($zsiteID)                
                {                
                    case 'All':
                        switch ($zownerAID)
                        {
                            case 'All': // ALL OWNER
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance <= sb.MinBalance";
                                $this->prepare($query);                                
                                break;
                            
                            case $zownerAID > 0: // OWNER AND ALL ASSIGNED SITES
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance <= sb.MinBalance AND s.OwnerAID = ?";
                                $this->prepare($query);   
                                $this->bindparameter(1,$zownerAID);                                                               
                                break;
                        }
                        break;
                    case $zsiteID > 0: // SPECIFIED OWNER AND SITE
                        $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                            sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                            s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                            WHERE sb.Balance <= sb.MinBalance AND sb.SiteID = ?";                        
                        $this->prepare($query);
                        $this->bindparameter(1,$zsiteID); 
                        break;
                }
                break;
            case 'safe':
                switch ($zsiteID)
                {
                    case 'All':
                        switch ($zownerAID)
                        {
                            case 'All': // ALL OWNER
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance > sb.MinBalance ";
                                $this->prepare($query);                                
                                break;
                            
                            case $zownerAID > 0: // OWNER AND ALL ASSIGNED SITES
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance > sb.MinBalance AND s.OwnerAID = ? ";
                                $this->prepare($query);   
                                $this->bindparameter(1,$zownerAID);                                                               
                                break;
                        }
                        break;
                    case $zsiteID > 0:  // SPECIFIED OWNER AND SITE
                        $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                            sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                            s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                            WHERE sb.Balance > sb.MinBalance AND sb.SiteID = ? ";                        
                        $this->prepare($query);
                        $this->bindparameter(1,$zsiteID); 
                        break;
                }
                break;
        }        
        $this->execute();        
        return $this->fetchAllData();         
    }
    
    public function grossHolMonitoring($startdate,$enddate, $zsiteID) 
    {
        //if site's dropdown box was selected all
        if($zsiteID == '')
        {
            $query = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.TransactionSummaryID, td.SiteID, 
                      s.SiteName, td.TerminalID, s.POSAccountNo, td.TransactionType, COALESCE(td.Amount,0) AS Amount, 
                      td.DateCreated, s.SiteCode, s.POSAccountNo, td.ServiceID, td.CreatedByAID, a.UserName, sb.Balance 
                      FROM transactiondetails td FORCE INDEX(IX_transactiondetails_DateCreated)
                      INNER JOIN sitebalance sb ON sb.SiteID = td.SiteID 
                      INNER JOIN accounts a ON a.AID = td.CreatedByAID 
                      INNER JOIN sites s ON s.SiteID = td.SiteID 
                      WHERE td.DateCreated >= ? AND td.DateCreated < ? AND td.Status IN(1,4) ORDER BY td.SiteID ASC";
            $this->prepare($query);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
        }
        else
        {
            $query = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.TransactionSummaryID, td.SiteID, 
                      s.SiteName, td.TerminalID, s.POSAccountNo, td.TransactionType, COALESCE(td.Amount,0) AS Amount, 
                     td.DateCreated, s.SiteCode, s.POSAccountNo, td.ServiceID, td.CreatedByAID, a.UserName, sb.Balance
                     FROM transactiondetails td td FORCE INDEX(IX_transactiondetails_DateCreated)
                     INNER JOIN sitebalance sb ON sb.SiteID = td.SiteID  
                     INNER JOIN accounts a ON a.AID = td.CreatedByAID 
                     INNER JOIN sites s ON s.SiteID = td.SiteID 
                     WHERE td.DateCreated >= ? AND td.DateCreated < ? AND td.SiteID = ? AND td.Status IN(1,4)
                     ORDER BY td.SiteID ASC";
            $this->prepare($query);
            $this->bindparameter(1, $startdate);
            $this->bindparameter(2, $enddate);
            $this->bindparameter(3, $zsiteID);
        }
        
        $this->execute();  
        $rows1 =  $this->fetchAllData();
        $varrmerge = array();
        $ctr = 0;
        $mergedep = 0;
        $mergerel = 0;
        $mergewith = 0; 
        
        //group each transction with SiteID
        foreach($rows1 as $value) 
        {                
            if(!isset($varrmerge[$value['SiteID']])) 
            {
                 $mergedep = 0;
                 $mergerel = 0;
                 $mergewith = 0; 
                 $varrmerge[$value['SiteID']] = array(
                    'TransactionSummaryID'=>$value['TransactionSummaryID'],
                    'TerminalID'=>$value['TerminalID'],
                    'SiteID'=>$value['SiteID'],
                    'SiteCode'=>$value['SiteCode'],
                    'SiteName'=>$value['SiteName'],
                    'POSAccountNo'=>$value['POSAccountNo'],
                    'Balance'=>$value['Balance'],
                    'Withdrawal'=>$mergewith,
                    'Deposit'=>$mergedep,
                    'Reload'=>$mergerel
                 ); 
            }
            $trans = array();
            switch ($value['TransactionType']) 
            {
                case 'W':
                    $mergewith = $mergewith + $value['Amount'];
                    $trans = array('Withdrawal'=>$mergewith);
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
        

        foreach($varrmerge as $key => $trans) {
                $vsiteID = $trans['SiteID'];
                // GET SUM of MANUAL REDEMPTION
                $query2 = "SELECT SUM(ActualAmount) AS ActualAmount FROM manualredemptions " . 
                    "WHERE SiteID = ? AND TransactionDate >= ? AND TransactionDate < ?";
                $this->prepare($query2);
                $this->bindparameter(1, $vsiteID); // $key is site id
                $this->bindparameter(2, $startdate);
                $this->bindparameter(3, $enddate);
                $this->execute();  
                $rows2 =  $this->fetchData();
                $varrmerge[$key]['ActualAmount'] = '0.00';
                if($rows2['ActualAmount'])
                    $varrmerge[$key]['ActualAmount'] = $rows2['ActualAmount'];
                
                // GET PickUpTag per site id
                $query3 = "SELECT PickUpTag FROM sitebalance WHERE SiteID = ?";
                $this->prepare($query3);
                $this->bindparameter(1, $vsiteID); // $key is site id
                $this->execute();
                $row3 =  $this->fetchData();
                
                if($row3['PickUpTag'] == 1) {
                    $varrmerge[$key]['PickUpTag'] = "Metro Manila";
                } else {
                    $varrmerge[$key]['PickUpTag'] = "Provincial";
                }
                
                $query4 = "SELECT SiteID FROM grossholdconfirmation WHERE SiteID = ? AND DateCredited = ?";
                $this->prepare($query4);
                $this->bindparameter(1, $vsiteID); // $key is site id   
                $this->bindparameter(2, $startdate);
                $this->execute();
                $row4 =  $this->fetchData();
                $withconfirmation = 'N';
                if($row4) {
                    $withconfirmation = 'Y';
                }
                $varrmerge[$key]['withconfirmation'] = $withconfirmation;
          }
          
          return $varrmerge;
    }
    
    public function bankDeposit($startdate,$enddate) {
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
                at.UserName as username,
                st.SiteName as siteName,
                DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %h:%i:%s %p') DateCreated,
                DATE_FORMAT(sr.StatusUpdateDate,'%Y-%m-%d %h:%i:%s %p') DateUpdated,
                bk.BankName as bankname,
                rt.RemittanceName as remittancename,
                ats.Username as PostedBy,
                st.POSAccountNo
                FROM siteremittance sr 
                LEFT JOIN sites st ON sr.SiteID = st.SiteID 
                LEFT JOIN accounts at ON sr.AID = at.AID 
                LEFT JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID 
                LEFT JOIN ref_banks bk ON sr.BankID = bk.BankID 
                LEFT JOIN accounts ats ON sr.VerifiedBy = ats.AID 
                WHERE DATE_FORMAT(sr.DateCreated,'%Y-%m-%d') BETWEEN '$startdate' AND '$enddate' AND sr.Status = 3 
                ORDER BY st.SiteName ASC";
         $this->prepare($query);
         $this->execute();
         return $this->fetchAllData();        
    }
    
    public function topUpHistory($startdate,$enddate,$type, $zsiteID) 
    {
        //if site was selected All
        if($zsiteID == '')
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
                           ORDER BY tuth.TopupHistoryID ASC";
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
                           ORDER BY tuth.TopupHistoryID ASC";      
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
                           AND tuth.SiteID = ? ORDER BY tuth.TopupHistoryID ASC";
                $this->prepare($stmt);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->bindparameter(3, $zsiteID);
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
                           ORDER BY tuth.TopupHistoryID ASC";      
                $this->prepare($stmt);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->bindparameter(3, $type);
                $this->bindparameter(4, $zsiteID);
            }
        }
                
        $this->execute();
        return $this->fetchAllData();        
    }
    
    public function reversalManual($startdate,$enddate) {
          $query = "SELECT th.TopupHistoryID,th.SiteID,sites.SiteName as SiteName,sites.SiteCode as SiteCode,
              sites.POSAccountNo, th.StartBalance,th.EndBalance,th.TopupAmount as ReversedAmount,
              th.DateCreated as TransDate,th.CreatedByAID,acc.Username as ReversedBy " .
              "FROM topuptransactionhistory as th " .
              "inner join accounts as acc on acc.AID = th.CreatedByAID " .
              "inner join sites on sites.SiteID = th.SiteID " . 
              "where th.DateCreated Between '$startdate' and '$enddate' and th.TopupTransactionType = 2 " . 
              "ORDER BY th.TopupHistoryID ASC ";
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();        
    }
    
    public function manualRedemption($startdate,$enddate) {
            $query = "SELECT mr.ManualRedemptionsID,
                mr.ReportedAmount,
                mr.ActualAmount,
                mr.Remarks,
                mr.Status,
                mr.TransactionDate TransDate,
                mr. TicketID,
                mr.TransactionID,
                st.SiteName,
                st.SiteCode,
                tm.TerminalCode,
                at.UserName,
                st.POSAccountNo,
                rs.ServiceName
                FROM manualredemptions mr 
                INNER JOIN sites st ON mr.SiteID = st.SiteID 
                INNER JOIN terminals tm ON mr.TerminalID = tm.TerminalID
                INNER JOIN accounts at ON mr.ProcessedByAID = at.AID
                LEFT JOIN ref_services rs ON mr.ServiceID = rs.ServiceID
                WHERE mr.TransactionDate BETWEEN '$startdate' AND '$enddate' ORDER BY mr.ManualRedemptionsID ASC";
            $this->prepare($query);
            $this->execute();
            return $this->fetchAllData();         
    }
    
    //added on 11/18/2011, for gross hold monitoring per cut off
    public function getGrossHoldCutoff($startdate, $enddate, $zsitecode) 
    {
        //if site was selected All
        if($zsitecode == '') {
            // to get beginning balance
            $query1 = "SELECT srb.SiteID, srb.PrevBalance, ad.Name, sd.SiteDescription, s.SiteCode, s.POSAccountNo FROM siterunningbalance srb " . 
                    "INNER JOIN sites s ON s.SiteID = srb.SiteID " . 
                    "INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID " .
                    "INNER JOIN sitedetails sd ON sd.SiteID = srb.SiteID  where TransactionDate >= '$startdate' and " . 
                    "TransactionDate < '$enddate' order by srb.TransactionDate  ";
            
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
                    "TransactionDate < '$enddate' AND srb.SiteID = '" . $zsitecode . "'  order by srb.TransactionDate  ";
            
            // to get sum of dep,reload and withdrawal
            $query2 = "SELECT SiteID, COALESCE(sum(Deposit),0) as InitialDeposit,sum(Reload) as Reload,sum(Withdrawal) as Redemption FROM siterunningbalance " . 
                    "where TransactionDate >= '$startdate' and TransactionDate < '$enddate' and SiteID = " . $zsitecode;

            // to get collection 
            $query3 = "SELECT SiteID, COALESCE(Sum(Amount),0) as Collection from siteremittance where StatusUpdateDate >= '$startdate' and " . 
                    "StatusUpdateDate < '$enddate' and SiteID = " . $zsitecode;

            // to get replenishment
            $query4 = "SELECT SiteID, COALESCE(Sum(Amount),0) as Replenishment from replenishments where DateCredited >= '$startdate' and " . 
                    "DateCredited < '$enddate' and SiteID = " . $zsitecode;
            
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
        return $consolidate;
    }
    
    /*
     * Get old gross hold balance if queried date is not today
     */
    
    public function getoldGHCutoff($startdate, $enddate, $zsiteid) 
    {       
       switch ($zsiteid)
                           {
           case 'All':
                $query1 = "SELECT sgc.SiteID, sgc.BeginningBalance, sgc.EndingBalance, ad.Name, sd.SiteDescription, 
                            s.SiteCode, s.POSAccountNo,sgc.DateFirstTransaction,sgc.DateLastTransaction,sgc.ReportDate,
                            sgc.DateCutOff,sgc.Deposit AS InitialDeposit, sgc.Reload AS Reload , sgc.Withdrawal AS Redemption                       
                            FROM sitegrossholdcutoff sgc
                            INNER JOIN sites s ON s.SiteID = sgc.SiteID
                            INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID
                            INNER JOIN sitedetails sd ON sd.SiteID = sgc.SiteID
                            WHERE sgc.DateFirstTransaction >= ?
                            AND sgc.DateFirstTransaction < ?
                            ORDER BY s.POSAccountNo";          

                $query2 = "SELECT SiteID,DateCredited,AmountConfirmed FROM grossholdconfirmation 
                    WHERE DateCredited  >= ? AND DateCredited < ? ";

                $query3 = "SELECT SiteID,Amount,StatusUpdateDate FROM siteremittance 
                    WHERE StatusUpdateDate  >= ? AND StatusUpdateDate < ? ";

                //$query4 = "SELECT SiteID,Amount,DateCredited FROM ";

                $query5 = "SELECT SiteID, ActualAmount AS ActualAmount,TransactionDate FROM manualredemptions " . 
                        "WHERE TransactionDate >= ? AND TransactionDate < ? ";   

                // to get beginning balance, sitecode, sitename
                $this->prepare($query1);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->execute(); 
                $rows1 = $this->fetchAllData();        
                $qr1 = array();
                foreach($rows1 as $row1) {
                    $qr1[] = array('siteid'=>$row1['SiteID'],'begbal'=>$row1['BeginningBalance'],'endbal'=>$row1['BeginningBalance'],
                        'sitecode'=>$row1['SiteCode'],'sitename'=>($row1['Name'] . ' ' . $row1['SiteDescription']), 'POSAccountNo' => $row1['POSAccountNo'],
                        'initialdep'=>$row1['InitialDeposit'],'reload'=>$row1['Reload'],'redemption'=>$row1['Redemption'],
                        'datestart'=>$row1['DateFirstTransaction'],'datelast'=>$row1['DateLastTransaction'],
                        'reportdate'=>$row1['ReportDate'],'cutoff'=>$row1['DateCutOff'],'manualredemption'=>0,
                        'replenishment'=>0,'collection'=>0,'replenishment'=>0
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
                    $qr2[] = array('siteid'=>$row2['SiteID'],'datecredit'=>$row2['DateCredited'],
                        'amount'=>$row2['AmountConfirmed']);
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
                    $qr3[] = array('siteid'=>$row3['SiteID'],'datecredit'=>$row3['StatusUpdateDate'],
                        'amount'=>$row3['Amount']);
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
                    $qr5[] = array('siteid'=>$row5['SiteID'],'manualredemption'=>$row5['ActualAmount'],'mrtransdate'=>$row5['TransactionDate']);
                } 

                $ctr = 0;
                while($ctr < count($qr1))
                {
                    $ctr2 = 0; // counter for manual redemptions array
                    while($ctr2 < count($qr5))
                    {
                        if($qr1[$ctr]['siteid'] == $qr5[$ctr2]['siteid'])
                        {
                            $amount = 0;
                            if(($qr5[$ctr2]['mrtransdate'] >= $qr1[$ctr]['reportdate']) && ($qr5[$ctr2]['mrtransdate'] < $qr1[$ctr]['cutoff']))
                            {              
                                 if($qr1[$ctr]['manualredemption'] == 0) 
                                     $qr1[$ctr]['manualredemption'] = $qr5[$ctr2]['manualredemption'];
                                 else
                                 {
                                     $amount = $qr1[$ctr]['manualredemption'];
                                     $qr1[$ctr]['manualredemption'] = $amount + $qr5[$ctr2]['manualredemption'];
                                 }
                            }
                        }
                        $ctr2 = $ctr2 + 1;
                    }            

                    $ctr3 = 0; //counter for grossholdconfirmation array
                    while($ctr3 < count($qr2))
                    {
                        if($qr1[$ctr]['siteid'] == $qr2[$ctr3]['siteid'])
                        {
                            $amount = 0;
                            if(($qr2[$ctr3]['datecredit'] >= $qr1[$ctr]['reportdate']) && ($qr2[$ctr3]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['replenishment'] == 0) 
                                    $qr1[$ctr]['replenishment'] = $qr2[$ctr3]['amount'];
                                else
                                {
                                    $amount = $qr1[$ctr]['replenishment'];
                                    $qr1[$ctr]['replenishment'] = $amount + $qr2[$ctr3]['amount'];
                                }                         
                            }                   
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr4 = 0; //counter for collection array
                    while($ctr4 < count($qr3))
                    {
                        if($qr1[$ctr]['siteid'] == $qr3[$ctr4]['siteid'])
                        {         
                            $amount = 0;
                            if(($qr3[$ctr4]['datecredit'] >= $qr1[$ctr]['reportdate']) && ($qr3[$ctr4]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['collection'] == 0) 
                                {
                                    $qr1[$ctr]['collection'] = $qr3[$ctr4]['amount'];         
                                }      
                                else
                                {
                                    $amount = $qr1[$ctr]['collection'];
                                    $qr1[$ctr]['collection'] = $amount + $qr3[$ctr4]['amount'];                                
                                }                           
                            }                    
                        }
                        $ctr4 = $ctr4 + 1;
                    }            
                    $ctr = $ctr + 1;
                }                 
               break;
           case $zsiteid > 0 :
                $query1 = "SELECT sgc.SiteID, sgc.BeginningBalance, sgc.EndingBalance, ad.Name, sd.SiteDescription, 
                            s.SiteCode, s.POSAccountNo,sgc.DateFirstTransaction,sgc.DateLastTransaction,sgc.ReportDate,
                            sgc.DateCutOff,sgc.Deposit AS InitialDeposit, sgc.Reload AS Reload , sgc.Withdrawal AS Redemption                       
                            FROM sitegrossholdcutoff sgc
                            INNER JOIN sites s ON s.SiteID = sgc.SiteID
                            INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID
                            INNER JOIN sitedetails sd ON sd.SiteID = sgc.SiteID
                            WHERE sgc.DateFirstTransaction >= ?
                            AND sgc.DateFirstTransaction < ? AND sgc.SiteID = ?
                            ORDER BY s.POSAccountNo";          

                $query2 = "SELECT SiteID,DateCredited,AmountConfirmed FROM grossholdconfirmation 
                    WHERE DateCredited  >= ? AND DateCredited < ?  AND SiteID = ?";

                $query3 = "SELECT SiteID,Amount,StatusUpdateDate FROM siteremittance 
                    WHERE StatusUpdateDate  >= ? AND StatusUpdateDate < ?  AND SiteID = ?";

                

                $query5 = "SELECT SiteID, ActualAmount AS ActualAmount,TransactionDate FROM manualredemptions " . 
                        "WHERE TransactionDate >= ? AND TransactionDate < ? AND SiteID = ? ";   

                // to get beginning balance, sitecode, sitename
                $this->prepare($query1);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                
                $this->execute(); 
                $rows1 = $this->fetchAllData();        
                $qr1 = array();
                foreach($rows1 as $row1) {
                    $qr1[] = array('siteid'=>$row1['SiteID'],'begbal'=>$row1['BeginningBalance'],'endbal'=>$row1['BeginningBalance'],
                        'sitecode'=>$row1['SiteCode'],'sitename'=>($row1['Name'] . ' ' . $row1['SiteDescription']), 'POSAccountNo' => $row1['POSAccountNo'],
                        'initialdep'=>$row1['InitialDeposit'],'reload'=>$row1['Reload'],'redemption'=>$row1['Redemption'],
                        'datestart'=>$row1['DateFirstTransaction'],'datelast'=>$row1['DateLastTransaction'],
                        'reportdate'=>$row1['ReportDate'],'cutoff'=>$row1['DateCutOff'],'manualredemption'=>0,
                        'replenishment'=>0,'collection'=>0,'replenishment'=>0
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
                    $qr2[] = array('siteid'=>$row2['SiteID'],'datecredit'=>$row2['DateCredited'],
                        'amount'=>$row2['AmountConfirmed']);
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
                    $qr3[] = array('siteid'=>$row3['SiteID'],'datecredit'=>$row3['StatusUpdateDate'],
                        'amount'=>$row3['Amount']);
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
                    $qr5[] = array('siteid'=>$row5['SiteID'],'manualredemption'=>$row5['ActualAmount'],'mrtransdate'=>$row5['TransactionDate']);
                } 

                $ctr = 0;
                while($ctr < count($qr1))
                {
                    $ctr2 = 0; // counter for manual redemptions array
                    while($ctr2 < count($qr5))
                    {
                        if($qr1[$ctr]['siteid'] == $qr5[$ctr2]['siteid'])
                        {
                            $amount = 0;
                            if(($qr5[$ctr2]['mrtransdate'] >= $qr1[$ctr]['reportdate']." ".BaseProcess::$cutoff) && ($qr5[$ctr2]['mrtransdate'] < $qr1[$ctr]['cutoff']))
                            {              
                                 if($qr1[$ctr]['manualredemption'] == 0) 
                                     $qr1[$ctr]['manualredemption'] = $qr5[$ctr2]['manualredemption'];
                                 else
                                 {
                                     $amount = $qr1[$ctr]['manualredemption'];
                                     $qr1[$ctr]['manualredemption'] = $amount + $qr5[$ctr2]['manualredemption'];
                                 }
                            }
                        }
                        $ctr2 = $ctr2 + 1;
                    }            

                    $ctr3 = 0; //counter for grossholdconfirmation array
                    while($ctr3 < count($qr2))
                    {
                        if($qr1[$ctr]['siteid'] == $qr2[$ctr3]['siteid'])
                        {
                            $amount = 0;
                            if(($qr2[$ctr3]['datecredit'] >= $qr1[$ctr]['reportdate']." ".BaseProcess::$cutoff) && ($qr2[$ctr3]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['replenishment'] == 0) 
                                    $qr1[$ctr]['replenishment'] = $qr2[$ctr3]['amount'];
                                else
                                {
                                    $amount = $qr1[$ctr]['replenishment'];
                                    $qr1[$ctr]['replenishment'] = $amount + $qr2[$ctr3]['amount'];
                                }                         
                            }                   
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr4 = 0; //counter for collection array
                    while($ctr4 < count($qr3))
                    {
                        if($qr1[$ctr]['siteid'] == $qr3[$ctr4]['siteid'])
                        {         
                            $amount = 0;
                            if(($qr3[$ctr4]['datecredit'] >= $qr1[$ctr]['reportdate']." ".BaseProcess::$cutoff) && ($qr3[$ctr4]['datecredit'] < $qr1[$ctr]['cutoff']))
                            {
                                if($qr1[$ctr]['collection'] == 0) 
                                {
                                    $qr1[$ctr]['collection'] = $qr3[$ctr4]['amount'];         
                                }      
                                else
                                {
                                    $amount = $qr1[$ctr]['collection'];
                                    $qr1[$ctr]['collection'] = $amount + $qr3[$ctr4]['amount'];                                
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
    
     public function getRptActiveTerminals($zsitecode) 
    {
          //if site was selected All
          if($zsitecode == "all")
          {
              $query = "SELECT ts.TerminalID, t.TerminalName,s.SiteName, s.POSAccountNo, s.SiteCode,ts.ServiceID,
                        t.TerminalCode, rs.ServiceName FROM terminalsessions ts
                        INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                        INNER JOIN sites as s ON t.SiteID = s.SiteID 
                        INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID";
              $this->prepare($query);
          }
          else
          {
              $query = "SELECT ts.TerminalID, t.TerminalName,s.SiteName, s.POSAccountNo, s.SiteCode,ts.ServiceID,
                        t.TerminalCode, rs.ServiceName FROM terminalsessions ts
                        INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                        INNER JOIN sites as s ON t.SiteID = s.SiteID 
                        INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                        WHERE s.SiteCode = '".$zsitecode."'";
              $this->prepare($query);
          }
                   
          $this->execute();
          return $this->fetchAllData();
    }
    
     /**
     * @author Gerardo V. Jagolino Jr.
      *@params $cardnumber
     * @return array
     * selecting active terminals via card number
     */    
     public function getRptActiveTerminalsUB($cardnumber) 
    {
          
              $query = "SELECT ts.TerminalID, t.TerminalName,s.SiteName, s.POSAccountNo, s.SiteCode,ts.ServiceID,
                        t.TerminalCode, rs.ServiceName FROM terminalsessions ts
                        INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                        INNER JOIN sites as s ON t.SiteID = s.SiteID 
                        INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                        WHERE ts.LoyaltyCardNumber = '".$cardnumber."'";
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
     public function ListPEGSSubjectReport($sort,$dir,$startdate,$enddate) {
          
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
          
          //all sitebalance record
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
          
          $arrResult = null;
          if($varrmerge2)
          {
              for($i=0; $i<count($varrmerge2); $i++) 
              {
                  $gross_hold = (($varrmerge2[$i]['Deposit'] + $varrmerge2[$i]['Reload'] - $varrmerge2[$i]['Redemption']) );
                  $allowable_topup = (( $varrmerge2[$i]['MaxBalance'] -($gross_hold + $varrmerge2[$i]['Balance'])));
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
          }
          return $arrResult;
          
              
    }

    
}