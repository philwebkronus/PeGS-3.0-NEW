<?php

/**
 * Controller for authentication
 * @package application.modules.managerss.controllers
 * @author Bryan Salazar
 */
class AuthController extends Controller{
    
    /**
     * Set default action
     * @var string 
     */
    public $defaultAction = 'login';
    
    /**
     * Set default layout
     * @var string 
     */
    public $layout = 'main';
    
    /**
     * Action for login
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        
        // check if post request
        if(isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            
            // check if input is valid and authenticate
            if($model->validate(array('username','password')) && $model->login()) {
                $this->redirect(Yii::app()->createUrl('/managerss/rss/overview'));
            }
        }
        
        // check if ajax request but came from redirect
        if(isset($_GET['isajax']) && $_GET['isajax'] == 1)
            $this->renderPartial('auth_login',array('model'=>$model));
        else
            $this->render('auth_login',array('model'=>$model));
    }
    
    /**
     * Action for change password
     */
    public function actionChangepassword()
    {
        $model = new LoginForm();
        if(isset($_GET['oldpassword']) && isset($_GET['username'])) {
            $model->oldpassword = $_GET['oldpassword'];
            $model->username = $_GET['username'];
        }
        if(isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            if($model->validate(array('username','oldpassword','newpassword','confirmpassword'))) {
                if(RssAccounts::model()->resetPassword(urldecode($model->username), $model->newpassword, urldecode($model->oldpassword))) {
                    Yii::app()->user->setFlash('success', "Success in updating password ");
                } else {
                    Yii::app()->user->setFlash('success', "Failed in updating password ");
                }
                $this->redirect(Yii::app()->createUrl('managerss/auth/login'));
            }
        }
        $this->render('auth_changepassword',array('model'=>$model));
    }
    
    /**
     * Action for logout
     */
    public function actionLogout()
    {
        RssAccountSessions::model()->delete(Yii::app()->user->getState('aid'));
        Yii::app()->user->logout();
        $this->redirect(RssConfig::app()->params['homeUrl']);
    }
}
