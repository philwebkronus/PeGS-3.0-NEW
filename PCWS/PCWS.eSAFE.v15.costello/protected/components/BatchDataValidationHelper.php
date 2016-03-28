<?php


/**
 * Description of Batch Data Validation
 *
 * @author Jeremiah D. Lachica
 * @date September 22, 2014
 * @functions
 *  - isAllNotEmpty
 *  - isAllNumeric
 *  - isAllAlphaNumeric
 *  - isAllAlpha
 *  - isAllAlphaWithSpaces
 *  - isAllAlphaNumericWithSpaces
 *  - isAllEmail
 *  - IsNullOrEmpty
 * 
 */


class BatchDataValidationHelper{
    /*
     * @params array: $arr;
     * @return boolean: true or false;
     * @author Jeremiah D. Lachica
     * @desc This function performs a batch validation to check if sets of data are NOT Empty. 
     *       If one data is empty, it will return false. If ALL data are not empty, it will return true.
     */
    public function isAllNotEmpty($arr){
        $Errors=0;
        try{
            foreach($arr as $value){
                if($this->isNullOrEmpty($value) || ctype_space($value)){$Errors++;}
            }
        }
        catch (Exception $e){}
        return $this->validity($Errors);
    }
    
    /*
     * @params array: $arr;
     * @return boolean: true or false;
     * @author Jeremiah D. Lachica
     * @desc This function performs a batch validation to check if sets of data contain numeric character(s). 
     *       If one data contains other types of character, it will return false. Otherwise, it will return true.
     
    public function isAllNumeric($arr){
        $Errors=0;
        foreach($arr as $value){
            if(!is_numeric(trim($value))){$Errors++;}
        }
        return $this->validity($Errors);
    }
    */
    
    /*
     * @params array: $arr;
     * @return boolean: true or false;
     * @author Jeremiah D. Lachica
     * @desc This function performs a batch validation to check if sets of data contain Alphanumeric character(s). 
     *       If one data contains other types of character, it will return false. Otherwise, it will return true.
     
    public function isAllAlphaNumeric($arr){
        $Errors=0;
        foreach($arr as $value){
        if(!ctype_alnum(trim($value))){$Errors++;}
        }
        return $this->validity($Errors);
    }
    
    */
    /*
     * @params array: $arr;
     * @return boolean: true or false;
     * @author Jeremiah D. Lachica
     * @desc This function performs a batch validation to check if sets of data contain Alpha character(s). 
     *       If one data contains other types of character, it will return false. Otherwise, it will return true.
     
    public function isAllAlpha($arr){
        $Errors=0;
        foreach($arr as $value){
            if(!ctype_alpha(trim($value))){$Errors++;}
        }
        return $this->validity($Errors);
    }
    */
    /*
     * @params array: $arr;
     * @return boolean: true or false;
     * @author Jeremiah D. Lachica
     * @desc This function performs a batch validation to check if sets of data contain Alpha with space character(s). 
     *       If one data contains other types of character, it will return false. Otherwise, it will return true.
     
    public function isAllAphaWithSpaces($arr){
        $Errors=0;
        foreach($arr as $value){
            if(!ctype_alpha(str_replace(' ','',trim($value)))){$Errors++;}
        }
        return $this->validity($Errors);
    }
    */
    /*
     * @params array: $arr;
     * @return boolean: true or false;
     * @author Jeremiah D. Lachica
     * @desc This function performs a batch validation to check if sets of data contain numeric character(s). 
     *       If one data contains other types of character, it will return false. Otherwise, it will return true.
     
    public function isAllAlphaNumericWithSpaces($arr){
        $Errors=0;
        foreach($arr as $value){
            if(!ctype_alnum(str_replace(' ','',trim($value)))){$Errors++;}
        }
        return $this->validity($Errors);
    }
    */
    /*
     * @params array: $arr;
     * @return boolean: true or false;
     * @author Jeremiah D. Lachica
     * @desc This function performs a batch validation to check if sets of data contain Email character(s). 
     *       If one data contains other types of character, it will return false. Otherwise, it will return true.
     
    public function isAllEmail($arr){
        $Errors=0;
        foreach($arr as $value){
            if(!filter_var(trim($value), FILTER_VALIDATE_EMAIL)){$Errors++;}
        }
        return $this->validity($Errors);
    }
    */
    /*
     * @params string: $value;
     * @return boolean: true or false;
     * @author Jeremiah D. Lachica
     * @desc This function performs validation to check if data is Null Or Empty. 
     *       If data is Null or Empty, it will return true. Else, it will return false.
     */
    public function isNullOrEmpty($value){
        $isNullOrEmpty=true;
        if($value!==null && $value!==''){$isNullOrEmpty=false;}
        return $isNullOrEmpty;
    }
//  
    /*
     * @params string: $value;
     * @return boolean: true or false;
     * @author Jeremiah D. Lachica
     * @desc This function performs validation to check if submitted parameter contains number of errors.
     */
    private function validity($Errors){
        $isValid = false;
        if($Errors===0){$isValid=true;}
        return $isValid;
    }
   
    
    
}
