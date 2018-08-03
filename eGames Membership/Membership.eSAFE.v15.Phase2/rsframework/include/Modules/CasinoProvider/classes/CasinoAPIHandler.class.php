<?php

Class CasinoAPIHandler {

    const RTG = 1, PT = 2, MG = 3, RTG2 = 4, HAB = 5;

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

        if ($this->_casinoProvider == self::RTG) {
            if ($config['APIType'] == 0) {
                $this->_URIPID = $config['URI_PID'];

                $certFilePath = $config['certFilePath'];
                $keyFilePath = $config['keyFilePath'];

                $this->_API = new RealtimeGamingAPIWrapper($this->_URI, RealtimeGamingAPIWrapper::CASHIER_API, $certFilePath, $keyFilePath, $this->_isCaching);
                $this->_API->SetDebug($this->_isDebug);
            } else {
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

        if ($this->_casinoProvider == self::HAB) {
            $this->_URIPID = $config['URI'];

            $brandID = $config['brandID'];
            $APIkey = $config['APIkey'];
            $currencyCode = $config['currencyCode'];
            $serverID = $config['serverID'];

            if ($config['APIType'] == 0) {
                $this->_API = new HabaneroAPIWrapper($this->_URI, $APIkey, $brandID, $currencyCode, $serverID, HabaneroAPIWrapper::PLAYER_API);
            }
        }
    }

    public function __destruct() {
        if ($this->_API) {
            $this->_API = NULL;
        }
    }

    public function CreateNewAccount($login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, 
    	$addr2, $city, $country, $province, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, 
	$mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $viplevel) {

        if ($this->_casinoProvider == self::RTG) {
            $result = $this->_API->AddUser($login, $password, $aid, $country, $casinoID, 
	    $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, 
	    $province, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, 
	    $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $viplevel);
        }

        if ($this->_casinoProvider == self::RTG2) {
            $result = $this->_API->AddUser($login, $password, $aid, $country, $casinoID, 
	    $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, 
	    $province, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, 
	    $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $viplevel);
        }

        return $result;
    }

    public function HabaneroCreateNewAccount($Username, $Password, $PlayerRank) {

        if ($this->_casinoProvider == self::HAB) {
            $result = $this->_API->AddUser($Username, $Password, $PlayerRank);
        }

        return $result;
    }

    public function UnlockUserAccount($login) {
        if ($this->_casinoProvider == self::MG)
            return $this->_API->UnlockUserAccount($login);
    }

    public function FreezeAccount($login, $frozen) {
        if ($this->_casinoProvider == self::PT) {
            return $this->_API->FreezePlayer($login, $frozen);
        }
    }

    public function GetAccountInfo($login, $password) {
        if ($this->_casinoProvider == self::RTG) {
            return $this->_API->GetAccountInfoByLogin($login);
        }

        if ($this->_casinoProvider == self::HAB) {
            return $this->_API->GetBalance($login, $password);
        }
    }

    public function GetPendingGames($login) {
        return $this->_API->_GetPendingGamesByPID($login);
    }

    public function ChangePassword($casinoID, $login, $oldpassword, $newpassword) {

        if ($this->_casinoProvider == self::RTG) {
            return $this->_API->ChangePassword($casinoID, $login, $oldpassword, $newpassword);
        }

        if ($this->_casinoProvider == self::HAB) {
            return $this->_API->ChangePassword($login, $newpassword);
        }
    }

    public function ChangeplayerClassification($pid, $playerClassID, $userID) {
        return $this->_API->ChangeplayerClassification($pid, $playerClassID, $userID);
    }

    public function GetPlayerClassification($pid) {
        return $this->_API->GetPlayerClassification($pid);
    }

    public function RevertBrokenGamesAPI($username, $playerMode, $revertMode) {
        return $this->_API->RevertBrokenGames($username, $playerMode, $revertMode);
    }

    public function TransactionInfo($transID, $login = '') {
        if ($this->_casinoProvider == self::RTG) {
            return $this->_API->TransactionSearchInfo($login, $transID);
        }
    }

    /**
     * Get Pending Game Bet
     * @param type $login
     * @return object 
     */
    public function GetPIDLogin($login) {
        if ($this->_casinoProvider == self::RTG)
            $pidResults = $this->_API->GetPIDUsingLogin($login);
        return $pidResults["PID"];
    }

    /**
     * Search and retrieve transaction info
     *
     * @param string $login
     * @param float $amount
     * @param string $tracking1
     * @param string $tracking2
     * @param string $tracking3
     * @param string $tracking4
     * @return array
     */
    public function TransactionSearchInfo($login, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '', $ticket_id = '') {
        if ($this->_casinoProvider == self::RTG)
            return $this->_API->TransactionSearchInfo($login, $tracking1, $tracking2, $tracking3, $tracking4);
    }

    /**
     * Checks if API endpoint is reachable
     *
     * @param none
     * @return boolean
     */
    public function IsAPIServerOK() {
        $port = 80;

        $urlInfo = parse_url($this->_URI);

        if ($urlInfo['scheme'] == 'https') {
            $port = 443;
        }

        return common::isHostReachable($this->_URI, $port);
    }

}

?>
