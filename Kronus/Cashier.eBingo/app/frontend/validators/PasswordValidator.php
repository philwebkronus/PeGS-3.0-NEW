<?php

/**
 * Description of PasswordValidator
 *
 * @author Bryan Salazar
 */
class PasswordValidator extends MI_Validator {
    
    public function rules($model, $attribute,$options = array()) 
    {
        $time =microtime(true);
        $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);
        $rawdate = new DateTime( date('Y-m-d H:i:s.'.$micro_time, $time) );
        $date = $rawdate->format("Y-m-d H:i:s.u");
        
        Mirage::loadModels(array('AccountsModel'));
        $accountsModel = new AccountsModel();
        
        $value = trim($model->$attribute);
        
        $username = trim($model->$options['user']);
 
        $attempt_count = $accountsModel->queryattempt($username);
        if($attempt_count >= 3) {
            $attempt_count++;
            $accountsModel->updateAttempt($attempt_count, $username);     
            throw new Exception('Access Denied.Please contact system administrator to have your account unlocked.');
        }
        
        if($value == '') {
            $attempt_count++;
            $accountsModel->updateAttempt($attempt_count, $username); 
            if($attempt_count >= 3)
                throw new Exception('Access Denied.Please contact system administrator to have your account unlocked.');
            throw new Exception('Password cannot be empty');
        }
            
        
        if(strlen($value) < $options['min']) {
            $attempt_count++;
            $accountsModel->updateAttempt($attempt_count, $username); 
            if($attempt_count >= 3)
                throw new Exception('Access Denied.Please contact system administrator to have your account unlocked.');            
            throw new Exception('Please enter your password. Minimum of 8 alphanumeric');
        }
            
        
    }
}
