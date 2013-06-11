<?php
/*
 * @author : owliber
 * @date : 2013-05-17
 */

require_once("../init.inc.php");

$pagetitle = "Membership Administration ";

App::LoadModuleClass("Admin","Accounts");
App::LoadModuleClass("Admin", "AccountStatus");
App::LoadModuleClass("Admin", "AccessRights");
App::LoadModuleClass("Admin","SiteAccounts");

App::LoadCore("URL.class.php");
App::LoadCore("Hashing.class.php");

App::LoadControl("TextBox");
App::LoadControl("Button");

$accounts = new Accounts();
$accessrights = new AccessRights();
$_SiteAccounts = new SiteAccounts();

$fproc = new FormsProcessor();

$txtUsername = new TextBox("txtUsername", "txtUsername", "Username ");
$txtUsername->Length = 30;
$txtUsername->Size = 30;
$txtUsername->ShowCaption = false;
$txtUsername->CssClass = "validate[required]";
$fproc->AddControl($txtUsername);

$txtPassword = new TextBox("txtPassword", "txtPassword", "Password ");
$txtPassword->Length = 30;
$txtPassword->Size = 30;
$txtPassword->Password = true;
$txtPassword->ShowCaption = false;
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
    
    $user = $accounts->validate($username);    
    
    if(count($user) > 0)
    {
        $status = $user[0]['Status'];
        
        // Valid and active account
        if($status == AccountStatus::Active)
        {
            $result = $accounts->authenticate($username,$password);

            if(count($result) > 0)
            {            
                //Get account info
                $row = $result[0];

                $accounttypeid = $row['AccountTypeID'];
                $_SESSION['userinfo']['Username'] = $username;
                $_SESSION['userinfo']['AID'] = $row['AID'];
                $_SESSION['userinfo']['AccountTypeID'] = $accounttypeid;

                if ($accounttypeid == 4) //Cashier
                {
                    $arrsiteaccounts = $_SiteAccounts->getSiteIDByAID($row['AID']);
                    $siteaccount = $arrsiteaccounts[0];
                    $_SESSION['userinfo']['SiteID'] = $siteaccount['SiteID'];
                }

                //Get user access
                $access = $accessrights->getAccessRights($accounttypeid);
                $_SESSION['menus'] = $access;

                $defaultpage = $accessrights->getDefaultPage($accounttypeid);

                if(isset($defaultpage) && count($defaultpage) > 0)
                {
                    $link = $defaultpage[0]['Link'];

                    if($link == '#')
                    {
                        //Get landing submenu page
                        $defaultpage = $accessrights->getLandingSubPage($accounttypeid);
                        $link = $defaultpage[0]['Link'];

                    }

                    URL::Redirect($link);
                }

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
    else
    {
        App::SetErrorMessage('Invalid Account');
    }
    
}
?>  
<?php include('header.php'); ?>
<div id="login">
    <div class="login-title"><?php echo $pagetitle; ?></div>
    <label for="Username">Username</label>
    <div class="inputfield"><?php echo $txtUsername; ?></div>
    <label for="Password">Password</label>
    <div class="inputfield"><?php echo $txtPassword; ?></div>
    <div class="inputfield"><?php echo $btnLogin; ?></div>
</div>
<?php include('footer.php'); ?>
