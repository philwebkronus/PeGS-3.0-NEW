<?php

/**
 * Description of EmailValidator
 *
 * @author Bryan Salazar
 */
class EmailValidator extends MI_Validator{
    public function rules($model, $attribute, $options = array()) {
        $value = $model->$attribute;
        
//        if(!filter_var($value, FILTER_VALIDATE_EMAIL)) 
//            throw new Exception('Invalid Email Address');
        
        $isvalid = $this->check_email_address($value);
        if(!$isvalid)
            throw new Exception('Invalid Email Address');
    }
    
    /**
     * validates if email address is valid, 
     * permits if number was appended on the last part of the email / added (02-20-12)
     * @link http://www.linuxjournal.com/article/9585
     * @param string email
     * @return boolean true or false
     */
    private function check_email_address($email) 
    {
          // First, we check that there's one @ symbol, 
          // and that the lengths are right.
          if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
            // Email invalid because wrong number of characters 
            // in one section or wrong number of @ symbols.
            return false;
          }
          
          // Split it into sections to make life easier
          $email_array = explode("@", $email);
          $local_array = explode(".", $email_array[0]);
          for ($i = 0; $i < sizeof($local_array); $i++) 
          {
            if(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&↪'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", 
                    $local_array[$i])) {
              return false;
            }
          }
          
          return true;
   }
}
