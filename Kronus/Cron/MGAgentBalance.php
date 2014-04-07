<?php

/*
 * Cron for MG Playing Balance, send email alert if MG threshold 
 * is less than or equal to 100k
 */

include_once "init.php";
require_once 'helper/common.class.php';
include "Autoemail.class.php";
include_once 'CasinoAPIHandler.class.php';
ini_set('display_errors',true);
ini_set('log_errors',true);

$omg= new Autoemail ($_DBConnectionString[0]);
$vconnected = $omg->open();
if($vconnected)
{    
    $vserverId = 9;
    //get all agentsession
    $getagent = $omg->getallagent();
    $vctr = 0;
    while($vctr < count($getagent))
    {
        $vagentid = $getagent[$vctr]['ServiceAgentID'];
        $vagentsite = $omg->getAgentSite($vagentid);
        $vsiteinfo = $omg->getSiteInfo($vagentsite['SiteID']);
        $vsessionGUID = $getagent[$vctr]['ServiceAgentSessionID'];        
        $vconfiguration = array( 'URI' => $_ServiceAPI[$vserverId-1],
                        'isCaching' => FALSE,
                        'isDebug' => TRUE,
                        'sessionGUID' => $vsessionGUID,
                        'currency' => $_MicrogamingCurrency );       
        //get the Site Threshold(Min Balance) of a particular site 
        $sitebcf = $omg->getsitethreshold($vagentsite['SiteID']);
        $vBCF  = $sitebcf['MinBalance'];
        
        $vCasinoAPIHandler = new CasinoAPIHandler( CasinoAPIHandler::MG, $vconfiguration );
        if ( (bool)$vCasinoAPIHandler->IsAPIServerOK() )
        {
            $vsitecode = $vsiteinfo['SiteCode'];
            $vpos = $vsiteinfo['POSAccountNo'];
            $vdate = date("m/d/Y h:i:s A");           
            $vagentbal = $vCasinoAPIHandler->GetMyBalance();
            $vcount = 0;
            
            
            $mgagentbal = $vagentbal["GetMyBalanceResult"]["MemberBalances"]["MemberBalance"]["Amount"];
            $thresholdamt = number_format($mgagentbal, 2, '.', ','); // format the amount
            $formatamt = str_replace(',', '', $thresholdamt); //remove the comma
            $mgthreshold = floatval($formatamt); //convert to float
            
            $sitebalance = number_format($vBCF, 2, '.', ','); //format the site threshold amount
            $formatamt1 = str_replace(',', '', $sitebalance); // remove the comma
            $sitethreshold = floatval($formatamt1); //convert to float
            
            
            //send email alert only of MG threshold is less than site threshold (MinBalance)
            if($mgthreshold < $sitethreshold)
            {
                while ($vcount < count($groupemaildb))
                {
                    $to = $group2[$vcount]; //email each balances to top-up for their reference
                    $subject = 'Alert - MG Agent Balance';
                    $message = "
                     <html>
                       <head>
                               <title>$subject</title>
                       </head>
                       <body>

                            <br/><br/>
                                *ALERT: Information*
                            <br/><br/>
                                <b>MG Agent Balance</b>
                            <br/><br/>
                                <b>DETAILS</b>
                            <br/><br/>
                                --------------------------------------------------------------------------------------------- 
                            <br/><br/>
                                Site : $vsitecode
                            <br/><br/>
                                POS Account Number : $vpos 
                            <br/><br/>
                                Agent ID: $vagentid                   
                            <br/><br/>
                                Amount: PHP ".$thresholdamt."
                            <br/><br/>                                    
                                Date/Time : $vdate                                                   
                            <br/><br/>                            
                        </body>
                      </html>";
                    $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";         
                    $sentEmail = mail($to, $subject, $message, $headers);  
                    $vcount++;
                }
            }
        }
        $vctr++;
    }
}
$omg->close();
?>
