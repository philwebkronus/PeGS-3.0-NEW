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
            
    function GetAccountTypeIDByName($accountName)
    {
        $this->TableName = "ref_accounttypes";
        $where = " WHERE Name = '$accountName'";
        $result = parent::SelectByWhere($where);
        return $result[0]['AccountTypeID'];
    }
    
    function getCardTypeByName ( $cardtypename )
    {
        App::LoadModuleClass("Loyalty", "CardTypes");
        $_CardTypes = new CardTypes();
        $where = " WHERE CardTypeName = '$cardtypename'";
        $result = $_CardTypes->SelectByWhere($where);
        return $result[0]['CardTypeID'];
    }
    
    public function sendEmailVerification( $email, $name, $tempcode)
    {
        App::LoadCore("PHPMailer.class.php");
        $mailer = new PHPMailer();
                        
        $mailer->AddAddress($email, $name);
        $mailer->IsHTML(true);
        
        $mailer->Body = "Dear $name,\n\n";
                
        $mailer->Body .= "Thank you for signing up! This is to inform you that your User Account has been successfully created on this date ".date('m/d/Y',time())." and time (".date('H:i:s',time())."). Your Temporary Account Code is $tempcode. Present this code and one (1) government-issued ID at any e-Games cafe near you. ";
        $mailer->Body .= "\n\nTo verify your account, please click this link http://".$_SERVER['HTTP_HOST']."/verify.php?email=$email&tempcode=$tempcode";
        $mailer->Body .= "\nTo read the Terms & Conditions, please click this link _________.";
        $mailer->Body .= "\nTo locate the e-Games cafes near you, please click this link ________. ";
        $mailer->Body .= "\n\nPlease be advised that your Temporary Account Code will be activated only after 24 hours.";
        $mailer->Body .= "For inquiries, please call our 24-hour Customer Service Hotlines at (02) 338-3388 / Toll Free 1800-10-7445932. You can also send an email to our Customer Service Team at customerservice@philweb.com.ph.";
        $mailer->Body .= "\n\nRegards,";
        $mailer->Body .= "\ne-Games";
        
        $mailer->From = "membership@philweb.com.ph";
        $mailer->FromName = "Philweb Membership";
        $mailer->Host = "localhost";
        $mailer->Subject = "E-GAMES CASINO MEMBERSHIP NOTIFICATION";
        $mailer->Send();
    }
    
}
?>
