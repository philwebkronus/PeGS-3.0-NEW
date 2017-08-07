<?php
/*
Change Log History
1.0 2011-11-09 - Login method, get password plain text and convert to SHA1 instead of hashedPassword
*/
//require_once( ROOT_DIR . 'sys/class/CasinoAPI/RealtimeGamingCashierAPI2.class.php' );
//Mirage::loadComponents('CasinoAPI/RealtimeGamingCashierAPI2.class');

Mirage::loadComponents(array('CasinoAPI/RealtimeGamingCashierAPI2.class',
                             'CasinoAPI/RealtimeGamingCasinoGamesAPI.class',
                             'CasinoAPI/RealtimeGamingPlayerAPI.class')); 
class RealtimeGamingUBAPIWrapper
{
    const CASHIER_API = 0;
    const PLAYER_API = 1;
    const GAME_API = 2;
    
    private $_API;
    private $_debug = FALSE;
    private $_depositMethodId = 503; // 502
    private $_withdrawMethodId = 502; // 503
    
    public function __construct( $URI, $API, $certFilePath, $keyFilePath,$caching = '' )
    {
        switch ($API)
        {
            case self::CASHIER_API:
            {
                $this->_API = new RealtimeGamingCashierAPI( $URI, $certFilePath, $keyFilePath, $caching);       
                break;
            }
            case self::GAME_API:
            {
                $this->_API = new RealtimeGamingCasinoGamesAPI($URI, $certFilePath, $keyFilePath, $caching); 
                break;
            }
            case self::PLAYER_API:
            {
                $this->_API = new RealtimeGamingPlayerAPI($URI, $certFilePath, $keyFilePath, ''); 
                break;
            }
        }        
    }

    public function SetDepositMethodId( $id )
    {
        $this->_depositMethodId = $id;
    }

    public function SetWithdrawalMethodId( $id )
    {
        $this->_withdrawMethodId = $id;
    }
    
    public function Deposit( $login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '' , $password = '', $locatorName = '')
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin( $login );

