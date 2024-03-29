<?php

class LoginController extends Controller
{
    public $showDialog;
    public $dialogMsg;
    
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
            return array(
                    // captcha action renders the CAPTCHA image displayed on the contact page
                    'captcha'=>array(
                            'class'=>'CCaptchaAction',
                            'backColor'=>0xFFFFFF,
                    ),
                    // page action renders "static" pages stored under 'protected/views/site/pages'
                    // They can be accessed via: index.php?r=site/page&view=FileName
                    'page'=>array(
                            'class'=>'CViewAction',
                    ),
            );
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
            // renders the view file 'protected/views/site/index.php'
            // using the default layout 'protected/views/layouts/main.php'
            $model = new LoginForm;
            $this->render('login',array('model'=>$model));
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
            if($error=Yii::app()->errorHandler->error)
            {
                    if(Yii::app()->request->isAjaxRequest)
                            echo $error['message'];
                    else
                            $this->render('error', $error);
            }
    }

    /**
     * Displays the login page
     * @modified Mark Kenneth Esguerra
     * @DateModified 2013-11-26 (@by: aqdepliyan)
     */
    public function actionLogin()
    {
        $model          = new LoginForm;
        $accountform    = new AccountForm();
        $sessionmodel   = new SessionForm();
        $partner        = new PartnersModel();
        $partnersession = new PartnerSessionModel();
        $refpartner     = new RefPartnerModel();
        $audittrail = new AuditTrailModel();
        
        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
        {
                echo CActiveForm::validate($model);
                Yii::app()->end();
        }
        $this->showDialog = false;

        // collect user input data
        if(isset($_POST['LoginForm']))
        {
            $model->attributes=$_POST['LoginForm'];
            
            //Implement XSS and Referrer
            $data =  Yii::app()->request->urlReferrer;
            $referrer1 = Yii::app()->params['referrer'];
            $referrer2 = Yii::app()->params['referrer2'];
            $defaultcontroller = Yii::app()->defaultController;
            $isValid = true;

            $chunks1 = preg_split('/\/\//', $data);
            $chunks2 = preg_split('/\/\//', $referrer1);
            $chunks3 = preg_split('/\/\//', $referrer2);
            $urlchunks1 = explode("/", $chunks1[1]);
            $urlchunks2 = explode("/", $chunks2[1]);
            $urlchunks3 = explode("/", $chunks3[1]);
            $controllerchunks = explode("/", $defaultcontroller);

            //Check if the url includes the default controller using "?r="
            if(isset($urlchunks1[1]) && $urlchunks1 != ""){
                $checkchunk = preg_match('/\?r\=/', $urlchunks1[1]);
                if($checkchunk){
                    $rechunk = preg_split('/\?r\=/', $urlchunks1[1]);
                    $urlchunks1[1] = $rechunk[0];
                    $urlchunks1[2] = $rechunk[1];
                }
            }

            if($chunks1[0] == $chunks2[0] || $chunks1[0] == $chunks3[0]){ 			//Check if http/http is same with referrer URL
                if($urlchunks1[0] == $urlchunks2[0] && $urlchunks1[0] == $urlchunks3[0]){ 		//Check Domain Name if same
                        if(isset($urlchunks1[1]) && $urlchunks1[1] != ""){				//Check for index.php if exists
                                if($urlchunks1[1] == $urlchunks2[1] || $urlchunks1[1] == $urlchunks3[1]){ 		//Check if config referrer has index.php
                                        if(isset($urlchunks1[2]) && $urlchunks1[2] != ""){			//Check if default controller is included in URL
                                                if(isset($urlchunks1[3]) && $urlchunks1[3] != ""){ 			//Check if default controller method is included in URL
                                                        if($urlchunks1[3] == $controllerchunks[1]){
                                                                $isValid = true;
                                                        } else { $isValid = false; }
                                                } else if($urlchunks1[2] == $controllerchunks[0]){
                                                        $isValid = true;
                                                } else { $isValid = false; }
                                        } else { $isValid = true; }
                                } else { $isValid = false; }
                        } else { $isValid = true; }
                } else { $isValid = false; }
            } else { $isValid = false; }    
            
            if(!$isValid){
                header('HTTP/1.0 403 Forbidden');
                $this->showDialog = true;
                $this->dialogMsg = "Forbidden";
                $this->render('login', array('model' => $model));
                Yii::app()->end();
            }
            
            $username = $model->UserName;
            $password = $model->Password;

            $usernamelen = strlen($username);
            $passwordlen = strlen($password);
            //Check if the username entered is partner or other account
            //otherwise username is invalid
            $acct = $accountform->checkUsername($username);
            $countacct = count($acct);
            $this->showDialog = false;
            if($countacct > 0)
            {
                foreach ($acct as $row) {
                    $aid = $row['AID'];
                    $username = $row['UserName'];
                    $accounttypeid = $row['AccountTypeID'];
                }
                //Check if has access rights

                $attempts = $accountform->getLoginAttempts($aid);

                foreach ($attempts as $row2) {
                    $oldnumattempts = $row2['LoginAttempts'];
                }
                $numattempts = $oldnumattempts + 1;

                if($passwordlen >= 8)
                {
                    $acctpass = $accountform->checkPassword($password);
                    $countacctpass = count($acctpass);

                    if($countacctpass > 0)
                    {

                        if($oldnumattempts < 3)
                        {
                            if($model->login()){
                                $numattempts = 0;
                                $accountform->updateLoginAttempts($aid, $numattempts);

                                $landingpage = SiteMenu::getLandingPage($accounttypeid);

                                if(!empty($landingpage)){

                                    $session = new CHttpSession;
                                    $session->open();
                                    $session->clear();

                                    $session_id = session_id();
                                    $session->setSessionID($session_id);

                                    $sessionmodel->checkSession($aid);
                                    if (empty($sessionmodel->aid)) {
                                        $sessionmodel->addSession($aid, $session_id);
                                    } else {
                                        $sessionmodel->updateSession($aid, $session_id);
                                    }

                                    Yii::app()->session['AID'] = $aid;
                                    Yii::app()->session['SessionID'] = $session_id;
                                    Yii::app()->session['AccountType'] = $accounttypeid;
                                    Yii::app()->session['UserName'] = $username;

                                    //Log event to audit trail
                                    $audittrail->logEvent(RefAuditFunctionsModel::LOGIN, "Username: ".Yii::app()->session['UserName'], array('SessionID' => Yii::app()->session['SessionID'], 'AID' => Yii::app()->session['AID']));
                                    $this->redirect(array($landingpage));
                                    
                                } else {
                                    $this->showDialog = true;
                                    $this->dialogMsg = "User has no access right, Please try again";
                                }

                            } else {
                                $accountform->updateLoginAttempts($aid, $numattempts);
                                $this->showDialog = true;
                                $this->dialogMsg = "Invalid Username or Password, Please try again";  
                                $transdetails = "Login Attempt/s Count: ".$numattempts;
                                
                                //Log event to audit trail
                                $audittrail->logEvent(RefAuditFunctionsModel::LOGIN_INVALID_ATTEMPTS, $transdetails, array('SessionID' => "", 'AID' => ""));
                            }
                            
                        } else {
                            if($oldnumattempts == 3){
                                $this->showDialog = true;
                                $this->dialogMsg = "Access Denied.Please contact system administrator to have your account unlocked.";
                                $transdetails = "Login Attempt/s Count: ".$oldnumattempts;
                                    
                                //Log event to audit trail
                                $audittrail->logEvent(RefAuditFunctionsModel::LOGIN_INVALID_ATTEMPTS, $transdetails, array('SessionID' => "", 'AID' => ""));
                                
                            } else if($oldnumattempts < 3) {
                                $accountform->updateLoginAttempts($aid, $numattempts);
                                $this->showDialog = true;
                                $this->dialogMsg = "Access Denied.Please contact system administrator to have your account unlocked.";
                                $transdetails = "Login Attempt/s Count: ".$numattempts;

                                //Log event to audit trail
                                $audittrail->logEvent(RefAuditFunctionsModel::LOGIN_INVALID_ATTEMPTS, $transdetails, array('SessionID' => "", 'AID' => ""));
                            }   
                        }

                    } else {
                        if($oldnumattempts == 3){

                            $this->showDialog = true;
                            $this->dialogMsg = "Access Denied.Please contact system administrator to have your account unlocked.";
                            $transdetails = "Login Attempt/s Count: ".$oldnumattempts;

                            //Log event to audit trail
                            $audittrail->logEvent(RefAuditFunctionsModel::LOGIN_INVALID_ATTEMPTS, $transdetails, array('SessionID' => "", 'AID' => ""));
                            
                        } else if($oldnumattempts < 3) {
                            $accountform->updateLoginAttempts($aid, $numattempts);

                            $this->showDialog = true;
                            $this->dialogMsg = "Invalid Username or Password, Please try again";
                            $transdetails = "Login Attempt/s Count: ".$numattempts;

                            //Log event to audit trail
                            $audittrail->logEvent(RefAuditFunctionsModel::LOGIN_INVALID_ATTEMPTS, $transdetails, array('SessionID' => "", 'AID' => ""));
                        }        
                    } 
                    
                } else {
                    $accountform->updateLoginAttempts($aid, $numattempts);
                    $this->showDialog = true;
                    $this->dialogMsg = "Please enter your password. Minimum of 8 alphanumeric.";
                    $transdetails = "Login Attempt/s Count: ".$numattempts;

                    //Log event to audit trail
                    $audittrail->logEvent(RefAuditFunctionsModel::LOGIN_INVALID_ATTEMPTS, $transdetails, array('SessionID' => Yii::app()->session['SessionID'], 'AID' => Yii::app()->session['PartnerPID']));
                }

            }
            //Check for Partner Access
            else if (count($partner->checkUsername($username)) > 0)
            {
                //Get partner details
                $result = $partner->checkUsername($username);

                foreach ($result as $rows)
                {
                    $partnerpid = $rows['PartnerPID'];
                    $accounttypeID = $rows['AccountTypeID'];
                    $username = $rows['UserName'];
                }

                //Check if password reach the minimum length
                if (strlen($password) >= 8)
                {
                    //Check if password exist
                    $check = $partner->checkPassword($partnerpid, $password);
                    if (count($check) > 0)
                    {
                        //Check if partner is active
                        //Blocked partner if inactive
                        $status = $refpartner->checkIfActive($partnerpid);
                        if ($status == 1)
                        {
                            //Login the partner
                            if ($model->loginPartner())
                            {
                                //Get Landing Page
                                $landingpage = SiteMenu::getLandingPage($accounttypeID);  
                                if(!empty($landingpage)){
                                    //Session
                                    $session = new CHttpSession;
                                    $session->open();
                                    $session->clear();

                                    $session_id = session_id();
                                    $session->setSessionID($session_id);
                                    //Check if partner has session
                                    $check = $partnersession->checkSession($partnerpid);
                                    if (count($check) > 0)
                                    {
                                        //Update Session
                                        $result = $partnersession->updateSession($partnerpid, $session_id);
                                        if ($result['TransCode'] == 0)
                                        {
                                            $this->showDialog = true;
                                            $this->dialogMsg = $result['TransMsg'];
                                        }
                                        
                                    } else {
                                        //Add Session
                                        $result = $partnersession->addSession($partnerpid, $session_id);
                                        if ($result['TransCode'] == 0)
                                        {
                                            $this->showDialog = true;
                                            $this->dialogMsg = $result['TransMsg'];
                                        }
                                    }
                                    //Set Session Variables
                                    Yii::app()->session['PartnerPID'] = $partnerpid;
                                    Yii::app()->session['SessionID'] = $session_id;
                                    Yii::app()->session['AccountType'] = $accounttypeID;
                                    Yii::app()->session['UserName'] = $username;
                                    
                                    //Update DateLastLogin for partners
                                    $result = $partner->updateLastLogin($partnerpid);
                                    if ($result)
                                    {
                                        //Log event to audit trail
                                        $audittrail->logEvent(RefAuditFunctionsModel::LOGIN, "Username: ".Yii::app()->session['UserName'], array('SessionID' => Yii::app()->session['SessionID'], 'AID' => Yii::app()->session['PartnerPID']));
                                        $this->redirect(array($landingpage)); //redirect
                                    }
                                    else
                                    {
                                        $this->showDialog = true;
                                        $this->dialogMsg = "An error occured while updating the partners record";
                                    }
                                    
                                } else {
                                    $this->showDialog = true;
                                    $this->dialogMsg = "User has no access right, Please try again";
                                }
                            } else {
                                $this->showDialog = true;
                                $this->dialogMsg = "Invalid Username or Password, Please try again";  
                            }
                        } else {
                            $this->showDialog = true;
                            $this->dialogMsg = "Partner status is Inactive";  
                        }
                    } else {
                        $this->showDialog = true;
                        $this->dialogMsg = "Invalid Username or Password, Please try again";
                    }
                } else {
                    $this->showDialog = true;
                    $this->dialogMsg = "Please enter your password. Minimum of 8 alphanumeric.";
                }
            } else {
                $this->showDialog = true;
                $this->dialogMsg = "Invalid Username or Password, Please try again";  
            }
        }
        
        // display the login form
        $this->render('login',array('model'=>$model));
    }
    
    public function actionCheckReferrer() {
        if(isset($_POST['ajax'])) {

            if(Yii::app()->param['referrer'] != $_SERVER['HTTP_REFERER']) {
                header('HTTP/1.0 403 Forbidden');
                Yii::app()->end();
                $option = '';

                //$referrer = $_SERVER['HTTP_REFERER'];
                //echo $referrer;
                //return $header;
            }
            else {
                $option = 'Authorized';
            }
            echo $option;
            
        } 
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        $aid = Yii::app()->session['AID'];
        $sessionmodel = new SessionForm();
        $audittrail = new AuditTrailModel();
            
        //Log event to audit trail  
        $audittrail->logEvent(RefAuditFunctionsModel::LOGOUT, "Username: ".Yii::app()->session['UserName'], array('SessionID' => Yii::app()->session['SessionID'], 'AID' => Yii::app()->session['AID']));
        $sessionmodel->deleteSession($aid);
        Yii::app()->user->logout();

        $this->redirect(Yii::app()->homeUrl);
    }
    
    /**
     * Log out event for partner
     */
    public function actionLogoutpartner()
    {
        $partnerpid = Yii::app()->session['PartnerPID'];
        $sessionmodel = new PartnerSessionModel();
        $audittrail = new AuditTrailModel();
        
        //Log event to audit trail  
        $audittrail->logEvent(RefAuditFunctionsModel::LOGOUT, "Username: ".Yii::app()->session['UserName'], array('SessionID' => Yii::app()->session['SessionID'], 'AID' => Yii::app()->session['AID']));
        $result = $sessionmodel->deleteSession($partnerpid);
        //Check if has error
        if ($result['TransCode'] == 0)
        {
            $this->dialogMsg = $result['TransMsg'];
            $this->showDialog = true;
        }
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }
    
}

   