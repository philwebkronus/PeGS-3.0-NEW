<?php

/*
 * @author : owliber
 * @date : 2013-04-19
 */


$MID = $_SESSION["MemberInfo"]["Member"]["MID"];

/**
 * Load Models
 */
$_MemberInfo = new MemberInfo();
$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_Sites = new Sites();

$fproc = new FormsProcessor();
$evt = new EventListener($fproc);
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();
$autocomplete = false;
$isOpen = 'false'; //Hide dialog box

$memberinfo = $_MemberInfo->getMemberInfo( $MID );
$row = $memberinfo[0];

$points = $_MemberCards->getMemberCardInfo( $MID );

(!empty($row['NickName']) ? $nick = $row['NickName'] : $nick = $row['FirstName']);

/**
 * Member Info
 */
$memberName = $row['FirstName'] . ' ' . $row['MiddleName'] . ' ' . $row['LastName'];
$cardNumber = $points[0]['CardNumber'];
$mobileNumber = $row['MobileNumber'];
$email = $row['Email'];

/**
 * Loyalty Points
 */
$currentPoints = $points[0]['CurrentPoints'];
$lifetimePoints = $points[0]['LifetimePoints'];
$bonusPoints = $points[0]['BonusPoints'];
$redeemedPoints = $points[0]['RedeemedPoints'];


/**
 * Member Profile
 */

$txtFirstName = new TextBox("txtFirstName", "txtFirstName", "FirstName");
$txtFirstName->ShowCaption = false;
$txtFirstName->Length = 30;
$txtFirstName->Size = 15;
$txtFirstName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$txtFirstName->Text = $row['FirstName'];
$fproc->AddControl($txtFirstName);

$txtMiddleName = new TextBox("txtMiddleName", "txtMiddleName", "MiddleName");
$txtMiddleName->ShowCaption = false;
$txtMiddleName->Length = 30;
$txtMiddleName->Size = 15;
$txtMiddleName->CssClass = "validate[custom[onlyLetterSp], minSize[2]]";;
$txtMiddleName->Text = $row['MiddleName'];
$fproc->AddControl($txtMiddleName);

$txtLastName = new TextBox("txtLastName", "txtLastName", "LastName");
$txtLastName->ShowCaption = false;
$txtLastName->Length = 30;
$txtLastName->Size = 15;
$txtLastName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";;
$txtLastName->Text = $row['LastName'];
$fproc->AddControl($txtLastName);

$txtNickName = new TextBox("txtNickName", "txtNickName", "NickName");
$txtNickName->ShowCaption = false;
$txtNickName->Length = 30;
$txtNickName->Size = 15;
$txtNickName->Text = $row['NickName'];
$txtNickName->CssClass = "validate[custom[onlyLetterSp]]";
$fproc->AddControl($txtNickName);

$txtMobileNumber = new TextBox("txtMobileNumber", "txtMobileNumber", "MobileNumber");
$txtMobileNumber->ShowCaption = false;
$txtMobileNumber->Length = 30;
$txtMobileNumber->Size = 15;
$txtMobileNumber->CssClass = "validate[required, custom[onlyNumber], minSize[9]]";
$txtMobileNumber->Text = $row['MobileNumber'];
$fproc->AddControl($txtMobileNumber);

$txtAlternateMobileNumber = new TextBox("txtAlternateMobileNumber", "txtAlternateMobileNumber", "AlternateMobileNumber");
$txtAlternateMobileNumber->ShowCaption = false;
$txtAlternateMobileNumber->Length = 30;
$txtAlternateMobileNumber->Size = 15;
$txtAlternateMobileNumber->Text = $row['AlternateMobileNumber'];
$txtAlternateMobileNumber->AutoComplete = false;
$txtAlternateMobileNumber->CssClass = "validate[custom[onlyNumber], minSize[9]]";
$fproc->AddControl($txtAlternateMobileNumber);

$txtPassword = new TextBox("txtPassword", "txtPassword", "Password");
$txtPassword->ShowCaption = false;
$txtPassword->Length = 30;
$txtPassword->Size = 15;
$txtPassword->Password = true;
$txtPassword->CssClass = "validate[custom[onlyLetterNumber], minSize[5]]";
$txtPassword->AutoComplete = false;
$fproc->AddControl($txtPassword);