        if ( !is_null( $GetPIDFromLoginResult ) )
        {
            if ( $GetPIDFromLoginResult[ 'IsSucceed'] == true )
            {
                $PID = $GetPIDFromLoginResult[ 'PID' ];
                
                if(!empty($locatorName))
                {
                    $skinres = $this->_GetSkinID($locatorName);
                    $skinID = $skinres['SkinID'];
                } 
                else 
                { 
                    $skinID = 1; 
                }

                $sessionId = $this->Login( $login, $password, $skinID );

                if ( !is_null( $sessionId ) )
                {
                    $response = $this->_API->DepositGeneric( 1, $PID, $this->_depositMethodId, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionId, $skinID );
                    
                    if ( !$this->_API->GetError() )
                    {
                        if ( is_array( $response ) )
                        {
                            return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response );
                        }
                        else
                        {
                            return array( 'IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Response malformed' );
                        }
                    }
                    else
                    {
                        return array( 'IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
                    }
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 52, 'ErrorMessage' => 'Session ID error' );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 53, 'ErrorMessage' => 'PID error' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 54, 'ErrorMessage' => 'GetPIDFromLogin error' );
        }
    }
    
    public function GetBalance( $login )
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin( $login );
        
        if ( !is_null( $GetPIDFromLoginResult ) )
        {
            if ( $GetPIDFromLoginResult[ 'IsSucceed' ] == true )
            {
                $GetAccountBalanceResult = $this->_API->GetAccountBalance( 1, $GetPIDFromLoginResult[ 'PID' ], 1 );

                if ( !$this->_API->GetError() )
                {
                    if ( $GetAccountBalanceResult[ 'GetAccountBalanceResult' ][ 'balance' ] )
                    {
                        $balance = (float)$GetAccountBalanceResult[ 'GetAccountBalanceResult' ][ 'balance' ];
                        $bonusBalance = (float)$GetAccountBalanceResult[ 'GetAccountBalanceResult' ][ 'bonusBalance' ];

                        // get redeamable balance
                        $redeemable = $balance - $bonusBalance;

                        return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'BalanceInfo' => array( 'Balance' => $balance, 'BonusBalance' => $bonusBalance, 'Redeemable' => $redeemable ) );
                    }
                    else
                    {
                        return array( 'IsSucceed' => false, 'ErrorCode' => 10, 'ErrorMessage' => 'Response malformed' );
                    }
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 11, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 12, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 13, 'ErrorMessage' => 'Response malformed' );
        }
    }
    
    public function TransactionSearchInfo( $login, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '' )
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin( $login );
        
        if ( !is_null( $GetPIDFromLoginResult ) )
        {
            if ( $GetPIDFromLoginResult[ 'IsSucceed'] == true )
            {
                $PID = $GetPIDFromLoginResult[ 'PID' ];

                $TrackingInfoTransactionSearchResult = $this->_API->TrackingInfoTransactionSearch( $PID, $tracking1, $tracking2, $tracking3, $tracking4 );

                if ( !$this->_API->GetError() )
                {
                    if ( is_array( $TrackingInfoTransactionSearchResult ) )
                    {
                        return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $TrackingInfoTransactionSearchResult );
                    }
                    else
                    {
                        return array( 'IsSucceed' => false, 'ErrorCode' => 40, 'ErrorMessage' => 'Response malformed' );
                    }
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 41, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 42, 'ErrorMessage' => 'Error retrieving PID' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 42, 'ErrorMessage' => 'Error retrieving PID' );
        }
    }
    
    public function Withdraw( $login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '', $password = '', $locatorName = '' )
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin( $login );

        if ( !is_null( $GetPIDFromLoginResult ) )
        {
            if ( $GetPIDFromLoginResult[ 'IsSucceed'] == true )
            {

                $PID = $GetPIDFromLoginResult[ 'PID' ];
                
                if(!empty($locatorName))
                {
                    $skinres = $this->_GetSkinID($locatorName);
                    $skinID = $skinres['SkinID'];
                } 
                else 
                { 
                    $skinID = 1; 
                }

                $sessionId = $this->Login( $login, $password, $skinID );

                if ( !is_null( $sessionId ) )
                {
                    $response = $this->_API->WithdrawGeneric( 1, $PID, $this->_withdrawMethodId, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionId, $skinID );

                    if ( !$this->_API->GetError() )
                    {
                        if ( is_array( $response ) )
                        {
                            return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response );
                        }
                        else
                        {
                            return array( 'IsSucceed' => false, 'ErrorCode' => 60, 'ErrorMessage' => 'Response malformed' );
                        }
                    }
                    else
                    {
                        return array( 'IsSucceed' => false, 'ErrorCode' => 61, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
                    }
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 62, 'ErrorMessage' => 'Session ID error' );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 63, 'ErrorMessage' => 'PID error' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 64, 'ErrorMessage' => 'GetPIDFromLogin error' );
        }
    }

    private function _GetAccountInfoByPID( $PID )
    {
        $response = $this->_API->GetAccountInfoByPID( 1, $PID );

        if ( !$this->_API->GetError() )
        {
            if ( $response[ 'GetAccountInfoByPIDResult' ] )
            {
                return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'AccountInfo' => $response );
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 20, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 21, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }

    private function _GetPIDFromLogin( $login )
    {        
        $response = $this->_API->GetPIDFromLogin( $login );
        
        if ( !$this->_API->GetError() )
        {           
            if ( $response[ 'GetPIDFromLoginResult' ] != null )
            {
                return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'PID' => $response[ 'GetPIDFromLoginResult' ] );
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 30, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {            
            return array( 'IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
    
    private function _GetSkinID( $locatorname )
    {
        $response = $this->_API->GetSkinID( $locatorname );

        if ( !$this->_API->GetError() )
        {           
            if ( $response[ 'GetSkinIDResult' ] )
            {
                return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'SkinID' => $response[ 'GetSkinIDResult' ] );
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 30, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {            
            return array( 'IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
       
    private function Login( $login, $password = '', $skinID = 1 )
    {
        $response = $this->_GetPIDFromLogin( $login );
        
        if ( !is_null( $response ) )
        {
            if ( $response[ 'IsSucceed' ] == true )
            {
                $PID = $response[ 'PID' ];

                if($password == ''){
                    $response = $this->_GetAccountInfoByPID( $PID );
                
                    if ( !is_null( $response ) )
                    {
                        if ( $response[ 'IsSucceed' ] == true )
                        {
                            $accountInfo = $response[ 'AccountInfo' ];

                            $hashedPassword = sha1( $accountInfo[ 'GetAccountInfoByPIDResult' ][ 'password' ] );

                            $response = $this->_API->Login( 1, $PID, $hashedPassword, 1, $_SERVER[ 'HTTP_HOST' ], $skinID );

                            if ( !$this->_API->GetError() )
                            {
                                if ( is_array( $response ) )
                                {
                                    if ( $response[ 'LoginResult' ] )
                                        return $response[ 'LoginResult' ];
                                }
                            }
                        }
                    }
                }
                else
                {
                    $hashedPassword = sha1( $password );

                    $response = $this->_API->Login( 1, $PID, $hashedPassword, 1, $_SERVER[ 'HTTP_HOST' ], $skinID );

                    if ( !$this->_API->GetError() )
                    {
                        if ( is_array( $response ) )
                        {
                            if ( $response[ 'LoginResult' ] )
                                return $response[ 'LoginResult' ];
                        }
                    }
                }
                
            }
        }        
        
        return NULL;
    }    
    
    public function SetDebug( $boolean )
    {
        $this->_debug = $boolean;
    }
    
    public function GetPendingGamesByPID($PID)
    {
        $response = $this->_API->GetPendingGamesByPID( $PID );

        if ( !$this->_API->GetError() )
        {           
            if(!is_null($response['GetPendingGamesByPIDResult'])){
                if (is_array($response) )
                {
                    return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 
                                                                'PendingGames' => $response);
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 30, 'ErrorMessage' => 'Response malformed' );
                }
            } 
            else 
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 66, 'ErrorMessage' => 'No Pending Game Bet.' );
            }
        }
        else
        {            
            return array( 'IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }

        return null;
    }
    
    public function GetPIDUsingLogin($login)
    {
        return $this->_GetPIDFromLogin($login);
    }
    
    public function LogoutPlayer($PID)
    {
        return $response = $this->_API->logoutPlayer( $PID );
    }

    // CCT BEGIN added
    public function GetPlayerClassification($pid)
    {
        $getPlayerClassResult = $this->_API->getPlayerClasification($pid);
        if(!is_null($getPlayerClassResult))
        {
            $response = $getPlayerClassResult['GetPlayerClassResult']['Data'];
            if($response == false)
            {
                $errormessage = $response['GetPlayerClassResult']['Data']['PlayerClass']['ClassID'];
                //return array('IsSucceed'=>true, 'ErrorMessage'=>'RTG: Get player classification was successfully retrieved');
                return array('IsSucceed'=>true, 'ErrorMessage'=>$errormessage);
            }
            else
            {
                $errorcoderesult = $getPlayerClassResult['GetPlayerClassResult']['ErrorCode'];
                $errormessage = $getPlayerClassResult['GetPlayerClassResult']['Message'];
                return array('IsSucceed'=>false, 'ErrorCode'=>$errorcoderesult,'ErrorMessage'=>$errormessage);
            }
        }
        else
        {
            return array('IsSucceed'=>false, 'ErrorMessage'=>'Get player classification error');
        }
    }
    
    public function ChangePlayerClassification($pid, $playerClassID)
    {
        $changePlayerClassResult = $this->_API->changePlayerClasification($pid, $playerClassID);
        if(!is_null($changePlayerClassResult))
        {
            if(isset($changePlayerClassResult['ChangePlayerClassResult']['HasErrors']))
            {
                $response = $changePlayerClassResult['ChangePlayerClassResult']['HasErrors'];
                if($response == false)
                {
                    return array('IsSucceed'=>true, 'ErrorMessage'=>'RTG: Player Classification was successfully updated');
                }
                else
                {
                    $errorcoderesult = $changePlayerClassResult['ChangePlayerClassResult']['ErrorCode'];
                    $errormessage = $changePlayerClassResult['ChangePlayerClassResult']['Message'];
                    return array('IsSucceed'=>false, 'ErrorCode'=>$errorcoderesult,'ErrorMessage'=>$errormessage);
                }
            }
        }
        else
        {
            return array('IsSucceed'=>false, 'ErrorMessage'=>'Change player classification error');
        }
    }
      // CCT END added
}
?>