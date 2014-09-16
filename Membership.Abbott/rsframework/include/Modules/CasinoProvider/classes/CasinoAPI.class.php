<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//

    App::LoadModuleClass("CasinoProvider", "CasinoAPIHandler");
    App::LoadModuleClass("CasinoProvider", "PlayTechAPIWrapper");
    App::LoadModuleClass("CasinoProvider", "MicrogamingCAPIWrapper");
    App::LoadModuleClass("CasinoProvider", "RealtimeGamingUBAPIWrapper");
    App::LoadModuleClass("CasinoProvider", "RealtimeGamingAPIWrapper");
    App::LoadModuleClass("CasinoProvider", "Array2XML");
    App::LoadModuleClass("CasinoProvider", "checkhost");
    App::LoadModuleClass("CasinoProvider", "common");
    
    Class CasinoAPI
    {

        public function configureRTG($serverID, $isPlayerAPI)
        {
            $_Log = new AuditTrail();
            $playerapi = App::getParam('player_api');
            $lobbyapi = App::getParam('lobby_api');
            $gameapi = App::getParam('game_api');
            $serviceapi = App::getParam('service_api');
            //var_dump($isPlayerAPI);exit;
            if($isPlayerAPI == 1){
                
                $config = array( 'URI' => $playerapi[$serverID - 1],
                    'URI_PID' => $lobbyapi[$serverID - 1],
                    'APIType' => $isPlayerAPI,
                    'isCaching' => FALSE,
                    'isDebug' => TRUE,
                    'certFilePath' => App::getParam('rtg_cert_dir').$serverID.'/cert-key.pem',
                    'keyFilePath' => App::getParam('rtg_cert_dir').$serverID.'/cert-key.pem', );
                
            } elseif($isPlayerAPI == 2){

                    $config = array( 'URI' => $playerapi[$serverID - 1],
                         'URI_PID' => $lobbyapi[$serverID - 1],
                        'isCaching' => FALSE,
                        'APIType' => $isPlayerAPI,
                        'isDebug' => TRUE,
                        'certFilePath' =>App::getParam('rtg_cert_dir').$serverID.'/cert.pem',
                        'keyFilePath' => App::getParam('rtg_cert_dir').$serverID.'/key.pem',
                        'depositMethodId' => App::getParam('deposit_method_id'),
                        'withdrawalMethodId' => App::getParam('withdrawal_method_id'));
                
            }
            else {
                
                if(strpos($playerapi[$serverID - 1], 'ECFTEST') !== FALSE)
                {
                    $deposit_method_id = 502;
                    $withdrawal_method_id = 503;
                } elseif(strpos($playerapi[$serverID - 1], 'ECFDEMO') !== FALSE) {
                    $deposit_method_id = 503;
                    $withdrawal_method_id = 502;    
                } else {
                    $deposit_method_id = 503;
                    $withdrawal_method_id = 502;    
                }

                $config = array( 'URI' => $serviceapi[$serverID - 1],
                     'URI_PID' => $gameapi[$serverID - 1],
                    'isCaching' => FALSE,
                    'APIType' => $isPlayerAPI,
                    'isDebug' => TRUE,
                    'certFilePath' =>App::getParam('rtg_cert_dir').$serverID.'/cert.pem',
                    'keyFilePath' => App::getParam('rtg_cert_dir').$serverID.'/key.pem',
                    'depositMethodId' => $deposit_method_id,
                    'withdrawalMethodId' => $withdrawal_method_id);
            }
            
            $_CasinoAPIHandler = new CasinoAPIHandler(CasinoAPIHandler::RTG, $config);
            
            if(file_exists($config['certFilePath']) == false){
                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP,'Casino:Connection Failed '.'Invalid Certificate directory'); //logging of API Error
                return false;
            }
            
            if(file_exists($config['keyFilePath']) == false){
                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP,'Casino:Connection Failed '.'Invalid Certificate directory'); //logging of API Error
                return false;
            }
            
             // check if connected
            if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP,'Casino:Connection Failed '.'Can\'t connect to RTG'); //logging of API Error
                return false;
            }
            else
                return $_CasinoAPIHandler;
        }
        
        
        public function configureMg($serverID)
        {
            $_Log = new AuditTrail();
            $playerapi = App::getParam('player_api');
            $lobbyapi = App::getParam('lobby_api');
            $gameapi = App::getParam('game_api');
            $_MGCredentials =  $playerapi[$serverID-1];
            list($mgurl, $mgserverID) = $_MGCredentials;
            $config = array('URI' => $mgurl,
                                'isCaching' =>FALSE,
                                'isDebug' => TRUE,
                                'authLogin' =>  App::getParam('mgcapi_username'),
                                'authPassword' => App::getParam('mgcapi_password'),
                                'playerName' =>  App::getParam('mgcapi_playername'),
                                'serverID' => $mgserverID);
            
            $_CasinoAPIHandler = new CasinoAPIHandler(CasinoAPIHandler::MG, $config);
            
             // check if connected
            if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP,'Casino:Connection Failed '.'Can\'t connect to MG'); //logging of API Error
                return false;
            }
            else
                return $_CasinoAPIHandler;
        }
        
        
        public function configurePT($serverID,$isRevert)
        {
            $_Log = new AuditTrail();
            $playerapi = App::getParam('player_api');
            $revertbrokenapi = App::getParam('revertbroken_api');
            
            if($isRevert == 0){
            $config = array('URI' => $playerapi[$serverID-1],
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                'authLogin' => App::getParam('pt_casino_name'),
                                'secretKey' => App::getParam('pt_secret_key') );
            $_CasinoAPIHandler = new CasinoAPIHandler(CasinoAPIHandler::PT, $config);
            
            if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP,'Casino:Connection Failed '.'Can\'t connect to PT'); //logging of API Error
                return false;
            }
            else{
                return $_CasinoAPIHandler;
            }
            
            } else {
                $config = array('URI' => $revertbrokenapi['URI'],
                                'isCaching' =>FALSE,
                                'isDebug' => TRUE,
                                'REVERT_BROKEN_GAME_MODE' => $revertbrokenapi['REVERT_BROKEN_GAME_MODE'],
                                'CASINO_NAME' => $revertbrokenapi['CASINO_NAME'],
                                "PLAYER_MODE" => $revertbrokenapi['PLAYER_MODE'],
                                'certFilePath' => App::getParam('rtg_cert_dir').$serverID.'/cert.pem',
                                'keyFilePath' => App::getParam('rtg_cert_dir').$serverID.'/key.pem' );
                $_CasinoAPIHandler = new CasinoAPIHandler(CasinoAPIHandler::PT, $config);
                
                if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
                    return false;
                }
                else{
                    return array('casinoAPIHandler' => $_CasinoAPIHandler,
                                    'game_mode' => $revertbrokenapi['REVERT_BROKEN_GAME_MODE'],
                                    'player_mode' => $revertbrokenapi['PLAYER_MODE']);
                }
            
            }
        }
        
        public function getAllData($casinoName)
        {   
             if(strpos($casinoName, 'RTG') !== false){
                 $config = array( 'aid' => App::getParam('AID'),
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
             if(strpos($casinoName, 'MG') !== false){
                 $config = array('aid' => App::getParam('AID'),
                                'password' => App::getParam('password'),
                                'termCode' => App::getParam('termcode_prefix'),
                                'userID' => App::getParam('userID'),
                                'currency' => App::getParam('microgaming_currency'),
                                'country' => App::getParam('country'));
                 
             }
             if(strpos($casinoName, 'PT') !== false){
                 $config = array('aid' => App::getParam('AID'),
                                'URI' => App::getParam('rr_URI'),
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                'userID' => '',
                                'casinoID' => '',
                                'downloadID' => '',
                                'clientID' => '',
                                'putInAffPID' => '',
                                'calledFromCasino' => '',
                                'currentPosition' => '',
                                'currency' => App::getParam('currency'),
                                'authLogin' => App::getParam('pt_casino_name'),
                                'secretKey' => App::getParam('pt_secret_key'),
                                'password' => App::getParam('rr_password'),
                                'country' => App::getParam('country'));
                 
             }
            
            return $config;
        }

        public  function getBalance($casinoName,$terminal_name)
        {
            switch ($casinoName){
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
                case 'MG - Test':
                    $serverID = 15;
                    $casinoAPIHandler = $this->configureMg($serverID);
                    break;
                case 'PT':
                    $serverID = 8;
                    $isRevert = 0; //0-No, 1-Yes
                    $casinoAPIHandler = $this->configurePT($serverID,$isRevert);
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
        
        public function createAccount($casinoName,$serverID, $user,$password, $fname,
                $lname,$bdate, $gender,$email,$phone1,$address1,$city,$zipcode,$viplevel)
        {
            $configuration = $this->getAllData($casinoName);
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
            if(strpos($casinoName, 'RTG2') !== false){
                $isPlayerAPI = 1;
                $casinoAPIHandler = $this->configureRTG($serverID,$isPlayerAPI);
            } 
            if(strpos($casinoName, 'MG') !== false){
                $casinoAPIHandler = $this->configureMg($serverID);
            }
            if(strpos($casinoName, 'PT') !== false){
                $isRevert = 0; //0-No, 1-Yes
                $casinoAPIHandler = $this->configurePT($serverID,$isRevert);
            }
            
            if(!$casinoAPIHandler){
                $response = false;
            }
            else{
                $response = $casinoAPIHandler->CreateNewAccount($user, $password, $configuration['aid'], $configuration['currency'], 
                                                                            $email, $fname, $lname, $phone1, $phone2, $address1, $address2, $city, 
                                                                            $configuration['country'], $province, $zipcode, $configuration['userID'], $bdate, $fax, $occupation, 
                                                                            $gender, $alias, $configuration['casinoID'], $ip, $mac, $configuration['downloadID'], $configuration['clientID'], 
                                                                            $configuration['putInAffPID'], $configuration['calledFromCasino'], $hashedPassword, $agentID, 
                                                                            $configuration['currentPosition'], $thirdPartyPID, $viplevel);
            }
                return $response;
        }
        
        public function GetAccountInfo($casinoName, $user, $password, $serverID)
        {
            if(strpos($casinoName, 'RTG') !== false){
                $isPlayerAPI = 0;
                $casinoAPIHandler = $this->configureRTG($serverID,$isPlayerAPI);
            } 
            if(strpos($casinoName, 'MG') !== false){
                $casinoAPIHandler = $this->configureMg($serverID);
            }
            if(strpos($casinoName, 'PT') !== false){
                $isRevert = 0; //0-No, 1-Yes
                $casinoAPIHandler = $this->configurePT($serverID,$isRevert);
            }
            
            return $casinoAPIHandler->GetAccountInfo($user, $password); 
        }
        
        
        public function ChangePassword($casinoName, $user, $oldpassword, $newpassword, $serverID)
        {
            if(strpos($casinoName, 'RTG2') !== false){
                $isPlayerAPI = 1;
                $casinoAPIHandler = $this->configureRTG($serverID,$isPlayerAPI);
            } 
            if(strpos($casinoName, 'MG') !== false){
                $casinoAPIHandler = $this->configureMg($serverID);
            }
            if(strpos($casinoName, 'PT') !== false){
                $isRevert = 0; //0-No, 1-Yes
                $casinoAPIHandler = $this->configurePT($serverID,$isRevert);
            }
            if(!$casinoAPIHandler){
                $response = false;
            }
            else{
                $response = $casinoAPIHandler->ChangePassword($serverID, $user, $oldpassword, $newpassword);
            }
            
            return $response;
        }
        
        public function ChangePlayerClassification($casinoName, $pid, $playerClassID, $userID, $serverID)
        {
            if(strpos($casinoName, 'RTG2') !== false){
                $isPlayerAPI = 1;
                $casinoAPIHandler = $this->configureRTG($serverID,$isPlayerAPI);
            }
            
            if(!$casinoAPIHandler){
                $response = false;
            }
            else{
                $response = $casinoAPIHandler->ChangePlayerClassification($pid, $playerClassID, $userID);
            }
            
            return $response;
        }
        
        public function GetPlayerClassification($casinoName, $pid, $serverID)
        {
            if(strpos($casinoName, 'RTG2') !== false){
                $isPlayerAPI = 1;
                $casinoAPIHandler = $this->configureRTG($serverID,$isPlayerAPI);
            }
            
            if(!$casinoAPIHandler){
                $response = false;
            }
            else{
                $response = $casinoAPIHandler->GetPlayerClassification($pid);
            }
            
            return $response;
        }
        
        
        public function GetPendingGames($user)
        {
            $serverID = 13;
            $isPlayerAPI = 2;
            $casinoAPIHandler = $this->configureRTG($serverID, $isPlayerAPI);
            
            $response = $casinoAPIHandler->GetPendingGames($user);
            return $response;
        }
        
        public function RevertBrokenGamesAPI($username)
        {
            $serverID = 8;
            $isRevert = 1; //0-No, 1-Yes
            $array = $this->configurePT($serverID, $isRevert);
            $casinoAPIHandler = $array['casinoAPIHandler'];
            $game_mode = $array['game_mode'];
            $player_mode = $array['player_mode'];
            $response = $casinoAPIHandler->RevertBrokenGamesAPI($username, $player_mode, $game_mode);
            return $response;
        }
        
        public function GetTransactionInfo($casinoName, $serverID, $ticketID, $tracking1 = '',$tracking2 = '',$tracking3 = '',$tracking4 = '', $username = '')
        {
            if(strpos($casinoName, 'RTG') !== false){
                $casino = "RTG";
            } else if(strpos($casinoName, 'MG') !== false) {
                $casino = "MG";
            } else {
                $casino = "PT";
            }
            switch ($casino){
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
                    $casinoAPIHandler = $this->configurePT($serverID,$isRevert);
                    return $casinoAPIHandler->TransactionSearchInfo($username, $tracking1, $tracking2, $tracking3, $tracking4, $ticketID);
                    break;
            }
        }
        
    }

?>
