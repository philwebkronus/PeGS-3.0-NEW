<?php
Mirage::loadModels('LoginFormModel');
Mirage::loadLibraries('util');
@session_start();
/**
 * Description of LoginController
 *
 * @author bryan
 */
class AuthenticateController extends MI_Controller{
    
    public $layout = 'layout/login_layout';
    public $title = 'Login Page';
    public $legend = '';
    
    /**
     * Description: Login page and post back
     */
    public function loginAction() {
        $this->legend = 'POS Kronus - Login';
        $error = '';
        
        Mirage::loadModels(array('SitesModel'));             
        $sitesModel = new SitesModel();  
        $loginForm = new LoginFormModel();
        if(isset($_POST['LoginFormModel'])) {
            $loginForm->setAttributes($_POST['LoginFormModel']);
            if($loginForm->isValid(array('username','password')) && $loginForm->authenticate()) {
                if(isset($_SESSION['expired_pass'])) {
                    $this->redirect($this->createUrl('updatepassword',array('aid'=>$loginForm->aid,'username'=>$loginForm->username,'password'=>sha1($loginForm->password))));
                } else {
                    if(isset($_SESSION['haspasskey']))
                        $this->redirect($this->createUrl('passkey'));
                    else{
                        $menu = $sitesModel->getMenu($_SESSION['AccountSiteID']);
                        $esafetab = $menu['ESafeTab'];
                        if($esafetab != 1){
                            $this->redirect($this->createUrl('viewtrans/history'));
                        } else {
                            $this->redirect($this->createUrl('forcet'));
                        }
                    }
                }
            } else {
                $error = $loginForm->getAttributeErrorMessage('message');
            }
        }
        $this->render('authenticate_login',array('loginForm'=>$loginForm,'error'=>$error));
    }
    
    /**
     * Description: Call through ajax after login page display to store machine info
     */
    public function storeMachineInfoAction() {
        if($this->isAjaxRequest() && $this->isPostRequest()) {
            if($_POST['macid'] == '') {
                header('HTTP/1.0 404 Not Found');
                Mirage::app()->end();
            }
            $_SESSION['scpuid'] = $_POST['cpuid'];
            $_SESSION['scpuname'] = $_POST['cpuname'];
            $_SESSION['sbiosid'] = $_POST['biosid'];
            $_SESSION['smbid'] = $_POST['mbid'];
            $_SESSION['sosid'] = $_POST['osid'];
            $_SESSION['smacid'] = $_POST['macid'];
            $_SESSION['sipid'] = gethostbyaddr($_SERVER['REMOTE_ADDR']) ;
            $_SESSION['sguid'] = $this->_guid();
//            $_SESSION['smachineid'] = sha1($_SESSION['sosid'].$_POST['oscaption'].$_POST['ossignature']);
            $_SESSION['smachineid'] = sha1($_SESSION['scpuname'].$_SESSION['sbiosid'].$_SESSION['smbid'].$_SESSION['sosid'].$_SESSION['smacid'].$_POST['oscaption'].$_POST['ossignature']);
            
        } else {
            Mirage::app()->error404();
        }
    }
    
    /**
     * Description: Display page asking for passkey and post back
     */
    public function passKeyAction() {
        $this->legend = 'Access Passkey';
        if(!isset($_SESSION['haspasskey'])) {
            $this->redirect($this->createUrl('logout'));
        }    
        
        Mirage::loadModels(array('SitesModel'));             
        $sitesModel = new SitesModel();  
        $loginForm = new LoginFormModel();
        if(isset($_POST['LoginFormModel'])) {
            $loginForm->setAttributes($_POST['LoginFormModel']);
            if($loginForm->isValid(array('passkey')) && $loginForm->authenticatePasskey()) {
                $menu = $sitesModel->getMenu($_SESSION['AccountSiteID']);
                $esafetab = $menu['ESafeTab'];
                if($esafetab != 1){
                    $this->redirect($this->createUrl('viewtrans/history'));
                } else {
                    $this->redirect($this->createUrl('forcet'));
                }
            } else {
//                if($loginForm->getAttributeErrorMessage('message')) {
//                    $_SESSION['error_message'] = $loginForm->getAttributeErrorMessage('message');
//                }
                $this->redirect($this->createUrl('login',array('error'=>$loginForm->getAttributeErrorMessage('message'))));
            }
        }
        $this->render('authenticate_passkey',array('loginForm'=>$loginForm));
    }
    
    
    /**
     * Description: clear session in file and database
     */
    public function logoutAction() {
        $loginForm = new LoginFormModel();
        $loginForm->logout();
        $this->redirect($this->createUrl('login'));
    }
    
