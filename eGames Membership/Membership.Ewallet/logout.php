<?php

/*
 * @author : owliber
 * @date : 2013-05-03
 */

require_once("init.inc.php");


App::LoadCore("URL.class.php");
ob_start();
if(isset($_SESSION['MemberInfo']))
{
    App::LoadModuleClass("Membership", "AuditTrail");
    App::LoadModuleClass("Membership", "AuditFunctions");
    App::LoadModuleClass("Membership", "MemberSessions");
    
    $username = $_SESSION['MemberInfo']['UserName'];
    //$accounttypeid = $_SESSION['MemberInfo']['AccountTypeID'];
    $id = $_SESSION['MemberInfo']['MID'];
    $sessionid = $_SESSION['MemberInfo']['SessionID'];
           
    $_MemberSessions = new MemberSessions();
    $session = $_MemberSessions->checkifsessionexist($id, $sessionid);
        foreach ($session as $value) {
        foreach ($value as $value2) {
            $sessioncount = $value2['Count'];
        }
    }
    
    if($sessioncount > 0){
        $_MemberSessions->deleteifsessionexist($id, $sessionid);
    }
    $_Log = new AuditTrail();
    $_Log->logEvent(AuditFunctions::LOGOUT, $username, array('ID'=>$id, 'SessionID'=>$sessionid));
}
    
session_destroy();
URL::Redirect("index.php");
ob_end_flush();
?>

