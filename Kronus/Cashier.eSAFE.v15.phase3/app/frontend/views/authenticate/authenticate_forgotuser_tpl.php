<div id="login-container">
    <fieldset>
        <legend><?php echo $this->legend; ?></legend>
        <form method="post" id="forgotuser-form">
            <br />
            <div class="row1 push-down50 append20">
                <?php echo MI_HTML::label($loginForm, 'email', 'Email Address:') ?>
                <?php echo MI_HTML::inputText($loginForm, 'email',array(
                    'onkeypress'=>'javascript: return numberandletter2(event);',
                    'ondragstart'=>'return false',
                    'onselectstart'=>'return false',
                    'onpaste'=>'return false')) ?>
            </div>            
        </form>
        <br />
        <div id="login-button" >
            <a href="<?php echo Mirage::app()->createUrl('forgotwizard'); ?>" class="btnAnchor">Back</a>
            <a id="btnForgotuser" class="btnAnchor">Submit</a>
        </div>            
    </fieldset>
</div>
<script type="text/javascript">
    var email_error = '<?php echo $loginForm->getAttributeErrorMessage('email'); ?>';
    var message_error = '<?php echo $loginForm->getAttributeErrorMessage('message'); ?>';
    if(email_error) {
        alert(email_error);
    } else if(message_error) {
        alert(message_error);
    }
    $('#btnForgotuser').click(function(){
        var emailval = $.trim($('#LoginFormModel_email').val());
        if(emailval == '') {
            alert('Email Address is required');
            return false;
        }
        $('#forgotuser-form').submit();
    });
</script>