$txtConfirmPassword = new TextBox("txtConfirmPassword", "txtConfirmPassword", "ConfirmPassword");
$txtConfirmPassword->ShowCaption = false;
$txtConfirmPassword->Length = 30;
$txtConfirmPassword->Size = 15;
$txtConfirmPassword->Password = true;
$txtConfirmPassword->CssClass = "validate[equals[txtPassword]]";
$fproc->AddControl($txtConfirmPassword);

$txtEmail = new TextBox("txtEmail", "txtEmail", "Email");
$txtEmail->ShowCaption = false;
$txtEmail->Length = 30;
$txtEmail->Size = 15;
$txtEmail->CssClass = "validate[required, custom[email]]";
$txtEmail->Text = $row['Email'];
$txtEmail->ReadOnly = true;
$fproc->AddControl($txtEmail);

$txtAlternateEmail = new TextBox("txtAlternateEmail", "txtAlternateEmail", "Username");
$txtAlternateEmail->ShowCaption = false;
$txtAlternateEmail->Length = 30;
$txtAlternateEmail->Size = 15;
$txtAlternateEmail->Text = $row['AlternateEmail'];
$txtAlternateEmail->CssClass = "validate[custom[email]]";
$fproc->AddControl($txtAlternateEmail);

$dsmaxdate->AddYears(-21);
$dsmindate->AddYears(-100);

$dtBirthDate = new DatePicker("dtBirthDate", "dtBirthDate", "Birth Date: ");
$dtBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtBirthDate->MinDate = $dsmindate->CurrentDate;
$dtBirthDate->SelectedDate = $row['Birthdate'];
$dtBirthDate->ShowCaption = false;
$dtBirthDate->YearsToDisplay = "-100";
$dtBirthDate->CssClass = "validate[required]";
$dtBirthDate->isRenderJQueryScript = true;
$fproc->AddControl($dtBirthDate);

$txtAddress1 = new TextBox("txtAddress1", "txtAddress1", "Address1");
$txtAddress1->ShowCaption = false;
$txtAddress1->Length = 30;
$txtAddress1->Size = 15;
$txtAddress1->Text = $row['Address1'];
$fproc->AddControl($txtAddress1);

$txtAddress2 = new TextBox("txtAddress2", "txtAddress2", "Address2");
$txtAddress2->ShowCaption = false;
$txtAddress2->Length = 30;
$txtAddress2->Size = 15;
$txtAddress2->Text = $row['Address2'];
$fproc->AddControl($txtAddress2);

$txtIDPresented = new TextBox("txtIDPresented", "txtIDPresented", "IDPresented");
$txtIDPresented->ShowCaption = false;
$txtIDPresented->Length = 30;
$txtIDPresented->Size = 15;
$txtIDPresented->CssClass = "validate[required, custom[onlyLetterNumber]]";
$txtIDPresented->Text = $row['IdentificationNumber'];
$fproc->AddControl($txtIDPresented);

$_identifications = new Identifications();
$arrids = $_identifications->SelectAll();
$cboIDSelection = new ComboBox("cboIDSelection", "cboIDSelection", "cboIDSelection");
$cboIDSelection->ShowCaption = false;
$cboIDSelection->DataSource = $arrids;
$cboIDSelection->DataSourceText = "IdentificationName";
$cboIDSelection->DataSourceValue = "IdentificationID";
$cboIDSelection->DataBind();
$cboIDSelection->SetSelectedValue($row['IdentificationID']);
$fproc->AddControl($cboIDSelection);

$txtAge = new TextBox("txtAge", "txtAge", "Age");
$txtAge->ShowCaption = false;
$txtAge->Length = 30;
$txtAge->Size = 15;
$txtAge->CssClass = "validate[required]";
$txtAge->Text = number_format((abs(strtotime($row['Birthdate']) - strtotime(date('Y-m-d'))) / 60 / 60 / 24 / 365),0);
$fproc->AddControl($txtAge);

$_nationality = new Nationality();
$arrnationality = $_nationality->SelectAll();
$cboNationality = new ComboBox("cboNationality", "cboNationality", "cboNationality");
$cboNationality->ShowCaption = false;
$cboNationality->DataSource = $arrnationality;
$cboNationality->DataSourceText = "Name";
$cboNationality->DataSourceValue = "NationalityID";
$cboNationality->DataBind();
$cboNationality->SetSelectedValue($row['NationalityID']); 
$fproc->AddControl($cboNationality);

