<?php

/**
 * Casino API Handler
 * Copyright (c) 2011. PhilWeb Corporation. WEBiTS
 *
 * @author  FTG
 * @version 0.1
 * @modified elperez
 * with MG CAPI Integration
 */
class CasinoCAPIHandler {

    /**
     * Casino Provider
     */
    const RTG = 1;
    const PT = 2;
    const MG = 3;
    const RTG2 = 4;
    /*
     * John Aaron Vida
     * 12/14/2017
     * Added ::Habanero
     */
    const Habanero = 5;

    /**
     * Default casino provider
     * @var integer
     */
    private $_gamingProvider = 1;

    /**
     * Default currency
     * @var integer
     */
    private $_currency = 9;

    /**
     * Holds API wrapper class
     * @var object
     */
    private $_API;

    /**
     * API Endpoint
     * @var string
     */
    private $_URI;

    /**
     * API Endpoint
     * @var string
     */
    private $_URIPID;

    /**
     * API Endpoint
     * @var string
     */
    private $_URIPID2;

    /**
     * Set API caching
     * @var boolean
     */
    private $_isCaching = FALSE;

    /**
     * Set debug mode
     * @var boolean
     */
    private $_isDebug = FALSE;
    private $_authLogin;
    private $_authPassword;
    private $_playerName;
    private $_serverId;

