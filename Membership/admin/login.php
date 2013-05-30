<?php
/*
 * @author : owliber
 * @date : 2013-05-17
 */

require_once("../init.inc.php");

$pagetitle = "Admin Login";

App::LoadModuleClass("Admin","Accounts");
App::LoadModuleClass("Admin", "AccountStatus");

App::LoadCore("URL.class.php");
App::LoadCore("Hashing.class.php");

App::LoadControl("TextBox");
App::LoadControl("Button");

$accounts = new Accounts();

$fproc = new FormsProcessor();

$txtUsername = new TextBox("txtUsername", "txtUsername", "Username ");
$txtUsername->Length = 30;
$txtUsername->Size = 30;
$txtUsername->ShowCaption = true;
$txtUsername->CssClass = "validate[required]";
$fproc->AddControl($txtUsername);

$txtPassword = new TextBox("txtPassword", "txtPassword", "Password ");
$txtPassword->Length = 30;
$txtPassword->Size = 30;
$txtPassword->Password = true;
$txtPassword->ShowCaption = true;
$txtPassword->CssClass = "validate[required]";
$fproc->AddControl($txtPassword);

$btnLogin = new Button("btnSubmit", "btnSubmit", "Login");
$btnLogin->IsSubmit = true;
$fproc->AddControl($btnLogin);

$fproc->ProcessForms();

if($fproc->IsPostBack)
{   
    $username = $txtUsername->SubmittedValue;    
    $password = $txtPassword->SubmittedValue;
    
    $status = $accounts->validate($username);    
    
    // Valid and active account
    if($status == AccountStatus::Active)
    {
        
        //Authenticate
        if($accounts->authenticate($username,$password))
        {
            $_SESSION['Username'] = $username;
            $_SESSION['AID'] = 1;
            
            URL::Redirect('index.php');
        }
        else
        {
            App::SetErrorMessage('Invalid Account');
        }
        
    }
    elseif ($status != AccountStatus::Active)
    {       
        switch ( $status )
        {
            case AccountStatus::Suspended:
                App::SetErrorMessage('Suspended Account');
                break;
            case AccountStatus::Locked_Attempts:
                App::SetErrorMessage('Account Locked');
                break;
            case AccountStatus::Locked_Admin:
                App::SetErrorMessage('Admin Locked');
                break;
            case AccountStatus::Banned:
                App::SetErrorMessage('Banned Account');
                break;
            case AccountStatus::Terminated;
                App::SetErrorMessage('Terminated Account');
                break;
        }
        
    }
    else
    {
        App::SetErrorMessage('Invalid Account');
    }
}
?>  
<?php include('header.php'); ?>
<?php echo $txtUsername; ?><br/>
<?php echo $txtPassword; ?><br/>
<?php echo $btnLogin; ?>
<?php include('footer.php'); ?>