$_Occupation = new Occupation();
$arrOccupation = $_Occupation->SelectAll();
$cboOccupation = new ComboBox("cboOccupation", "cboOccupation", "cboOccupation");
$cboOccupation->ShowCaption = false;
$cboOccupation->DataSource = $arrOccupation;
$cboOccupation->DataSourceText = "Name";
$cboOccupation->DataSourceValue = "OccupationID";
$cboOccupation->DataBind();
$cboOccupation->SetSelectedValue($row['OccupationID']);
$fproc->AddControl($cboOccupation);

$rdoGroupGender = new RadioGroup("rdoGender", "rdoGender", "Gender");
$rdoGroupGender->AddRadio("1", "Male");
$rdoGroupGender->AddRadio("2", "Female");
$rdoGroupGender->ShowCaption = true;
$rdoGroupGender->Initialize();
$rdoGroupGender->SetSelectedValue($row['Gender']);
$fproc->AddControl($rdoGroupGender);

$rdoGroupSmoker = new RadioGroup("rdoGroupSmoker", "rdoGroupSmoker", "rdoGroupSmoker");
$rdoGroupSmoker->AddRadio("1", "Smoker");
$rdoGroupSmoker->AddRadio("2", "Non-Smoker");
$rdoGroupSmoker->ShowCaption = true;
$rdoGroupSmoker->Initialize();
$rdoGroupSmoker->SetSelectedValue($row['IsSmoker']);
$rdoGroupGender->Args="onclick='\"window.close()\"'";
$fproc->AddControl($rdoGroupSmoker);

//Check if has scanned file

(!empty($row['PhotoFileName'])) ? $memberfile = 'File: '.$row['PhotoFileName'] : $memberfile = "";

/*
 * End member profile
 */
        
$btnUpdate = new Button('btnUpdate', 'btnUpdate', 'Update Profile');
$btnUpdate->IsSubmit = false;
$btnUpdate->ShowCaption = true;
$btnUpdate->CssClass = "yellow-btn";
$fproc->AddControl($btnUpdate);

$btnClose = new Button('close','close','Close');
$btnClose->Args = "onclick='window.close()'";
$btnClose->ShowCaption = true;
$fproc->AddControl($btnClose);

$trans = $_CardTransactions->getLastTransaction($cardNumber);
if(count( $trans ) > 0)
{
    $site = $_Sites->getSite($trans[0]['SiteID']);
    $siteName = $site[0]['SiteName'];
    $transDate = date('M d, Y ',strtotime($trans[0]['TransactionDate']));
    
    $lastPlay = "<p>You last played in $siteName on $transDate.</p>";
}
else
{
    $lastPlay = "";
}

$fproc->ProcessForms();

$dateupdated = "now_usec()";

echo App::GetErrorMessage();

