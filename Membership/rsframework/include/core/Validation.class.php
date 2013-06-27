<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 02 4, 11
 * Company: Philweb
 *****************************/
class Validation extends BaseObject
{
    var $ControlArray;
    var $ValidationMessages;
    var $ValidationTypeArray;

    function Validation()
    {
        $this->ValidationTypeArray = array("email", "number", "alpha", "alphanumeric");
    }

    function AddControl($control, $validationtype)
    {
        $con["control"] = $control;
        $con["type"] = $validationtype;
        $con["result"] = true;
        $this->ControlArray[] = $con;
    }

    function Validate()
    {
        $retval = true;
        for ($i = 0; $i < count($this->ControlArray); $i++)
        {
            $con = $this->ControlArray[$i];
            $control = $con["control"];
            $validationtype = $con["type"];

            if ($validationtype == "email")
            {
                $result = $this->validateEmail($control->Text);
                $con["result"] = $result;
                if (!$result)
                {
                    $con["message"] = "Invalid Email";
                    $this->ValidationMessages[] = $con["message"];
                }
            }

            if ($validationtype == "number")
            {
                $result = $this->validateNumber($control->Text);
                $con["result"] = $result;
                if (!$result)
                {
                    $con["message"] = "Invalid Number";
                    $this->ValidationMessages[] = $con["message"];
                }
            }

            if ($validationtype == "alpha")
            {
                $result = $this->validateAlpha($control->Text);
                $con["result"] = $result;
                if (!$result)
                {
                    $con["message"] = "Invalid Alpha Value";
                    $this->ValidationMessages[] = $con["message"];
                }
            }

            if ($validationtype == "alphanumeric")
            {
                $result = $this->validateAlphaNumeric($control->Text);
                $con["result"] = $result;
                if (!$result)
                {
                    $con["message"] = "Invalid Alpha-numeric Value";
                    $this->ValidationMessages[] = $con["message"];
                }
            }

            if (!$result)
            {
                $retval = false;
            }

            $this->ControlArray[$i] = $con;

        }
        return $retval;
    }

    function validateEmail($input)
    {
        //$result = ereg ("^[^@ ]+@[^@ ]+\.[^@ \.]+$", $input); //deprecated 
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/'; 
        if (preg_match($regex, $input))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function validateNumber($input)
    {
        if (is_numeric($input))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function validateAlpha($input)
    {
        $result = ereg ("^[A-Za-z]+$", $input);
        if ($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    function validateAlphaSpaceDashAndDot($input)
    {
        $result = preg_match ("/^[A-Za-z\s-.]+$/", $input);
        if ($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function validateAlphaNumeric($input)
    {
        $result = preg_match ("/^[A-Za-z0-9]+$/", $input);
        if ($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


}
?>
