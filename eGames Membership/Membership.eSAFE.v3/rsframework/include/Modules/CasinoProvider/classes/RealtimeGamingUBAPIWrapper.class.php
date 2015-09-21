<?php

/*

Change Log History

1.0 2011-11-09 - Login method, get password plain text and convert to SHA1 instead of hashedPassword

*/

require_once('RealtimeGamingWCFPlayerAPI.class.php');

class RealtimeGamingUBAPIWrapper
{
    const CASHIER_API = 0;
    const PLAYER_API = 1;
    const GAME_API = 2;
    
    private $_API;
    private $_API2;
    private $_debug = FALSE;
    private $_depositMethodId = 502; // 503
    private $_withdrawMethodId = 503; // 502
    
    public function __construct( $playerUrl, $API, $localCert, $cache )
    {
        switch ($API)
        {
            case self::PLAYER_API:
                {
                    $this->_API = new RealtimeGamingWCFPlayerAPI($playerUrl, $localCert, $cache, ''); 
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
    
    public function Deposit( $login, $password, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '' )
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin( $login );
        
        if ( !is_null( $GetPIDFromLoginResult ) )
        {
            if ( $GetPIDFromLoginResult[ 'IsSucceed'] == true )
            {
                $PID = $GetPIDFromLoginResult[ 'PID' ];

                $sessionId = $this->Login( $login, $password );

                if ( !is_null( $sessionId ) )
                {
                    $response = $this->_API->DepositGeneric( 1, $PID, $this->_depositMethodId, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionId );

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
                //var_dump($login);exit;
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
                //var_dump($TrackingInfoTransactionSearchResult);exit;
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
    
    public function Withdraw( $login, $passwrod, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '' )
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin( $login );

        if ( !is_null( $GetPIDFromLoginResult ) )
        {
            if ( $GetPIDFromLoginResult[ 'IsSucceed'] == true )
            {

                $PID = $GetPIDFromLoginResult[ 'PID' ];

                $sessionId = $this->Login( $login, $passwrod );
                
                $pendingGames = $this->_GetPendingGamesByPID($PID);
                
                if($pendingGames['IsSucceed'] != true){
                    if ( !is_null( $sessionId ) )
                    {
                        $response = $this->_API->WithdrawGeneric( 1, $PID, $this->_withdrawMethodId, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionId );

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
                    return array( 'IsSucceed' => false, 'ErrorCode' => 65, 'ErrorMessage' => 'Pending Game Bet.' );
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
    
    public function AddUser($login, $password, $aid, $country, 
            $casinoID, $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, 
            $city, $state, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, 
            $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $playerClass)
    { 
        $createTerminalResult = $this->_API->createTerminalAccount($login, $password, $aid, $country, $casinoID, $fname,
                $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, $state, $zip, $ip, $mac, $userID, 
                $downloadID, $birthdate, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, 
                $currentPosition, $thirdPartyPID, $playerClass);
        

        if(!is_null($createTerminalResult))
        {
            if(isset($createTerminalResult['CreatePlayerResult']['HasErrors']))
            {
                $playerResult = $createTerminalResult['CreatePlayerResult']['HasErrors'];
                if($playerResult == false)
                {
                    return array('IsSucceed'=>true, 'ErrorMessage'=>'Service Successfully Created', 'ErrorID'=> 1);
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
                    return array('IsSucceed'=>false, 'ErrorMessage'=>$msg, 'ErrorID'=> $errorid);
                }
            }            
        }
        else
        {
            return array('IsSucceed'=>false, 'ErrorMessage'=>'createNewPlayerFull error', 'ErrorID'=> 0);
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
    
    public function GetPlayerClassification($pid)
    {
        $response = $this->_API->getPlayerClasification($pid);
        if( !$this->_API->GetError() )
        {
            if ( $response['GetPlayerClassResult']['Data'] )
            {          
                return array('IsSucceed'=>true, 'ErrorCode' => 0, 'ErrorMessage'=> null, 'ClassID' => $response['GetPlayerClassResult']['Data']['PlayerClass']['ClassID']);
            }
            else
            {
                return array('IsSucceed'=>false, 'ErrorCode'=> 30,'ErrorMessage'=> 'Response malformed');
            }
            
        }
        else
        {
            return array('IsSucceed'=>false, 'ErrorCode' => 31, 'ErrorMessage'=>'API Error: ' . $this->_API->GetError());
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
       
    private function Login( $login, $password )
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

                        $hashedPassword = $accountInfo[ 'GetAccountInfoByPIDResult' ][ 'password' ];

                        $response = $this->_API->Login( 1, $PID, $hashedPassword, 1, $_SERVER[ 'HTTP_HOST' ] );
                        
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
    
    public function SetDebug( $boolean )
    {
        $this->_debug = $boolean;
    }
    
    public function _GetPendingGamesByPID($login){
        
            if(is_numeric($login)){
                $response = $this->_API2->GetPendingGamesByPID( $login );

                if ( !$this->_API2->GetError() )
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
                    return array( 'IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API2->GetError() );
                }
            } else {
                $response = $this->_GetPIDFromLogin($login);
                
                if($response['IsSucceed'] == true){
                    $PID = $response[ 'PID' ];
                    
                    $response = $this->_API2->GetPendingGamesByPID( $PID );
                    
                    if ( !$this->_API2->GetError() )
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
                        return array( 'IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API2->GetError() );
                    }
                } 
            }
        
        return null;
    }
    
    public function GetPIDUsingLogin($login){
        return $this->_GetPIDFromLogin($login);
    }
    
    
}


?>
