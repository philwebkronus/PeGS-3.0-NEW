<div id="login-container">
    <form method="post" action="<?php echo Mirage::app()->createUrl('login') ?>">
        <fieldset>
            <legend><?php echo $this->legend; ?></legend>
            <br />
<!--            <input type="hidden" id="hidreferrer" value="<?php //echo Mirage::app()->param['referrer']?>" />-->
            <div class="row">
                <?php echo MI_HTML::label($loginForm, 'username', 'User Name:') ?>
                <?php echo MI_HTML::inputText($loginForm, 'username',array(
                        'onkeypress'=>'javascript: return numberandletter(event);',
                        'ondragstart'=>'return false',
                        'onselectstart'=>'return false',
                        'onpaste'=>'return false',
                        'maxlength' => 20)) ?>
            </div>
            <div class="row push-down10">
                <?php echo MI_HTML::label($loginForm, 'password', 'Password:') ?>
                <?php echo MI_HTML::inputPassword($loginForm, 'password',array(
                    'onkeypress'=>'javascript: return numberandletter(event);',
                    'ondragstart'=>'return false',
                    'onselectstart'=>'return false',
                    'onpaste'=>'return false',
                    'maxlength' => 12)) ?>
            </div>
            <div id="login-button">
                <input type="submit" value="Login Now!" id="btnLogin"/>
            </div>
            <div class="center push-down10">
                <a href="<?php echo Mirage::app()->createUrl('forgotwizard') ?>">Can't access my account?</a>
            </div>
        </fieldset>
    </form>    
  
</div>
<?php if($loginForm->error_count > 0): ?>
<script type="text/javascript">
$(document).ready(function(){
    var username_error = '<?php echo $loginForm->getAttributeErrorMessage('username'); ?>';
    var password_error = '<?php echo $loginForm->getAttributeErrorMessage('password'); ?>';
    //var message_error = '<?php echo $loginForm->getAttributeErrorMessage('message'); ?>';
    var msg = '';
    if(username_error)
        msg = username_error;
    else if(password_error)
        msg = password_error;
//    else
//        msg = message_error;

    if(msg)
        alert(msg);
    
    <?php if($error != ''): ?>
        alert('<?php echo $error; ?>');
    <?php endif; ?>
        
        
        $('#LoginFormModel_username').focus();    
});
</script>
<?php endif; ?>   
<script type="text/javascript">
    $(document).ready(function(){
        <?php if(isset($_GET['error']) && $_GET['error'] != ''): ?>
            alert('<?php echo htmlentities($_GET['error']) ?>');
        <?php endif; ?>    
            
        <?php if(isset($_SESSION['notification'])): ?>
                var notification = '<?php echo $_SESSION['notification'] ?>';
                <?php unset($_SESSION['notification']); ?>
                alert(notification);
                window.location.reload(true);
        <?php endif; ?>
        

        
//        alert('<?php //echo $_SESSION['error_message']; ?>');
        <?php if(isset($_SESSION['error_message'])): ?>
                var error_passkey = '<?php echo $_SESSION['error_message'] ?>';
                <?php unset($_SESSION['error_message']); ?>
                alert(error_passkey);
        <?php endif; ?>
            
        $('#btnLogin').click(function(){
            if($.trim($('#LoginFormModel_username').val()) == '') {
                alert('Please enter your username');
                $('#LoginFormModel_username').focus();
                return false;
            }
            
//            if($.trim($('#LoginFormModel_password').val()) == '') {
//                alert('Please enter your password');
//                $('#LoginFormModel_password').focus();
//                return false;
//            }
        });
        
        $('form').submit(function(e){
            e.preventDefault();
                      
                // var hidreferrer = $('#hidreferrer').val();
                 var self = this;
//                       if(option == 1 || option == '1'){
//                           return false;
//                           //event.preventDefault();
//                       }
//                       else
//                           return true;
                  var url = '<?php echo Mirage::app()->createUrl('checkreferrer') ?>';
                  $.ajax({
                  url: url,
                  type: 'post',
//                  data: {hidreferrer: hidreferrer,
//////                         cmbsitename: function(){return jQuery("#cmbsitename").val();},
//////                         txtposacc : function() {return posaccount;}
//                        },
                  dataType: 'text',
                  cahe: false,
                  success: function(result){
                        if($.trim(result) == "Authorized")
                            self.submit();
//                      jQuery.each(data, function()
//                      {
//                            alert(result);
                                
//                            }
//                            else {
                               
//                            }
                            
//                          jQuery("#lblopsname").text(this.Username);
//                          jQuery("#txtownerID").val(this.AccountTypeID);
//                          jQuery("#txtsiteID").val(this.SiteID);
//                          var sitecode = jQuery("#cmbsitename").find("option:selected").text();
//                          jQuery("#lblsitecode").text(sitecode);
//                          jQuery("#txtsitecode").val(sitecode);
                     // });
//                      document.getElementById('light').style.display='block';
//                      document.getElementById('fade').style.display='block';
                  },
                  error: function(XMLHttpRequest, e){
   
                        alert('Forbidden');
//                        if(XMLHttpRequest.status == 403)
//                        {
//                            window.location.reload();
//                        }
                  }
                  
               });
        });
        
        
    });
</script>
<script type="text/javascript" src="jscripts/getMachineInfo.js"></script>
