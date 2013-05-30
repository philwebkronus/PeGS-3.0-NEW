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
foreach ($zsiteID as $row)
{
array_push($listsite, "'".$row."'");
}
$site = implode(',', $listsite);
$stmt = "select tr.TransactionSummaryID,ts.DateStarted,ts.DateEnded,tr.DateCreated, ts.LoyaltyCardNumber, tr.TerminalID,tr.SiteID,
t.TerminalCode as TerminalCode, tr.TransactionType, sum(tr.Amount) AS amount,a.UserName from transactiondetails tr
inner join transactionsummary ts on ts.TransactionsSummaryID = tr.TransactionSummaryID
inner join terminals t on t.TerminalID = tr.TerminalID
inner join accounts a on a.AID = tr.CreatedByAID
where tr.SiteID IN(".$site.") AND
tr.DateCreated >= ? and tr.DateCreated < ? and tr.Status IN(1,4)
group by tr.TransactionType,tr.TransactionSummaryID order by t.TerminalCode,tr.DateCreated Desc ";
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
    
    public final function getActiveSessionCount ($siteID) {
        
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
        
        $this->execute();
        
        $record = $this->fetchAllData();
        
        return $record[0]["ActiveSession"];
        
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
    public final function getActiveSessionPlayingBalance ($siteID, $_ServiceAPI,$_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,$_PlayerAPI,$_ptsecretkey) {
        
        include_once __DIR__.'/../../sys/class/CasinoCAPIHandler.class.php';
        
        $row = new stdClass();
        
        $row->page = 1;
        
        $query = "
                  SELECT  
                    t.TerminalID, 
                    t.TerminalCode, 
                    rs.ServiceID,
                    rs.ServiceName
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
                
                $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                
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
                
                $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                
                if(array_key_exists("BalanceInfo", $data))
                {
                    $r["PlayingBalance"] = $data["BalanceInfo"]["Balance"];
                }
                else{
                    $r["PlayingBalance"] = 0;
                }
            }
            
            $row->rows[$ctr]["id"] = $r["TerminalID"];
            
            $row->rows[$ctr]["cell"] = array(
                $r["TerminalID"],
                $r["TerminalCode"],
                number_format($r["PlayingBalance"], 2, '.',',')
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
    public final function getActiveSessionPlayingBalanceub ($cardnumber, $serviceusername, $_ServiceAPI,$_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,$_PlayerAPI,$_ptsecretkey) {
        
        include_once __DIR__.'/../../sys/class/CasinoCAPIHandler.class.php';
        
        $row = new stdClass();
        
        $row->page = 1;
        
        $query = "
                  SELECT  
                    t.TerminalID, 
                    t.TerminalCode, 
                    rs.ServiceID,
                    rs.ServiceName
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
                
                $data = $_CasinoAPIHandler->GetBalance($r["TerminalCode"]);
                
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
                $usermode = $this->checkUserMode($r["ServiceName"]);
                
                $url = $_PlayerAPI[(int)$r["ServiceID"]-1];
                $configuration = self::getConfigurationForPTCAPI( $url,$_ptcasinoname,$_ptsecretkey);
                
                $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::PT, $configuration );
                
                if($usermode == 0){
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
            
            $row->rows[$ctr]["id"] = $r["TerminalID"];
            
            $row->rows[$ctr]["cell"] = array(
                $r["TerminalID"],
                $r["TerminalCode"],
                number_format($r["PlayingBalance"], 2, '.',',')
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
    
}

?>