if ($fproc->IsPostBack)
{
    //Check if password was changed.
    (!empty($txtPassword->SubmittedValue)) ? $arrMembers["Password"] = md5($txtPassword->SubmittedValue) : "";
    $arrMembers["DateUpdated"] = $dateupdated;
    $arrMembers["MID"] = $MID;

    $arrMemberInfo["FirstName"] = $txtFirstName->SubmittedValue;
    $arrMemberInfo["MiddleName"] = $txtMiddleName->SubmittedValue;
    $arrMemberInfo['LastName'] = $txtLastName->SubmittedValue;                 
    $arrMemberInfo['NickName'] = $txtNickName->SubmittedValue;

    $arrMemberInfo['Address1'] = $txtAddress1->SubmittedValue;
    $arrMemberInfo['Address2'] = $txtAddress2->SubmittedValue;  
    $arrMemberInfo['MobileNumber'] = $txtMobileNumber->SubmittedValue;
    $arrMemberInfo['AlternateMobileNumber'] = $txtAlternateMobileNumber->SubmittedValue;        
    $arrMemberInfo['Email'] = $txtEmail->SubmittedValue;
    $arrMemberInfo['AlternateEmail'] = $txtAlternateEmail->SubmittedValue;                          
    $arrMemberInfo['Birthdate'] = $dtBirthDate->SubmittedValue;
    $arrMemberInfo['NationalityID'] = $cboNationality->SubmittedValue;
    $arrMemberInfo['OccupationID'] = $cboOccupation->SubmittedValue;
    $arrMemberInfo['IdentificationID'] = $cboIDSelection->SubmittedValue;
    $arrMemberInfo['IdentificationNumber'] = $txtIDPresented->SubmittedValue;

    $arrMemberInfo['Gender'] = $rdoGroupGender->SubmittedValue;     
    $arrMemberInfo['IsSmoker'] = $rdoGroupSmoker->SubmittedValue;  
  
//    if(isset($_FILES['ScannedID']['name']))
//    {
//        $allowedsize = 4194304; //4MB
//        $allowedext = array("jpg","gif","tif","png");
//        $extension = end(explode(".", $_FILES["ScannedID"]["name"]));
//        //$filename = prev(explode(".", $_FILES["ScannedID"]["name"]));
//        $upload_dir = "/home/webadmin/www/membershipsystem/memberfiles/"; // remote
//        //$upload_dir = "C:\Apache2\htdocs\philweb\membership\memberfiles\\";
//
//        $tmpFile = $_FILES["ScannedID"]["tmp_name"];
//        //$scannedFile = str_replace(" ", "_", $_FILES["ScannedID"]["name"]);
//        $IDFile = 'ID' . str_pad($MID, 5, 0, STR_PAD_LEFT) . '.' . $extension;
        
//        if(!in_array($extension, $allowedext))
//        {
//            App::SetErrorMessage("Invalid file extension.");
//        }
//        else
//        {
//            if($_FILES["ScannedID"]["size"] > $allowedsize && in_array($extension, $allowedext))
//            {
//                App::SetErrorMessage("File error.");
//            }
//
//            if($_FILES["ScannedID"]["size"] < $allowedsize)
//            {
//                if(move_uploaded_file($tmpFile, $upload_dir . $IDFile))
//                    $arrMemberInfo["PhotoFileName"] = $IDFile;
//                
//
//            }
//        //}
//        
//
//    }
    
    //Proceed with the update profile
    $_MemberInfo->updateProfile($arrMembers,$arrMemberInfo);
    
    if(!App::HasError())
        $isSuccess = true;
    else
        $isSuccess = false;
        
    /*
     * Load message dialog box
     */
    $isOpen = 'true';
         
}

?>

<script language="javascript" type="text/javascript">
    
    $(document).ready(function() {
        
        function loadprofile() {
            $("#home-latest-news").addClass('profile-box');
            $("#carousel").hide();
//            $("#home-login-box").addClass('profile-wrapper');
        }
        
        window.onload = loadprofile;
        
        function reloadProfile() {
            parent.window.location.href='index.php';
        }
        
        $("#txtPassword").blur(function(){
            txtpass = $('#txtPassword').val();

            if(txtpass != "")
            {
                $('#txtConfirmPassword').addClass('validate[required, custom[onlyLetterNumber], equals[txtPassword]]');
            }
        });
        
        
        
        $('#dtBirthDate').change(function()
        {
            dob1 = $('#dtBirthDate').val();
            dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) -1, dob1.substr(8, 2));
            var today = new Date();
            var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
            $('#txtAge').val(age);
        });
        
        $("#btnUpdate").click(function() {     
            $("#dialog:ui-dialog").dialog("destroy");
            $("#UpdateProfileDialog").dialog("open");
            
        });
        
        $("#UpdateProfileDialog").dialog({
            autoOpen: false,
            modal: true,
            width: '800',
            title : 'PROFILE UPDATE',
            closeOnEscape: true,
            
            buttons: {
                "Submit": function() {
                    $('#SubForm').submit();
                },
                "Cancel" : function() {
                    $(this).dialog("close");
                }
            },
            
            open: function (event, ui) {
                $(event.target).parent().css('position', 'fixed');
                $(event.target).parent().css('top', '5%');
                $(event.target).parent().css('left', '20%');
            }
        }).parent().appendTo($("#SubForm").validationEngine());
        
        $('#SuccessDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title : 'Update Profile',
            closeOnEscape: true,            
            buttons: {
                "Ok": function() {
                    reloadProfile();
                    $(this).dialog("close");
                }
            }
        });
        
    });                   