    /**
     * Class constructor
     *
     * @param string $gamingProvider One of the casino provider contants
     * @param array $configuration
     * @return void
     */
    public function __construct($gamingProvider, $configuration) {
        $this->_gamingProvider = $gamingProvider;
        $this->_URI = $configuration['URI'];
        $this->_isCaching = $configuration['isCaching'];
        $this->_isDebug = $configuration['isDebug'];

        if ($this->_gamingProvider == self::MG) {
            if ($configuration['authLogin'] &&
                    $configuration['authPassword'] && $configuration['playerName'] &&
                    $configuration['serverID']) {
                $this->_authLogin = $configuration['authLogin'];
                $this->_authPassword = $configuration['authPassword'];
                $this->_playerName = $configuration['playerName'];
                $this->_serverId = $configuration['serverID'];
            }

            $this->_API = new MicrogamingCAPIWrapper($this->_URI, $this->_authLogin, $this->_authPassword, $this->_playerName, $this->_serverId);
        } else if ($this->_gamingProvider == self::PT) {
            if (!isset($configuration['REVERT_BROKEN_GAME_MODE'])) {
                $casinoName = $configuration['pt_casino_name'];
                $secretKey = $configuration['pt_secret_key'];
                $this->_API = new PlayTechAPIWrapper($this->_URI, $casinoName, $secretKey);
            } else {
                $this->_URIPID = $configuration['URI_RBAPI'];
                $certFilePath = $configuration['certFilePath'];
                $keyFilePath = $configuration['keyFilePath'];
                $this->_API = new PlayTechAPIWrapper($this->_URIPID, '', '', $certFilePath, $keyFilePath, 1);
            }
        } else if ($this->_gamingProvider == self::RTG) {
            $this->_URIPID = $configuration['URI_PID'];
            $this->_URIPID2 = $configuration['URI_PID2'];
            $certFilePath = $configuration['certFilePath'];
            $keyFilePath = $configuration['keyFilePath'];

            if ($configuration['APIType'] == 2) {
                $this->_API = new RealtimeGamingUBAPIWrapper($this->_URIPID, RealtimeGamingUBAPIWrapper::GAME_API, $certFilePath, $keyFilePath, $this->_isCaching);
            } else if ($configuration['APIType'] == 0) {
                if ($configuration['CasinoService'] == 'v12') {
                    $this->_API = new RealtimeGamingAPIWrapper($this->_URI, RealtimeGamingAPIWrapper::CASHIER_API, $certFilePath, $keyFilePath, $this->_isCaching);
                } else {
                    $this->_API = new RealtimeGamingUBAPIWrapper($this->_URI, RealtimeGamingUBAPIWrapper::CASHIER_API, $certFilePath, $keyFilePath, $this->_isCaching);
                }
            } else {
                if ($configuration['CasinoService'] == 'v12') {
                    $this->_API = new RealtimeGamingAPIWrapper($this->_URIPID2, RealtimeGamingAPIWrapper::PLAYER_API, $certFilePath, $keyFilePath, $this->_isCaching);
                } else {
                    $this->_API = new RealtimeGamingUBAPIWrapper($this->_URIPID2, RealtimeGamingAPIWrapper::PLAYER_API, $certFilePath, $keyFilePath, $this->_isCaching);
                }
            }

            $this->_API->SetDebug($this->_isDebug);
            $this->_API->SetDepositMethodId($configuration['depositMethodId']);
            $this->_API->SetWithdrawalMethodId($configuration['withdrawalMethodId']);
        } else if ($this->_gamingProvider == self::RTG2) {
            $this->_URIPID = $configuration['URI_PID'];
            $this->_URIPID2 = $configuration['URI_PID2'];
            $certFilePath = $configuration['certFilePath'];
            $keyFilePath = $configuration['keyFilePath'];

            if ($configuration['APIType'] == 2) {
                $this->_API = new RealtimeGamingUBAPIWrapper($this->_URIPID, RealtimeGamingUBAPIWrapper::GAME_API, $certFilePath, $keyFilePath, $this->_isCaching);
            } else if ($configuration['APIType'] == 0) {
                $this->_API = new RealtimeGamingUBAPIWrapper($this->_URI, RealtimeGamingUBAPIWrapper::CASHIER_API, $certFilePath, $keyFilePath, $this->_isCaching);
            } else {
                $this->_API = new RealtimeGamingUBAPIWrapper($this->_URIPID2, RealtimeGamingAPIWrapper::PLAYER_API, $certFilePath, $keyFilePath, $this->_isCaching);
            }

            $this->_API->SetDebug($this->_isDebug);
            $this->_API->SetDepositMethodId($configuration['depositMethodId']);
            $this->_API->SetWithdrawalMethodId($configuration['withdrawalMethodId']);
        }
        /*
         * John Aaron Vida
         * 12/14/2017
         * Added ::Habanero
         */ else if ($this->_gamingProvider == self::Habanero) {
            if ($configuration['brandID'] && $configuration['APIkey'] && $configuration['CasinoService']) {
                $this->_URI = $configuration['URI'];
                $this->_authLogin = $configuration['brandID'];
                $this->_authPassword = $configuration['APIkey'];
                $this->_playerName = $configuration['currencyCode'];
                $this->_serverId = $configuration['CasinoService'];
            }
            if ($configuration['APIType'] == 0) {
                $this->_API = new HabaneroAPIWrapper($this->_URI, $this->_authPassword, $this->_authLogin, $this->_playerName, $this->_serverId, HabaneroAPIWrapper::PLAYER_API);
            }
        }
    }

