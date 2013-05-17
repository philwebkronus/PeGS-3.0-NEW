<?php

/*
 * Created by: Lea Tuazon
 * Modified By: Edson L. Perez
 * Date Created : June 7, 2011
 */

include "DbHandler.class.php";
include "AppendArray.class.php";

class TopUp extends DBHandler
{
    public $cut_off = CUT_OFF;
    
    public function __construct($sconectionstring)
    {
        parent::__construct($sconectionstring);
    }
    
    /*
     * Get old gross hold balance if queried date is not today
     */
    public function getoldGHBalance($sort, $dir, $startdate,$enddate,$zsiteid)
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

                $query4 = "SELECT SiteID,Amount,DateCredited FROM ";

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
                                    $qr1[$ctr]['replenishment'] = $qr2[$ctr3]['replenishment'];
                                else
                                {
                                    $amount = $qr1[$ctr]['replenishment'];
                                    $qr1[$ctr]['replenishment'] = $amount + $qr2[$ctr3]['replenishment'];
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
//                print_r($qr1);
//                print_r($qr5);
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
                                 
//                                 echo $ctr."==>".$qr5[$ctr2]['mrtransdate']." >= ".$qr1[$ctr]['reportdate']."==>".
//                                         $qr5[$ctr2]['mrtransdate']." < ".$qr1[$ctr]['cutoff']."==>".$qr1[$ctr]['manualredemption']."<br />";
//                                 
                            }
//                            else {
//                                
//                                echo "NOT IN ".$qr5[$ctr2]['mrtransdate'].">=".$qr1[$ctr]['reportdate']."<br />";
//                                
//                            }
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
                                    $qr1[$ctr]['replenishment'] = $qr2[$ctr3]['replenishment'];
                                else
                                {
                                    $amount = $qr1[$ctr]['replenishment'];
                                    $qr1[$ctr]['replenishment'] = $amount + $qr2[$ctr3]['replenishment'];
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
    
    /*
     * Get gross hold balance based on previous cutoff
     */
    public function getGrossHoldBalance($sort, $dir, $startdate,$enddate) {
   
        if(isset($_GET['site']) && $_GET['site'] == '') {
            // to get beginning balance
            $query1 = "SELECT srb.SiteID, srb.PrevBalance, ad.Name, sd.SiteDescription, s.SiteCode, s.POSAccountNo FROM siterunningbalance srb " . 
                    "INNER JOIN sites s ON s.SiteID = srb.SiteID " . 
                    "INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID " .
                    "INNER JOIN sitedetails sd ON sd.SiteID = srb.SiteID  where TransactionDate >= '$startdate' and " . 
                    "TransactionDate < '$enddate' order by srb.TransactionDate";
            
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
                    "TransactionDate < '$enddate' AND srb.SiteID = '" . $_GET['site'] . "'  order by srb.TransactionDate  ";
            
            // to get sum of dep,reload and withdrawal
            $query2 = "SELECT SiteID, COALESCE(sum(Deposit),0) as InitialDeposit,sum(Reload) as Reload,sum(Withdrawal) as Redemption FROM siterunningbalance " . 
                    "where TransactionDate >= '$startdate' and TransactionDate < '$enddate' and SiteID = " . $_GET['site'];

            // to get collection 
            $query3 = "SELECT SiteID, COALESCE(Sum(Amount),0) as Collection from siteremittance where StatusUpdateDate >= '$startdate' and " . 
                    "StatusUpdateDate < '$enddate' and SiteID = " . $_GET['site'];

            // to get replenishment
            $query4 = "SELECT SiteID, COALESCE(Sum(Amount),0) as Replenishment from replenishments where DateCredited >= '$startdate' and " . 
                    "DateCredited < '$enddate' and SiteID = " . $_GET['site'];
            
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
        
        unset($query1, $query2, $query3, $query4, $query5, $rows1, $qr1, $rows2, 
              $qr2, $rows3, $qr3, $rows3, $qr4, $rows4, $qr5, $rows5);
        return $consolidate;
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
        unset($zdetails);
        return $res;
    }
    
    public function getConfirmation($sort, $dir, $start, $limit,$startdate,$enddate) {
        $query = "SELECT ghc.GrossHoldConfirmationID, a.UserName, s.SiteCode, ghc.DateCreated, ghc.DateCredited, ghc.SiteRepresentative, ghc.AmountConfirmed,s.POSAccountNo " . 
                "FROM grossholdconfirmation ghc INNER JOIN accounts a ON ghc.PostedByAID = a.AID " . 
                "INNER JOIN sites s ON ghc.SiteID = s.SiteID WHERE ghc.DateCreated BETWEEN ? AND ? ORDER BY $sort $dir LIMIT $start,$limit";
          $this->prepare($query);
          $this->bindparameter(1, $startdate);
          $this->bindparameter(2, $enddate);
          $this->execute();
          return $this->fetchAllData();
    }
    
    public function getConfirmationTotal($startdate,$enddate) {
        $query = "SELECT COUNT(ghc.GrossHoldConfirmationID ) as totalrow " . 
                "FROM grossholdconfirmation ghc INNER JOIN accounts a ON ghc.PostedBYAID = a.AID " . 
                "INNER JOIN sites s ON ghc.SiteID = s.SiteID WHERE ghc.DateCreated BETWEEN ? AND ? ";
        $this->prepare($query);
        $this->bindparameter(1, $startdate);
        $this->bindparameter(2, $enddate);
        $this->execute();
        $row = $this->fetchAllData(); 
        $total_row = 0;
        if(isset($row[0]['totalrow'])) {
            $total_row = $row[0]['totalrow'];
        }
        return $total_row;           
    }
    
    public function getReplenishment($sort, $dir, $start, $limit,$startdate,$enddate) {
        $query = "SELECT r.ReplenishmentID, s.SiteCode, r.Amount, r.DateCredited, r.DateCreated, a.UserName,s.POSAccountNo FROM replenishments r " . 
                "INNER JOIN sites s ON s.SiteID = r.SiteID " . 
                "INNER JOIN accounts a ON a.AID = r.CreatedByAID WHERE r.DateCreated BETWEEN ? AND ? ORDER BY $sort $dir LIMIT $start,$limit";
          $this->prepare($query);
          $this->bindparameter(1, $startdate);
          $this->bindparameter(2, $enddate);
          $this->execute();
          return $this->fetchAllData();
    }
    
    public function getReplenishmentTotal($startdate,$enddate) {
        $query = "SELECT COUNT(r.ReplenishmentID) AS totalrow FROM replenishments r " . 
                "INNER JOIN sites s ON s.SiteID = r.SiteID " . 
                "INNER JOIN accounts a ON a.AID = r.CreatedByAID WHERE r.DateCreated BETWEEN ? AND ?";
        $this->prepare($query);
        $this->bindparameter(1, $startdate);
        $this->bindparameter(2, $enddate);
        $this->execute();
        $row =  $this->fetchAllData(); 
        $total_row = 0;
        if(isset($row[0]['totalrow'])) {
            $total_row = $row[0]['totalrow'];
        }
        return $total_row;        
    }
    
    //get all bcf
    function getallbcf($zSiteID)
    {
        //if site was selected
        if($zSiteID > 0)
        {
            $stmt = "SELECT Balance,MinBalance,MaxBalance,TopUpType, PickUpTag  FROM sitebalance WHERE SiteID ='".$zSiteID."'";
            $this->executeQuery($stmt);
            return $this->fetchAllData();
        }
        else
        {
            $stmt = "SELECT Balance,MinBalance,MaxBalance,TopUpType,PickUpTag FROM sitebalance";
            $this->executeQuery($stmt);
            return $this->fetchData();
        }
    }
    //posting of manual topup:insert record in sitebalance,sitebalancelogs and transaction history tables
    function insertsitebalance($zSiteID,$zBalance,$zMinBalance,$zMaxBalance,$zLastTransactionDate,$zLastTransactionDescription,$zTopUpType,$zPickUpTag,
              $zAmount,$zPrevBalance,$zNewBalance,$zCreatedByAID,$zDateCreated,$zStartBalance,$zEndBalance,$zToupAmount,$zTotalTopupAmount,
              $zTopupCount,$zRemarks,$zAutoTopUpEnabled ,$zAutoTopUpAmount,$zTopupTransactionType,$zStatus)
    {
        $this->prepare("SELECT COUNT(*) FROM sitebalance WHERE SiteID =?");
        $this->bindparameter(1, $zSiteID);
        $this->execute();
        if($this->hasRows() == 0) 
        {
             $this->begintrans();
             try
             {
                 $this->prepare("INSERT INTO sitebalance(SiteID,Balance,MinBalance,MaxBalance,LastTransactionDate,LastTransactionDescription,TopUpType,AutoTopupEnabled,PickUpTag) VALUES (?,?,?,?,?,?,?,?,?)");
                 $this->bindparameter(1,$zSiteID);
                 $this->bindparameter(2,$zBalance);
                 $this->bindparameter(3,$zMinBalance);
                 $this->bindparameter(4,$zMaxBalance);
                 $this->bindparameter(5,$zLastTransactionDate);
                 $this->bindparameter(6,$zLastTransactionDescription);
                 $this->bindparameter(7,$zTopUpType);
                 $this->bindparameter(8,$zAutoTopUpEnabled);
                 $this->bindparameter(9,$zPickUpTag);
                 $this->execute();             
                 $sitebalance = $this->insertedid();
                 try
                 {
                     $this->prepare("INSERT  INTO sitebalancelogs(SiteID,Amount,PrevBalance,NewBalance,TopupType,CreatedByAID,DateCreated) VALUES (?,?,?,?,?,?,?)");
                     $this->bindparameter(1,$zSiteID);
                     $this->bindparameter(2,$zAmount);
                     $this->bindparameter(3,$zPrevBalance);
                     $this->bindparameter(4,$zNewBalance);
                     $this->bindparameter(5,$zTopUpType);
                     $this->bindparameter(6,$zCreatedByAID);
                     $this->bindparameter(7,$zDateCreated);
                     $this->execute();
                     $sitebalancelogs = $this->insertedid();    
                     try
                     {
                         $this->prepare("INSERT INTO topuptransactionhistory(SiteID,StartBalance,EndBalance,MinBalance,MaxBalance,TopupAmount,TotalTopupAmount,TopupType,TopupCount,TopupTransactionType,
                           DateCreated,Status,Remarks,CreatedByAID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                         $this->bindparameter(1,$zSiteID);
                         $this->bindparameter(2,$zStartBalance);
                         $this->bindparameter(3,$zEndBalance);
                         $this->bindparameter(4,$zMinBalance);
                         $this->bindparameter(5,$zMaxBalance);
                         $this->bindparameter(6,$zToupAmount);
                         $this->bindparameter(7,$zTotalTopupAmount);
                         $this->bindparameter(8,$zTopUpType);
                         $this->bindparameter(9,$zTopupCount);
                         $this->bindparameter(10,$zTopupTransactionType);
                         $this->bindparameter(11,$zDateCreated);
                         $this->bindparameter(12,$zStatus);
                         $this->bindparameter(13,$zRemarks);
                         $this->bindparameter(14, $zCreatedByAID);
                         if($this->execute())
                        {
                           $topuptransactionhistory = $this->insertedid();
                           $this->committrans();
                           return 1;
                        }
                        else
                        {
                           $this->rollbacktrans();
                           return 0;
                        }                         
                     }
                     catch (PDOException $e){
                        $this->rollbacktrans();
                        return 0;
                     }                      
                 }
                 catch (PDOException $e){
                    $this->rollbacktrans();
                    return 0;
                 }                 
            }
            catch (PDOException $e){
              $this->rollbacktrans();
              return 0;
            }
        }
        else
        {
            return 0;
        }
    }

   
    //posting of manual topup: update sitebalance
    function updatebalance($zAmount,$zSiteID,$zPrevBalance,$zNewBalance,$zCreatedByAID,$zDateCreated,
            $zTopUpType,$zMinBalance,$zMaxBalance,$zPickUpTag , $zTopUpCount,$zStatus,$zRemarks,
            $zTopupTransactionType)
    {
        $this->begintrans();
        
         try{
                $this->prepare("UPDATE sitebalance SET Balance =?, WillEmailAlert = 0  WHERE SiteID =?");
                $this->bindparameter(1,$zNewBalance);
                $this->bindparameter(2,$zSiteID);
                $this->execute();
                try 
                {
                    $this->prepare("INSERT  INTO sitebalancelogs(SiteID,Amount,PrevBalance,NewBalance,TopupType,
                        CreatedByAID,DateCreated) VALUES (?,?,?,?,?,?,?)");
                    $this->bindparameter(1,$zSiteID);
                    $this->bindparameter(2,$zAmount);
                    $this->bindparameter(3,$zPrevBalance);
                    $this->bindparameter(4,$zNewBalance);
                    $this->bindparameter(5,$zTopUpType);
                    $this->bindparameter(6,$zCreatedByAID);
                    $this->bindparameter(7,$zDateCreated);       
                    $this->execute();
                    try
                    {
                       $this->prepare("INSERT INTO topuptransactionhistory(SiteID,StartBalance,EndBalance,MinBalance,
                            MaxBalance,TopupAmount,TotalTopupAmount,TopupType,TopupCount,TopupTransactionType,
                               DateCreated,Status,Remarks,CreatedByAID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                       $this->bindparameter(1,$zSiteID);
                       $this->bindparameter(2,$zPrevBalance);
                       $this->bindparameter(3,$zNewBalance);
                       $this->bindparameter(4,$zMinBalance);
                       $this->bindparameter(5,$zMaxBalance);        
                       $this->bindparameter(6,$zAmount);        
                       $this->bindparameter(7,$zAmount);        
                       $this->bindparameter(8,$zTopUpType);
                       $this->bindparameter(9,$zTopUpCount);
                       $this->bindparameter(10,$zTopupTransactionType);        
                       $this->bindparameter(11,$zDateCreated);
                       $this->bindparameter(12,$zStatus);
                       $this->bindparameter(13,$zRemarks);        
                       $this->bindparameter(14, $zCreatedByAID);
                        if($this->execute())
                        {
                            $this->committrans();
                            return 1; // replace $insertedid , always return 0 even if updated was successful
                        }
                        else
                        {
                           $this->rollbacktrans();
                           return 0;
                        }
                    }
                    catch (PDOException $e){
                          $this->rollbacktrans();
                          return 0;
                    }

                }
                catch (PDOException $e){
                      $this->rollbacktrans();
                      return 0;
                }              
              
          }catch (PDOException $e){
              $this->rollbacktrans();
              return 0;
          }
    }

    //posting of manual topup: update sitebalance
    function updatesiteparam($zSiteID,$zMinimumBalance,$zMaximumBalance,$zTopUpType,$zPickUpTag)   
    {
        $this->prepare("UPDATE sitebalance SET MinBalance =?,MaxBalance =?,TopUpType =?, PickUpTag=?  WHERE SiteID =?");
        $this->bindparameter(1,$zMinimumBalance);
        $this->bindparameter(2,$zMaximumBalance);
        $this->bindparameter(3,$zTopUpType);        
        $this->bindparameter(4,$zPickUpTag);        
        $this->bindparameter(5,$zSiteID);
        $this->execute();
        return $this->rowCount();
    }

    //reversal of deposits : update status in siteremittance from 0 to 1
    //reversal of deposits : update status in siteremittance from 2 to 0    
    //reversal of deposits : update status in siteremittance from 2 to 3 , meaning verified  
    function updatesiteremittancestatus($zSiteRemittanceID,$zaid,$zvdate)
    {
        $this->prepare("UPDATE siteremittance SET Status =3, VerifiedBy = ?, StatusUpdateDate = ?  WHERE SiteRemittanceID =?");
        $this->bindparameter(1,$zaid);
        $this->bindparameter(2,$zvdate);
        $this->bindparameter(3,$zSiteRemittanceID);
        $this->execute();
        return $this->rowCount();
    }
    
    //update verified site remittance, change 3 to 0 or 1
    function updateverifiedsiteremittance($zSiteRemittanceID,$zVerifiedRemitStat,$zaid,$zdate)
    {
        $this->prepare("UPDATE siteremittance SET Status =? , AID = ? , StatusUpdateDate = ?  WHERE SiteRemittanceID =?");
        $this->bindparameter(1,$zVerifiedRemitStat);
        $this->bindparameter(2,$zaid); 
        $this->bindparameter(3,$zdate); 
        $this->bindparameter(4,$zSiteRemittanceID);
        $this->execute();
        return $this->rowCount();
    }

    //reversal of manual topup
    function updatereversal($zAmount, $zSiteID, $zPrevBalance,$zNewBalance,$zTopUpType,$zCreatedByAID,
            $zDateCreated,$zTopUpType, $zMinBalance,$zMaxBalance,$zPickUpTag ,$zTopUpCount,$zStatus,
            $zRemarks,$zTopupTransactionType)
    {
        $this->begintrans();
        try
        {
            $this->prepare("UPDATE sitebalance SET Balance =? WHERE SiteID =?");
            $this->bindparameter(1,$zNewBalance);
            $this->bindparameter(2,$zSiteID);
            $this->execute();
            $this->prepare("INSERT  INTO sitebalancelogs(SiteID,Amount,PrevBalance,NewBalance,TopupType,CreatedByAID,DateCreated) VALUES (?,?,?,?,?,?,?)");
            $this->bindparameter(1,$zSiteID);
            $this->bindparameter(2,$zAmount);
            $this->bindparameter(3,$zPrevBalance);
            $this->bindparameter(4,$zNewBalance);
            $this->bindparameter(5,$zTopUpType);
            $this->bindparameter(6,$zCreatedByAID);
            $this->bindparameter(7,$zDateCreated);
            $this->execute(); 
            try
            {
                $this->prepare("INSERT INTO topuptransactionhistory(SiteID,StartBalance,EndBalance,MinBalance,MaxBalance,TopupAmount,TotalTopupAmount,TopupType,TopupCount,TopupTransactionType,
                       DateCreated,Status,Remarks,CreatedByAID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $this->bindparameter(1,$zSiteID);
                $this->bindparameter(2,$zPrevBalance);
                $this->bindparameter(3,$zNewBalance);
                $this->bindparameter(4,$zMinBalance);
                $this->bindparameter(5,$zMaxBalance);
                $this->bindparameter(6,$zAmount);
                $this->bindparameter(7,$zAmount);
                $this->bindparameter(8,$zTopUpType);
                $this->bindparameter(9,$zTopUpCount);
                $this->bindparameter(10,$zTopupTransactionType);
                $this->bindparameter(11,$zDateCreated);
                $this->bindparameter(12,$zStatus);
                $this->bindparameter(13,$zRemarks);
                $this->bindparameter(14, $zCreatedByAID);
                 if($this->execute())
                 {            
                    $this->committrans();
                    return 1;
                 }
                 else
                 {
                    $this->rollbacktrans();
                    return 0;
                 }                
            }
            catch (PDOException $e){
              $this->rollbacktrans();
              return 0;
            }
        }
        catch (PDOException $e){
              $this->rollbacktrans();
              return 0;
        }
        
    }

    //view individual site remittances via remittance id
    function viewsiteremittance($zsiteremit)
    {
        $stmt = "SELECT  a.RemittanceTypeID,a.BankID,a.Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a 			INNER JOIN sites b ON  a.SiteID = b.SiteID  
		INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID WHERE a.SiteRemittanceID = '".$zsiteremit."'";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }
    
    //view verified individual  site remittance via remittance id
    function viewverifiedsiteremittance($zsiteremit)
    {
        $stmt = "SELECT  a.SiteRemittanceID,a.RemittanceTypeID,a.BankID,Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a INNER JOIN sites b
		ON a.SiteID = b.SiteID INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID WHERE a.SiteRemittanceID = '".$zsiteremit."'";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }

    //get site remittance ID where status = 0, select all SiteRemittances on combo box base on site ID
    //get site remittance ID where status = 2, select all SiteRemittances on combo box base on site ID
    function getsiteremittanceid($zsiteID)
    {
        $stmt = "Select SiteRemittanceID from siteremittance where SiteID = '".$zsiteID."' and Status = 2";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }
    
    function getsiteremittanceid2($zsiteID)
    {
        $stmt = "Select SiteRemittanceID from siteremittance where SiteID = '".$zsiteID."' and Status = 3";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }

    //get all sites
    function getsites()
    {
        return $this->getallsites();
    }
    
     //for pagination
     //count site remmitance details (for pagination)
    function countrevdeposits($zsiteID)
    {
          $stmt = "Select COUNT(*) as count from siteremittance where SiteID = '".$zsiteID."' AND Status = 2";
          $this->executeQuery($stmt);
          $this->_row = $this->fetchData();
          return $this->_row;
      }
    
     //get all verified deposits per site
    function countrevdeposits2($zsiteID)
    {
          $stmt = "Select COUNT(*) as count from siteremittance where SiteID = '".$zsiteID."' AND Status = 3";
          $this->executeQuery($stmt);
          $this->_row = $this->fetchData();
          return $this->_row;
      }

     //view all site remittances to reverse (for pagination)
     function viewreversalpage($zsiteID, $zstart, $zlimit)
      {
         if($zsiteID > 0)
         {
          $stmt = "SELECT a.SiteRemittanceID, a.SiteRemittanceID, a.RemittanceTypeID,a.BankID,a.Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a INNER JOIN sites b
		ON a.SiteID = b.SiteID INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID WHERE a.SiteID = '".$zsiteID."' AND a.Status = 2 LIMIT $zstart, $zlimit";
         }
         else
         {
          $stmt = "SELECT a.SiteRemittanceID, a.RemittanceTypeID,a.BankID,a.Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a INNER JOIN sites b
		ON  a.SiteID = b.SiteID INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID LIMIT $zstart, $zlimit";
         }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
     //view all site remittances to reverse (for pagination)
     function viewreversalpage2($zsiteID, $zstart, $zlimit)
      {
         if($zsiteID > 0)
         {
          $stmt = "SELECT a.SiteRemittanceID, a.SiteRemittanceID, a.RemittanceTypeID,a.BankID,Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a INNER JOIN sites b
		ON a.SiteID = b.SiteID INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID WHERE a.SiteID = '".$zsiteID."' AND a.Status = 3 LIMIT $zstart, $zlimit";
         }
         else
         {
          $stmt = "SELECT a.SiteRemittanceID, a.RemittanceTypeID,a.BankID,Branch,a.Amount,a.BankTransactionID,a.BankTransactionDate,
		a.ChequeNumber,a.AID,a.Particulars,a.SiteID,a.Status,b.SiteName, c.RemittanceName, d.BankCode FROM siteremittance a INNER JOIN sites b
		ON a.SiteID = b.SiteID INNER JOIN ref_remittancetype c ON a.RemittanceTypeID = c.RemittanceTypeID 
                LEFT JOIN ref_banks d ON a.BankID = d.BankID WHERE a.Status = 3 LIMIT $zstart, $zlimit";
         }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }      
      
      //insert in siteremittance, for posting of deposit(cashierdeposit.php)
      function insertdepositposting($zremittancetypeID, $zbankID, $zbranch, $zamount, $zbanktransID, $zbanktransdate, $zcheckno, $zaid, $zparticulars, $zsiteID, $zstatus, $zdatecreated, $zsitedate)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO siteremittance (RemittanceTypeID, BankID, Branch, Amount, BankTransactionID, BankTransactionDate, ChequeNumber, AID, Particulars, SiteID, Status, DateCreated, StatusUpdateDate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
          $this->bindparameter(1, $zremittancetypeID);
          $this->bindparameter(2, $zbankID);
          $this->bindparameter(3, $zbranch);
          $this->bindparameter(4, $zamount);
          $this->bindparameter(5, $zbanktransID);
          $this->bindparameter(6, $zbanktransdate);
          $this->bindparameter(7, $zcheckno);
          $this->bindparameter(8, $zaid);
          $this->bindparameter(9, $zparticulars);
          $this->bindparameter(10, $zsiteID);
          $this->bindparameter(11, $zstatus);
          $this->bindparameter(12, $zdatecreated);
          $this->bindparameter(13, $zsitedate);
          if($this->execute())
          {
              $this->committrans();
              return 1;
          }
          else
          {
              $this->rollbacktrans();
              return 0;
          }
      }
      
      //get bankcode, and bankID
      function getbanknames()
      {
          $stmt = "SELECT BankID, BankName, BankCode FROM ref_banks";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();
      }
      
      //get owner per site
      function getoperator($zsiteID)
      {
          $stmt = "select a.AID,a.Username,a.AccountTypeID,a.Status, b.SiteID from accounts a inner join siteaccounts b on b.AID = a.AID where b.SiteID = ? and a.AccountTypeID = 2 and a.Status = 1";      
          $this->prepare($stmt);
          $this->bindparameter(1, $zsiteID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      //insert pegs/ grosshold confirmation
      function insertconfirmation($zsiteID, $zdatecredited, $zsiterep, $zamount, $zaid, $zdatecreated)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO grossholdconfirmation(SiteID,DateCredited,SiteRepresentative,AmountConfirmed,PostedbyAID,DateCreated) values(?,?,?,?,?, now_usec())");
          $this->bindparameter(1, $zsiteID);
          $this->bindparameter(2, $zdatecredited);
          $this->bindparameter(3, $zsiterep);
          $this->bindparameter(4, $zamount);
          $this->bindparameter(5, $zaid);
          
          if($this->execute())
          {
              $confirmationID = $this->insertedid();
              try
              {
                  $this->committrans();
                  return $confirmationID;
              }
              catch(PDOException $e)
              {
                  $this->rollbacktrans();
                  return 0;
              }
          }
          else
          {
              $this->rollbacktrans();
              return 0;
          }
      }
       
      public function getSiteCodeList() {
         $query = "SELECT SiteCode,SiteName, SiteID, POSAccountNo FROM sites WHERE SiteID <> 1 AND Status = 1 ORDER BY SiteCode";   
         $this->prepare($query);
         $this->execute();
         return $this->fetchAllData();         
      }
      
      public function grossHoldMonitoringTotal($startdate,$enddate) {
         $yesterday = date('Y-m-d',strtotime(date("Y-m-d", strtotime(date('Y-m-d'))) . " -1 day"));
         $search = '';
         $field = '';
         $field2 = '';         
         if(isset($_GET['siteid']) && $_GET['siteid'] != '') {
             $search .= " AND srb.SiteID = '".$_GET['siteid']."'";
             $field = 'and SiteID = '.$_GET['siteid'];
             $field2 = 'and t.SiteID = '.$_GET['siteid'];
         }
         $total_row = 0; 
         
         // with site id
         if(isset($_GET['siteid']) && $_GET['siteid']) {
             $query = "SELECT td.TransactionDetailsID, td.TransactionReferenceID,td.TransactionSummaryID, " . 
                "td.SiteID,s.SiteCode,s.SiteName,td.TerminalID,s.POSAccountNo,td.TransactionType,COALESCE(td.Amount,0) AS " . 
                "Amount, td.DateCreated,td.ServiceID,td.CreatedByAID, a.UserName " . 
                "FROM transactiondetails td " . 
                "INNER JOIN accounts a ON a.AID = td.CreatedByAID " . 
                "INNER JOIN sites s ON s.SiteID = td.SiteID " . 
                "WHERE td.DateCreated >= ? AND td.DateCreated < ? AND td.Status IN (1,4) AND td.SiteID = ?";
         } else {
             $query = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.TransactionSummaryID, " . 
                "td.SiteID, s.SiteCode, s.SiteName, td.TerminalID, s.POSAccountNo, td.TransactionType, " . 
                "COALESCE(td.Amount,0) AS Amount, td.DateCreated, td.ServiceID, td.CreateBYAID, a.UserName " . 
                "FROM transactiondetails td " . 
                "INNER JOIN accounts a ON a.AID = td.CreatedByAID " . 
                "INNER JOIN sites s ON s.SiteID = td.SiteID " . 
                "WHERE td.DateCreated >= ? AND td.DateCreated < ? AND td.Status IN (1,4) ORDER BY s.SiteCode";
         }
         $this->prepare($query);
         $this->bindparameter(1, $startdate);
         $this->bindparameter(2, $enddate);
         if(isset($_GET['siteid']) && $_GET['siteid']) {
             $this->bindparameter(3, $_GET['siteid']);
         }
         
         $this->execute();
         $row = $this->fetchAllData();
         if(isset($row[0]['totalrow']))
             $total_row = $row[0]['totalrow'];
         unset($yesterday, $search, $field, $field2, $total_row, $query, $row);
         return $total_row;
      }      
      
      public function grossHoldMonitoring($sort,$dir,$startdate,$enddate) {

          if(isset($_GET['siteid']) && $_GET['siteid'] != '') {
              $query = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.TransactionSummaryID, " . 
                "td.SiteID, s.SiteCode, s.SiteName, s.POSAccountNo, td.TerminalID, s.POSAccountNo, td.TransactionType, " . 
                "COALESCE(td.Amount,0) AS Amount, td.DateCreated, td.ServiceID, td.CreatedByAID, a.UserName, sb.Balance as Balance" . 
                "FROM transactiondetails td FORCE INDEX(IX_transactiondetails_DateCreated) " . 
                "INNER JOIN sitebalance sb ON sb.SiteID = td.SiteID " . 
                "INNER JOIN accounts a ON a.AID = td.CreatedByAID " . 
                "INNER JOIN sites s ON s.SiteID = td.SiteID " .                 
                "WHERE td.DateCreated >= ? AND td.DateCreated < ? AND td.Status IN (1,4) AND td.SiteID = ? " . 
                "ORDER BY s.$sort $dir";
//              
              
          } else {
              $query = "SELECT td.TransactionDetailsID, td.TransactionReferenceID, td.TransactionSummaryID, td.SiteID, " . 
                "s.SiteName, td.TerminalID, s.POSAccountNo, td.TransactionType, COALESCE(td.Amount,0) AS Amount, " . 
                "td.DateCreated, s.SiteCode, s.POSAccountNo, td.ServiceID, td.CreatedByAID, a.UserName, sb.Balance as Balance " . 
                "FROM transactiondetails td  FORCE INDEX(IX_transactiondetails_DateCreated)" . 
                "INNER JOIN sitebalance sb ON sb.SiteID = td.SiteID " . 
                "INNER JOIN accounts a ON a.AID = td.CreatedByAID " . 
                "INNER JOIN sites s ON s.SiteID = td.SiteID " .
                "WHERE td.DateCreated >= ? AND td.DateCreated < ? AND td.Status IN(1,4) " . 
                "ORDER BY s.$sort $dir ";
//              
              
          }
          $this->prepare($query);
          $this->bindparameter(1, $startdate);
          $this->bindparameter(2, $enddate);
          if(isset($_GET['siteid']) && $_GET['siteid']) {
            $this->bindparameter(3, $_GET['siteid']);
          }    
        
          $this->execute();        
          $rows1 =  $this->fetchAllData();
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
          unset($query, $sort, $dir, $rows1, $trans_details);          
          return $varrmerge;
      }
      
      public function getBankDepositHistoryTotal($startdate,$enddate) {
          $total_row = 0;
          $query = "SELECT count(sr.SiteRemittanceID) AS totalrow " .
                "FROM siteremittance sr " .
                "LEFT JOIN sites st ON sr.SiteID = st.SiteID " .
                "LEFT JOIN accounts at ON sr.AID = at.AID " .
                "LEFT JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID " .
                "LEFT JOIN ref_banks bk ON sr.BankID = bk.BankID " .
                "LEFT JOIN accounts ats ON sr.VerifiedBy = ats.AID " .
                "WHERE DATE_FORMAT(sr.DateCreated,'%Y-%m-%d') BETWEEN '$startdate' AND '$enddate' AND sr.Status = 3 ";
         $this->prepare($query);
         $this->execute();
         $row =  $this->fetchAllData();
         if(isset($row[0]['totalrow']))
             $total_row = $row[0]['totalrow'];
         unset($row, $query);
         return $total_row;
      }
      
      public function getBankDepositHistory($sort, $dir, $start, $limit,$startdate,$enddate) {
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
                ORDER BY $sort $dir LIMIT $start,$limit";
         $this->prepare($query);
         $this->execute();
         return $this->fetchAllData();
      }
      
      public function getTopUpHistoryTotal($startdate,$enddate,$type,$site_code) {
          //if site was selected All
          if($site_code == '')
          {
                //if top-up type was selected All
                if($type == '')
                {
                    $stmt = "SELECT count(tuth.TopupHistoryID) AS totalrow
                             FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                             WHERE tuth.DateCreated >= ?
                             AND tuth.DateCreated < ? AND tuth.TopupTransactionType IN(0,1)";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                }
                else
                {
                    $stmt = "SELECT count(tuth.TopupHistoryID) AS totalrow
                             FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                             WHERE tuth.DateCreated >= ?
                             AND tuth.DateCreated < ?
                             AND tuth.TopupTransactionType = ?";      
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
                    $stmt = "SELECT count(tuth.TopupHistoryID) AS totalrow
                             FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                             WHERE tuth.DateCreated >= ?
                             AND tuth.DateCreated < ? AND tuth.SiteID = ? 
                             AND tuth.TopupTransactionType IN(0,1)";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                    $this->bindparameter(3, $site_code);
                }
                else
                {
                    $stmt = "SELECT count(tuth.TopupHistoryID) AS totalrow
                             FROM topuptransactionhistory tuth JOIN sites st ON tuth.SiteID = st.SiteID
                             WHERE tuth.DateCreated >= ?
                             AND tuth.DateCreated < ?
                             AND tuth.TopupTransactionType = ? AND tuth.SiteID = ?";      
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                    $this->bindparameter(3, $type);
                    $this->bindparameter(4, $site_code);
                }
          }
          $this->execute();
          return $this->fetchData();
      }
      
      public function getTopUpHistory($sort, $dir, $start, $limit,$startdate,$enddate,$type,$site_code) {
          //if site was selected All
          if($site_code == '')
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
                               ORDER BY $sort $dir LIMIT $start,$limit";
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
                               ORDER BY $sort $dir LIMIT $start,$limit";      
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
                               AND tuth.SiteID = ? ORDER BY $sort $dir LIMIT $start,$limit";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                    $this->bindparameter(3, $site_code);
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
                               ORDER BY $sort $dir LIMIT $start,$limit";      
                    $this->prepare($stmt);
                    $this->bindparameter(1, $startdate);
                    $this->bindparameter(2, $enddate);
                    $this->bindparameter(3, $type);
                    $this->bindparameter(4, $site_code);
                }
          }
          
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function getReversalManualTotal($startdate,$enddate) {
          $total_row = 0;
          $query = "SELECT count(th.TopupHistoryID) AS totalrow " .
              "FROM topuptransactionhistory as th " .
              "inner join accounts as acc on acc.AID = th.CreatedByAID " .
              "inner join sites on sites.SiteID = th.SiteID " . 
              "where th.DateCreated >= '$startdate' and th.DateCreated < '$enddate' and th.TopupTransactionType = 2 " . 
              "ORDER BY sites.SiteCode ASC";
          $this->prepare($query);
          $this->execute();
          $row =  $this->fetchAllData();
          if(isset($row[0]['totalrow']))
              $total_row = $row[0]['totalrow'];
          unset($query, $row);
          return $total_row;
      }     
      
      public function getReversalManual($sort, $dir, $start, $limit,$startdate,$enddate) {
          $query = "SELECT th.TopupHistoryID,th.SiteID,sites.SiteName as SiteName,sites.SiteCode as SiteCode,
              th.StartBalance,th.EndBalance,th.TopupAmount as ReversedAmount,
              th.DateCreated as TransDate,th.CreatedByAID,acc.Username as ReversedBy,sites.POSAccountNo " .
              "FROM topuptransactionhistory as th " .
              "inner join accounts as acc on acc.AID = th.CreatedByAID " .
              "inner join sites on sites.SiteID = th.SiteID " . 
              "where th.DateCreated Between '$startdate' and '$enddate' and th.TopupTransactionType = 2 " . 
              "ORDER BY $sort $dir LIMIT $start,$limit";
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function getManualRedemptionTotal($startdate,$enddate) {
            $total_row = 0;
            $query = "SELECT count(mr.ManualRedemptionsID) AS totalrow 
                      FROM manualredemptions mr 
                      JOIN sites st ON mr.SiteID = st.SiteID
                      JOIN terminals tm ON mr.TerminalID = tm.TerminalID
                      JOIN accounts at ON mr.ProcessedByAID = at.AID 
                      WHERE mr.TransactionDate BETWEEN '$startdate ' AND '$enddate'";
            $this->prepare($query);
            $this->execute();
            
            $rows = $this->fetchAllData(); 
            if(isset($rows[0]['totalrow'])) {
                $total_row = $rows[0]['totalrow'];
            }
            unset($query, $rows);
            return $total_row;
      }
      
      public function getManualRedemption($sort, $dir, $start, $limit,$startdate,$enddate) {
            $query = "SELECT mr.ManualRedemptionsID,
                mr.ReportedAmount,
                mr.ActualAmount,
                mr.Remarks,
                mr.Status,
                mr.TransactionDate as TransDate,
                mr.TicketID,
                mr.TransactionID,
                st.SiteName,
                st.SiteCode,
                tm.TerminalCode,
                st.POSAccountNo,
                at.UserName,
                rs.ServiceName
                FROM manualredemptions mr 
                JOIN sites st ON mr.SiteID = st.SiteID 
                JOIN terminals tm ON mr.TerminalID = tm.TerminalID
                JOIN accounts at ON mr.ProcessedByAID = at.AID 
                JOIN ref_services rs ON mr.ServiceID = rs.ServiceID
                WHERE mr.TransactionDate BETWEEN '$startdate' AND '$enddate'
                ORDER BY $sort $dir LIMIT $start,$limit";
            $this->prepare($query);
            $this->execute();
            return $this->fetchAllData();      
      }
      
      public function getAllSiteCode() {
            $query = "SELECT SiteID, SiteName, SiteCode, POSAccountNo from sites WHERE Status = 1 AND SiteID <> 1 ORDER BY SiteCode ASC";
            $this->prepare($query);
            $this->execute();
            return $this->fetchAllData();
      }
      
      public function getSitesDetails($owner_id) {
            $and = '';
            if($owner_id != 'All') {
                $and = " AND OwnerAID = '" . $owner_id . "' ";
            }
            $query = "SELECT SiteID, SiteName, SiteCode, POSAccountNo from sites WHERE Status = 1 AND SiteID <> 1 $and ORDER BY SiteCode ASC";
            $this->prepare($query);
            $this->execute();
            return $this->fetchAllData();
      }
      
      public function getActiveTerminalsTotal() {
          $sitecode = $_GET['sitecode'];
          $condition = " WHERE s.SiteCode = '$sitecode' ";
          if($_GET['sitecode'] == 'all') {
              $condition = '';
          }
          $total_row = 0;
          $query = "SELECT count(ts.TerminalID) AS totalrow FROM terminalsessions ts " .
              "left join (terminals as t, sites as s) on (ts.TerminalID = t.terminalID and t.SiteID = s.SiteID) $condition " . 
              "ORDER BY s.SiteCode ASC";
          $this->prepare($query);
          $this->execute();
          $rows = $this->fetchAllData();
          if($rows[0]['totalrow'])
              $total_row = $rows[0]['totalrow'];
          unset($rows, $query, $condition, $sitecode);
          return $total_row;
      }     
      
      public function getActiveTerminals($sort, $dir, $start, $limit) {
          $sitecode = $_GET['sitecode'];
          $condition = " WHERE s.SiteCode = '$sitecode' ";
          if($_GET['sitecode'] == 'all') {
              $condition = '';
          }
          
          $query = "SELECT ts.TerminalID, t.TerminalName,s.SiteName, s.POSAccountNo, s.SiteCode,ts.ServiceID,
                            t.TerminalCode, rs.ServiceName FROM terminalsessions ts
                            INNER JOIN terminals as t ON ts.TerminalID = t.terminalID 
                            INNER JOIN sites as s ON t.SiteID = s.SiteID 
                            INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                            $condition
                            ORDER BY $sort $dir LIMIT $start,$limit";
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
      
      public function getBettingCreditTotal($condition = null,$comp=null,$owner=null,$site_id=null,$report=null) {
            switch ($report)
            {
                case 'critical':
                    switch ($site_id)
                    {
                        case 'All':
                            switch ($owner)
                            {
                                case 'All': // ALL OWNER
                                    $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance
                                        FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance <= sb.MinBalance";
                                    $this->prepare($query);                                    
                                    break;
                                
                                case $owner > 0: //SPECIFIED OWNER
                                    $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance
                                    s.OwnerAID  FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance <= sb.MinBalance AND s.OwnerAID = ?";  
                                    $this->prepare($query);
                                    $this->bindparameter(1,$owner); 
                                    break;
                            }
                            break;
                        case $site_id > 0: // with owner specified
                            $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance,sb.SiteID
                            FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                            WHERE sb.Balance <= sb.MinBalance AND sb.SiteID = ?";                        
                            $this->prepare($query);
                            $this->bindparameter(1,$site_id); 
                            break;
                    }
                    break;
                case 'safe':
                    switch ($site_id)
                    {
                        case 'All':
                            switch ($owner)
                            {
                                case 'All': // ALL OWNER
                                    $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance
                                        FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance > sb.MinBalance";
                                    $this->prepare($query);                                    
                                    break;
                                
                                case $owner > 0: //SPECIFIED OWNER
                                    $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance,
                                    s.OwnerAID  FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance > sb.MinBalance AND s.OwnerAID = ?";  
                                    $this->prepare($query);
                                    $this->bindparameter(1,$owner); 
                                    break;
                            }
                            break;
                        case $site_id > 0: // with owner specified
                            $query = "SELECT count(sb.SiteID) as totalrow,sum(sb.Balance) as totalbalance,sb.SiteID
                            FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                            WHERE sb.Balance > sb.MinBalance AND sb.SiteID = ?";                        
                            $this->prepare($query);
                            $this->bindparameter(1,$site_id); 
                            break;
                    }
                    break;
            }
            $this->execute();
            unset($condition,$comp,$owner,$site_id,$report);
            return $this->fetchAllData(); 
      }
      
      public function getBettingCredit($sort, $dir, $start, $limit,$condition = null,$comp=null,$owner=null,$site_id=null, $report = null) {
    
        switch ($report)
        {
            case 'critical':
                switch ($site_id)                
                {                
                    case 'All':
                        switch ($owner)
                        {
                            case 'All': // ALL OWNER
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance <= sb.MinBalance ORDER BY $sort $dir LIMIT $start,$limit";
                                $this->prepare($query);                                
                                break;
                            
                            case $owner > 0: // OWNER AND ALL ASSIGNED SITES
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance <= sb.MinBalance AND s.OwnerAID = ? ORDER BY $sort $dir LIMIT $start,$limit";
                                $this->prepare($query);   
                                $this->bindparameter(1,$owner);                                                               
                                break;
                        }
                        break;
                    case $site_id > 0: // SPECIFIED OWNER AND SITE
                        $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                            sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                            s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                            WHERE sb.Balance <= sb.MinBalance AND sb.SiteID = ? ORDER BY $sort $dir LIMIT $start,$limit";                        
                        $this->prepare($query);
                        $this->bindparameter(1,$site_id); 
                        break;
                }
                break;
            case 'safe':
                switch ($site_id)
                {
                    case 'All':
                        switch ($owner)
                        {
                            case 'All': // ALL OWNER
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance > sb.MinBalance ORDER BY $sort $dir LIMIT $start,$limit";
                                $this->prepare($query);                                
                                break;
                            
                            case $owner > 0: // OWNER AND ALL ASSIGNED SITES
                                $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                                    sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                                    s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                                    WHERE sb.Balance > sb.MinBalance AND s.OwnerAID = ? ORDER BY $sort $dir LIMIT $start,$limit";
                                $this->prepare($query);   
                                $this->bindparameter(1,$owner);                                                               
                                break;
                        }
                        break;
                    case $site_id > 0:  // SPECIFIED OWNER AND SITE
                        $query = "SELECT sb.SiteID,sb.Balance,sb.MinBalance,sb.MaxBalance, s.OwnerAID,
                            sb.LastTransactionDate,sb.TopUpType,sb.PickUpTag, s.SiteName,
                            s.SiteCode, s.POSAccountNo FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID  
                            WHERE sb.Balance > sb.MinBalance AND sb.SiteID = ? ORDER BY $sort $dir LIMIT $start,$limit";                        
                        $this->prepare($query);
                        $this->bindparameter(1,$site_id); 
                        break;
                }
                break;
        }        
        $this->execute();
        unset($sort, $dir, $start, $limit,$condition ,$comp,$owner,$site_id, $report);
        return $this->fetchAllData(); 
      }  
      
      public function getOwner() {
          $query = 'SELECT DISTINCT s.OwnerAID, ad.Name FROM sites s INNER JOIN accountdetails ad ON ad.AID = s.OwnerAID INNER JOIN accounts a ON a.AID = s.OwnerAID WHERE a.AccountTypeID = 2 ' . 
                  'ORDER BY ad.Name';
          $this->prepare($query);
          $this->execute();
          return $this->fetchAllData();
      }
      
      public function getTerminalMap($terminalid) {
         $query = "SELECT ServiceTerminalAccount FROM serviceterminals A INNER JOIN terminalmapping B ON A.ServiceTerminalID = B.ServiceTerminalID WHERE B.TerminalID = '" . $terminalid . "';";
         $this->prepare($query);
         $this->execute();
         $row =  $this->fetchAllData();
         if(isset($row[0]['ServiceTerminalAccount']))
             return $row[0]['ServiceTerminalAccount'];
         return '';
      }
      
      /***** For Manual Redemption ******/
      
      //get service terminals mapped (for MG)
      function getmglogin($zTerminalID){
          $stmt = "Select ServiceAgentID, ServiceTerminalAccount from serviceterminals as a INNER JOIN terminalmapping as b ON a.ServiceTerminalID = b.ServiceTerminalID where TerminalID = '".$zTerminalID."'";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
      //get agent session ID based from agentid (for MG)
      function getagentsession($zAgentID)
      {
          $stmt = "select ServiceAgentSessionID from serviceagentsessions where ServiceAgentID = '".$zAgentID."'";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
      
      function getterminalvalues($zterminalID)
      {
          $stmt = "SELECT TerminalName, TerminalCode FROM terminals WHERE TerminalID = ? ORDER BY TerminalCode ASC";
          $this->prepare($stmt);
          $this->bindparameter(1, $zterminalID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      /**** End Manual Redemption *****/
      function getremittancetypes()
      {
          $stmt = "SELECT RemittanceTypeID, RemittanceName FROM ref_remittancetype ORDER BY RemittanceName ASC";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();
      }
      
      function checkaccountdetails($zaccname, $zaccpassword)
      {
          $convertpass= sha1($zaccpassword);
          $stmt = "SELECT COUNT(AID) ctracc FROM accounts WHERE UserName = ? AND Password = ? AND Status = 1 AND AccountTypeID = 5";
          $this->prepare($stmt);
          $this->bindparameter(1, $zaccname);
          $this->bindparameter(2, $convertpass);
          $this->execute();
          return $this->fetchData();
      }
      
      function insertreplenishment($zsiteID, $zamount, $vaid, $zdatecredited)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO replenishments (SiteID, Amount, DateCreated, CreatedByAID, DateCredited) VALUES (?,?,now_usec(),?,?)");
          $this->bindparameter(1, $zsiteID);
          $this->bindparameter(2, $zamount);
          //$this->bindparameter(3, $zdatecreated);
          $this->bindparameter(3, $vaid);
          $this->bindparameter(4, $zdatecredited);
          if($this->execute())
          {
              $replenishmentID = $this->insertedid();
              try
              {
                  $this->committrans();
                  return $replenishmentID;
              }
              catch(PDOException $e)
              {
                  $this->rollbacktrans();
                  return 0;
              }
          }
          else
          {
              $this->rollbacktrans();
              return 0;
          }
      }
      public function ListPEGSSubject($sort,$dir,$startdate,$enddate) {
          
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
          
          //all sitebalance resord
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
          $arrResult = array();         
          for($i=0; $i<count($varrmerge2); $i++) 
          {                 
              $gross_hold = (($varrmerge2[$i]['Deposit'] + $varrmerge2[$i]['Reload'] - $varrmerge2[$i]['Redemption']) );
              $allowable_topup = (($varrmerge2[$i]['MaxBalance'] -($gross_hold + $varrmerge2[$i]['Balance'] )));
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
          unset($query1, $query2, $query3, $rows1, $siteaccount, $sitebalance, 
                $trans_details, $varrmerge, $trans, $columnNamesToBind, $mergedColumnNames, 
                $varrmerge1, $append, $append1, $varrmerge2);
          return $arrResult;        
      } 
      
      function getidbyposacc($zposaccno)
      {
          $stmt = "SELECT SiteID FROM sites WHERE POSAccountNo = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $zposaccno);
          $this->execute();
          return $this->fetchData();
      }
      
      public function getterminalcredentials($zterminalID, $zserviceID)
      {
                $stmt = "SELECT ServicePassword FROM terminalservices 
                             WHERE ServiceID = ? AND TerminalID = ? 
                             AND Status = 1 AND isCreated = 1";
            $this->prepare($stmt);
            $this->bindparameter(1, $zserviceID);
            $this->bindparameter(2, $zterminalID);
            $this->execute();
            return $this->fetchData();
      }
      
       public function viewTerminalID($zterminalcode)
        {
            $stmt = "SELECT TerminalID FROM terminals WHERE TerminalCode = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zterminalcode);
            $this->execute();
            return $this->fetchData();
        }
        
        /**
         * temporary
         * @return type 
         */
        public function getLastInsertedID()
        {
            $stmt = "SELECT MAX(ManualRedemptionsID) AS manualredeem FROM manualredemptions";
            $this->prepare($stmt);
            $this->execute();
            return $this->fetchData();
        }
        
        //insert into manualredemption
      function insertmanualredemption($zsiteID, $zterminalID, $zreportedAmt, 
              $zactualAmt, $ztransactionDate, $zreqByAID, $zprocByAID, $zremarks, 
              $zdateeff, $zstatus, $ztransactionID, $zsummaryID,$zticketID, $zCmbServerID,
              $ztransStatus)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO manualredemptions(SiteID, TerminalID, ReportedAmount, 
              ActualAmount, TransactionDate, RequestedByAID, ProcessedByAID, Remarks, 
              DateEffective, Status, TransactionID, LastTransactionSummaryID, TicketID, ServiceID,
              TransactionStatus) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
          $this->bindparameter(1, $zsiteID);
          $this->bindparameter(2, $zterminalID);
          $this->bindparameter(3, $zreportedAmt);
          $this->bindparameter(4, $zactualAmt);
          $this->bindparameter(5, $ztransactionDate);
          $this->bindparameter(6, $zreqByAID);
          $this->bindparameter(7, $zprocByAID);
          $this->bindparameter(8, $zremarks);
          $this->bindparameter(9, $zdateeff);
          $this->bindparameter(10, $zstatus);
          $this->bindparameter(11, $ztransactionID);
          $this->bindparameter(12, $zsummaryID);
          $this->bindparameter(13, $zticketID);
          $this->bindparameter(14, $zCmbServerID);
          $this->bindparameter(15, $ztransStatus);
          if($this->execute())
           {
              $this->committrans();
              return 1;
           }
          else
           {
               $this->rollbacktrans();
               return 0;
           }
      }
      
      /**
         * @author Gerardo V. Jagolino Jr.
         * @param $zsiteID, $zterminalID, $zreportedAmt, 
              $zactualAmt, $ztransactionDate, $zreqByAID, $zprocByAID, $zremarks, 
              $zdateeff, $zstatus, $ztransactionID, $zsummaryID,$zticketID, $zCmbServerID,
              $ztransStatus, $loyaltycardnumber, $mid, $usermode
         * @return integer 
         * insert into manualredemption user based with loyalty card number, memberid and user mode
         */
      function insertmanualredemptionub($zsiteID, $zterminalID, $zreportedAmt, 
              $zactualAmt, $ztransactionDate, $zreqByAID, $zprocByAID, $zremarks, 
              $zdateeff, $zstatus, $ztransactionID, $zsummaryID,$zticketID, $zCmbServerID,
              $ztransStatus, $loyaltycardnumber, $mid, $usermode)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO manualredemptions(SiteID, TerminalID, ReportedAmount, 
              ActualAmount, TransactionDate, RequestedByAID, ProcessedByAID, Remarks, 
              DateEffective, Status, TransactionID, LastTransactionSummaryID, TicketID, ServiceID,
              TransactionStatus, LoyaltyCardNumber, MID, UserMode) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
          $this->bindparameter(1, $zsiteID);
          $this->bindparameter(2, $zterminalID);
          $this->bindparameter(3, $zreportedAmt);
          $this->bindparameter(4, $zactualAmt);
          $this->bindparameter(5, $ztransactionDate);
          $this->bindparameter(6, $zreqByAID);
          $this->bindparameter(7, $zprocByAID);
          $this->bindparameter(8, $zremarks);
          $this->bindparameter(9, $zdateeff);
          $this->bindparameter(10, $zstatus);
          $this->bindparameter(11, $ztransactionID);
          $this->bindparameter(12, $zsummaryID);
          $this->bindparameter(13, $zticketID);
          $this->bindparameter(14, $zCmbServerID);
          $this->bindparameter(15, $ztransStatus);
          $this->bindparameter(16, $loyaltycardnumber);
          $this->bindparameter(17, $mid);
          $this->bindparameter(18, $usermode);
          if($this->execute())
           {
              $this->committrans();
              return 1;
           }
          else
           {
               $this->rollbacktrans();
               return 0;
           }
      }
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index, $search
     * @return array 
     * insert service transaction refence
     */
      function insertserviceTransRef($zserviceid, $ztransorigin)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO servicetransactionref (ServiceID, TransactionOrigin, DateCreated) VALUES (?,?,now_usec())");
          $this->bindparameter(1, $zserviceid);
          $this->bindparameter(2, $ztransorigin);
          $this->execute();
          $insertedid = $this->insertedid();
          try{
              $this->committrans();
              return $insertedid;
          }catch (PDOException $e){
              $this->rollbacktrans();
              return 0;
          }
      }
      
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index, $search
     * @return array 
     * get last summary id using certain terminal id
     */
      function getLastSummaryID($zterminalID)
      {
          $stmt ="SELECT max(TransactionsSummaryID) as summaryID 
              FROM transactionsummary 
              WHERE TerminalID = ? and DateEnded <> 0";
          
          $this->prepare($stmt);
          $this->bindparameter(1, $zterminalID);
          $this->execute();
          return $this->fetchData();
      }
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index, $search
     * @return array 
     * get casino array with given casino service
     */
      public function loopAndFind($array, $index, $search){
                    $returnArray = array();
                    foreach($array as $k=>$v){
                          if($v[$index] == $search){   
                               $returnArray[] = $v;
                          }
        }
        return $returnArray;
       }
      
              /**
         * @author Gerardo V. Jagolino Jr.
         * @param int $serviceid
         * @return array 
         * get service name and status of a certain service provider using its id
         */
        public function getCasinoName($serviceid)
        {
            $stmt = "SELECT ServiceName, Status, UserMode FROM ref_services WHERE ServiceID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $serviceid);
            $this->execute();
            return $this->fetchAllData();
        }
        
        
         /**
         * @author Gerardo V. Jagolino Jr.
         * @param int $serviceid
         * @return array 
         * get service name and status of a certain service provider using its id
         */
        public function getTransSummary($terminalid)
        {
            $stmt = "SELECT max(TransactionsSummaryID) as summaryID, MAX(LoyaltyCardNumber) as loyaltyCard FROM transactionsummary
                WHERE TerminalID = ? AND DateEnded <> 0";
            $this->prepare($stmt);
            $this->bindparameter(1, $terminalid);
            $this->execute();
            return $this->fetchAllData();
        }
        
        /**
         * @author Gerardo V. Jagolino Jr.
         * @param int $terminalid
         * @return array 
         * get service name and status of a certain service provider using its id
         */
        public function getTCodeSiteID($terminalid)
        {
            $stmt = "SELECT TerminalCode, SiteID FROM terminals WHERE TerminalID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $terminalid);
            $this->execute();
            return $this->fetchAllData();
        }
        
        
        /**
         * @author Gerardo V. Jagolino Jr.
         * @param $loyaltycard, $serviceid
         * @return string 
         * get TransactionRequestLogID of a certain service provider and mermbership card
         */
        public function getMaxTransreqlogid($loyaltycard, $serviceid)
        {
            $stmt = "SELECT MAX(TransactionRequestLogID) AS TransactionRequestLogID FROM transactionrequestlogs 
                     WHERE LoyaltyCardNumber = ? AND ServiceID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $loyaltycard);
            $this->bindparameter(2, $serviceid);
            $this->execute();
            $site =  $this->fetchData();
            return $site['TransactionRequestLogID'];
        }
        
        /**
         * @author Gerardo V. Jagolino Jr.
         * @param $loyaltycard, $serviceid
         * @return string 
         * get SiteID, TerminalID of a certain TransactionRequestLogID
         */
        public function getSiteTer($transid)
        {
            $stmt = "SELECT SiteID, TerminalID FROM transactionrequestlogs WHERE TransactionRequestLogID = ? ";
            $this->prepare($stmt);
            $this->bindparameter(1, $transid);
            $this->execute();
            return $this->fetchAllData();
        }
        
}
?>