<?php

/**
 * Description of Helpers
 * @date 6-23-2014
 * @author fdlsison
 */

class Helpers
{
    //send automated email for forgot password module
    public function sendEmailForgotPassword($email, $name, $hashedUBCards) {
        $subject = "E-Games Membership";
        $message = "Hi <label style='font-style: italic;'>$name</label>,<br />";
        $message .= "<p style='text-align: justify; text-justify: inter-word;'><br />Your password has been reset on ".date('m-d-Y',time())." ".date('H:i:s',time()).".";
        $message .= "<br /><br />It is advisable that you change your password upon log-in. ";
        $message .= "<br /><br />Please click through the link provided below to log-in to your account. ";
        $message .= "<br /><br /><b><a href='http://".$_SERVER['HTTP_HOST']."/changepassword.php?CardNumber=$hashedUBCards'>Forgot Password</a></b> ";
        $message .= "<br /><br />For inquiries, please call our 24-hour Customer Service Hotlines at (02) 338-3388 / Toll Free 1800-10-7445932. You can also send an email to our Customer Service Team at <b>customerservice@philweb.com.ph</b>.";
        $message .= "<br /><br />Thank you and good day! ";
        $message .= "<br /><br />Best Regards, ";
        $message .= "<br />PhilWeb Customer Service Team";
        $message .= "<br /><br />This email and any attachments are confidential and may also be privileged. If you are not the addressee, do not disclose, copy, circulate or in any other way use or rely on the information contained in this";
        $message .= "email or any attachments. If received in error, notify the sender immediately and delete this email and any attachments from your system. Any opinions expressed in this message do not necessarily ";
        $message .= "represent the official positions of PhilWeb Corporation. Emails cannot be guaranteed to be secure or error free as the message and any attachments could be intercepted, corrupted, lost, delayed, incomplete";
        $message .= "or amended. PhilWeb Corporation and its subsidiaries do not accept liability for damage caused by this email or any attachments and may monitor email traffic.</p>";
        
        $sender = "membership@egamescasino.ph";
        mail($email, $subject, $message, $sender);
    }
    
    //@date 6-24-2014
    //set error message(s) for different card status
    public function setErrorMsgForCardStatus($cardStatus) {
        switch($cardStatus) {
            case 0: return "Membership Card is Inactive";
                    break;
            case 2: return "Membership Card is Deactived ";
                    break;
            case 7: return "Membership Card is Newly Migrated ";
                    break;
            case 8: return "Membership Card is Temporary Migrated";
                    break;
            case 9: return "Membership Card is Banned";
                    break;
            default: return "Membership Card is Invalid";
                    break;
        }
    }
    
    //@date 6-30-2014
    //send automated email for member registration module
    public function sendEmailVerification($email, $name, $tempcode) {
        $subject = "E-Games Membership";
        $message = "<html><body>Dear <label style='font-style: italic;'>$name</label>,<br />";
        $message .= "<p style='text-align: justify; text-justify: inter-word;'><br />Thank you for signing up! This is to inform you that your User Account has been successfully created on this date ".date('m/d/Y',time())." and time (".date('H:i:s',time())."). Your Temporary Account Code is $tempcode. Present this code and one (1) government-issued ID at any e-Games cafe near you. ";
        $message .= "<br /><br />To verify your account, please click this link <b><a href='http://".$_SERVER['HTTP_HOST']."/verify.php?email=$email&tempcode=$tempcode'>http://".$_SERVER['HTTP_HOST']."/verify.php?email=$email&tempcode=$tempcode</a></b>";
        $message .= "<br />To read the Terms & Conditions, please click this link <b><a href='http://www.egamescasino.ph/terms-and-conditions/'>http://www.egamescasino.ph/terms-and-conditions/</a></b>.";
        $message .= "<br />To locate the e-Games cafes near you, please click this link <b><a href='http://www.egamescasino.ph/locations/'>http://www.egamescasino.ph/locations/</a></b>.";
        $message .= "<br /><br />Please be advised that your Temporary Account Code will be activated only after 24 hours.";
        $message .= "<br />For inquiries, please call our 24-hour Customer Service Hotlines at (02) 338-3388 / Toll Free 1800-10-7445932. You can also send an email to our Customer Service Team at customerservice@philweb.com.ph.";
        $message .= "<br /><br />Regards,";
        $message .= "<br />e-Games</p></body></html>";
        
        $sender = "membership@egamescasino.ph";
        mail($email, $subject, $message, $sender);
    }
    
}

?>