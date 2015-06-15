<?php
/**
 * Description of LoginForm
 *
 * @author bryan
 */
class LoginFormModel extends MI_Model{
    public $username;
    public $password;
    public $message = '';
    public $passkey;
    public $email;
    public $newpassword;
    public $confirmpassword;
    public $aid;
    
    protected function _validation() {
        return array(
            array('fields'=>array('username'),'validator'=>'StringValidator','message'=>'Please enter your username.'),
            array('fields'=>array('passkey'),'validator'=>'StringValidator','message'=>'Please enter your passkey'),
            array('fields'=>array('newpassword'),'validator'=>'StringValidator','message'=>'Please enter your New Password'),
            array('fields'=>array('confirmpassword'),'validator'=>'StringValidator','message'=>'Please enter your Confirm Password'),
            array('fields'=>array('email'),'validator'=>'EmailValidator'),
            array(
                'fields'=>array('password'),
                'validator'=>'PasswordValidator',
                'options'=>array('min'=>Mirage::app()->param['min_pass_len'],'user'=>'username')
            ),
            array(
                'fields'=>array('newpassword','confirmpassword'),
                'validator'=>'StringValidator',
                'options'=>array('min'=>Mirage::app()->param['min_pass_len']),'message'=>'Please enter your password. Minimum of 8 alphanumeric.'
            ),            
            array('fields'=>array('confirmpassword'),
                'validator'=>'StringValidator',
                'options'=>array('compare'=>array('attr'=>'newpassword','comparator'=>'==')),
                'message'=>'New Password should be equal to Confirm Password'),
        );
    }
    
