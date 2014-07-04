<?php

include "model/UpdateCashDenom.php";

$vcashier = new UpdateCashDenom('mysql:host=localhost;dbname=npos,root,admin');
$connected = $vcashier->open();


if($connected)
{
    $sitecount = 0;
    $sitefailed = 0;
    $sitefailedarray = array();
    
    $allsites = $vcashier->getAllSites();
    
    foreach($allsites as $value){
        
        $siteid = $value['SiteID'];
        
        $sitecounts = $vcashier->getSiteDenomCount($siteid);
        
        if($sitecounts['Count'] > 0){
                
            $sitedenom = $vcashier->revertsitedenom($siteid,$siteid);
        
            if($sitedenom > 0){
                $sitecount += 1;
            }
            else{
                $sitefailed += 1;
                $sitearr = array('SiteID'=>$siteid);
                
                array_push($sitefailedarray, $sitearr);
            }
        }
        
    }
    
    if($sitecount > 0 && $sitefailed == 0){
        $message = "Site Denominations Successfully Updated, $sitecount Sites Updated";
    }
    else if($sitecount > 0 && $sitefailed > 0){
        
        foreach($sitefailedarray as $value2){
            $sites .= $value2['SiteID'].', ';
        }

        $message = "Site Denominations Successfully Updated, $sitecount Sites Updated, $sitefailed Sites Failed to Update, SiteID ".$sites;
    }
    else{
        $message = "Failed to update Site Denomination, $sitefailed Sites Failed to Update";
    }
    
    echo $message;
}

?>
