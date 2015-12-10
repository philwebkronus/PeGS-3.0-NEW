<?php

/*

  Change Log History

  1.0 2011-11-09 - Login method, get password plain text and convert to SHA1 instead of hashedPassword

 */

//require_once( ROOT_DIR . 'sys/class/CasinoAPI/RealtimeGamingCashierAPI2.class.php' );
//Mirage::loadComponents('CasinoAPI/RealtimeGamingCashierAPI2.class');
//require_once('RealtimeGamingCashierAPI2.class.php');

class RealtimeGamingAPIWrapper
{

    const CASHIER_API = 0;
    const PLAYER_API = 1;
    const GAME_API = 2;

    private $_API;
    private $_debug = FALSE;
    private $_depositMethodId = 502; // 503
    private $_withdrawMethodId = 503; // 502

    public function __construct($URI, $API, $certFilePath, $keyFilePath, $caching = '')
    {
        Yii::import('application.components.RealtimeGamingCashierAPI2');
        switch ($API)
        {
            case self::CASHIER_API: {
                    $this->_API = new RealtimeGamingCashierAPI2($URI, $certFilePath, $keyFilePath, $caching);
                    break;
                }
            case self::GAME_API: {
                    $this->_API = new RealtimeGamingCasinoGamesAPI($URI, $certFilePath, $keyFilePath, $caching);
                    break;
                }
            case self::PLAYER_API: {
                    // RESERVED
                }
        }
    }

    public function SetDepositMethodId($id)
    {
        $this->_depositMethodId = $id;
    }

    public function SetWithdrawalMethodId($id)
    {
        $this->_withdrawMethodId = $id;
    }

    public function Deposit($login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '')
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin($login);

