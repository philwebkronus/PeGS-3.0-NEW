<?php
/*
 * Created by: Sheryl S. Basbas
 * Date Created : March 8, 2012
 */

include __DIR__."/../sys/class/Override.class.php";
require __DIR__.'/../sys/core/init.php';

$aid = 0;
if(isset($_SESSION['sessionID']))
{
    $new_sessionid = $_SESSION['sessionID'];
}
else 
{
    $new_sessionid = '';
}
if(isset($_SESSION['accID']))
{
    $aid = $_SESSION['accID'];
}

$_SESSION['alert'] = '';
$override = new Override($_DBConnectionString[0]);
$connected = $override->open();

function removeComma($money) {
    return str_replace(',', '', $money);
}

if($connected){
    /************ SESSION CHECKING **************/        
   $isexist=$override->checksession($aid);
   if($isexist == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $override->close();
      if($override->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
   }    
   $isexistsession =$override->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $override->close();
      header("Location: login.php?mess=".$msg);
   }
   /************ END SESSION CHECKING **********/ 
   
   $_SESSION['sites'] = $override->getSites();
   
   if(isset($_POST['submit']))
   {
        $vtopupAmt = 0.00;
        if(isset($_POST['txttopupamt']))
            $vtopupAmt = removeComma($_POST['txttopupamt']);
        if($_POST['cmbsite'] == -1 && $_POST['txtposacc'] == '' )
        {
            $_SESSION['alert'] = "Please select a Site";
        }
        elseif((float)$vtopupAmt <= 0.00 && isset($_POST['txttopupamt'])){
            $_SESSION['alert'] = "Top-up Amount is required.";
        }
        else
        {
            $SiteID =$_POST['cmbsite'];
            $Enabled =$_POST['optpick'];

            $update = $override->updateSitebalanceAutoToUp($SiteID, $Enabled, $vtopupAmt);

            if($update)
            {
                $_SESSION['alert'] = "Update Site Balance Parameter: Record updated.";

                //insert to audit trail all successful inserted records
                $vdate = $override->getDate();
                $vtransdetails = 'Updated Site'.$SiteID.' Changed AutoTopUpEnabled value to '.$Enabled;
                $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
                $servername = $_SERVER['HTTP_HOST'];
                $vauditfuncID = 59;            
                $override->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
            }
            else
            {
                $_SESSION['alert'] = "Update Site Balance Parameter: Record unchanged.";
            }
        }
    }
    //used for automatic  displaying sitename and POSAccountNo in textbox txtposacc
    if(isset($_POST['cmbsitename']))
    {
        $vsiteID = $_POST['cmbsitename'];
        $rresult = array();
        $rresult = $override->getSiteNameANDAutoTopUp($vsiteID);        
        foreach($rresult as $row)
        {
            $rsitename = $row['SiteName'];
            $rposaccno = $row['POS'];
            $autoEnable = $row['AutoTopupEnabled'];
            $rtopupamt = $row['TopupAmount'];
        }
        if(count($rresult) > 0)
        {
            $vsiteName->SiteName = $rsitename;
            $vsiteName->POSAccNo = $rposaccno;
            $vsiteName->AutoTopup = $autoEnable;
            $vsiteName->TopupAmt = $rtopupamt;
        }
        else
        {
            $vsiteName->SiteName = "";
            $vsiteName->POSAccNo = "";
            $vsiteName->AutoTopup = "";
            $vsiteName->TopupAmt = "";
        }
        if($vsiteName->AutoTopup == "")
        {
            $_SESSION['alert'] = "Site Balance not yet created";
        }

        echo json_encode($vsiteName);
        unset($rresult);
        $override->close();
        exit; 
     }

    //used for automatic  displaying sitename and SiteCode in ComboBox cmbsite
    elseif(isset($_POST['POSAccount']))
    {
        $POSAccount = $_POST['POSAccount'];
        $rresult = array();
        $rresult = $override->getSiteNamebyPOSAccount($POSAccount);
        foreach($rresult as $row)
        {
            $rsiteID = $row['SiteID'];
            $rsitename = $row['SiteName'];
            $POS = $row['POS'];
            $autoEnable = $row['AutoTopupEnabled'];
            $rtopupamt = $row['TopupAmount'];
        }
        if(count($rresult) > 0)
        {
            $vsiteName->SiteID = $rsiteID;
            $vsiteName->SiteName = $rsitename;
            $vsiteName->POSAccNo = $POS;
            $vsiteName->AutoTopup = $autoEnable;
            $vsiteName->TopupAmt = $rtopupamt;
        }
        else
        {
            $vsiteName->SiteID  = "";
            $vsiteName->SiteName = "";
            $vsiteName->POSAccNo = "";
            $vsiteName->AutoTopup = "";
            $vsiteName->TopupAmt = "";
        }
        if($vsiteName->AutoTopup == "")
        {
            $_SESSION['alert'] = "Site Balance not yet created";
        }

        echo json_encode($vsiteName);
        unset($rresult);
        $override->close();
        exit; 
    }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>