</script>
<div id="home-login-box">      
    <div id="home-login-wrapper">
        <!--<div id="home-page-login-form">-->
            <div class="profile">
            <p>Hi <?php echo strtoupper($nick); ?>! [<a href="logout.php">Logout</a>]</p>
            <ul style="list-style: none;">
                <li><strong><?php echo strtoupper($memberName); ?></strong></li>
                <li>Card Number: <?php echo $cardNumber; ?></li>
                <li>Mobile Number: <?php echo $mobileNumber; ?></li>
                <li>Email Address: <?php echo $email; ?></li>
            </ul>
            <ul style="list-style: none;">
                <li>Current Points: <?php echo $currentPoints; ?></li>
                <li>Bonus Points: <?php echo $bonusPoints; ?></li>
                <li>Redeemed Points: <?php echo $redeemedPoints; ?></li>
                <li>Lifetime Points: <?php echo $lifetimePoints; ?></li>
            </ul>

            <?php echo $lastPlay; ?>
            <?php echo $btnUpdate; ?>

        </form> <!-- End form declared in the header -->
            </div>
        <!--</div>-->
    </div>
<!--</div>-->
<form name="SubForm" id="SubForm" method="post" action="" enctype="multipart/form-data" >

<!-- Update Profile page holder -->
<div id="UpdateProfileDialog" name="UpdateProfileDialog">
    <br />
    <table>
    <tr>
        <td width="20%">First Name*</td>
        <td width="30%"><?php echo $txtFirstName; ?></td>
        <td width="20%">Nickname</td>
        <td width="30%"><?php echo $txtNickName; ?></td>
    </tr>
    <tr>
        <td>Middle Name</td>
        <td><?php echo $txtMiddleName; ?></td>
        <td>Mobile Number*</td>
        <td><?php echo $txtMobileNumber; ?></td>
    </tr>
    <tr>
        <td>Last Name*</td>
        <td><?php echo $txtLastName; ?></td>
        <td>Alternate Mobile Number</td>
        <td><?php echo $txtAlternateMobileNumber; ?></td>
    </tr>
    <tr>
        <td>Password</td>
        <td><?php echo $txtPassword; ?></td>
        <td>Email Address*</td>
        <td><?php echo $txtEmail; ?></td>
    </tr>
    <tr>
        <td>Confirm Password</td>
        <td><?php echo $txtConfirmPassword; ?></td>
        <td>Alternate Email</td>
        <td><?php echo $txtAlternateEmail; ?></td>
    </tr>
    <tr>
        <td>Permanent Address</td>
        <td><?php echo $txtAddress1; ?><br/>
            <?php echo $txtAddress2; ?><br/></td>
        <td>Gender</td>
        <td><?php echo $rdoGroupGender->Radios[0]; ?><?php echo $rdoGroupGender->Radios[1]; ?></td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td>Birthdate*</td>
        <td><?php echo $dtBirthDate; ?></td>
    </tr>      
    
    <tr>
<!--        <td colspan="2">Attached scanned ID/<br />Supporting Documents <br />
            <input type="file" name="ScannedID" id="ScannedID" value="" /><br />
            <em><php echo $memberfile; ?></em>
        </td>-->
        <td>ID Presented*</td>
        <td><?php echo $txtIDPresented; ?><br/>
            <?php echo $cboIDSelection; ?></td>
        <td>Age</td>
        <td><?php echo $txtAge; ?></td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
        <td>Nationality</td>
        <td><?php echo $cboNationality; ?></td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
        <td>Occupation</td>
        <td><?php echo $cboOccupation; ?></td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
        <td><?php echo $rdoGroupSmoker->Radios[0]; ?></td>
        <td><?php echo $rdoGroupSmoker->Radios[1]; ?></td>
    </tr>
    
</table>
</div>

<div id="SuccessDialog" name="SuccessDialog">
    <?php if($isOpen == 'true') 
    {?>
        <?php if($isSuccess)
        {?>
            <p>
                You have successfully updated your profile.
            </p>
        <?php 
        } 
        else 
        { 
        ?>
            <p>
                Update profile failed.
            </p>
        <?php
        }?>
    <?php
    }?>
</div>