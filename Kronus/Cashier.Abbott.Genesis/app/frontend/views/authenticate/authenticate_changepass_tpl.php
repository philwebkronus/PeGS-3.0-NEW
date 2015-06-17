<div id="login-container">
    <fieldset>
        <legend><?php echo $this->legend; ?></legend>
        <form method="post" id="changepass-form" action="<?php echo Mirage::app()->createUrl('changepass') ?>">
        <br />
        <div class="row1 push-down20 append20">
            <?php echo MI_HTML::label($loginForm, 'username', 'Username:') ?>
            <?php echo MI_HTML::inputText($loginForm, 'username',array(
                'onkeypress'=>'javascript: return numberandletter(event);',
                'ondragstart'=>'return false',
                'onselectstart'=>'return false',
                'onpaste'=>'return false',
                'maxlength'=>20)) ?>
        </div> 
        <div class="row1 push-down20 append20">
            <?php echo MI_HTML::label($loginForm, 'email', 'Email Address:') ?>
            <?php echo MI_HTML::inputText($loginForm, 'email',array(
                'onkeypress'=>'javascript: return numberandletter2(event);',
                'ondragstart'=>'return false',
                'onselectstart'=>'return false',
                'onpaste'=>'return false')) ?>
        </div>
        </form>
        <div id="login-button">
            <a href="<?php echo Mirage::app()->createUrl('forgotwizard'); ?>" class="btnAnchor">Back</a>
            <a id="btnChangepass" class="btnAnchor">Submit</a>
        </div>
    </fieldset>
</div>
<script type="text/javascript">
$(document).ready(function() {
    var msg = '';
    var message_error = '<?php echo $loginForm->getAttributeErrorMessage('message'); ?>';
    var email_error = '<?php echo $loginForm->getAttributeErrorMessage('email'); ?>';
    
    if(email_error)
        msg = email_error;
    else if(message_error)
        msg = message_error;
    
    if(msg)
        alert(msg);
    
    $('#btnChangepass').click(function(){
        var userval = $.trim($('#LoginFormModel_username').val());
        var emailval = $.trim($('#LoginFormModel_email').val());
        if(userval == '') {
            alert('Username is required');
            return false;
        }
        if(emailval == '') {
            alert('Email Address is required');
            return false;
        }
        $('#changepass-form').submit();
    });

});
</script>