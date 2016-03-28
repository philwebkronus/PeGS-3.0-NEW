<?php
/*
 * Created BY: Edson L. Perez
 * Date Created: August 08, 2011
 * Description: process for site groups module
 */
include "../../sys/class/SiteGroupManagement.class.php";
require '../../sys/core/init.php';
ini_set('display_errors',true);
ini_set('log_errors',true);

if (!isset($_SESSION))
{
    session_start();
}
$new_sessionid = $_SESSION['sessionID'];
$aid = $_SESSION['accID'];

$ositegrp = new SiteGroupManagement($_DBConnectionString[0]);
$connected = $ositegrp->open();
$nopage = 0;
if($connected)
{    
   $isexist=$ositegrp->checksession($aid);
   if($isexist == 0)
   {
     session_destroy();
     $msg = "Not Connected";
     header("Location: ../views/login.php?mess=".$msg);
   } 
   
   $isexistsession =$ositegrp->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
     session_destroy();
     $msg = "Not Connected";
     header("Location: ../views/login.php?mess=".$msg);
   }
   
   $vdate = $ositegrp->getDate();
   $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
   $servername = $_SERVER['HTTP_HOST'];
   
   $vsitegrps = $ositegrp->getsitegrp();
   $_SESSION['sitegrps'] = $vsitegrps; // session variable to populate combo boxes
   
   //pagination starts here:
   if(isset($_POST['paginate']))
   {     
            $page = $_POST['page']; // get the requested page
            $limit = $_POST['rows']; // get how many rows we want to have into the grid
            $vsitegrp = $_POST['cmbsitegrp'];
            $resultcount = array();
            $resultcount = $ositegrp->countsitegroups();
            $count = $resultcount['count'];
            //this is for computing the limit
            if($count > 0 ) {
              $total_pages = ceil($count/$limit);
            } else {
              $total_pages = 0;
            }
            if ($page > $total_pages)
            {
              $page = $total_pages;
            }

            $start = $limit * $page - $limit;
            $limit = (int)$limit;
            
            //this is for proper rendering of results, if count is 0 $result is also must be 0
            if($count > 0)
            {
               $result = $ositegrp->viewsitegrp($vsitegrp, $start, $limit);
            }
            else{
               $result = 0; 
            }
            if($result > 0)
            {
               $i = 0;
               $responce->page = $page;
               $responce->total = $total_pages;
               $responce->records = $count;
               foreach($result as $vview) {
                    $rgrpID = $vview['SiteGroupID'];
                    $responce->rows[$i]['id']= $rgrpID;
                    $responce->rows[$i]['cell']=array($vview['SiteGroupsName'], $vview['Description'], "<input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='../process/ProcessSiteGroup.php?grpid=$rgrpID'+'&page='+'ViewGroups';\"/>");
                    $i++;
               }
           }
           else
           {
               $i = 0;
               $responce->page = $page;
               $responce->total = $total_pages;
               $responce->records = $count;
               $msg = "Site Group Management: No returned result";
               $responce->msg = $msg;
           }
           echo json_encode($responce);
   }
   //end pagination
   if(isset ($_POST['page']))
   {
     $vpage = $_POST['page'];  
     switch ($vpage)
     {
       case 'SiteGroupCreation':
         if(isset ($_POST['txtgrpname']) && isset ($_POST['txtgrpdesc']))
         {
             $vgrpname = $_POST['txtgrpname'];
             $vgrpdesc = $_POST['txtgrpdesc'];
             $vdatecreated = $vdate;
             $rresult = $ositegrp->createsitegroup($vgrpname, $vgrpdesc, $vdatecreated, $aid);
             if($rresult > 0)
             {
                 $nopage= 1;
                 $msg = "Site Groups Creation: Successfully Created";
                 //insert into audit trail
                  $vtransdetails = "Site Groups Creation-add ".$vgrpname." msg-".$msg;
                  $ositegrp->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress); 
                 $_SESSION['mess'] = $msg;
                 $ositegrp->close();
                 header("Location: ../views/sitegrpcreate.php");
             }
             
             else
             {
                 $msg = "Site Groups Creation: Failed to create site group";
                 $_SESSION['mess'] = $msg;
                 $ositegrp->close();
                 header("Location: ../views/sitegrpcreate.php");
             }
         }
         
         else
         {
             $msg = "Site Group Management: Invalid Fields";
             $ositegrp->close();
             $_SESSION['mess'] = $msg;
             header("Location: ../views/sitegrpcreate.php");
         }
       break;
       
       case 'SiteGroupUpdate':
          $vsitegrpID = $_POST['txtgrpid'];
          $vgrpname = $_POST['txtgrpname'];
          $vgrpdesc = $_POST['txtgrpdesc'];
          $vdatemodified = $vdate;
          
          $rresult = $ositegrp->updatesitegroup($vgrpname, $vgrpdesc, $vsitegrpID);
           if($rresult > 0)
             {
                 $nopage= 1;
                 $msg = "Site Groups Update: Successfully Updated";
                 //insert into audit trail
                  $vtransdetails = "Site Groups Update".$vgrpname." msg-".$msg;
                  $ositegrp->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress); 
                 $_SESSION['mess'] = $msg;
                 $ositegrp->close();
                 header("Location: ../views/sitegrpview.php");
             }
             
           else
             {
                 $msg = "Site Groups Update: Site Groups Details unchanged";
                 $_SESSION['mess'] = $msg;
                 $ositegrp->close();
                 header("Location: ../views/sitegrpview.php");
             }
       break;
       
       case 'SiteGroupsView':
        $rsitegrpID = $_POST['cmbsitegrp'];
        $rgrpdetails = $ositegrp->viewsitegrp($rsitegrpID, 0, 0);
        echo json_encode($rgrpdetails);
       break;
       default:
           if($nopage == 0)
           {
             $ositegrp->close();
           }
     }
   }
   
   //for displaying  the data
   if(isset ($_GET['page']))
   {
       $vgrpID = $_GET['grpid'];
       $vgrpdetails = $ositegrp->viewsitegrp($vgrpID, 0, 0);
       $_SESSION['grpdetails'] = $vgrpdetails;
       $ositegrp->close();
       header("Location: ../views/sitegrpedit.php");
   }

}

else{
  $msg =  "Not connected";
  header("Location: ../views/login.php?mess=".$msg);
}
?>
