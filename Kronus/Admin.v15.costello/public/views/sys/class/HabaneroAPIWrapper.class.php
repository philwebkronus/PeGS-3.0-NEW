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
                // EDITED CCT 07/31/2018 BEGIN
                //$retMsg = str_replace("'", "", $GetAccountExistsResult[ 'queryplayermethodResult' ][ 'Message' ]);
                $retMsg = 'Account does not exist.';
                // EDITED CCT 07/31/2018 END
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
                            // EDITED CCT 07/31/2018 BEGIN
                            //$msg = $QueryPlayerResult[ 'queryplayermethodResult' ][ 'Message' ] ;
                            $msg = 'Account does not exist.';
                            // EDITED CCT 07/31/2018 END
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