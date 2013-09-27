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
}
?>
