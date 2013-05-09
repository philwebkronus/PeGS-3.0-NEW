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
        $mailer->Body .= "Thank you for registering to Philweb Loyalty Program\n\n";
        $mailer->Body .= "Please keep and use this temporary account code for you to be able to earn points.\n\n";
        $mailer->Body .= "Temporary Account Code: <strong>$tempcode</strong>\n\n";
        $mailer->Body .= "Verify your account by clicking the link below:\n";
        $mailer->Body .= "Link: http://".$_SERVER['HTTP_HOST']."/verify.php?email=$email&tempcode=$tempcode\n\n"; 
        $mailer->Body .= "Thank you.\n";
        $mailer->Body .= "Philweb Corporation";

        $mailer->From = "membership@philweb.com.ph";
        $mailer->FromName = "Philweb Membership";
        $mailer->Host = "localhost";
        $mailer->Subject = "Philweb Membership Verification";
        $mailer->Send();
    }
    
}
?>
