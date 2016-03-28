<?php
#Name: RealtimeGamingCashierAPI.class.php
#Author: FTG
#Version: 1.0.0
#Copyright 2010 PhilWeb Corporation

//require_once( 'libs/nusoap/nusoap.php' );
//$_RealtimeGamingCashierAPI = new RealtimeGamingCashierAPI( $wsdlUrl, $certFilePath, $keyFilePath, $passPhrase );

//class RealtimeGamingCashierAPI
//{
//    private $_soapClient;
//    
//    public function __construct()
//    {
//        $argv = func_get_args();
//
//        switch ( func_num_args() )
//        {
//            default:
//            case 4: self::__construct1( $argv[0], $argv[1], $argv[2], $argv[3] ); break;
//            case 5: self::__construct2( $argv[0], $argv[1], $argv[2], $argv[3], $argv[4] ); break;
//        }
//    }
//	
//    public function __construct1( $wsdlUrl, $certFilePath = '', $keyFilePath = '', $passPhrase = '' )
//    {
//        $this->_soapClient = new nusoap_client( $wsdlUrl, 'wsdl' );
//        $this->_soapClient->authtype = 'certificate';
//        $this->_soapClient->certRequest[ 'sslcertfile' ] = $certFilePath;
//        $this->_soapClient->certRequest[ 'sslkeyfile' ] = $keyFilePath;
//        $this->_soapClient->certRequest[ 'passphrase' ] = $passPhrase;
//        $this->_soapClient->certRequest[ 'verifypeer' ] = 0;
//        $this->_soapClient->certRequest[ 'verifyhost' ] = 0;
//	$this->_soapClient->response_timeout = 500;
//	$this->_soapClient->timeout = 500;
//    }
//	
//    public function __construct2( $wsdlUrl, $certFilePath = '', $keyFilePath = '', $passPhrase = '', $caching = FALSE )
//    {
//	if ($caching == TRUE)
//        {
//            $cache = new nusoap_wsdlcache( ROOT_DIR . 'sys/tmp/cache', 86400 );
//
//            $wsdl = $cache->get( $wsdlUrl );
//
//            if ( is_null( $wsdl ) )
//            {
//                $wsdl = new wsdl( $wsdlUrl, '', '', '', '', 5 );
//                $cache->put( $wsdl );
//            }
//        }
//        else
//        {
//            $wsdl = new wsdl( $wsdlUrl, '', '', '', '', 5 );
//        }
//		
//	$this->_soapClient = new nusoap_client( $wsdl, 'wsdl' );
//        $this->_soapClient->authtype = 'certificate';
//        $this->_soapClient->certRequest[ 'sslcertfile' ] = $certFilePath;
//        $this->_soapClient->certRequest[ 'sslkeyfile' ] = $keyFilePath;
//        $this->_soapClient->certRequest[ 'passphrase' ] = $passPhrase;
//        $this->_soapClient->certRequest[ 'verifypeer' ] = 0;
//        $this->_soapClient->certRequest[ 'verifyhost' ] = 0;
//	$this->_soapClient->response_timeout = 500;
//	$this->_soapClient->timeout = 500;
//    }
//
//    public function GetError()
//    {
//    	return $this->_soapClient->getError();
//    }
//
//    public function GetSoapClient()
//    {
//	return $this->_soapClient;
//    }
//    
//    public function DepositGeneric($casinoID,
//				   $PID,
//				   $methodID,
//				   $amount,
//				   $tracking1,
//				   $tracking2,
//				   $tracking3,
//				   $tracking4,
//				   $sessionID,
//				   $userID = 0,
//				   $skinID = 1)
//    {
//        $result = $this->_soapClient->call('DepositGeneric', array('casinoID' => $casinoID,
//                                                                   'PID' => $PID,
//                                                                   'methodID' => $methodID,
//                                                                   'amount' => $amount,
//                                                                   'tracking1' => $tracking1,
//                                                                   'tracking2' => $tracking2,
//                                                                   'tracking3' => $tracking3,
//                                                                   'tracking4' => $tracking4,
//                                                                   'sessionID' => $sessionID,
//                                                                   'userID' => $userID,
//                                                                   'SkinID' => $skinID));
//        
//        return $result;
//    }
//    
//    public function GetAccountBalance($casinoID, $PID, $forMoney = 1)
//    {
//        $result = $this->_soapClient->call('GetAccountBalance', array('casinoID' => $casinoID,
//                                                                      'PID' => $PID,
//                                                                      'forMoney' => $forMoney));
//        
//        return $result;
//    }
//    
//    public function GetAccountInfoByPID($casinoID, $PID)
//    {
//        $result = $this->_soapClient->call('GetAccountInfoByPID', array('casinoID' => $casinoID,
//                                                                        'PID' => $PID));
//
//        return $result;
//    }
//    
//    public function GetPIDFromLogin($login)
//    {	
//        $result = $this->_soapClient->call('GetPIDFromLogin', array('login' => $login));
//        
//        return $result;
//    }
//    
//    public function Login($casinoID, $PID, $hashedPassword, $forMoney, $IP, $skinID = 1)
//    {
//        $result = $this->_soapClient->call('Login', array('casinoID' => $casinoID,
//                                                          'PID' => $PID,
//                                                          'hashedPassword' => $hashedPassword,
//                                                          'forMoney' => $forMoney,
//                                                          'IP' => $IP,
//                                                          'skinID' => $skinID));
//        
//        return $result;
//    }
//    
//    public function WithdrawGeneric($casinoID,
//									$PID,
//									$methodID,
//									$amount,
//									$tracking1,
//									$tracking2,
//									$tracking3,
//									$tracking4,
//									$sessionID,
//									$userID = 0,
//									$skinID = 1)
//    {
//        $result = $this->_soapClient->call('WithdrawGeneric', array('casinoID' => $casinoID,
//                                                                    'PID' => $PID,
//                                                                    'methodID' => $methodID,
//                                                                    'amount' => $amount,
//                                                                    'tracking1' => $tracking1,
//                                                                    'tracking2' => $tracking2,
//                                                                    'tracking3' => $tracking3,
//                                                                    'tracking4' => $tracking4,
//                                                                    'sessionID' => $sessionID,
//                                                                    'userID' => $userID,
//                                                                    'skinID' => $skinID));
//        
//        return $result;
//    }    
//    
//    public function TrackingInfoTransactionSearch($PID,
//												  $tracking1,
//												  $tracking2 = '',
//												  $tracking3 = '',
//												  $tracking4 = '')
//    {
//        $result = $this->_soapClient->call('TrackingInfoTransactionSearch', array('pid' => $PID,
//                                                                                  'tracking1' => $tracking1,
//                                                                                  'tracking2' => $tracking2,
//                                                                                  'tracking3' => $tracking3,
//                                                                                  'tracking4' => $tracking4));
//        
//        return $result;
//    }     
//}

?>