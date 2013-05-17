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

//require_once( ROOT_DIR . 'sys/class/nusoap/nusoap.php' );
require_once( ROOT_DIR . 'sys/class/nusoap/class.wsdlcache.php' );
require_once( ROOT_DIR . 'sys/class/MicrogamingCAPIWrapper.class.php' );
require_once( ROOT_DIR . 'sys/class/Array2XML.class.php');
require_once( ROOT_DIR . 'sys/class/RealtimeGamingAPIWrapper.class.php' );
require_once( ROOT_DIR . 'sys/class/helper/common.class.php' );
require_once( ROOT_DIR . 'sys/class/PlayTechAPIWrapper.class.php' );


class CasinoCAPIHandler
{
    /**
     * Casino Provider
     */
    const RTG = 1;
    const PT = 2;
    const MG = 3;    

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
    
    private $_userType;
	
    private $_secretKey;

    /**
     * Class constructor
     *
     * @param string $gamingProvider One of the casino provider contants
     * @param array $configuration
     * @return void
     */
    public function __construct( $gamingProvider, $configuration )
    {
        $this->_gamingProvider = $gamingProvider;

        $this->_URI = $configuration[ 'URI' ];
        $this->_isCaching = $configuration[ 'isCaching' ];
        $this->_isDebug = $configuration[ 'isDebug' ];
        
        if ( $this->_gamingProvider == self::MG )
        {
            if ( $configuration['authLogin'] &&
                 $configuration['authPassword'] && $configuration['playerName'] &&
                 $configuration['serverID'])
            {
                $this->_authLogin = $configuration['authLogin'];
                $this->_authPassword = $configuration['authPassword'];
                $this->_playerName = $configuration['playerName'];
                $this->_serverId = $configuration['serverID'];
            }
            
            $this->_API = new MicrogamingCAPIWrapper( $this->_URI, $this->_authLogin, 
                    $this->_authPassword, $this->_playerName,  $this->_serverId );
        }
        else if ( $this->_gamingProvider == self::PT )
        {
            $this->_URI = $configuration['URI'];
            $this->_authLogin = $configuration['authLogin'];
            $this->_secretKey = $configuration['secretKey'];
            $this->_API = new PlayTechAPIWrapper($this->_URI, $this->_authLogin, $this->_secretKey);
        }
        else if ( $this->_gamingProvider == self::RTG )
        {
            if(isset ($configuration[ 'depositMethodId' ]) && isset($configuration[ 'withdrawalMethodId' ])) 
            {
                $certFilePath = $configuration[ 'certFilePath' ];
                $keyFilePath = $configuration[ 'keyFilePath' ];

                $this->_API = new RealtimeGamingAPIWrapper( $this->_URI, RealtimeGamingAPIWrapper::CASHIER_API, $certFilePath, $keyFilePath, $this->_isCaching );

                $this->_API->SetDebug( $this->_isDebug );
                $this->_API->SetDepositMethodId( $configuration[ 'depositMethodId' ] );
                $this->_API->SetWithdrawalMethodId( $configuration[ 'withdrawalMethodId' ] );
            }
            else
            {
                $certFilePath = $configuration[ 'certFilePath' ];
                $keyFilePath = $configuration[ 'keyFilePath' ];

                $this->_API = new RealtimeGamingAPIWrapper( $this->_URI, RealtimeGamingAPIWrapper::PLAYER_API, $certFilePath, $keyFilePath, $this->_isCaching );

                $this->_API->SetDebug( $this->_isDebug );
            }
        }
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ( $this->_API )
        {
            $this->_API = NULL;
        }
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

        if (isset($urlInfo[ 'scheme' ])  && $urlInfo[ 'scheme' ] == 'https')
        {
            $port = 443;
        }

        return common::isHostReachable( $this->_URI, $port );
    }

