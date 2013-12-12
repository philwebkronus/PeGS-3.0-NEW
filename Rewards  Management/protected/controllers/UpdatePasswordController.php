<?php
/**
 * Update Password Controller
 * @Author Mark Kenneth Esguerra
 * @DateCreated September 30, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
class UpdatePasswordController extends Controller
{
    public $Username;
    public $TempPass;
    public $dialogmsg;
    public $dialogshow = false;
    public $dialogshow2 = false;
    public $dialogtitle;
    
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
    
    public function actionIndex()
    {
        $model      = new UpdatePasswordModel();
        $validation = new Validations();
        $audittrail = new AuditTrailModel();
        //Get the query strings in the URL
        $this->Username = $_GET['username'];
        $this->TempPass = $_GET['password'];
        
        //Check if the update password form is submitted
        if (isset($_POST['UpdatePasswordModel']))
        {
            $model->attributes = $_POST['UpdatePasswordModel'];
            
            $username       = $model->Username;
            $oldpassword    = $model->TempPass;
            $newpassword    = $model->NewPassword;
            $confirmpass    = $model->ConfirmPassword;
            //Check if username and the old password are valid
            $isValid = $model->checkUsernamePassword($username, $oldpassword);
            if ($isValid)
            {
                //Check if fields are blank
                if ($username == "" || $oldpassword == "" || $newpassword == ""
                    || $confirmpass == "")
                {
                    $this->dialogmsg = "Please fill up all fields.";
                    $this->dialogshow = true;
                    $this->dialogtitle = "ERROR MESSAGE";
                }
                //Check if the password has valid length
                else if (strlen($newpassword) < 8)
                {
                    $this->dialogmsg = "Invalid Password. Minimum of 8 alphanumeric.";
                    $this->dialogshow = true;
                    $this->dialogtitle = "ERROR MESSAGE";
                }
                //Check if the passwords have valid characters
                else if (!$validation->validatePassword($newpassword) && !$validation->validatePassword($confirmpass))
                {
                    $this->dialogmsg = "Invalid Password. Allowed characters are alphanumeric and the special characters such as _%*+-!$=#.:?/&";
                    $this->dialogshow = true;
                    $this->dialogtitle = "ERROR MESSAGE";
                }
                //Check if new password is equal to confirm password
                else if ($newpassword != $confirmpass)
                {
                    $this->dialogmsg = "Password not match.";
                    $this->dialogshow = true;
                    $this->dialogtitle = "ERROR MESSAGE";
                }
                else
                {
                    //Update Password
                    $result = $model->updatePassword(sha1($newpassword), $username);
                    
                    if ($result['TransCode'] == 1)
                    {
                        $this->dialogtitle = "SUCCESS MESSAGE";
                        $this->dialogshow2 = true;
                        //Log event to audit trail
                        $audittrail->logEvent(RefAuditFunctionsModel::CHANGE_PASSWORD, "Username: ".$username, array('SessionID' => Yii::app()->session['SessionID'], 'AID' => Yii::app()->session['PartnerPID']));
                    }
                    else if ($result['TransCode'] == 2)
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogshow = true;
                    }
                    $this->dialogmsg = $result['TransMsg'];
                }
                    
            }
            else
            {
                $this->dialogmsg = "Invalid Username or Password.";
                $this->dialogtitle = "ERROR MESSAGE";
                $this->dialogshow = true;
            }

        }
        $this->render('index', array('model' => $model));
    }
            
}
?>
