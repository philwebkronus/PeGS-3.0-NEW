<?php

/*
 * @author : owliber
 * @date : 2013-05-03
 */

require_once("../init.inc.php");

App::LoadCore("URL.class.php");

// Update accountsession
if(isset($_SESSION['userinfo']['SessionID']))
{
    App::LoadModuleClass("Admin", "AccountSessions");
    App::LoadModuleClass("Membership", "AuditFunctions");
    App::LoadModuleClass("Membership", "AuditTrail");
    
    $_Log = new AuditTrail();
    $_AccountSessions = new AccountSessions();
    
    $sessionid = $_SESSION['userinfo']['SessionID'];
    $_AccountSessions->ExecuteQuery("UPDATE accountsessions SET DateEnded = 'now_usec()' WHERE SessionID = '$sessionid'");
    $_Log->logEvent(AuditFunctions::LOGOUT, $_SESSION['userinfo']['Username'] .':Successful', array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$sessionid));
}

session_destroy();

URL::Redirect("login.php");

?>