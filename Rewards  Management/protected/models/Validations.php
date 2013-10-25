<?php
/**
 * Validations used in Rewards Item Management
 * @author Mark Kenneth Esguerra
 * @date September 25, 2013
 * @copyright (c) 2013, Philweb
 */
class Validations
{

    /**
     * Check if email is valid.
     * @author mgesguerra 09-20-13
     * @param string $email Email to be validated
     * @return boolean 
     * Returns <b>TRUE</b> if the email is valid, else <b>FALSE</b>
     */
    public function validateEmail($email) 
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    /**
     * Check if the website URL is valid
     * @author mgesguerra 09-20-13
     * @param string $page Website URL to be validated
     * @return boolean
     * Return <b>TRUE</b> if the page is valid, else <b>FALSE</b>
     */
    public function validateWebsite($page)
    {
        $reg_ex = "@^(http\:\/\/|https\:\/\/)?([a-z0-9][a-z0-9\-]*\.)+[a-z0-9][a-z0-9\-]*$@i";
        if (preg_match($reg_ex, $page) == TRUE)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    /**
     * Check if the inputted string has no special characters. <br />
     * Allowed characters are number, letters and space only.
     * @param string $input Inputted string
     * @return boolean 
     * Returns <b>TRUE</b> if theres no special charaters, else, <b>FALSE</b>
     * @author mgesguerra 09-25-13
     */
    public function validateAlphaNumeric($input)
    {
        $result = preg_match ("/^[0-9a-zA-Z\s]+$/", $input);
        if ($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    /**
     * Check if Address format is valid. Allowed characters are (.), (,) <br />
     * (-), (/)
     * @param string $input Inputted address
     * @return boolean
     * @author mgesguerra 09-25-13
     */
    public function validateAddress($input)
    {
        $result = preg_match ("/^[0-9a-zA-Z\s \/\.\-\,]+$/", $input);
        if ($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    /**
     * Check if Password is valid. Allowed characters are <br /> _%*+-!$=#.:?/&
     * @param type $input
     * @return boolean
     * @author mgesguerra 10-02-13
     */
    public function validatePassword($input)
    {
        $result = preg_match ("/^[0-9a-zA-Z\s \_\%\#\.\-\+\!\=\:]+$/", $input);
        if ($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    /**
     * Validate the minumum length of the text depending on field
     * @param text $text The inputted text
     * @param type $field The field to be validated
     * @return boolean Returns <b>TRUE</b> if VALID, FALSE if INVALID
     * @author Mark Kenneth Esguerra
     * @date October 21, 2013
     */
    public function validateMinimum($text, $field)
    {
        $length = strlen($text);
        
        switch ($field)
        {
            case "PartnerName":
                $limit = 5;
                break;
            case "CompanyName":
                $limit = 5;
                break;
            case "PhoneNumber":
                $limit = 7;
                break;
            case "FaxNumber":
                $limit = 7;
                break;
            case "ContactPosition":
                $limit = 5;
                break;
            case "ContactPhone":
                $limit = 7;
                break;
            case "ContactMobile":
                $limit = 11;
                break;
        }
        if ($length < $limit)
            return true;
        else
            return false;
    }
}
?>
