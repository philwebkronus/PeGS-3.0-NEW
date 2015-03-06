<?php session_start();
if($_SESSION['sessionID'] == ""){
    header("Location: login.php");
}
?>
<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Welcome  Page</title>
    </head>
    <body>
        <div style="float:right;">
            <a href="../process/ProcessLogout.php">Logout</a>
        </div>
         <?php      
            $xmlDoc = new DOMDocument();
            $xmlDoc->load("newXMLDocument.xml");
           
            
           $x = $xmlDoc->documentElement;
          foreach ($x->right AS $item)
          {
             print $item->menuid . " = " . $item->menuname . "<br />";
             print $item->submenus . " = " . $item->submenusname . "<br />";
          }
          ?> 
        
    </body>
</html>
