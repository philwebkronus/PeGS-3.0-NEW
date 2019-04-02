<?php

class RealtimeGamingAPIWrapper {

    public function EncryptCredentials($username, $password) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingWCFRemoteAPI');

        $url = Yii::app()->params->forcetapi;
        $certpath = Yii::app()->params->rtg_certkey_dir . '19/cert-key.pem';

        $rtgremote = new RealtimeGamingWCFRemoteAPI($url, $certpath, '');

        $result = $rtgremote->remoteAuth($username, $password);

        if (!empty($result)) {
            if ($result['encryptCredentialsResult']['Success'] == true && $result['encryptCredentialsResult']['ErrorText'] == '') {
                $result = $result['encryptCredentialsResult'];
            } else {
                $result = '';
            }
        } else {
            $result = '';
        }
        return $result;
    }

    public function GetBalance($serviceid, $username) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->cashierapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        $result = $rtgcashier->GetPIDFromLogin($username);
        if (!empty($result)) {
            if (!empty($result['GetPIDFromLoginResult']) && $result['GetPIDFromLoginResult'] !== null) {

                $pid = $result['GetPIDFromLoginResult'];

                $result2 = $rtgcashier->GetAccountBalance(1, $pid);

                if (!empty($result2['GetAccountBalanceResult']) && $result2['GetAccountBalanceResult'] !== null) {
                    $result2 = $result2['GetAccountBalanceResult'];
                } else {
                    $result2 = 'Cant connect to casino';
                }
            } else {
                $result2 = 'Cant connect to casino';
            }
        } else {
            $result2 = 'Cant connect to casino';
        }
        return $result2;
    }

    public function GetPID($serviceid, $username) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->cashierapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';

        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');

        $result = $rtgcashier->GetPIDFromLogin($username);

        if (!empty($result)) {
            if (!empty($result['GetPIDFromLoginResult']) && $result['GetPIDFromLoginResult'] !== null) {
                $result2 = $result['GetPIDFromLoginResult'];
            } else {
                $result2 = '';
            }
        } else {
            $result2 = '';
        }
        return $result2;
    }

    public function GetToken($serviceid, $pid) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->cashierapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';

        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');

        $result = $rtgcashier->CreateToken($pid);

        if (!empty($result)) {
            if (!empty($result['CreateTokenResult']) && $result['CreateTokenResult'] !== null) {
                $result2 = $result['CreateTokenResult'];
            } else {
                $result2 = '';
            }
        } else {
            $result2 = '';
        }
        return $result2;
    }

    public function Withdraw($serviceid, $username, $password, $casinoID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $locatorname = '') {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->cashierapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';

        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');

        $PID = $this->GetPID($serviceid, $username);
        if (!empty($locatorname)) {
            $skinID = $this->_GetSkinID($serviceid, $locatorname);
        } else {
            $skinID = 1;
        }

        if ($PID != NULL) {
            $hashedPassword = sha1($password);

            $login = $rtgcashier->Login($casinoID, $PID, $hashedPassword, 1, $_SERVER['HTTP_HOST'], $skinID);

            if (!empty($login)) {
                $sessionID = $login['LoginResult'];
                $methodID = Yii::app()->params->withdrawmethodid;
                $response = $rtgcashier->WithdrawGeneric($casinoID, $PID, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID, $skinID);

                if (!$rtgcashier->GetError()) {
                    if (is_array($response)) {
                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response);
                    } else {
                        return array('IsSucceed' => false, 'ErrorCode' => 60, 'ErrorMessage' => 'Response malformed');
                    }
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 61, 'ErrorMessage' => 'API Error: ' . $rtgcashier->GetError());
                }
            } else {
                return 'Cant connect to RTG';
            }
        } else {
            return 'Cant connect to RTG';
        }
    }

    private function _GetSkinID($serviceid, $locatorname) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->cashierapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';

        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');

        $result = $rtgcashier->GetSkinID($locatorname);

        if (!empty($result)) {
            if (!empty($result['GetSkinIDResult']) && $result['GetSkinIDResult'] !== null) {
                $result2 = $result['GetSkinIDResult'];
            } else {
                $result2 = '';
            }
        } else {
            $result2 = '';
        }
        return $result2;
    }

    public function Deposit($serviceid, $username, $password, $casinoID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $locatorname = '') {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->cashierapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';

        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');

        $PID = $this->GetPID($serviceid, $username);

        if (!empty($locatorname)) {
            $skinID = $this->_GetSkinID($serviceid, $locatorname);
        } else {
            $skinID = 1;
        }
        if ($PID != NULL) {

            $hashedPassword = sha1($password);

            $login = $rtgcashier->Login($casinoID, $PID, $hashedPassword, 1, $_SERVER['HTTP_HOST'], $skinID);

            if (!empty($login)) {
                $sessionID = $login['LoginResult'];
                $methodID = Yii::app()->params->depositmethodid;
                $response = $rtgcashier->DepositGeneric($casinoID, $PID, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID, $skinID);

                if (!$rtgcashier->GetError()) {
                    if (is_array($response)) {
                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response);
                    } else {
                        return array('IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Response malformed');
                    }
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $rtgcashier->GetError());
                }
            } else {
                return 'Cant connect to RTG';
            }
        } else {
            return 'Cant connect to RTG';
        }
    }

    public function TransactionSerachInfo($serviceid, $username, $tracking1, $tracking2, $tracking3, $tracking4) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->cashierapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';

        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');

        $PID = $this->GetPID($serviceid, $username);

        if ($PID != NULL) {
            $TrackingInfoTransactionSearchResult = $rtgcashier->TrackingInfoTransactionSearch($PID, $tracking1, $tracking2, $tracking3, $tracking4);

            if (!$rtgcashier->GetError()) {
                if (is_array($TrackingInfoTransactionSearchResult)) {
                    return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $TrackingInfoTransactionSearchResult);
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 40, 'ErrorMessage' => 'Response malformed');
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 41, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError());
            }
        } else {
            return 'Cant connect to RTG';
        }
    }

    public function AddToCurrentBalance($serviceid, $username, $amount) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->cashierapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        $result = $rtgcashier->GetPIDFromLogin($username);

        if (!empty($result)) {
            if (!empty($result['GetPIDFromLoginResult']) && $result['GetPIDFromLoginResult'] !== null) {

                $pid = $result['GetPIDFromLoginResult'];

                $result2 = $rtgcashier->AdjustComps(1, $pid, $amount, 0);

                if (!empty($result2['AdjustCompsResult']) && $result2['AdjustCompsResult'] !== null) {
                    $result2 = $result2['AdjustCompsResult'];
                } else {
                    $result2 = '';
                }
            } else {
                $result2 = '';
            }
        } else {
            $result2 = '';
        }

        return $result2;
    }

    public function DeductToCurrentBalance($serviceid, $username, $amount) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->cashierapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        $result = $rtgcashier->GetPIDFromLogin($username);

        if (!empty($result)) {
            if (!empty($result['GetPIDFromLoginResult']) && $result['GetPIDFromLoginResult'] !== null) {

                $pid = $result['GetPIDFromLoginResult'];

                $result2 = $rtgcashier->AdjustComps(1, $pid, -$amount, 1);

                if (!empty($result2['AdjustCompsResult']) && $result2['AdjustCompsResult'] !== null) {
                    $result2 = $result2['AdjustCompsResult'];
                } else {
                    $result2 = '';
                }
            } else {
                $result2 = '';
            }
        } else {
            $result2 = '';
        }

        return $result2;
    }

    public function logout($serviceid, $username) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingPlayerAPI');
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->cashierapi[$serviceid - 1];
        $url2 = Yii::app()->params->playerapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';
        $rtgplayer = new RealtimeGamingPlayerAPI($url2, $certpath, $keypath, '');
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');

        $PID = $rtgcashier->GetPIDFromLogin($username);
        if ($PID != NULL) {
            $result = $rtgplayer->logoutPlayer($PID['GetPIDFromLoginResult']);

            if (!empty($result)) {
                if (!empty($result['LogoutPlayerResult']) && $result['LogoutPlayerResult'] !== null) {

                    $result2 = $result['LogoutPlayerResult'];
                } else {
                    $result2 = '';
                }
            } else {
                $result2 = '';
            }
        } else {
            return 'Cant connect to RTG';
        }

        return $result2;
    }

    public function changePassword($serviceid, $casinoID, $login, $oldpassword, $newpassword) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingPlayerAPI');

        $url2 = Yii::app()->params->playerapi[$serviceid - 1];
        $certpath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir . $serviceid . '/key.pem';
        $rtgplayer = new RealtimeGamingPlayerAPI($url2, $certpath, $keypath, '');
        $result = $rtgplayer->changePlayerPassword($casinoID, $login, $oldpassword, $newpassword);
        return $result;
    }

    public function getPendingGamesRTG($Username, $serviceID) {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCasinoGamesAPI');

        $PID = $this->GetPID($serviceID, $Username);

        if ($PID != NULL) {

            $url2 = Yii::app()->params->gameapi[$serviceID - 1];
            $certpath = Yii::app()->params->rtg_certkey_dir . $serviceID . '/cert.pem';
            $keypath = Yii::app()->params->rtg_certkey_dir . $serviceID . '/key.pem';
            $rtgplayer = new RealtimeGamingCasinoGamesAPI($url2, $certpath, $keypath, '');

            $result = $rtgplayer->GetPendingGamesByPID($PID);

            if (!empty($result)) {
                if (!is_null(!empty($result['GetPendingGamesByPIDResult']) && $result['GetPendingGamesByPIDResult'] !== null)) {

                    $result = $result['GetPendingGamesByPIDResult'];
                } else {
                    $result = '';
                }
            } else {
                $result = '';
            }
        } else {
            return 'Cant connect to RTG';
        }

        return $result;
    }

    public function getPendingGamesHabanero($Username, $serviceID) {
        Yii::import('application.components.CasinoAPI.HabaneroPlayerAPI');

        $URI = Yii::app()->params->gameapi[$serviceid - 1];
        $brandID = Yii::app()->params['HB_BrandID'];
        $APIkey = Yii::app()->params['HB_APIkey'];
        $currencyCode = Yii::app()->param['HB_CurrencyCode'];
        $habaneroAPI = new HabaneroPlayerAPI($URI, $brandID, $APIkey, $currencyCode);


        $result = $habaneroAPI->GetPendingGamesHabanero($PID);

        if (!empty($result)) {
            if (is_array($result)) {
                if ($result['getgamesmethodResult'] != null) {

                    $result = $result['getgamesmethodResult'];
                } else {
                    $result = '';
                }
            } else {
                $result = '';
            }
        } else {
            return 'Cant connect to RTG';
        }

        return $result;
    }

}

?>