        if (!is_null($GetPIDFromLoginResult))
        {
            if ($GetPIDFromLoginResult['IsSucceed'] == true)
            {
                $PID = $GetPIDFromLoginResult['PID'];

                $sessionId = $this->Login($login);

                if (!is_null($sessionId))
                {
                    $response = $this->_API->DepositGeneric(1, $PID, $this->_depositMethodId, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionId);

                    if (!$this->_API->GetError())
                    {
                        if (is_array($response))
                        {
                            return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response);
                        }
                        else
                        {
                            return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Response malformed');
                        }
                    }
                    else
                    {
                        return array('IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
                    }
                }
                else
                {
                    return array('IsSucceed' => false, 'ErrorCode' => 52, 'ErrorMessage' => 'Session ID error');
                }
            }
            else
            {
                return array('IsSucceed' => false, 'ErrorCode' => 53, 'ErrorMessage' => 'PID error');
            }
        }
        else
        {
            return array('IsSucceed' => false, 'ErrorCode' => 54, 'ErrorMessage' => 'GetPIDFromLogin error');
        }
    }

    public function GetBalance($login)
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin($login);

        if (!is_null($GetPIDFromLoginResult))
        {
            if ($GetPIDFromLoginResult['IsSucceed'] == true)
            {
                $GetAccountBalanceResult = $this->_API->GetAccountBalance(1, $GetPIDFromLoginResult['PID'], 1);

                if (!$this->_API->GetError())
                {
                    if ($GetAccountBalanceResult['GetAccountBalanceResult']['balance'])
                    {
                        $balance = (float) $GetAccountBalanceResult['GetAccountBalanceResult']['balance'];
                        $bonusBalance = (float) $GetAccountBalanceResult['GetAccountBalanceResult']['bonusBalance'];

                        // get redeamable balance
                        $redeemable = $balance - $bonusBalance;

                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'BalanceInfo' => array('Balance' => $balance, 'BonusBalance' => $bonusBalance, 'Redeemable' => $redeemable));
                    }
                    else
                    {
                        return array('IsSucceed' => false, 'ErrorCode' => 10, 'ErrorMessage' => 'Response malformed');
                    }
                }
                else
                {
                    return array('IsSucceed' => false, 'ErrorCode' => 11, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
                }
            }
            else
            {
                return array('IsSucceed' => false, 'ErrorCode' => 12, 'ErrorMessage' => 'Response malformed');
            }
        }
        else
        {
            return array('IsSucceed' => false, 'ErrorCode' => 13, 'ErrorMessage' => 'Response malformed');
        }
    }

    public function TransactionSearchInfo($login, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '')
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin($login);

        if (!is_null($GetPIDFromLoginResult))
        {
            if ($GetPIDFromLoginResult['IsSucceed'] == true)
            {
                $PID = $GetPIDFromLoginResult['PID'];

                $TrackingInfoTransactionSearchResult = $this->_API->TrackingInfoTransactionSearch($PID, $tracking1, $tracking2, $tracking3, $tracking4);

                if (!$this->_API->GetError())
                {
                    if (is_array($TrackingInfoTransactionSearchResult))
                    {
                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $TrackingInfoTransactionSearchResult);
                    }
                    else
                    {
                        return array('IsSucceed' => false, 'ErrorCode' => 40, 'ErrorMessage' => 'Response malformed');
                    }
                }
                else
                {
                    return array('IsSucceed' => false, 'ErrorCode' => 41, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
                }
            }
            else
            {
                return array('IsSucceed' => false, 'ErrorCode' => 42, 'ErrorMessage' => 'Error retrieving PID');
            }
        }
        else
        {
            return array('IsSucceed' => false, 'ErrorCode' => 42, 'ErrorMessage' => 'Error retrieving PID');
        }
    }

    public function Withdraw($login, $amount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '')
    {
        $GetPIDFromLoginResult = $this->_GetPIDFromLogin($login);

        if (!is_null($GetPIDFromLoginResult))
        {
            if ($GetPIDFromLoginResult['IsSucceed'] == true)
            {

                $PID = $GetPIDFromLoginResult['PID'];

                $sessionId = $this->Login($login);

                if (!is_null($sessionId))
                {
                    $response = $this->_API->WithdrawGeneric(1, $PID, $this->_withdrawMethodId, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionId);

                    if (!$this->_API->GetError())
                    {
                        if (is_array($response))
                        {
                            return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response);
                        }
                        else
                        {
                            return array('IsSucceed' => false, 'ErrorCode' => 60, 'ErrorMessage' => 'Response malformed');
                        }
                    }
                    else
                    {
                        return array('IsSucceed' => false, 'ErrorCode' => 61, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
                    }
                }
                else
                {
                    return array('IsSucceed' => false, 'ErrorCode' => 62, 'ErrorMessage' => 'Session ID error');
                }
            }
            else
            {
                return array('IsSucceed' => false, 'ErrorCode' => 63, 'ErrorMessage' => 'PID error');
            }
        }
        else
        {
            return array('IsSucceed' => false, 'ErrorCode' => 64, 'ErrorMessage' => 'GetPIDFromLogin error');
        }
    }

    private function _GetAccountInfoByPID($PID)
    {
        $response = $this->_API->GetAccountInfoByPID(1, $PID);

        if (!$this->_API->GetError())
        {
            if ($response['GetAccountInfoByPIDResult'])
            {
                return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'AccountInfo' => $response);
            }
            else
            {
                return array('IsSucceed' => false, 'ErrorCode' => 20, 'ErrorMessage' => 'Response malformed');
            }
        }
        else
        {
            return array('IsSucceed' => false, 'ErrorCode' => 21, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
        }
    }

    public function GetAccountInfoByLogin($login)
    {

        $response = $this->_GetPIDFromLogin($login);

        if (!is_null($response))
        {
            if ($response['IsSucceed'] == true)
            {
                $PID = $response['PID'];

                $response = $this->_GetAccountInfoByPID($PID);

                if (!$this->_API->GetError())
                {

                    if ($response['AccountInfo'])
                    {
                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'AccountInfo' => $response['AccountInfo']['GetAccountInfoByPIDResult']);
                    }
                    else
                    {
                        return array('IsSucceed' => false, 'ErrorCode' => 30, 'ErrorMessage' => 'Response malformed');
                    }
                }
                else
                {
                    return array('IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
                }
            }
        }

        return null;
    }

    private function _GetPIDFromLogin($login)
    {
        $response = $this->_API->GetPIDFromLogin($login);

        if (!$this->_API->GetError())
        {
            if ($response['GetPIDFromLoginResult'] != null)
            {
                return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'PID' => $response['GetPIDFromLoginResult']);
            }
            else
            {
                return array('IsSucceed' => false, 'ErrorCode' => 30, 'ErrorMessage' => 'Response malformed');
            }
        }
        else
        {
            return array('IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
        }
    }

    private function Login($login)
    {
        $response = $this->_GetPIDFromLogin($login);

        if (!is_null($response))
        {
            if ($response['IsSucceed'] == true)
            {
                $PID = $response['PID'];

                $response = $this->_GetAccountInfoByPID($PID);

                if (!is_null($response))
                {
                    if ($response['IsSucceed'] == true)
                    {
                        $accountInfo = $response['AccountInfo'];

                        $hashedPassword = sha1($accountInfo['GetAccountInfoByPIDResult']['password']);

                        $response = $this->_API->Login(1, $PID, $hashedPassword, 1, $_SERVER['HTTP_HOST']);

                        if (!$this->_API->GetError())
                        {
                            if (is_array($response))
                            {
                                if ($response['LoginResult'])
                                    return $response['LoginResult'];
                            }
                        }
                    }
                }
            }
        }

        return NULL;
    }

    public function SetDebug($boolean)
    {
        $this->_debug = $boolean;
    }

    public function GetPendingGamesByPID($PID)
    {

        $response = $this->_API->GetPendingGamesByPID($PID);

        if (!$this->_API->GetError())
        {
            if (!is_null($response['GetPendingGamesByPIDResult']))
            {
                if (is_array($response))
                {
                    return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null,
                        'PendingGames' => $response);
                }
                else
                {
                    return array('IsSucceed' => false, 'ErrorCode' => 30, 'ErrorMessage' => 'Response malformed');
                }
            }
            else
            {
                return array('IsSucceed' => false, 'ErrorCode' => 66, 'ErrorMessage' => 'No Pending Game Bet.');
            }
        }
        else
        {
            return array('IsSucceed' => false, 'ErrorCode' => 31, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
        }

        return null;
    }

    public function GetPIDUsingLogin($login)
    {
        return $this->_GetPIDFromLogin($login);
    }

}

?>
