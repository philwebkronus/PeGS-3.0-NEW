<?php

/**
 * Form model for login page
 * @package application.modules.managerss.models
 * @author Bryan Salazar
 */
class LoginForm extends CFormModel{
    public $username;
    public $password;
    public $oldpassword;
    public $newpassword;
    public $confirmpassword;
    
    private $_identity;
    
    /**
     * Set validation for each attributes
     * @return array 
     */
    public function rules() {
        return array(
            array('username,oldpassword,confirmpassword,newpassword','required'),
            array('password','application.modules.managerss.validators.LoginValidator'),
            array('newpassword','length','min'=>RssConfig::app()->params['min_password_length']),
            array('password', 'authenticate'),
            array('confirmpassword','compare','operator'=>'==','compareAttribute'=>'newpassword','message'=>'New password and Confirm password should be the same'),
        );
    }
    
    /**
     * Set the label for attributes
     * @return array 
     */
    public function attributeLabels() {
        return array(
            'oldpassword'=>'Old Password',
            'confirmpassword'=>'Confirm Password',
            'newpassword'=>'New Password'
        );
    }
    
    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate($attribute,$params)
    {
        if(!$this->hasErrors()) {
                $this->_identity=new RssUserIdentity($this->username,$this->password);
                if(!$this->_identity->authenticate())
                        $this->addError('password','Incorrect username or password.');
        }
    }
    
    /**
     * Login the identity of user
     * @return boolean 
     */
    public function login()
    {
        if($this->_identity===null) {
            $this->_identity=new RssUserIdentity($this->username,$this->password);
            $this->_identity->authenticate();
        }
        if($this->_identity->errorCode===RssUserIdentity::ERROR_NONE)
        {
            $duration=3600*24*30; // 30 days
            Yii::app()->user->login($this->_identity,$duration);
            Yii::app()->user->setState('aid', $this->_identity->aid);
            
            RssAccountSessions::model()->login($this->_identity->aid);
            return true;
        }
        else
            return false;
    }
}