    /**
     * Retrieve the current balance of the casino account
     *
     * @param string $login
     * @return array
     */
    public function GetBalance( $login )
    {
        if ( $this->_gamingProvider == self::MG )
        {
            return $this->_API->GetBalance( $login );
        }
        else if ( $this->_gamingProvider == self::RTG )
        {            
           return $this->_API->GetBalance( $login ); 
        }
        else if ( $this->_gamingProvider == self::PT )
        {            
           return $this->_API->GetBalance( $login ); 
        }
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
    public function Deposit( $login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '' )
    {
        if ( $this->_gamingProvider == self::MG )
        {
            return $this->_API->Deposit( $login, $tracking1, $amount, $tracking2, $tracking3, $tracking4 );
        }
        else if ( $this->_gamingProvider == self::RTG )
        {
            return $this->_API->Deposit( $login, $amount, $tracking1, $tracking2, $tracking3, $tracking4 );
        }
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
    public function Withdraw( $login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '', $methodname = '' )
    {
        if ( $this->_gamingProvider == self::MG )
        {
            return $this->_API->Withdraw( $login, $tracking1, $amount, $tracking2, $tracking3, $tracking4, $methodname );
        }
        else if ( $this->_gamingProvider == self::RTG )
        {
            return $this->_API->Withdraw( $login, $amount, $tracking1, $tracking2, $tracking3, $tracking4 );
        }
        else if ( $this->_gamingProvider == self::PT )
        {
            return $this->_API->Withdraw($login,$tracking1,$amount,$tracking2);
        }
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
    public function TransactionSearchInfo( $login, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '' )
    {
        if ( $this->_gamingProvider == self::RTG )
        {
            return $this->_API->TransactionSearchInfo( $login, $tracking1, $tracking2, $tracking3, $tracking4 );
        }
        else if ( $this->_gamingProvider == self::MG )
        {
            return $this->_API->GetMethodStatus( $tracking4 );
        }
    }
    
    
    public function GetMyBalance()
    {
        if ( $this->_gamingProvider == self::MG )
        {
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
    public function CreateTerminalAccount($login, $password, $aid, $currency, $email, $fname,
            $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $province,
            $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, 
            $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword,
            $agentID,$currentPosition, $thirdPartyPID,$isVIP='')
    {
        if ( $this->_gamingProvider == self::RTG)
        {
            return $this->_API->AddUser($login, $password, $aid, $country, $casinoID, 
                    $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, 
                    $province, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, 
                    $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, 
                    $thirdPartyPID);
        }
        else if ( $this->_gamingProvider == self::MG)
        {
            return $this->_API->AddUser($aid, $login, $password, $email, 
                    $fname, $lname, $dayphone, $evephone, $fax, $addr1, $addr2, $city, 
                    $country, $province, $zip, $userID, $currency, $occupation, $sex, $birthdate, $alias);
        }
        else if ( $this->_gamingProvider == self::PT)
        {
            return $this->_API->NewPlayer($login,$password,$email,$fname,$lname,$birthdate,
                    $addr1,$city,$country,$dayphone,$zip,$currency,$isVIP);
        }
    }
    
    /**
     * Change Terminal Password
     * @param int $casinoID always 1
     * @param type $loginName
     * @param type $oldPassword
     * @param type $newPassword
     * @return type 
     */
    public function ChangeTerminalPassword($casinoID, $loginName, $oldPassword, $newPassword)
    {
        if ( $this->_gamingProvider == self::RTG)
        {
            return $this->_API->ChangePassword($casinoID, $loginName, $oldPassword, $newPassword);
        }
        else if ($this->_gamingProvider == self::MG)
        {
            return $this->_API->ChangePassword($loginName, $oldPassword, $newPassword);
        }
        else if($this->_gamingProvider == self::PT)
        {
            return $this->_API->ChangePassword($loginName,$newPassword);
        }
    }
    
    /**
     * Get account info by login
     * @param type $login
     * @return object 
     */
    public function GetAccountInfo($login,$password){
        if ( $this->_gamingProvider == self::RTG)
        {
            return $this->_API->GetAccountInfoByLogin($login);
        }
        if( $this->_gamingProvider == self::MG)
        {
            return $this->_API->AccountExists($login);
        }
        if( $this->_gamingProvider == self::PT)
        {
            return $this->_API->GetPlayerInfo($login,$password);
        }
    }
    
    /**
     * Reset's password in MG, regardless of old password
     * @param type $login
     * @param type $password
     * @return type 
     */
    public function ResetPassword($login, $password){
        if( $this->_gamingProvider == self::MG)
        {
            return $this->_API->ResetPassword($login, $password);
        }
    }
    
    /**
     * Unfreeze's player in PT, regardless of old password
     * @param type $login
     * @return type 
     */
    public function unfreezePlayer($login, $frozen){
        if( $this->_gamingProvider == self::PT)
        {
            return $this->_API->FreezePlayer($login, $frozen);
        }
        
        if( $this->_gamingProvider == self::MG)
        {
            return $this->_API->UnlockUserAccount($login);
        }
    }
    
}

?>