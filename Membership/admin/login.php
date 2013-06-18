<?php
/*
 * @author : owliber
 * @date : 2013-05-17
 */

require_once("../init.inc.php");

$pagetitle = "Membership Administration ";

App::LoadModuleClass("Admin", "AccountStatus");
App::LoadModuleClass("Admin", "AccessRights");
App::LoadModuleClass("Kronus","SiteAccounts");
App::LoadModuleClass("Admin", "AccountSessions");
App::LoadModuleClass("Kronus", "Accounts");
App::LoadModuleClass("Kronus", "AccountTypes");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Membership", "AuditTrail");

App::LoadCore("URL.class.php");
App::LoadCore("Hashing.class.php");

App::LoadControl("TextBox");
App::LoadControl("Button");

$accounts = new Accounts();
$accessrights = new AccessRights();
$_SiteAccounts = new SiteAccounts();
$_AccountSessions = new AccountSessions();
$_Log = new AuditTrail();

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

                if ($accounttypeid == AccountTypes::Cashier) //Cashier
                {
                    $arrsiteaccounts = $_SiteAccounts->getSiteIDByAID($row['AID']);
                    $siteaccount = $arrsiteaccounts[0];
                    $_SESSION['userinfo']['SiteID'] = $siteaccount['SiteID'];
                }

                //Get user access
                $access = $accessrights->getAccessRights($accounttypeid);
                
                if(count($access) > 0)
                {
                    // Insert into account session
                    $arrEntry['SessionID'] = uniqid();
                    $arrEntry['AID'] = $row['AID'];
                    $arrEntry['RemoteIP'] = $_SERVER['REMOTE_ADDR'];
                    $arrEntry['DateStarted'] = "now_usec()";
                    
                    $_AccountSessions->Insert($arrEntry);
                    
                    $_SESSION['menus'] = $access;
                    $_SESSION['userinfo']['SessionID'] = $arrEntry['SessionID'];
                   
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
                    
                    $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Successful', array('ID'=>$row['AID'], 'SessionID'=>$arrEntry['SessionID']));
                }
                else
                {
                    App::SetErrorMessage('Account has no access rights');
                    $_Log->logEvent(AuditFunctions::LOGIN, $username .':No access rights', array('ID'=>$row['AID'], 'SessionID'=>$arrEntry['SessionID']));
                }
                
                
                

            }
            else
            {
                App::SetErrorMessage('Invalid Account');
                $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Invalid account', array('ID'=>"", 'SessionID'=>""));
            }

        }
        elseif ($status != AccountStatus::Active)
        {       
            switch ( $status )
            {
                case AccountStatus::Pending:
                    App::SetErrorMessage('Pending Account');
                    $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Pending account', array('ID'=>"", 'SessionID'=>""));
                    break;
                case AccountStatus::Suspended:
                    App::SetErrorMessage('Suspended Account');
                    $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Suspended account', array('ID'=>"", 'SessionID'=>""));
                    break;
                case AccountStatus::Locked_Attempts:
                    App::SetErrorMessage('Account Locked');
                    $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Account Locked', array('ID'=>"", 'SessionID'=>""));
                    break;
                case AccountStatus::Locked_Admin:
                    App::SetErrorMessage('Admin Locked');
                    $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Admin Locked', array('ID'=>"", 'SessionID'=>""));
                    break;
                case AccountStatus::Banned:
                    App::SetErrorMessage('Banned Account');
                    $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Banned Account', array('ID'=>"", 'SessionID'=>""));
                    break;
                case AccountStatus::Terminated;
                    App::SetErrorMessage('Terminated Account');
                    $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Terminated Account', array('ID'=>"", 'SessionID'=>""));
                    break;
            }

        }
        else
        {
            App::SetErrorMessage('Invalid Account');
            $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Invalid Account', array('ID'=>"", 'SessionID'=>""));
        }
    }
    else
    {
        App::SetErrorMessage('Invalid Account');
        $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Invalid Account', array('ID'=>"", 'SessionID'=>""));
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