    public function forgotWizardAction() {
        $this->legend = 'Experiencing Problems?';
        $this->render('authenticate_forgotwizard');
    }
    
    public function changePasswordAction() {
        $this->legend = 'Change Password';
        $loginForm = new LoginFormModel();
        if(isset($_POST['LoginFormModel'])) {
            $loginForm->setAttributes($_POST['LoginFormModel']);
            if($loginForm->isValid(array('email','username')) && $loginForm->isChangePassword()) {
                //redirect to login page with message
                $this->redirect($this->createUrl('login'));
            }
        }
        $this->render('authenticate_changepass',array('loginForm'=>$loginForm));
    }
    
    public function updatePasswordAction() {
        $this->legend = 'Update Password';
        $loginForm = new LoginFormModel();
        $error = '';
        if(isset($_POST['LoginFormModel'])) {
            $loginForm->setAttributes($_POST['LoginFormModel']);
            if($loginForm->isValid(array('username','password','newpassword','confirmpassword')) && $loginForm->updatePassword()) {
                $this->redirect($this->createUrl('login'));
            }
        } else {
            if(!isset($_GET['username']) || !isset($_GET['password']) || !isset($_GET['aid']) || $_GET['username'] == '' || $_GET['password'] == '' || $_GET['aid'] == '') {
                $this->redirect($this->createUrl('login'));
            }
            if(isset($_SESSION['expired_pass'])) {
                $error = 'Your password has been expired. Please update your password';
            }
            
            $loginForm->username = $_GET['username'];
            $loginForm->password = $_GET['password'];
            $loginForm->aid = $_GET['aid'];
        }
        
        $this->render('authenticate_updatepass',array('loginForm'=>$loginForm,'error'=>$error));
    }
    
    public function forgotPasswordAction() {
        $this->legend = 'Forgot Password';
        $loginForm = new LoginFormModel();
        if(isset($_POST['LoginFormModel'])) {
            $loginForm->setAttributes($_POST['LoginFormModel']);
            if($loginForm->isValid(array('email')) && $loginForm->isForgotPassowrd()) {
                $this->redirect($this->createUrl('login'));
            }
        }
        
        $this->render('authenticate_forgotpass',array('loginForm'=>$loginForm));
    }
    
    public function forgotUsernameAction() {
        $this->legend = 'Forgot Username';
        $loginForm = new LoginFormModel();
        if(isset($_POST['LoginFormModel'])) {
            $loginForm->setAttributes($_POST['LoginFormModel']);
            if($loginForm->isValid(array('email')) && $loginForm->isForgotUsername()) {
                $this->redirect($this->createUrl('login'));
            }
        }
        $this->render('authenticate_forgotuser',array('loginForm'=>$loginForm));
    }
    
    public function checkReferrerAction() {
        if($this->isAjaxRequest() && $this->isPostRequest()) {

            if(Mirage::app()->param['referrer'] != $_SERVER['HTTP_REFERER']) {
                header('HTTP/1.0 403 Forbidden');
                //Mirage::app()->end();
                $option = 'Forbidden';

                //$referrer = $_SERVER['HTTP_REFERER'];
                //echo $referrer;
                //return $header;
            }
            else {
                $option = 'Authorized';
            }
            echo $option;
//            if($_POST['hidreferrer'] == '') {
//                header('HTTP/1.0 404 Not Found');
//                Mirage::app()->end();
//            }
//            $_SESSION['scpuid'] = $_POST['cpuid'];
//            $_SESSION['scpuname'] = $_POST['cpuname'];
//            $_SESSION['sbiosid'] = $_POST['biosid'];
//            $_SESSION['smbid'] = $_POST['mbid'];
//            $_SESSION['sosid'] = $_POST['osid'];
//            $_SESSION['smacid'] = $_POST['macid'];
//            $_SESSION['sipid'] = gethostbyaddr($_SERVER['REMOTE_ADDR']) ;
//            $_SESSION['sguid'] = $this->_guid();
////            $_SESSION['smachineid'] = sha1($_SESSION['sosid'].$_POST['oscaption'].$_POST['ossignature']);
//            $_SESSION['smachineid'] = sha1($_SESSION['scpuname'].$_SESSION['sbiosid'].$_SESSION['smbid'].$_SESSION['sosid'].$_SESSION['smacid'].$_POST['oscaption'].$_POST['ossignature']);
            
        } else {
            Mirage::app()->error404();
        }
    }
    
    //create guid to be used in cashier terminal credential
    private function _guid() {
        if (function_exists('com_create_guid'))
        {
            return com_create_guid();
        }
        else
       {
             mt_srand((double)microtime()*10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = chr(123)
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);
            return $uuid;
        }
    }    
}