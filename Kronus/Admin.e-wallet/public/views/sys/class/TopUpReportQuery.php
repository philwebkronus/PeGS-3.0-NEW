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
                ad.Name as name,
                st.SiteName as siteName,
                sr.DateCreated as DateCreated,
                DATE_FORMAT(sr.StatusUpdateDate,'%Y-%m-%d %h:%i:%s %p') DateUpdated,
                bk.BankName as bankname,
                rt.RemittanceName as remittancename,
                ats.Username as PostedBy,
                st.POSAccountNo
                FROM siteremittance sr 
                LEFT JOIN sites st ON sr.SiteID = st.SiteID 
                LEFT JOIN accountdetails ad ON sr.CreatedByAID = ad.AID 
                LEFT JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID 
                LEFT JOIN ref_banks bk ON sr.BankID = bk.BankID 
                LEFT JOIN accounts ats ON sr.VerifiedBy = ats.CreatedByAID 
                WHERE sr.DateCreated BETWEEN '$startdate' AND '$enddate' AND sr.Status = 3 
                ORDER BY sr.DateCreated ASC";
         $this->prepare($query);
         $this->execute();
         return $this->fetchAllData();        
    }
    
    public function getCohAdjustment($startdate,$enddate) {
          $query = "SELECT b.SiteName, b.POSAccountNo, 
                    a.Amount, a.Reason, d.Name as ApprovedBy, 
                    c.Name AS CreatedBy, a.DateCreated
                    FROM npos.cohadjustment a
                    LEFT JOIN npos.sites b ON a.SiteID = b.SiteID
                    LEFT JOIN npos.accountdetails c ON a.CreatedByAID = c.AID
                    LEFT JOIN npos.accountdetails d ON a.ApprovedByAID = d.AID
                WHERE a.DateCreated BETWEEN '$startdate' AND '$enddate' ORDER BY a.DateCreated ASC";
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
                WHERE mr.TransactionDate BETWEEN '$startdate 06:00:00' AND '$enddate 06:00:00' ORDER BY mr.ManualRedemptionsID ASC";
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
               //Query for the generated site gross hold per cutoff (this is only up to the last Cut off)
                $query1 = "SELECT sgc.SiteID, sgc.BeginningBalance, sgc.EndingBalance, ad.Name, sd.SiteDescription, sgc.Coupon,
                            s.SiteCode, s.POSAccountNo,sgc.DateFirstTransaction,sgc.DateLastTransaction,sgc.ReportDate,
                            sgc.DateCutOff,sgc.Deposit AS InitialDeposit, sgc.Reload AS Reload , sgc.Withdrawal AS Redemption, sgc.EwalletDeposits,  sgc.EwalletWithdrawals
                            FROM sitegrossholdcutoff sgc
                            INNER JOIN sites s ON s.SiteID = sgc.SiteID
                            INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID
                            INNER JOIN sitedetails sd ON sd.SiteID = sgc.SiteID
                            WHERE sgc.DateCutOff > ?
                            AND sgc.DateCutOff <= ?
                            ORDER BY s.SiteCode, sgc.DateCutOff";          

               //Query for Replenishments
                $query2 = "SELECT SiteID, Amount, DateCreated FROM replenishments
                                    WHERE DateCreated >= ? AND DateCreated < ? ";

                //Query for Collection
                $query3 = "SELECT SiteID, Amount, DateCreated FROM siteremittance
                                    WHERE Status = 3 AND DateCreated >= ? AND DateCreated < ? ";

                //Query for Manual Redemption (per site/per cut off)
                $query5 = "SELECT SiteID, ActualAmount AS ActualAmount,TransactionDate FROM manualredemptions " . 
                        "WHERE TransactionDate >= ? AND TransactionDate < ? ";   
                
                //Query for Deposit (Cash,Coupon,Ticket),  Reload (Cash,Coupon,Ticket) and Redemption (Cashier,Genesis)
                $query6 = "SELECT tr.TransactionSummaryID AS TransSummID, SUBSTR(t.TerminalCode,11) AS TerminalCode, tr.TransactionType AS TransType,

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

                                tr.DateCreated, tr.SiteID
                                FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
                                INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                WHERE tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY tr.TerminalID, tr.DateCreated DESC"; 
                
                //Query for Unused or Active Tickets of the Pick Date (per site/per cutoff)
                $query7 = "SELECT SiteID, IFNULL(SUM(Amount), 0) AS UnusedTickets, DateCreated FROM
                                            ((SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode, tr.SiteID, tr.DateCreated FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
                                              INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                              INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                              INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                              LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                              WHERE tr.SiteID = :siteid
                                                AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                                                AND tr.Status IN(1,4)
                                                AND tr.TransactionType = 'W'
                                                AND tr.StackerSummaryID IS NOT NULL
                                                AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4,15)
                                                AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID =  :siteid))
                                                    )
                                            UNION ALL
                                            (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode, sa.SiteID, stckr.DateCancelledOn as DateCreated FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
                                              INNER JOIN npos.siteaccounts sa ON stckr.CreatedByAID = sa.AID
                                              WHERE stckr.Status IN (1, 2)
                                              AND stckr.DateCancelledOn >= :startdate AND stckr.DateCancelledOn < :enddate
                                              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4, 15))
                                              AND sa.SiteID =  :siteid
                                              )) AS UnionPrintedTickets
                                            WHERE TicketCode NOT IN  
                                                    (SELECT tckt.TicketCode FROM vouchermanagement.tickets tckt -- Less: Encashed Tickets
                                                            INNER JOIN npos.accounts acct ON  tckt.EncashedByAID = acct.AID
                                                            INNER JOIN npos.siteaccounts sa ON tckt.EncashedByAID = sa.AID
                                                            WHERE tckt.DateEncashed >=  :startdate AND tckt.DateEncashed < :enddate
                                                              AND acct.AccountTypeID = 4 AND sa.SiteID = :siteid
                                                            UNION ALL
                                                            (SELECT stckrdtls.VoucherCode AS TicketCode
                                                              FROM stackermanagement.stackersummary stckr
                                                              INNER JOIN stackermanagement.stackerdetails stckrdtls ON stckr.StackerSummaryID = stckrdtls.StackerSummaryID
                                                              WHERE stckrdtls.PaymentType = 2
                                                                    AND stckrdtls.StackerSummaryID IN
                                                                      (SELECT tr.StackerSummaryID
                                                                            FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)
                                                                            INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                                                            INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                                                            INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                                                            LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                                                            WHERE tr.SiteID = :siteid
                                                                              AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                                                                              AND tr.Status IN(1,4)
                                                                              AND tr.TransactionType In ('D', 'R')
                                                                                    AND tr.StackerSummaryID IS NOT NULL
                                                                              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4,15)
                                                                              AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID =  :siteid)))
                                                            )
                                                    ) 
                                        GROUP BY SiteID";
                
                //Query for Printed Tickets of the pick date (per site/per cutoff)
                $query8 = "SELECT tr.SiteID, IFNULL(SUM(stckr.Withdrawal), 0) AS PrintedTickets, tr.DateCreated FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
                                        INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                        INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                        INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                        LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                        WHERE tr.DateCreated >= ? AND tr.DateCreated < ?
                                          AND tr.Status IN(1,4)
                                          AND tr.SiteID = ?
                                          AND tr.TransactionType = 'W'
                                          AND tr.StackerSummaryID IS NOT NULL
                                          GROUP BY tr.SiteID";
                
                //Query for Encashed Tickets of the pick date (per site/per cutoff)
                $query9 = "SELECT tckt.SiteID, IFNULL(SUM(tckt.Amount), 0) AS EncashedTickets, tckt.DateEncashed as DateCreated FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                                        WHERE tckt.DateEncashed >= ? AND tckt.DateEncashed < ?
                                        AND tckt.SiteID = ?
                                        GROUP BY tckt.SiteID";
                
                //Query for Encashed Tickets of the pick date (per site/per cutoff)
                $query10 = "SELECT et.SiteID, et.CreatedByAID, ad.Name,
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
                                        END) AS EwalletVoucherDeposit

                                    FROM npos.ewallettrans et
                                    LEFT JOIN npos.accountdetails ad ON et.CreatedByAID = ad.AID
                                    WHERE et.StartDate >= ? AND et.StartDate < ?
                                    AND et.Status IN (1,3) 
                                    GROUP BY et.CreatedByAID";

                // to get beginning balance, sitecode, sitename
                $this->prepare($query1);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->execute(); 
                $rows1 = $this->fetchAllData();        
                $qr1 = array();
                foreach($rows1 as $row1) {
                    $qr1[] = array('SiteID'=>$row1['SiteID'],'BegBal'=>$row1['BeginningBalance'],'EndBal'=>$row1['EndingBalance'], 
                        'POSAccountNo' => $row1['POSAccountNo'],'SiteName' => $row1['Name'],'SiteCode'=>$row1['SiteCode'], 
                        'InitialDeposit'=>'0.00','Reload'=>'0.00','Redemption'=> '0.00',
                        'DateStart'=>$row1['DateFirstTransaction'],'DateLast'=>$row1['DateLastTransaction'],
                        'ReportDate'=>$row1['ReportDate'],'CutOff'=>$row1['DateCutOff'],'ManualRedemption'=>0,'Coupon'=>'0.00',
                        'PrintedTickets'=>'0.00','EncashedTickets'=>'0.00', 'RedemptionCashier'=>'0.00',
                        'RedemptionGenesis'=>'0.00','DepositCash'=>'0.00','ReloadCash'=>'0.00','UnusedTickets'=>'0.00','DepositTicket'=>'0.00',
                        'ReloadTicket'=>'0.00','DepositCoupon'=>'0.00','ReloadCoupon'=>'0.00', 'Replenishment'=>0,'Collection'=>0,
                        'EwalletDeposits' => $row1['EwalletDeposits'], 'EwalletWithdrawals' => $row1['EwalletWithdrawals'], 'EwalletCashLoads' => 0
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
                    $qr2[] = array('SiteID'=>$row2['SiteID'],'DateCreated'=>$row2['DateCreated'],
                        'Amount'=>$row2['Amount']);
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
                    $qr3[] = array('SiteID'=>$row3['SiteID'],'DateCreated'=>$row3['DateCreated'],
                        'Amount'=>$row3['Amount']);
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
                    $qr5[] = array('SiteID'=>$row5['SiteID'],'ManualRedemption'=>$row5['ActualAmount'],'MRTransDate'=>$row5['TransactionDate']);
                } 
                
                //Total the Deposit and Reload Cash, Deposit and Reload Coupons, Deposit and Reload Tickets in EGM
                //Total Redemption made by the cashier and the EGM
                $this->prepare($query6);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->execute();  
                $rows6 =  $this->fetchAllData();
                foreach ($rows6 as $row6) {
                    foreach ($qr1 as $keys => $value1) {
                        if($row6["SiteID"] == $value1["SiteID"]){
                            if(($row6['DateCreated'] >= $value1['ReportDate']." ".BaseProcess::$cutoff) && ($row6['DateCreated'] < $value1['CutOff'])){
                                if($row6["DepositCash"] != '0.00'){
                                    $qr1[$keys]["DepositCash"] = (float)$qr1[$keys]["DepositCash"] + (float)$row6["DepositCash"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositCash"];
                                }
                                if($row6["ReloadCash"] != '0.00'){
                                    $qr1[$keys]["ReloadCash"] = (float)$qr1[$keys]["ReloadCash"] + (float)$row6["ReloadCash"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadCash"];
                                }
                                if($row6["RedemptionCashier"] != '0.00'){
                                    $qr1[$keys]["RedemptionCashier"] = (float)$qr1[$keys]["RedemptionCashier"] + (float)$row6["RedemptionCashier"];
                                    $qr1[$keys]["Redemption"] = (float)$qr1[$keys]["Redemption"] + (float)$row6["RedemptionCashier"];
                                }
                                if($row6["RedemptionGenesis"] != '0.00'){
                                    $qr1[$keys]["RedemptionGenesis"] = (float)$qr1[$keys]["RedemptionGenesis"] + (float)$row6["RedemptionGenesis"];
                                    $qr1[$keys]["Redemption"] = (float)$qr1[$keys]["Redemption"] + (float)$row6["RedemptionGenesis"];
                                }
                                if($row6["DepositCoupon"] != '0.00'){
                                    $qr1[$keys]["DepositCoupon"] = (float)$qr1[$keys]["DepositCoupon"] + (float)$row6["DepositCoupon"];
                                    $qr1[$keys]["Coupon"] = (float)$qr1[$keys]["Coupon"] + (float)$row6["DepositCoupon"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositCoupon"];
                                }
                                if($row6["ReloadCoupon"] != '0.00'){
                                    $qr1[$keys]["ReloadCoupon"] = (float)$qr1[$keys]["ReloadCoupon"] + (float)$row6["ReloadCoupon"];
                                    $qr1[$keys]["Coupon"] = (float)$qr1[$keys]["Coupon"] + (float)$row6["ReloadCoupon"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadCoupon"];
                                }
                                if($row6["DepositTicket"] != '0.00'){
                                    $qr1[$keys]["DepositTicket"] = (float)$qr1[$keys]["DepositTicket"] + (float)$row6["DepositTicket"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositTicket"];
                                }
                                if($row6["ReloadTicket"] != '0.00'){
                                    $qr1[$keys]["ReloadTicket"] = (float)$qr1[$keys]["ReloadTicket"] + (float)$row6["ReloadTicket"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadTicket"];
                                }
                            }
                        }
                    }     
                }
                
                foreach ($qr1 as $keys => $value2) {
                    //Get the total Unused Tickets per site
                    $this->prepare($query7);
                    $this->bindparameter(":siteid", $value2["SiteID"]);
                    $this->bindparameter(":startdate", $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(":enddate", $value2["CutOff"]);
                    $this->execute();  
                    $rows7 =  $this->fetchAllData();
                    foreach ($rows7 as $row7) {
                        if($row7["SiteID"] == $value2["SiteID"]){
                            if(($row7['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row7['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["UnusedTickets"] = (float)$row7["UnusedTickets"];
                            }
                        }
                    }
                    
                    //Get the total Printed Tickets per site
                    $this->prepare($query8);
                    $this->bindparameter(1, $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(2, $value2["CutOff"]);
                    $this->bindparameter(3, $value2["SiteID"]);
                    $this->execute();  
                    $rows8 =  $this->fetchAllData();
                    
                    foreach ($rows8 as $row8) {
                        if($row8["SiteID"] == $value2["SiteID"]){
                            if(($row8['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row8['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["PrintedTickets"] = (float)$row8["PrintedTickets"];
                            }
                            break;
                        }
                    }
                    
                    //Get the total Encashed Tickets per site
                    $this->prepare($query9);
                    $this->bindparameter(1, $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(2, $value2["CutOff"]);
                    $this->bindparameter(3, $value2["SiteID"]);
                    $this->execute();  
                    $rows9 =  $this->fetchAllData();
                    
                    foreach ($rows9 as $row9) {
                        if($row9["SiteID"] == $value2["SiteID"]){
                            if(($row9['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row9['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["EncashedTickets"] = (float)$row9["EncashedTickets"];
                            }
                            break;
                        }
                    }
                }
                
                //Get the total Encashed Tickets per site
                $this->prepare($query10);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->execute();  
                $rows10 =  $this->fetchAllData();

                foreach ($rows10 as $value1) {
                    foreach ($qr1 as $keys => $value2) {
                        if($value1["SiteID"] == $value2["SiteID"]){
                                $qr1[$keys]["EwalletCashLoads"] += (float)$value1["EwalletCashDeposit"];
                                $qr1[$keys]["EwalletCashLoads"] += (float)$value1["EwalletBancnetDeposit"];
                                $qr1[$keys]["Coupon"] += (float)$value1["EwalletVoucherDeposit"];
                            break;
                        }
                    }  
                }

                $ctr = 0;
                while($ctr < count($qr1))
                {
                    $ctr2 = 0; // counter for manual redemptions array
                    while($ctr2 < count($qr5))
                    {
                        if($qr1[$ctr]['SiteID'] == $qr5[$ctr2]['SiteID'])
                        {
                            $amount = 0;
                            if(($qr5[$ctr2]['MRTransDate'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr5[$ctr2]['MRTransDate'] < $qr1[$ctr]['CutOff']))
                            {              
                                 if($qr1[$ctr]['ManualRedemption'] == 0) 
                                     $qr1[$ctr]['ManualRedemption'] = $qr5[$ctr2]['ManualRedemption'];
                                 else
                                 {
                                     $amount = $qr1[$ctr]['ManualRedemption'];
                                     $qr1[$ctr]['ManualRedemption'] = $amount + $qr5[$ctr2]['ManualRedemption'];
                                 }
                            }
                        }
                        $ctr2 = $ctr2 + 1;
                    }            

                    $ctr3 = 0; //counter for grossholdconfirmation array
                    while($ctr3 < count($qr2))
                    {
                        if($qr1[$ctr]['SiteID'] == $qr2[$ctr3]['SiteID'])
                        {
                            $amount = 0;
                            if(($qr2[$ctr3]['DateCreated'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr2[$ctr3]['DateCreated'] < $qr1[$ctr]['CutOff']))
                            {
                                if($qr1[$ctr]['Replenishment'] == 0) 
                                    $qr1[$ctr]['Replenishment'] = $qr2[$ctr3]['Amount'];
                                else
                                {
                                    $amount = $qr1[$ctr]['Replenishment'];
                                    $qr1[$ctr]['Replenishment'] = $amount + $qr2[$ctr3]['Amount'];
                                }                         
                            }                   
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr4 = 0; //counter for collection array
                    while($ctr4 < count($qr3))
                    {
                        if($qr1[$ctr]['SiteID'] == $qr3[$ctr4]['SiteID'])
                        {         
                            $amount = 0;
                            if(($qr3[$ctr4]['DateCreated'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr3[$ctr4]['DateCreated'] < $qr1[$ctr]['CutOff']))
                            {
                                if($qr1[$ctr]['Collection'] == 0) 
                                {
                                    $qr1[$ctr]['Collection'] = $qr3[$ctr4]['Amount'];             
                                }      
                                else
                                {
                                    $amount = $qr1[$ctr]['Collection'];
                                    $qr1[$ctr]['Collection'] = $amount + $qr3[$ctr4]['Amount'];                                      
                                }                           
                            }                    
                        }
                        $ctr4 = $ctr4 + 1;
                    }            
                    $ctr = $ctr + 1;
                }                 
               break;
           case $zsiteid > 0 :
               //Query for the generated site gross hold per cutoff (this is only up to the last Cut off)
                $query1 = "SELECT sgc.SiteID, sgc.BeginningBalance, sgc.EndingBalance, ad.Name, sd.SiteDescription, sgc.Coupon,
                            s.SiteCode, s.POSAccountNo,sgc.DateFirstTransaction,sgc.DateLastTransaction,sgc.ReportDate,
                            sgc.DateCutOff,sgc.Deposit AS InitialDeposit, sgc.Reload AS Reload , sgc.Withdrawal AS Redemption, sgc.EwalletDeposits,  sgc.EwalletWithdrawals
                            FROM sitegrossholdcutoff sgc
                            INNER JOIN sites s ON s.SiteID = sgc.SiteID
                            INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID
                            INNER JOIN sitedetails sd ON sd.SiteID = sgc.SiteID
                            WHERE sgc.DateCutOff > ?
                            AND sgc.DateCutOff <= ? AND sgc.SiteID = ?
                            ORDER BY s.SiteCode, sgc.DateCutOff";          

               //Query for Replenishments
                $query2 = "SELECT SiteID, Amount, DateCreated FROM replenishments
                                    WHERE DateCreated >= ? AND DateCreated < ? AND SiteID = ? ";

                //Query for Collection
                $query3 = "SELECT SiteID, Amount, DateCreated FROM siteremittance
                                    WHERE Status = 3 AND DateCreated >= ? AND DateCreated < ? AND SiteID = ? ";

                //Query for Manual Redemption (per site/per cut off)
                $query5 = "SELECT SiteID, ActualAmount AS ActualAmount,TransactionDate FROM manualredemptions " . 
                        "WHERE TransactionDate >= ? AND TransactionDate < ? AND SiteID = ? ";   
                
                //Query for Deposit (Cash,Coupon,Ticket),  Reload (Cash,Coupon,Ticket) and Redemption (Cashier,Genesis)
                $query6 = "SELECT tr.TransactionSummaryID AS TransSummID, SUBSTR(t.TerminalCode,11) AS TerminalCode, tr.TransactionType AS TransType,

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

                                tr.DateCreated, tr.SiteID
                                FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated) 
                                INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                WHERE tr.SiteID = ?
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4)
                                GROUP By tr.TransactionType, tr.TransactionSummaryID
                                ORDER BY tr.TerminalID, tr.DateCreated DESC"; 
                
                //Query for Unused or Active Tickets of the Pick Date (per site/per cutoff)
                $query7 = "SELECT SiteID, IFNULL(SUM(Amount), 0) AS UnusedTickets, DateCreated FROM
                                            ((SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode, tr.SiteID, tr.DateCreated FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
                                              INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                              INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                              INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                              LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                              WHERE tr.SiteID = :siteid
                                                AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                                                AND tr.Status IN(1,4)
                                                AND tr.TransactionType = 'W'
                                                AND tr.StackerSummaryID IS NOT NULL
                                                AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4,15)
                                                AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID =  :siteid))
                                                    )
                                            UNION ALL
                                            (SELECT IFNULL(stckr.Withdrawal, 0) As Amount, stckr.TicketCode, sa.SiteID, stckr.DateCancelledOn as DateCreated FROM stackermanagement.stackersummary stckr -- Cancelled Tickets in Stacker
                                              INNER JOIN npos.siteaccounts sa ON stckr.CreatedByAID = sa.AID
                                              WHERE stckr.Status IN (1, 2)
                                              AND stckr.DateCancelledOn >= :startdate AND stckr.DateCancelledOn < :enddate
                                              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4, 15))
                                              AND sa.SiteID =  :siteid
                                              )) AS UnionPrintedTickets
                                            WHERE TicketCode NOT IN  
                                                    (SELECT tckt.TicketCode FROM vouchermanagement.tickets tckt -- Less: Encashed Tickets
                                                            INNER JOIN npos.accounts acct ON  tckt.EncashedByAID = acct.AID
                                                            INNER JOIN npos.siteaccounts sa ON tckt.EncashedByAID = sa.AID
                                                            WHERE tckt.DateEncashed >=  :startdate AND tckt.DateEncashed < :enddate
                                                              AND acct.AccountTypeID = 4 AND sa.SiteID = :siteid
                                                            UNION ALL
                                                            (SELECT stckrdtls.VoucherCode AS TicketCode
                                                              FROM stackermanagement.stackersummary stckr
                                                              INNER JOIN stackermanagement.stackerdetails stckrdtls ON stckr.StackerSummaryID = stckrdtls.StackerSummaryID
                                                              WHERE stckrdtls.PaymentType = 2
                                                                    AND stckrdtls.StackerSummaryID IN
                                                                      (SELECT tr.StackerSummaryID
                                                                            FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)
                                                                            INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                                                            INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                                                            INNER JOIN npos.accounts a ON tr.CreatedByAID = a.AID
                                                                            LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                                                            WHERE tr.SiteID = :siteid
                                                                              AND tr.DateCreated >= :startdate AND tr.DateCreated < :enddate
                                                                              AND tr.Status IN(1,4)
                                                                              AND tr.TransactionType In ('D', 'R')
                                                                                    AND tr.StackerSummaryID IS NOT NULL
                                                                              AND stckr.CreatedByAID In (SELECT acct.AID FROM npos.accounts acct WHERE acct.AccountTypeID IN (4,15)
                                                                              AND acct.AID IN (SELECT sacct.AID FROM npos.siteaccounts sacct WHERE sacct.SiteID =  :siteid)))
                                                            )
                                                    ) 
                                        GROUP BY SiteID";
                
                //Query for Printed Tickets of the pick date (per site/per cutoff)
                $query8 = "SELECT tr.SiteID, IFNULL(SUM(stckr.Withdrawal), 0) AS PrintedTickets, tr.DateCreated FROM npos.transactiondetails tr FORCE INDEX(IX_transactiondetails_DateCreated)  -- Printed Tickets through W
                                        INNER JOIN npos.transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                        INNER JOIN npos.terminals t ON t.TerminalID = tr.TerminalID
                                        INNER JOIN npos.accounts a ON ts.CreatedByAID = a.AID
                                        LEFT JOIN stackermanagement.stackersummary stckr ON stckr.StackerSummaryID = tr.StackerSummaryID
                                        WHERE tr.DateCreated >= ? AND tr.DateCreated < ?
                                          AND tr.Status IN(1,4)
                                          AND tr.SiteID = ?
                                          AND tr.TransactionType = 'W'
                                          AND tr.StackerSummaryID IS NOT NULL
                                          GROUP BY tr.SiteID";
                
                //Query for Encashed Tickets of the pick date (per site/per cutoff)
                $query9 = "SELECT tckt.SiteID, IFNULL(SUM(tckt.Amount), 0) AS EncashedTickets, tckt.DateEncashed as DateCreated FROM vouchermanagement.tickets tckt  -- Encashed Tickets
                                        WHERE tckt.DateEncashed >= ? AND tckt.DateEncashed < ?
                                        AND tckt.SiteID = ?
                                        GROUP BY tckt.SiteID";
                
                //Query for Encashed Tickets of the pick date (per site/per cutoff)
                $query10 = "SELECT et.SiteID, et.CreatedByAID, ad.Name,
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
                                        END) AS EwalletVoucherDeposit

                                    FROM npos.ewallettrans et
                                    LEFT JOIN npos.accountdetails ad ON et.CreatedByAID = ad.AID
                                    WHERE et.StartDate >= ? AND et.StartDate < ?
                                    AND et.Status IN (1,3) AND et.SiteID = ?
                                    GROUP BY et.CreatedByAID";

                // to get beginning balance, sitecode, sitename
                $this->prepare($query1);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);                
                $this->bindparameter(3, $zsiteid);                
                $this->execute(); 
                $rows1 = $this->fetchAllData();        
                $qr1 = array();
                foreach($rows1 as $row1) {
                    $qr1[] = array('SiteID'=>$row1['SiteID'],'BegBal'=>$row1['BeginningBalance'],'EndBal'=>$row1['EndingBalance'],
                            'POSAccountNo' => $row1['POSAccountNo'],'SiteName' => $row1['Name'],'SiteCode'=>$row1['SiteCode'], 
                            'InitialDeposit'=>'0.00','Reload'=>'0.00','Redemption'=>'0.00',
                            'DateStart'=>$row1['DateFirstTransaction'],'DateLast'=>$row1['DateLastTransaction'],
                            'ReportDate'=>$row1['ReportDate'],'CutOff'=>$row1['DateCutOff'],'ManualRedemption'=>0,'Coupon'=>'0.00',
                            'PrintedTickets'=>'0.00','EncashedTickets'=>'0.00', 'RedemptionCashier'=>'0.00',
                            'RedemptionGenesis'=>'0.00','DepositCash'=>'0.00','ReloadCash'=>'0.00','UnusedTickets'=>'0.00','DepositTicket'=>'0.00',
                            'ReloadTicket'=>'0.00','DepositCoupon'=>'0.00','ReloadCoupon'=>'0.00', 'Replenishment'=>0,'Collection'=>0,
                            'EwalletDeposits' => $row1['EwalletDeposits'], 'EwalletWithdrawals' => $row1['EwalletWithdrawals'], 'EwalletCashLoads' => 0
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
                    $qr2[] = array('SiteID'=>$row2['SiteID'],'DateCreated'=>$row2['DateCreated'],
                        'Amount'=>$row2['Amount']);
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
                    $qr3[] = array('SiteID'=>$row3['SiteID'],'DateCreated'=>$row3['DateCreated'],
                        'Amount'=>$row3['Amount']);
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
                    $qr5[] = array('SiteID'=>$row5['SiteID'],'ManualRedemption'=>$row5['ActualAmount'],'MRTransDate'=>$row5['TransactionDate']);
                } 
                
                //Total the Deposit and Reload Cash, Deposit and Reload Coupons, Deposit and Reload Tickets in EGM
                //Total Redemption made by the cashier and the EGM
                $this->prepare($query6);
                $this->bindparameter(1, $zsiteid);
                $this->bindparameter(2, $startdate);
                $this->bindparameter(3, $enddate);
                $this->execute();  
                $rows6 =  $this->fetchAllData();
                
                foreach ($rows6 as $row6) {
                    foreach ($qr1 as $keys => $value1) {
                        if($row6["SiteID"] == $value1["SiteID"]){
                            if(($row6['DateCreated'] >= $value1['ReportDate']." ".BaseProcess::$cutoff) && ($row6['DateCreated'] < $value1['CutOff'])){
                                if($row6["DepositCash"] != '0.00'){
                                    $qr1[$keys]["DepositCash"] = (float)$qr1[$keys]["DepositCash"] + (float)$row6["DepositCash"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositCash"];
                                }
                                if($row6["ReloadCash"] != '0.00'){
                                    $qr1[$keys]["ReloadCash"] = (float)$qr1[$keys]["ReloadCash"] + (float)$row6["ReloadCash"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadCash"];
                                }
                                if($row6["RedemptionCashier"] != '0.00'){
                                    $qr1[$keys]["RedemptionCashier"] = (float)$qr1[$keys]["RedemptionCashier"] + (float)$row6["RedemptionCashier"];
                                    $qr1[$keys]["Redemption"] = (float)$qr1[$keys]["Redemption"] + (float)$row6["RedemptionCashier"];
                                }
                                if($row6["RedemptionGenesis"] != '0.00'){
                                    $qr1[$keys]["RedemptionGenesis"] = (float)$qr1[$keys]["RedemptionGenesis"] + (float)$row6["RedemptionGenesis"];
                                    $qr1[$keys]["Redemption"] = (float)$qr1[$keys]["Redemption"] + (float)$row6["RedemptionGenesis"];
                                }
                                if($row6["DepositCoupon"] != '0.00'){
                                    $qr1[$keys]["DepositCoupon"] = (float)$qr1[$keys]["DepositCoupon"] + (float)$row6["DepositCoupon"];
                                    $qr1[$keys]["Coupon"] = (float)$qr1[$keys]["Coupon"] + (float)$row6["DepositCoupon"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositCoupon"];
                                }
                                if($row6["ReloadCoupon"] != '0.00'){
                                    $qr1[$keys]["ReloadCoupon"] = (float)$qr1[$keys]["ReloadCoupon"] + (float)$row6["ReloadCoupon"];
                                    $qr1[$keys]["Coupon"] = (float)$qr1[$keys]["Coupon"] + (float)$row6["ReloadCoupon"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadCoupon"];
                                }
                                if($row6["DepositTicket"] != '0.00'){
                                    $qr1[$keys]["DepositTicket"] = (float)$qr1[$keys]["DepositTicket"] + (float)$row6["DepositTicket"];
                                    $qr1[$keys]["InitialDeposit"] = (float)$qr1[$keys]["InitialDeposit"] + (float)$row6["DepositTicket"];
                                }
                                if($row6["ReloadTicket"] != '0.00'){
                                    $qr1[$keys]["ReloadTicket"] = (float)$qr1[$keys]["ReloadTicket"] + (float)$row6["ReloadTicket"];
                                    $qr1[$keys]["Reload"] = (float)$qr1[$keys]["Reload"] + (float)$row6["ReloadTicket"];
                                }
                            }
                        }
                    }     
                }
                
                foreach ($qr1 as $keys => $value2) {
                    //Get the total Unused Tickets per site
                    $this->prepare($query7);
                    $this->bindparameter(":siteid", $value2["SiteID"]);
                    $this->bindparameter(":startdate", $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(":enddate", $value2["CutOff"]);
                    $this->execute();  
                    $rows7 =  $this->fetchAllData();
                    foreach ($rows7 as $row7) {
                        if($row7["SiteID"] == $value2["SiteID"]){
                            if(($row7['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row7['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["UnusedTickets"] = (float)$row7["UnusedTickets"];
                            }
                        }
                    }
                    
                    //Get the total Printed Tickets per site
                    $this->prepare($query8);
                    $this->bindparameter(1, $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(2, $value2["CutOff"]);
                    $this->bindparameter(3, $value2["SiteID"]);
                    $this->execute();  
                    $rows8 =  $this->fetchAllData();
                    
                    foreach ($rows8 as $row8) {
                        if($row8["SiteID"] == $value2["SiteID"]){
                            if(($row8['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row8['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["PrintedTickets"] = (float)$row8["PrintedTickets"];
                            }
                            break;
                        }
                    }
                    
                    //Get the total Encashed Tickets per site
                    $this->prepare($query9);
                    $this->bindparameter(1, $value2["ReportDate"]." ".BaseProcess::$cutoff);
                    $this->bindparameter(2, $value2["CutOff"]);
                    $this->bindparameter(3, $value2["SiteID"]);
                    $this->execute();  
                    $rows9 =  $this->fetchAllData();
                    
                    foreach ($rows9 as $row9) {
                        if($row9["SiteID"] == $value2["SiteID"]){
                            if(($row9['DateCreated'] >= $value2['ReportDate']." ".BaseProcess::$cutoff) && ($row9['DateCreated'] < $value2['CutOff'])){
                                $qr1[$keys]["EncashedTickets"] = (float)$row9["EncashedTickets"];
                            }
                            break;
                        }
                    }
                }
                
                //Get the total Encashed Tickets per site
                $this->prepare($query10);
                $this->bindparameter(1, $startdate);
                $this->bindparameter(2, $enddate);
                $this->bindparameter(3, $zsiteid);
                $this->execute();  
                $rows10 =  $this->fetchAllData();

                foreach ($rows10 as $value1) {
                    foreach ($qr1 as $keys => $value2) {
                        if($value1["SiteID"] == $value2["SiteID"]){
                                $qr1[$keys]["EwalletCashLoads"] += (float)$value1["EwalletCashDeposit"];
                                $qr1[$keys]["EwalletCashLoads"] += (float)$value1["EwalletBancnetDeposit"];
                                $qr1[$keys]["Coupon"] += (float)$value1["EwalletVoucherDeposit"];
                            break;
                        }
                    }  
                }
                
                
                $ctr = 0;
                while($ctr < count($qr1))
                {
                    $ctr2 = 0; // counter for manual redemptions array
                    while($ctr2 < count($qr5))
                    {
                        if($qr1[$ctr]['SiteID'] == $qr5[$ctr2]['SiteID'])
                        {
                            $amount = 0;
                            if(($qr5[$ctr2]['MRTransDate'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr5[$ctr2]['MRTransDate'] < $qr1[$ctr]['CutOff']))
                            {              
                                 if($qr1[$ctr]['ManualRedemption'] == 0) 
                                     $qr1[$ctr]['ManualRedemption'] = $qr5[$ctr2]['ManualRedemption'];
                                 else
                                 {
                                     $amount = $qr1[$ctr]['ManualRedemption'];
                                     $qr1[$ctr]['ManualRedemption'] = $amount + $qr5[$ctr2]['ManualRedemption'];
                                 }
                            }
                        }
                        $ctr2 = $ctr2 + 1;
                    }            

                    $ctr3 = 0; //counter for grossholdconfirmation array
                    while($ctr3 < count($qr2))
                    {
                        if($qr1[$ctr]['SiteID'] == $qr2[$ctr3]['SiteID'])
                        {
                            $amount = 0;
                            if(($qr2[$ctr3]['DateCreated'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr2[$ctr3]['DateCreated'] < $qr1[$ctr]['CutOff']))
                            {
                                if($qr1[$ctr]['Replenishment'] == 0) 
                                    $qr1[$ctr]['Replenishment'] = $qr2[$ctr3]['Amount'];
                                else
                                {
                                    $amount = $qr1[$ctr]['Replenishment'];
                                    $qr1[$ctr]['Replenishment'] = $amount + $qr2[$ctr3]['Amount'];
                                }                         
                            }                   
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr4 = 0; //counter for collection array
                    while($ctr4 < count($qr3))
                    {
                        if($qr1[$ctr]['SiteID'] == $qr3[$ctr4]['SiteID'])
                        {         
                            $amount = 0;
                            if(($qr3[$ctr4]['DateCreated'] >= $qr1[$ctr]['ReportDate']." ".BaseProcess::$cutoff) && ($qr3[$ctr4]['DateCreated'] < $qr1[$ctr]['CutOff']))
                            {
                                if($qr1[$ctr]['Collection'] == 0) 
                                {
                                    $qr1[$ctr]['Collection'] = $qr3[$ctr4]['Amount'];           
                                }      
                                else
                                {
                                    $amount = $qr1[$ctr]['Collection'];
                                    $qr1[$ctr]['Collection'] = $amount + $qr3[$ctr4]['Amount'];                                 
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
    
     public function getRptActiveTerminals($zsitecode, $terminalid="all") 
    {
          //if site was selected All
          if($zsitecode == "all")
          {
              $query = "SELECT ts.TerminalID, t.TerminalName,s.SiteName, s.POSAccountNo, s.SiteCode,ts.ServiceID,
                        CASE t.TerminalType WHEN 0 THEN 'Regular' WHEN 1 THEN 'Genesis' ELSE 'e-SAFE' END AS TerminalType, 
                        t.TerminalCode, rs.ServiceName, ts.UserMode, m.IsEwallet FROM terminalsessions ts
                        INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                        INNER JOIN sites as s ON t.SiteID = s.SiteID 
                        INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                        INNER JOIN membership.members m ON m.MID = ts.MID
                        ORDER BY s.SiteCode, t.TerminalCode  ASC";
              $this->prepare($query);
          }
          else
          {
              $terminalid != "all" ? $additionalcond = "AND ts.TerminalID = $terminalid ": $additionalcond = "";
              $query = "SELECT ts.TerminalID, t.TerminalName,s.SiteName, s.POSAccountNo, s.SiteCode,ts.ServiceID,
                        CASE t.TerminalType WHEN 0 THEN 'Regular' WHEN 1 THEN 'Genesis' ELSE 'e-SAFE' END AS TerminalType, 
                        t.TerminalCode, rs.ServiceName, ts.UserMode, m.IsEwallet FROM terminalsessions ts
                        INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                        INNER JOIN sites as s ON t.SiteID = s.SiteID 
                        INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                        INNER JOIN membership.members m ON m.MID = ts.MID
                        WHERE s.SiteCode = '".$zsitecode." ".$additionalcond."'ORDER BY s.SiteCode, t.TerminalCode ASC";
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
                        CASE t.TerminalType WHEN 0 THEN 'Regular' WHEN 1 THEN 'Genesis' ELSE 'e-SAFE' END AS TerminalType, 
                        t.TerminalCode, rs.ServiceName, ts.UserMode, ts.UBServiceLogin, m.IsEwallet FROM terminalsessions ts
                        INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                        INNER JOIN sites as s ON t.SiteID = s.SiteID 
                        INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                        INNER JOIN membership.members m ON m.MID = ts.MID
                        WHERE ts.LoyaltyCardNumber = '".$cardnumber."' ORDER BY s.SiteCode, t.TerminalCode ASC";
              $this->prepare($query);
             
          $this->execute();
          return $this->fetchAllData();
    }
    
    public function getUBServiceLogin($terminalid) {

          $query = "SELECT UBServiceLogin FROM terminalsessions WHERE TerminalID = ?";
          $this->prepare($query);
          $this->bindparameter(1, $terminalid);
          $this->execute();
          $ublogin = $this->fetchData();
          $ublogin = $ublogin['UBServiceLogin'];
          return $ublogin;
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
          $query = "SELECT rs.ServiceID, rs.Alias, rs.ServiceName, rsg.ServiceGroupName FROM ref_services rs INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID";
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
    
    public function geteWalletTransactionHistoryReport($site,$transType,$transStatus,$startDate,$endDate)
      {
        $where="";
         
         if($transType != 'All' && $transStatus != 'All'){
             
          
                $where.="WHERE a.SiteID=$site"
                     ." AND a.TransType='$transType'"
                     ." AND a.Status=$transStatus"
                     ." AND a.StartDate >= '$startDate' "
                     ." AND a.StartDate <  '$endDate' ";
             
         }
         elseif($transType == 'All' && $transStatus <> 'All'){
              
             
                 $where.="WHERE a.SiteID=$site"
                        . " AND a.Status=$transStatus"
                        .  " AND a.StartDate >= '$startDate' "
                        .   " AND a.StartDate < '$endDate' ";
             

         }
         elseif($transType <> 'All' && $transStatus == 'All'){
             $where.="WHERE a.SiteID=$site"
                     ." AND a.TransType='$transType'"
                     . " AND a.StartDate >= '$startDate' "
                     .  " AND a.StartDate < '$endDate' ";
             
         }
         else{
             $where.="WHERE a.SiteID=$site"
                     ." AND a.StartDate >= '$startDate' "
                     . " AND a.StartDate < '$endDate' "; 
         }
         
        
          $stmt = "SELECT a.EwalletTransID,a.LoyaltyCardNumber, a.StartDate ,"
                        ." a.EndDate , a.Amount, a.TransType,
                            CASE a.Status 
                                  WHEN 0 THEN 'Pending'
                                  WHEN 1 THEN 'Success'
                                  WHEN 2 THEN 'Failed'
                                  WHEN 3 THEN 'Fulfillment Approved'
                                  WHEN 4 THEN 'Fulfillment Denied'
                            END AS Status, b.Name"
                        ." FROM npos.ewallettrans a"
                        ." INNER JOIN npos.accountdetails b ON b.AID = a.CreatedByAID ".$where;     
          
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function geteWalletTransactionCardHistoryReport($cardNum,$transType,$transStatus,$startDate,$endDate)
      {
          $where="";

         if($transType != 'All' && $transStatus != 'All'){
            
             $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                     ." AND a.TransType='$transType'"
                     . " AND a.Status=$transStatus"
                     .  " AND a.StartDate >= '$startDate' "
                     .   " AND a.StartDate < '$endDate' 
                     ";
             
         }
         elseif($transType == 'All' && $transStatus <> 'All'){
            
                $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                        . " AND a.Status=$transStatus"
                        . " AND a.StartDate >= '$startDate' "
                        .  " AND a.StartDate < '$endDate' ";
          

         }
         elseif($transType <> 'All' && $transStatus == 'All'){
             $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                     ." AND a.TransType='$transType'"
                     . " AND a.StartDate >= '$startDate' "
                     .  " AND a.StartDate < '$endDate' ";
             
         }
         else{
             $where.="WHERE a.LoyaltyCardNumber='$cardNum'"
                     ." AND a.StartDate >= '$startDate' "
                     . " AND a.StartDate < '$endDate' "; 
         }
         
        
          $stmt = "SELECT a.EwalletTransID,a.LoyaltyCardNumber, a.StartDate ,"
                  ." a.EndDate , a.Amount, a.TransType,a.Status,a.SiteID,
                      CASE a.Status 
                            WHEN 0 THEN 'Pending'
                            WHEN 1 THEN 'Success'
                            WHEN 2 THEN 'Failed'
                            WHEN 3 THEN 'Fulfillment Approved'
                            WHEN 4 THEN 'Fulfillment Denied'
                      END AS Status, b.Name "
                  ." FROM npos.ewallettrans a"
                  ." INNER JOIN npos.accountdetails b ON b.AID = a.CreatedByAID ".$where;     
          
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();
      }
      
      //@date modified 03-03-2015
      public function replenish($startdate, $enddate) {
//        $query = "SELECT r.ReplenishmentID, s.SiteCode, s.POSAccountNo, r.Amount, r.DateCredited, r.DateCreated, a.UserName, r.ReferenceNumber FROM replenishments r " . 
//                "INNER JOIN sites s ON s.SiteID = r.SiteID " . 
//                "INNER JOIN accounts a ON a.AID = r.CreatedByAID WHERE r.DateCreated BETWEEN '$startdate' AND '$enddate' ORDER BY r.ReplenishmentID ASC";
        $query = "SELECT r.ReplenishmentID, s.SiteCode, s.POSAccountNo, r.Amount, r.DateCredited, r.DateCreated, a.UserName, r.ReferenceNumber, ad.Name, ref.ReplenishmentName FROM replenishments r " . 
                "INNER JOIN sites s ON s.SiteID = r.SiteID " .
                "INNER JOIN ref_replenishmenttype ref ON r.ReplenishmentTypeID = ref.ReplenishmentTypeID " .
                "INNER JOIN accounts a ON a.AID = r.CreatedByAID " .
                "INNER JOIN accountdetails ad ON a.AID = ad.AID WHERE r.DateCreated BETWEEN '$startdate' AND '$enddate' ORDER BY r.DateCreated ASC";
        $this->prepare($query);
        $this->execute();
        return $this->fetchAllData();
    }

    
}