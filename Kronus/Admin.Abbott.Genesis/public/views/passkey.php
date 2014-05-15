<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Passkey</title>
        <script type="text/javascript" src="../jscripts/jquery-1.4.1.js"></script>
        <script type="text/javascript" src="../jscripts/validations.js"></script>
        <script type="text/javascript">
         function preventBackandForward()
         {
             window.history.forward();
         }

         preventBackandForward();
         window.inhibited_load=preventBackandForward;
         window.onpageshow=function(evt){if(evt.persisted)preventBackandForward();};
         window.inhibited_unload=function(){void(0);};
         //*** end **/
        
         jQuery(document).ready(function(){
              //this will disable all cut, copy, paste on all textbox
              jQuery(':text').live("cut copy paste",function(e) {
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
        <link rel="stylesheet" type="text/css" href="../css/login.css" media="screen" />  
    </head>
    <body onload=" javascript:preventBackandForward();document.getElementById('txtpasskey').focus();">
        <form method="post" action="../process/ProcessPassKey.php">
            <fieldset>
                <legend>Access Passkey</legend>
                <br /><br/>
                <div align="center">
                   <label for="txtusername" style="margin-left: 15px;">Enter Passkey:</label>
                
                <br /><br />
                <input type="text" name="txtpasskey" id="txtpasskey" maxlength="10" size="10" onkeypress="return numberonly(event);" ondragstart="return false" onselectstart="return false" onpaste="return false" />
                </div>
                <br /><br />
                <p class="submit">
                    <input type ="submit" value="Submit Passkey" onclick="return chkpasskey();">
                </p>
            </fieldset>
        </form>
    </body>
</html>
