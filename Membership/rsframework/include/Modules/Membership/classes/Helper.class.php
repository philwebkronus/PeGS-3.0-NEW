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
        
        $mailer->Body = "Dear $name,<br />";
                
        $mailer->Body .= "<br />Thank you for signing up! This is to inform you that your User Account has been successfully created on this date ".date('m/d/Y',time())." and time (".date('H:i:s',time())."). Your Temporary Account Code is $tempcode. Present this code and one (1) government-issued ID at any e-Games cafe near you. ";
        $mailer->Body .= "<br /><br />To verify your account, please click this link http://".$_SERVER['HTTP_HOST']."/verify.php?email=$email&tempcode=$tempcode";
        $mailer->Body .= "<br />To read the Terms & Conditions, please click this link _________.";
        $mailer->Body .= "<br />To locate the e-Games cafes near you, please click this link ________. ";
        $mailer->Body .= "<br /><br />Please be advised that your Temporary Account Code will be activated only after 24 hours.";
        $mailer->Body .= "<br />For inquiries, please call our 24-hour Customer Service Hotlines at (02) 338-3388 / Toll Free 1800-10-7445932. You can also send an email to our Customer Service Team at customerservice@philweb.com.ph.";
        $mailer->Body .= "<br /><br />Regards,";
        $mailer->Body .= "<br />e-Games";
        
        $mailer->From = "membership@egamescasino.ph";
        $mailer->FromName = "Philweb Membership";
        $mailer->Host = "localhost";
        $mailer->Subject = "E-Games Membership";
        $mailer->Send();
    }
    
    public static function removeDash($str){
        return str_replace("-", "", $str);
    }
    
}
?>
