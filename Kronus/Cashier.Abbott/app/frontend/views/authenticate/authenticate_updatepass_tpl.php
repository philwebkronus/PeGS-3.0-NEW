<div id="login-container">
    <fieldset>
        <legend><?php echo $this->legend; ?></legend>
        <form method="post" id="changepass-form" action="<?php echo Mirage::app()->createUrl('updatepassword') ?>">
        <br />
        <div class="row1 push-down10 append20">
            <?php echo MI_HTML::label($loginForm, 'username', 'Username:') ?>
            <?php echo MI_HTML::inputText($loginForm, 'username',array('readonly'=>'readonly')) ?>
        </div> 
        <div class="row1 push-down20 append20">
            <?php echo MI_HTML::label($loginForm, 'password', 'Old Password:') ?>
            <?php echo MI_HTML::inputPassword($loginForm, 'password',array('readonly'=>'readonly')) ?>
        </div>
        <div class="row1 push-down20 append20">
            <?php echo MI_HTML::label($loginForm, 'newpassword', 'New Password:') ?>
            <?php echo MI_HTML::inputPassword($loginForm, 'newpassword',array('ondragstart'=>'return false','onselectstart'=>'return false','onpaste'=>'return false')) ?>
        </div>
        <div class="row1 push-down20 append20">
            <?php echo MI_HTML::label($loginForm, 'confirmpassword', 'Confirm Password:') ?>
            <?php echo MI_HTML::inputPassword($loginForm, 'confirmpassword',array('ondragstart'=>'return false','onselectstart'=>'return false','onpaste'=>'return false')) ?>
        </div>
        <?php echo MI_HTML::inputHidden($loginForm, 'aid') ?>
        <div id="login-button">
            <input type="submit" value="Update Password" />
        </div>
        </form>
    </fieldset>
</div>
<script type="text/javascript">
$(document).ready(function(){
   var error_newpass = '<?php echo $loginForm->getAttributeErrorMessage('newpassword'); ?>'; 
   var error_confirm = '<?php echo $loginForm->getAttributeErrorMessage('confirmpassword'); ?>'; 
   if(error_newpass != '') {
       alert(error_newpass);
   }else if(error_confirm != '') {
       alert(error_confirm);
   }
    <?php if($error != ''): ?>
        alert('<?php echo $error; ?>');
    <?php endif; ?>   
});
</script>