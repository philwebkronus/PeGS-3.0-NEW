<div id="login-container">
    <form method="post" id="pass-form">
        <fieldset>
            <legend><?php echo $this->legend; ?></legend>
            <br /><br />
            <div class="label2">
                <?php echo MI_HTML::label($loginForm, 'passkey', 'Enter Passkey:') ?>
                <br /><br />
            </div>  
            <br />
            <div class="input1">
                <?php echo MI_HTML::inputText($loginForm, 'passkey',
                        array('size'=>10,'maxlength'=>8,
                            'onkeypress'=>'return numberonly(event);',
                            'ondragstart'=>'return false',
                            'onselectstart'=>'return false',
                            'onpaste'=>'return false')) ?>
            </div>
            <br />
            <div id="login-button">
                <input type="submit" value="Submit Passkey" id="btnPasskey"/>
            </div>
        </fieldset>
    </form>
</div>
<?php if($loginForm->error_count > 0): ?>
<script>
$(document).ready(function(){
    var passkey_error = '<?php echo $loginForm->getAttributeErrorMessage('passkey'); ?>';
    var message_error = '<?php echo $loginForm->getAttributeErrorMessage('message'); ?>';
    var msg = '';
    if(passkey_error)
        msg = passkey_error;
    else
        msg = message_error;

    if(msg)
        alert(msg);
});
</script>
<?php endif; ?>   

<script>
$(document).ready(function(){
    $('#btnPasskey').click(function(){
        if($.trim($('#LoginFormModel_passkey').val()) == '') {
            alert('Please enter your passkey');
            $('#LoginFormModel_passkey').focus();
            return false;
        }
    });
});
</script>