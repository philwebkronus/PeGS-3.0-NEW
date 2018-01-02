<?php
require_once(ROOT_DIR . 'sys/class/CasinoAPI/HabaneroPlayerAPI.class.php');

class HabaneroAPIWrapper
{
    private $_API;
    private $_debug = FALSE;
    
    public function __construct($URI, $brandID, $apiKey)
    {
        $this->_API = new HabaneroPlayerAPI($URI, $brandID, $apiKey);
    }

    public function SetDebug($boolean)
    {
        $this->_debug = $boolean;
    }

    public function GetBalance($username, $password)
    {
        $GetAccountBalanceResult = $this->_API->QueryPlayer($username, $password);

        if ( !$this->_API->GetError() )
        {
            if (( $GetAccountBalanceResult[ 'queryplayermethodResult' ][ 'Found' ] ) == true)
            {
                $balance = (float)$GetAccountBalanceResult[ 'queryplayermethodResult' ][ 'RealBalance' ];
                return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'BalanceInfo' => array( 'Balance' => $balance ) );
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Player not found.' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }

    public function Withdraw( $login, $amount, $password, $tracking1)
    {
        $GetAccountBalanceResult = $this->_API->QueryPlayer($login, $password);

        if ( !$this->_API->GetError() )
        {
            if (( $GetAccountBalanceResult[ 'queryplayermethodResult' ][ 'Found' ] ) == true)
            {
                $amount = $amount * -1;
                $response = $this->_API->WithdrawFunds( $login, $password, $amount, $tracking1);

                if ( !$this->_API->GetError() )
                {
                    if ( is_array( $response ) )
                    {
                        return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response );
                    }
                    else
                    {
                        return array( 'IsSucceed' => false, 'ErrorCode' => 4, 'ErrorMessage' => 'Response malformed' );
                    }
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Player not found.' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
            
    }
    
    public function AccountExists($login, $password)
    {
        $GetAccountExistsResult = $this->_API->QueryPlayer($login, $password);

        if ( !$this->_API->GetError() )
        {
            if (( $GetAccountExistsResult[ 'queryplayermethodResult' ][ 'Found' ] ) == true)
            {
                return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'Count' => 1 );
            }
            else
            {
                $retMsg = str_replace("'", "", $GetAccountExistsResult[ 'queryplayermethodResult' ][ 'Message' ]);
                $msg = $retMsg;
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => $msg, 'Count' => 0 );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError(), 'Count' => 0);
        }
            
    }
    
    public function AddUser($login, $password, $playerClass)
    {
        $PlayerIP = Getenv("REMOTE_ADDR");
        $UserAgent = $_SERVER['HTTP_USER_AGENT'];       

        $createTerminalResult = $this->_API->CreatePlayer($PlayerIP, $UserAgent, $login, $password, $playerClass);

        if(!is_null($createTerminalResult))
        {
            if(isset($createTerminalResult['createplayermethodResult']))
            {
                if ($createTerminalResult['createplayermethodResult'][ 'PlayerCreated' ] == true)
                {
                    return array('IsSucceed'=>true, 'ErrorMessage'=>'Service Successfully Created', 'ErrorCode'=> 0);
                }
                else
                {
                    //Query if Player was created
                    $QueryPlayerResult = $this->_API->QueryPlayer($login, $password);

                    if ( !$this->_API->GetError() )
                    {
                        if (( $QueryPlayerResult[ 'queryplayermethodResult' ][ 'Found' ] ) == true)
                        {
                            return array('IsSucceed'=>true, 'ErrorMessage'=>'Service Successfully Created', 'ErrorCode'=> 0);
                        }
                        else 
                        {
                            $retMsg = str_replace("'", "", $QueryPlayerResult[ 'queryplayermethodResult' ][ 'Message' ]);
                            $msg = $QueryPlayerResult[ 'queryplayermethodResult' ][ 'Message' ] ;
                            return array('IsSucceed'=>false, 'ErrorMessage'=>$msg, 'ErrorCode'=> 3);
                        }
                    }
                    else
                    {
                        return array( 'IsSucceed' => false, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError(), 'ErrorCode' => 2);
                    }
                }
            }            
        }
        else
        {
            return array('IsSucceed'=>false, 'ErrorMessage'=>'Create Player API error', 'ErrorCode'=> 1);
        }
    }

    public function ChangePassword($login, $password)
    {
        $changePwdResult = $this->_API->UpdatePlayerPassword($login, $password);
        
        if(!is_null($changePwdResult))
        {
            if(isset($changePwdResult['updatepasswordmethodResult']))
            {
                if ($changePwdResult['updatepasswordmethodResult']['Success'] == true)
                {
                    return array('IsSucceed'=>true, 'ErrorMessage'=>'Habanero: Password updated');
                }
                else
                {
                    $msg = $changePwdResult['updatepasswordmethodResult']['Message'];
                    return array('IsSucceed'=>false, 'ErrorMessage'=>$msg);
                }
            }
        }
        else
        {
            return array('IsSucceed'=>false, 'ErrorMessage'=>'createNewPlayerFull error');
        }
    }
    
    protected function refUpdatePasswordStatus($zstatus)
    {
//        switch($zstatus)
//        {
//            case 0:
//                $msg = "Failed/unspecified(internal) error";
//            break;
//            case 1 :
//                $msg = "Success";
//            break;
//            case 3:
//                $msg = "New password too short or too long";
//            break;
//            case 6:
//                $msg = "Old login/password do not match";
//            break;
//            default :
//                $msg = "RTG: Invalid Status";
//            break;
//        }
//        return $msg;
    }
    
    private function _GetAccountInfoByPID( $PID )
    {
//        $response = $this->_API->GetAccountInfoByPID( 1, $PID );
//
//        if ( !$this->_API->GetError() )
//        {
//            if ( $response[ 'GetAccountInfoByPIDResult' ] )
//            {
//                return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'AccountInfo' => $response );
//            }
//            else
//            {
//                return array( 'IsSucceed' => false, 'ErrorCode' => 20, 'ErrorMessage' => 'Response malformed' );
//            }
//        }
//        else
//        {
//            return array( 'IsSucceed' => false, 'ErrorCode' => 21, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
//        }
    }

    private function _GetPIDFromLogin( $login )
    {        
//        $response = $this->_API->GetPIDFromLogin( $login );
//
//        if ( !$this->_API->GetError() )
//        {           
//            if ( $response[ 'GetPIDFromLoginResult' ] )
//            {
//                return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'PID' => $response[ 'GetPIDFromLoginResult' ] );
//            }
//            else
//            {
//                return array( 'IsSucceed' => false, 'ErrorCode' => 30, 'ErrorMessage' => 'Response malformed' );
//            }
//        }
//        else
//        {            
//            return array( 'IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
//        }
    }
       
    private function Login( $login )
    {
//        $response = $this->_GetPIDFromLogin( $login );
//        
//        if ( !is_null( $response ) )
//        {
//            if ( $response[ 'IsSucceed' ] == true )
//            {
//                $PID = $response[ 'PID' ];
//
//                $response = $this->_GetAccountInfoByPID( $PID );
//
//                if ( !is_null( $response ) )
//                {
//                    if ( $response[ 'IsSucceed' ] == true )
//                    {
//                        $accountInfo = $response[ 'AccountInfo' ];
//        		$hashedPassword = sha1( $accountInfo[ 'GetAccountInfoByPIDResult' ][ 'password' ] );
//
//                        $response = $this->_API->Login( 1, $PID, $hashedPassword, 1, $_SERVER[ 'HTTP_HOST' ] );
//
//                        if ( !$this->_API->GetError() )
//                        {
//                            if ( is_array( $response ) )
//                            {
//                                if ( $response[ 'LoginResult' ] )
//                                    return $response[ 'LoginResult' ];
//                            }
//                        }
//                    }
//                }
//            }
//        }        
//        
//        return NULL;
    }   
    
    public function GetAccountInfoByLogin($login)
    {
//        $response = $this->_GetPIDFromLogin( $login );
//        
//        if ( !is_null( $response ) )
//        {
//            if ( $response[ 'IsSucceed' ] == true )
//            {
//                $PID = $response[ 'PID' ];
//
//                $response = $this->_GetAccountInfoByPID( $PID );
//                
//                if ( !$this->_API->GetError() )
//                {           
//                    if ( $response[ 'AccountInfo' ] )
//                    {
//                        return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'AccountInfo' => $response[ 'AccountInfo' ]['GetAccountInfoByPIDResult'] );
//                    }
//                    else
//                    {
//                        return array( 'IsSucceed' => false, 'ErrorCode' => 30, 'ErrorMessage' => 'Response malformed' );
//                    }
//                }
//                else
//                {            
//                    return array( 'IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
//                }
//            }
//        }
//        
//        return null;
    }
    
    public function Deposit($login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '')
    {
//        //$GetPIDFromLoginResult = $this->_GetPIDFromLogin( $login );
//
//        //if ( !is_null( $GetPIDFromLoginResult ) )
//        //{
//            if ( $GetPIDFromLoginResult[ 'IsSucceed'] == true )
//            {
//                $PID = $GetPIDFromLoginResult[ 'PID' ];
//
//                $sessionId = $this->Login( $login );
//
//                if ( !is_null( $sessionId ) )
//                {
//                    $response = $this->_API->DepositGeneric( 1, $PID, $this->_depositMethodId, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionId );
//
//                    if ( !$this->_API->GetError() )
//                    {
//                        if ( is_array( $response ) )
//                        {
//                            return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response );
//                        }
//                        else
//                        {
//                            return array( 'IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Response malformed' );
//                        }
//                    }
//                    else
//                    {
//                        return array( 'IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
//                    }
//                }
//                else
//                {
//                    return array( 'IsSucceed' => false, 'ErrorCode' => 52, 'ErrorMessage' => 'Session ID error' );
//                }
//            }
//            else
//            {
//                return array( 'IsSucceed' => false, 'ErrorCode' => 53, 'ErrorMessage' => 'PID error' );
//            }
//        //}
//        //else
//        //{
//        //    return array( 'IsSucceed' => false, 'ErrorCode' => 54, 'ErrorMessage' => 'GetPIDFromLogin error' );
//        //}
    }
    
    public function TransactionSearchInfo($tracking1)
    {
        $queryTransResult = $this->_API->QueryTrans($tracking1);
        
        if(!is_null($queryTransResult))
        {
            if(isset($queryTransResult['querytransmethodResult']))
            {
                if ($queryTransResult['querytransmethodResult']['Success'] == true)
                {
                    return array('IsSucceed'=>true, 'ErrorCode' => 0, 'ErrorMessage'=>null, 'TransactionInfo' => $queryTransResult);
                }
                else
                {
                    return array('IsSucceed'=>false, 'ErrorCode' => 3, 'ErrorMessage'=> 'Transaction Not Found.');
                }
            }
            else
            {
                return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed' );
            }            
        }
        else
        {
            return array('IsSucceed'=>false, 'ErrorCode' => 1, 'ErrorMessage' =>  'API Error');
        }
    }
}
?>