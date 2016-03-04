<?php

$servername = $_SERVER['HTTP_HOST'];

 $vmessage = "<html>
                           <head>
                                   <title>Password Expired</title>
                           </head>
                           <body>
                                <i>Hi </i>,
                                <br/><br/>
                                    Your password has been expired.
                                <br/><br/>
                                    It is advisable that you change your password upon log-in.
                                <br/><br/>
                                    Please click through the link provided below to log-in to your account.
                                <br/><br/>

                                <div>
                                     <b><a href='http://".$servername."/UpdatePassword.php?username='>Change passwor</a></b>
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
                                This email and any attachments are confidential and may also be
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
                                monitor email traffic.
                            </body>
                         </html>";

									
$vsentEmail = mail("itswebadmin@gmail.com", "Test", $vmessage, "From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html");
                
echo $vsentEmail;

								
?>
