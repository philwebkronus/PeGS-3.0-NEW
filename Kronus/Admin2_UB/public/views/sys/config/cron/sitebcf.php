<?php
include_once "../../core/init.php";
require_once '../../class/helper/common.class.php';
include "../../class/Autoemail.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);

$obbcf = new Autoemail($_DBConnectionString[0]);
$connected = $obbcf->open();
if($connected)
{
    $rbcf = $obbcf->getsitebcf(); //get all site bcf
   
    if(count($rbcf) > 0)
    {
       foreach($rbcf as $row)
        {
            $rrembalance = $row['Balance'];
            $rreqbalance = $row['reqbal'];            
            //validate bcf if remaining balance is less than the required balance; then email
            if($rrembalance < $rreqbalance)
            {
                $vcount = 0;
                while($vcount < count($groupemail))
                {
                    
                    $to = $groupemail[$vcount]; 
                    $subject = 'PEGS Alert - Station remaining balance already below 20% of the required account balance to operate the game.';
                    $message = '<html>
                                  <head>
                                    <title>'.$subject.'</title>
                                  </head>
                                    <body> 
                                    <br /><br />
                                    ALERT: CRITICAL! 
                                    <br /><br />
                                    Station remaining balance already below 20% of the required account balance to operate the game.
                                    <br /><br />
                                    DETAILS 
                                    <br /><br />
                                    -----------------------------------------------------------------------------
                                    <br /><br />
                                    Account No: '.$row['POS'].'
                                    <br /><br />
                                    Username: '.$row['UserName'].'
                                    <br /><br />
                                    Account Name: '.$row['Name'].''." ".''.$row['SiteName'].'
                                    <br /><br />
                                    Remaining Balance: PHP '.  number_format($rrembalance, 2, '.', ',').'
                                    <br /><br />
                                    Required Balance: PHP '.  number_format($rreqbalance, 2, '.', ',').'
                                    <br /><br />';
                    $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
                    $sentEmail = mail($to, $subject, $message, $headers);    
                    $vcount = $vcount + 1;
                    $obbcf->updatesiteemailalert($row['SiteID']);
                }
            }
        }
    }
}
unset($rbcf,$rrembalance,$rreqbalance,$vcount ,$to,$subject,$message,$headers,$sentEmail);
$obbcf->close();
?>
