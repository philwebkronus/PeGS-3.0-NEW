<?php
/**
 * Webservice for EGM (Electronic Gaming Terminal)
 * @author elperez
 * @date 09/26/12
 */

class WsGamingController extends Controller{
    
    /**
     * This method authenticates if a certain machine Id is valid | registered
     * @param str machineId
     * @param str terminalcode
     * @return json
     */
    public function actionAuthenticateClient(){
       $token = '';
       if(isset($_GET['machineId']) && $_GET['machineId'] != '')
       {
           if(isset($_GET['terminalCode']) && $_GET['terminalCode'] != '')
           {
               $machineId = htmlentities($_GET['machineId']);
               $terminalCode = htmlentities($_GET['terminalCode']);
               
               if (Utilities::validateInput($machineId) && Utilities::validateInput($terminalCode))
               {
                   $gamingMachineModel = new GamingMachineModel();
                   $sitesModel = new SitesModel();

                   $machine = $gamingMachineModel->getMachineInfo(trim($machineId), trim($terminalCode));
                   
                   $terminalID = '';
                   
                   //verify if gaming terminal was registered or valid
                   if(isset($machine['EGMMachineInfoId_PK']) && $machine['EGMMachineInfoId_PK'] != '')
                   {
                       $siteID = $machine['SiteID'];
                       $AID = $machine['CreatedByAID'];
                       $machineInfoId = $machine['EGMMachineInfoId_PK'];
                       $terminalID = $machine['TerminalID'];
                       $terminalIDVIP = $machine['TerminalIDVIP'];
                       $token = $machine['Token'];
                       $terminalStatus = $machine['tstatus']; //PEGS Terminal Status
                       $egmStatus = $machine['egmstatus']; //EGM Machine Status
                       
                       $isSiteActive = $sitesModel->checkIfActiveSite($siteID);

                       //Check if site is deactivated
                       if(!$isSiteActive){
                           $message = "Inactive Site.";
                           Utilities::log($message);
                           $this->_sendResponse(200, CJSON::encode(array('AuthenticateClient'=>(array('Token'=>'',
                                                                            'AID'=>'','Terminals'=>'','ErrorCode'=>56,
                                                                            'ErrorMessage'=>$message))))); 
                           exit;
                       }
                       
                       //Check if PEGS Terminal is inactive
                       if((int)$terminalStatus != 1){
                           $message = "Inactive PEGS Terminal.";
                           Utilities::log($message);
                           $this->_sendResponse(200, CJSON::encode(array('AuthenticateClient'=>(array('Token'=>'',
                                                                            'AID'=>'','Terminals'=>'','ErrorCode'=>56,
                                                                            'ErrorMessage'=>$message))))); 
                           exit;
                       }
                       
                       //Check if EGM Terminal is inactive
                       if((int)$egmStatus != 1){
                           $message = "Inactive EGM Terminal.";
                           Utilities::log($message);
                           $this->_sendResponse(200, CJSON::encode(array('AuthenticateClient'=>(array('Token'=>'',
                                                                            'AID'=>'','Terminals'=>'','ErrorCode'=>56,'ErrorMessage'=>$message))))); 
                           exit;
                       }
                       
                       $terminals = array("TerminalID"=>$terminalID,"TerminalIDVip"=>$terminalIDVIP);
                       
                       //check if token was set, then return 
                       if(isset($token) && !is_null($token))
                       {
                           $this->_sendResponse(200, CJSON::encode(array('AuthenticateClient'=>
                                                                          (array('Token'=>$token,'AID'=>(int)$AID,'Terminals'=>$terminals,
                                                                                 'ErrorCode'=>0,'ErrorMessage'=>'Successful')))));   
                       } 
                       else 
                       {
                           $token = $this->generateToken();                      
                           $isAuthenticated = $gamingMachineModel->updateToken($token, $machineId);

                           //return token if it was set
                           if($isAuthenticated)
                               $this->_sendResponse(200, CJSON::encode(array('AuthenticateClient'=>
                                                                                (array('Token'=>$token,'AID'=>(int)$AID,'Terminals'=>$terminals,
                                                                                       'ErrorCode'=>0,'ErrorMessage'=>'Successful')))));            
                           else{
                               $message = 'Failed to update machine token.';
                               Utilities::log($message);
                               $this->_sendResponse(200, CJSON::encode(array('AuthenticateClient'=>(array('Token'=>'',
                                                                             'AID'=>'','Terminals'=>'','ErrorCode'=>64,'ErrorMessage'=>$message)))));
                           }
                       }
                   } 
                   else 
                   {
                       $message = 'Machine ID and Terminal Code did not match';
                       Utilities::log($message);
                       $this->_sendResponse(200, CJSON::encode(array('AuthenticateClient'=>(array('Token'=>'','AID'=>'','Terminals'=>'',
                                                                                                  'ErrorCode'=>66,'ErrorMessage'=>$message)))));    
                   }
               } 
               else
               {
                   $message = 'Parameters contains invalid special characters';
                   Utilities::log($message);
                   $this->_sendResponse(200, CJSON::encode(array('AuthenticateClient'=>(array('Token'=>'','AID'=>'','Terminals'=>'',
                                                                                              'ErrorCode'=>2,'ErrorMessage'=>$message))))); 
               }
           }
           else
           {
               $message = "Terminal Code is not set or blank";
               Utilities::log($message);
               $this->_sendResponse(200, CJSON::encode(array('AuthenticateClient'=>(array('Token'=>'','AID'=>'','Terminals'=>'',
                                                                                          'ErrorCode'=>63,'ErrorMessage'=>$message)))));  
           }
       }
       else
       {
           $message = "MachineID is not set or blank";
           Utilities::log($message);
           $this->_sendResponse(200, CJSON::encode(array('AuthenticateClient'=>(array('Token'=>'','AID'=>'','Terminals'=>'',
                                                                                      'ErrorCode'=>3,'ErrorMessage'=>$message)))));  
       }
    }
    
