<?php

/*
 * John Aaron Vida
 * 12/14/2017
 */

Mirage::loadComponents(array('CasinoAPI/HabaneroPlayerAPI.class'));

class HabaneroAPIWrapper {

    const PLAYER_API = 0;

    private $_API;
    private $_debug = FALSE;

    public function __construct($URI, $APIkey, $brandID, $currencyCode = 'PHP', $serverID = '', $API) {
        switch ($API) {
            case self::PLAYER_API: {
                    $this->_API = new HabaneroPlayerAPI($URI, $brandID, $APIkey, $currencyCode);
                    break;
                }
        }
    }

    public function Deposit($Username, $Password, $Amount, $RequestId) {

        $isAccountExists = $this->GetBalance($Username, $Password);

        if ($isAccountExists['TransactionInfo']['Found'] == true) {
            $depositResponse = $this->_API->DepositPlayerMoney($Username, $Password, $Amount, $RequestId);

            if (!$this->_API->GetError()) {
                if (is_array($depositResponse)) {
                    if ($depositResponse['depositmethodResult']['Success'] == true) {
                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $depositResponse['depositmethodResult']);
                    } else {
                        return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => $depositResponse['depositmethodResult']['Message']);
                    }
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Request Malformed');
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
            }
        }
    }

    public function GetBalance($Username, $Password) {
        $queryPlayerResponse = $this->_API->QueryPlayer($Username, $Password);

        if (!$this->_API->GetError()) {
            if (is_array($queryPlayerResponse)) {
                if ($queryPlayerResponse['queryplayermethodResult']['Found'] == true) {
                    return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $queryPlayerResponse['queryplayermethodResult']);
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => $queryPlayerResponse['queryplayermethodResult']['Message']);
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Request Malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
        }
    }

    public function TransactionSearchInfo($RequestId) {
        $queryTransferResponse = $this->_API->QueryTransfer($RequestId);

        if (!$this->_API->GetError()) {
            if (is_array($queryTransferResponse)) {
                if ($queryTransferResponse['querytransferResult']['Found'] == true) {
                    return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $queryTransferResponse['queryplayermethodResult']);
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => $queryTransferResponse['queryplayermethodResult']['Message']);
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Request Malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
        }
    }

    public function Withdraw($Username, $Password, $Amount, $RequestId) {
        //Set Amount to Negative
        $Amount = $Amount = $Amount * -1;

        $isAccountExists = $this->GetBalance($Username, $Password);

        if ($isAccountExists['TransactionInfo']['Found'] == true) {
            $WithdrawPlayerMoneyResponse = $this->_API->WithdrawPlayerMoney($Username, $Password, $Amount, $RequestId);

            if (!$this->_API->GetError()) {
                if (is_array($WithdrawPlayerMoneyResponse)) {
                    if ($WithdrawPlayerMoneyResponse['withdrawmethodResult']['Success'] == true) {
                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $WithdrawPlayerMoneyResponse['withdrawmethodResult']);
                    } else {
                        return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => $WithdrawPlayerMoneyResponse['withdrawmethodResult']['Message']);
                    }
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Request Malformed');
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
            }
        }
    }

    public function LogoutPlayer($Username, $Password) {
        $isAccountExists = $this->GetBalance($Username, $Password);

        if ($isAccountExists['TransactionInfo']['Found'] == true) {
            $logoutResponse = $this->_API->logoutPlayer($Username, $Password);
            if (!$this->_API->GetError()) {
                if (is_array($logoutResponse)) {
                    if ($logoutResponse['logoutplayermethodResult']['Success'] == true) {
                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $logoutResponse['logoutplayermethodResult']);
                    } else {
                        return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => $logoutResponse['logoutplayermethodResult']['Message']);
                    }
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Request Malformed');
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
            }
        }
    }

    public function GetPendingGamesHabanero($Username, $Password) {
        $isAccountExists = $this->GetBalance($Username, $Password);

        if ($isAccountExists['TransactionInfo']['Found'] == true) {
            $getPendingGamesResponse = $this->_API->GetPendingGamesHabanero($Username);
            if (!$this->_API->GetError()) {
                if (is_array($getPendingGamesResponse)) {
                    if ($getPendingGamesResponse['getgamesmethodResult'] != null) {
                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $getPendingGamesResponse['getgamesmethodResult']);
                    } else {
                        return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => $getPendingGamesResponse['getgamesmethodResult']);
                    }
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Request Malformed');
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
            }
        }
    }

    public function WithdrawPlayerPointsHabanero($Username, $Password, $RequestId) {
        $isAccountExists = $this->GetBalance($Username, $Password);

        if ($isAccountExists['TransactionInfo']['Found'] == true) {
            $WithdrawAll = true;
            $WithdrawPlayerPointsCustom = $this->_API->WithdrawPlayerPointsCustom($Username, $Password, $WithdrawAll, $RequestId);
            if (!$this->_API->GetError()) {
                if (is_array($WithdrawPlayerPointsCustom)) {
                    if ($WithdrawPlayerPointsCustom['withdrawplayerpointscustommethodResult'] != null) {
                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $WithdrawPlayerPointsCustom['withdrawplayerpointscustommethodResult']);
                    } else {
                        return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => $WithdrawPlayerPointsCustom['withdrawplayerpointscustommethodResult']);
                    }
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Request Malformed');
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
            }
        }
    }

}

?>
