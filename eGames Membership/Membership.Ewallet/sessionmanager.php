<?php

require_once("init.inc.php");

App::LoadCore("URL.class.php");
App::LoadModuleClass("Membership", "MemberSessions");


   if(isset($_SESSION['sessionID'])){
        $sessionid = $_SESSION['sessionID'];
        $aid = $_SESSION['MID'];
    }
    else{
        $sessionid = 0;
        $aid = 0;
    }
    //Check restricted page
    
    $_MemberSessions = new MemberSessions();
    
    $sessioncount = $_MemberSessions->checkifsessionexist($aid, $sessionid);
    foreach ($sessioncount as $value) {
        $sessioncount = $value['Count'];
//        foreach ($value as $value2) {
//            $sessioncount = $value2['Count'];
//        }
    }
    
    if($sessioncount > 0)
    {
        $msg = "Not Connected";
    }
    else 
    {
        $msg = "Not Connected";
        session_destroy();
       echo'<script> alert("Session is Expired"); window.location="index.php"; </script> ';
    }

?>
