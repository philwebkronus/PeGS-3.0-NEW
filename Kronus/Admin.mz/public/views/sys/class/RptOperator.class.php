<?php
/*
 *Created BY: Edson L. Perez
 *Date Created: September 22, 2011
 */
include 'DbHandler.class.php';
ini_set('display_errors',true);
ini_set('log_errors',true);

class RptOperator extends DBHandler
{
    // CCT ADDED 02/21/2018 BEGIN
    public function getServiceIDwithTransactions($serviceGrpIDs, $siteIDs, $datefrom, $dateto) 
    {
        $siteIDArr = implode(',', array_map(function($el){ return $el['SiteID']; }, $siteIDs));
       
        $sql = "SELECT DISTINCT td.ServiceID As ServiceID, rs.ServiceName, rs.UserMode 
                FROM transactiondetails td INNER JOIN ref_services rs ON rs.ServiceID = td.ServiceID
                WHERE td.SiteID IN (".$siteIDArr.")
                    AND rs.ServiceGroupID IN (".implode(",", $serviceGrpIDs).")
                    AND td.TransactionType IN ('D', 'R', 'W')
                    AND td.Status IN (1, 4)
                    AND td.DateCreated >= ? AND td.DateCreated < ?
                UNION
                SELECT DISTINCT mr.ServiceID As ServiceID, rs.ServiceName, rs.UserMode  
                FROM manualredemptions mr INNER JOIN ref_services rs ON rs.ServiceID = mr.ServiceID
                WHERE mr.SiteID IN  (".$siteIDArr.")
                    AND rs.ServiceGroupID IN (".implode(",", $serviceGrpIDs).")
                    AND mr.Status = 1
                    AND mr.TransactionDate >= ? AND mr.TransactionDate < ? 
                ORDER By ServiceID ";
        
        $this->prepare($sql);
        $this->bindparameter(1, $datefrom);
        $this->bindparameter(2, $dateto);
        $this->bindparameter(3, $datefrom);
        $this->bindparameter(4, $dateto);
        $this->execute();
        $result = $this->fetchAllData();
        return $result;
    }
    
    public function getGrossHoldTBPerProvider($siteID, $serviceID, $transtype, $datefrom, $dateto) 
    {
        if ($transtype == "DR") 
        {
            $sql = "SELECT IFNULL(SUM(td.Amount), 0) as Amount 
                    FROM transactiondetails td 
                    WHERE td.SiteID = ? 
                        AND td.ServiceID = ? 
                        AND td.TransactionType IN ('D', 'R') 
                        AND td.Status IN (1, 4) 
                        AND td.DateCreated >= ? AND td.DateCreated < ?"; 
        }
        else if ($transtype == "W")
        {
            $sql = "SELECT IFNULL(SUM(td.Amount), 0) as Amount 
                    FROM transactiondetails td 
                    WHERE td.SiteID = ? 
                        AND td.ServiceID = ? 
                        AND td.TransactionType IN ('W') 
                        AND td.Status IN (1, 4) 
                        AND td.DateCreated >= ? AND td.DateCreated < ?"; 
        }

        $datefrom = $datefrom." 06:00:00";
        $dateto = $dateto." 06:00:00";
        
        $this->prepare($sql);
        $this->bindparameter(1, $siteID);
        $this->bindparameter(2, $serviceID);
        $this->bindparameter(3, $datefrom);
        $this->bindparameter(4, $dateto);
        
        $this->execute();
        $result = $this->fetchData();
        return $result;
    }    
    
    public function getManualRedemptionTBPerProvider($siteID, $serviceID, $datefrom, $dateto) 
    {
        $sql = "SELECT IFNULL(SUM(ActualAmount), 0) as Amount 
                FROM manualredemptions mr 
                WHERE mr.ServiceID = ?
                    AND mr.Status = 1 
                    AND mr.SiteID = ?  
                    AND mr.TransactionDate >= ? AND mr.TransactionDate < ? ";
        
        $datefrom = $datefrom." 06:00:00";
        $dateto = $dateto." 06:00:00";
        $this->prepare($sql);
        $this->bindparameter(1, $serviceID);
        $this->bindparameter(2, $siteID);
        $this->bindparameter(3, $datefrom);
        $this->bindparameter(4, $dateto);
        $this->execute();
        $result = $this->fetchData();
        return $result;
    }    
    // CCT ADDED 02/21/2018 END
    
    public function __construct($connectionString) 
    {
        parent::__construct($connectionString);
    }
    
    //view all accounts --> 
    function viewsitebyowner($zaid)
    {
        $stmt = "SELECT DISTINCT b.SiteID 
                FROM accounts AS a INNER JOIN siteaccounts AS b ON a.AID = b.AID 
                WHERE a.AID = '".$zaid."' AND b.Status = 1";
        $this->executeQuery($stmt); 
        return $this->fetchAllData();
    }
    
    // for site transactions report
    function viewtransactionperday($zdateFROM, $zdateto, $zsiteID)
    {
        $listsite = array();
        foreach ($zsiteID as $row) 
        {
            array_push($listsite,$row);
        }
        $site = implode(',', $listsite);
        // CCT EDITED 10/22/2018 BEGIN
        // $stmt = "select tr.TransactionSummaryID, ts.DateStarted, ts.DateEnded, tr.DateCreated, ts.LoyaltyCardNumber,  
        //                    -- AND ts.DateStarted >= ? and ts.DateStarted < ? -- replaced by tr.DateCreated
        $stmt = "select tr.TransactionSummaryID, ts.DateStarted, ts.DateEnded, tr.DateCreated, tr.LoyaltyCardNumber,  
                    tr.TerminalID, tr.SiteID, t.TerminalCode as TerminalCode,  tr.TransactionType, sum(tr.Amount) AS amount, a.UserName 
                from transactiondetails tr inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
                    inner join terminals t on t.TerminalID = tr.TerminalID 
                    inner join accounts a on a.AID = tr.CreatedByAID 
                where tr.SiteID IN(".$site.") 
                    AND tr.DateCreated >= ? and tr.DateCreated < ? 
                    AND tr.Status IN(1,4) 
                    and (tr.StackerSummaryID IS NULL OR trim(tr.StackerSummaryID) <> '') 
                group by tr.TransactionType,tr.TransactionSummaryID 
                order by t.TerminalCode,ts.DateStarted Desc ";
        // CCT EDITED 10/22/2018 END
        $this->prepare($stmt);
        $this->bindparameter(1, $zdateFROM);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        unset($listsite);
        return $this->fetchAllData();
    }
    
    /*function viewtransactionperday($zdateFROM, $zdateto, $zsiteID)
    {
        $listsite = array();
        foreach ($zsiteID as $row)
        {
            array_push($listsite, "'".$row."'");
        }
        $site = implode(',', $listsite); 
        
        $stmt = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, tr.TerminalID,tr.SiteID,
                    t.TerminalCode as TerminalCode, tr.TransactionType, sum(tr.Amount) AS amount,a.UserName from transactiondetails tr 
                    inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID 
                    inner join terminals t on t.TerminalID = tr.TerminalID
                    inner join accounts a on a.AID = tr.CreatedByAID
                     where tr.SiteID IN(".$site.") AND 
                    tr.DateCreated >= ? and tr.DateCreated <  ? and tr.Status IN(1,4)
                    group by tr.TransactionType,tr.TransactionSummaryID order by t.TerminalCode,tr.DateCreated Desc ";
        
        $this->prepare($stmt);
        $this->bindparameter(1, $zdateFROM);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        unset($listsite);
        return $this->fetchAllData();
    }*/
    
