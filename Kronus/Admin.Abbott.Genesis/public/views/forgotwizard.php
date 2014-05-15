<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
         <title>Can't access account</title> 
         <script type="text/javascript" src="jscripts/jquery-1.4.1.js"></script>
         <script type="text/javascript" src="jscripts/validations.js"></script>    
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
        <link rel="stylesheet" type="text/css" href="css/login.css" media="screen" />
    </head>
    <body onload="javascript:preventBackandForward();">
        <form method="post" action="#">
            <fieldset style="width:350px;height:190px;">
                <p class ="legend">Experiencing Problems?</p>
               
                <br />
                <table align="center">
                    <tr>
                        <td><input type="radio" name="forgot" id="changepass" CHECKED style="border:none;"/></td>
                        <td>Change Password?</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="forgot" id="forgotpass" style="border:none;" /></td>
                        <td>Forgot Password?</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="forgot" id="forgotuname" style="border:none;" /></td>
                        <td>Forgot Username?</td>
                    </tr>
                </table>
                <br />
                <p class="submit">
                    <input type="button" value="Back to Login" onclick="window.location.href='login.php';"/>
                    <input type="button" value="Next" onclick="return loadWizard();"/>
                </p>
                </fieldset>
               
        </form>
    </body>
</html>
