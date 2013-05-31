<?php

/*
 * @author : owliber
 * @date : 2013-05-17
 */
require_once("../init.inc.php");
App::LoadCore("URL.class.php");
App::LoadModuleClass("Admin", "AccessRights");

//Get user access
$accessrights = new AccessRights();
        
if(isset($_SESSION['userinfo']) 
    && is_array($_SESSION['userinfo']) 
    && count($_SESSION['userinfo']) > 0)
{
    //Check restricted page
    
    $currentPage = URL::CurrentPage();
    $currentMenuID = $accessrights->getMenuID($currentPage);
        
    if(isset($_SESSION['menus']) && count($_SESSION['menus']) > 0)
    {
        $usermenu = $_SESSION['menus'];
        $accounttypeid = $usermenu['0']['AccountTypeID'];
    }
    else
    {
        
        $accounttypeid = $_SESSION["userinfo"]['AccountTypeID'];
        
        $usermenu = $accessrights->getAccessRights($accounttypeid);
        
        $_SESSION["menus"] = $usermenu;
        
    }
    $accessibleMenus = $accessrights->getAccessibleMenuID($accounttypeid);
    $accessibleSubMenus = $accessrights->getAccessibleSubMenuID($accounttypeid);
    
    if(!in_array($currentMenuID, $accessibleMenus))
    {
        if(count($accessibleSubMenus) > 0 )
        {
            $currentSubMenuID = $accessrights->getSubMenuID($currentPage);
            
            if(!in_array($currentSubMenuID, $accessibleSubMenus))
                URL::Redirect ('forbidden.php');
        }
        else
        {
             URL::Redirect ('forbidden.php');
        }
            
    }
    
}
else
{
    URL::Redirect('login.php');
}
?>
