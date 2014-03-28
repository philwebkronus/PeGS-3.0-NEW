<?php

/**
 * Validator for login
 * @package application.modules.managerss.validators
 * @author Bryan Salazar
 */
class LoginValidator extends CValidator{
    
    /**
     *
     * @param object $object
     * @param string $attribute 
     */
    protected function validateAttribute($object, $attribute) {
        $value = $object->$attribute;
        
        if($this->isEmpty($value,true)) {
            $message=$this->message!==null?$this->message:Yii::t('yii','{attribute} cannot be blank.');
            $this->addError($object,$attribute,$message);
        }
        
        if($attribute == 'password') {
            $row = RssAccounts::model()->getAccountByUsername($object->username);
            $loginAttempts = (isset($row['LoginAttempts'])?$row['LoginAttempts']:NULL);
            
            // check if loginAttempts not equal to null
            if($loginAttempts !== NULL) {
                
                // check if login attempt is greater than or equal to RssConfig::app()->params['max_login_attempts']
                if($loginAttempts >= RssConfig::app()->params['max_login_attempts']) {
                    $message=$this->message!==null?$this->message:Yii::t('yii','Access Denied.Please contact system administrator to have your account unlocked.');
                    $this->addError($object,$attribute,$message);
                    
                } else {
                    
                    // check if username is not empty and password is empty
                    if(!$this->isEmpty($object->username, true) && $this->isEmpty($value,true)) {
                        RssAccounts::model()->incrementLoginAttempts($loginAttempts,$object->username);
                        $message=$this->message!==null?$this->message:Yii::t('yii','{attribute} cannot be blank.');
                        $this->addError($object,$attribute,$message);

                    // check if username and password not empty    
                    } elseif(!$this->isEmpty($object->username, true) && !$this->isEmpty($value,true)) {
                        
                        // check if password did not match
                        if(sha1($value) !== $row['Password']) {
                            RssAccounts::model()->incrementLoginAttempts($loginAttempts,$object->username);
                            $message=$this->message!==null?$this->message:Yii::t('yii','Incorrect username or password.');
                            $this->addError($object,$attribute,$message);
                            
                        // reset login attempts       
                        } else {
                            if($loginAttempts > 0)
                                RssAccounts::model()->resetLoginAttempts($row['AID']);
                        }
                    }
                }
            }
        }
    }
}
