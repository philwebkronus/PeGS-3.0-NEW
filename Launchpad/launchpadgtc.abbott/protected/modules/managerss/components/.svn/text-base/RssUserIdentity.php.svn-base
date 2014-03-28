<?php

/**
 * User identity for managerss module
 * @package application.modules.managerss.components
 * @author Bryan Salazar
 */
class RssUserIdentity extends CUserIdentity{
    
    // Status not equal to 1
    const ERROR_INACTIVE_ACCOUNT = 3;
    
    // LoginAttempts > 3
    const ERROR_BLOCK_ACCOUNT = 4;
    
    // ForChangePassword = 0
    const ERROR_FOR_CHANGE_PASSWORD = 5;
    
    /**
     * ID of user
     * @var int 
     */
    public $aid;
    
    /**
     *
     * @return bool
     */
    public function authenticate() {
        $row = RssAccounts::model()->isLogin($this->username,  $this->password);
        if(!$row) {
            $this->errorCode=self::ERROR_UNKNOWN_IDENTITY;
        } else {
            if($row['UserName'] != $this->username)
                $this->errorCode=self::ERROR_USERNAME_INVALID;
            elseif($row['Password'] != sha1($this->password))
                $this->errorCode=self::ERROR_PASSWORD_INVALID;
            else {
                if($row['Status'] == 0)
                    $this->errorCode=self::ERROR_INACTIVE_ACCOUNT;
                elseif($row['LoginAttempts'] > 3)
                    $this->errorCode=self::ERROR_BLOCK_ACCOUNT;
                elseif($row['ForChangePassword'] == 0) {
                    $this->sendMail(
                        $this->removeNumbersInEndOfEmailAdd($row['Email']),
                        'Change Password',
                        $this->emailContent(
                            Yii::app()->createAbsoluteUrl('/managerss/auth/changepassword',array('username'=>urlencode($row['UserName']),'oldpassword'=>urlencode($row['Password']))),
                            $row['UserName'],
                            'Change Password'
                        )
                    );
                    Yii::app()->request->redirect(Yii::app()->createAbsoluteUrl('/managerss/auth/changepassword',array('username'=>urlencode($row['UserName']),'oldpassword'=>urlencode($row['Password']))));
                
                } else {
                    $this->errorCode=self::ERROR_NONE;
                    $this->aid = $row['AID'];
                }    
            }
        }
        return !$this->errorCode;
    }
    
    /**
     * Helper method. Remove the numbers in right side of email address
     * @param string $emailAddress
     * @return string 
     */
    protected function removeNumbersInEndOfEmailAdd($emailAddress) {
        return preg_replace("/[0-9]+$/", '', $emailAddress);
    }
    
    /**
     * Mailer for host with mail server and no mail server
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return boolean true if successfully send email 
     */
    protected function sendMail($to,$subject,$body) {
        $from = RssConfig::app()->params['from'];
        $fromName = RssConfig::app()->params['from_name'];
        
        // for host without mail server
        if(YII_DEBUG) {
            Yii::import('application.modules.managerss.extensions.phpmailer.JPhpMailer');
            $mailer = new JPhpMailer;
            $mailer->IsSMTP();
            $mailer->IsHTML();
            $mailer->SMTPAuth = true;
            $mailer->Host = 'ssl://smtp.gmail.com';
            $mailer->Port = 465;
            $mailer->Username = 'fittingvirtual@gmail.com';
            $mailer->Password = 'zero1932';
            $mailer->From = $from;
            $mailer->FromName = $fromName;
            $mailer->AddReplyTo($from);
            $mailer->Subject = $subject;
            $mailer->AddAddress($to);
            $mailer->Body = $body;
            if($mailer->Send()) {
                return true;
            } else {
                return false;
            }
            
        // for host with mail server    
        } else {
            $from='From: ' . $from . "\r\nContent-type:text/html";
            return mail($to,$subject, $body, $from);
        }
    }
    
    
    /**
     * Helper method. Content of email
     * @param string $link
     * @param string $username
     * @param string $title
     * @return string 
     */
    protected function emailContent($link,$username,$title) {
        return $message = "
                       <html>
                       <head>
                               <title>Change Password</title>
                       </head>
                       <body>
                            <i>Hi $username</i>,
                            <br/><br/>
                                Your password has been reset on " . Date("m-d-y h:i:s") . ".
                            <br/><br/>
                                It is advisable that you change your password upon log-in.
                            <br/><br/>
                                Please click through the link provided below to log-in to your account.
                            <br/><br/>

                            <div>
                                <b><a href=\"".$link."\">".$title."</a></b>
                            </div>
                            <br />
                                For further inquiries, please call our Customer Service hotline at telephone numbers (02) 3383388 or toll free from
                                PLDT lines 1800-10PHILWEB (1800-107445932)
                                or email us at <b>customerservice@philweb.com.ph</b>.
                            <br/><br/>
                                Thank you and good day!
                            <br/><br/>
                            Best Regards,<br/>
                            PhilWeb Customer Service Team
                            
                            <br /><br />
                            <p>This email and any attachments are confidential and may also be
                            privileged.  If you are not the addressee, do not disclose, copy,
                            circulate or in any other way use or rely on the information contained
                            in this email or any attachments.  If received in error, notify the
                            sender immediately and delete this email and any attachments from your
                            system.  Any opinions expressed in this message do not necessarily
                            represent the official positions of PhilWeb Corporation. Emails cannot
                            be guaranteed to be secure or error free as the message and any
                            attachments could be intercepted, corrupted, lost, delayed, incomplete
                            or amended.  PhilWeb Corporation and its subsidiaries do not accept
                            liability for damage caused by this email or any attachments and may
                            monitor email traffic.</p>                            

                        </body>
                     </html>";
    }
}
