<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>POS Kronus - Login</title>      
        <script type="text/javascript" src="jscripts/jquery-1.4.1.js"></script>
        <script type="text/javascript" src="jscripts/validations.js"></script>
        <script type="text/javascript">
             jQuery(document).ready(function(){
                  //this will disable all cut, copy, paste on all textbox
                  jQuery(':text').live("cut copy paste",function(e) {
                      e.preventDefault();
                  });

                  jQuery(':password').live("cut copy paste",function(e) {
                      e.preventDefault();
                  });
                  
                  jQuery('#browser').val(jQuery.browser.msie);
                  jQuery('#version').val(jQuery.browser.version);
                  jQuery('#chrome').val(jQuery.browser.safari);
                  jQuery('#txtusername').focus();
                    
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
                   document.onmousedown = mousehandler;
                   document.onmouseup = mousehandler;
                   
                   jQuery('form').submit(function(e){
                       e.preventDefault();
                      
                       //var hidreferrer = jQuery('#hidreferrer').val();
                       var self = this;

                       jQuery.ajax({
                            url: 'checkreferrer.php',
                            type: 'post',
//                            data: {hidreferrer: hidreferrer,
//
//                                  },
                            dataType: 'text',
                            cahe: false,
                            success: function(result){
                                  if($.trim(result) == "Authorized")
                                      self.submit();

                            },
                            error: function(XMLHttpRequest, e){

                                  alert('Forbidden');
//                                  if(XMLHttpRequest.status == 403)
//                                  {
//                                      window.location.reload();
//                                  }
                            }

                            });
               

                       });
             });
             
             function preventBackandForward()
             {
                 window.history.forward();
             }
             preventBackandForward();
             window.inhibited_load=preventBackandForward;
             window.onpageshow=function(evt){if(evt.persisted)preventBackandForward();};
             window.inhibited_unload=function(){void(0);};
        </script>     
        <link rel="stylesheet" type="text/css" href="css/login.css" media="screen" />
    </head>
    <body onload="preventBackandForward();document.getElementById('txtusername').focus();">        
        <form action='process/ProcessLogin.php' method='post'>        
            <input type="hidden" name="page" value="LoginPage" />
            <input type="hidden" name ="browser" id="browser" />
            <input type="hidden" name="version" id="version" />
            <input type="hidden" name="chrome" id="chrome"  />
<!--            <input type="hidden" name="hidreferrer" id="hidreferrer" value="<?php //echo $_SERVER['HTTP_REFERER'];?>"/>-->
                       
            <fieldset>
                <p class ="legend">POS Kronus - Login</p>
<!--                <legend>POS Kronus - Login</legend>-->
                <br />
                <label for="txtusername">User Name:</label>
                <input style="width:200px;" type =" text" name="txtusername" id="txtusername" maxlength="20" onkeypress="javascript: return numberandletter(event);" ondragstart="return false" onselectstart="return false" onpaste="return false"/>
                <br /><br />
                <label for="txtpassword">Password:</label>
                <input style="width:200px;" type ="password" name="txtpassword" id="txtpassword" maxlength="12"  onkeypress="javascript: return numberandletter(event);" ondragstart="return false" onselectstart="return false" onpaste="return false" />
                <br />
                <p class="submit"><input type ="submit" value="Login Now!" onclick="return chklogin();"></p>
                <p class="otherlink"><a href="forgotwizard.php">Can't access my account?</a></p>
                
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
