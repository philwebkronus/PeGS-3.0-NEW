<?php

/*
 * @author : owliber
 * @date : 2013-05-17
 */
require_once("../init.inc.php");
App::LoadCore("URL.class.php");

App::LoadModuleClass("Admin", "AccountSessions");
App::LoadModuleClass("Kronus", "Accounts");

//Get user access
$accounts = new Accounts();
$_AccountSessions = new AccountSessions();

if(isset($_SESSION['userinfo']) 
    && is_array($_SESSION['userinfo']) 
    && count($_SESSION['userinfo']) > 0)
{
    if(isset($_SESSION['sessionID'])){
        $sessionid = $_SESSION['sessionID'];
        $aid = $_SESSION['aID'];
    }
    else{
        $sessionid = 0;
        $aid = 0;
    }
    //Check restricted page
    
    
    
    $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);
    foreach ($sessioncount as $value) {
        foreach ($value as $value2) {
            $sessioncount = $value2['Count'];
        }
    }
    
    if($sessioncount > 0)
    {
        $currentPage = URL::CurrentPage();
    }
    else 
    {
        $msg = "Session Expired";
        session_destroy();
        URL::Redirect("login.php?mess=".$msg);
    }
        
}
else
{
    URL::Redirect('login.php');
}
?>