    /**
     *
     * @return bool
     */
    public function authenticate() {
        unset($_SESSION['haspasskey']);
        unset($_SESSION['expired_pass']);
        Mirage::loadModels(array(
            'AccountsModel',
            'AccountSessionsModel',
            'AccountDetailsModel',
            'AuditTrailModel',
            'CashierMachineCountsModel',
            'CashierMachineInfoModel',
            'RefAccountTypesModel',
            'SiteAccountsModel',
            'SitesModel',
            ));
        
        $accountsModel = new AccountsModel();
        $accountSessionsModel = new AccountSessionsModel();
        $accountDetailsModel = new AccountDetailsModel();
        $refAccountTypeName = new RefAccountTypesModel();
        $siteAccountsModel = new SiteAccountsModel();
        $cashierMachineInfoModel = new CashierMachineInfoModel();
        $cashierMachineCountsModel = new CashierMachineCountsModel();
        $auditTrailModel = new AuditTrailModel();
        $sitesModel = new SitesModel();
        
        $login_result = $accountsModel->login($this->username, $this->password);
        $attempt_count = $accountsModel->queryattempt($this->username);
        $date = $this->getDate();

        $accounttype = $accountsModel->getAccountTypebyUN($this->username);
        
        if($accounttype == 15){
            $this->setAttributeErrorMessage('message', "User has no acccess rights..");
            $this->close();
            return false;
        }
        
        $attempt_count++;
        $accountsModel->updateAttempt($attempt_count, $this->username);          
        
        // user not found
        if(!isset($login_result['UserName'])) {
            // check number of attempt
            if($attempt_count >= 3) {
                $this->setAttributeErrorMessage('message', "Access Denied.Please contact system administrator to have your account unlocked.");
                $this->close();
                return false;
            }
            
            $this->setAttributeErrorMessage('message', "Invalid username or password");
            $this->close();
            return false;
        }
        else {
            $accountsModel->updateOnLogin($attempt_count=0, $date, $this->username);  
        }
        
        // check if mac address exist
        if(!isset($_SESSION['smacid'])) {
//            if($_SESSION['smacid'] == ''){
//                
//            }
            $accountSessionsModel->deleteSession($login_result['AID']);    
            session_destroy();
            $this->close();
            $this->setAttributeErrorMessage('message', "Login: MAC Address is empty");
            return false;
        }   
        
        // get destination name
        $vdesignation = $accountDetailsModel->getDesignation($login_result['AID']);
        // get account name
        $raccountname = $refAccountTypeName->getAccTypeName($login_result['AccountTypeID']);
        
        
        // check $_SESSION['acctype'] 4, 7, 2, check ie browser and version
        if(!in_array($login_result['AccountTypeID'], Mirage::app()->param['allowed_acctype'])) {
            $accountSessionsModel->deleteSession($login_result['AID']);
            session_destroy();          
            $this->close();
            $this->setAttributeErrorMessage('message', "You are not allowed to access cashier module");
            return false;     
        }
        
//        if(!isIEBrowser() || getIEVersion() < 8) {
//            $accountSessionsModel->deleteSession($login_result['AID']);
//            session_destroy();          
//            $this->close();
//            $this->setAttributeErrorMessage('message', "Please use Internet Explorer version 8.0 and above to view the Cashier");
//            return false; 
//        }
        
        // put to variable hardware info due to session will be destroy
        $tempHardwareInfoContainer = $this->getHardwareInfo();
        
        // check if session already exist
        if($accountSessionsModel->hasSession($login_result['AID'])) {
            // delete old session
            $accountSessionsModel->deleteSession($login_result['AID']);
            session_destroy();
        }
        
        $this->restoreHardwareInfoSession($tempHardwareInfoContainer);
        
        $_SESSION['uname'] = trim($login_result['UserName']);
        $_SESSION['accID'] = $login_result['AID'];
        $_SESSION['userid'] = $login_result['AID'];
        $_SESSION['designation'] = $vdesignation;
        $_SESSION['acctype'] = $login_result['AccountTypeID'];
        $_SESSION['accname'] = $raccountname;
        $_SESSION['mid']  = "";
//        $_SESSION['browser'] = $browser;
//        $_SESSION['version'] = $version;
//        $_SESSION['chrome'] = $chrome; 
        
        $siteid = $siteAccountsModel->getSiteID($login_result['AID']);
        $_SESSION['AccountSiteID'] = $siteid; 
        
        $cashierVersion = $sitesModel->getCashierVersion($siteid);
        
        //check if cashier version accessed by cashier was valid
        if(Mirage::app()->param['cashier_version'] != $cashierVersion){
            $accountSessionsModel->deleteSession($login_result['AID']);
            session_destroy();          
            $this->close();
            $this->setAttributeErrorMessage('message', "You are trying to access the system using a wrong URL/web address. Please use the specific URL/web address assigned to your site specifically to access the system.");
            return false; 
        }
        
        $transdetails = $this->username;
        if($login_result['WithPasskey'] > 0) {
            // redirect to pass key
            $_SESSION['haspasskey'] = true;
            $this->aid = $login_result['AID'];
            if($accountsModel->checkpwdexpired($this->username)) {
                if($attempt_count > 0)
                    $attempt_count--;
                $new_sessionid = session_id();
                $accountsModel->updateLoginAttempt($attempt_count, $login_result['AID']);
                if (isset($login_result['AID'])){  
                $auditTrailModel->logToAudit($new_sessionid, $login_result['AID'], $transdetails, $date, gethostbyaddr($_SERVER['REMOTE_ADDR']), '65');
                }
                $this->setAttributeErrorMessage('message', "Your password has been expired. Please check your email to change your password");
                $this->close();
                $_SESSION['expired_pass'] = true;
                return true;
            }
            return true;
        } else {
            
            // without passkey
            $old_sessionid = session_id();
            session_regenerate_id();
            $new_sessionid = session_id(); 
           
            $_SESSION['sessionID'] = $new_sessionid ;  
            
            //updates loginattempt = 0, lastlogin and passkey
            $loginattempt = 0;                    
            $accountsModel->updateOnLogin($loginattempt, $date, $login_result['UserName']);
            
            //insert sessionid in sessionaccounts
            if(!$accountSessionsModel->insertSession($login_result['AID'], $new_sessionid, $date)) {
                $this->setAttributeErrorMessage('message', "Session not created");
                $this->close();
                return false;
            }
        }
        
        $ctrmachine = $cashierMachineInfoModel->checkmachineid($_SESSION['smachineid']);
        
        $iscomputerexist = $cashierMachineInfoModel->checkComputerCredential($_SESSION['smachineid']);
        
        if($iscomputerexist['ctrsite'] > 0) {
            //check if same machine ID but different site;
            if(($ctrmachine['ctrmachine'] > 0) && ($ctrmachine['POSAccountNo'] <> $_SESSION['AccountSiteID'])) {
                 $new_sessionid = session_id();
                $transdetails = $this->username;
                $accountsModel->updateLoginAttempt($attempt_count, $login_result['AID']);
                if (isset($login_result['AID'])){  
                $auditTrailModel->logToAudit($new_sessionid, $login_result['AID'], $transdetails, $date, gethostbyaddr($_SERVER['REMOTE_ADDR']), '66');
                }
                $this->setAttributeErrorMessage('message', 'Conflicting Machine ID. Please inform Customer Service');
                $this->close();
                return false;
            }
        }
        
        $isadded = 0;
        
        //check if computer credential exist; if not exist, it must be added
        if(!isset($iscomputerexist['CashierMachineInfoId_PK']) || $iscomputerexist['CashierMachineInfoId_PK'] == 0 || $iscomputerexist['CashierMachineInfoId_PK'] == null){
            
            $ctrcashier = $cashierMachineCountsModel->checkCashierMachine($_SESSION['AccountSiteID']); //get number of allowed cashier  per site
            $issite = $cashierMachineInfoModel->checksitecount($_SESSION['AccountSiteID']);
            
            $ctrsite = $issite['ctrsite'];
            
            //before adding, check if cashier machine count is greater than the number of pos account / site
            if($ctrcashier['CashierMachineCount'] <= $ctrsite){
                
                if (isset($login_result['AID'])){  
                $auditTrailModel->logToAudit($new_sessionid, $login_result['AID'], $transdetails, $date, gethostbyaddr($_SERVER['REMOTE_ADDR']), '67');
                }
                $this->setAttributeErrorMessage('message', 'Please inform Customer Service to adjust the number of cashier terminal for this site');
                session_destroy();
                $this->close();
                return false;
            }
            if($ctrcashier['CashierMachineCount'] > $ctrsite) {
                $isadded = $cashierMachineInfoModel->addComputerCredential(
                        $_SESSION['scpuid'], 
                        $_SESSION['scpuname'],
                        $_SESSION['sbiosid'], 
                        $_SESSION['smbid'],
                        $_SESSION['sosid'], 
                        $_SESSION['smacid'], 
                        $_SESSION['sipid'], 
                        $_SESSION['sguid'],
                        $_SESSION['AccountSiteID'], 
                        $_SESSION['smachineid'], 
                        $date);
                
                if($isadded > 0)
                {
                    //get site details
                    $rsites = $siteAccountsModel->getSiteDetails($_SESSION['AccountSiteID']); 
                    $posaccno = $rsites['POS'];
                    $sitename = $rsites['SiteName'];
                    //get account name
                    $raccname = $accountsModel->getAccountname($_SESSION['accID']); 
                    $cashiername = $raccname['Name'];
                    $cashieruname = $_SESSION['uname'];
                    $asgroupemail = Mirage::app()->param['ASgroup']; //get AS group email on config file
                    //send email alert if cashier's first login on Kronus
                    $title = "New Cashier Terminal Accessing Kronus";
                    $dateformat = date("Y-m-d h:i:s A", strtotime($date)); //formats date on 12 hr cycle AM / PM 
                    $body = $this->_sendCashierFirstLogin($title, $sitename, $posaccno, $cashiername, $cashieruname, $dateformat);
                    $to = $asgroupemail;
                    $sentEmail = mail($to,$title, $body, "From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html");
                    
                    // Check if message is sent or not
                    if($sentEmail <> 1){
                        $msg = "Message sending failed";
                        $this->setAttributeErrorMessage('message', 'Message sending failed');
                        return false;
                    }
                }
            }
        }
        
        if($isadded > 0 || $iscomputerexist['CashierMachineInfoId_PK'] > 0 || !isset($iscomputerexist['CashierMachineInfoId_PK'])) {
            $this->aid = $login_result['AID'];
            if($accountsModel->checkpwdexpired($this->username)) {
                if($attempt_count > 0)
                    $attempt_count--;
                $new_sessionid = session_id();
                $transdetails = $this->username;
                $accountsModel->updateLoginAttempt($attempt_count, $login_result['AID']);
                if (isset($login_result['AID'])){  
                $auditTrailModel->logToAudit($new_sessionid, $login_result['AID'], $transdetails, $date, gethostbyaddr($_SERVER['REMOTE_ADDR']), '65');
                }
                $accountsModel->updateLoginAttempt($attempt_count, $login_result['AID']);                
                $this->setAttributeErrorMessage('message', "Your password has been expired. Please check your email to change your password");
                $this->close();
                $_SESSION['expired_pass'] = true;
                return true;
            }
            
            //insert to audittrail table
            $transdetails = Mirage::app()->param['sys_version'];
            if (isset($login_result['AID'])){  
                $auditTrailModel->logToAudit($new_sessionid, $login_result['AID'], $transdetails, $date, gethostbyaddr($_SERVER['REMOTE_ADDR']), '1');
                }
            $this->close();
            $_SESSION['success_login'] = true;
            return true;
        }
        
        $accountSessionsModel->deleteSession($login_result['AID']);
        session_destroy();
        $this->close();
        $this->setAttributeErrorMessage('message', 'Login:Invalid computer credential');
        return false;
    } // end of authenticate
    
