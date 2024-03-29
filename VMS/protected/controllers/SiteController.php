<?php

class SiteController extends VMSBaseIdentity {

    public $showDialog = false;
    public $dialogMsg;

    /**
     * Declares class-based actions.
     */
    public function actions() {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex() {
        // renders the view file 'protected/views/site/index.php'
        // using the default layout 'protected/views/layouts/main.php'
        $this->render('index');
        //echo "&nbsp;";
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            //Log error
            $transDetails = ' from user ' . Yii::app()->user->getId() . ' (Site:' . Yii::app()->user->getSiteID() . ') using URL ' . Yii::app()->request->requestUri;
            AuditLog::logTransactions(30, $transDetails);

            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    /**
     * Displays the login page
     */
    public function actionLogin() {
        if (!Yii::app()->user->isGuest) {
            //$this->redirect(Yii::app()->homeUrl);
            $this->redirect(array(Yii::app()->session['homeUrl']));
        } else {
            $model = new LoginForm();

            // if it is ajax validation request
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
                echo CActiveForm::validate($model);
                Yii::app()->end();
            }

            // collect user input data
            if (isset($_POST['LoginForm'])) {
                $model->attributes = $_POST['LoginForm'];

                //Implement anti-xss and referrer
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
                // validate user input and redirect to the previous page if valid
                if ($model->validate() && $model->login()) {
                    
                    // If login is successfull, reset login attempts to 0
                    $username = $_POST['LoginForm']['UserName'];
                    $update_attempts = new LoginForm();
                    $update_attempts->updateLoginAttemptsByUsername($username, 0);
              
                    //Log to audit trail
                    AuditLog::logTransactions(1, ' as ' . $model->UserName);

                    //Redirect to default page set on the access rights
                    $this->redirect(array(Yii::app()->session['homeUrl']));
                } else if ($model->noaccess == true) {
                    $this->showDialog = true;
                    $this->dialogMsg = "No access rights found for this user";
                    Yii::app()->user->logout();
                }
                
                else
                {
                    // Get Login Attempts and Display Appropriate Error Message
                    $username = $_POST['LoginForm']['UserName'];
                    
                    $update_attempts = new LoginForm();
                    $get_attempts = new LoginForm();
                    $num_attempts = $get_attempts->getLoginAttemptsByUsername($username);
                    $int_attempts = intval($num_attempts);
                    
                    if($int_attempts == 2)
                    {
                        $int_attempts = $int_attempts + 1;
                        $update_attempts->updateLoginAttemptsByUsername($username, $int_attempts);
                        $update_status = new AccountTypes();
                        $update_status->changeStatusByUserName($username);
                        
                        //Prompt Lock Account
                        $this->showDialog = true;
                        $this->dialogMsg = "Access Denied. Please contact system administrator to have your account unlocked.";
                  
                    }
                    else if($int_attempts >= 0 && $int_attempts <= 3)
                    {
                        if($int_attempts == 3)
                        {
                            //Prompt Lock Account
                            $this->showDialog = true;
                            $this->dialogMsg = "Access Denied. Please contact system administrator to have your account unlocked.";
                        }
                        else
                        {
                            $int_attempts = $int_attempts + 1;
                            $update_attempts->updateLoginAttemptsByUsername($username, $int_attempts);
                            //Prompt Invalid Password
                            $this->showDialog = true;
                            $this->dialogMsg = "Incorrect username or password.";
                        }
                    }
                }
            }

            // display the login form
            $this->render('login', array('model' => $model));
        }
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        //Log to audit trail
        AuditLog::logTransactions(2, ' user ' . Yii::app()->user->getName());
        if(isset(Yii::app()->session['AID']) && Yii::app()->session['AID'] != '') {
            $aid = Yii::app()->session['AID'];
        } else {
            $aid = 0;
        }
        Yii::app()->user->logout();
        $sessionmodel = new SessionModel();
        $checkSession = $sessionmodel->checkSession($aid);
        if (!empty($checkSession) || $checkSession > 0) {
            $sessionmodel->deleteSession($aid);
        }
        $this->redirect(array(Yii::app()->defaultController));
    }

}
