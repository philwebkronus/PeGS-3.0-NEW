<?php

/**
 * Casino API Handler
 * Copyright (c) 2011. PhilWeb Corporation. WEBiTS
 *
 * @author  FTG
 * @version 0.1
 */

require_once( ROOT_DIR . 'nusoap/nusoap.php' );
require_once( ROOT_DIR . 'nusoap/class.wsdlcache.php' );
require_once( ROOT_DIR . 'MicrogamingAPIWrapper.class.php' );
require_once( ROOT_DIR . 'RealtimeGamingAPIWrapper.class.php' );
require_once( ROOT_DIR . 'helper/common.class.php' );

class CasinoAPIHandler
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
            $sessionGUID = $configuration[ 'sessionGUID' ];
            
            if ( $configuration[ 'currency' ] )
            {
                $this->_currency = $configuration[ 'currency' ];
            }

            $this->_API = new MicrogamingAPIWrapper( $this->_URI, '' , '', $sessionGUID, $this->_currency );
        }
        else if ( $this->_gamingProvider == self::PT )
        {
            // TODO
        }
        else if ( $this->_gamingProvider == self::RTG )
        {
            $certFilePath = $configuration[ 'certFilePath' ];
            $keyFilePath = $configuration[ 'keyFilePath' ];
            
            $this->_API = new RealtimeGamingAPIWrapper( $this->_URI, RealtimeGamingAPIWrapper::CASHIER_API, $certFilePath, $keyFilePath, $this->_isCaching );

            $this->_API->SetDebug( $this->_isDebug );
            $this->_API->SetDepositMethodId( $configuration[ 'depositMethodId' ] );
            $this->_API->SetWithdrawalMethodId( $configuration[ 'withdrawalMethodId' ] );
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

        if ( $urlInfo[ 'scheme' ] == 'https' )
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
            return $this->_API->Deposit( $login, $amount, $this->_currency );
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
    public function Withdraw( $login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '' )
    {
        if ( $this->_gamingProvider == self::MG )
        {
            return $this->_API->Withdraw( $login, $amount );
        }
        else if ( $this->_gamingProvider == self::RTG )
        {
            return $this->_API->Withdraw( $login, $amount, $tracking1, $tracking2, $tracking3, $tracking4 );
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
    }
    
    
    public function GetMyBalance()
    {
        if ( $this->_gamingProvider == self::MG )
        {
            return $this->_API->GetMyBalance();
        }
        
    }
    
}

?>
