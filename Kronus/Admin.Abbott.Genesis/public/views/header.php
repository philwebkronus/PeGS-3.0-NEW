<?php
if (!isset($_SESSION))
{
    session_start();
}

if(isset($_SESSION['sessionID']) == ""){
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}

if (isset ($_POST['mid']))
{
    
    $menuid = $_POST['mid'];
    
    $_SESSION['mid'] = $menuid;
}
else
{
    if(isset($_SESSION['mid']) != "") {
        $menuid = $_SESSION['mid'];
    }
    else
    {
        $menuid = "";
    }
}

 $menugroup='';
if (isset ($_POST['group']))
{
    $menugroup = $_POST['group'];
}
else
{
    if(isset($_SESSION['menugroup']))
    {
        $menugroup = $_SESSION['menugroup'];
    }
    else
    {
        $menugroup = "";
    }
}

$_SESSION['menugroup'] = $menugroup;

//echo "mid=".$menuid."XXX";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $pagetitle; ?></title>

        <script type="text/javascript" src="jscripts/jquery-1.4.1.js"></script>
<!--        <script type="text/javascript" src="../jscripts/jquery.datepick.js"></script>        -->
        <script type="text/javascript" src="jscripts/validations.js"></script>
        <script type="text/javascript" src="jscripts/ajax.js"></script>
        <script type="text/javascript" src="jscripts/npos.js"></script>
        <script type="text/javascript" src="jscripts/menu.js"></script>
        <!--<script type="text/javascript" src="jscripts/jquery.jqGrid.js"></script>-->
        <link rel="stylesheet" type="text/css" href="jscripts/jquery.jqGrid-4.3.1v/css/ui.jqgrid.css" />
        <script type="text/javascript" src="jscripts/jquery.jqGrid-4.3.1v/js/i18n/grid.locale-en.js"></script>
        <script type="text/javascript" src="jscripts/jquery.jqGrid-4.3.1v/js/jquery.jqGrid.min.js"></script>

        <link rel="stylesheet" type="text/css" href="css/npos.css" media="screen" />
<!--        <link rel="stylesheet" type="text/css" href="../css/jquery.datepick.css" media="screen" />-->
        <link href="css/jquery-ui-1.8.1.custom.css" rel="stylesheet" type="text/css" />
        <link href="css/ui.multiselect.css" rel="stylesheet" type="text/css" />
        <link href="css/ui.jqgrid.css" rel="stylesheet" type="text/css" />
        
	<!-- DATE PICKER  -->                                                        
        <script type="text/javascript" src="jscripts/datetimepicker.js"></script>
        
        <!-- this uses date and time-->
        <script type="text/javascript" src="jscripts/datetimepicker1.js"></script>
        
         <script type="text/javascript">
             function preventBackandForward()
             {
                 window.history.forward();
             }

            preventBackandForward();
            window.inhibited_load=preventBackandForward;
            window.onpageshow=function(evt){if(evt.persisted)preventBackandForward();};
            window.inhibited_unload=function(){void(0);};
            
            //this will disable the right click
                   var isNS = (navigator.appName == "Netscape") ? 1 : 0;
                   if(navigator.appName == "Netscape") document.captureEvents(Event.MOUSEDOWN||Event.MOUSEUP);
                   function mischandler(){
                        return false;
                   }
                   function mousehandler(e){
                         var myevent = (isNS) ? e : event;
                         var eventbutton = (isNS) ? myevent.which : myevent.button;
                         if((eventbutton==2)||(eventbutton==3)) return false;
                   }
                   document.oncontextmenu = mischandler;
                   //document.onmousedown = mousehandler;
                   //document.onmouseup = mousehandler;
      
            //for inputting of amounts
            jQuery(document).ready(function(){
                //disable 0 on first input
               jQuery('.auto').bind('blur', function()
               {
                    if(jQuery(this).val().substr(0,1) == '0')
                    {
                        jQuery(this).val("");
                        return false;
                    }
                    else
                    {
                        return true;
                    }
               });
            });
        </script>
        <!-- For formatting of amounts-->
        <script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
        <script type="text/javascript" src="jscripts/autoLoader.js"></script>
    </head>
    <body>
        <div id="container">
<!--
            <div id="header">
                
                <h1>New POS</h1>
                
            </div>
            -->
            <div id="navigation">
                
                <?php include "menulist.php"; ?>
                
            </div>
            
            <div id="content-container">
                <div id="blockl" class="white_content" style="width: 300px;">
                    <br />
                    <div align="center" style="font-size: 20px;">
                        <?php echo "Forbidden!"; ?>
                    </div>
                    <br /><br />
                    <div align="center">
                        <input type="button" id="btnok" value="OK"  onclick="window.location.href='process/ProcessLogout.php';" />
                    </div>
                </div>
                <div id="blockf" class="black_overlay"></div>
