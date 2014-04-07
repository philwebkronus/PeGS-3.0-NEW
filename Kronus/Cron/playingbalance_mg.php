<?php
include_once "init.php";
include "Autoemail.class.php";
include_once 'CasinoAPIHandler.class.php';
require_once "class.export_excel.php";

ini_set('display_errors',true);
ini_set('log_errors',true);


/* export to excel*/
$oplayingbal = new Autoemail($_DBConnectionString[0]);
$connected = $oplayingbal->open();

if($connected)
{
    $vtermsession = $oplayingbal->getTerminalSessionsMG();
    $vctr = 0;
    $varrtermbal = array();
    while($vctr < count($vtermsession))
    {
        $oplayingbal->close();
        $oplayingbal = new Autoemail($_DBConnectionString[0]);
        $connected = $oplayingbal->open();
        if($connected)
        {
            $vterminalid = $vtermsession[$vctr]['TerminalID'];
            $vserviceid = $vtermsession[$vctr]['ServiceID'];
            $vservicename = $oplayingbal->getServiceName($vserviceid);
            $vterminalcode = $oplayingbal->getTerminalCode($vterminalid );
            $time =microtime(true);
            $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);
            $rawdate = new DateTime( date('Y-m-d H:i:s.'.$micro_time, $time) );
            $vtransdatetime = $rawdate->format("Y-m-d H:i:s.u");   
            if($vservicename['Code']=='VV')  
            {
               //connect to MG
               $siteid = 2;
               //get agent id
               $agentsession = $oplayingbal->getAgentID($siteid);
               //get agent session id
               //$agentsession = $oplayingbal->getAgentSession($vterminalcode['SiteID']);
               $agentsessionid = $oplayingbal->getAgentSession($agentsession['ServiceAgentID']);

               $configuration = array( 'URI' => $_ServiceAPI[$vserviceid-1],
                    'isCaching' => FALSE,
                    'isDebug' => TRUE,
                    'sessionGUID' => $agentsessionid['ServiceAgentSessionID'] ,
                    'currency' => $_MicrogamingCurrency );            
               $_CasinoAPIHandler = new CasinoAPIHandler( CasinoAPIHandler::MG, $configuration );

               if ( (bool)$_CasinoAPIHandler->IsAPIServerOK() )
               {

                   $vmgoc = $oplayingbal->getOCAccount($vterminalid);
                   $vpbalance = $_CasinoAPIHandler->GetBalance($vmgoc['ServiceTerminalAccount']);
                   if($vpbalance['IsSucceed'] == true)
                   {                          
                        $terminalbalance = $vpbalance['BalanceInfo']['Balance'];
                        $vamount = number_format($terminalbalance,2,'.',',');      
                        array_push($varrtermbal,array($vterminalcode['SiteCode'],$vterminalcode['TerminalCode'],$vamount,$vservicename['ServiceName'],$vtransdatetime));
                   }
               }
            }
        }
        $oplayingbal->close();
        $vctr++;
    }
    
    $vcount = 0;
    $vfirst = 0;
    sort($varrtermbal);
    $vconsolidated = array();
    while($vcount < count($varrtermbal))
    {
        $vgrpsite = $varrtermbal[$vcount][0];  
        if($vfirst == 0)
        {
            $vgrpsite2 = $varrtermbal[$vcount][0];
            array_push($vconsolidated,array($varrtermbal[$vcount][0],$varrtermbal[$vcount][1],$varrtermbal[$vcount][2],$varrtermbal[$vcount][3],$varrtermbal[$vcount][4]));
            $vfirst = 1;
        }
        else
        {
            if($vgrpsite2 == $vgrpsite)
            {
                array_push($vconsolidated,array(' ',$varrtermbal[$vcount][1],$varrtermbal[$vcount][2],$varrtermbal[$vcount][3],$varrtermbal[$vcount][4]));
            }
            else
            {
                array_push($vconsolidated,array($varrtermbal[$vcount][0],$varrtermbal[$vcount][1],$varrtermbal[$vcount][2],$varrtermbal[$vcount][3],$varrtermbal[$vcount][4]));
                $vgrpsite2 = $varrtermbal[$vcount][0];
            }            
        }            
        $vcount++;
        
    }

    /* attachment to an email*/
    $my_file = "playingbalance_mg.xls";
    $my_path = ROOT_DIR;
    $from = "poskronusadmin@philweb.com.ph";
    $my_subject = "PEGS Station Manager - Playing Balance";
    $my_message = "PEGS Station Manager - Playing Balance";

    $file = $my_path.$my_file ;
    $file_size = filesize($file);

    //opens and writes the excel file with latest array
    $handle = fopen($file, "w+") or exit("Unable to open file!");;       
    $vheader = array('Site Code','Terminal Code','Amount','Casino Server','Balance as of Date/Time');
    $header='';
    $data='';
    foreach ($vheader as $title_val)
    {
            $header .= $title_val."\t";
    }
    for($i=0;$i<sizeof($vconsolidated);$i++)
    {
            $line = '';
            foreach($vconsolidated[$i] as $value)
            {
                    if ((!isset($value)) OR ($value == ""))
                    {
                            $value = "\t";
                    } //end of if
                    else
                    {
                            $value = str_replace('"', '""', $value);
                            $value = '"' . $value . '"' . "\t";
                        //$value = "\t";
                    } //end of else
                    $line .= $value;
            } //end of foreach
            $data .= trim($line)."\n";
    }//end of the while
    $data = str_replace("\r", "", $data);

    if ($data == "")
    {
            $data = "\n(0) Records Found!\n";
    }
    $strcontent = "$header\n$data";
    fwrite($handle,$strcontent);
    fclose($handle);
    
    //opens and reads the file only and put the contents in a variable
    $vfile = fopen($my_file,"r") or exit("Unable to open file!");;
    $content = fread($vfile, $file_size);
    fclose($vfile);
    
    $content = chunk_split(base64_encode($content));        
    $uid = md5(uniqid(time()));
    $name = basename($file);
    $vcount = 0;

    while($vcount < count($groupemaildb))
    {
        $my_mail = $groupemaildb[$vcount];
        $header = "From: <".$from.">\r\n";        
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
        $header .= "This is a multi-part message in MIME format.\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
        $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $header .= $my_message."\r\n\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-Type: application/ms-excel; name=\"".$name ."\"\r\n"; // use different content types here
        $header .= "Content-Transfer-Encoding: base64\r\n";
        $header .= "Content-Disposition: attachment; filename=\"".$name ."\"\r\n\r\n";
        $header .= $content."\r\n\r\n";
        $header .= "--".$uid."--";
        mail($my_mail, $my_subject, "", $header) ;
        $vcount++; 
    }
}
unset($vtermsession,$vctr,$varrtermbal,$vterminalid,$vserviceid,$vservicename, 
      $vterminalcode,$time,$micro_time,$rawdate,$vtransdatetime,$vpbalance,
      $terminalbalance,$vamount,$vfirst,$vconsolidated,$vgrpsite,$vgrpsite2,
      $from,$my_message,$file,$file_size ,$handle,$content,$uid,$name,
      $vcount,$header,$my_mail,$my_subject, $vheader, $data, $strcontent, $vfile);
$oplayingbal->close();

?>
