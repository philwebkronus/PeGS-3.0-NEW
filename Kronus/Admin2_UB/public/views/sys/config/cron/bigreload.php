<?php
include_once "../../core/init.php";
require_once '../../class/helper/common.class.php';
include "../../class/Autoemail.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);

$obreload = new Autoemail($_DBConnectionString[0]);
$connected = $obreload->open();

if($connected){ 
    
    $vlastcron = $obreload->getcronsched('LastReloadEmailDate');   
    $vresult = $obreload->getbigreload($vlastcron['LastReloadEmailDate']);
    $vbigreloadamt = (float)$vresult['Amount'];
    
    if($vbigreloadamt > 0)
    {
        //check in what group does the big reload amount satisfies
        switch ($vbigreloadamt)
        {
          case (($vbigreloadamt >= $varrbracket["group1"])  &&  ($vbigreloadamt < $varrbracket["group2"])):
              $vbracket = $varrbracket["group1"];
          break;
          case ($vbigreloadamt >= $varrbracket["group2"]):
              $vbracket = $varrbracket["group2"];
          break;
          default:
              $vbracket = 0;
              $group1 = 0;
          break;
        }
        
        if($vbracket > 0)
        {
            // update time in autoemailsched table
            $vtime = $obreload->updatetime($vresult['querytime'],'LastReloadEmailDate');
            if($vtime == 1)
            {
                //get all records equal to $vbigreloadamt
                $vbigreload = $obreload->getallbigreload($vlastcron['LastReloadEmailDate'], $vbigreloadamt);
                $vctr = 0;
                
                while($vctr < count($vbigreload))
                {
                    $vlogid = $vbigreload[$vctr]["TransactionRequestLogID"];
                    $vttype = $vbigreload[$vctr]["TransactionType"];
                    $vreload = $vbigreload[$vctr]["BigReload"];
                    $vposno = $vbigreload[$vctr]['POS'];
                    $vsiteID = $vbigreload[$vctr]['SiteID'];
                    $vsite = $vbigreload[$vctr]["SiteName"];
                    $voperator = $vbigreload[$vctr]["Name"];
                    $vterminalcode = $vbigreload[$vctr]["TerminalCode"];
                    $vstart = date("m/d/Y h:i:s A", (strtotime($vbigreload[$vctr]["StartDate"])));
                    $vend = date("m/d/Y h:i:s A", (strtotime($vbigreload[$vctr]["EndDate"])));
                    $vserver = $vbigreload[$vctr]["ServiceName"];
                    $vterminalID = $vbigreload[$vctr]['TerminalID'];
                    $vwithdrawamt = 0;
                    $vnetwin = 0;       
                    $vserviceID = $vbigreload[$vctr]['ServiceID'];
                    $vtranstype = $vbigreload[$vctr]['TransactionType'];
                    if($vreload <> null)
                    {
                        $vcount = 0;
                        $vinserted = $obreload->insertbigreload($vstart,$vend, $vsiteID, $vsite, $vterminalID, $vterminalcode,$vreload, $vwithdrawamt, $vnetwin, $vserviceID, $vtranstype);
                        if($vinserted > 0)
                        {
                            while($vcount < count($group1))
                            {
                              $to = $group1[$vcount];  
                              $subject = 'PEGS Alert - Reload greater than or equal to PHP '.number_format($vbracket, 2, '.', ',');
                              $message = "
                                 <html>
                                   <head>
                                           <title>$subject</title>
                                   </head>
                                   <body>

                                        <br/><br/>
                                            *ALERT: Information*
                                        <br/><br/>
                                            RELOAD GREATER THAN OR EQUAL TO PHP ".number_format($vbracket, 2, '.', ',')."
                                        <br/><br/>
                                            DETAILS
                                        <br/><br/>
                                            --------------------------------------------------------------------------------------------- 
                                        <br/><br/>
                                            Account No.: $vposno 
                                        <br/><br/>
                                            Login: $vterminalcode
                                        <br/><br/>
                                            Account Name: $voperator $vsite
                                        <br/><br/>
                                            Amount: PHP ".number_format($vreload, 2, '.', ',')."
                                        <br/><br/>                                    
                                            Date/Time of Transaction: $vstart  
                                        <br/><br/>
                                            Casino Server: $vserver                                  
                                        <br/><br/>                            
                                    </body>
                                  </html>";
                              $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
                              $sentEmail = mail($to, $subject, $message, $headers);                                                     

                              $vcount = $vcount + 1;                              
                            }
                        }
                        if($vcount > 0)
                            break;
                    }   
                    $vctr++;
                }        
            }
        }
    }
}
unset($vlastcron,$vresult,$vbigreload,$vctr,$vlogid,$vttype,$vreload,$vposno,$vsiteID,$vsite,$voperator,$vterminalcode,
        $vstart,$vend,$vserver,$vterminalID,$vwithdrawamt,$vnetwin,$vserviceID,$vtranstype,$vbracket,
        $vinserted,$vcount,$sentEmail,$to,$subject,$message,$headers);
$obreload->close();
?>