    public function authenticatePasskey() {

        Mirage::loadModels(array('AccountsModel','CashierMachineCountsModel','AccountSessionsModel','SitesModel','CashierMachineInfoModel','SiteAccountsModel','AuditTrailModel'));
        $accountsModel = new AccountsModel();
        $accountSessionsModel = new AccountSessionsModel();
        $siteAccountsModel = new SiteAccountsModel();
        $sitesModel = new SitesModel();
        $cashierMachineInfoModel = new CashierMachineInfoModel();
        $auditTrailModel = new AuditTrailModel();
        $cashierMachineCountsModel = new CashierMachineCountsModel();
        
        $old_sessionid = session_id();
        session_regenerate_id();
        $new_sessionid = session_id();
        $_SESSION['userid'] = $_SESSION['accID'];
        $vusername = $_SESSION['uname'];
        $aid =  $_SESSION['userid'];
        $access = $_SESSION['acctype'];
        $_SESSION['sessionID'] = $new_sessionid;
        $_SESSION['acctype'] = $access;
        $date = $this->getDate();
        
        //check passkey
        if(!$accountsModel->checkpasskey($this->passkey, $aid)) {
            $this->setAttributeErrorMessage('message', 'Invalid Passkey');
              if (isset($aid)){  
            $auditTrailModel->logToAudit($new_sessionid, $aid, $vusername, $date, gethostbyaddr($_SERVER['REMOTE_ADDR']), '69');
                }
            return false;
        }
        
        // check if with existing session
        if($accountSessionsModel->hasSession($aid)) {
            $accountSessionsModel->deleteSession($aid);
            session_destroy();
        }
        
        //updates loginattempt = 0, lastlogin and passkey
        $loginattempt = 0;
        $isadded = 0;
        $siteid = $siteAccountsModel->getSiteID($aid);
        
        $issiteactive = $sitesModel->checkIfActiveSite($siteid);
        if(!$issiteactive) {
            session_destroy();
            $this->setAttributeErrorMessage('message', 'Inactive Site');
            $this->close();
            return false;
        }
        
        // update login attempt
        $accountsModel->updateOnLogin($loginattempt, $date, $vusername);
        
        if(!$accountSessionsModel->insertSession($aid, $new_sessionid, $date)) {
             session_destroy();
             $this->setAttributeErrorMessage('message', 'Passkey: Session not created');
             $this->close();
             return false;
        }
        
        if($_SESSION['smacid'] == "") {
            $accountSessionsModel->deleteSession($aid);
            session_destroy();
            $this->close();
            $this->setAttributeErrorMessage('message', 'Login: MAC Address is empty');
            return false;         
        }
        
        $ctrmachine = $cashierMachineInfoModel->checkmachineid($_SESSION['smachineid']);
        
        //check if computer credential exist; if not exist, it must be added
        $iscomputerexist = $cashierMachineInfoModel->checkComputerCredential($_SESSION['smachineid']);
        
        //check if same machine ID but different site;
        if($iscomputerexist['ctrsite'] > 0) {
            if(($ctrmachine['ctrmachine'] > 0) && ($ctrmachine['POSAccountNo'] <> $_SESSION['AccountSiteID'])) {
                $this->setAttributeErrorMessage('message', 'Conflicting Machine ID. Please inform Customer Service');
                if (isset($aid)){  
                $auditTrailModel->logToAudit($new_sessionid, $aid, $vusername, $date, gethostbyaddr($_SERVER['REMOTE_ADDR']), '66');
                }
                session_destroy();
                $this->close();
                return false;
            }                
        }
        
        $isadded = 0;
 
        //check if computer credential exist; if not exist, it must be added
        if($iscomputerexist['CashierMachineInfoId_PK'] == 0 || $iscomputerexist['CashierMachineInfoId_PK'] == null) {
            $ctrcashier = $cashierMachineCountsModel->checkCashierMachine($_SESSION['AccountSiteID']); //get number of allowed cashier  per site
            $issite = $cashierMachineInfoModel->checksitecount($_SESSION['AccountSiteID']);
            $ctrsite = $issite['ctrsite'];
            //before adding, check if cashier machine count is greater than the number of pos account / site
            if($ctrcashier['CashierMachineCount'] > $ctrsite) {
                $isadded = $cashierMachineInfoModel->addComputerCredential(
                            $_SESSION['scpuid'], 
                            $_SESSION['scpuname'], 
                            $_SESSION['sbiosid'], 
                            $_SESSION['smbid'], 
                            $_SESSION['sosid'], 
                            $_SESSION['smacid'], 
                            $_SESSION['sipid'], 
                            $_SESSION['sguid'], 
                            $_SESSION['AccountSiteID'], 
                            $_SESSION['smachineid'], 
                            $date);
               if($isadded > 0)
               {
                    //get site details
                    $rsites = $siteAccountsModel->getSiteDetails($_SESSION['AccountSiteID']); 
                    $posaccno = $rsites['POS'];
                    $sitename = $rsites['SiteName'];
                    //get account name
                    $raccname = $accountsModel->getAccountname($_SESSION['accID']); 
                    $cashiername = $raccname['Name'];
                    $cashieruname = $_SESSION['uname'];
                    $asgroupemail = Mirage::app()->param['ASgroup']; //get AS group email on config file
                    //send email alert if cashier's first login on Kronus
                    $title = "New Cashier Terminal Accessing Kronus";
                    $dateformat = date("Y-m-d h:i:s A", strtotime($date)); //formats date on 12 hr cycle AM / PM 
                    $body = $this->_sendCashierFirstLogin($title, $sitename, $posaccno, $cashiername, $cashieruname, $dateformat);
                    $to = $asgroupemail;
                    $sentEmail = mail($to,$title, $body, "From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html");
                    
                    // Check if message is sent or not
                    if($sentEmail <> 1){
                        $msg = "Message sending failed";
                        $this->setAttributeErrorMessage('message', 'Message sending failed');
                        return false;
                    }
                }
            } else {
                $this->setAttributeErrorMessage('message', 'Please inform Customer Service to adjust the number of cashier terminal for this site');
                if (isset($aid)){  
                    $auditTrailModel->logToAudit($new_sessionid, $aid, $vusername, $date, gethostbyaddr($_SERVER['REMOTE_ADDR']), '67');
                }
                session_destroy();
                $this->close();
                return false;
            }
        }

        if($isadded > 0 || $iscomputerexist['CashierMachineInfoId_PK'] > 0) {
                  //insert to audittrail table
                  //$transdetails = "Login -".$aid;
                  $transdetailsl = Mirage::app()->param['sys_version'];
                if (isset($aid)){  
                  $auditTrailModel->logToAudit($new_sessionid, $aid, $transdetailsl, $date, gethostbyaddr($_SERVER['REMOTE_ADDR']), '1');
                }
                  $this->close();
                  $_SESSION['success_login'] = true;
                  return true;
        }
        
        $accountSessionsModel->deleteSession($aid);
        session_destroy();
        $this->close();
        $this->setAttributeErrorMessage('message', 'Login:Invalid computer credential');
        return false;
    } // end of authenticatePasskey
    