    /**
     * Checks if Terminal has a active session
     * @param str token
     * @param int isVip
     * @param str terminalCode
     */
    public function actionCheckActiveSession() {
       $egmSession = false;
       $isSessionStarted = false;
       $startSessionDate = '';
       $balance = '';
       
       //Check if parameters are set
       if(isset($_GET['token']) && $_GET['token'] != '') {
           if(isset($_GET['isVip']) && $_GET['isVip'] != '' ) {
               if(isset($_GET['terminalCode']) && $_GET['terminalCode'] != '' ) {
                   
                   $token = htmlentities($_GET['token']);
                   $isVip = htmlentities($_GET['isVip']);
                   $terminalCode = htmlentities($_GET['terminalCode']);
                   
                   if (Utilities::validateInput($token) && Utilities::validateInput($isVip)
                       && Utilities::validateInput($terminalCode))
                   {
                       $terminalSessionsModel = new TerminalSessionsModel();
                       $terminalServiceModel = new TerminalServicesModel();
                       
                       //validate isVip value if numeric
                       if(!is_numeric($isVip)){
                           $message = "Invalid isVip value";
                           Utilities::log($message);
                           $this->_sendResponse(200, CJSON::encode(array('CheckActiveSession'=>(array(
                                        'MappedServices'=>'','ErrorCode'=>71,'ErrorMessage'=>$message))))); 
                           exit;
                       }
                       
                       $terminalID = $this->runCommonValidator($token, $terminalCode, $isVip, $transType = '');
                       
                       //if successfully validated, terminalID result must be numeric
                       if(is_numeric($terminalID)){
                
                           //Get Terminal/ Kronus Session
                           $kronusSession = $terminalSessionsModel->isSessionActive($terminalID);

                           //Get casino/services mapped to a terminal 
                           $services = $terminalServiceModel->getCasinoByTerminal($terminalID);

                           //If terminal session was active, send success response
                           if($kronusSession != 0) {

                                $this->_sendResponse(200, CJSON::encode(array('CheckActiveSession'=>(array(
                                            'MappedServices'=>$services,'ErrorCode'=>0,'ErrorMessage'=>'Successful')))));
                           } else {
                               $message = 'Error: Terminal has no active session.';
                               Utilities::log($message);
                               $this->_sendResponse(200, CJSON::encode(array('CheckActiveSession'=>(array(
                                            'MappedServices'=>$services,'ErrorCode'=>43,'ErrorMessage'=>$message)))));
                           }

                        } else {
                            $message = $terminalID['message'];
                            $errorCode = $terminalID['ErrorCode'];
                            Utilities::log($message);
                            $this->_sendResponse(200, CJSON::encode(array('CheckActiveSession'=>(array(
                                        'MappedServices'=>'','ErrorCode'=>(int)$errorCode,'ErrorMessage'=>$message)))));
                       }
                       
                   } else {
                       $message = 'Parameters contains invalid special characters';
                       Utilities::log($message);
                       $this->_sendResponse(200, CJSON::encode(array('CheckActiveSession'=>(array(
                                        'MappedServices'=>'','ErrorCode'=>9,'ErrorMessage'=>$message)))));
                   }
                   
               } else {
                   $message = 'Terminal Code is not set or blank';
                   Utilities::log($message);
                   $this->_sendResponse(200, CJSON::encode(array('CheckActiveSession'=>(array(
                                        'MappedServices'=>'','ErrorCode'=>63,'ErrorMessage'=>$message)))));
               }
               
           } else {
               $message = 'isVip is not set or blank';
               Utilities::log($message);
               $this->_sendResponse(200, CJSON::encode(array('CheckActiveSession'=>(array(
                                        'MappedServices'=>'','ErrorCode'=>59,'ErrorMessage'=>$message)))));
           }
           
       } else {
           $message = 'Token is not set or blank';
           Utilities::log($message);
           $this->_sendResponse(200, CJSON::encode(array('CheckActiveSession'=>(array(
                            'MappedServices'=>'','ErrorCode'=>6,'ErrorMessage'=>$message)))));
       }
    }
    
