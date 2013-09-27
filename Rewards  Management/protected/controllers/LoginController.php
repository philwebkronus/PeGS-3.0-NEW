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
	 */
	public function actionLogin()
	{
		$model = new LoginForm;
        $accountform = new AccountForm();
        $sessionmodel = new SessionForm();
        
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
            $username = $model->UserName;
            $password = $model->Password;
            
            $usernamelen = strlen($username);
            $passwordlen = strlen($password);
            
                $acct = $accountform->checkUsername($username);
                $countacct = count($acct);
                $this->showDialog = false;
                if($countacct > 0)
                {
                        foreach ($acct as $row) {
                            $aid = $row['AID'];
                            $accounttypeid = $row['AccountTypeID'];
                        }
                        
                        $session = new CHttpSession;
                        $session->open();
                        $session->clear();
                        
                        $session_id = session_id();
                        $session->setSessionID($session_id);
            
                        $sessionmodel->checkSession($aid);
                        if (empty($sessionmodel->aid)) {
                            $sessionmodel->addSession($aid, $session_id);
                        }
                        else {
                            $sessionmodel->updateSession($aid, $session_id);
                        }

                        Yii::app()->session['AID'] = $aid;
                        Yii::app()->session['SessionID'] = $session_id;
                        Yii::app()->session['AccountType'] = $accounttypeid;
                        
                        $attempts = $accountform->getLoginAttempts($aid);

                        foreach ($attempts as $row2) {
                            $oldnumattempts = $row2['LoginAttempts'];
                        }
                        $numattempts = $oldnumattempts + 1;
                        
                    if($passwordlen >= 8)
                    {
                            $acctpass = $accountform->checkPassword($password);
                            $countacctpass = count($acctpass);

                            if($countacctpass > 0){

                                if($oldnumattempts < 3)
                                {
                                    if($model->login()){
                                        $numattempts = 0;
                                        $accountform->updateLoginAttempts($aid, $numattempts);
                                        
                                        $landingpage = SiteMenu::getLandingPage($accounttypeid);
                                        
                                        if(!empty($landingpage)){
                                            $this->redirect(array($landingpage));
                                        }
                                        else{
                                            $this->showDialog = true;
                                            $this->dialogMsg = "User has no access right, Please try again";
                                        }

                                    }
                                    else{
                                        $this->showDialog = true;
                                        $this->dialogMsg = "Invalid Username or Password, Please try again";  
                                    }
                                }
                                else
                                {
                                    
                                    if($oldnumattempts == 3){
                                        
                                        $this->showDialog = true;
                                        $this->dialogMsg = "Access Denied.Please contact system administrator to have your account unlocked.";
                                    }
                                    else if($oldnumattempts < 3){
                                        $accountform->updateLoginAttempts($aid, $numattempts);
                                        $this->showDialog = true;

                                        $this->dialogMsg = "Access Denied.Please contact system administrator to have your account unlocked.";
                                    }   
                                }

                            }
                            else{

                                if($oldnumattempts == 3){

                                    $this->showDialog = true;
                                    $this->dialogMsg = "Access Denied.Please contact system administrator to have your account unlocked.";
                                }
                                else if($oldnumattempts < 3){
                                    $accountform->updateLoginAttempts($aid, $numattempts);

                                    $this->showDialog = true;
                                    $this->dialogMsg = "Invalid Username or Password, Please try again";
                                }        
                        } 
                    }
                    else{
                        $accountform->updateLoginAttempts($aid, $numattempts);
                        $this->showDialog = true;
                        $this->dialogMsg = "Please enter your password. Minimum of 8 alphanumeric.";
                    }
                    

                }
                else{
                    $this->showDialog = true;
                    $this->dialogMsg = "Invalid Username or Password, Please try again";  
                }
		}
        
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		$aid = Yii::app()->session['AID'];
        $sessionmodel = new SessionForm();
        $sessionmodel->deleteSession($aid);
        Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
    
    
}

   