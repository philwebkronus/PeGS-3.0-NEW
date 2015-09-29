
<?php

/**
 * @Description: Wrapper for RTG - Abbott
 * @DateCreated: 2014-02-03
 */

require_once( ROOT_DIR . 'sys/class/CasinoAPI/RealtimeGamingCashierAPI2.class.php' );
require_once(ROOT_DIR . 'sys/class/CasinoAPI/RealtimeGamingWCFPlayerAPI.class.php');

class RealtimeGamingUBAPIWrapper
{
    const CASHIER_API = 0;
    const PLAYER_API = 1;
    
    private $_API;
    private $_debug = FALSE;
    private $_depositMethodId = 502; // 503
    private $_withdrawMethodId = 503; // 502
    
    public function __construct( $URI, $API, $certFilePath, $keyFilePath )
    {
        switch ($API)
        {
            case self::CASHIER_API:
                {
                    $this->_API = new RealtimeGamingCashierAPI( $URI, $certFilePath, $keyFilePath, '' ); break;
                }
            case self::PLAYER_API:
                {
                    $this->_API = new RealtimeGamingWCFPlayerAPI($URI, $certFilePath, $keyFilePath, ''); break;
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
    
    public function Deposit( $login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '',  $locatorName = '' )
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin( $login );

        if ( !is_null( $GetPIDFromLoginResult ) )
        {
            if ( $GetPIDFromLoginResult[ 'IsSucceed'] == true )
            {
                $PID = $GetPIDFromLoginResult[ 'PID' ];
                
                if(!empty($locatorName)){
                    $skinID = $this->_GetSkinID($locatorName);
                    if (isset($skinID['SkinID'])) {
                        $skinID = $skinID['SkinID'];
                    }
                } else { $skinID = 1; }

                $sessionId = $this->Login( $login );

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
    
    public function Withdraw( $login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '', $locatorName = '' )
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin( $login );

        if ( !is_null( $GetPIDFromLoginResult ) )
        {
            if ( $GetPIDFromLoginResult[ 'IsSucceed'] == true )
            {

                $PID = $GetPIDFromLoginResult[ 'PID' ];
                
                if(!empty($locatorName)){
                    $skinID = $this->_GetSkinID($locatorName);
                    if (isset($skinID['SkinID'])) {
                        $skinID = $skinID['SkinID'];
                    }
                } else { $skinID = 1; }
                
                $sessionId = $this->Login( $login, '', $skinID );
                
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

    private function _GetPIDFromLogin( $login )
    {        
        $response = $this->_API->GetPIDFromLogin( $login );

        if ( !$this->_API->GetError() )
        {           
            if ( $response[ 'GetPIDFromLoginResult' ] )
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
       
    private function Login( $login, $password = '', $skinID = 1 )
    {
        $response = $this->_GetPIDFromLogin( $login );
        
        if ( !is_null( $response ) )
        {
            if ( $response[ 'IsSucceed' ] == true )
            {
                $PID = $response[ 'PID' ];

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
        }        
        
        return NULL;
    }    
    
    public function SetDebug( $boolean )
    {
        $this->_debug = $boolean;
    }
    
    /**
     * @Description Get Pending Game Bet
     * @author aqdepliyan
     * @param int $PID
     * @return array
     */
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
            } else {
                return array( 'IsSucceed' => false, 'ErrorCode' => 66, 'ErrorMessage' => 'No Pending Game Bet.' );
            }
        }
        else
        {            
            return array( 'IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
        
        return null;
    }

    /**
     * @Description Get PID
     * @author aqdepliyan
     * @param string $login
     * @return array
     */
    public function GetPIDUsingLogin($login){
        return $this->_GetPIDFromLogin($login);
    }
    
    
    public function GetAccountInfoByLogin($login){
        
        $response = $this->_GetPIDFromLogin( $login );
       
        if ( !is_null( $response ) )
        {
            if ( $response[ 'IsSucceed' ] == true )
            {
                $PID = $response[ 'PID' ];

                $response = $this->_GetAccountInfoByPID( $PID );
                
                if ( !$this->_API->GetError() )
                {           
                    
                    if ( $response[ 'AccountInfo' ] )
                    {
                        return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'AccountInfo' => $response[ 'AccountInfo' ]['GetAccountInfoByPIDResult'] );
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
        }
        
        return null;
    }
    
    public function AddUser($login, $password, $aid, $country, 
            $casinoID, $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, 
            $city, $state, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, 
            $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $playerClass)
    { 
        $createTerminalResult = $this->_API->createTerminalAccount($login, $password, $aid, $country, $casinoID, $fname,
                $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, $state, $zip, $ip, $mac, $userID, 
                $downloadID, $birthdate, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, 
                $currentPosition, $thirdPartyPID, $playerClass);
        
        $pid = null;
        if(!is_null($createTerminalResult))
        {
            if(isset($createTerminalResult['CreatePlayerResult']['HasErrors']))
            {
                $playerResult = $createTerminalResult['CreatePlayerResult']['HasErrors'];
                if($playerResult == false)
                {
                    $pid = $createTerminalResult['CreatePlayerResult']['Data']['Player']['PID'];
                    return array('IsSucceed'=>true, 'ErrorMessage'=>'Service Successfully Created', 'ErrorID'=> 1, 'PID'=>$pid);
                }
                else
                {
                    $errorcoderesult = $createTerminalResult['CreatePlayerResult']['ErrorCode'];
                    switch ($errorcoderesult)
                    {
                          case 0:
                              $errorid = 0;
                              $msg = "RTG: Failed or internal server error";
                          break;
                          case 2:
                              $errorid = 2;
                              $msg = "RTG: Login too long or too short";
                          break;
                          case 3:
                              $errorid = 3;
                              $msg = "RTG: Password too long or too short";
                          break;
                          case 4:
                              $errorid = 4;
                              $msg = "RTG: Banned";
                          break;
                          case 5:
                              $errorid = 5;
                              $msg = "RTG: Account already exists";
                          break;
                          default:
                              $errorid = 0;
                              $msg = "Terminal Service Assignment : Error in creating service terminal account";
                          break;
                    }
                    return array('IsSucceed'=>false, 'ErrorMessage'=>$msg, 'ErrorID'=> $errorid, 'PID'=>$pid);
                }
            }            
        }
        else
        {
            return array('IsSucceed'=>false, 'ErrorMessage'=>'createNewPlayerFull error', 'ErrorID'=> 0, 'PID'=>$pid);
        }
    }

    public function ChangePassword($casinoID, $login, $oldpassword, $newpassword)
    {
        $changePwdResult = $this->_API->changePlayerPassword($login, $oldpassword, $newpassword);
        if(!is_null($changePwdResult))
        {
            if(isset($changePwdResult['ChangePasswordResult']['HasErrors']))
            {
                $passwordResult = $changePwdResult['ChangePasswordResult']['HasErrors'];
                if($passwordResult == false)
                {
                    return array('IsSucceed'=>true, 'ErrorMessage'=>'RTG: Account password was successfully updated');
                }
                else
                {
                    $errorcoderesult = $changePwdResult['ChangePasswordResult']['ErrorCode'];
                    $msg = $this->refUpdatePasswordStatus($errorcoderesult);
                    return array('IsSucceed'=>false, 'ErrorCode'=>$passwordResult,'ErrorMessage'=>$msg);
                }
            }
        }
        else
        {
            return array('IsSucceed'=>false, 'ErrorMessage'=>'createNewPlayerFull error');
        }
    }
    /**
     * RTG : Reference for status 
     * @param int $zstatus 
     * @return string message
     */
    protected function refUpdatePasswordStatus($zstatus)
    {
        switch($zstatus)
        {
            case 0:
                $msg = "Failed/unspecified(internal) error";
            break;
            case 1 :
                $msg = "Success";
            break;
            case 3:
                $msg = "New password too short or too long";
            break;
            case 6:
                $msg = "Old login/password do not match";
            break;
            case 11:
                $msg = "Player Passsword combination does not match";
            break;
            default :
                $msg = "RTG: Invalid Status";
            break;
        }
        return $msg;
    }
    
    
    public function ChangePlayerClassification($pid, $playerClassID, $userID)
    {
        $changePwdResult = $this->_API->changePlayerClasification($pid, $playerClassID, $userID);
        if(!is_null($changePwdResult))
        {
            if(isset($changePwdResult['ChangePlayerClassResult']['HasErrors']))
            {
                $passwordResult = $changePwdResult['ChangePlayerClassResult']['HasErrors'];
                if($passwordResult == false)
                {
                    return array('IsSucceed'=>true, 'ErrorMessage'=>'RTG: Player Classification was successfully updated');
                }
                else
                {
                    $errorcoderesult = $changePwdResult['ChangePlayerClassResult']['ErrorCode'];
                    
                    return array('IsSucceed'=>false, 'ErrorCode'=>$errorcoderesult,'ErrorMessage'=>$errorcoderesult);
                }
            }
        }
        else
        {
            return array('IsSucceed'=>false, 'ErrorMessage'=>'createNewPlayerFull error');
        }
    }
}

?>