    //generating date with microseconds
    public function getDate() {
        $time =microtime(true);
        $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);
        $rawdate = new DateTime( date('Y-m-d H:i:s.'.$micro_time, $time) );
        $date = $rawdate->format("Y-m-d H:i:s.u");
        return $date;
    }    
    
    public function getHardwareInfo() {
        return array(
            'scpuid'=>$_SESSION['scpuid'],
            'scpuname'=>$_SESSION['scpuname'],
            'sbiosid'=>$_SESSION['sbiosid'],
            'smbid'=>$_SESSION['smbid'],
            'sosid'=>$_SESSION['sosid'],
            'smacid'=>$_SESSION['smacid'],
            'sipid'=>$_SESSION['sipid'],
            'sguid'=>$_SESSION['sguid'],
            'smachineid'=>$_SESSION['smachineid']
        );
    }
    
//    public function restoreHardwareInfoSession($hardware_info) {
//        if(!isset($_SESSION)) 
//        { 
//            session_start(); 
//        } 
//        foreach($hardware_info as $k => $v) {
//            $_SESSION[$k] = $v;
//        }
//    }
    
    public function restoreHardwareInfoSession($hardware_info) {        
            @session_start();        
        foreach($hardware_info as $k => $v) {
            $_SESSION[$k] = $v;
        }
    }
    
    public function logout() {
        Mirage::loadModels(array('AuditTrailModel','AccountSessionsModel'));
        $accountSessionsModel = new AccountSessionsModel();
        $auditTrailModel = new AuditTrailModel();
        
        $sessionid = session_id();        
        $transdetails = Mirage::app()->param['sys_version'];
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $date = $this->getDate();
                
        if (isset($_SESSION['accID']) && isset($sessionid))
        {  
            $auditTrailModel->logToAudit($sessionid, $_SESSION['accID'], $transdetails, $date, $ipaddress, 2);
            $accountSessionsModel->deleteSession($_SESSION['accID']);
        }
        
        session_destroy();
        $this->close();
    }
    
    public function isForgotUsername() {
        Mirage::loadModels(array('AccountsModel','AuditTrailModel'));
        $accountsModel = new AccountsModel();
        $auditTrailModel = new AuditTrailModel();
        
        $result = $accountsModel->checkemail($this->email);
        $this->username = $result['UserName'];
        $aid = $accountsModel->getaid($this->username);
        
        $sessionid = session_id();       
        $transdetails = $this->username;
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $date = $this->getDate();
        
        $result = $accountsModel->checkemail($this->email); //check if email exist
        if(!isset($result['UserName'])) {
            $this->setAttributeErrorMessage('message', 'Email Address does not exists');
            $this->close();
            return false;
        }
        if($result['Status'] == 1 || $result['Status'] == 6){
        
            $subject = 'Forgot Username';
            $body = $this->_getForgotUsernameEmailContent($result['UserName']);


            $to = preg_replace("/[0-9]+$/", '', $this->email);

    //        if($this->testMailer('poskronusadmin@philweb.com.ph', 'poskronusadmin',$to, $subject, $body)) {
    //            $_SESSION['notification'] = 'Your username has been sent to you through your email';
    //            return true;
    //        } else {
    //            $this->setAttributeErrorMessage('message', 'Message sending failed');
    //            return false;
    //        }


            $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
            $sentEmail = mail($to,$subject, $body, "From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html");
              if (isset($aid)){  
            $auditTrailModel->logToAudit($sessionid, $aid, $transdetails, $date, $ipaddress, 62);
                    }

             // Check if message is sent or not
             if($sentEmail == 1){
                 $_SESSION['notification'] = 'Your password has been sent to you through your email';
                 return true;
             }
             else{
                 $msg = "Message sending failed";
                 $this->setAttributeErrorMessage('message', 'Message sending failed');
                 return false;
             }
        }
        else{
            $this->setAttributeErrorMessage('message', 'Account is inactive or terminated');
            $this->close();
            return false;
        }
         
    }
    
    public function isForgotPassowrd() {
        Mirage::loadModels(array('AccountsModel','AuditTrailModel'));
        $accountsModel = new AccountsModel();
        $result = $accountsModel->checkemail($this->email); //check if email exist

        if(!isset($result['UserName'])) {
            $this->setAttributeErrorMessage('message', 'Email Address does not exists');
            $this->close();
            return false;
        }
        if($result['Status'] == 1 || $result['Status'] == 6){
            $auditTrailModel = new AuditTrailModel();

            $vusername = $result['UserName'];
            $this->username = $result['UserName'];
            $isemailexist = $result['count'];   
            $aid = $accountsModel->getaid($this->username); //get account ID
            $time = Date("m-d-y h:i:s");
            $newhashedpass = sha1("temppassword".$time);
            $accountsModel->temppassword($newhashedpass, $this->username, $this->password);
//            $accountsModel->temppassword($newhashedpass, $aid, $this->username, $this->email);
            $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
            $date = $this->getDate();
            $sessionid = session_id();

            $subject = 'Forgot Password';
            $body = $this->_getChangePassEmailContent(
                    $time,
                    Mirage::app()->createUrl('updatepassword',array(
                        'aid'=>$aid,
                        'username'=>urlencode($this->username),
                        'password'=>urlencode($newhashedpass)
                        )
                    ), 
                    $this->username,
                    $newhashedpass,
                    $subject);

            $to = preg_replace("/[0-9]+$/", '', $this->email);

    //        if($this->testMailer('poskronusadmin@philweb.com.ph', 'poskronusadmin',$to, $subject, $body)) {
    //            $_SESSION['notification'] = 'Your password has been sent to you through your email';
    //            return true;
    //        } else {
    //            $this->setAttributeErrorMessage('message', 'Message sending failed');
    //            return false;
    //        }

            $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
            $sentEmail = mail($to,$subject, $body, "From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html");
             if (isset($aid)){
            $auditTrailModel->logToAudit($sessionid, $aid, $vusername, $date, $ipaddress, 63);
                    }

             // Check if message is sent or not
             if($sentEmail == 1){
                 $_SESSION['notification'] = 'Your password has been sent to you through your email';
                 return true;
             }
             else{
                 $msg = "Message sending failed";
                 $this->setAttributeErrorMessage('message', 'Message sending failed');
                 return false;
             }
        }
        else{
            $this->setAttributeErrorMessage('message', 'Account is inactive or terminated');
            $this->close();
            return false;
        }
    }
    
    
    
    public function isChangePassword() {
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $date = $this->getDate();
        $sessionid = session_id();
        $vusername = $this->username;
        Mirage::loadModels(array('AccountsModel', 'AuditTrailModel'));
        $accountsModel = new AccountsModel();
        $auditTrailModel = new AuditTrailModel();
        $result = $accountsModel->checkusernameandemail($this->username, $this->email);
        if(!$result['count']) {
                $this->setAttributeErrorMessage('message', 'Email and username did not match');
            $this->close();
            return false;
        }
        if($result['Status'] == 1 || $result['Status'] == 6){
            $aid = $accountsModel->getaid($this->username);
            $time = Date("m-d-y h:i:s");
            $newhashedpass = sha1("temppassword".$time);
            $accountsModel->temppassword($newhashedpass, $this->username, $this->password);
//            $accountsModel->temppassword($newhashedpass, $aid, $this->username, $this->password);

            $subject = 'Change Password';
            $body = $this->_getChangePassEmailContent(
                    $time,
                    Mirage::app()->createUrl('updatepassword',array(
                        'aid'=>$aid,
                        'username'=>urlencode($this->username),
                        'password'=>urlencode($newhashedpass)
                        )
                    ), 

                    $this->username,
                    $newhashedpass,
                    $subject);

            $to = preg_replace("/[0-9]+$/", '', $this->email);

    //        if($this->testMailer('poskronusadmin@philweb.com.ph', 'poskronusadmin',$to, $subject, $body)) {
    //            $_SESSION['notification'] = 'Your password has been sent to you through your email';
    //            return true;
    //        } else {
    //            $this->setAttributeErrorMessage('message', 'Message sending failed');
    //            return false;
    //        }

            $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
            $sentEmail = mail($to,$subject, $body, "From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html");
           if (isset($aid)){
            $auditTrailModel->logToAudit($sessionid, $aid, $vusername, $date, $ipaddress, 64);
                    }
             // Check if message is sent or not
             if($sentEmail == 1){
                 $_SESSION['notification'] = 'Your password has been sent to you through your email';
                 return true;
             }
             else{
                 $msg = "Message sending failed";
                 $this->setAttributeErrorMessage('message', 'Message sending failed');
                 return false;
             }
        }
        else{
            $this->setAttributeErrorMessage('message', 'Account is inactive or terminated');
            $this->close();
            return false;
        }
        
    }
    
    public function updatePassword() {
        Mirage::loadModels(array('AccountsModel','AuditTrailModel'));
        $date = $this->getDate();
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        
        session_regenerate_id();
        $new_sessionid = session_id();        
        
        $accountsModel = new AccountsModel();
        $auditTrailModel = new AuditTrailModel();
        
        $vhashpassword= sha1($this->newpassword);
        //check if txtusername and txtoldpassword exists
        $result = $accountsModel->updatepwd($this->username, htmlentities($this->password));
        if(!$result) {
            $this->setAttributeErrorMessage('message', 'Username or password does not exist');
            return false;
        }

//        $result = $accountsModel->checkAID($this->username, htmlentities($this->password));
//        if(!$result) {
//            $this->setAttributeErrorMessage('message', 'Username or password does not exist');
//            return false;
//        }
//        
//        //Check if the new password is among the list of last 5 passwords of the account
//        $isOldPassword = $accountsModel->checkifrecentpassword($result['AID'],$vhashpassword);
//        if(!$isOldPassword){
//            $this->setAttributeErrorMessage('message', 'Password cannot be used.');
//            return false;
//        }
        
        //update changepassword field  and password field   
        $updatedrow = $accountsModel->resetpassword($vhashpassword, $this->username, $this->aid);
        if($updatedrow) {
            $_SESSION['notification'] = 'Success in updating password';
            $transdetails = Mirage::app()->param['sys_version'];
            if (isset($this->aid)){
            $auditTrailModel->logToAudit($new_sessionid, $this->aid, $transdetails, $date, $ipaddress, '3'); //insert in audittrail      
                }
            return true;
        }
        return false;      
    }
    
    private function _getForgotUsernameEmailContent($username) {
          return $message = "
                           <html>
                           <head>
                                   <title>Forgot Username</title>
                           </head>
                           <body>
                                <i>Hi </i> ,
                                <br/><br/>
                                    Your username is <b>$username</b>
                                <br />
                                    For further inquiries, please call our Customer Service hotline at telephone numbers (02) 3383388 or toll free from
                                    PLDT lines 1800-10PHILWEB (1800-107445932)
                                    or email us at <b>customerservice@philweb.com.ph</b>.
                                <br/><br/>
                                    Thank you and good day!
                                <br/><br/>
                                Best Regards,<br/>
                                PhilWeb Customer Service Team
        
                            <br /><br />
                            <p>This email and any attachments are confidential and may also be
                            privileged.  If you are not the addressee, do not disclose, copy,
                            circulate or in any other way use or rely on the information contained
                            in this email or any attachments.  If received in error, notify the
                            sender immediately and delete this email and any attachments from your
                            system.  Any opinions expressed in this message do not necessarily
                            represent the official positions of PhilWeb Corporation. Emails cannot
                            be guaranteed to be secure or error free as the message and any
                            attachments could be intercepted, corrupted, lost, delayed, incomplete
                            or amended.  PhilWeb Corporation and its subsidiaries do not accept
                            liability for damage caused by this email or any attachments and may
                            monitor email traffic.</p>        
                            </body>
                         </html>";
    }
    
    private function _getChangePassEmailContent($time,$servername,$vusername,$newhashedpass,$title) {
        return $message = "
                       <html>
                       <head>
                               <title>Change Password</title>
                       </head>
                       <body>
                            <i>Hi $vusername</i>,
                            <br/><br/>
                                Your password has been reset on $time.
                            <br/><br/>
                                It is advisable that you change your password upon log-in.
                            <br/><br/>
                                Please click through the link provided below to log-in to your account.
                            <br/><br/>

                            <div>
                                <b><a href=\"".$servername."\">".$title."</a></b>
                            </div>
                            <br />
                                For further inquiries, please call our Customer Service hotline at telephone numbers (02) 3383388 or toll free from
                                PLDT lines 1800-10PHILWEB (1800-107445932)
                                or email us at <b>customerservice@philweb.com.ph</b>.
                            <br/><br/>
                                Thank you and good day!
                            <br/><br/>
                            Best Regards,<br/>
                            PhilWeb Customer Service Team
                            
                            <br /><br />
                            <p>This email and any attachments are confidential and may also be
                            privileged.  If you are not the addressee, do not disclose, copy,
                            circulate or in any other way use or rely on the information contained
                            in this email or any attachments.  If received in error, notify the
                            sender immediately and delete this email and any attachments from your
                            system.  Any opinions expressed in this message do not necessarily
                            represent the official positions of PhilWeb Corporation. Emails cannot
                            be guaranteed to be secure or error free as the message and any
                            attachments could be intercepted, corrupted, lost, delayed, incomplete
                            or amended.  PhilWeb Corporation and its subsidiaries do not accept
                            liability for damage caused by this email or any attachments and may
                            monitor email traffic.</p>                            

                        </body>
                     </html>";
    }
    
    /**
     * email content for cashier first login
     * @author elperez
     * @param type $title
     * @param type $sitename
     * @param type $posaccno
     * @param type $cashiername
     * @param type $cashieruname
     * @param type $dateformat
     * @return string message 
     */
    private function _sendCashierFirstLogin($title, $sitename, $posaccno, $cashiername, $cashieruname, $dateformat)
    {
         return $message = "
                         <html>
                           <head>
                                  <title>$title</title>
                           </head>
                           <body>
                                <br/><br/>
                                    $title
                                <br/><br/>
                                    Site Name: $sitename
                                <br /><br />
                                    POS Account Number: $posaccno
                                <br/><br/>
                                    Cashier Username: $cashieruname
                                <br/><br/>
                                    Cashier Fullname: $cashiername
                                <br/><br/>
                                    Date of First Accessed: $dateformat
                                <br/><br/>   
                                    <p>This email and any attachments are confidential and may also be
                                            privileged.  If you are not the addressee, do not disclose, copy,
                                            circulate or in any other way use or rely on the information contained
                                            in this email or any attachments.  If received in error, notify the
                                            sender immediately and delete this email and any attachments from your
                                            system.  Any opinions expressed in this message do not necessarily
                                            represent the official positions of PhilWeb Corporation. Emails cannot
                                            be guaranteed to be secure or error free as the message and any
                                            attachments could be intercepted, corrupted, lost, delayed, incomplete
                                            or amended.  PhilWeb Corporation and its subsidiaries do not accept
                                            liability for damage caused by this email or any attachments and may
                                            monitor email traffic.
                                    </p>   
                            </body>
                          </html>";
    }
    
    public function testMailer($from,$fromName,$to,$subject,$body) {
        Mirage::loadComponents('mailer/EMailer');
        
        $mailer = new EMailer();
        $mailer->IsSMTP();
        $mailer->IsHTML();
        $mailer->SMTPAuth = true;
        $mailer->Host = 'ssl://smtp.gmail.com';
        $mailer->Port = 465;
        $mailer->Username = 'fittingvirtual@gmail.com';
        $mailer->Password = 'zero1932';
        $mailer->From = $from;
        $mailer->FromName = $fromName;
        $mailer->AddReplyTo($from);
        $mailer->Subject = $subject;
        $mailer->AddAddress($to);
        $mailer->Body = $body;
        if($mailer->Send()) {
            return true;
        } else {
            return false;
        }
    }
}