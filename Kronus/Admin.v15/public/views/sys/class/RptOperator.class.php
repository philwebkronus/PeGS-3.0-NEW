<?php

/**
 * 
 *Created BY: Edson L. Perez
 *Date Created: September 22, 2011
 */
include 'DbHandler.class.php';
ini_set('display_errors',true);
ini_set('log_errors',true);

class RptOperator extends DBHandler
{
    public function __construct($connectionString) 
    {
        parent::__construct($connectionString);
    }
    
    //view all accounts --> 
    function viewsitebyowner($zaid)
    {
        $stmt = "SELECT DISTINCT b.SiteID FROM accounts AS a INNER JOIN siteaccounts AS b 
                 ON a.AID = b.AID WHERE a.AID = '".$zaid."' AND b.Status = 1";
        $this->executeQuery($stmt); 
        return $this->fetchAllData();
    }
    
    // for site transactions report
	// for site transactions report
    function viewtransactionperday($zdateFROM, $zdateto, $zsiteID)
    {
        $listsite = array();
        foreach ($zsiteID as $row) {
            array_push($listsite,$row);
        }
        $site = implode(',', $listsite);
        $stmt = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, ts.LoyaltyCardNumber, tr.TerminalID,tr.SiteID,
        t.TerminalCode as TerminalCode, tr.TransactionType, sum(tr.Amount) AS amount,a.UserName from transactiondetails tr
        inner join transactionrequestlogs trl on tr.TransactionReferenceID = trl.TransactionReferenceID
        inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID
        inner join terminals t on t.TerminalID = tr.TerminalID
        inner join accounts a on a.AID = tr.CreatedByAID
        where tr.SiteID IN(".$site.") AND
        ts.DateStarted >= ? and ts.DateStarted < ? AND tr.Status IN(1,4)
        and trl.Status IN(1,3) and (tr.StackerSummaryID IS NULL OR trim(tr.StackerSummaryID) <> '')
        group by tr.TransactionType,tr.TransactionSummaryID order by t.TerminalCode,ts.DateStarted Desc ";
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
                     FROM sitebalance sb 
                     INNER JOIN sites s ON sb.SiteID = s.SiteID WHERE sb.SiteID IN (".$siteID.") ORDER BY s.SiteCode ASC";
        }
        else
        {
            $stmt = "SELECT sb.SiteID, sb.Balance, sb.MinBalance, sb.MaxBalance, sb.LastTransactionDate,
                     sb.TopUpType, sb.PickUpTag, s.SiteName, s.SiteCode, if(isnull(s.POSAccountNo), '0000000000', s.POSAccountNo) AS POS
                     FROM sitebalance sb 
                     INNER JOIN sites s ON sb.SiteID = s.SiteID
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
    public final function getActiveSessionCount ($siteID, $cardnumber) {
        if($siteID == ''){
            $query = "
                  SELECT  
                    count(t.TerminalID) as ActiveSession
                  FROM 
                    terminalsessions as ts, 
                    terminals as t
                  WHERE
                    t.TerminalID = ts.TerminalID
                    AND
                    ts.LoyaltyCardNumber = :cardnumber";
        
        $this->prepare($query);
        
        $this->bindParam(":cardnumber", $cardnumber);
        }
        else{
            $query = "
                  SELECT  
                    count(t.TerminalID) as ActiveSession
                  FROM 
                    terminalsessions as ts, 
                    terminals as t
                  WHERE
                    t.TerminalID = ts.TerminalID
                    AND
                    t.SiteID = :siteID";
        
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
    public final function getActiveSessionCountMod ($cardnumber, $usermode, $siteID) {
        if($siteID == ''){
        $query = "
                  SELECT  
                    count(t.TerminalID) as ActiveSession
                  FROM 
                    terminalsessions as ts, 
                    terminals as t
                  WHERE
                    t.TerminalID = ts.TerminalID
                    AND
                    ts.LoyaltyCardNumber = :cardnumber
                    AND ts.UserMode = :usermode";
        
        $this->prepare($query);
        
        $this->bindParam(":cardnumber", $cardnumber);
        $this->bindParam(":usermode", $usermode);
        }
        else{
        
            $query = "
                  SELECT  
                    count(t.TerminalID) as ActiveSession
                  FROM 
                    terminalsessions as ts, 
                    terminals as t
                  WHERE
                    t.TerminalID = ts.TerminalID
                    AND
                    t.SiteID = :siteID
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
    public function getLoyaltycardNumberLogin($terminal){
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
    public final function getActiveSessionPlayingBalance ($cardinfo, $siteID, $_ServiceAPI,$_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,$_PlayerAPI,$_ptsecretkey) {
        
        include_once __DIR__.'/../../sys/class/CasinoCAPIHandler.class.php';
        include_once __DIR__.'/../../sys/class/LoyaltyUBWrapper.class.php';
        
        $loyalty = new LoyaltyUBWrapper();
        
        $row = new stdClass();
        
        $row->page = 1;
        
        $query = "
                  SELECT  
                    t.TerminalID, 
                    t.TerminalCode, 
                    CASE t.TerminalType WHEN 0
                        THEN 'Regular'
                        WHEN 1
                        THEN 'Genesis'
                        ELSE 'e-SAFE'
                    END AS TerminalType, 
                    rs.ServiceID,
                    rs.ServiceName,
                    ts.UBServiceLogin,
                    ts.UserMode, 
                    ts.LoyaltyCardNumber
                  FROM 
                    terminalsessions as ts, 
                    terminals as t,
                    ref_services as rs
                  WHERE
                    t.TerminalID = ts.TerminalID
                    AND
                    ts.ServiceID = rs.ServiceID
                    AND
                    t.SiteID = :siteID
                 
                  ORDER BY
                    t.TerminalID ASC";
        
        $this->prepare($query);
        
        $this->bindParam(":siteID", $siteID);
        
        $this->execute();
        
        $record = $this->fetchAllData();

        $newRecord = array();
        
        $ctr = 0;
        
        foreach($record as $r) {
            
            if(preg_match("/RTG/", $r["ServiceName"])) {
                
                $configuration = self::getConfigurationForRTGCAPI(
                                    strpos($_ServiceAPI[(int)$r["ServiceID"]-1], "ECFTEST"), 
                                    $_ServiceAPI[(int)$r["ServiceID"]-1], 
                                    $r["ServiceID"]);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG, $configuration );
                
                if($r["UserMode"] == 1){
                    $data = $_CasinoAPIHandler->GetBalance($r["UBServiceLogin"]);
                } else {
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
            else if (preg_match("/MG/", $r["ServiceName"])) {

                $configuration = self::getConfigurationForMGCAPI($r["ServiceID"], 
                                $_ServiceAPI, $_CAPIUsername, $_CAPIPassword, 
                                $_CAPIPlayerName, $_MicrogamingCurrency);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::MG, $configuration );
                
                $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);

                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                } 
                else{
                    $r["PlayingBalance"] = 0;
                }
                
            }
            else if (preg_match("/PT/", $r["ServiceName"])) {
          
                $url = $_PlayerAPI[(int)$r["ServiceID"]-1];
                $configuration = self::getConfigurationForPTCAPI( $url,$_ptcasinoname,$_ptsecretkey);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::PT, $configuration );
                
                //if user mode is terminal based, get each balances of each terminal
                if($r["UserMode"] == 0){
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                }
                //if user mode is user based, get each balances of each casino mapped to a card
                else
                {
                    $serviceusername = $this->getLoyaltycardNumberLogin($r["TerminalID"]);   
                    
                    $data = $_CasinoAPIHandler->GetBalance($serviceusername);
                }    
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else{
                    $r["PlayingBalance"] = 0;
                }
            }
            
            $loyalty_result = json_decode($loyalty->getCardInfo2($r['LoyaltyCardNumber'], $cardinfo, 1));
            
            $loyalty_result->CardInfo->IsEwallet == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            $r['UserMode'] == 0 ? $r['UserMode'] = "Terminal Based" : $r['UserMode'] = "User Based";
            
            if($r["PlayingBalance"] == 0){
               
                $r["PlayingBalance"] = "N/A";
            }
            else{
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
    public final function getPagcorActiveSessionPlayingBalance ($cardinfo, $siteID, $_ServiceAPI,$_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,$_PlayerAPI,$_ptsecretkey) {
        
        include_once __DIR__.'/../../sys/class/CasinoCAPIHandler.class.php';
        include_once __DIR__.'/../../sys/class/LoyaltyUBWrapper.class.php';
        
        $loyalty = new LoyaltyUBWrapper();
        
        $row = new stdClass();
        
        $row->page = 1;
        
        $query = "
                SELECT  
                t.TerminalID, 
                t.TerminalCode, 
                rs.ServiceID,
                rs.ServiceName,
                ts.UBServiceLogin,
                ts.UserMode,
                ts.LoyaltyCardNumber
                FROM 
                terminalsessions as ts, 
                terminals as t,
                ref_services as rs
                WHERE
                t.TerminalID = ts.TerminalID
                AND
                ts.ServiceID = rs.ServiceID
                AND
                t.SiteID = :siteID
                ORDER BY
                t.TerminalID ASC";
        
        $this->prepare($query);
        
        $this->bindParam(":siteID", $siteID);
        
        $this->execute();
        
        $record = $this->fetchAllData();

        $newRecord = array();
        
        $ctr = 0;
        
        foreach($record as $r) {
            
            if(preg_match("/RTG/", $r["ServiceName"])) {
                
                $configuration = self::getConfigurationForRTGCAPI(
                                    strpos($_ServiceAPI[(int)$r["ServiceID"]-1], "ECFTEST"), 
                                    $_ServiceAPI[(int)$r["ServiceID"]-1], 
                                    $r["ServiceID"]);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG, $configuration );
                
                if($r["UserMode"] == 1){
                    $data = $_CasinoAPIHandler->GetBalance($r["UBServiceLogin"]);
                } else {
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
            else if (preg_match("/MG/", $r["ServiceName"])) {

                $configuration = self::getConfigurationForMGCAPI($r["ServiceID"], 
                                $_ServiceAPI, $_CAPIUsername, $_CAPIPassword, 
                                $_CAPIPlayerName, $_MicrogamingCurrency);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::MG, $configuration );
                
                $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);

                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                } 
                else{
                    $r["PlayingBalance"] = 0;
                }
                
            }
            else if (preg_match("/PT/", $r["ServiceName"])) {
          
                $url = $_PlayerAPI[(int)$r["ServiceID"]-1];
                $configuration = self::getConfigurationForPTCAPI( $url,$_ptcasinoname,$_ptsecretkey);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::PT, $configuration );
                
                //if user mode is terminal based, get each balances of each terminal
                if($r["UserMode"] == 0){
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                }
                //if user mode is user based, get each balances of each casino mapped to a card
                else
                {
                    $serviceusername = $this->getLoyaltycardNumberLogin($r["TerminalID"]);   
                    
                    $data = $_CasinoAPIHandler->GetBalance($serviceusername);
                }    
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else{
                    $r["PlayingBalance"] = 0;
                }
            }
            
            $loyalty_result = json_decode($loyalty->getCardInfo2($r['LoyaltyCardNumber'], $cardinfo, 1));
            
            $loyalty_result->CardInfo->IsEwallet == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            $r['UserMode'] == 0 ? $r['UserMode'] = "Terminal Based" : $r['UserMode'] = "User Based";
            
            if($r["PlayingBalance"] == 0){
               
                $r["PlayingBalance"] = "N/A";
            }
            else{
                $r["PlayingBalance"] = number_format($r['PlayingBalance'], 2);
            }
            
            
            $row->rows[$ctr]["id"] = $r["TerminalID"];
            
            $row->rows[$ctr]["cell"] = array(
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
    public final function getActiveSessionPlayingBalanceub ($cardinfo, $cardnumber, $serviceusername, $_ServiceAPI,$_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,$_PlayerAPI,$_ptsecretkey) {
        
        include_once __DIR__.'/../../sys/class/CasinoCAPIHandler.class.php';
        include_once __DIR__.'/../../sys/class/LoyaltyUBWrapper.class.php';
        
        $loyalty = new LoyaltyUBWrapper();
        
        $row = new stdClass();
        
        $row->page = 1;
        
        $query = "
                  SELECT  
                    t.TerminalID, 
                    t.TerminalCode, 
                    CASE t.TerminalType WHEN 0
                        THEN 'Regular'
                        WHEN 1
                        THEN 'Genesis'
                        ELSE 'e-SAFE'
                    END AS TerminalType, 
                    rs.ServiceID,
                    rs.ServiceName,
                    ts.UBServiceLogin,
                    ts.UserMode
                  FROM 
                    terminalsessions as ts, 
                    terminals as t,
                    ref_services as rs
                  WHERE
                    t.TerminalID = ts.TerminalID
                    AND
                    ts.ServiceID = rs.ServiceID
                    AND
                    ts.LoyaltyCardNumber = :cardnum
                  ORDER BY
                    t.TerminalID ASC";
        
        $this->prepare($query);
        
        $this->bindParam(":cardnum", $cardnumber);
        
        $this->execute();
        
        $record = $this->fetchAllData();

        $newRecord = array();
        
        $ctr = 0;

        foreach($record as $r) {
            
            if(preg_match("/RTG/", $r["ServiceName"])) {
                
                $configuration = self::getConfigurationForRTGCAPI(
                                    strpos($_ServiceAPI[(int)$r["ServiceID"]-1], "ECFTEST"), 
                                    $_ServiceAPI[(int)$r["ServiceID"]-1], 
                                    $r["ServiceID"]);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG, $configuration );

                if($r["UserMode"] == 1){
                    $data = $_CasinoAPIHandler->GetBalance($r["UBServiceLogin"]);
                } else {
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
            else if (preg_match("/MG/", $r["ServiceName"])) {

                $configuration = self::getConfigurationForMGCAPI($r["ServiceID"], 
                                $_ServiceAPI, $_CAPIUsername, $_CAPIPassword, 
                                $_CAPIPlayerName, $_MicrogamingCurrency);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::MG, $configuration );
                
                $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);

                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                } 
                else{
                    $r["PlayingBalance"] = 0;
                }
                
            }
            else if (preg_match("/PT/", $r["ServiceName"])) {
                
                $url = $_PlayerAPI[(int)$r["ServiceID"]-1];
                $configuration = self::getConfigurationForPTCAPI( $url,$_ptcasinoname,$_ptsecretkey);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::PT, $configuration );
                
                if($r["UserMode"] == 0){
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                }
                else
                {
                    $data = $_CasinoAPIHandler->GetBalance($serviceusername);
                }    
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else{
                    $r["PlayingBalance"] = 0;
                }        
            }
            
            $loyalty_result = json_decode($loyalty->getCardInfo2($cardnumber, $cardinfo, 1));
            
            $loyalty_result->CardInfo->IsEwallet == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            $r['UserMode'] == 0 ? $r['UserMode'] = "Terminal Based" : $r['UserMode'] = "User Based";
                

            if($r["PlayingBalance"] ==  0){

                $r["PlayingBalance"] == "N/A";
            }
            else{
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
    public final function getPagcorActiveSessionPlayingBalanceub ($cardinfo, $cardnumber, $serviceusername, $_ServiceAPI,$_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,$_PlayerAPI,$_ptsecretkey) {
        
        include_once __DIR__.'/../../sys/class/CasinoCAPIHandler.class.php';
        include_once __DIR__.'/../../sys/class/LoyaltyUBWrapper.class.php';
        
        $loyalty = new LoyaltyUBWrapper();
        
        $row = new stdClass();
        
        $row->page = 1;
        
        $query = "
                  SELECT  
                    t.TerminalID, 
                    t.TerminalCode, 
                    rs.ServiceID,
                    rs.ServiceName,
                    ts.UBServiceLogin,
                    ts.UserMode
                  FROM 
                    terminalsessions as ts, 
                    terminals as t,
                    ref_services as rs
                  WHERE
                    t.TerminalID = ts.TerminalID
                    AND
                    ts.ServiceID = rs.ServiceID
                    AND
                    ts.LoyaltyCardNumber = :cardnum
                  ORDER BY
                    t.TerminalID ASC";
        
        $this->prepare($query);
        
        $this->bindParam(":cardnum", $cardnumber);
        
        $this->execute();
        
        $record = $this->fetchAllData();

        $newRecord = array();
        
        $ctr = 0;
        
        foreach($record as $r) {
            
            if(preg_match("/RTG/", $r["ServiceName"])) {
                
                $configuration = self::getConfigurationForRTGCAPI(
                                    strpos($_ServiceAPI[(int)$r["ServiceID"]-1], "ECFTEST"), 
                                    $_ServiceAPI[(int)$r["ServiceID"]-1], 
                                    $r["ServiceID"]);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG, $configuration );
                
                if($r["UserMode"] == 1){
                    $data = $_CasinoAPIHandler->GetBalance($r["UBServiceLogin"]);
                } else {
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
            else if (preg_match("/MG/", $r["ServiceName"])) {

                $configuration = self::getConfigurationForMGCAPI($r["ServiceID"], 
                                $_ServiceAPI, $_CAPIUsername, $_CAPIPassword, 
                                $_CAPIPlayerName, $_MicrogamingCurrency);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::MG, $configuration );
                
                $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);

                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                } 
                else{
                    $r["PlayingBalance"] = 0;
                }
                
            }
            else if (preg_match("/PT/", $r["ServiceName"])) {
                
                $url = $_PlayerAPI[(int)$r["ServiceID"]-1];
                $configuration = self::getConfigurationForPTCAPI( $url,$_ptcasinoname,$_ptsecretkey);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::PT, $configuration );
                
                if($r["UserMode"] == 0){
                    $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                }
                else
                {
                    $data = $_CasinoAPIHandler->GetBalance($serviceusername);
                }    
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else{
                    $r["PlayingBalance"] = 0;
                }
                
                
            }
            
            $loyalty_result = json_decode($loyalty->getCardInfo2($cardnumber, $cardinfo, 1));

            //check if user mode is terminal or user based
            
            $loyalty_result->CardInfo->IsEwallet == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            $r['UserMode'] == 0 ? $r['UserMode'] = "Terminal Based" : $r['UserMode'] = "User Based";

            if($r["PlayingBalance"] ==  0){

                $r["PlayingBalance"] == "N/A";
            }
            else{
                $r["PlayingBalance"] = number_format($r['PlayingBalance'], 2);
            }
            
            
            $row->rows[$ctr]["id"] = $r["TerminalID"];
            
            $row->rows[$ctr]["cell"] = array(
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
    private static final function getConfigurationForRTGCAPI ($_isECFTEST, $serverURI, $serverID) {
        
        if(!$_isECFTEST) {
            
            $gdeposit = 502;
            $gwithdraw = 503; 
            
        }
        else {
            
            $gdeposit = 503;
            $gwithdraw = 502;
        }

        $configuration = array( 'URI' => $serverURI,
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                'certFilePath' => RTGCerts_DIR . $serverID  . '/cert.pem',
                                'keyFilePath' => RTGCerts_DIR . $serverID  . '/key.pem',
                                'depositMethodId' =>$gdeposit ,
                                'withdrawalMethodId' => $gwithdraw);
        
        return $configuration;
        
    }
    
    /**
     *This returns the configuration for MG
     * 
     * @param Int $serverID
     * @param Mixed $_ServiceAPI
     * @param String $_CAPIUsername
     * @param String $_CAPIPassword
     * @param String $_CAPIPlayerName
     * @param String $_MicrogamingCurrency
     * @return Mixed 
     */
    private static final function getConfigurationForMGCAPI ($serverID, $_ServiceAPI,$_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency) {
        
        $_MGCredentials = $_ServiceAPI[$serverID -1]; 
        list($mgurl, $mgserverID) =  $_MGCredentials;

        $configuration = array( 'URI' => $mgurl,
                            'isCaching' => FALSE,
                            'isDebug' => TRUE,
                            'authLogin'=>$_CAPIUsername,
                            'authPassword'=>$_CAPIPassword,
                            'playerName'=>$_CAPIPlayerName,
                            'serverID'=>$mgserverID,
                            'currency' => $_MicrogamingCurrency );
        
        return $configuration;
        
    }
    
    private static final function getConfigurationForPTCAPI ($_ptURI, $_CAPIUsername,$_CAPISecretKey) {
        
        $configuration = array( 'URI' => $_ptURI,
                            'isCaching' => FALSE,
                            'isDebug' => TRUE,
                            'authLogin'=>$_CAPIUsername,
                            'secretKey'=>$_CAPISecretKey );
        
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
    
    
    public function checkUserMode($casino){
        $sql = "SELECT UserMode FROM ref_services WHERE ServiceName LIKE ?";
        $this->prepare($sql);
        $this->bindparameter(1, $casino);
        $this->execute();
        $usermode = $this->fetchData();
        $usermode = $usermode['UserMode'];
        return $usermode;
    }
    
    
    function getCashOnHandDetails($datefrom, $dateto, $siteid) {
        $listsite = array();
        $cohdata = array('TotalCashLoad' => 0, 'TotalCashRedemption' => 0, 'TotalMR' => 0);
        foreach ($siteid as $row){ array_push($listsite, "".$row.""); }
        $site = implode(',', $listsite);

        $query1 = "SELECT tr.SiteID, tr.CreatedByAID,

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

                                -- RELOAD CASH --
                                SUM(CASE tr.TransactionType
                                   WHEN 'R' THEN
                                     CASE tr.PaymentType
                                       WHEN 2 THEN 0 -- Coupon
                                       ELSE -- Not Coupon
                                         CASE IFNULL(tr.StackerSummaryID, '')
                                           WHEN '' THEN tr.Amount -- Reload, Cash
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
                                
                                -- REDEMPTION CASHIER --
                                SUM(CASE tr.TransactionType
                                  WHEN 'W' THEN
                                        CASE a.AccountTypeID
                                          WHEN 4 THEN tr.Amount -- Cashier
                                          ELSE 0
                                        END -- Genesis
                                  ELSE 0 --  Not Redemption
                                END) As RedemptionCashier,

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
                                        
                                        -- Total e-SAFE Withdrawal
                                        SUM(CASE TransType
                                                WHEN 'W' THEN Amount -- if redemption
                                                ELSE 0 -- if not redemption
                                        END) AS EwalletRedemption

                                    FROM ewallettrans WHERE StartDate >= ? AND StartDate < ?
                                    AND SiteID IN (".$site.") AND Status IN (1,3) GROUP BY SiteID";
        
        $query3 = "SELECT SiteID, SUM(ActualAmount) AS ManualRedemption FROM manualredemptions
                            WHERE TransactionDate >= ? AND TransactionDate < ?
                            AND SiteID IN (".$site.") GROUP BY SiteID";   
        
        //Get total deposit cash and reload cash (with bancnet transaction included)
        $this->prepare($query1);
        $this->bindparameter(1, $datefrom);
        $this->bindparameter(2, $dateto);
        $this->execute();
        $rows1 = $this->fetchAllData();
        
        //Get the summation of total cash load and cash redemption
        foreach ($rows1 as $value) {
            $cohdata['TotalCashLoad'] += (float)$value['DepositCash'];
            $cohdata['TotalCashLoad'] += (float)$value['ReloadCash'];
            $cohdata['TotalCashRedemption'] += (float)$value['RedemptionCashier'];
        }
       
        //Get total e-SAFE loaded cash (with bancnet transaction included)
        $this->prepare($query2);
        $this->bindparameter(1, $datefrom);
        $this->bindparameter(2, $dateto);
        $this->execute();
        $rows2 = $this->fetchAllData();
        
        //Add the total e-SAFE cash load and e-SAFE cash redemption
        foreach ($rows2 as $value) {
            $cohdata['TotalCashLoad'] += (float)$value['EwalletCashDeposit'];
            $cohdata['TotalCashLoad'] += (float)$value['EwalletBancnetDeposit'];
            $cohdata['TotalCashRedemption'] += (float)$value['EwalletRedemption'];
        }

        //Get total manual redemption per site
        $this->prepare($query3);
        $this->bindparameter(1, $datefrom);
        $this->bindparameter(2, $dateto);
        $this->execute();
        $rows3 = $this->fetchAllData();
        
        //Add the total e-SAFE cash load and e-SAFE cash redemption
        foreach ($rows3 as $value) {
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
                  WHERE SiteID IN(".$site.") AND
                  StartDate >= ? AND StartDate < ? AND Status IN(1,3)";
        
        $this->prepare($stmt1);
        $this->bindparameter(1, $zdateFROM);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows1 = $this->fetchAllData();
       
        unset($listsite);
        return $rows1;
    }
    
    function getTotalTicketEncashment($zdateFROM, $zdateto, $zsiteID) {
        $listsite = array();
        $totalencashedtickets = 0;
        foreach ($zsiteID as $row)
        {
            array_push($listsite, "'".$row."'");
        }
        $site = implode(',', $listsite);
        $stmt = "SELECT SiteID, IFNULL(SUM(Amount), 0) AS EncashedTickets FROM vouchermanagement.tickets  -- Encashed Tickets
                                WHERE DateEncashed >= ? AND DateEncashed < ?
                                AND SiteID IN (".$site.") GROUP BY SiteID";
        $this->prepare($stmt);
        $this->bindparameter(1, $zdateFROM);
        $this->bindparameter(2, $zdateto);
        $this->execute();
        $rows = $this->fetchAllData();
        
        //combine total encashed tickets
        foreach ($rows as $value) {
            $totalencashedtickets += (float)$value['EncashedTickets'];
        }
        
        return $totalencashedtickets;
    }
    
}

?>