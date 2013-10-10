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
$txtUsername->Args = 'onkeypress="javascript: return numberandletter(event)"';
$fproc->AddControl($txtUsername);

$txtPassword = new TextBox("txtPassword", "txtPassword", "Password ");
$txtPassword->Length = 12;
$txtPassword->Size = 12;
$txtPassword->Password = true;
$txtPassword->ShowCaption = false;
$txtPassword->CssClass = "validate[required]";
$txtPassword->Args = 'onkeypress="javascript: return numberandletter(event)"';
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
            //check account number of login attempts
            $loginattempts = $accounts->getAttemptCount($username);
            if($loginattempts >= 3)
            {
                App::SetErrorMessage('Access Denied. Please contact system administrator to have your account unlocked.');
                $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Account Locked', array('ID'=>"", 'SessionID'=>""));  
            } else {
                
                $result = $accounts->authenticate($username,$password);

                if(count($result) > 0)
                {            
                    //Get account info
                    $row = $result[0];

                    $accounttypeid = $row['AccountTypeID'];
                    $_SESSION['userinfo']['Username'] = $username;
                    $_SESSION['userinfo']['AID'] = $row['AID'];
                    $_SESSION['userinfo']['AccountTypeID'] = $accounttypeid;

                    $_SESSION['userinfo']['SiteID'] = 1; //Head Office
                    if ($accounttypeid == AccountTypes::Cashier) //Cashier
                    {
                        $arrsiteaccounts = $_SiteAccounts->getSiteIDByAID($row['AID']);
                        $siteaccount = $arrsiteaccounts[0];
                        $_SESSION['userinfo']['SiteID'] = $siteaccount['SiteID'];
                    }
                    if ($accounttypeid == 9)
                    {
                        // Insert into account session
                        $arrEntry['SessionID'] = session_id();
                        $arrEntry['AID'] = $row['AID'];
                        $arrEntry['RemoteIP'] = $_SERVER['REMOTE_ADDR'];
                        $arrEntry['DateStarted'] = "now_usec()";
                        
                        $activesession = $_AccountSessions->checkSession($row['AID']);
                        foreach ($activesession as $value) {
                            foreach ($value as $value2) {
                                $activesession = $value2['Count'];
                            }
                        }
                        
                        if($activesession > 0)
                        {
                            $_AccountSessions->updateSession($arrEntry['SessionID'], $row['AID'], 
                                    $arrEntry['RemoteIP'], $arrEntry['DateStarted']);
                        }
                        else{
                            $_AccountSessions->Insert($arrEntry);
                        }
                
                        $_SESSION['sessionID'] = $arrEntry['SessionID'];
                        $_SESSION['aID'] = $row['AID'];
                        
                        $_SESSION['menus'] = $access;
                        $_SESSION['userinfo']['SessionID'] = $arrEntry['SessionID'];
                        URL::Redirect("pendingredemption.php");
                    }
                    //Get user access
                    $access = $accessrights->getAccessRights($accounttypeid);

                    if(count($access) > 0)
                    {
                        // Insert into account session
                        $arrEntry['SessionID'] = session_id();
                        $arrEntry['AID'] = $row['AID'];
                        $arrEntry['RemoteIP'] = $_SERVER['REMOTE_ADDR'];
                        $arrEntry['DateStarted'] = "now_usec()";
                        
                        $activesession = $_AccountSessions->checkSession($row['AID']);
                        foreach ($activesession as $value) {
                            foreach ($value as $value2) {
                                $activesession = $value2['Count'];
                            }
                        }
                        
                        if($activesession > 0)
                        {
                            $_AccountSessions->updateSession($arrEntry['SessionID'], $row['AID'], 
                                    $arrEntry['RemoteIP'], $arrEntry['DateStarted']);
                        }
                        else{
                            $_AccountSessions->Insert($arrEntry);
                        }
                
                        $_SESSION['sessionID'] = $arrEntry['SessionID'];
                        $_SESSION['aID'] = $row['AID'];
                        
                        $_SESSION['menus'] = $access;
                        $_SESSION['userinfo']['SessionID'] = $arrEntry['SessionID'];

                        $updateresults = $accounts->updateAttemptcounts(0, $username);
                        
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
                        $_Log->logEvent(AuditFunctions::LOGIN, $username .':No access rights', array('ID'=>$row['AID'], 'SessionID'=>''));
                    }
                }
                else
                {
                    $loginattempts+=1;
                    $updateresults = $accounts->updateAttemptcounts($loginattempts, $username);
                    if($loginattempts >=3 ){
                        App::SetErrorMessage('Access Denied. Please contact system administrator to have your account unlocked.');
                        $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Account Locked', array('ID'=>"", 'SessionID'=>""));
                    } else {
                        App::SetErrorMessage('Invalid Account');
                        $_Log->logEvent(AuditFunctions::LOGIN, $username . ':Invalid account', array('ID'=>"", 'SessionID'=>""));
                    }
                }
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
<script type="text/javascript">
             jQuery(document).ready(function(){
                  //this will disable all cut, copy, paste on all textbox
                  jQuery(':text').live("cut copy paste",function(e) {
                      e.preventDefault();
                  });

                  jQuery(':password').live("cut copy paste",function(e) {
                      e.preventDefault();
                  });
                  
                  jQuery('#browser').val(jQuery.browser.msie);
                  jQuery('#version').val(jQuery.browser.version);
                  jQuery('#chrome').val(jQuery.browser.safari);
                  jQuery('#txtusername').focus();
                    
                  //this will disable the right click
                   var isNS = (navigator.appName == "Netscape") ? 1 : 0;
                   if(navigator.appName == "Netscape") document.captureEvents(Event.MOUSEDOWN||Event.MOUSEUP);
                   function mischandler(){
                        return false;
                   }
                   function mousehandler(e){
                         var myevent = (isNS) ? e : event;
                         var eventbutton = (isNS) ? myevent.which : myevent.button;
                         if((eventbutton==2)||(eventbutton==3)) return false;
                   }
                   document.oncontextmenu = mischandler;
                   document.onmousedown = mousehandler;
                   document.onmouseup = mousehandler;
             });
             
             function preventBackandForward()
             {
                 window.history.forward();
             }
             preventBackandForward();
             window.inhibited_load=preventBackandForward;
             window.onpageshow=function(evt){if(evt.persisted)preventBackandForward();};
             window.inhibited_unload=function(){void(0);};
        </script>     
<div id="login">
    <div class="login-title"><?php echo $pagetitle; ?></div>
    <label for="Username">Username</label>
    <div class="inputfield"><?php echo $txtUsername; ?></div>
    <label for="Password">Password</label>
    <div class="inputfield"><?php echo $txtPassword; ?></div>
    <div class="inputfield"><?php echo $btnLogin; ?></div>
</div>
<!--  For Javascript Alert Dialog (Errors)  -->        
<?php
    if(isset($_GET['mess']))
       {
        $msg = $_GET['mess'];
?>
<script type="text/javascript" language="javascript">
    $(document).ready(function(){
        <?php echo "alert('".$msg."');"; ?>
    });
</script>
<?php
      }
?>
<?php include('footer.php'); ?>
