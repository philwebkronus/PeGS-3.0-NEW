<?php

class BaseObject
{
	var $errormessage;
        /**
         * @var Boolean Returns true if the object has an Error 
         */
        var $HasError = false;
	private $arrErrors;
        
	protected function BaseObject()
	{
	}
	
        /**
         * Returns the Error Message
         * @return String The errormessage 
         */
	function getError()
	{
            return $this->errormessage;
	}
	
	function getErrors()
	{
            return $this->arrErrors;
	}
	
        /**
         * Sets the Error Message
         * @param String $errmessage The Error Message
         */
	function setError($errmessage)
	{
            if (isset($errmessage) && $errmessage !='')
            {
                $this->HasError = true;
		$this->errormessage = $errmessage;
		$this->arrErrors[] = $errmessage;
                App::SetErrorMessage($errmessage);
            }
	}
	
	function AddErrors($arrErr)
	{
            if (isset($arrErr) && count($arrErr) > 0)
            {
		$this->arrErrors[] = $arrErr;
            }
	}


}

?>