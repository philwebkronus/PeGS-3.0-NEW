<?php
/**
 * Created By: Edson L. Perez
 * Created On: November 15, 2011
 * Purpose: Process For Menu, Submenu, AccessRights Maintenance
 */

include  __DIR__."/../sys/class/MenuMaintenance.class.php";
require  __DIR__."/../sys/core/init.php";

$aid = 0;
if(isset($_SESSION['sessionID']))
{
    $new_sessionid = $_SESSION['sessionID'];
}
if(isset($_SESSION['accID']))
{
    $aid = $_SESSION['accID'];
}

$omenu = new MenuMaintenance($_DBConnectionString[0]);
$connected = $omenu->open();
$nopage = 0;
if($connected)
{    
   $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
   $vdate = $omenu->getDate();    
/*************** SESSION CHECKING ****************/        
   $isexist=$omenu->checksession($aid);
   if($isexist == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $omenu->close();
      if($omenu->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
   }    
   $isexistsession =$omenu->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $omenu->close();
      header("Location: login.php?mess=".$msg);
   }
/*************** END SESSION CHECKING ****************/    
   
   //checks if account was locked 
   $islocked = $omenu->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $omenu->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $omenu->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
   $vmenuID = 0;
   $rmenu = $omenu->getactivemenu();
   $_SESSION['menus'] = $rmenu;
       
   //for pagination of menus and submenus
   if(isset($_POST['paginate']))
   {
       $vgridpage = $_POST['paginate'];
       $page = $_POST['page']; // get the requested page
       $limit = $_POST['rows']; // get how many rows we want to have into the grid
       $search = $_POST['_search'];
       $direction = $_POST['sord'];
       if(isset ($_POST['searchField']) && isset($_POST['searchString']))
       {
           $searchField = $_POST['searchField'];
           $searchString = $_POST['searchString'];
           echo $searchField;
           echo $searchString;
       }
       switch ($vgridpage)
       {
           case 'MenuView':
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "MenuID";
                }
                
                $rctrsite = $omenu->countmenu(0);
                $count = $rctrsite['ctrmenu'];
                
                //this is for computing the limit
                if($count > 0 ) {
                  $total_pages = ceil($count/$limit);
                } else {
                  $total_pages = 0;
                }

                if ($page > $total_pages)
                {
                  $page = $total_pages;
                  $start = $limit * $page - $limit;           
                }

                if($page == 0)
                {
                  $start = 0;
                }

                else{
                  $start = $limit * $page - $limit;   
                }
                
                $limit = (int)$limit;

                //this is for proper rendering of results, if count is 0 $result is also must be 0
                if($count > 0)
                {
                   $result = $omenu->viewmenu(0, $start, $limit, $sort, $direction);
                }
                else{
                   $result = array(); 
                }
                if(count($result) > 0)
                {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   foreach($result as $vview) 
                   {
                        $menuID = $vview['MenuID'];
                        if($vview['Status'] == 0)
                        {
                            $vstatus = "Inactive";
                        }
                        else
                        {
                            $vstatus = "Active";
                        }
                        $response->rows[$i]['id'] = $menuID;
                        $response->rows[$i]['cell']=array($vview['Name'], $vview['Link'], $vview['Description'], $vstatus, "<input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='process/ProcessMenuMaintenance.php?menuid=$menuID'+'&getpage='+'MenuDetails';\"/>");
                        $i++;
                   }
               }
               else
               {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   $msg = "Menu Listing: No return result";
                   $response->msg = $msg;
               }
               echo json_encode($response);
               unset($result);
               $omenu->close();
               exit;
           break;
           case 'SubMenuView':
               //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "SubMenuID";
                }
                $vmenuID = $_POST['menuid'];
                $rctrsub = $omenu->countsubmenu($vmenuID, 0);
                $count = $rctrsub['ctrsubmenu'];
                
                //this is for computing the limit
                if($count > 0 ) {
                  $total_pages = ceil($count/$limit);
                } else {
                  $total_pages = 0;
                }
                
                if ($page > $total_pages)
                {
                  $page = $total_pages;
                  $start = $limit * $page - $limit;           
                }
                
                if($page == 0)
                {
                  $start = 0;
                }

                else{
                  $start = $limit * $page - $limit;   
                }
                
                $limit = (int)$limit;

                //this is for proper rendering of results, if count is 0 $result is also must be 0
                if($count > 0)
                {
                   $result = $omenu->viewsubmenu(0, $vmenuID, $start, $limit, $sort, $direction);
                }
                else{
                   $result = 0; 
                }
                if($result > 0)
                {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   foreach($result as $vview) 
                   {
                        $submenuID = $vview['SubMenuID'];
                        $response->rows[$i]['id'] = $submenuID;
                        $response->rows[$i]['cell']=array($vview['MenuName'], $vview['SubMenuName'], $vview['Group'], $vview['Description'], "<input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='process/ProcessMenuMaintenance.php?submenuid=$submenuID'+'&getpage='+'SubMenuDetails';\"/>");
                        $i++;
                   }
               }
               else
               {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   $msg = "Submenu Listing: No return result";
                   $response->msg = $msg;
               }
               echo json_encode($response);
               $omenu->close();
               exit;
           break;
           case 'AccessRightsView':
               //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "AccountTypeID";
                }
                
                $vacctypeID = $_POST['acctypeid'];
                $rctraccess = $omenu->countaccessrights($vacctypeID);
                $count = $rctraccess['ctraccess'];
                
                //this is for computing the limit
                if($count > 0 ) {
                  $total_pages = ceil($count/$limit);
                } else {
                  $total_pages = 0;
                }
                
                if ($page > $total_pages)
                {
                  $page = $total_pages;
                  $start = $limit * $page - $limit;           
                }
                
                if($page == 0)
                {
                  $start = 0;
                }

                else{
                  $start = $limit * $page - $limit;   
                }
                
                $limit = (int)$limit;

                //this is for proper rendering of results, if count is 0 $result is also must be 0
                if($count > 0)
                {
                   $result = $omenu->viewaccessrights($vacctypeID, $sort, $direction, $start, $limit);
                }
                else{
                   $result = 0; 
                }
                if(count($result) > 0)
                {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   foreach($result as $vview) 
                   {
                        $accessrightID = $vview['AccessRightsID'];
                        $vsubmenuname = $vview['SubMenuName'];
                        $vacctype = $vview['AccountType'];
                        $response->rows[$i]['id'] = $accessrightID;
                        $response->rows[$i]['cell']=array($vacctype, $vview['MenuName'], 
                                                          $vsubmenuname, $vview['OrderID'], 
                                                          $vview['DefaultURL'], $vview['DefaultURL2'],
                                                          "<input type=\"button\" value=\"Terminate\" onclick=\"window.location.href='process/ProcessMenuMaintenance.php?rightid=$accessrightID&submenu=$vsubmenuname&acctype=$vacctype'+'&getpage='+'DeactivateAccessRights';\"/>");
                        $i++;
                   }
               }
               else
               {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   $msg = "Access Rights Listing: No return result";
                   $response->msg = $msg;
               }
               echo json_encode($response);
               $omenu->close();
               exit;
           break;
           case 'MenuDeactivation':
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "MenuID";
                }
                
                $vmenuID = $_POST['menuid'];
                $rctrsite = $omenu->countmenu($vmenuID);
                $count = $rctrsite['ctrmenu'];
                
                //this is for computing the limit
                if($count > 0 ) {
                  $total_pages = ceil($count/$limit);
                } else {
                  $total_pages = 0;
                }

                if ($page > $total_pages)
                {
                  $page = $total_pages;
                  $start = $limit * $page - $limit;           
                }

                if($page == 0)
                {
                  $start = 0;
                }

                else{
                  $start = $limit * $page - $limit;   
                }
                
                $limit = (int)$limit;

                //this is for proper rendering of results, if count is 0 $result is also must be 0
                if($count > 0)
                {
                   $result = $omenu->viewmenu($vmenuID, $start, $limit, $sort, $direction);
                }
                else{
                   $result = 0; 
                }
                if(count($result) > 0)
                {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   foreach($result as $vview) 
                   {
                        $menuID = $vview['MenuID'];
                        $menuname = $vview['Name'];
                        $response->rows[$i]['id'] = $menuID;
                        $rstatus = $vview['Status'];
                        if($rstatus == 0)
                        {
                            $vstatus = "Inactive";
                            $response->rows[$i]['cell']=array($menuname, $vview['Link'], $vview['Description'], $vstatus, 
                                                              "<input type=\"button\" value=\"Activate Menu\" onclick=\"window.location.href='process/ProcessMenuMaintenance.php?menuid=$menuID&status=1&name=$menuname'+'&getpage='+'DeactivateMenu';\" />");
                        }
                        else
                        {
                            $vstatus = "Active";
                            $response->rows[$i]['cell']=array($menuname, $vview['Link'], $vview['Description'], $vstatus, 
                                                              "<input type=\"button\" value=\"Deactivate Menu\" onclick=\"window.location.href='process/ProcessMenuMaintenance.php?menuid=$menuID&status=0&name=$menuname'+'&getpage='+'DeactivateMenu';\" />");
                        }
                        $i++;
                   }
               }
               else
               {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   $msg = "Menu Deactivation: No return result";
                   $response->msg = $msg;
               }
               echo json_encode($response);
               $omenu->close();
               exit;
           break;
           case 'SubMenuDeactivation':
               //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "SubMenuID";
                }
                $vmenuID = $_POST['menuid'];
                $vsubmenuid = $_POST['submenuid'];
                $rctrsub = $omenu->countsubmenu($vmenuID, $vsubmenuid);
                $count = $rctrsub['ctrsubmenu'];
                
                //this is for computing the limit
                if($count > 0 ) {
                  $total_pages = ceil($count/$limit);
                } else {
                  $total_pages = 0;
                }
                
                if ($page > $total_pages)
                {
                  $page = $total_pages;
                  $start = $limit * $page - $limit;           
                }
                
                if($page == 0)
                {
                  $start = 0;
                }

                else{
                  $start = $limit * $page - $limit;   
                }
                
                $limit = (int)$limit;

                //this is for proper rendering of results, if count is 0 $result is also must be 0
                if($count > 0)
                {
                   $result = $omenu->viewsubmenu($vsubmenuid, $vmenuID, $start, $limit, $sort, $direction);
                }
                else{
                   $result = 0; 
                }
                if($result > 0)
                {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   foreach($result as $vview) 
                   {
                        $submenuID = $vview['SubMenuID'];
                        $menuID = $vview['MenuID'];
                        $submenuname = $vview['SubMenuName'];
                        $rstatus = $vview['Status'];
                        $response->rows[$i]['id'] = $submenuID;
                        if($rstatus == 0){
                            $vstatus = "Inactive";
                            $response->rows[$i]['cell']=array($vview['MenuName'], $vview['SubMenuName'], 
                                                          $vview['Group'], $vview['Description'], $vstatus, 
                                                          "<input type=\"button\" value=\"Activate Sub-menu\" onclick=\"window.location.href='process/ProcessMenuMaintenance.php?submenuid=$submenuID&status=1&menuid=$menuID&name=$submenuname'+'&getpage='+'DeactivateSubMenu';\" />");
                        } else {
                            $vstatus = "Active";    
                            $response->rows[$i]['cell']=array($vview['MenuName'], $vview['SubMenuName'], 
                                                          $vview['Group'], $vview['Description'], $vstatus, 
                                                          "<input type=\"button\" value=\"Deactivate Sub-menu\" onclick=\"window.location.href='process/ProcessMenuMaintenance.php?submenuid=$submenuID&status=0&menuid=$menuID&name=$submenuname'+'&getpage='+'DeactivateSubMenu';\" />");
                        }
                        $i++;
                   }
               }
               else
               {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   $msg = "Sub-menu Listing: No return result";
                   $response->msg = $msg;
               }
               echo json_encode($response);
               $omenu->close();
               exit;
           break;
           case 'UserDeactivation':
               //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "AID";
                }
                
                $rctraccs = $omenu->countaccounts();
                $count = $rctraccs['ctracc'];
                
                //this is for computing the limit
                if($count > 0 ) {
                  $total_pages = ceil($count/$limit);
                } else {
                  $total_pages = 0;
                }

                if ($page > $total_pages)
                {
                  $page = $total_pages;
                  $start = $limit * $page - $limit;           
                }

                if($page == 0)
                {
                  $start = 0;
                }

                else{
                  $start = $limit * $page - $limit;   
                }
                
                $limit = (int)$limit;

                //this is for proper rendering of results, if count is 0 $result is also must be 0
                if($count > 0)
                {
                   $result = $omenu->viewaccounts($vaid = 0, $start, $limit, $sort, $direction);
                }
                else{
                   $result = 0; 
                }
               
                if(count($result) > 0)
                {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   foreach($result as $vview) 
                   {
                        $rstatus = $vview['Status'];
                        $accID = $vview['AID'];
                        $vstatname = $omenu->showstatusname($rstatus);
                        $vaccname = $vview['UserName'];
                        $response->rows[$i]['id']=$accID;
                        $response->rows[$i]['cell']=array($vview['UserName'],$vview['Name'],$vview['Email'],$vview['Address'], 
                                                          $vstatname, "<input type=\"button\" class=\"btnterminate\" value=\"Deactivate User\" onclick=\"window.location.href='process/ProcessMenuMaintenance.php?uname=$vaccname&aid=$accID'+'&getpage='+'DeactivateUser';\" />");
                        $i++;
                   }
               }
               else
               {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   $msg = "User Deactivation: No return result";
                   $response->msg = $msg;
               }
               echo json_encode($response);
               $omenu->close();
               exit;
           break;
           default:
               $msg = "Menu Maintenance: Page not found";
               $omenu->close();
               header("Location: login.php?mess=".$msg);
           break;
       }
   }
   //for page submit and requests
   if(isset($_POST['page']))
   {
       $vpage = $_POST['page'];
       switch ($vpage)
       {
           //create menu
           case 'MenuCreation':
               if((isset($_POST['txtmenuname'])) && (isset($_POST['txtdescription'])) && (isset($_POST['txtdefault'])))
               {
                   $vmenuname = trim($_POST['txtmenuname']);
                   $vmenudesc = trim($_POST['txtdescription']);
                   $vdefault = trim($_POST['txtdefault']);
                   $vpath = ROOT_DIR.$vdefault;
                   
                   //check of the default page does exists
                   if(file_exists($vpath))
                   {
                       $vmenu = $omenu->createmenu($vmenuname, $vdefault, $vmenudesc);
                       if($vmenu > 0)
                       {
                           $msg = "Menu Creation: Successfully created";
                           $vtransdetails = "Menu Name ".$vmenuname.";Menu ID ".$vmenu;
                           $vauditfuncID = 51;
                           $omenu->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                       }
                       else
                       {
                           $msg = "Menu Creation: Error on creating menu";
                       }
                   }
                   else
                   {
                       $msg = "Menu Creation:  File does not exists";
                   }
               }
               else
               {
                   $msg = "Menu Creation: Invalid Fields.";
               }
               $_SESSION['mess'] = $msg;
               $omenu->close();
               header("Location: ../menucreation.php");
           break;
           //this updates menu
           case 'MenuUpdate':
               if((isset($_POST['txtmenuname'])) && (isset($_POST['txtdescription'])))
               {
                   $vmenuname = trim($_POST['txtmenuname']);
                   $vdescription = trim($_POST['txtdescription']);
                   $vmenuID = trim($_POST['txtmenuID']);
                   $risupdate = $omenu->updatemenu($vmenuname, $vdescription, $vmenuID);

                   if($risupdate > 0)
                   {
                       $msg = "Update Menu: Successfully updated.";
               
                       //log to audit trail
                       $arrnewdetails = array($vmenuname, $vdescription, $vmenuID);
                       $vnewdetails = implode(",", $arrnewdetails);
                       $vtransdetails = "Old Details ".$_POST['txtolddetails'].";New Details ".$vnewdetails;
                       $vauditfuncID = 52;
                       $omenu->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                       unset($arrnewdetails);
                   }
                   else
                   {
                       $msg = "Update Menu: Update details unchanged";
                   }
               }
               else
               {
                   $msg = "Update Menu: Invalid Fields.";
               }
               
               $_SESSION['mess'] = $msg;
               $omenu->close();
               header("Location: ../menuview.php");
           break;
           //this creates submenu
           case 'CreateSubMenu':
               if((isset($_POST['cmbmenu'])) && (isset($_POST['txtsubname'])) 
                       && (isset($_POST['txtdescription'])) && (isset($_POST['txtgroup'])))
               {
                   $vmenuID = $_POST['cmbmenu'];
                   $vname = trim($_POST['txtsubname']);
                   $vdescription = trim($_POST['txtdescription']);
                   $vgroup = trim($_POST['txtgroup']);
                   
                   $vsubmenuID = $omenu->insertsubmenu($vmenuID, $vname, $vdescription, $vgroup, $vstatus = 1);
                   if($vsubmenuID > 0)
                   {   
                       //log to audit trail
                       $msg = "Submenu Creation: Successfully created.";
                       $vtransdetails = "Submenu Name ".$vname.";Submenu ID ".$vsubmenuID;
                       $vauditfuncID = 53;
                       $omenu->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                   }
                   else
                   {
                       $msg = "Submenu Creation: Error on creating submenu";
                   }
               }
               else
               {
                   $msg = "Submenu created: Invalid Fields.";    
               }
               $_SESSION['mess'] = $msg;
               $omenu->close();
               header("Location: ../submenucreate.php");
           break;
           //this updates submenu
           case 'SubMenuUpdate':
               if((isset($_POST['txtsubname'])) && (isset($_POST['cmbmenu'])) 
                       && (isset($_POST['txtdescription'])) && (isset($_POST['txtgroup'])))
               {
                   $vmenuID = $_POST['cmbmenu'];
                   $vsubmenuname = $_POST['txtsubname'];
                   $vdescription = trim($_POST['txtdescription']);
                   $vgroup = trim($_POST['txtgroup']);
                   $vsubmenuID = $_POST['txtsubmenuID'];
                   $risupdate = $omenu->updatesubmenu($vmenuID, $vsubmenuname, $vdescription, $vgroup, $vsubmenuID);
                   if($risupdate > 0)
                   {
                       $msg = "Update Submenu: Successfully updated.";
                       //log to audit trail
                       $arrnewdetails = array($vmenuID, $vsubmenuname, $vdescription, $vgroup);
                       $vnewdetails = implode(",", $arrnewdetails);
                       $vtransdetails = "Old Details ".$_POST['txtolddetails'].";New Details ".$vnewdetails;
                       $vauditfuncID = 54;
                       $omenu->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                       unset($arrnewdetails);
                   }
                   else
                   {
                       $msg = "Update Submenu: Update details unchanged.";
                   }
               }
               else
               {
                   $msg = "Update Submenu: Invalid Fields";
               }
               $_SESSION['mess'] = $msg;
               $omenu->close();
               header("Location: ../submenuview.php");
           break;
           //get all menus
           case 'GetMenu':
               $rmenu = array();
               $vmenuID = $_POST['menuid'];
               $rmenu = $omenu->viewmenu($vmenuID, $vstart = null, $vlimit = null, $sort = null, $direction = null);
               $omenu->close();
               echo json_encode($rmenu);
               unset($rmenu);
               exit;
           break;
           //get active menus only
           case 'GetActiveMenu':
               $rmenu = array();
               $vmenuID = $_POST['menuid'];
               $rmenu = $omenu->getactivemenu();
               $omenu->close();
               echo json_encode($rmenu);
               unset($rmenu);
               exit;
           break;
           //filtering of submenu by menuID
           case 'GetSubmenu':
               $vmenuID = $_POST['menuid'];
               $vsubmenuID = $_POST['submenuid'];
               $vsubmenu = $omenu->viewsubmenu($vsubmenuID, $vmenuID, $vstart = null, $vlimit = null, $sort = null, $direction = null);
               echo json_encode($vsubmenu);
               $omenu->close();
               unset($vsubmenu);
               exit;
           break;
           case 'GetAccountTypes':
               $vacctypes = $omenu->getaccounttypes("Name");
               echo json_encode($vacctypes);
               $omenu->close();
               unset($vacctypes);
               exit;
           break;
           case 'CreateAccessRights':
               if(isset($_POST['cmbacctype']) && isset ($_POST['cmbmenu']) && isset($_POST['cmbsubmenu'])
                       && isset($_POST['txtorderid']) && isset($_POST['txturl']) && isset($_POST['txturl2']))
               {
                   $vacctypeID = $_POST['cmbacctype'];
                   $vmenuID = $_POST['cmbmenu'];
                   $vsubmenuID = $_POST['cmbsubmenu'];
                   $vorderID = trim($_POST['txtorderid']);
                   $vurl = trim($_POST['txturl']);
                   $vurl2 = trim($_POST['txturl2']);
                   $vpath1 = "../".$vurl;
                   $vpath2 = "../".$vurl2;
                   //check of URL does exists
                   if(file_exists($vpath1))
                   {
                       //check of URL2 does exists
                       if(file_exists($vpath2))
                       {
                           $vrightID = $omenu->createaccessrights($vacctypeID, $vmenuID, $vsubmenuID, $vorderID, $vurl, $vurl2);
                           if($vrightID > 0)
                           {
                               $msg = "Access Rights Creation: Successfully created";
                               //write to xml file
                               $racctypes = $omenu->getaccounttypes("AccountTypeID");
                               $arraccess = $omenu->viewaccessrights($vacctypeID = 0, $sort=null, $direction=null, $start=null, $zlimit=null);
                               $vpath = "../../xml/newXMLDocument.xml"; //path to XML File

                               //begin writing to the XML File
                               $omenu->writetoxml($racctypes, $arraccess, $vpath);
                               //log to audit trail
                               $vtransdetails = "Access Rights ID ".$vrightID;
                               $vauditfuncID = 55;
                               $omenu->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                           }
                           else
                           {
                               $msg = "Access Rights Creation: Error on creation";
                           }
                       }
                       else
                       {
                           $msg = "Default URL2 does not exists";
                       }
                   }
                   else
                   {
                       $msg = "Default URL does not exists";
                   }
               }
               else
               {
                   $msg = "Access Rights Creation: Invalid Fields";
               }
               $_SESSION['mess'] = $msg;
               $omenu->close();
               header("Location: ../accessrights.php");
           break;
           //deactivates menu
           case 'DeactivateMenu':
               if(isset($_POST['txtmenuid'])){
                   $vmenuID = $_POST['txtmenuid'];
                   $vstatus = $_POST['txtstatus'];
                   $vresult = $omenu->deactivatemenu($vmenuID, $vstatus);
                   if($vresult > 0)
                   {
                       if($vstatus == 1)
                            $msg = "Menu Activation: Success on activating menu";
                       else
                            $msg = "Menu Deactivation: Success on deactivating menu";
                       
                       $racctypes = $omenu->getaccounttypes("AccountTypeID");
                       $arraccess = $omenu->viewaccessrights($vacctypeID = 0, $sort=null, $direction=null, $start=null, $zlimit=null);
                       $vpath = "../../xml/newXMLDocument.xml";

                       //update xml file--
                       $omenu->writetoxml($racctypes, $arraccess, $vpath);

                       //log to audit trail
                       $vtransdetails = "Menu ID ".$vmenuID;
                       $vauditfuncID = 57;
                       $omenu->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                   }
                   else
                       $msg = "Menu Deactivation: Status unchanged";
               }
               else
                    $msg = "Menu Deactivation: Invalid Menu ID";
               
               $_SESSION['mess'] = $msg;
               $omenu->close();
               header("Location: ../menudeactivation.php");
           break;
           case 'LoadMenu':
               $vmenuID = -1;
               $rmenu = $omenu->viewmenu($vmenuID, $vstart = null, $vlimit = null, $sort = null, $direction = null);
               echo json_encode($rmenu);
               unset($rmenu);
               $omenu->close();
               exit;
           break;
           case 'GetUserAccounts':
               $raccounts = $omenu->viewuseraccounts();
               echo json_encode($raccounts);
               unset($raccounts);
               $omenu->close();
               exit;
           break;
           case 'ViewUserAccount':
               $vaid = $_POST['aid'];
               $rresult = $omenu->viewaccounts($vaid, $start = null, $zlimit = null, $sort = null,$direction=null);
               $arrdetails = array();
               foreach ($rresult as $val)
               {
                   $vstatname = $omenu->showstatusname($val['Status']);
                   $arrnewdetails = array("UserName"=>$val['UserName'],"Name"=>$val['Name'],
                                    "Email"=>$val['Email'], "Address"=>$val['Address'], "Status"=>$vstatname, "AID"=>$val['AID']);
                   array_push($arrdetails, $arrnewdetails);
                   unset($arrnewdetails);
               }
               echo json_encode($arrdetails);
               $omenu->close();
               exit;
               unset($arrdetails);
           break;
           case 'DeactivateUser':
               $vaid = $_POST['txtaccid'];
               $isdeactivate = $omenu->deactivateaccount($vaid);
               if($isdeactivate > 0)
               {
                   $msg = "User Deactivation: User successfully deactivated";
                   //log to audit trail
                   $vtransdetails = "AID ".$vaid;
                   $vauditfuncID = 58;
                   $omenu->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
               }
               else
               {
                   $msg = "User Deactivation: User was already deactivated";
               }
               $_SESSION['mess'] = $msg;
               $omenu->close();
               header("Location: ../accountdeactivation.php");
           break;
           case 'GetAccessOrder':
               if(isset($_POST['menuid']) && isset($_POST['acctypeid'])){
                   $vmenuID = $_POST['menuid'];
                   $vacctypeID = $_POST['acctypeid'];
                   $raccess = $omenu->getMaxAccessOrder($vmenuID, $vacctypeID);
                   if(isset($raccess['ctrorder']))
                      $vorderID->orderID = (int)$raccess['ctrorder'] + 1;
                   else
                      $vorderID->orderID = 0;
                   echo json_encode($vorderID);   
               } 
               else 
                   echo 'Access Rights: Invalid fields';
               exit;
           break;
           case 'DeactivateSubMenu':
               if(isset($_POST['txtmenuid']) && isset($_POST['txtsubmenuid'])){
                   $vmenuID = $_POST['txtmenuid'];
                   $vsubmenuID = $_POST['txtsubmenuid'];
                   $vstatus = $_POST['txtstatus'];
                   $vresult = $omenu->deactivatesubmenu($vmenuID, $vsubmenuID, $vstatus);
                   if($vresult > 0)
                   {
                       if($vstatus == 1)
                            $msg = "Sub-menu Activation: Success on activating sub-menu";
                       else
                            $msg = "Sub-menu Deactivation: Success on deactivating sub-menu";
                       $racctypes = $omenu->getaccounttypes("AccountTypeID");
                       $arraccess = $omenu->viewaccessrights($vacctypeID = 0, $sort=null, $direction=null, $start=null, $zlimit=null);
                       $vpath = "../../xml/newXMLDocument.xml";

                       //update xml file--
                       $omenu->writetoxml($racctypes, $arraccess, $vpath);

                       //log to audit trail
                       $vtransdetails = "Menu ID ".$vmenuID.", Sub-menu ID ".$vsubmenuID;
                       $vauditfuncID = 57;
                       $omenu->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                   }
                   else
                       $msg = "Sub-menu Deactivation: Status unchanged";
               }
               else
                    $msg = "Sub-menu Deactivation: Invalid Request";
               
               $_SESSION['mess'] = $msg;
               unset($vresult, $racctypes, $arraccess);
               $omenu->close();
               header("Location: ../submenudeactivation.php");
           break;
           case 'DeactivateAccessRights':
               if(isset($_POST['rightid'])){
                   $vaccessrightID = $_POST['rightid'];
                   $vaccesstype = $_POST['accesstype'];
                   $vsubmenu = $_POST['submenu'];
                   
                   $isdeactivate = $omenu->deleteaccessrights($vaccessrightID);
                   
                   if($isdeactivate > 0){
                       $racctypes = $omenu->getaccounttypes("AccountTypeID");
                       $arraccess = $omenu->viewaccessrights($vacctypeID = 0, $sort=null, $direction=null, $start=null, $zlimit=null);
                       $vpath = "../../xml/newXMLDocument.xml";

                       //update xml file--
                       $omenu->writetoxml($racctypes, $arraccess, $vpath);

                       //log to audit trail
                       $vtransdetails = "Access Type=".$vaccesstype.", Sub-menu=".$vsubmenu;
                       $vauditfuncID = 57;
                       $omenu->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                       
                       $msg = "Deactivate Access Rights: Successfully deactivated access rights.";
                   }
                   else
                       $msg = "Deactivate Access Rights: Error in deactivating access rights.";
               }
               else
                   $msg = "Deactivate Access Rights: Invalid fields.";
               $_SESSION['mess'] = $msg;
               $omenu->close();
               header("Location: ../accessrightsdel.php");
           break;
           default:
               $msg = "Menu Maintenance: Page not found";
               $omenu->close();
               header("Location: login.php?mess=".$msg);
           break;
       }
   }
   elseif(isset($_GET['getpage']))
   {
       $vgetpage = $_GET['getpage'];
       switch($vgetpage)
       {
           case 'MenuDetails':
               $vmenuID = $_GET['menuid'];
               $vmenuresults = $omenu->viewmenu($vmenuID, $vstart = null, $vlimit = null,$sort=null,$direction=null);
               $_SESSION['menu'] = $vmenuresults;
               unset($vmenuresults);
               $omenu->close();
               header("Location: ../menuedit.php");
           break;
           case 'SubMenuDetails':
               $vsubmenuID = $_GET['submenuid'];
               $vsubresults = $omenu->viewsubmenu($vsubmenuID, $vmenuID = null, $vstart=null, $vlimit = null, $sort = null, $direction = null);
               $_SESSION['submenu'] = $vsubresults;
               unset($vsubresults);
               $omenu->close();
               header("Location: ../submenuedit.php");
           break;
           //creates session to display the popup message on menudeactivation view
           case 'DeactivateMenu':
               $vmenuID = $_GET['menuid'];
               $vmenuname = $_GET['name'];
               $vstatus = $_GET['status'];
               $arrmenu = array("MenuID"=>$vmenuID, "MenuName"=>$vmenuname,"Status"=>$vstatus);
               $_SESSION['menudet'] = $arrmenu;
               unset($arrmenu);
               $omenu->close();
               header("Location: ../menudeactivation.php");
           break;
           //creates session to display the popup message on accountdeactivation view
           case 'DeactivateUser':
               $vAID = $_GET['aid'];
               $vuname = $_GET['uname'];
               $accdetails = array("AID"=>$vAID, "Username"=>$vuname);
               $_SESSION['accdetails'] = $accdetails;
               unset($accdetails);
               $omenu->close();
               header("Location: ../accountdeactivation.php");
           break;
           case 'DeactivateSubMenu':
               $vsubmenuID = $_GET['submenuid'];
               $vmenuID = $_GET['menuid'];
               $vmenuname = $_GET['name'];
               $vstatus = $_GET['status'];
               $arrmenu = array("MenuID"=>$vmenuID,"SubMenuID"=>$vsubmenuID,"SubMenuName"=>$vmenuname,"Status"=>$vstatus);
               $_SESSION['submenudet'] = $arrmenu;
               unset($arrmenu);
               $omenu->close();
               header("Location: ../submenudeactivation.php");
           break;
           case 'DeactivateAccessRights':
               if(isset($_GET['rightid'])){
                   $vaccessrightID = $_GET['rightid'];
                   $vsubmenu = $_GET['submenu'];
                   $vacctype = $_GET['acctype'];
                   $_SESSION['accessdetails'] = array("RightID"=>$vaccessrightID,
                                                      "Submenu"=>$vsubmenu,"AccountType"=>$vacctype);
                   $omenu->close();
                   header("Location: ../viewaccessrights.php");
              }
           break;
           default:
               $msg = "Menu Maintenance: Page not found";
               $omenu->close();
               header("Location: login.php?mess=".$msg);
           break;
       }
   }
   else
   {
       $omenu->close();
   }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>
