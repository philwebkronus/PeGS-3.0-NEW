<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Change Password</title>
        <script type="text/javascript" src="jscripts/jquery-1.4.1.js"></script>
        <script type="text/javascript">
             function preventBackandForward()
             {
                 window.history.forward();
             }
             preventBackandForward();
             window.inhibited_load=preventBackandForward;
             window.onpageshow=function(evt){if(evt.persisted)preventBackandForward();};
             window.inhibited_unload=function(){void(0);};


             jQuery(document).ready(function(){
                  //this will disable all cut, copy, paste on all textbox
                  jQuery(':text').live("cut copy paste",function(e) {
                      e.preventDefault();
                  });

                  jQuery(':password').live("cut copy paste",function(e) {
                      e.preventDefault();
                  });


                  //this will disable the right click
                  //http://www.reconn.us/content/view/36/45/
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
                   document.onmousedown = mousehandler;
                   document.onmouseup = mousehandler;
              });
        </script>
        <script type="text/javascript" src="jscripts/validations.js"></script>
        <link rel="stylesheet" type="text/css" href="css/login.css" media="screen" />
    </head>
    <body onload="javascript:preventBackandForward();document.getElementById('txtusername').focus();">
        <form method="post" action="process/ProcessLogin.php">
            <input type="hidden" name="page" value="ChangePassword" />
            <fieldset style="width:350px;height:230px;">
                <p class ="legend">Change Password</p>
                <br />
                <table align="center">
                    <tr>
                        <td>User Name:</td>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="txtusername" maxlength="20" id="txtusername" onkeypress="return numberandletter(event);" ondragstart="return false" onselectstart="return false" onpaste="return false"/>
                        </td>
                    </tr>
                    <tr>
                        <td height="15"></td>
                    </tr>
                    <tr>
                        <td>Email Address:</td>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="txtemail" id="txtemail" maxlength="100" ondragstart="return false" onselectstart="return false" onpaste="return false" onkeypress="return emailkeypress(event);"/>
                        </td>
                    </tr>
                </table>
    
                <p class="submit">
                    <input type="button" value="Back" onclick="window.location.href='forgotwizard.php';"/>
                    <input type="submit" value="Submit" onclick="return chkChangePwd();"/>
                </p>
            </fieldset>
        </form>
    </body>
</html>
<!--  For Javascript Alert Dialog (Errors)  -->        
<?php
    if(isset($_GET['mess']))
       {
        $msg = $_GET['mess'];
?>
<script type="text/javascript" language="javascript">
    $(document).ready(function(){
        <?php echo "alert('".$msg."');"; ?>
    });
</script>
<?php
         }
?>