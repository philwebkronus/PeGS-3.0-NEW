<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

App::LoadModuleClass("CasinoProvider", "CasinoAPIHandler");
App::LoadModuleClass("CasinoProvider", "PlayTechAPIWrapper");
App::LoadModuleClass("CasinoProvider", "MicrogamingCAPIWrapper");
App::LoadModuleClass("CasinoProvider", "RealtimeGamingUBAPIWrapper");
App::LoadModuleClass("CasinoProvider", "RealtimeGamingAPIWrapper");
App::LoadModuleClass("CasinoProvider", "HabaneroAPIWrapper");
App::LoadModuleClass("CasinoProvider", "Array2XML");
App::LoadModuleClass("CasinoProvider", "checkhost");
App::LoadModuleClass("CasinoProvider", "common");

Class CasinoAPI {

    public function configureRTG($serverID, $isPlayerAPI) {
        $_Log = new AuditTrail();
        $playerapi = App::getParam('player_api');
        $lobbyapi = App::getParam('lobby_api');
        $gameapi = App::getParam('game_api');
        $serviceapi = App::getParam('service_api');
        //var_dump($isPlayerAPI);exit;
        if ($isPlayerAPI == 1) {

            $config = array('URI' => $playerapi[$serverID - 1],
                'URI_PID' => $lobbyapi[$serverID - 1],
                'APIType' => $isPlayerAPI,
                'isCaching' => FALSE,
                'isDebug' => TRUE,
                'certFilePath' => App::getParam('rtg_cert_dir') . $serverID . '/cert-key.pem',
                'keyFilePath' => App::getParam('rtg_cert_dir') . $serverID . '/cert-key.pem',);
        } elseif ($isPlayerAPI == 2) {

            $config = array('URI' => $playerapi[$serverID - 1],
                'URI_PID' => $lobbyapi[$serverID - 1],
                'isCaching' => FALSE,
                'APIType' => $isPlayerAPI,
                'isDebug' => TRUE,
                'certFilePath' => App::getParam('rtg_cert_dir') . $serverID . '/cert.pem',
                'keyFilePath' => App::getParam('rtg_cert_dir') . $serverID . '/key.pem',
                'depositMethodId' => App::getParam('deposit_method_id'),
                'withdrawalMethodId' => App::getParam('withdrawal_method_id'));
        } else {

            if (strpos($playerapi[$serverID - 1], 'ECFTEST') !== FALSE) {
                $deposit_method_id = 502;
                $withdrawal_method_id = 503;
            } elseif (strpos($playerapi[$serverID - 1], 'ECFDEMO') !== FALSE) {
                $deposit_method_id = 503;
                $withdrawal_method_id = 502;
            } else {
                $deposit_method_id = 503;
                $withdrawal_method_id = 502;
            }

            $config = array('URI' => $serviceapi[$serverID - 1],
                'URI_PID' => $gameapi[$serverID - 1],
                'isCaching' => FALSE,
                'APIType' => $isPlayerAPI,
                'isDebug' => TRUE,
                'certFilePath' => App::getParam('rtg_cert_dir') . $serverID . '/cert.pem',
                'keyFilePath' => App::getParam('rtg_cert_dir') . $serverID . '/key.pem',
                'depositMethodId' => $deposit_method_id,
                'withdrawalMethodId' => $withdrawal_method_id);
        }

        $_CasinoAPIHandler = new CasinoAPIHandler(CasinoAPIHandler::RTG, $config);

        if (file_exists($config['certFilePath']) == false) {
            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, 'Casino:Connection Failed ' . 'Invalid Certificate directory'); //logging of API Error
            return false;
        }

        if (file_exists($config['keyFilePath']) == false) {
            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, 'Casino:Connection Failed ' . 'Invalid Certificate directory'); //logging of API Error
            return false;
        }

        // check if connected
        if (!(bool) $_CasinoAPIHandler->IsAPIServerOK()) {
            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, 'Casino:Connection Failed ' . 'Can\'t connect to RTG'); //logging of API Error
            return false;
        } else
            return $_CasinoAPIHandler;
    }

    public function configureHabanero($serverID) {
        $_Log = new AuditTrail();
        $playerapi = App::getParam('player_api');
        $lobbyapi = App::getParam('lobby_api');
        $gameapi = App::getParam('game_api');
        $_habaneroUrl = $playerapi[$serverID - 1];
        $config = array('URI' => $_habaneroUrl,
            'isCaching' => FALSE,
            'isDebug' => TRUE,
            'brandID' => App::getParam('HB_BrandID'),
            'APIkey' => App::getParam('HB_APIkey'),
            'currencyCode' => App::getParam('HB_CurrencyCode'),
            'serverID' => $serverID,
            'APIType' => 0);

        $_CasinoAPIHandler = new CasinoAPIHandler(CasinoAPIHandler::HAB, $config);

        // check if connected
        if (!(bool) $_CasinoAPIHandler->IsAPIServerOK()) {
            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, 'Casino:Connection Failed ' . 'Can\'t connect to Habanero'); //logging of API Error
            return false;
        } else
            return $_CasinoAPIHandler;
    }

    public function getAllData($casinoName) {
        if (strpos($casinoName, 'RTG') !== false) {
            $config = array('aid' => App::getParam('AID'),
                'currency' => '',
                'password' => App::getParam('password'),
                'termCode' => App::getParam('termcode_prefix'),
                'casinoID' => App::getParam('casinoID'),
                'userID' => App::getParam('userID'),
                'downloadID' => App::getParam('downloadID'),
                'clientID' => App::getParam('clientID'),
                'putInAffPID' => App::getParam('putInAffPID'),
                'calledFromCasino' => App::getParam('calledFromCasino'),
                'currentPosition' => App::getParam('currentPosition'),
                'country' => App::getParam('country'));
        }

        return $config;
    }

    public function getBalance($casinoName, $terminal_name) {
        switch ($casinoName) {
            case 'RTG - ECF Test':
                $serverID = 7;
                $isPlayerAPI = 0;
                $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
                break;
            case 'RTG - ECF Demo':
                $serverID = 13;
                $isPlayerAPI = 0;
                $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
                break;
            default :
                $casinoAPIHandler = "Invalid Casino Name.";
        }
        $balanceInfo = $casinoAPIHandler->GetBalance($terminal_name);
        return $balanceInfo;
    }

    public static function throwError($message) {
        header("HTTP/1.0 404 Not Found");
        echo $message;
    }

    public function createAccount($casinoName, $serverID, $user, $password, $fname, 
    	$lname, $bdate, $gender, $email, $phone1, $address1, $city, $zipcode, $viplevel) {
        $configuration = $this->getAllData($casinoName);
        $address2 = '';
        $phone2 = '123-456';
        $fax = '';
        $occupation = '';
        $alias = '';
        $ip = '';
        $mac = '';
        $province = '';
        $agentID = '';
        $thirdPartyPID = '';
        $hashedPassword = strtoupper(sha1($password));


        if (strpos($casinoName, 'RTG2') !== false) {
            $isPlayerAPI = 1;
            $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
        }

        if (!$casinoAPIHandler) {
            $response = false;
        } else {
            try {
                $response = $casinoAPIHandler->CreateNewAccount($user, $password, $configuration['aid'], $configuration['currency'], $email, $fname, $lname, $phone1, $phone2, $address1, $address2, $city, $configuration['country'], $province, $zipcode, $configuration['userID'], $bdate, $fax, $occupation, $gender, $alias, $configuration['casinoID'], $ip, $mac, $configuration['downloadID'], $configuration['clientID'], $configuration['putInAffPID'], $configuration['calledFromCasino'], $hashedPassword, $agentID, $configuration['currentPosition'], $thirdPartyPID, $viplevel);
            } catch (Exception $ex) {
                $response = $ex;
            }
        }

        return $response;
    }

    public function habaneroCreateAccount($casinoName, $serverID, $Username, $Password, $PlayerRank) {

        if (strpos($casinoName, 'HAB') !== false) {
            $casinoAPIHandler = $this->configureHabanero($serverID);
        }

        if (!$casinoAPIHandler) {
            $response = false;
        } else {
            try {
                $response = $casinoAPIHandler->HabaneroCreateNewAccount($Username, $Password, $PlayerRank);
            } catch (Exception $ex) {
                $response = $ex;
            }
        }

        return $response;
    }

    public function GetAccountInfo($casinoName, $user, $password, $serverID) {
        if (strpos($casinoName, 'RTG') !== false) {
            $isPlayerAPI = 0;
            $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
        }
        if (strpos($casinoName, 'HAB') !== false) {
            $casinoAPIHandler = $this->configureHabanero($serverID);
        }

        return $casinoAPIHandler->GetAccountInfo($user, $password);
    }

    public function ChangePassword($casinoName, $user, $oldpassword, $newpassword, $serverID) {
        if (strpos($casinoName, 'RTG2') !== false) {
            $isPlayerAPI = 1;
            $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
        }
        if (strpos($casinoName, 'HAB') !== false) {
            $casinoAPIHandler = $this->configureHabanero($serverID);
        }
        if (!$casinoAPIHandler) {
            $response = false;
        } else {
            $response = $casinoAPIHandler->ChangePassword($serverID, $user, $oldpassword, $newpassword);
        }

        return $response;
    }

    public function ChangePlayerClassification($casinoName, $pid, $playerClassID, $userID, $serverID) {
        if (strpos($casinoName, 'RTG2') !== false) {
            $isPlayerAPI = 1;
            $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
        }

        if (!$casinoAPIHandler) {
            $response = false;
        } else {
            $response = $casinoAPIHandler->ChangePlayerClassification($pid, $playerClassID, $userID);
        }

        return $response;
    }

    public function GetPlayerClassification($casinoName, $pid, $serverID) {
        if (strpos($casinoName, 'RTG2') !== false) {
            $isPlayerAPI = 1;
            $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
        }

        if (!$casinoAPIHandler) {
            $response = false;
        } else {
            $response = $casinoAPIHandler->GetPlayerClassification($pid);
        }

        return $response;
    }

    public function GetPendingGames($user) {
        $serverID = 13;
        $isPlayerAPI = 2;
        $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);

        $response = $casinoAPIHandler->GetPendingGames($user);
        return $response;
    }

    public function RevertBrokenGamesAPI($username) {
        $serverID = 8;
        $isRevert = 1; //0-No, 1-Yes
        $array = $this->configurePT($serverID, $isRevert);
        $casinoAPIHandler = $array['casinoAPIHandler'];
        $game_mode = $array['game_mode'];
        $player_mode = $array['player_mode'];
        $response = $casinoAPIHandler->RevertBrokenGamesAPI($username, $player_mode, $game_mode);
        return $response;
    }

    public function GetTransactionInfo($casinoName, $serverID, $ticketID, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '', $username = '') {
        if (strpos($casinoName, 'RTG') !== false) {
            $casino = "RTG";
        }

        switch ($casino) {
            case 'RTG':
                $isPlayerAPI = 0;
                $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
                return $casinoAPIHandler->TransactionSearchInfo($username, $tracking1, $tracking2, $tracking3, $tracking4, $ticketID);
        }
    }

    /**
     * Description: this will return date with milliseconds
     * @param string $format date format to be return
     * @param string $utimestamp
     * @return string date 
     */
    public static function udate($format, $utimestamp = null) {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }

}

?>
