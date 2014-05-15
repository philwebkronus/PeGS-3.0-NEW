<?php
include_once "../../core/init.php";
require_once '../../class/helper/common.class.php';
include "../../class/Autoemail.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);


$obdaily = new Autoemail($_DBConnectionString[0]);
$connected = $obdaily->open();
if($connected)
{
    $cutoff = $cutoff_time;
    $vdatefrom = date ( 'Y-m-d' , strtotime ('-1 day' , strtotime(date('Y-m-d')))); 
    $vdateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($vdatefrom)))." ".$cutoff;   

    $vcount = 0;
    
    while($vcount < count($groupemail_daily))
    {
        $to = $groupemail_daily[$vcount]; 
        $subject = 'PEGS Station Manager - Daily Total Buy-In Report for '.$vdatefrom;
        
        $message =  '<html>
                     <head>
                        <title>'.$subject.'</title>
                     </head>
                     <body>
                       <br />
                       <b>PAGCOR eGames Station</b>
                       <br />
                       Station Manager - Daily Total Buy-In Report for '.$vdatefrom.'
                        <br /><br />
                        <table border="1" style="text-align: center;">
                          <tr>
                            <td colspan = "2">Account Info </td>
                            <td colspan = "3">Initial</td>
                            <td colspan = "3">Reloads</td>
                            <td colspan = "3">Total</td>
                          </tr>
                          <tr>
                            <td>Number</td>	
                            <td>Name</td>	
                            <td>Amount</td>	
                            <td>Count</td>	
                            <td>Average</td>	
                            <td>Amount</td>	
                            <td>Count</td>
                            <td>Average</td>
                            <td>Totals</td>
                            <td>Players</td>
                            <td>Average</td>
                          </tr>';    
        $rresult = $obdaily->getdailybuyin($vdatefrom.' '.$cutoff, $vdateto); 
        
        $arrdaily = array();       
        foreach($rresult as $row) 
        {
              if(!isset($arrdaily[$row['SiteID']]['Deposit'])) {
                  $arrdaily[$row['SiteID']]['Deposit'] = 0;    
                  $arrdaily[$row['SiteID']]['CtrDep'] = 0;                  
              }
              if(!isset($arrdaily[$row['SiteID']]['Reload'])) {
                  $arrdaily[$row['SiteID']]['Reload'] = 0;
                  $arrdaily[$row['SiteID']]['CtrRel'] = 0;                  
              }
              if(!isset($arrdaily[$row['SiteID']]['Withdrawal'])) {
                  $arrdaily[$row['SiteID']]['Withdrawal'] = 0;
              }              
              
              switch($row['TransactionType']){
                  case 'D':
                      $arrdaily[$row['SiteID']]['Deposit']= $arrdaily[$row['SiteID']]['Deposit'] + $row['Amount'];                      
                      $arrdaily[$row['SiteID']]['CtrDep'] += 1;
                      break;
                  case 'R':
                      $arrdaily[$row['SiteID']]['Reload'] = $arrdaily[$row['SiteID']]['Reload'] + $row['Amount'];                      
                      $arrdaily[$row['SiteID']]['CtrRel'] += 1;
                      break;
                  case 'W':
                      $arrdaily[$row['SiteID']]['Withdrawal'] = $arrdaily[$row['SiteID']]['Withdrawal'] + $row['Amount'];
                      break;
              }
              $arrdaily[$row['SiteID']]['SiteName'] = $row['SiteName'];      
              $arrdaily[$row['SiteID']]['POSAccountNo'] = $row['POSAccountNo'];
              $arrdaily[$row['SiteID']]['SiteID'] = $row['SiteID']; 
              $arrdaily[$row['SiteID']]['Name'] = $row['Name'];               
          }
        //$rtotal = $countdep + $countrel ;        
        if(count($arrdaily) > 0)
        {            
            foreach($arrdaily as $row)
            {
                //$rtotal = $row['dep'] + $row['rel'];
                //$rtotave = $rtotal / $row['icount'];
                $avedep = 0;
                $rtotave = 0;
                if($row['Deposit'] > 0)
                    $avedep = ($row['Deposit']/$row['CtrDep']);
                else
                    $avedep = 0;
                
                if($row['Reload'] > 0) 
                    $averel = ($row['Reload']/$row['CtrRel']);
                else
                    $averel = 0;
                
                if(($row['Deposit'] + $row['Reload']) > 0)
                    $tlave = (($row['Deposit'] + $row['Reload'])/$row['CtrDep']);
                else
                    $tlave = 0;
                
                $message .= '<tr>
                             <td>'.$row['POSAccountNo'].'</td>
                             <td>'.$row['Name'].' '.$row['SiteName'].'</td>
                             <td>'.number_format($row['Deposit'], 2, '.', ',').'</td>  
                             <td>'.$row['CtrDep'].'</td>     
                             <td>'.number_format($avedep,2,'.',',').'</td>   
                             <td>'.number_format($row['Reload'], 2, '.', ',').'</td>  
                             <td>'.$row['CtrRel'].'</td>                                    
                             <td>'.number_format($averel,2,'.',',').'</td>        
                             <td>'.number_format(($row['Deposit'] + $row['Reload']), 2, '.', ',').'</td>  
                             <td>'.$row['CtrDep'].'</td>     
                             <td>'.number_format($tlave , 2, '.', ',').'</td>    
                           </tr>';
            }
            $message .= '</table></body></html>';           
            $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
            $sentEmail = mail($to, $subject, $message, $headers); 
        }
        $vcount = $vcount + 1;
    }
    
}
unset($vdatefrom,$vdateto,$vcount,$to,$subject,$message,$rresult ,$arrdaily,$avedep ,$rtotave,$headers,$sentEmail,$vcount);
$obdaily->close();
?>