    /**
     * Class destructor
     */
    public function __destruct() {
        if ($this->_API) {
            $this->_API = NULL;
        }
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

    /**
     * Checks if API endpoint is reachable
     *
     * @param none
     * @return boolean
     */
    public function IsAPIServerOK2() {
        $port = 80;

        $urlInfo = parse_url($this->_URIPID);

        if ($urlInfo['scheme'] == 'https') {
            $port = 443;
        }

        return common::isHostReachable($this->_URIPID, $port);
    }

    /**
     * Retrieve the current balance of the casino account
     *
     * @param string $login
     * @return array
     */
    public function GetBalance($login) {
        if ($this->_gamingProvider == self::MG)
            return $this->_API->GetBalance($login);

        if ($this->_gamingProvider == self::RTG)
            return $this->_API->GetBalance($login);

        if ($this->_gamingProvider == self::RTG2)
            return $this->_API->GetBalance($login);

        if ($this->_gamingProvider == self::PT)
            return $this->_API->GetBalance($login);
    }

    public function GetBalanceHabanero($Username, $Password) {
        /*
         * John Aaron Vida
         * 12/14/2017
         * Added ::Habanero
         */
        if ($this->_gamingProvider == self::Habanero)
            return $this->_API->GetBalance($Username, $Password);
    }

    /**
     * Perform deposit to casino account
     *
     * @param string $login
     * @param float $amount
     * @param string $tracking1
     * @param string $tracking2
     * @param string $tracking3
     * @param string $tracking4
     * @return array
     */
    public function Deposit($login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '', $terminalPassword = '', $event_id = '', $transaction_id = '', $locatorname = '') {
        if ($this->_gamingProvider == self::MG)
            return $this->_API->Deposit($login, $terminalPassword, $amount, $transaction_id, $event_id, $transaction_id);

        if ($this->_gamingProvider == self::RTG)
            if (!empty($locatorname)) {

                return $this->_API->Deposit($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $terminalPassword, $locatorname);
            } else {
                return $this->_API->Deposit($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $terminalPassword);
            }

        if ($this->_gamingProvider == self::RTG2)
            return $this->_API->Deposit($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $terminalPassword);

        if ($this->_gamingProvider == self::PT)
            return $this->_API->Deposit($login, $terminalPassword, $amount, $tracking1);

        /*
         * John Aaron Vida
         * 12/14/2017
         * Added ::Habanero
         */
        if ($this->_gamingProvider == self::Habanero)
            $RequestID = $tracking1 . '' . $tracking2;
        return $this->_API->Deposit($login, $terminalPassword, $amount, $RequestID);
    }

    /**
     * Perform withdraw from casino account
     *
     * @param string $login
     * @param float $amount
     * @param string $tracking1
     * @param string $tracking2
     * @param string $tracking3
     * @param string $tracking4
     * @return array
     */
    public function Withdraw($login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '', $terminalPassword = '', $event_id = '', $transaction_id = '') {
        if ($this->_gamingProvider == self::MG)
            return $this->_API->Withdraw($login, $terminalPassword, $amount, $transaction_id, $event_id, $transaction_id);

        if ($this->_gamingProvider == self::RTG)
            return $this->_API->Withdraw($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $terminalPassword);

        if ($this->_gamingProvider == self::RTG2)
            return $this->_API->Withdraw($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $terminalPassword);

        if ($this->_gamingProvider == self::PT)
            return $this->_API->Withdraw($login, $terminalPassword, $amount, $tracking1);

        /*
         * John Aaron Vida
         * 12/14/2017
         * Added ::Habanero
         */
        if ($this->_gamingProvider == self::Habanero)
            $RequestID = $tracking1 . '' . $tracking2;
        return $this->_API->Withdraw($login, $terminalPassword, $amount, $RequestID);
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
        if ($this->_gamingProvider == self::RTG)
            return $this->_API->TransactionSearchInfo($login, $tracking1, $tracking2, $tracking3, $tracking4);

        if ($this->_gamingProvider == self::RTG2)
            return $this->_API->TransactionSearchInfo($login, $tracking1, $tracking2, $tracking3, $tracking4);

        if ($this->_gamingProvider == self::MG)
            return $this->_API->GetMethodStatus($ticket_id);

        if ($this->_gamingProvider == self::PT)
            return $this->_API->CheckTransaction($tracking1);

        /*
         * John Aaron Vida
         * 12/14/2017
         * Added ::Habanero
         */
        if ($this->_gamingProvider == self::Habanero)
            $RequestID = $tracking1 . '' . $tracking2;
        return $this->_API->TransactionSearchInfo($RequestID);
    }

    public function GetMyBalance() {
        if ($this->_gamingProvider == self::MG) {
            return $this->_API->GetMyBalance();
        }
    }

    /**
     * create terminal account whether RTG / MG
     * @param type $login
     * @param type $password
     * @param type $aid
     * @param type $currency
     * @param type $email
     * @param type $fname
     * @param type $lname
     * @param type $dayphone
     * @param type $evephone
     * @param type $addr1
     * @param type $addr2
     * @param type $city
     * @param type $country
     * @param type $province
     * @param type $zip
     * @param type $userID
     * @param type $birthdate
     * @param type $fax
     * @param type $occupation
     * @param type $sex
     * @param type $alias
     * @param type $casinoID
     * @param type $ip
     * @param type $mac
     * @param type $downloadID
     * @param type $clientID
     * @param type $putInAffPID
     * @param type $calledFromCasino
     * @param type $hashedPassword
     * @param type $agentID
     * @param type $currentPosition
     * @param type $thirdPartyPID
     * @return type 
     */
    public function CreateTerminalAccount($login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $province, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID) {
        if ($this->_gamingProvider == self::RTG) {
            return $this->_API->AddUser($login, $password, $aid, $country, $casinoID, $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, $province, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID);
        } else if ($this->_gamingProvider == self::MG) {
            return $this->_API->AddUser($aid, $login, $password, $email, $fname, $lname, $dayphone, $evephone, $fax, $addr1, $addr2, $city, $country, $province, $zip, $userID, $currency, $occupation, $sex, $birthdate, $alias);
        }
    }

    public function UnlockUserAccount($login) {
        if ($this->_gamingProvider == self::MG) {
            return $this->_API->UnlockUserAccount($login);
        }
    }

    /**
     * Change Account Status
     * @param str $login
     * @param int $status 
     * @return object
     */
    public function ChangeAccountStatus($login, $status) {
        if ($this->_gamingProvider == self::PT)
            return $this->_API->FreezePlayer($login, $status);
    }

    /**
     * Kick Player 
     * @param type $login
     * @return object 
     */
    public function KickPlayer($login) {
        if ($this->_gamingProvider == self::PT)
            return $this->_API->KickPlayer($login);
    }

    /**
     * Logout Player
     * @param type $pid
     * @return object 
     */
    public function LogoutPlayer($pid) {
        if ($this->_gamingProvider == self::RTG)
            return $this->_API->LogoutPlayer($pid);
    }

    /*
     * John Aaron Vida
     * 12/14/2017
     * Added ::Habanero
     * 
     * Logout Player Habanero
     * @param type Username
     * @param type Password
     * @return object 
     */

    public function LogoutPlayerHabanero($Username, $Password) {
        if ($this->_gamingProvider == self::Habanero)
            return $this->_API->LogoutPlayer($Username, $Password);
    }

    /**
     * Get Pending Game Bet
     * @param type $PID
     * @return object 
     */
    public function GetPendingGames($PID) {
        if ($this->_gamingProvider == self::RTG)
            return $this->_API->GetPendingGamesByPID($PID);

        if ($this->_gamingProvider == self::RTG2)
            return $this->_API->GetPendingGamesByPID($PID);
    }

    /**
     * Get Pending Game Bet
     * @param type $login
     * @return object 
     */
    public function GetPIDLogin($login) {
        if ($this->_gamingProvider == self::RTG)
            $pidResults = $this->_API->GetPIDUsingLogin($login);

        if ($this->_gamingProvider == self::RTG2)
            $pidResults = $this->_API->GetPIDUsingLogin($login);

        return $pidResults["PID"];
    }

    public function RevertBrokenGamesAPI($username, $playerMode, $revertMode) {
        return $this->_API->RevertBrokenGames($username, $playerMode, $revertMode);
    }

    // CCT BEGIN added VIP
    //public function ChangePlayerClassification($pid, $playerClassID) 
    //{       
    //    if($this->_gamingProvider == self::RTG)
    //        return $this->_API->ChangePlayerClassification($pid, $playerClassID);
    //}
    //public function GetPlayerClassification($pid) 
    //{       
    //    if($this->_gamingProvider == self::RTG)
    //        return $this->_API->GetPlayerClassification($pid);
    //}
    // CCT END added VIP
}

?>