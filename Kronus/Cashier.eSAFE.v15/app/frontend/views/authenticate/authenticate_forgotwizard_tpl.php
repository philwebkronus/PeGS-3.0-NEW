<div id="login-container">
    <fieldset>
        <legend><?php echo $this->legend; ?></legend>
        <br />
        <form method="post" id="forgot-wizard-form" action="">
        <div class="row push-down20 append50">
            <table class="push40">
                <tr>
                    <td><input type="radio" class="prob" name="prob" value="1" checked="checked" id="cpass" /></td>
                    <td class="left"><label for="cpass">Change Password?</label></td>
                </tr>
                <tr>
                    <td><input type="radio" class="prob" name="prob" value="2" id="fpass"/></td>
                    <td class="left"><label for="fpass">Forgot Password?</label></td>
                </tr>
                <tr>
                    <td><input type="radio" class="prob" name="prob" value="3" id="fuser" /></td>
                    <td class="left"><label for="fuser">Forgot Username?</label></td>
                </tr>                    
            </table>
        </div>   
        <div id="login-button">
            <a href="<?php echo Mirage::app()->createUrl('login') ?>" class="btnAnchor">Back to Login</a>
            <a id="login-btnnext" class="btnAnchor">Next</a>
        </div>            
        </form>    
    </fieldset>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $('#login-btnnext').click(function(){
        var rad_val = $('.prob:checked').val();
        var url;
        switch(rad_val) {
            case '1':
                url = '<?php echo Mirage::app()->createUrl('changepass') ?>';
                break;
            case '2':
                url = '<?php echo Mirage::app()->createUrl('forgotpass') ?>';
                break;
            case '3':
                url = '<?php echo Mirage::app()->createUrl('forgotuser') ?>';
                break;
        }
        $(this).attr('href',url);
    });
});
</script>