    /**
     * @param str token
     * @param trackingId
     */
    public function actionCheckTransaction() {
        if(isset($_GET['token']) && isset($_GET['trackingId'])) 
        {
             $token = trim($_GET['token']);
             $trackingId = trim($_GET['trackingId']);
             
             if($token == '' || $trackingId == '') {
                 $message = 'Parameters contains blank values';
                 Utilities::log($message);
                 $this->_sendResponse(200, CJSON::encode(array('CheckTransaction'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'',
                                              'TransDate'=>'','TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>10,'DateExpiry'=>'')))));
                 exit;
                 
             } 
             
             if((!Utilities::validateInput($token)) || (!Utilities::validateInput($trackingId)))
             {
                  $message = 'Parameters contains invalid special characters';
                  Utilities::log($message);
                  $this->_sendResponse(200, CJSON::encode(array('CheckTransaction'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'','TransDate'=>'',
                                              'TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>9,'DateExpiry'=>'')))));
                  exit;
             }
             
             $gamingMachineModel = new GamingMachineModel();
             
             //Get EGM Machine Info By Token
             $egmInfo = $gamingMachineModel->getMacInfoByToken(trim($token));

             //Is token valid | expired
             if(!is_array($egmInfo)) {
                   $message = 'Token is invalid or expired';
                   Utilities::log($message);
                   $this->_sendResponse(200, CJSON::encode(array('CheckTransaction'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'','TransDate'=>'',
                                              'TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>8,'DateExpiry'=>'')))));
                   exit;
             }

             $egmStatus = $egmInfo['Status'];

             //Check if EGM Terminal is inactive
             if((int)$egmStatus != 1){
                   $message = "Inactive EGM Terminal.";
                   Utilities::log($message);
                   $this->_sendResponse(200, CJSON::encode(array('CheckTransaction'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'','TransDate'=>'',
                                              'TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>56,'DateExpiry'=>'')))));
                   exit;
             }
             
             $isTransSuccess = $this->checkTransaction($token, $trackingId);
             
             $this->_sendResponse(200, CJSON::encode(array('CheckTransaction'=>($isTransSuccess))));
             
             
        } else {
            $this->_sendResponse(200, CJSON::encode(array('CheckTransaction'=>(
                                         array('TransStatus'=>2,
                                               'TransAmount'=>'','TransDate'=>'',
                                               'TrackingID'=>'','TransID'=>'',
                                               'TransMessage'=>'Parameters are not set',
                                               'ErrorCode'=>11,'DateExpiry'=>'')))));
        }
            
    }
    
    /**
     * Deposit transaction
     * @param str token
     * @param int isVip
     * @param str terminalCode
     * @param int transMethod
     * @param str transDetail
     * @param str trackingId
     * @return json
     */
    public function actionDeposit(){
        if(isset($_GET['token']) && isset($_GET['isVip']) && 
           isset($_GET['terminalCode']) && isset($_GET['transMethod']) && 
           isset($_GET['transDetail']) && isset($_GET['trackingId'])) 
        {
             $token = trim($_GET['token']);
             $isVip = trim($_GET['isVip']);
             $terminalCode = trim($_GET['terminalCode']);
             $transMethod = trim($_GET['transMethod']);
             $transDetail = trim($_GET['transDetail']);
             $trackingId = trim($_GET['trackingId']);
             
             if($token == '' || $isVip == '' || $transMethod == '' || 
                $terminalCode == '' || $trackingId == '') 
             {
                 $message = 'Parameters contains blank values';
                 Utilities::log($message);
                 $this->_sendResponse(200, CJSON::encode(array('Deposit'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'',
                                              'TransDate'=>'','TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>10)))));
                 exit;
                 
             } 
             
             if((!Utilities::validateInput($token)) || (!Utilities::validateInput($isVip)) || 
                (!Utilities::validateInput($transMethod)) || (!Utilities::validateInput($terminalCode)) || 
                (!Utilities::validateInput($trackingId)))
             {
                  $message = 'Parameters contains invalid special characters';
                  Utilities::log($message);
                  $this->_sendResponse(200, CJSON::encode(array('Deposit'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'','TransDate'=>'',
                                              'TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>9)))));
                  exit;
             }

             //validate isVip value if numeric
             if(!is_numeric($isVip)){
                  $message = "Invalid isVip value";
                  Utilities::log($message);
                  $this->_sendResponse(200, CJSON::encode(array('Deposit'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'','TransDate'=>'',
                                              'TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>71)))));
                  exit;
             }
             
             
            $terminalID = $this->runCommonValidator($token, $terminalCode, $isVip, $transType = 'D');
            
            //if successfully validated, terminalID result must be numeric
            if(is_numeric($terminalID)){
                
                //Cash method is only allowed in deposit transaction
                if($transMethod == 0){
                   $isTransSuccess = $this->doCashMethod($terminalID, $transType = 'D', 
                                                     $transDetail, $trackingId);

                   $this->_sendResponse(200, CJSON::encode(array('Deposit'=>($isTransSuccess))));

                } else {
                   $message = "Transaction Method is invalid";
                   Utilities::log($message);
                   $this->_sendResponse(200, CJSON::encode(array('Deposit'=>(
                                             array('TransStatus'=>2,
                                                   'TransAmount'=>'','TransDate'=>'',
                                                   'TrackingID'=>'','TransID'=>'',
                                                   'TransMessage'=>$message,
                                                   'ErrorCode'=>12)))));
                }
                
            } else {
                $message = $terminalID['message'];
                $errorCode = $terminalID['ErrorCode'];
                Utilities::log($message);
                $this->_sendResponse(200, CJSON::encode(array('Deposit'=>(
                                             array('TransStatus'=>2,
                                                   'TransAmount'=>'','TransDate'=>'',
                                                   'TrackingID'=>'','TransID'=>'',
                                                   'TransMessage'=>$message,
                                                   'ErrorCode'=>(int)$errorCode)))));
            }
            
        }
        else
        {
            $this->_sendResponse(200, CJSON::encode(array('Deposit'=>(
                                         array('TransStatus'=>2,
                                               'TransAmount'=>'','TransDate'=>'',
                                               'TrackingID'=>'','TransID'=>'',
                                               'TransMessage'=>'Parameters are not set',
                                               'ErrorCode'=>11)))));
        }
    }
    
    /**
     * returns the min and max denomination
     * @param str $token
     * @return json
     */
    public function actionMinMaxInfo(){
        $token = '';
        
        if(isset($_GET['token']) && $_GET['token'] != '')
        {
            $token = htmlentities($_GET['token']);
            
            
            if(Utilities::validateInput($token))
            {
                $gamingmachinemodel = new GamingMachineModel();
                $sitedenomodel = new SiteDenominationModel();
                
                $posaccountno = $gamingmachinemodel->getPOSAccountNo($token);

                //Check if token was set
                if($posaccountno)
                {
                    $results = $sitedenomodel->getMinMaxInfo($posaccountno);

                    foreach ($results as $rows) {
                        if($rows['DenominationName'] == "Regular")
                        {
                            $minreg = $rows['MinDenominationValue'];
                            $maxreg = $rows['MaxDenominationValue'];
                        } else {
                            $minvip = $rows['MinDenominationValue'];
                            $maxvip = $rows['MaxDenominationValue'];
                        }
                    }

                    if(isset($minreg) && $minreg != '')
                    {
                        $message = 'Successful';
                        $this->_sendResponse(200, CJSON::encode(array('MinMaxInfo'=>(array('MinReg'=>(float)$minreg,'MaxReg'=>(float)$maxreg,'MinVIP'=>(float)$minvip,'MaxVIP'=>(float)$maxvip,'ErrorCode'=>0,'ErrorMessage'=>$message)))));


                    } else {

                        $message = 'Cannot get minimum and maximum denomination';
                        Utilities::log($message);
                        $this->_sendResponse(200, CJSON::encode(array('MinMaxInfo'=>(array('MinReg'=>'','MaxReg'=>'','MinVIP'=>'','MaxVIP'=>'','ErrorCode'=>62,'ErrorMessage'=>$message)))));

                    }

                } else {
                    $message = 'Token is invalid or expired';
                    Utilities::log($message);
                    $this->_sendResponse(200, CJSON::encode(array('MinMaxInfo'=>(array('MinReg'=>'','MaxReg'=>'','MinVIP'=>'','MaxVIP'=>'','ErrorCode'=>8,'ErrorMessage'=>$message)))));
                }
            } else {
                
                $message = 'Token contains invalid special characters';
                Utilities::log($message);
                $this->_sendResponse(200, CJSON::encode(array('MinMaxInfo'=>(array('MinReg'=>'','MaxReg'=>'','MinVIP'=>'','MaxVIP'=>'','ErrorCode'=>5,'ErrorMessage'=>$message)))));
            }
            
        } else {
            $message = 'Token is not set or blank';
            Utilities::log($message);
            $this->_sendResponse(200, CJSON::encode(array('MinMaxInfo'=>(array('MinReg'=>'','MaxReg'=>'','MinVIP'=>'','MaxVIP'=>'','ErrorCode'=>6,'ErrorMessage'=>$message)))));
        }
    }
     
    /**
     * Reload transaction
     * @param str token
     * @param str terminalCode
     * @param int transMethod
     * @param str transDetail
     * @param str trackingId
     * @return json
     */
    public function actionReload(){
        if(isset($_GET['token']) &&  isset($_GET['terminalCode']) && 
           isset($_GET['transMethod']) && isset($_GET['transDetail']) && 
           isset($_GET['trackingId'])) 
        {
             $token = trim($_GET['token']);
             $terminalCode = trim($_GET['terminalCode']);
             $transMethod = trim($_GET['transMethod']);
             $transDetail = trim($_GET['transDetail']);
             $trackingId = trim($_GET['trackingId']);
             
             if($token == '' || $transMethod == '' || 
                $terminalCode == '' || $trackingId == '') 
             {
                 $message = 'Parameters contains blank values';
                 Utilities::log($message);
                 $this->_sendResponse(200, CJSON::encode(array('Reload'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'',
                                              'TransDate'=>'','TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>10)))));
                 exit;
                 
             } 
             
             if((!Utilities::validateInput($token)) || (!Utilities::validateInput($transMethod)) || 
                (!Utilities::validateInput($terminalCode)) || (!Utilities::validateInput($trackingId)))
             {
                  $message = 'Parameters contains invalid special characters';
                  Utilities::log($message);
                  $this->_sendResponse(200, CJSON::encode(array('Reload'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'','TransDate'=>'',
                                              'TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>9)))));
                  exit;
             }
             
             $terminalID = $this->runCommonValidator($token, $terminalCode, $isVip = null, $transType = 'R');
            
             //if successfully validated, terminalID result must be numeric
             if(is_numeric($terminalID)){
                 
                 switch((int)$transMethod){
                     case 0 :
                         $isTransSuccess = $this->doCashMethod($terminalID, $transType = 'R', 
                                                     $transDetail, $trackingId);

                         $this->_sendResponse(200, CJSON::encode(array('Reload'=>($isTransSuccess))));
                     break;
                     case 1:
                         $isTransSuccess = $this->doVoucherMethod($token, $terminalID, $transType = 'R', $transDetail, $trackingId);

                         $this->_sendResponse(200, CJSON::encode(array('Reload'=>($isTransSuccess))));
                     break;
                     default : 
                         $message = "Transaction Method is invalid";
                         Utilities::log($message);
                         $this->_sendResponse(200, CJSON::encode(array('Reload'=>(
                                                     array('TransStatus'=>2,
                                                           'TransAmount'=>'','TransDate'=>'',
                                                           'TrackingID'=>'','TransID'=>'',
                                                           'TransMessage'=>$message,
                                                           'ErrorCode'=>12)))));
                     break;
                 }
                
             } else {
                $message = $terminalID['message'];
                $errorCode = $terminalID['ErrorCode'];
                Utilities::log($message);
                $this->_sendResponse(200, CJSON::encode(array('Reload'=>(
                                             array('TransStatus'=>2,
                                                   'TransAmount'=>'','TransDate'=>'',
                                                   'TrackingID'=>'','TransID'=>'',
                                                   'TransMessage'=>$message,
                                                   'ErrorCode'=>(int)$errorCode)))));
             }
            
        }
        else
        {
            $this->_sendResponse(200, CJSON::encode(array('Reload'=>(
                                         array('TransStatus'=>2,
                                               'TransAmount'=>'','TransDate'=>'',
                                               'TrackingID'=>'','TransID'=>'',
                                               'TransMessage'=>'Parameters are not set',
                                               'ErrorCode'=>11)))));
        }
    }
    
    /**
     * Validates a token
     * @param str $token
     */
    public function actionValidateToken(){
        $token = '';
        
        if(isset($_GET['token']) && $_GET['token'] != '')
        {
            $token = htmlentities($_GET['token']);
            
            if(Utilities::validateInput($token))
            {
                $gamingmachinemodel = new GamingMachineModel();
                $sitedenomodel = new SiteDenominationModel();
                
                $isTokenValid = $gamingmachinemodel->verifyToken($token);

                //Check if token was set
                if((int)$isTokenValid > 0)
                {
                    $this->_sendResponse(200, CJSON::encode(array('ValidateToken'=>(array('ErrorCode'=>0,'ErrorMessage'=>'Successful')))));
                } else {
                    $message = 'Token is invalid or expired';
                    Utilities::log($message);
                    $this->_sendResponse(200, CJSON::encode(array('ValidateToken'=>(array('ErrorCode'=>8,'ErrorMessage'=>$message)))));
                }
                
            } else {
                
                $message = 'Token contains invalid special characters';
                Utilities::log($message);
                $this->_sendResponse(200, CJSON::encode(array('ValidateToken'=>(array('ErrorCode'=>5,'ErrorMessage'=>$message)))));
            }
            
        } else {
            $message = 'Token is not set or blank';
            Utilities::log($message);
            $this->_sendResponse(200, CJSON::encode(array('ValidateToken'=>(array('ErrorCode'=>6,'ErrorMessage'=>$message)))));
        }
    }
    
    /**
     * Withdraw Transaction
     * @param str token
     * @param str terminalCode
     * @param int transMethod
     * @param str transDetail
     * @param str trackingId
     */
    public function actionWithdraw(){
        if(isset($_GET['token']) &&  isset($_GET['terminalCode']) && 
           isset($_GET['trackingId'])) 
        {
             $token = trim($_GET['token']);
             $terminalCode = trim($_GET['terminalCode']);
             $trackingId = trim($_GET['trackingId']);
             
             if($token == '' || $terminalCode == '' || $trackingId == '') 
             {
                 $message = 'Parameters contains blank values';
                 Utilities::log($message);
                 $this->_sendResponse(200, CJSON::encode(array('Withdraw'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'',
                                              'TransDate'=>'','TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>10)))));
                 exit;
                 
             } 
             
             if((!Utilities::validateInput($token)) || (!Utilities::validateInput($terminalCode)) 
                  || (!Utilities::validateInput($trackingId)))
             {
                  $message = 'Parameters contains invalid special characters';
                  Utilities::log($message);
                  $this->_sendResponse(200, CJSON::encode(array('Withdraw'=>(
                                        array('TransStatus'=>2,'TransAmount'=>'','TransDate'=>'',
                                              'TrackingID'=>'','TransID'=>'',
                                              'TransMessage'=>$message,
                                              'ErrorCode'=>9)))));
                  exit;
             }
             
             $terminalID = $this->runCommonValidator($token, $terminalCode, $isVip = null, $transType = 'W');
             
             //if successfully validated, terminalID result must be numeric
             if(is_numeric($terminalID)){
                 
                 $commonController = new CommonController();
        
                 $isTransSuccess = $commonController->withrawTrans($token, $terminalID, $trackingId);
        
                 $this->_sendResponse(200, CJSON::encode(array('Withdraw'=>($isTransSuccess))));
                 
             } else {
                $message = $terminalID['message'];
                $errorCode = $terminalID['ErrorCode'];
                Utilities::log($message);
                $this->_sendResponse(200, CJSON::encode(array('Withdraw'=>(
                                             array('TransStatus'=>2,
                                                   'TransAmount'=>'','TransDate'=>'',
                                                   'TrackingID'=>'','TransID'=>'',
                                                   'TransMessage'=>$message,
                                                   'ErrorCode'=>(int)$errorCode)))));
             }
            
        }
        else
        {
            $this->_sendResponse(200, CJSON::encode(array('Withdraw'=>(
                                         array('TransStatus'=>2,
                                               'TransAmount'=>'','TransDate'=>'',
                                               'TrackingID'=>'','TransID'=>'',
                                               'TransMessage'=>'Parameters are not set',
                                               'ErrorCode'=>11)))));
        }
    }
    
    public function actionError(){
        die('Page not found');
    }
    
    /**
     * Checks if the status of a certain transaction
     * @param str $token
     * @param str $trackingID
     * @return array 
     */
    protected function checkTransaction($token, $trackingID){
        $egmLogsModel = new GamingRequestLogs();
        $transDetailsModel = new TransactionDetailsModel();
        
        $transResult = $egmLogsModel->chkTransaction($trackingID);
        
        //check if tracking ID is valid
        if(is_array($transResult)){
            
            //check if transaction was successful
            if(isset($transResult['TransactionReferenceID']) && $transResult['Status'] == 1){
                $transDetailsResult = $transDetailsModel->getDetailsByReferenceID($transResult['TransactionReferenceID']);
                
                //If transaction type is (W) or 2, display DateExpiry
                if($transResult['TransactionType'] == 'W')
                    $result = array('TransStatus'=>(int)$transResult['Status'],
                                    'TransAmount'=>Utilities::toDecimal($transDetailsResult['Amount']),
                                    'TransDate'=>$transDetailsResult['DateCreated'],'TrackingID'=>$trackingID,
                                    'TransID'=>$transResult['Option1'],'TransMessage'=>$this->_getStatusMessage($transResult['Status']),
                                    'ErrorCode'=>0,"DateExpiry"=>$transResult['Option2']);
                else
                    $result = array('TransStatus'=>(int)$transResult['Status'],
                                    'TransAmount'=>Utilities::toDecimal($transDetailsResult['Amount']),
                                    'TransDate'=>$transDetailsResult['DateCreated'],'TrackingID'=>$trackingID,
                                    'TransID'=>$transResult['Option1'],'TransMessage'=>$this->_getStatusMessage($transResult['Status']),
                                    'ErrorCode'=>0,"DateExpiry"=>'');
            } else {
                $result = array('TransStatus'=>(int)$transResult['Status'],
                                'TransAmount'=>Utilities::toDecimal($transResult['ReportedAmount']),
                                'TransDate'=>$transResult['DateLastUpdated'],'TrackingID'=>$trackingID,'TransID'=>'',
                                'TransMessage'=>$this->_getStatusMessage($transResult['Status']),'ErrorCode'=>0,
                                'DateExpiry'=>'');
            }
        } else {
            $message = 'Tracking ID is not valid';
            Utilities::log($message);
            $result = array('TransStatus'=>2,'TransAmount'=>'','TransDate'=>'',
                            'TrackingID'=>$trackingID,'TransID'=>'',
                            'TransMessage'=>$message,'ErrorCode'=>54,'DateExpiry'=>'');
        }
        return $result;
    }
    
    /**
     *
     * @param str $token
     * @param int $terminalID
     * @param str $transType
     * @param str $transDetails
     * @param str $trackingId
     * @param str $lastTransDate
     * @return array
     */
    protected function withdrawTrans($token, $terminalID, $transType, $transDetails, 
                                     $trackingId,$lastTransDate){
        
        //check if tracking id is blank
        if($trackingId != '') 
        {
            $commonController = new CommonController();
        
            $balanceResult = $this->getBalance($token, $terminalID, 3, $lastTransDate);

            //check if amount is not blank or set
            if(isset($balanceResult['TransAmount']) || $balanceResult['TransAmount'] != '')
            {

                $actualBalance = Utilities::toInt($balanceResult['TransAmount']);

                $apiResult = $commonController->withrawTrans($token, $terminalID, $transType, $actualBalance, $trackingId, $lastTransDate);

            }
            else
                $apiResult = $balanceResult;
        }
        else
        {
            $message = 'Tracking ID is not set';
            Utilities::log($message);
            $apiResult = array('TransStatus'=>2,'TransType'=>$transType,'TransAmount'=>'',
                               'TransDate'=>'','TrackingID'=>'','TransID'=>'',
                               'TransMessage'=>$message,'ErrorCode'=>36,'DateExpiry'=>'');
        }
        
        return $apiResult;
    }
    
    /**
     * Cash Method
     * @param int $terminalID
     * @param str $transType (Deposit, Reload, Withdraw, GetBalance, CheckTransaction)
     * @param str $transDetails (service_id;amount;loyalty_card_id;)
     * @param str $trackingId 
     * @return array
     */
    protected function doCashMethod($terminalID, $transType, $transDetails, $trackingId){
        Yii::import('application.controllers.CashMethodController');
        $cashMethod = new CashMethodController();
        
        switch($transType){
            case 'D' : 
                $apiResult = $cashMethod->depositTrans($terminalID, $transDetails, $trackingId);
            break;
            case 'R' :
                $apiResult = $cashMethod->reloadTrans($terminalID, $transDetails, $trackingId);
            break;
            case 'W' :
                $apiResult = $this->withdrawTrans($terminalID, $transDetails, $trackingId);
            break;
            default:
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>'','TransID'=>'',
                             'TransMessage'=>'Invalid Transaction Type','ErrorCode'=>13);
            break;
        }
        $this->end();
        return $apiResult;
    }
    
    /**
     * Voucher Method
     * @param str $token
     * @param int $terminalID
     * @param str $transType
     * @param str $transDetails
     * @param str $trackingId
     * @return array
     */
    protected function doVoucherMethod($token, $terminalID, $transType, $transDetails, $trackingId){
        Yii::import('application.controllers.VoucherMethodController');
        
        $voucherMethod = new VoucherMethodController();
        
        switch($transType){
            case 'D' : 
                $apiResult = $voucherMethod->depositTrans($token, $terminalID, $transDetails, $trackingId);
            break;
            case 'R' :
                $apiResult = $voucherMethod->reloadTrans($token, $terminalID, $transDetails, $trackingId);
            break;
            case 'W' :
                $apiResult = $this->withdrawTrans($token, $terminalID, $transDetails, $trackingId);
            break;
            default:
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>'','TransID'=>'',
                             'TransMessage'=>'Invalid Transaction Type','ErrorCode'=>13);
            break;
        }
        $this->end();
        return $apiResult;
    }
    
    /**
     * Reference Method for status
     * @param string $status
     * @return string 
     */
    private function _getStatusMessage($status){
        switch($status){
            case 0 :
                $message = 'Transaction Pending';
            break;
            case 1 :
                $message = 'Transaction Successful';
            break;
            case 2:
                $message = 'Transaction Failed';
            breaK;
            case 3 : 
                $message = 'Fulfillment Approved';
            break;
            case 4:
                $message = "Fulfillment Denied";
            break;
            default : 
                $message = "Invalid Status";
            break;
        }
        
        return $message;
    }
    
    /**
     *
     * @param type $status
     * @param string $body
     * @param type $content_type 
     * @link http://www.yiiframework.com/wiki/175/how-to-create-a-rest-api
     */
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if($body != '')
        {
            // send the body
            echo $body;
        }
        // we need to create the body if none is passed
        else
        {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch($status)
            {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 200:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on 
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
                    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                        <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
                    </head>
                    <body>
                        <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
                        <p>' . $message . '</p>
                        <hr />
                        <address>' . $signature . '</address>
                    </body>
                    </html>';

            echo $body;
        }
        //Yii::app()->end();
    }
    
    /**
     * HTTP Status Code Message
     * @param string $status
     * @return bool
     */
    private function _getStatusCodeMessage($status)
    {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            200 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
    
    /**
     * @return string sessionID as tokenID 
     */
    private function generateToken(){
        return Yii::app()->getSession()->getSessionID(); //generate token
    }
    
    /**
     * Common EGM-KAPI Validator for most of web methods
     * (CheckActiveSession, Deposit, Reload, Withdraw, CheckTransaction)
     * @param str $token
     * @param str $terminalCode
     * @param int $isVip
     * @return int | array
     */
    protected function runCommonValidator($token, $terminalCode, $isVip, $transType){
         $gamingMachineModel = new GamingMachineModel();
         $sitesModel = new SitesModel();

         $issuccess = false;
         $egmTerminalID = ''; 
         $response = array();
         
         //Get EGM Machine Info By Token
         $egmInfo = $gamingMachineModel->getMacInfoByToken(trim($token));

         //Is token invalid | expired
         if(!is_array($egmInfo)) {
             $message = 'Token is invalid or expired';
             return array("message"=>$message,"ErrorCode"=>8);
         }

         $egmStatus = $egmInfo['Status'];
         $siteID = $egmInfo['POSAccountNo'];

         $isSiteActive = $sitesModel->checkIfActiveSite($siteID);

         //Check if site is deactivated
         if(!$isSiteActive){
            $message = "Inactive Site.";
            return array("message"=>$message,"ErrorCode"=>56);
         }

         //Check if EGM Terminal is inactive
         if((int)$egmStatus != 1){
            $message = "Inactive EGM Terminal.";
            return array("message"=>$message,"ErrorCode"=>68);
         }

         //Check isVip parameter for Deposit and CheckActiveSession Method
         if($transType == 'D' || $transType == ''){
             $terminalsModel = new TerminalsModel();
         
             switch($isVip)
             {
                //Check if isVip parameter is Regular 
                //then pass regular EGM registered terminal
                case 0 : 
                   $terminalCode = $terminalCode;
                   $egmTerminalID = $egmInfo['TerminalID'];
                break;
                //Check if isVip parameter is VIP, 
                //then append VIP string and pass VIP EGM registered terminal
                case 1 :
                   $terminalCode = $terminalCode."VIP";
                   $egmTerminalID = $egmInfo['TerminalIDVIP'];
                break;
                default :
                   $message = "Invalid isVip value";
                   return array("message"=>$message,"ErrorCode"=>71);
                break;
             }
             
             //Check if terminal code is valid
             $terminalInfo = $terminalsModel->getTerminalDetails($terminalCode, $isVip);
             
             // Validate terminal code
             if(!is_array($terminalInfo)){
               $message = 'TerminalCode is invalid';
               return array("message"=>$message,"ErrorCode"=>69);
             }
            
         } else {
             
             $terminalSessionsModel = new TerminalSessionsModel();
             
             //Check if terminal code is valid, then pass active terminal
             $terminalInfo = $terminalSessionsModel->getActiveTerminal($terminalCode);
             
             // Validate terminal code
             if(!is_array($terminalInfo)){
               $message = 'Error: Terminal has no active session.';
               return array("message"=>$message,"ErrorCode"=>43);
             }
             
             $isVip = (int)$terminalInfo['isVIP'];
             
             switch($isVip)
             {
                //Check if isVip parameter is Regular 
                //then pass regular EGM registered terminal
                case 0 : 
                   $terminalCode = $terminalCode;
                   $egmTerminalID = $egmInfo['TerminalID'];
                break;
                //Check if isVip parameter is VIP, 
                //then append VIP string and pass VIP EGM registered terminal
                case 1 :
                   $terminalCode = $terminalCode."VIP";
                   $egmTerminalID = $egmInfo['TerminalIDVIP'];
                break;
                default :
                   $message = "Invalid isVip value";
                   return array("message"=>$message,"ErrorCode"=>71);
                break;
             }
        }

        $terminalID = $terminalInfo['TerminalID'];
        $terminalStatus= $terminalInfo['Status'];

        //Check if PEGS Terminal is inactive
        if((int)$terminalStatus != 1){
           $message = "Inactive PEGS Terminal.";
           return array("message"=>$message,"ErrorCode"=>56);
        }
        
        //Check if Terminal Code and token are match
        if($terminalID != $egmTerminalID){
           $issuccess = true; $message = "Token and TerminalCode did not match";
           return array("message"=>$message,"ErrorCode"=>70);
        }
        
        //return terminal ID if success
        if(!is_null($terminalID)) {
            return $terminalID;
        } 
    }
}

?>
