<?php

/*
 * John Aaron Vida
 * 12/14/2017
 */

require_once('HabaneroPlayerAPI.class.php');

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

    public function AddUser($login, $password, $playerClass) {
        $PlayerIP = Getenv("REMOTE_ADDR");
        $UserAgent = $_SERVER['HTTP_USER_AGENT'];

        $createTerminalResult = $this->_API->LoginOrCreatePlayer($PlayerIP, $UserAgent, $login, $password, $playerClass);

        if (!is_null($createTerminalResult)) {
            if (isset($createTerminalResult['createplayermethodResult'])) {
                if ($createTerminalResult['createplayermethodResult']['PlayerCreated'] == true) {
                    return array('IsSucceed' => true, 'ErrorMessage' => 'Service Successfully Created', 'ErrorCode' => 0);
                } else {
                    //Query if Player was created
                    $QueryPlayerResult = $this->_API->QueryPlayer($login, $password);

                    if (!$this->_API->GetError()) {
                        if (( $QueryPlayerResult['queryplayermethodResult']['Found'] ) == true) {
                            return array('IsSucceed' => true, 'ErrorMessage' => 'Service Successfully Created', 'ErrorCode' => 0);
                        } else {
                            $retMsg = str_replace("'", "", $QueryPlayerResult['queryplayermethodResult']['Message']);
                            $msg = $QueryPlayerResult['queryplayermethodResult']['Message'];
                            return array('IsSucceed' => false, 'ErrorMessage' => $msg, 'ErrorCode' => 3);
                        }
                    } else {
                        return array('IsSucceed' => false, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError(), 'ErrorCode' => 2);
                    }
                }
            }
        } else {
            return array('IsSucceed' => false, 'ErrorMessage' => 'Create Player API error', 'ErrorCode' => 1);
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

    public function ChangePassword($login, $password) {
        $changePwdResult = $this->_API->UpdatePlayerPassword($login, $password);

        if (!is_null($changePwdResult)) {
            if (isset($changePwdResult['updatepasswordmethodResult'])) {
                if ($changePwdResult['updatepasswordmethodResult']['Success'] == true) {
                    return array('IsSucceed' => true, 'ErrorMessage' => 'Habanero: Password updated');
                } else {
                    $msg = $changePwdResult['updatepasswordmethodResult']['Message'];
                    return array('IsSucceed' => false, 'ErrorMessage' => $msg);
                }
            }
        } else {
            return array('IsSucceed' => false, 'ErrorMessage' => 'createNewPlayerFull error');
        }
    }

}

?>
