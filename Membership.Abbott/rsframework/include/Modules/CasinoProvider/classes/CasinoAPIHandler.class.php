<?php

Class CasinoAPIHandler
{
    const RTG = 1, PT = 2, MG = 3, RTG2 = 4;
    
    private $_casinoProvider = 1;
    private $_casinoName;
    private $_currency = 9;
    private $_API;
    private $_URI;
    private $_URIPID;
    private $_isCaching = FALSE;
    private $_isDebug = FALSE;
    private $_authLogin;
    private $_authPassword;
    private $_playerName;
    private $_serverId;
    private $_secretKey;


    public function __construct($casinoProvider, $config) {
        $this->_casinoProvider = $casinoProvider;
        
        $this->_URI = $config['URI'];
        $this->_isCaching = $config['isCaching'];
        $this->_isDebug = $config["isDebug"];
        
        if($this->_casinoProvider == self::MG)
        {
            if($config['authLogin'] && $config['authPassword'] && $config['playerName'] && $config['serverID'])
            {
                $this->_authLogin = $config['authLogin'];
                $this->_authPassword = $config['authPassword'];
                $this->_playerName = $config['playerName'];
                $this->_serverId = $config['serverID'];
            }
            $this->_API = new MicrogamingCAPIWrapper($this->_URI, $this->_authLogin, $this->_authPassword, $this->_playerName, $this->_serverId);
        
        } elseif ($this->_casinoProvider == self::PT ) {
            if(isset($config['REVERT_BROKEN_GAME_MODE'])){
                    $certFilePath = $config['certFilePath'];
                    $keyFilePath = $config['keyFilePath'];
                    
                    $this->_API = new PlayTechAPIWrapper($this->_URI, $this->_casinoName, $this->_secretKey,$certFilePath, $keyFilePath,1);   
            } else {
                    $this->_casinoName = $config['authLogin'];
                    $this->_secretKey = $config['secretKey'];
                    $this->_API = new PlayTechAPIWrapper($this->_URI, $this->_casinoName, $this->_secretKey);   
            }
        } elseif ($this->_casinoProvider == self::RTG) {
            if($config['APIType'] == 0){
                $this->_URIPID = $config['URI_PID'];
                
                $certFilePath = $config['certFilePath'];
                $keyFilePath = $config['keyFilePath'];

                $this->_API = new RealtimeGamingAPIWrapper( $this->_URI, RealtimeGamingAPIWrapper::CASHIER_API, $certFilePath, $keyFilePath, $this->_isCaching );
                $this->_API->SetDebug($this->_isDebug);
            }
            else{
                $this->_URIPID = $config['URI_PID'];
                
                $certFilePath = $config['certFilePath'];
                $keyFilePath = $config['keyFilePath'];

                $this->_API = new RealtimeGamingUBAPIWrapper($this->_URI, RealtimeGamingUBAPIWrapper::PLAYER_API, $certFilePath, $this->_isCaching);
                $this->_API->SetDebug($this->_isDebug);
            }
            
                
            
        }
        
        if ($this->_casinoProvider == self::RTG2) {
                $this->_URIPID = $config['URI_PID'];
                
                $certFilePath = $config['certFilePath'];
                $keyFilePath = $config['keyFilePath'];

                $this->_API = new RealtimeGamingUBAPIWrapper($this->_URI, RealtimeGamingUBAPIWrapper::PLAYER_API, $certFilePath, $this->_isCaching);
                $this->_API->SetDebug($this->_isDebug);
            
            
        }
        
    }
    
    public function __destruct() {
        if($this->_API){
            $this->_API = NULL;
        }
    }
    
    public function GetMyBalance()
    {
        if($this->_casinoProvider == self::MG )
            return $this->_API->GetMyBalance();
    }
    
    public function CreateNewAccount($login, $password, $aid, $currency, $email, $fname,$lname, $dayphone, $evephone, $addr1, 
            $addr2, $city, $country, $province, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, 
            $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID,$currentPosition, $thirdPartyPID,$viplevel)
    {
        
        if ( $this->_casinoProvider == self::RTG) {
            $result = $this->_API->AddUser($login, $password, $aid, $country, $casinoID, 
                    $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, 
                    $province, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, 
                    $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, 
                    $thirdPartyPID,$viplevel);
        }
        
        if ( $this->_casinoProvider == self::RTG2) {
            $result = $this->_API->AddUser($login, $password, $aid, $country, $casinoID, 
                    $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, 
                    $province, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, 
                    $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, 
                    $thirdPartyPID,$viplevel);
        }
        
        if ( $this->_casinoProvider == self::MG) {
            $result = $this->_API->AddUser($aid, $login, $password, $email, 
                    $fname, $lname, $dayphone, $evephone, $fax, $addr1, $addr2, $city, 
                    $country, $province, $zip, $userID, $currency, $occupation, $sex, $birthdate, $alias);
        } 
        if ($this->_casinoProvider == self::PT ) {
            $result = $this->_API->NewPlayer($login, $password, $email, $fname, $lname, 
                    $birthdate, $addr1, $city, $country, $dayphone, $zip,$currency);
            
        }
        
        return $result;
    }
    
    public function UnlockUserAccount($login) {
        if($this->_casinoProvider == self::MG )
            return $this->_API->UnlockUserAccount($login);
    }
    
    public function FreezeAccount($login,$frozen)
    {
        if($this->_casinoProvider == self::PT){
            return $this->_API->FreezePlayer($login,$frozen);
        }
    }
    
    public function GetAccountInfo($login, $password) {
        if($this->_casinoProvider == self::RTG){
            return $this->_API->GetAccountInfoByLogin($login);
        } elseif ($this->_casinoProvider == self::MG) {
            return $this->_API->AccountExists($login);
        } elseif ($this->_casinoProvider == self::PT) {
            return $this->_API->GetPlayerInfo($login,$password);
        }
    }
    
    public function GetPendingGames($login) {  
        return $this->_API->_GetPendingGamesByPID($login);
    }
    
    public function ChangePassword($casinoID, $login, $oldpassword, $newpassword) {       
        return $this->_API->ChangePassword($casinoID, $login, $oldpassword, $newpassword);
    }
    
    public function ChangeplayerClassification($pid, $playerClassID, $userID) {       
        return $this->_API->ChangeplayerClassification($pid, $playerClassID, $userID);
    }
    
    public function RevertBrokenGamesAPI( $username, $playerMode, $revertMode ){
        return $this->_API->RevertBrokenGames( $username, $playerMode, $revertMode );
    }
    
    public function TransactionInfo($transID, $login = ''){
        if($this->_casinoProvider == self::RTG){
            return $this->_API->TransactionSearchInfo($login, $transID);
        } elseif ($this->_casinoProvider == self::MG) {
            return $this->_API->GetMethodStatus($transID);
        } elseif ($this->_casinoProvider == self::PT) {
            return $this->_API->CheckTransaction($transID);
        }
    }
    
    
    /**
     * Get Pending Game Bet
     * @param type $login
     * @return object 
     */
    public function GetPIDLogin($login)
    {
        if ( $this->_casinoProvider == self::RTG )
            $pidResults =  $this->_API->GetPIDUsingLogin($login);
            return $pidResults["PID"];

    }
    
    /**
     * Checks if API endpoint is reachable
     *
     * @param none
     * @return boolean
     */
    public function IsAPIServerOK()
    {
        $port = 80;
        
        $urlInfo = parse_url( $this->_URI );        

        if ( $urlInfo[ 'scheme' ] == 'https' )
        {
            $port = 443;
        }

        return common::isHostReachable( $this->_URI, $port );
    }
}

?>
