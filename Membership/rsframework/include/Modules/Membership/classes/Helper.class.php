<?php

/*
 * @author : owliber
 * @date : 2013-04-17
 */

class Helper extends BaseEntity
{
    function Helper()
    {
        $this->ConnString = 'membership';
    }
        
    public function getParameterValue( $paramName )
    {
        $this->TableName = "ref_parameters";
        $where = " WHERE ParamName = '$paramName' ";
        $result = parent::SelectByWhere($where);
        
        return $result[0]['ParamValue'];
    }
    
    public function sendEmailVerification( $email, $name, $tempcode)
    {
        App::LoadCore("PHPMailer.class.php");
        $mailer = new PHPMailer();
                        
        $mailer->AddAddress($email, $name);
        $mailer->IsHTML(true);
        
        $mailer->Body = "<html><body>Dear <label style='font-style: italic;'>$name</label>,<br />";
                
        $mailer->Body .= "<p style='text-align: justify; text-justify: inter-word;'><br />Thank you for signing up! This is to inform you that your User Account has been successfully created on this date ".date('m/d/Y',time())." and time (".date('H:i:s',time())."). Your Temporary Account Code is $tempcode. Present this code and one (1) government-issued ID at any e-Games cafe near you. ";
        $mailer->Body .= "<br /><br />To verify your account, please click this link http://".$_SERVER['HTTP_HOST']."/verify.php?email=$email&tempcode=$tempcode";
        $mailer->Body .= "<br />To read the Terms & Conditions, please click this link http://www.philweb.com.ph/.";
        $mailer->Body .= "<br />To locate the e-Games cafes near you, please click this link http://www.philweb.com.ph/. ";
        $mailer->Body .= "<br /><br />Please be advised that your Temporary Account Code will be activated only after 24 hours.";
        $mailer->Body .= "<br />For inquiries, please call our 24-hour Customer Service Hotlines at (02) 338-3388 / Toll Free 1800-10-7445932. You can also send an email to our Customer Service Team at customerservice@philweb.com.ph.";
        $mailer->Body .= "<br /><br />Regards,";
        $mailer->Body .= "<br />e-Games</p></body></html>";
        
        $mailer->From = "membership@egamescasino.ph";
        $mailer->FromName = "E-Games Membership";
        $mailer->Host = "localhost";
        $mailer->Subject = "E-Games Membership";
        $mailer->Send();
    }
    
    public function sendEmailForgotPassword($email, $name, $hashedubcards){
        App::LoadCore("PHPMailer.class.php");
        $mailer = new PHPMailer();
                        
        $mailer->AddAddress($email, $name);
        $mailer->IsHTML(true);
        
        $mailer->Body = "Hi <label style='font-style: italic;'>$name</label>,<br />";
                
        $mailer->Body .= "<p style='text-align: justify; text-justify: inter-word;'><br />Your password has been reset on ".date('m-d-Y',time())." ".date('H:i:s',time()).".";
        $mailer->Body .= "<br /><br />It is advisable that you change your password upon log-in. ";
        $mailer->Body .= "<br /><br />Please click through the link provided below to log-in to your account. ";
        $mailer->Body .= "<br /><br /><b><a href='http://".$_SERVER['HTTP_HOST']."/changepassword.php?CardNumber=$hashedubcards'>Forgot Password</a></b> ";
        $mailer->Body .= "<br /><br />For inquiries, please call our 24-hour Customer Service Hotlines at (02) 338-3388 / Toll Free 1800-10-7445932. You can also send an email to our Customer Service Team at <b>customerservice@philweb.com.ph</b>.";
        $mailer->Body .= "<br /><br />Thank you and good day! ";
        $mailer->Body .= "<br /><br />Best Regards, ";
        $mailer->Body .= "<br />PhilWeb Customer Service Team";
        $mailer->Body .= "<br /><br />This email and any attachments are confidential and may also be privileged. If you are not the addressee, do not disclose, copy, circulate or in any other way use or rely on the information contained in this";
        $mailer->Body .= "email or any attachments. If received in error, notify the sender immediately and delete this email and any attachments from your system. Any opinions expressed in this message do not necessarily ";
        $mailer->Body .= "represent the official positions of PhilWeb Corporation. Emails cannot be guaranteed to be secure or error free as the message and any attachments could be intercepted, corrupted, lost, delayed, incomplete";
        $mailer->Body .= "or amended. PhilWeb Corporation and its subsidiaries do not accept liability for damage caused by this email or any attachments and may monitor email traffic.</p>";
        
        $mailer->From = "membership@egamescasino.ph";
        $mailer->FromName = "Philweb Membership";
        $mailer->Host = "localhost";
        $mailer->Subject = "E-Games Membership";
        $mailer->Send();
    }
    
    public static function removeDash($str){
        return str_replace("-", "", $str);
    }
    
    /**
     * Reference method for card types
     * @author Kenneth
     * @date 07-04-13
     * @param int $cardtype
     * @return string
     */
    public static function DetermineCardTypes($cardtype)
    {
        switch ($cardtype)
        {
            case 1: return "Gold Member Card";
                    break;
            case 2: return "Green Member Card";
                    break;
            case 3: return "Temporary Card";
                    break;
            default: return "Invalid Card Type";
                    break;
        }
    }
    
    public static function CheckCardTypeStatus($cardTypeStatus)
    {
        switch($cardTypeStatus)
        {
            case 0: return "Inactive";
                    break;
            case 1: return "Active";
                    break;
            case 2: return "Deactivated";
                    break;
        }
    }
    public static function DetermineCardStatus($cardstatus)
    {
        switch ($cardstatus)
        {
            case 0: return "Inactive";
                    break;
            case 1: return "Active";
                    break;
            case 2: return "Deactived";
                    break;
            case 5: return "Active Temporary";
                    break;
            case 7: return "New Migrated";
                    break;
            case 8: return "Temporary Migrated";
                    break;
            case 9: return "Banned Card";
        }
    }
    
    /**
     * @author Kenneth
     * @param int $cardstatus card status
     * @return string
     */
    public static function SetErrorMsgForCardStatus($cardstatus)
    {
        switch ($cardstatus)
        {
            case 0: return App::setErrorMessage("Membership Card is Inactive");
                    break;
            case 2: return App::setErrorMessage("Membership Card is Deactived ");
                    break;
            case 7: return App::setErrorMessage("Membership Card is New Migrated ");
                    break;
            case 8: return App::setErrorMessage("Membership Card is Temporary Migrated");
                    break;
            case 9: return App::setErrorMessage("Membership Card is Banned");
                    break;
            default: return App::setErrorMessage("Membership Card is Invalid");
                    break;
        }
    }
}
?>
