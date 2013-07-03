<script language="javascript" type="text/javascript">
    $(document).ready(function() 
    {
        $("#loginForm").validationEngine();
    });        
</script>
<form name="loginForm" method="post" action="" id="loginForm" />
<div class="login-error"><?php echo App::GetErrorMessage(); ?></div>
<div id="home-login-box">    
    <div id="home-login-wrapper">
        <div id="home-page-login-form">
            <div class="home-login-form-wrapper">
                <div class="home-login-form-label">Username</div>
                <div class="home-login-form-input"><?php echo $txtUsername; ?></div>
                <div class="clearfix"></div>
            </div>
            
            <div class="home-login-form-wrapper">
                <div class="home-login-form-label">Password:</div>
                <div class="home-login-form-input"><?php echo $txtPassword; ?></div>
                <div class="clearfix"></div>
            </div>
            
            <div class="home-login-form-wrapper">
                <div class="home-login-form-label">&nbsp;</div>
                <div class="home-login-form-input"><?php echo $btnLogin; ?></div>
                <div class="clearfix"></div>
            </div>
            <div id="home-login-form-link">
                <a href="forgotpassword.php">Forgot Password?</a><br/>
                Not yet a member? Sign up <a href="registration.php">here</a>
            </div>
            
        </div>
    </div>
<!--</div>-->
</form>

