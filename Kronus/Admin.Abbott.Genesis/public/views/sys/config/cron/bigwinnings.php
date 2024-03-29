<?php
include_once "../../core/init.php";
require_once '../../class/helper/common.class.php';
include "../../class/Autoemail.class.php";
include "../../class/CasinoAPI/RealtimeGamingLobbyAPI.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);

$obwinnings = new Autoemail($_DBConnectionString[0]);
$connected = $obwinnings->open();
if($connected)
{
    $vlastcron = $obwinnings->getcronsched('EmailDate');      
    $vresultrw = $obwinnings->getredeem($vlastcron['EmailDate']);    
    $vbigwithdrawamt = (float)($vresultrw['Amount']);
    if($vbigwithdrawamt > 0)
    {       
        // insert time into autoemailsched table
        $vtime = $obwinnings->updatetime($vresultrw['querytime'],'EmailDate');        
        if($vtime == 1)
        {
            //get all records equal to $vresult['Amount']
        $vbigwinnings = $obwinnings->getallbigredeem($vlastcron['EmailDate'],$vbigwithdrawamt);              
        $vctr = 0;            
        while($vctr < count($vbigwinnings))
        {                
            $vterminalID = $vbigwinnings[$vctr]['TerminalID'];
            $vredeemamt = $vbigwinnings[$vctr]['Redeem'];
            $vtranstype = $vbigwinnings[$vctr]['TransactionType'];
            $vend = date("m/d/Y h:i:s A", (strtotime($vbigwinnings[$vctr]["EndDate"])));          
            //check if terminalID is nut null; otherwise this will not send email                
            if($vterminalID <> null)
            {
                    //get TerminalID from redeem result
                    $vresultd = $obwinnings->getdeposits($vbigwinnings[$vctr]['TransactionSummaryID']);
                    $vstart = date("m/d/Y h:i:s A", (strtotime($vresultd['DateStarted'])));
                    if(count($vresultd) > 0)
                    {                      
                        $vresultr = $obwinnings->getreload($vbigwinnings[$vctr]['TransactionSummaryID']);                      
                        $vtotalload = $vresultd['Deposit'] + $vresultr['Reload'];                      
                        $vlogid = $vbigwinnings[$vctr]["TransactionRequestLogID"];
                        $vttype = $vbigwinnings[$vctr]["TransactionType"];
                        $vreload = $vtotalload;
                        $vposno = $vbigwinnings[$vctr]['POS'];
                        $vsite = $vbigwinnings[$vctr]["SiteName"];
                        $voperator = $vbigwinnings[$vctr]["Name"];
                        $vterminalcode = $vbigwinnings[$vctr]["TerminalCode"];
                        $vserver = $vbigwinnings[$vctr]["ServiceName"];
                        $vservercode = $vbigwinnings[$vctr]["Code"];
                        $vnetwin = (float)$vredeemamt - (float)$vreload; //compute the net win 
                        $vsiteID = $vbigwinnings[$vctr]['SiteID'];
                        $vserviceID = $vbigwinnings[$vctr]['ServiceID'];        

                        //check $vbracket vs $vnetwin
                        $ctrbracket = 0;
                        while($ctrbracket < count($varrbracketwin))
                        {
                            if(($vnetwin >= $varrbracketwin["group1"])  &&  ($vnetwin < $varrbracketwin["group2"]))
                            {
                                $vbracket = $varrbracketwin["group1"];
                                $emailgroup = $group2;                                          
                            }
                            else if($vnetwin >= $varrbracketwin["group2"])
                            {
                                $vbracket = $varrbracketwin["group2"];
                                $emailgroup = $group3;
                            }
                            else
                            {
                                $vbracket = 0;
                                $group1 = 0;
                            }
                            $ctrbracket = $ctrbracket + 1;
                        }

                        //check if net win is greater than its reload
                        if($vbracket > 0)
                        {
                            if($vnetwin > $vreload && $vnetwin >= $vbracket)
                            { 
                                //checked if RTG
                                if($vservercode == "MM")
                                {
                                    $certFilePath = RTGCerts_DIR . $vserviceID  . '/cert.pem';
                                    $keyFilePath = RTGCerts_DIR . $vserviceID  . '/key.pem';

                                    $lobby = new RealtimeGamingLobbyAPI($url ='', $certFilePath, $keyFilePath, $passPhrase = '');

                                    $cashierURI = $_ServiceAPI[$vserviceID-1];
                                    $login = $vterminalcode;

                                    $getPidFromLogin = $lobby->GetPIDFromLogin($cashierURI, $login);

                                    $pid = $getPidFromLogin['GetPIDFromLoginResult'];

                                    $games = '';
                                    if(count($pid) > 0){
                                    $lobbyURI = $_LobbyAPI[$vserviceID-1];
                                    $gamesnum = 1; //no of games that can be displayed
                                    $getgame = $lobby->getLastGamesPlayed($lobbyURI, $pid, $gamesnum);
                                    if($getgame <> null )
                                        $games = $getgame['GameDescription'];
                                    }
                                    $vgamedesc = "<br/><br/> Last Game Played: ".$games; 
                                }
                                else
                                {
                                $vgamedesc = "";
                                }          

                                $ctr = 0;
                                $forexit = 0; 

                                $vinserted = $obwinnings->insertbigreload($vstart,$vend, $vsiteID, $vsite, $vterminalID, $vterminalcode,$vreload, $vredeemamt, $vnetwin, $vserviceID, $vtranstype);
                                if($vinserted > 0)
                                {
                                    $vcount = 0;
                                    while($vcount < count($emailgroup))
                                    {
                                            $to = $group2[$vcount];               
                                            $subject = 'PEGS Alert - Player Wins More than or Equal to PHP '.number_format($vbracket, 2, '.',',');

                                            $message = "
                                                <html>
                                                <head>
                                                        <title>$subject</title>
                                                </head>
                                                <body>
                                                    <br/><br/>
                                                        ALERT: CRITICAL!
                                                    <br/><br/>
                                                        PLAYER WINS MORE THAN  OR EQUAL TO  PHP ".number_format($vbracket, 2, '.',',')."
                                                    <br/><br/>
                                                        DETAILS
                                                    <br/><br/>
                                                        --------------------------------------------------------------------------------------------- 
                                                    <br/><br/>
                                                        Time In: $vstart
                                                    <br/><br/>
                                                        Time Out: $vend 
                                                    <br/><br/>
                                                        Account No.: $vposno 
                                                    <br/><br/>
                                                        Login: $vterminalcode
                                                    <br/><br/>
                                                        Account Name: $voperator $vsite
                                                    <br/><br/>
                                                        Player Total Load: PHP ".number_format($vreload, 2, '.', ',')."
                                                    <br/><br/>
                                                        Amount Withdrawn: PHP ".number_format($vredeemamt, 2, '.', ',')."
                                                    <br/><br/>
                                                        Player Net Win: PHP ".number_format($vnetwin, 2, '.', ',')."
                                                        $vgamedesc
                                                    <br/><br/>    
                                                        Casino Server: $vserver                                  
                                                    <br/><br/>
                                                        Date/Time of Withdrawal: $vend
                                                    <br/><br/>                            
                                                </body>
                                                </html>";
                                            $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
                                            $sentEmail = mail($to, $subject, $message, $headers);    

                                            $vcount = $vcount + 1;
                                    }
                                    if($vcount > 0)
                                    break;                                   
                                }
                            }                            
                        }
                    }
            }
            $vctr++;
            }
        }
    }
}
unset($vlastcron,$vresultrw,$vtime,$vbigwinnings,$vctr,$vterminalID,$vredeemamt,
        $vtranstype,$vend,$vresultd,$vresultr,$vtotalload,$vlogid,$vttype,$vreload,
        $vposno,$vsite,$voperator,$vterminalcode,$vstart,$vserver,$vservercode,
        $vnetwin,$vsiteID,$vserviceID,$vgamedesc,$vbracket,$group2,$vinserted,
        $vcount,$sentEmail,$ctr,$to,$subject,$message,$headers);
$obwinnings->close();
?>
