<?php

/*
 * @author : owliber
 * @date : 2013-05-03
 */

require_once("init.inc.php");


App::LoadCore("URL.class.php");

if(isset($_SESSION['MemberInfo']))
{
    App::LoadModuleClass("Membership", "AuditTrail");
    App::LoadModuleClass("Membership", "AuditFunctions");
    
    $username = $_SESSION['MemberInfo']['UserName'];
    $accounttypeid = $_SESSION['MemberInfo']['AccountTypeID'];
    $id = $_SESSION['MemberInfo']['MID'];
    $sessionid = $_SESSION['MemberInfo']['SessionID'];
            
    $_Log = new AuditTrail();
    $_Log->logEvent(AuditFunctions::LOGOUT, $username, array('ID'=>$id, 'SessionID'=>$sessionid));
}
    
session_destroy();
URL::Redirect("index.php");

?>

