<?php
$username = htmlentities($_GET['username']);
$oldPass = htmlentities($_GET['password']);
$vaid = htmlentities($_GET['aid']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Update Password</title>
        <script type="text/javascript" src="jscripts/validations.js"></script>
        <script type="text/javascript" src="jscripts/jquery-1.4.1.js"></script>
        <link rel="stylesheet" type="text/css" href="css/login.css" media="screen" />
        <script type="text/javascript">
            jQuery(document).ready(function(){
               var url = 'process/ProcessLogin.php'; 
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
              
               jQuery.ajax({
                   url: url,
                   type: 'post',
                   data: {page: function(){ return 'CheckChangePassword'},
                          aid : function(){return jQuery("#txtaid").val();}
                         },
                   success: function(data)
                   {
                   },
                   error: function(XMLHttpRequest, e)
                   {
                       alert(XMLHttpRequest.responseText);
                       if(XMLHttpRequest.status == 401)
                       {
                           window.location.href = 'login.php';
                       }
                   }
               });
          });
        </script>
    </head>
    <body onload="document.getElementById('txtnewpassword').focus();">
        <form method = "post" action="process/ProcessLogin.php">
            <input type="hidden" name="page" value="UpdatePassword" />
            <input type="hidden" name="txtaid" id="txtaid" value="<?php echo $vaid; ?>" />
            <fieldset style="width:400px;height:300px;">
                <legend>Update Password</legend>
                <br />
                <label for="chngeuser">UserName</label>
                <input type="text" id="chngeuser" name="chngeuser" maxlength="20"  size="20" value="<?php echo $username; ?>" readonly="readonly" />
                <br /><br />
                <label for="txtoldpassword">Old Password</label>
                <input type="password" id="txtoldpassword" name="txtoldpassword" value="<?php echo $oldPass;?>" readonly="readonly" />
                <br /><br />
                <label for="txtnewpassword">New Password</label>
                <input type="password" id="txtnewpassword" name="txtnewpassword" maxlength="12"  onkeypress="javascript: return numberandletter(event);" ondragstart="return false" onselectstart="return false" onpaste="return false"/>
                <br /><br />
                <label for="txtconfirmpass">Confirm Password</label>
                <input type="password" id="txtconfirmpass" name="txtconfirmpass" maxlength="12" onkeypress="javascript: return numberandletter(event);" ondragstart="return false" onselectstart="return false" onpaste="return false" />
                <br /><br />
                <p class="submit">
                    <input type="submit" value="Update Password" onclick="return chkupdatepass();" />
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