<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//

Class CasinoAPI
{

    public function configureRTG($serverID, $isPlayerAPI)
    {
        Yii::import('application.components.CasinoAPIHandler');
//        $_Log = new AuditTrail();
        $playerapi = Yii::app()->params['player_api'];
        $lobbyapi = Yii::app()->params['lobby_api'];
        $gameapi = Yii::app()->params['game_api'];
        $serviceapi = Yii::app()->params['service_api'];
        //var_dump($isPlayerAPI);exit;
        if ($isPlayerAPI == 1)
        {

            $config = array('URI' => $playerapi[$serverID - 1],
                'URI_PID' => $lobbyapi[$serverID - 1],
                'APIType' => $isPlayerAPI,
                'isCaching' => FALSE,
                'isDebug' => TRUE,
                'certFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverID . '/cert-key.pem',
                'keyFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverID . '/cert-key.pem',);
        }
        elseif ($isPlayerAPI == 2)
        {

            $config = array('URI' => $playerapi[$serverID - 1],
                'URI_PID' => $lobbyapi[$serverID - 1],
                'isCaching' => FALSE,
                'APIType' => $isPlayerAPI,
                'isDebug' => TRUE,
                'certFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverID . '/cert.pem',
                'keyFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverID . '/key.pem',
                'depositMethodId' => Yii::app()->params['deposit_method_id'],
                'withdrawalMethodId' => Yii::app()->params['withdrawal_method_id']);
        }
        else
        {

            if (strpos($playerapi[$serverID - 1], 'ECFTEST') !== FALSE)
            {
                $deposit_method_id = 502;
                $withdrawal_method_id = 503;
            }
            elseif (strpos($playerapi[$serverID - 1], 'ECFDEMO') !== FALSE)
            {
                $deposit_method_id = 503;
                $withdrawal_method_id = 502;
            }
            else
            {
                $deposit_method_id = 503;
                $withdrawal_method_id = 502;
            }

            $config = array('URI' => $serviceapi[$serverID - 1],
                'URI_PID' => $gameapi[$serverID - 1],
                'isCaching' => FALSE,
                'APIType' => $isPlayerAPI,
                'isDebug' => TRUE,
                'certFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverID . '/cert.pem',
                'keyFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverID . '/key.pem',
                'depositMethodId' => $deposit_method_id,
                'withdrawalMethodId' => $withdrawal_method_id);
        }

        $_CasinoAPIHandler = new CasinoAPIHandler(CasinoAPIHandler::RTG, $config);
        if (file_exists($config['certFilePath']) == false)
        {
//            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, 'Casino:Connection Failed ' . 'Invalid Certificate directory'); //logging of API Error
            return false;
        }

        if (file_exists($config['keyFilePath']) == false)
        {
//            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, 'Casino:Connection Failed ' . 'Invalid Certificate directory'); //logging of API Error
            return false;
        }

        // check if connected
        if (!(bool) $_CasinoAPIHandler->IsAPIServerOK())
        {
//            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, 'Casino:Connection Failed ' . 'Can\'t connect to RTG'); //logging of API Error
            return false;
        }
        else
            return $config;
    }

    public static function getAllData($casinoName)
    {
        if (strpos($casinoName, 'RTG') !== false)
        {
            $config = array('aid' => Yii::app()->params['AID'],
                'currency' => '',
                'password' => Yii::app()->params['password'],
                'termCode' => Yii::app()->params['termcode_prefix'],
                'casinoID' => Yii::app()->params['casinoID'],
                'userID' => Yii::app()->params['userID'],
                'downloadID' => Yii::app()->params['downloadID'],
                'clientID' => Yii::app()->params['clientID'],
                'putInAffPID' => Yii::app()->params['putInAffPID'],
                'calledFromCasino' => Yii::app()->params['calledFromCasino'],
                'currentPosition' => Yii::app()->params['currentPosition'],
                'country' => Yii::app()->params['country']);
        }

        return $config;
    }

    public function getBalance($casinoName, $terminal_name)
    {
        Yii::import('application.components.CasinoAPIHandler');
        switch ($casinoName)
        {
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

    public function throwError($message)
    {
        header("HTTP/1.0 404 Not Found");
        echo $message;
    }

    public static function createAccount($casinoName, $serverID, $user, $password, $fname, $lname, $bdate, $gender, $email, $phone1, $address1, $city, $zipcode, $viplevel)
    {
        Yii::import('application.components.CasinoAPIHandler');
        $data = new CasinoAPI;
        $configuration = $data->getAllData($casinoName);
        $address2 = '';
        $phone2 = '';
        $fax = '';
        $occupation = '';
        $alias = '';
        $ip = '';
        $mac = '';
        $province = '';
        $agentID = '';
        $thirdPartyPID = '';
        $hashedPassword = strtoupper(sha1($password));


        if (strpos($casinoName, 'RTG2') !== false)
        {
            $isPlayerAPI = 1;
            $casinoAPIHandler = $data->configureRTG($serverID, $isPlayerAPI);
        }

        if (!$casinoAPIHandler)
        {
            $response = false;
        }
        else
        {
            try
            {
                $response = CasinoAPIHandler::CreateNewAccount($casinoAPIHandler, $user, $password, $configuration['aid'], $configuration['currency'], $email, $fname, $lname, $phone1, $phone2, $address1, $address2, $city, $configuration['country'], $province, $zipcode, $configuration['userID'], $bdate, $fax, $occupation, $gender, $alias, $configuration['casinoID'], $ip, $mac, $configuration['downloadID'], $configuration['clientID'], $configuration['putInAffPID'], $configuration['calledFromCasino'], $hashedPassword, $agentID, $configuration['currentPosition'], $thirdPartyPID, $viplevel);
           
            }
            catch (Exception $ex)
            {
                $response = $ex;
            }
        }

        return $response;
    }

    public function GetAccountInfo($casinoName, $user, $password, $serverID)
    {
        Yii::import('application.components.CasinoAPIHandler');
        if (strpos($casinoName, 'RTG') !== false)
        {
            $isPlayerAPI = 0;
            $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
        }

        return $casinoAPIHandler->GetAccountInfo($user, $password);
    }

    public function ChangePassword($casinoName, $user, $oldpassword, $newpassword, $serverID)
    {
        Yii::import('application.components.CasinoAPIHandler');
        if (strpos($casinoName, 'RTG2') !== false)
        {
            $isPlayerAPI = 1;
            $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
        }
        if (!$casinoAPIHandler)
        {
            $response = false;
        }
        else
        {
            $response = $casinoAPIHandler->ChangePassword($serverID, $user, $oldpassword, $newpassword);
        }

        return $response;
    }

    public function ChangePlayerClassification($casinoName, $pid, $playerClassID, $userID, $serverID)
    {
        Yii::import('application.components.CasinoAPIHandler');
        if (strpos($casinoName, 'RTG2') !== false)
        {
            $isPlayerAPI = 1;
            $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
        }

        if (!$casinoAPIHandler)
        {
            $response = false;
        }
        else
        {
            $response = $casinoAPIHandler->ChangePlayerClassification($pid, $playerClassID, $userID);
        }

        return $response;
    }

    public function GetPlayerClassification($casinoName, $pid, $serverID)
    {
        Yii::import('application.components.CasinoAPIHandler');
        if (strpos($casinoName, 'RTG2') !== false)
        {
            $isPlayerAPI = 1;
            $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
        }

        if (!$casinoAPIHandler)
        {
            $response = false;
        }
        else
        {
            $response = $casinoAPIHandler->GetPlayerClassification($pid);
        }

        return $response;
    }

    public function GetPendingGames($user)
    {
        Yii::import('application.components.CasinoAPIHandler');
        $serverID = 13;
        $isPlayerAPI = 2;
        $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);

        $response = $casinoAPIHandler->GetPendingGames($user);
        return $response;
    }

    public function RevertBrokenGamesAPI($username)
    {
        Yii::import('application.components.CasinoAPIHandler');
        $serverID = 8;
        $isRevert = 1; //0-No, 1-Yes
        $array = $this->configurePT($serverID, $isRevert);
        $casinoAPIHandler = $array['casinoAPIHandler'];
        $game_mode = $array['game_mode'];
        $player_mode = $array['player_mode'];
        $response = $casinoAPIHandler->RevertBrokenGamesAPI($username, $player_mode, $game_mode);
        return $response;
    }

    public function GetTransactionInfo($casinoName, $serverID, $ticketID, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '', $username = '')
    {
        Yii::import('application.components.CasinoAPIHandler');
        if (strpos($casinoName, 'RTG') !== false)
        {
            $casino = "RTG";
        }
        else if (strpos($casinoName, 'MG') !== false)
        {
            $casino = "MG";
        }
        else
        {
            $casino = "PT";
        }
        switch ($casino)
        {
            case 'RTG':
                $isPlayerAPI = 0;
                $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
                return $casinoAPIHandler->TransactionSearchInfo($username, $tracking1, $tracking2, $tracking3, $tracking4, $ticketID);
            case 'MG':
                $casinoAPIHandler = $this->configureMg($serverID);
                return $casinoAPIHandler->TransactionSearchInfo($username, $tracking1, $tracking2, $tracking3, $tracking4, $ticketID);
                break;
            case 'PT':
                $isRevert = 0; //0-No, 1-Yes
                $casinoAPIHandler = $this->configurePT($serverID, $isRevert);
                return $casinoAPIHandler->TransactionSearchInfo($username, $tracking1, $tracking2, $tracking3, $tracking4, $ticketID);
                break;
        }
    }

    /**
     * Description: this will return date with milliseconds
     * @param string $format date format to be return
     * @param string $utimestamp
     * @return string date 
     */
    public function udate($format, $utimestamp = null)
    {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }

}

?>