    //for operators report,
    function getrptsitecode($zsiteID)
    {
       $listsite = array();
       foreach ($zsiteID as $row)
       {
          $rsite = $row;
          array_push($listsite, "'".$rsite."'");
       }
       $site = implode(',', $listsite); 
       $stmt = "SELECT SiteCode FROM sites WHERE SiteID IN (".$site.")";
       $this->prepare($stmt);
       $this->execute();
       unset($listsite);
       return $this->fetchData();
    }
    
    //for bcf per site reports
    function countbcfpersite($zarrsites)
    {
        $listsites = array();
        foreach($zarrsites as $row)
        {
            array_push($listsites, "'".$row['SiteID']."'");
        }
        $siteID = implode(",",$listsites);
        $stmt = "SELECT COUNT(*) as ctrbcf 
                 FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID 
                 WHERE sb.SiteID IN (".$siteID.")";
        $this->prepare($stmt);
        $this->execute();
        unset($listsites);
        return $this->fetchData();
    }
    
    //for bcf per site reports
    function viewbcfpersite($zarrsites, $zstart, $zlimit)
    {
        $listsites = array();
        foreach($zarrsites as $row)
        {
            array_push($listsites, "'".$row['SiteID']."'");
        }
        $siteID = implode(",",$listsites);
        if($zstart == null && $zlimit == null)
        {
            $stmt = "SELECT sb.SiteID, sb.Balance, sb.MinBalance, sb.MaxBalance, sb.LastTransactionDate, 
                        sb.TopUpType, sb.PickUpTag, s.SiteName, s.SiteCode, if(isnull(s.POSAccountNo), '0000000000', s.POSAccountNo) AS POS 
                     FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID 
                     WHERE sb.SiteID IN (".$siteID.") ORDER BY s.SiteCode ASC";
        }
        else
        {
            $stmt = "SELECT sb.SiteID, sb.Balance, sb.MinBalance, sb.MaxBalance, sb.LastTransactionDate,
                        sb.TopUpType, sb.PickUpTag, s.SiteName, s.SiteCode, if(isnull(s.POSAccountNo), '0000000000', s.POSAccountNo) AS POS 
                     FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID 
                     WHERE sb.SiteID IN (".$siteID.") ORDER BY s.SiteCode ASC LIMIT ".$zstart.", ".$zlimit."";
        }
        
        $this->prepare($stmt);
        $this->execute();
        unset($listsites);
        return $this->fetchAllData();
    }
    
    //get path for standalone terminal monitoring
    function getpath($zacctType, $zmenuID)
    {
       $this->prepare("SELECT DefaultURl2 FROM accessrights WHERE AccountTypeID =:acctype AND MenuID = :menuid ORDER BY AccountTypeID, MenuID,OrderID,SubMenuID LIMIT 1");
       $xparams = array(':acctype' =>$zacctType, ':menuid'=>$zmenuID);
       $this->executewithparams($xparams);
       return $this->hasRows();  
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
        return $res;
    }
    
    /**
      * @author Gerardo V. Jagolino Jr.
      * @return array
      * get active session count using siteid and cardnumber
      */
    public final function getActiveSessionCount ($siteID, $cardnumber) 
    {
        if($siteID == '')
        {
            $query = "SELECT count(t.TerminalID) as ActiveSession 
                  FROM terminalsessions ts INNER JOIN terminals t ON t.terminalID=ts.TerminalID 
                  WHERE ts.LoyaltyCardNumber = :cardnumber";
        
            $this->prepare($query);
            $this->bindParam(":cardnumber", $cardnumber);
        }
        else
        {
            $query = "SELECT count(t.TerminalID) as ActiveSession 
                  FROM terminalsessions ts INNER JOIN terminals t ON t.terminalID=ts.TerminalID 
                  WHERE t.SiteID = :siteID";
            $this->prepare($query);
            $this->bindParam(":siteID", $siteID);
        }
        $this->execute();
        $record = $this->fetchAllData();
        return $record[0]["ActiveSession"];
    }

    /**
      * @author Gerardo V. Jagolino Jr.
      * @return array
      * get active session count using siteid and cardnumber and usermode
      */
    public final function getActiveSessionCountMod ($cardnumber, $usermode, $siteID) 
    {
        if($siteID == '')
        {
            $query = "SELECT  count(t.TerminalID) as ActiveSession 
                  FROM terminalsessions ts INNER JOIN terminals t ON t.terminalID=ts.TerminalID 
                  WHERE ts.LoyaltyCardNumber = :cardnumber 
                        AND ts.UserMode = :usermode";
            $this->prepare($query);
            $this->bindParam(":cardnumber", $cardnumber);
            $this->bindParam(":usermode", $usermode);
        }
        else
        {
            $query = "SELECT  count(t.TerminalID) as ActiveSession 
                  FROM terminalsessions ts INNER JOIN terminals t ON t.terminalID=ts.TerminalID 
                  WHERE t.SiteID = :siteID 
                        AND ts.UserMode = :usermode";
            $this->prepare($query);
            $this->bindParam(":siteID", $siteID);
            $this->bindParam(":usermode", $usermode);
        }
        $this->execute();
        $record = $this->fetchAllData();
        return $record[0]["ActiveSession"];
    }
  
    /**
      * @author Gerardo V. Jagolino Jr.
      * @return array
      * get UserLogin for getBalance using terminalid
      */
    public function getLoyaltycardNumberLogin($terminal)
    {
        $stmt = "SELECT UBServiceLogin FROM terminalsessions WHERE TerminalID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $terminal);
        $this->execute();
        $card = $this->fetchData();
        $card = $card['UBServiceLogin'];
        return $card;
    }

    /**
     *This method returns an array composed of:
     * 1. Terminal ID
     * 2. Terminal Code
     * 3. Service ID
     * 4. Service Name
     * 5. Playing Balance
     * 
     * This is used by the Active Session and Terminal Balance Per Site Module
     * 
     * @param int $siteID
     * @param Mixed $_ServiceAPI
     * @param String $_CAPIUsername
     * @param String $_CAPIPassword
     * @param String $_CAPIPlayerName
     * @param String $_MicrogamingCurrency
     * @return Mixed 
     */
    public final function getActiveSessionPlayingBalance 
            ($cardinfo, $siteID, $_ServiceAPI,$_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,
            $_PlayerAPI,$_ptsecretkey, $_HABbrandID = "", $_HABapiKey = "") 
    {
        include_once __DIR__.'/../../sys/class/CasinoCAPIHandler.class.php';
        include_once __DIR__.'/../../sys/class/LoyaltyUBWrapper.class.php';
        
        $loyalty = new LoyaltyUBWrapper();
        
        $row = new stdClass();
        
        $row->page = 1;
        
        $query = "SELECT t.TerminalID, t.TerminalCode, 
                    CASE t.TerminalType WHEN 0
                        THEN 'Regular'
                        WHEN 1
                        THEN 'Genesis'
                        ELSE 'e-SAFE'
                    END AS TerminalType, 
                    rs.ServiceID, rs.ServiceName, ts.UBServiceLogin, ts.UserMode, ts.LoyaltyCardNumber 
                  FROM terminalsessions ts INNER JOIN terminals t ON t.TerminalID = ts.TerminalID 
                        INNER JOIN ref_services rs ON rs.ServiceID = ts.ServiceID 
                  WHERE t.SiteID = :siteID 
                  ORDER BY t.TerminalID ASC";
        $this->prepare($query);
        $this->bindParam(":siteID", $siteID);
        $this->execute();
        $record = $this->fetchAllData();
        $newRecord = array();
        $ctr = 0;
        
        foreach($record as $r) 
        {
            if(preg_match("/RTG/", $r["ServiceName"])) 
            {
                $configuration = self::getConfigurationForRTGCAPI(
                                    strpos($_ServiceAPI[(int)$r["ServiceID"]-1], "ECFTEST"), 
                                    $_ServiceAPI[(int)$r["ServiceID"]-1], 
                                    $r["ServiceID"]);
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG, $configuration );
                
                // EDITED CCT 07/11/2018 BEGIN
                //if($r["UserMode"] == 1)
                if(($r["UserMode"] == 1) || ($r["UserMode"] == 3))
                // EDITED CCT 07/11/2018 END
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["UBServiceLogin"]);
                } 
                else 
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                }
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else
                {
                    $r["PlayingBalance"] = 0;
                }
                
            }
            else if (preg_match("/Habanero/", $r["ServiceName"])) 
            {
                $url = $_ServiceAPI[(int)$r["ServiceID"]-1];
                $configuration = self::getConfigurationForHabaneroCAPI( $url,$_HABbrandID,$_HABapiKey);
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::HAB, $configuration );

                //getterminalcredentials
                // EDITED CCT 07/11/2018 BEGIN
                if($r["UserMode"] == 0 || $r["UserMode"] == 2)
                {
                    $stmt = "SELECT ServicePassword FROM terminalservices 
                        WHERE ServiceID = ? AND TerminalID = ? AND Status = 1 AND isCreated = 1";
                }
                else  //getusercredentials
                { 
                    $stmt = "SELECT UBServicePassword FROM terminalsessions "
                            . "WHERE ServiceID = ? AND TerminalID = ?";
                }
                // EDITED CCT 07/11/2018 END
                $this->prepare($stmt);
                $this->bindparameter(1, $r["ServiceID"]);
                $this->bindparameter(2, $r["TerminalID"]);
                $this->execute();
                $servicePwdResult = $this->fetchData();   

                //if user mode is terminal based, get each balances of each terminal
                if($r["UserMode"] == 0 || $r["UserMode"] == 2)
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"], $servicePwdResult['ServicePassword']);
                }
                // ADDED CCT 07/11/2018 BEGIN
                //if user mode is user based, get each balances of each casino mapped to a card
                else
                {
                    $serviceusername = $this->getLoyaltycardNumberLogin($r["TerminalID"]);   
                    $data = $_CasinoAPIHandler->GetBalance($serviceusername, $servicePwdResult['UBServicePassword']);
                }    
                // ADDED CCT 07/11/2018 END
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else
                {
                    $r["PlayingBalance"] = 0;
                }
            }            
            else if (preg_match("/e-Bingo/", $r["ServiceName"])) 
            {
                // e-Bingo Balance is always zero
                $r["PlayingBalance"] = 0;
            }            
            
            $loyalty_result = json_decode($loyalty->getCardInfo2($r['LoyaltyCardNumber'], $cardinfo, 1));
            $loyalty_result->CardInfo->IsEwallet == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            
            // EDITED CCT 07/11/2018 BEGIN
            //$r['UserMode'] == 1 ? $r['UserMode'] = "User Based" : $r['UserMode'] = "Terminal Based";
            (($r['UserMode'] == 1) || ($r['UserMode'] == 3)) ? $r['UserMode'] = "User Based" : $r['UserMode'] = "Terminal Based";
            // EDITED CCT 07/11/2018 END
            
            if($r["PlayingBalance"] == 0)
            {
                $r["PlayingBalance"] = 0;
            }
            else
            {
                $r["PlayingBalance"] = number_format($r['PlayingBalance'], 2);
            }
            
            $row->rows[$ctr]["id"] = $r["TerminalID"];
            
            $row->rows[$ctr]["cell"] = array(
                $r["TerminalType"],
                $r["TerminalCode"],
                $r["PlayingBalance"],
                $r["UserMode"],
                $isEwallet
            );
            $ctr++;
        }
        return json_encode($row);
    }
    
    /**
     *This method returns an array composed of:
     * 1. Terminal ID
     * 2. Terminal Code
     * 3. Service ID
     * 4. Service Name
     * 5. Playing Balance
     * 
     * This is used by the Active Session and Terminal Balance Per Site Module
     * 
     * @param int $siteID
     * @param Mixed $_ServiceAPI
     * @param String $_CAPIUsername
     * @param String $_CAPIPassword
     * @param String $_CAPIPlayerName
     * @param String $_MicrogamingCurrency
     * @return Mixed 
     */
    public final function getPagcorActiveSessionPlayingBalance 
                ($cardinfo, $siteID, $_ServiceAPI,$_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, 
                $_ptcasinoname,$_PlayerAPI,$_ptsecretkey, $_HABbrandID = "", $_HABapiKey = "") 
    {
        include_once __DIR__.'/../../sys/class/CasinoCAPIHandler.class.php';
        include_once __DIR__.'/../../sys/class/LoyaltyUBWrapper.class.php';
        
        $loyalty = new LoyaltyUBWrapper();
        
        $row = new stdClass();
        
        $row->page = 1;
        
        $query = "SELECT t.TerminalID, t.TerminalCode,
                CASE t.TerminalType WHEN 0
                        THEN 'Regular'
                        WHEN 1
                        THEN 'Genesis'
                        ELSE 'e-SAFE'
                    END AS TerminalType,
                rs.ServiceID, rs.ServiceName, ts.UBServiceLogin, ts.UserMode, ts.LoyaltyCardNumber 
                FROM  terminalsessions ts  INNER JOIN terminals t  ON t.TerminalID = ts.TerminalID 
                        INNER JOIN ref_services rs ON rs.ServiceID = ts.ServiceID 
                WHERE t.SiteID = :siteID 
                ORDER BY t.TerminalID ASC";
        
        $this->prepare($query);
        
        $this->bindParam(":siteID", $siteID);
        
        $this->execute();
        
        $record = $this->fetchAllData();

        $newRecord = array();
        
        $ctr = 0;
        
        foreach($record as $r) 
        {
            if(preg_match("/RTG/", $r["ServiceName"])) 
            {
                $configuration = self::getConfigurationForRTGCAPI(
                                    strpos($_ServiceAPI[(int)$r["ServiceID"]-1], "ECFTEST"), 
                                    $_ServiceAPI[(int)$r["ServiceID"]-1], 
                                    $r["ServiceID"]);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG, $configuration );
                // EDITED CCT 07/11/2018 BEGIN
                //if($r["UserMode"] == 1)
                if (($r["UserMode"] == 1) || ($r["UserMode"] == 3))
                // EDITED CCT 07/11/2018 END
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["UBServiceLogin"]);
                } 
                else 
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                }
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else{
                    $r["PlayingBalance"] = 0;
                }
            }
            else if (preg_match("/Habanero/", $r["ServiceName"])) 
            {
                $url = $_ServiceAPI[(int)$r["ServiceID"]-1];
                $configuration = self::getConfigurationForHabaneroCAPI( $url,$_HABbrandID,$_HABapiKey);
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::HAB, $configuration );
                        
                //getterminalcredentials
                // EDITED CCT 07/11/2018 BEGIN
                if($r["UserMode"] == 0 || $r["UserMode"] == 2)
                {
                    $stmt = "SELECT ServicePassword FROM terminalservices 
                        WHERE ServiceID = ? AND TerminalID = ? AND Status = 1 AND isCreated = 1";
                }
                else  //getusercredentials
                { 
                    $stmt = "SELECT UBServicePassword FROM terminalsessions "
                            . "WHERE ServiceID = ? AND TerminalID = ?";
                }
                // EDITED CCT 07/11/2018 END
                $this->prepare($stmt);
                $this->bindparameter(1, $r["ServiceID"]);
                $this->bindparameter(2, $r["TerminalID"]);
                $this->execute();
                $servicePwdResult = $this->fetchData();   

                //if user mode is terminal based, get each balances of each terminal
                if($r["UserMode"] == 0 || $r["UserMode"] == 2)
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"], $servicePwdResult['ServicePassword']);
                }
                //if user mode is user based, get each balances of each casino mapped to a card
                else
                {
                    $serviceusername = $this->getLoyaltycardNumberLogin($r["TerminalID"]);   
                    $data = $_CasinoAPIHandler->GetBalance($serviceusername, $servicePwdResult['UBServicePassword']);
                }    

                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else
                {
                    $r["PlayingBalance"] = 0;
                }
            }            
            else if (preg_match("/e-Bingo/", $r["ServiceName"])) 
            {
                // Playing Balance is always zero for e-Bingo
                $r["PlayingBalance"] = 0;
            }            
            
            $loyalty_result = json_decode($loyalty->getCardInfo2($r['LoyaltyCardNumber'], $cardinfo, 1));
            $loyalty_result->CardInfo->IsEwallet == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            
            // EDITED CCT 07/11/2018 BEGIN
            //$r['UserMode'] == 1 ? $r['UserMode'] = "User Based" : $r['UserMode'] = "Terminal Based";
            (($r['UserMode'] == 1) || ($r['UserMode'] == 3)) ? $r['UserMode'] = "User Based" : $r['UserMode'] = "Terminal Based";
            // EDITED CCT 07/11/2018 END
         
            if($r["PlayingBalance"] == 0)
            {
                $r["PlayingBalance"] = 0;
            }
            else
            {
                $r["PlayingBalance"] = number_format($r['PlayingBalance'], 2);
            }
            
            $row->rows[$ctr]["id"] = $r["TerminalID"];
            
            $row->rows[$ctr]["cell"] = array(
                $r["TerminalType"],
                $r["TerminalCode"],
                $r["PlayingBalance"],
                $r["UserMode"],
                $isEwallet,
            );
            $ctr++;
        }
        return json_encode($row);
    }
    
    /**
     *This method returns an array composed of:
     * 1. Terminal ID
     * 2. Terminal Code
     * 3. Service ID
     * 4. Service Name
     * 5. Playing Balance
     * 
     * This is used by the Active Session and Terminal Balance Per Site Module
     * 
     * @param String $cardnumber
     * @param Mixed $_ServiceAPI
     * @param String $_CAPIUsername
     * @param String $_CAPIPassword
     * @param String $_CAPIPlayerName
     * @param String $_MicrogamingCurrency
     * @return Mixed 
     */
    public final function getActiveSessionPlayingBalanceub ($cardinfo, $cardnumber, $serviceusername, $_ServiceAPI, $_CAPIUsername, 
            $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname, $_PlayerAPI, $_ptsecretkey, $_HABbrandID = '',$_HABapiKey = '') 
    {
        include_once __DIR__.'/../../sys/class/CasinoCAPIHandler.class.php';
        include_once __DIR__.'/../../sys/class/LoyaltyUBWrapper.class.php';
        
        $loyalty = new LoyaltyUBWrapper();
        
        $row = new stdClass();
        
        $row->page = 1;
        
        $query = "SELECT t.TerminalID, t.TerminalCode, 
                    CASE t.TerminalType WHEN 0
                        THEN 'Regular'
                        WHEN 1
                        THEN 'Genesis'
                        ELSE 'e-SAFE'
                    END AS TerminalType, 
                    rs.ServiceID, rs.ServiceName, ts.UBServiceLogin, ts.UserMode 
                  FROM terminalsessions ts  INNER JOIN terminals t  ON t.TerminalID = ts.TerminalID 
                        INNER JOIN ref_services rs ON rs.ServiceID = ts.ServiceID 
                  WHERE ts.LoyaltyCardNumber = :cardnum 
                  ORDER BY t.TerminalID ASC";
        
        $this->prepare($query);
        $this->bindParam(":cardnum", $cardnumber);
        $this->execute();
        $record = $this->fetchAllData();

        $newRecord = array();
        
        $ctr = 0;

        foreach($record as $r) 
        {
            if(preg_match("/RTG/", $r["ServiceName"])) 
            {
                $configuration = self::getConfigurationForRTGCAPI(
                                    strpos($_ServiceAPI[(int)$r["ServiceID"]-1], "ECFTEST"), 
                                    $_ServiceAPI[(int)$r["ServiceID"]-1], 
                                    $r["ServiceID"]);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG, $configuration );

                // EDITED CCT 07/11/2018 BEGIN
                //if($r["UserMode"] == 1)
                if(($r["UserMode"] == 1) || ($r["UserMode"] == 3))
                // EDITED CCT 07/11/2018 END
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["UBServiceLogin"]);
                } 
                else 
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                }
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else
                {
                    $r["PlayingBalance"] = 0;
                }
            }
            else if (preg_match("/Habanero/", $r["ServiceName"])) 
            {
                $url = $_ServiceAPI[(int)$r["ServiceID"]-1];
                $configuration = self::getConfigurationForHabaneroCAPI( $url,$_HABbrandID,$_HABapiKey);
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::HAB, $configuration );
                        
                //getterminalcredentials
                // EDITED CCT 07/11/2018 BEGIN
                if($r["UserMode"] == 0 || $r["UserMode"] == 2)
                {
                    $stmt = "SELECT ServicePassword FROM terminalservices 
                        WHERE ServiceID = ? AND TerminalID = ? AND Status = 1 AND isCreated = 1";
                }
                else  //getusercredentials
                { 
                    $stmt = "SELECT UBServicePassword FROM terminalsessions "
                            . "WHERE ServiceID = ? AND TerminalID = ?";
                }
                // EDITED CCT 07/11/2018 END                
                $this->prepare($stmt);
                $this->bindparameter(1, $r["ServiceID"]);
                $this->bindparameter(2, $r["TerminalID"]);
                $this->execute();
                $servicePwdResult = $this->fetchData();   

                //if user mode is terminal based, get each balances of each terminal
                if($r["UserMode"] == 0 || $r["UserMode"] == 2)
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"], $servicePwdResult['ServicePassword']);
                }
                //ADDED CCT 07/11/2018 BEGIN
                //if user mode is user based, get each balances of each casino mapped to a card
                else
                {
                    $serviceusername = $this->getLoyaltycardNumberLogin($r["TerminalID"]);   
                    $data = $_CasinoAPIHandler->GetBalance($serviceusername, $servicePwdResult['UBServicePassword']);
                }  //
                //ADDED CCT 07/11/2018 END

                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else
                {
                    $r["PlayingBalance"] = 0;
                }
            }            
            else if (preg_match("/e-Bingo/", $r["ServiceName"])) 
            {
                // e-Bingo balance is always zero
                $r["PlayingBalance"] = 0;
            }            
            
            $loyalty_result = json_decode($loyalty->getCardInfo2($cardnumber, $cardinfo, 1));
            $loyalty_result->CardInfo->IsEwallet == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            
            // EDITED CCT 07/11/2018 BEGIN
            //$r['UserMode'] == 1 ? $r['UserMode'] = "User Based" : $r['UserMode'] = "Terminal Based";
            (($r['UserMode'] == 1) || ($r['UserMode'] == 3)) ? $r['UserMode'] = "User Based" : $r['UserMode'] = "Terminal Based";
            // EDITED CCT 07/11/2018 END
                
            if($r["PlayingBalance"] ==  0)
            {
                $r["PlayingBalance"] = 0;
            }
            else
            {
                $r["PlayingBalance"] = number_format($r['PlayingBalance'], 2);
            }
            
            $row->rows[$ctr]["id"] = $r["TerminalID"];
            
            $row->rows[$ctr]["cell"] = array(
                $r["TerminalType"],
                $r["TerminalCode"],
                $r["PlayingBalance"],
                $r["UserMode"],
                $isEwallet
            );
            $ctr++;
        }
        return json_encode($row);
    }
    
    /**
     *This method returns an array composed of:
     * 1. Terminal ID
     * 2. Terminal Code
     * 3. Service ID
     * 4. Service Name
     * 5. Playing Balance
     * 
     * This is used by the Active Session and Terminal Balance Per Site Module
     * 
     * @param String $cardnumber
     * @param Mixed $_ServiceAPI
     * @param String $_CAPIUsername
     * @param String $_CAPIPassword
     * @param String $_CAPIPlayerName
     * @param String $_MicrogamingCurrency
     * @return Mixed 
     */
    public final function getPagcorActiveSessionPlayingBalanceub ($cardinfo, $cardnumber, $serviceusername, $_ServiceAPI, $_CAPIUsername, 
            $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname, $_PlayerAPI, $_ptsecretkey, $_HABbrandID = '',$_HABapiKey = '') 
    {
        include_once __DIR__.'/../../sys/class/CasinoCAPIHandler.class.php';
        include_once __DIR__.'/../../sys/class/LoyaltyUBWrapper.class.php';
        
        $loyalty = new LoyaltyUBWrapper();
        
        $row = new stdClass();
        
        $row->page = 1;
        
        $query = "SELECT  t.TerminalID, t.TerminalCode, 
                    CASE t.TerminalType WHEN 0
                        THEN 'Regular'
                        WHEN 1
                        THEN 'Genesis'
                        ELSE 'e-SAFE'
                    END AS TerminalType, 
                    rs.ServiceID, rs.ServiceName, ts.UBServiceLogin, ts.UserMode 
                  FROM terminalsessions ts INNER JOIN terminals t ON t.TerminalID = ts.TerminalID 
                        INNER JOIN ref_services rs ON rs.ServiceID = ts.ServiceID 
                  WHERE ts.LoyaltyCardNumber = :cardnum 
                  ORDER BY t.TerminalID ASC";
        
        $this->prepare($query);
        $this->bindParam(":cardnum", $cardnumber);
        $this->execute();
        
        $record = $this->fetchAllData();

        $newRecord = array();
        
        $ctr = 0;
        
        foreach($record as $r) 
        {
            if(preg_match("/RTG/", $r["ServiceName"])) 
            {
                $configuration = self::getConfigurationForRTGCAPI(
                                    strpos($_ServiceAPI[(int)$r["ServiceID"]-1], "ECFTEST"), 
                                    $_ServiceAPI[(int)$r["ServiceID"]-1], 
                                    $r["ServiceID"]);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG, $configuration );
                
                // EDITED CCT 07/11/2018 BEGIN
                //if($r["UserMode"] == 1)
                if(($r["UserMode"] == 1) || ($r["UserMode"] == 3))
                // EDITED CCT 07/11/2018 END
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["UBServiceLogin"]);
                } 
                else 
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                }
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else
                {
                    $r["PlayingBalance"] = 0;
                }
                
            }
            else if (preg_match("/Habanero/", $r["ServiceName"])) 
            {
                $url = $_ServiceAPI[(int)$r["ServiceID"]-1];
                $configuration = self::getConfigurationForHabaneroCAPI( $url,$_HABbrandID,$_HABapiKey);
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::HAB, $configuration );
                        
                //getterminalcredentials
                // EDITED CCT 07/11/2018 BEGIN
                if($r["UserMode"] == 0 || $r["UserMode"] == 2)
                {
                    $stmt = "SELECT ServicePassword FROM terminalservices 
                        WHERE ServiceID = ? AND TerminalID = ? AND Status = 1 AND isCreated = 1";
                }
                else  //getusercredentials
                { 
                    $stmt = "SELECT UBServicePassword FROM terminalsessions "
                            . "WHERE ServiceID = ? AND TerminalID = ?";
                }
                // EDITED CCT 07/11/2018 END                    
                $this->prepare($stmt);
                $this->bindparameter(1, $r["ServiceID"]);
                $this->bindparameter(2, $r["TerminalID"]);
                $this->execute();
                $servicePwdResult = $this->fetchData();   

                //if user mode is terminal based, get each balances of each terminal
                if($r["UserMode"] == 0 || $r["UserMode"] == 2)
                {
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"], $servicePwdResult['ServicePassword']);
                }
                //ADDED CCT 07/11/2018 BEGIN
                //if user mode is user based, get each balances of each casino mapped to a card
                else
                {
                    $serviceusername = $this->getLoyaltycardNumberLogin($r["TerminalID"]);   
                    $data = $_CasinoAPIHandler->GetBalance($serviceusername, $servicePwdResult['UBServicePassword']);
                }  //
                //ADDED CCT 07/11/2018 END
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else
                {
                    $r["PlayingBalance"] = 0;
                }
            }            
            else if (preg_match("/e-Bingo/", $r["ServiceName"])) 
            {
                // e-Bingo Balance is always zero
                $r["PlayingBalance"] = 0;
            }            
            
            $loyalty_result = json_decode($loyalty->getCardInfo2($cardnumber, $cardinfo, 1));

            //check if user mode is terminal or user based
            $loyalty_result->CardInfo->IsEwallet == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            
            // EDITED CCT 07/11/2018 BEGIN
            //$r['UserMode'] == 1 ? $r['UserMode'] = "User Based" : $r['UserMode'] = "Terminal Based";
            (($r['UserMode'] == 1) || ($r['UserMode'] == 3)) ? $r['UserMode'] = "User Based" : $r['UserMode'] = "Terminal Based";
            // EDITED CCT 07/11/2018 END
            
            if($r["PlayingBalance"] ==  0)
            {
                $r["PlayingBalance"] = 0;
            }
            else
            {
                $r["PlayingBalance"] = number_format($r['PlayingBalance'], 2);
            }
            
            $row->rows[$ctr]["id"] = $r["TerminalID"];
            
            $row->rows[$ctr]["cell"] = array(
                $r["TerminalType"],
                $r["TerminalCode"],
                $r["PlayingBalance"],
                $r["UserMode"],
                $isEwallet,
            );
            $ctr++;
        }
        return json_encode($row);
    }

    /**
     *This return the configuration RTG
     * 
     * @param Boolean $_isECFTEST
     * @param String $serverURI
     * @param Int $serverID
     * @return Mixed 
     */
    private static final function getConfigurationForRTGCAPI ($_isECFTEST, $serverURI, $serverID) 
    {
        $gdeposit = 503; 
        $gwithdraw = 502; 

        $configuration = array( 'URI' => $serverURI,
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                'certFilePath' => RTGCerts_DIR . $serverID  . '/cert.pem',
                                'keyFilePath' => RTGCerts_DIR . $serverID  . '/key.pem',
                                'depositMethodId' =>$gdeposit ,
                                'withdrawalMethodId' => $gwithdraw);
        return $configuration;
    }
    
    private static final function getConfigurationForHabaneroCAPI ($_HabaneroURI, $_HABbrandID, $_HABapiKey) 
    {
        $configuration = array( 'URI' => $_HabaneroURI,
                            'isCaching' => FALSE,
                            'isDebug' => TRUE,
                            'brandID'=>$_HABbrandID,
                            'apiKey'=>$_HABapiKey );
        return $configuration;
    }    
    
    //fget service name
    function getServiceName($serviceID)
    {
        $stmt = "SELECT ServiceName FROM ref_services WHERE ServiceID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $serviceID);
        $this->execute();
        $servicename = $this->fetchData();
        return $servicename = $servicename['ServiceName'];
    }
    
    public function checkUserMode($casino)
    {
        $sql = "SELECT UserMode FROM ref_services WHERE ServiceName LIKE ?";
        $this->prepare($sql);
        $this->bindparameter(1, $casino);
        $this->execute();
        $usermode = $this->fetchData();
        $usermode = $usermode['UserMode'];
        return $usermode;
    }
    
    function getCashOnHandDetails($datefrom, $dateto, $siteid) 
    {
        $listsite = array();
        $cohdata = array('TotalCashLoad' => 0, 
                         'TotalCashRedemption' => 0,
                         'TotalGenesisRedemption' => 0,
                         'TotalCashLoadGenesis'=> 0,
                         'TotalEsafeTicketLoadGenesis'=>0,
                         'TotalTicketLoadGenesis'=>0,
                         'TotalCashLoadEwallet'=> 0,
                         'TotalEwalletBancnet'=> 0,
                         'TotalEwalletCoupon'=> 0,
                         'TotalBancnetLoad'=> 0,
                         'TotalCouponLoad'=> 0,
                         'TotalEwalletRedemption' => 0,
                         'TotalRedemption'=>0,
                         'TotalMR' => 0);
        foreach ($siteid as $row)
        { 
            array_push($listsite, "".$row.""); 
        }
        $site = implode(',', $listsite);

        $query1 = "SELECT tr.SiteID, tr.CreatedByAID,

                                -- DEPOSIT CASH --
                                SUM(CASE tr.TransactionType
                                   WHEN 'D' THEN
                                     CASE tr.PaymentType
                                       WHEN 2 THEN 0 -- Coupon
                                       ELSE -- Not Coupon
                                         CASE IFNULL(tr.StackerSummaryID, '')
                                           WHEN '' THEN 
                                                CASE (SELECT COUNT(btl.BankTransactionLogID) as IsBancnet FROM banktransactionlogs btl
                                                            INNER JOIN transactionrequestlogs trl ON btl.TransactionRequestLogID = trl.TransactionRequestLogID
                                                      WHERE trl.TransactionReferenceID = tr.TransactionReferenceID)
                                                WHEN 0 THEN tr.Amount -- Cash
                                                ELSE 0 END 
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

                                -- RELOAD CASH --
                                SUM(CASE tr.TransactionType
                                   WHEN 'R' THEN
                                     CASE tr.PaymentType
                                       WHEN 2 THEN 0 -- Coupon
                                       ELSE -- Not Coupon
                                         CASE IFNULL(tr.StackerSummaryID, '')
                                           WHEN '' THEN 
                                                CASE (SELECT COUNT(btl.BankTransactionLogID) as IsBancnet FROM banktransactionlogs btl
                                                            INNER JOIN transactionrequestlogs trl ON btl.TransactionRequestLogID = trl.TransactionRequestLogID
                                                      WHERE trl.TransactionReferenceID = tr.TransactionReferenceID)
                                                WHEN 0 THEN tr.Amount -- Reload, Cash
                                                ELSE 0 END 
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
                                
                                -- DEPOSIT COUPON --
                                SUM(CASE tr.TransactionType
                                  WHEN 'D' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN tr.Amount
                                      ELSE 0
                                     END
                                  ELSE 0 END) As DepositCoupon,
                                  
                                -- RELOAD COUPON --
                                SUM(CASE tr.TransactionType
                                  WHEN 'R' THEN
                                    CASE tr.PaymentType
                                      WHEN 2 THEN tr.Amount
                                      ELSE 0
                                     END
                                  ELSE 0 END) As ReloadCoupon,
                                
                                -- DEPOSIT Bancnet --
                                SUM(CASE tr.TransactionType
                                   WHEN 'D' THEN
                                     CASE tr.PaymentType
                                       WHEN 2 THEN 0 -- Coupon
                                       ELSE -- Not Coupon
                                         CASE IFNULL(tr.StackerSummaryID, '')
                                            WHEN '' THEN 
                                                CASE (SELECT COUNT(btl.BankTransactionLogID) as IsBancnet FROM banktransactionlogs btl
                                                            INNER JOIN transactionrequestlogs trl ON btl.TransactionRequestLogID = trl.TransactionRequestLogID
                                                      WHERE trl.TransactionReferenceID = tr.TransactionReferenceID)
                                                WHEN 1 THEN tr.Amount -- Bancnet
                                                ELSE 0 END 
                                            ELSE 0 END
                                    END
                                   ELSE 0 -- Not Deposit
                                END) As DepositBancnet,
                                
                                -- RELOAD BANCNET --
                                SUM(CASE tr.TransactionType
                                   WHEN 'R' THEN
                                     CASE tr.PaymentType
                                       WHEN 2 THEN 0 -- Coupon
                                       ELSE -- Not Coupon
                                         CASE IFNULL(tr.StackerSummaryID, '')
                                            WHEN '' THEN 
                                                CASE (SELECT COUNT(btl.BankTransactionLogID) as IsBancnet FROM banktransactionlogs btl
                                                            INNER JOIN transactionrequestlogs trl ON btl.TransactionRequestLogID = trl.TransactionRequestLogID
                                        WHERE trl.TransactionReferenceID = tr.TransactionReferenceID)
                                                WHEN 1 THEN tr.Amount -- Reload, Bancnet
                                                ELSE 0 END 
                                            ELSE 0 END
                                     END
                                   ELSE 0 -- Not Reload
                                END) As ReloadBancnet,
                                
                                -- REDEMPTION CASHIER --
                                SUM(CASE tr.TransactionType
                                  WHEN 'W' THEN
                                        CASE a.AccountTypeID
                                          WHEN 4 THEN tr.Amount -- Cashier
                                          ELSE 0
                                        END -- Genesis
                                  ELSE 0 --  Not Redemption
                                END) As RedemptionCashier, 
                                
                                -- REDEMPTION GENESIS --
                                SUM(CASE tr.TransactionType
                                  WHEN 'W' THEN
                                        CASE a.AccountTypeID
                                          WHEN 15 THEN tr.
                                          Amount -- Cashier
                                          ELSE 0
                                        END -- Genesis
                                  ELSE 0 --  Not Redemption
                                END) As RedemptionGenesis, 
                                
                                -- Total Redemption --
                               SUM(CASE tr.TransactionType
                                    WHEN 'W' THEN
                                    tr.Amount -- Redemption
                                ELSE 0 --  Not Redemption
                              END) As TotalRedemption, 


                                tr.DateCreated
                                FROM transactiondetails tr INNER JOIN transactionsummary ts ON ts.TransactionsSummaryID = tr.TransactionSummaryID
                                INNER JOIN terminals t ON t.TerminalID = tr.TerminalID
                                INNER JOIN accounts a ON ts.CreatedByAID = a.AID
                                INNER JOIN sites s ON tr.SiteID = s.SiteID
                                WHERE tr.SiteID IN (".$site.")
                                  AND tr.DateCreated >= ? AND tr.DateCreated < ?
                                  AND tr.Status IN(1,4) AND a.AccountTypeID NOT IN (17)
                                GROUP By tr.SiteID";
        
        $query2 = "SELECT SiteID, 
                        -- Ewallet Cash Deposits -- 
                        SUM(CASE IFNULL(TraceNumber,'')
                            WHEN '' THEN  
                                    CASE IFNULL(ReferenceNumber, '')
                                    WHEN '' THEN -- if not bancnet
                                            CASE TransType
                                            WHEN 'D' THEN -- if deposit
                                                    CASE PaymentType 
                                                        WHEN 1 THEN Amount -- if Cash
                                                        ELSE 0 -- if not Cash
                                                    END
                                            ELSE 0 -- if not deposit
                                            END
                                    ELSE 0 -- if bancnet
                                    END
                            ELSE 0
                        END) AS EwalletCashDeposit,

                        -- Ewallet Bancnet Deposits -- 
                        SUM(CASE IFNULL(TraceNumber,'')
                                WHEN '' THEN 0
                                ELSE CASE IFNULL(ReferenceNumber, '')
                                        WHEN '' THEN 0 -- if not bancnet
                                        ELSE CASE TransType -- if bancnet
                                                WHEN 'D' THEN Amount -- if deposit
                                                ELSE 0 -- if not deposit
                                                END
                                        END
                        END) AS EwalletBancnetDeposit, 
                        
                        -- Ewallet Coupon Deposits -- 
                        SUM(CASE TransType  
                            WHEN 'D' THEN 
                                CASE PaymentType 
                                    WHEN 2 THEN Amount 
                                    ELSE 0
                                END 
                            ELSE 0 
                        END) AS EwalletVoucherDeposit, 
                        
                        -- Ewallet Ticket Loads -- 
                        SUM(CASE TransType
                                WHEN 'D' THEN -- if deposit
                                        CASE PaymentType
                                            WHEN 3 THEN Amount -- if voucher
                                            ELSE 0 -- if not voucher
                                        END
                                ELSE 0 -- if not deposit
                        END) AS EwalletTicketLoad, 

                        -- Total e-SAFE Withdrawal -- 
                        SUM(CASE TransType
                                WHEN 'W' THEN Amount 
                                ELSE 0 -- if not redemption
                        END) AS EwalletRedemption, 
                        
                        -- Total e-SAFE Cash Withdrawal -- 
                        SUM(CASE TransType
                                WHEN 'W' THEN 
                                    CASE PaymentType -- redemption in genesis not included
                                        WHEN 1 THEN Amount 
                                        ELSE 0
                                    END
                                ELSE 0 -- if not redemption
                        END) AS EwalletCashRedemption, 
                        
                        -- Total e-SAFE Genesis Withdrawal -- 
                        SUM(CASE TransType
                                WHEN 'W' THEN 
                                    CASE PaymentType -- redemption in genesis not included
                                        WHEN 3 THEN Amount 
                                        ELSE 0
                                    END
                                ELSE 0 -- if not redemption
                        END) AS EwalletGenRedemption 

                    FROM ewallettrans WHERE StartDate >= ? AND StartDate < ?
                    AND SiteID IN (".$site.") AND Status IN (1,3) GROUP BY SiteID";
        
        // CCT 06/11/2019 BEGIN -- filter MR Status = 1
        $query3 = "SELECT SiteID, SUM(ActualAmount) AS ManualRedemption FROM manualredemptions
                            WHERE TransactionDate >= ? AND TransactionDate < ? 
                                    AND Status = 1 
                                    AND SiteID IN (".$site.") GROUP BY SiteID";   
        // CCT 06/11/2019 END
        
        //Get total deposit cash and reload cash (with bancnet transaction included)
        $this->prepare($query1);
        $this->bindparameter(1, $datefrom);
        $this->bindparameter(2, $dateto);
        $this->execute();
        $rows1 = $this->fetchAllData();
        //Get the summation of total cash load and cash redemption
        foreach ($rows1 as $value) 
        {
            $cohdata['TotalCashLoad'] += (float)$value['DepositCash'];
            $cohdata['TotalCashLoad'] += (float)$value['ReloadCash'];
            $cohdata['TotalTicketLoadGenesis'] += (float)$value['DepositTicket'];
            $cohdata['TotalTicketLoadGenesis'] += (float)$value['ReloadTicket'];
            $cohdata['TotalCashLoad'] += (float)$value['ReloadBancnet'];
            $cohdata['TotalCashLoad'] += (float)$value['DepositBancnet'];
            $cohdata['TotalCouponLoad'] += (float)$value['DepositCoupon'];
            $cohdata['TotalCouponLoad'] += (float)$value['ReloadCoupon'];
            $cohdata['TotalCashRedemption'] += (float)$value['RedemptionCashier'];
            //$cohdata['TotalGenesisRedemption'] += (float)$value['RedemptionGenesis']; //Non e-SAFE
            $cohdata['TotalRedemption'] += (float)$value['TotalRedemption'];
        }
       
        //Get total e-SAFE loaded cash (with bancnet transaction included)
        $this->prepare($query2);
        $this->bindparameter(1, $datefrom);
        $this->bindparameter(2, $dateto);
        $this->execute();
        $rows2 = $this->fetchAllData();
        
        //Add the total e-SAFE cash load and e-SAFE cash redemption
        foreach ($rows2 as $value) 
        {
            $cohdata['TotalCashLoadEwallet'] += (float)$value['EwalletCashDeposit'];
            $cohdata['TotalCashLoadEwallet'] += (float)$value['EwalletBancnetDeposit'];
            $cohdata['TotalEsafeTicketLoadGenesis'] += (float)$value['EwalletTicketLoad'];
            $cohdata['TotalEwalletCoupon'] += (float)$value['EwalletVoucherDeposit'];
            $cohdata['TotalEwalletRedemption'] += (float)$value['EwalletCashRedemption'];
            $cohdata['TotalGenesisRedemption'] += (float)$value['EwalletGenRedemption']; //e-SAFE
        }

        //Get total manual redemption per site
        $this->prepare($query3);
        $this->bindparameter(1, $datefrom);
        $this->bindparameter(2, $dateto);
        $this->execute();
        $rows3 = $this->fetchAllData();
        
        //Add the total e-SAFE cash load and e-SAFE cash redemption
        foreach ($rows3 as $value) 
        {
            $cohdata['TotalMR'] += (float)$value['ManualRedemption'];
        }

        unset($listsite);
        return $cohdata;
    }
    
    // for site transactions report
    // for ewallet loads and withdraws
    //@date added 03-20-2015
    //@author fdlsison
    function viewewtransactionperday($zdateFROM, $zdateto, $zsiteID)
    {
        $listsite = array();
        foreach ($zsiteID as $row)
        {
            array_push($listsite, "'".$row."'");
        }
        $site = implode(',', $listsite);
        $stmt1 = "SELECT EwalletTransID, SiteID, LoyaltyCardNumber, StartDate, EndDate, TransType, 
                    IFNULL(CASE WHEN TransType = 'D' THEN Amount END,0) AS EWLoads, 
                    IFNULL(CASE WHEN TransType = 'W' THEN Amount END,0) AS EWWithdrawals 
                  FROM ewallettrans 
                  WHERE SiteID IN(".$site.") 
                      AND StartDate >= ? AND StartDate < ? 
                      AND Status IN(1,3)";
        $this->prepare($stmt1);
        $this->bindparameter(1, $zdateFROM);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows1 = $this->fetchAllData();
        unset($listsite);
        return $rows1;
    }
    
    function getTotalTicketEncashment($zdateFROM, $zdateto, $zsiteID) 
    {
        $listsite = array();
        $totalencashedtickets = 0;
        foreach ($zsiteID as $row)
        {
            array_push($listsite, "'".$row."'");
        }
        $site = implode(',', $listsite);
        $stmt = "SELECT SiteID, IFNULL(SUM(Amount), 0) AS EncashedTickets 
                FROM vouchermanagement.tickets  -- Encashed Tickets 
                WHERE DateEncashed >= ? AND DateEncashed < ? 
                        AND SiteID IN (".$site.") GROUP BY SiteID";
        $this->prepare($stmt);
        $this->bindparameter(1, $zdateFROM);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows = $this->fetchAllData();
        
        //combine total encashed tickets
        foreach ($rows as $value) 
        {
            $totalencashedtickets += (float)$value['EncashedTickets'];
        }
        return $totalencashedtickets;
    }
    
    public function getSiteByAID($aid) 
    {
        $sql = "SELECT DISTINCT s.SiteID, s.SiteCode 
                FROM siteaccounts sa INNER JOIN accounts a ON a.AID = sa.AID 
                    INNER JOIN sites s ON s.SiteID = sa.SiteID 
                WHERE a.AID = ? AND sa.Status = 1 AND s.Status = 1";
        $this->prepare($sql);
        $this->bindparameter(1, $aid);
        $this->execute();
        $result = $this->fetchAllData();
        return $result;
    }
    
    public function getGrossHoldTB($siteID, $serviceGroupIDs, $transtype, $datefrom, $dateto) 
    {
        if ($transtype == "DR") 
        {
            $sql = "SELECT IFNULL(SUM(td.Amount), 0) as Amount 
                    FROM transactiondetails td INNER JOIN ref_services rs ON rs.ServiceID = td.ServiceID 
                    WHERE td.SiteID = ? 
                        AND rs.ServiceGroupID IN (".implode(",", $serviceGroupIDs).") 
                        AND td.TransactionType IN ('D', 'R') 
                        AND td.Status IN (1, 4) 
                        AND td.DateCreated >= ? AND td.DateCreated < ?"; 
        }
        else if ($transtype == "W")
        {
            $sql = "SELECT IFNULL(SUM(td.Amount), 0) as Amount 
                    FROM transactiondetails td INNER JOIN ref_services rs ON rs.ServiceID = td.ServiceID 
                    WHERE td.SiteID = ? 
                        AND rs.ServiceGroupID IN (".implode(",", $serviceGroupIDs).") 
                        AND td.TransactionType IN ('W') 
                        AND td.Status IN (1, 4) 
                        AND td.DateCreated >= ? AND td.DateCreated < ?"; 
        }

        $datefrom = $datefrom." 06:00:00";
        $dateto = $dateto." 06:00:00";
        
        $this->prepare($sql);
        $this->bindparameter(1, $siteID);
        $this->bindparameter(2, $datefrom);
        $this->bindparameter(3, $dateto);
        $this->execute();
        $result = $this->fetchData();
        return $result;
    }
    
    /**
     * @param type $siteID
     * @param type $serviceIDs
     * @param type $datefrom
     * @param type $dateto
     * @return type
     */
    public function getManualRedemptionTrans($siteID, $serviceGroupIDs, $datefrom, $dateto) 
    {
        $sql = "SELECT IFNULL(SUM(ActualAmount), 0) as Amount 
                FROM manualredemptions mr INNER JOIN ref_services rs ON rs.ServiceID = mr.ServiceID 
                WHERE rs.ServiceGroupID IN (".implode(",", $serviceGroupIDs).") 
                    AND mr.Status = 1 
                    AND mr.SiteID = ?  
                    AND mr.TransactionDate >= ? AND mr.TransactionDate < ? 
                ORDER BY mr.ManualRedemptionsID DESC";
        
        $datefrom = $datefrom." 06:00:00";
        $dateto = $dateto." 06:00:00";
        $this->prepare($sql);
        $this->bindparameter(1, $siteID);
        $this->bindparameter(2, $datefrom);
        $this->bindparameter(3, $dateto);
        $this->execute();
        $result = $this->fetchData();
        return $result;
    }
    
    public function getGrossHoldeSAFE($siteID, $datefrom, $dateto) 
    {
        $sql = "SELECT TransactionDate, s.SiteCode Login, SUM(ts.StartBalance) StartBalance, 
                SUM(ts.WalletReloads) WalletReloads, SUM(ts.EndBalance) EndBalance, SUM(IFNULL(tl.GenesisWithdrawal,0)) GenesisWithdrawal 
                FROM transactionsummary ts INNER JOIN sites s ON ts.SiteID = s.SiteID 
                    LEFT JOIN transactionsummarylogs tl tl.TransactionSummaryID = ts.TransactionsSummaryID 
                WHERE ts.SiteID = ? 
                    AND ts.DateEnded >= ? AND ts.DateEnded < ?";
        
        $datefrom = $datefrom." 06:00:00";
        $dateto = $dateto." 06:00:00";
        
        $this->prepare($sql);
        $this->bindparameter(1, $siteID);
        $this->bindparameter(2, $datefrom);
        $this->bindparameter(3, $dateto);
        $this->execute();
        $result = $this->fetchData();
        return $result;
    }
    
    function getEncashedTicketsV15($zsiteID, $zdatefrom, $zdateto) 
    {
        $listsite = array();
        $totalencashedtickets = 0.00;
        foreach ($zsiteID as $row)
        {
            array_push($listsite, "'".$row."'");
        }
        
        for ($i = 0; $i < count($listsite); $i++) 
        {
            $query = "SELECT IFNULL(SUM(Amount), 0) AS EncashedTicketsV2, t.UpdatedByAID, t.SiteID, ad.Name   
                        FROM vouchermanagement.tickets t LEFT JOIN accountdetails ad ON t.UpdatedByAID = ad.AID 
                        WHERE t.DateEncashed >= :startdate AND t.DateEncashed < :enddate 
                            AND t.UpdatedByAID IN (SELECT sacct.AID 
                                                    FROM siteaccounts sacct 
                                                    WHERE sacct.SiteID IN (".$listsite[$i]."))
                            AND t.SiteID = ".$listsite[$i]." 
                            AND TicketCode NOT IN (SELECT IFNULL(ss.TicketCode, '') 
                                                    FROM stackermanagement.stackersummary ss 
                                                        INNER JOIN ewallettrans ewt ON ewt.StackerSummaryID = ss.StackerSummaryID 
                                                    WHERE ewt.SiteID = ".$listsite[$i]." AND ewt.TransType = 'W')";
            $this->prepare($query);
            $this->bindparameter(":startdate", $zdatefrom);
            $this->bindparameter(":enddate", $zdateto);
            $this->execute();
            $rows = $this->fetchAllData();
             
            if (count($rows) > 0) 
            {
                $totalencashedtickets += (float)$rows[0]['EncashedTicketsV2'];
            }
        }
        return $totalencashedtickets;
    }
}
?>
