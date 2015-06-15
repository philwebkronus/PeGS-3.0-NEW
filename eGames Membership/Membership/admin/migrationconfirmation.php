<?php
/*
 * Description:
 * @Author: 
 * Date Created: 
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Confirmation of Migrated Cards";
$currentpage = "Confirmation of Migrated Cards";

App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Kronus", "TransactionSummary");

App::LoadCore('Validation.class.php');

App::LoadControl("DatePicker");
App::LoadControl("ComboBox");
App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("Hidden");

$fproc = new FormsProcessor();


$arrids = array(array('SearchID'=>'1', 'SearchName'=>'Temporary Card'),array('SearchID'=>'2', 'SearchName'=>'Old Card'), array('SearchID'=>'3', 'SearchName'=>'Membership Card'));
$cboIDSelection = new ComboBox("cboIDSelection", "cboIDSelection", "cboIDSelection");
$opt1[] = new ListItem("Select One", "-1", true);
$cboIDSelection->Items = $opt1;
$cboIDSelection->ShowCaption = false;
$cboIDSelection->DataSource = $arrids;
$cboIDSelection->DataSourceText = "SearchName";
$cboIDSelection->DataSourceValue = "SearchID";
$cboIDSelection->DataBind();
$fproc->AddControl($cboIDSelection);


$cboIDSelection2 = new ComboBox("cboIDSelection2", "cboIDSelection2", "cboIDSelection2");
$cboIDSelection2->Items = $opt1;
$cboIDSelection2->ShowCaption = false;
$cboIDSelection2->DataSource = $arrids;
$cboIDSelection2->DataSourceText = "SearchName";
$cboIDSelection2->DataSourceValue = "SearchID";
$cboIDSelection2->DataBind();
$fproc->AddControl($cboIDSelection2);

$txtFullName = new TextBox("txtFullName", "txtFullName", "FullName: ");
$txtFullName->ShowCaption = false;
$txtFullName->CssClass = 'validate[required]]';
$txtFullName->Style = 'color: #666';
$txtFullName->Size = 20;
$txtFullName->AutoComplete = false;
$txtFullName->Args = 'placeholder="Enter First or Last Name" onkeypress="javascript: return AlphaNumericOnly(event)"';
$fproc->AddControl($txtFullName);

$txtEmail = new TextBox("txtEmail", "txtEmail", "Email: ");
$txtEmail->ShowCaption = false;
$txtEmail->CssClass = 'validate[required]]';
$txtEmail->Style = 'color: #666';
$txtEmail->Size = 20;
$txtEmail->AutoComplete = false;
$txtEmail->Args = 'placeholder="Enter Email" onkeypress="javascript: return alphanumericemail(event)"';
$fproc->AddControl($txtEmail);

$txtCardNumber = new TextBox("txtCardNumber", "txtCardNumber", "Card Number: ");
$txtCardNumber->ShowCaption = false;
$txtCardNumber->CssClass = 'validate[required]]';
$txtCardNumber->Style = 'color: #666';
$txtCardNumber->Size = 20;
$txtCardNumber->AutoComplete = false;
$txtCardNumber->Args = 'placeholder="Enter Card Number" onkeypress="javascript: return AlphaNumericOnly(event)"';
$fproc->AddControl($txtCardNumber);

$txtNewCardNumber = new TextBox("txtNewCardNumber", "txtNewCardNumber", "New Card Number: ");
$txtNewCardNumber->ShowCaption = false;
$txtNewCardNumber->CssClass = 'validate[required]]';
$txtNewCardNumber->Style = 'color: #666';
$txtNewCardNumber->Size = 20;
$txtNewCardNumber->AutoComplete = false;
$txtNewCardNumber->Args = 'placeholder="Enter Card Number" onkeypress="javascript: return AlphaNumericOnly(event)"';
$fproc->AddControl($txtNewCardNumber);

$hdnMID = new Hidden('hdnMID', 'hdnMID');
$fproc->AddControl($hdnMID);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Search");
$btnSubmit->ShowCaption = true;
$btnSubmit->Enabled = true;
$btnSubmit->Style = "padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px;";
$fproc->AddControl($btnSubmit);

$btnSubmit2 = new Button("btnSubmit2", "btnSubmit2", "Search");
$btnSubmit2->ShowCaption = true;
$btnSubmit2->Enabled = true;
$btnSubmit2->Style = "padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px;";
$fproc->AddControl($btnSubmit2);

$btnClear1 = new Button("btnClear1", "btnClear1", "Clear");
$btnClear1->ShowCaption = true;
$btnClear1->Enabled = true;
$btnClear1->Style = "padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px; width: 70px";
$fproc->AddControl($btnClear1);

$btnClear2 = new Button("btnClear2", "btnClear2", "Clear");
$btnClear2->ShowCaption = true;
$btnClear2->Enabled = true;
$btnClear2->Style = "padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px; width: 70px";
$fproc->AddControl($btnClear2);

$btnAdvanceSearch = new Button("btnAdvanceSearch", "btnAdvanceSearch", "Advanced Search");
$btnAdvanceSearch->ShowCaption = true;
$btnAdvanceSearch->Enabled = true;
$btnAdvanceSearch->Style = "padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px; width: 140px";
$fproc->AddControl($btnAdvanceSearch);


$fproc->ProcessForms();

//Clear the session for Redemtion
if(isset($_SESSION['CardRed'])){
    unset($_SESSION['CardRed']);
}
?>

<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>-->
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript'>

    $(document).ready(function() {
        
        $('#btnClear1').live('click', function() {
            document.getElementById("results").style.display = "none";
            document.getElementById("results2").style.display = "none";
            document.getElementById("results3").style.display = "none";
            document.getElementById("results4").style.display = "none";
            document.getElementById("results5").style.display = "none";
            document.getElementById("results6").style.display = "none";
                    $("#txtCardNumber").val("");
        });
        
        
        $('#btnClear2').live('click', function() {
            document.getElementById("results").style.display = "none";
            document.getElementById("results2").style.display = "none";
            document.getElementById("results3").style.display = "none";
            document.getElementById("results4").style.display = "none";
            document.getElementById("results5").style.display = "none";
            document.getElementById("results6").style.display = "none";
                    $("#txtNewCardNumber").val("");
                    $("#txtFullName").val("");
                    $("#txtEmail").val("");
                    
        });


        $('#btnAdvanceSearch').live('click', function() {
            var advsearchdiv = document.getElementById("cardsearch2").style.display;
            
            if(advsearchdiv == 'block'){
                document.getElementById("cardsearch").style.display = 'none';
                document.getElementById("cardsearch2").style.display = 'none';
                document.getElementById("playerprofile").style.display = "none";
                document.getElementById("playerprofile2").style.display = "none";
                document.getElementById("advancesearch").style.display = 'block';
                
                document.getElementById('cboIDSelection2').selectedIndex = 0;
                
            }
            else{
                document.getElementById("cardsearch").style.display = 'none';
                document.getElementById("cardsearch2").style.display = 'block';
                document.getElementById("playerprofile").style.display = "none";
                document.getElementById("playerprofile2").style.display = "none";
                document.getElementById("advancesearch").style.display = 'none';
            }
            
            document.getElementById("results").style.display = "none";
            document.getElementById("results2").style.display = "none";
            document.getElementById("results3").style.display = "none";
            document.getElementById("results4").style.display = "none";
            document.getElementById("results5").style.display = "none";
            document.getElementById("results6").style.display = "none";
        });
        
        
        
        
        $('#cboIDSelection2').live('change', function(){
            var id = jQuery("#cboIDSelection2 option:selected").val();
            $("#txtCardNumber").val("");
            document.getElementById("results").style.display = "none";
            document.getElementById("results2").style.display = "none";
            document.getElementById("results3").style.display = "none";
            document.getElementById("results4").style.display = "none";
            document.getElementById("results5").style.display = "none";
            document.getElementById("results6").style.display = "none";
            if(id > 0){
               document.getElementById("cardsearch").style.display = 'block';
            }
            else{
                document.getElementById("cardsearch").style.display = 'none';
            }
        });
        
        
        $('#btnSubmit').live('click', function() {
            
            var cardtype = jQuery("#cboIDSelection2").val();
            var cardnumber = jQuery("#txtCardNumber").val();
            hideAllDiv();
            if(cardnumber == ''){
                alert('Please Enter Card Number');
            }
            else{
                if(cardtype == 1){
                    getProfileData();
                }
                else if(cardtype == 2){
                    getProfileData2();
                }
                else if(cardtype == 3){
                    getProfileData3();
                }
            }
            
            
            
        });
        
        $('#btnSubmit2').live('click', function() {
            hideAllDiv();
          var cardnumber = jQuery("#txtEmail").val();
            if(cardnumber == ''){
                alert('Please Enter a Valid Email Address');
            }   
            else{
                getSearchData();
            }
           
        });
        
        
        function getProfileData() {
            $.ajax(
                    {
                        url: "Helper/helper.migrationconfirmation.php",
                        type: 'post',
                        data: {
                            pager: function() {
                                return "SearchMigrate1";
                            },
                            Card: function() {
                                return jQuery("#txtCardNumber").val();
                            },
                            CardType: function() {
                                return jQuery("#cboIDSelection2").val();
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                            var dataprofile = $.parseJSON(data);
                            
                            if (dataprofile.CardType == 1) {
                                
                                if(dataprofile.IDdetect == '1.1'){
                                    document.getElementById("results").style.display = "block";
                                    document.getElementById("playerprofile").style.display = "block";
                                    document.getElementById("playerprofiledetails").style.display = "block";
                                    
                                    document.getElementById("results2").style.display = "none";
                                    document.getElementById("playerprofiledetails2").style.display = "none";
                                    document.getElementById("playerprofile2").style.display = "none";
                                    
                                    $("#tblmsg").html("<label>" + dataprofile.Msg + "</label>");
                                    $("#tblage").html("<label>" + dataprofile.DateTimeMigration + "</label>");
                                    $("#tblgender").html("<label>" + dataprofile.Site + "</label>");
                                    $("#tblstatus").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tbllpoints").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    
                                     $('#playerprofile').show();
                                         $('#results').show();
                                }
                                else if(dataprofile.IDdetect == '1.2'){
                                    document.getElementById("results2").style.display = "block";
                                    document.getElementById("playerprofile2").style.display = "block";
                                    document.getElementById("playerprofiledetails2").style.display = "block";
                                    
                                    document.getElementById("results").style.display = "none";
                                    document.getElementById("playerprofiledetails").style.display = "none";
                                    document.getElementById("playerprofile").style.display = "none";
                                    
                                    $("#tblmsg2").html("<label>" + dataprofile.Msg + "</label>");
                                    $("#tblage2").html("<label>" + dataprofile.DateTimeMigration + "</label>");
                                    $("#tblgender2").html("<label>" + dataprofile.Site + "</label>");
                                    $("#tblstatus2").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tbllpoints2").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints2").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints2").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints2").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    
                                    $('#playerprofile2').show();
                                    $('#results2').show();
                                }
                                else if(dataprofile.IDdetect == '1.3'){
                                    
                                     jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                                    $('#SuccessDialog').dialog({
                                        modal: true,
                                        width: '400',
                                        title: 'Confirmation of Migrated Cards',
                                        closeOnEscape: true,
                                        buttons: {
                                            "Ok": function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                    
                                }
                                    
                            }
                            else {
                            document.getElementById("results").style.display = "none";
                            document.getElementById("results2").style.display = "none";
                            document.getElementById("results3").style.display = "none";
                            document.getElementById("results4").style.display = "none";
                            document.getElementById("results5").style.display = "none";
                            document.getElementById("results6").style.display = "none";
                                
                                $('#SuccessDialog').dialog({
                                    modal: true,
                                    width: '400',
                                    title: 'Confirmation of Migrated Cards',
                                    closeOnEscape: true,
                                    buttons: {
                                        "Ok": function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                                jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                            }
                        },
                        error: function(error)
                        {
                            var dataprofile = $.parseJSON(error);
                            $("#errorMessage").html("<span>" + dataprofile.msg + "</span>");

                        }
                    });
        }
        
        
        function getProfileData2() {
            $.ajax(
                    {
                        url: "Helper/helper.migrationconfirmation.php",
                        type: 'post',
                        data: {
                            pager: function() {
                                return "SearchMigrate1";
                            },
                            Card: function() {
                                return jQuery("#txtCardNumber").val();
                            },
                            CardType: function() {
                                return jQuery("#cboIDSelection2").val();
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                            var dataprofile = $.parseJSON(data);
                            if (dataprofile.CardType == 2) {
                                
                                if(dataprofile.IDdetect == '2.1'){
                                    document.getElementById("results3").style.display = "block";
                                    document.getElementById("playerprofile3").style.display = "block";
                                    document.getElementById("playerprofiledetails3").style.display = "block";
                                    
                                    document.getElementById("results4").style.display = "none";
                                    document.getElementById("playerprofiledetails4").style.display = "none";
                                    document.getElementById("playerprofile4").style.display = "none";
                                    
                                    $("#tblmsg3").html("<label>" + dataprofile.Msg + "</label>");
                                    $("#tblage3").html("<label>" + dataprofile.DateTimeMigration + "</label>");
                                    $("#tblgender3").html("<label>" + dataprofile.Site + "</label>");
                                    $("#tblstatus3").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tbllpoints3").html("<label>" + dataprofile.LifeTimePoints + "</label>");
        
                                    $("#tblrpoints3").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                 
                                    
                                     $('#playerprofile3').show();
                                     $('#results3').show();
                                }
                                else if(dataprofile.IDdetect == '2.2'){
                                    document.getElementById("results4").style.display = "block";
                                    document.getElementById("playerprofile4").style.display = "block";
                                    document.getElementById("playerprofiledetails4").style.display = "block";
                                    
                                    document.getElementById("results3").style.display = "none";
                                    document.getElementById("playerprofiledetails3").style.display = "none";
                                    document.getElementById("playerprofile3").style.display = "none";
                                    
                                    $("#tblmsg4").html("<label>" + dataprofile.Msg + "</label>");
                                    $("#tblage4").html("<label>" + dataprofile.DateTimeMigration + "</label>");
                                    $("#tblgender4").html("<label>" + dataprofile.Site + "</label>");
                                    $("#tblstatus4").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tbllpoints4").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints4").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints4").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints4").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    
                                    $('#playerprofile4').show();
                                    $('#results4').show();
                                }
                                    
                            }
                            else {
                                document.getElementById("results").style.display = "none";
                                document.getElementById("results2").style.display = "none";
                                document.getElementById("results3").style.display = "none";
                                document.getElementById("results4").style.display = "none";
                                document.getElementById("results5").style.display = "none";
                                document.getElementById("results6").style.display = "none";

                                $('#SuccessDialog').dialog({
                                    modal: true,
                                    width: '400',
                                    title: 'Confirmation of Migrated Cards',
                                    closeOnEscape: true,
                                    buttons: {
                                        "Ok": function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                                jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                            }
                        },
                        error: function(error)
                        {
                            var dataprofile = $.parseJSON(error);
                            $("#errorMessage").html("<span>" + dataprofile.msg + "</span>");

                        }
                    });
        }
        
        
        function getProfileData3() {
            $.ajax(
                    {
                        url: "Helper/helper.migrationconfirmation.php",
                        type: 'post',
                        data: {
                            pager: function() {
                                return "SearchMigrate1";
                            },
                            Card: function() {
                                return jQuery("#txtCardNumber").val();
                            },
                            CardType: function() {
                                return jQuery("#cboIDSelection2").val();
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                            var dataprofile = $.parseJSON(data);
                            if (dataprofile.CardType == 3) {
                                
                                if(dataprofile.IDdetect == '3.1'){
                                    document.getElementById("results5").style.display = "block";
                                    document.getElementById("playerprofile5").style.display = "block";
                                    document.getElementById("playerprofiledetails5").style.display = "block";
                                    
                                    document.getElementById("results6").style.display = "none";
                                    document.getElementById("playerprofiledetails6").style.display = "none";
                                    document.getElementById("playerprofile6").style.display = "none";
                                    
                                    $("#tblmsg5").html("<label>" + dataprofile.Msg + "</label>");
                                    $("#tblage5").html("<label>" + dataprofile.DateTimeMigration + "</label>");
                                    $("#tblgender5").html("<label>" + dataprofile.Site + "</label>");
                                    $("#tblstatus5").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tbllpoints5").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints5").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints5").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                     $("#tblbpoints5").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    
                                     $('#playerprofile5').show();
                                     $('#results5').show();
                                }
                                else if(dataprofile.IDdetect == '3.2'){
                                    document.getElementById("results6").style.display = "block";
                                    document.getElementById("playerprofile6").style.display = "block";
                                    document.getElementById("playerprofiledetails6").style.display = "block";
                                    
                                    document.getElementById("results5").style.display = "none";
                                    document.getElementById("playerprofiledetails5").style.display = "none";
                                    document.getElementById("playerprofile5").style.display = "none";
                                    
                                    if(dataprofile.Migrated == '1'){
                                        document.getElementById("playerprofiledetails7").style.display = "block";
                                        
                                        $("#tblmsg7").html("<label>" + dataprofile.MigratedInfo + "</label>");
                                        $("#tblage7").html("<label>" + dataprofile.MigratedDate + "</label>");
                                        $("#tblgender7").html("<label>" + dataprofile.MigratedSite + "</label>");
                                        
                                        $("#tbllpoints7").html("<label>" + dataprofile.MigratedLifeTimePoints + "</label>");
                                        $("#tblcpoints7").html("<label>" + dataprofile.MigratedCurrentPoints + "</label>");
                                        $("#tblrpoints7").html("<label>" + dataprofile.MigratedRedeemedPoints + "</label>");
                                        $("#tblbpoints7").html("<label>" + dataprofile.MigratedBonusPoints + "</label>");
                                        
                                        $('#playerprofiledetails7').show();
                                    }
                                    if(dataprofile.RedCard == '1'){
                                        document.getElementById("playerprofiledetails8").style.display = "block";
                                        
                                        $("#tblmsg8").html("<label>" + dataprofile.RedCardInfo + "</label>");
                                        $("#tblage8").html("<label>" + dataprofile.RedCardDate + "</label>");
                                        $("#tblgender8").html("<label>" + dataprofile.RedCardSite + "</label>");
                                        
                                        $("#tbllpoints8").html("<label>" + dataprofile.RedCardLifeTimePoints + "</label>");
                                        $("#tblcpoints8").html("<label>" + dataprofile.RedCardCurrentPoints + "</label>");
                                        $("#tblrpoints8").html("<label>" + dataprofile.RedCardRedeemedPoints + "</label>");
                                        $("#tblbpoints8").html("<label>" + dataprofile.RedCardBonusPoints + "</label>");
                                        
                                        $('#playerprofiledetails8').show();
                                    }
                                    
                                    $("#tblmsg6").html("<label>" + dataprofile.Msg + "</label>");
                                    $("#tblage6").html("<label>" + dataprofile.DateTimeMigration + "</label>");
                                    $("#tblgender6").html("<label>" + dataprofile.Site + "</label>");
                                    $("#tblstatus6").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tbllpoints6").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints6").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints6").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints6").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    
                                    $('#playerprofile6').show();
                                    $('#results6').show();
                                }
                                else if(dataprofile.IDdetect == '3.3'){
                                    if(dataprofile.Migrated == '1'){
                                        document.getElementById("playerprofiledetails7").style.display = "block";
                                        
                                        $("#tblmsg7").html("<label>" + dataprofile.MigratedInfo + "</label>");
                                        $("#tblage7").html("<label>" + dataprofile.MigratedDate + "</label>");
                                        $("#tblgender7").html("<label>" + dataprofile.MigratedSite + "</label>");
                                        
                                        $("#tbllpoints7").html("<label>" + dataprofile.MigratedLifeTimePoints + "</label>");
                                        $("#tblcpoints7").html("<label>" + dataprofile.MigratedCurrentPoints + "</label>");
                                        $("#tblrpoints7").html("<label>" + dataprofile.MigratedRedeemedPoints + "</label>");
                                        $("#tblbpoints7").html("<label>" + dataprofile.MigratedBonusPoints + "</label>");
                                        
                                        $('#playerprofiledetails7').show();
                                    }
                                    if(dataprofile.RedCard == '1'){
                                        document.getElementById("playerprofiledetails8").style.display = "block";
                                        
                                        $("#tblmsg8").html("<label>" + dataprofile.RedCardInfo + "</label>");
                                        $("#tblage8").html("<label>" + dataprofile.RedCardDate + "</label>");
                                        $("#tblgender8").html("<label>" + dataprofile.RedCardSite + "</label>");
                                        
                                        $("#tbllpoints8").html("<label>" + dataprofile.RedCardLifeTimePoints + "</label>");
                                        $("#tblcpoints8").html("<label>" + dataprofile.RedCardCurrentPoints + "</label>");
                                        $("#tblrpoints8").html("<label>" + dataprofile.RedCardRedeemedPoints + "</label>");
                                        $("#tblbpoints8").html("<label>" + dataprofile.RedCardBonusPoints + "</label>");
                                        
                                        $('#playerprofiledetails8').show();
                                    }
                                    document.getElementById("results6").style.display = "block";
                                    document.getElementById("playerprofile6").style.display = "block";
                                    document.getElementById("playerprofiledetails6").style.display = "block";
                                    
                                    document.getElementById("results5").style.display = "none";
                                    document.getElementById("playerprofiledetails5").style.display = "none";
                                    document.getElementById("playerprofile5").style.display = "none";
                                    
                                    $("#tblmsg6").html("<label>" + dataprofile.Msg + "</label>");
                                    $("#tblage6").html("<label>" + dataprofile.DateTimeMigration + "</label>");
                                    $("#tblgender6").html("<label>" + dataprofile.Site + "</label>");
                                    $("#tblstatus6").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tbllpoints6").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints6").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints6").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints6").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    
                                    $('#playerprofile6').show();
                                    $('#results6').show();
                                }
                                    
                            }
                            else {
                                document.getElementById("results").style.display = "none";
                                document.getElementById("results2").style.display = "none";
                                document.getElementById("results3").style.display = "none";
                                document.getElementById("results4").style.display = "none";
                                document.getElementById("results5").style.display = "none";
                                document.getElementById("results6").style.display = "none";
                                
                                $('#SuccessDialog').dialog({
                                    modal: true,
                                    width: '400',
                                    title: 'Confirmation of Migrated Cards',
                                    closeOnEscape: true,
                                    buttons: {
                                        "Ok": function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                                jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                            }
                        },
                        error: function(error)
                        {
                            var dataprofile = $.parseJSON(error);
                            $("#errorMessage").html("<span>" + dataprofile.msg + "</span>");

                        }
                    });
        }


                    
        function getSearchData() {
            $.ajax(
                    {
                        url: "Helper/helper.migrationconfirmation.php",
                        type: 'post',
                        data: {
                            pager: function() {
                                return "SearchMigrate2";
                            },
                            Email: function() {
                                return jQuery("#txtEmail").val();
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                            var dataprofile = $.parseJSON(data);
                            if (dataprofile.CardType == 3) {
                                
                                if(dataprofile.IDdetect == '3.1'){
                                    document.getElementById("results5").style.display = "block";
                                    document.getElementById("playerprofile5").style.display = "block";
                                    document.getElementById("playerprofiledetails5").style.display = "block";
                                    
                                    document.getElementById("results6").style.display = "none";
                                    document.getElementById("playerprofiledetails6").style.display = "none";
                                    document.getElementById("playerprofile6").style.display = "none";
                                    
                                    $("#tblmsg5").html("<label>" + dataprofile.Msg + "</label>");
                                    $("#tblage5").html("<label>" + dataprofile.DateTimeMigration + "</label>");
                                    $("#tblgender5").html("<label>" + dataprofile.Site + "</label>");
                                    $("#tblstatus5").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tbllpoints5").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints5").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints5").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                     $("#tblbpoints5").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    
                                     $('#playerprofile5').show();
                                     $('#results5').show();
                                }
                                else if(dataprofile.IDdetect == '3.2'){
                                    document.getElementById("results6").style.display = "block";
                                    document.getElementById("playerprofile6").style.display = "block";
                                    document.getElementById("playerprofiledetails6").style.display = "block";
                                    
                                    document.getElementById("results5").style.display = "none";
                                    document.getElementById("playerprofiledetails5").style.display = "none";
                                    document.getElementById("playerprofiledetails7").style.display = "none";
                                    document.getElementById("playerprofiledetails8").style.display = "none";
                                    document.getElementById("playerprofile5").style.display = "none";
                                    
                                    $("#tblmsg6").html("<label>" + dataprofile.Msg + "</label>");
                                    $("#tblage6").html("<label>" + dataprofile.DateTimeMigration + "</label>");
                                    $("#tblgender6").html("<label>" + dataprofile.Site + "</label>");
                                    $("#tblstatus6").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tbllpoints6").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints6").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints6").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints6").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    
                                    $('#playerprofile6').show();
                                    $('#results6').show();
                                }
                                else if(dataprofile.IDdetect == '3.3'){
                                    if(dataprofile.Migrated == '1'){
                                        document.getElementById("playerprofiledetails7").style.display = "block";
                                        
                                        $("#tblmsg7").html("<label>" + dataprofile.MigratedInfo + "</label>");
                                        $("#tblage7").html("<label>" + dataprofile.MigratedDate + "</label>");
                                        $("#tblgender7").html("<label>" + dataprofile.MigratedSite + "</label>");
                                        
                                        $("#tbllpoints7").html("<label>" + dataprofile.MigratedLifeTimePoints + "</label>");
                                        $("#tblcpoints7").html("<label>" + dataprofile.MigratedCurrentPoints + "</label>");
                                        $("#tblrpoints7").html("<label>" + dataprofile.MigratedRedeemedPoints + "</label>");
                                        $("#tblbpoints7").html("<label>" + dataprofile.MigratedBonusPoints + "</label>");
                                        
                                        $('#playerprofiledetails7').show();
                                    }
                                    if(dataprofile.RedCard == '1'){
                                        document.getElementById("playerprofiledetails8").style.display = "block";
                                        
                                        $("#tblmsg8").html("<label>" + dataprofile.RedCardInfo + "</label>");
                                        $("#tblage8").html("<label>" + dataprofile.RedCardDate + "</label>");
                                        $("#tblgender8").html("<label>" + dataprofile.RedCardSite + "</label>");
                                        
                                        $("#tbllpoints8").html("<label>" + dataprofile.RedCardLifeTimePoints + "</label>");
                                        $("#tblcpoints8").html("<label>" + dataprofile.RedCardCurrentPoints + "</label>");
                                        $("#tblrpoints8").html("<label>" + dataprofile.RedCardRedeemedPoints + "</label>");
                                        $("#tblbpoints8").html("<label>" + dataprofile.RedCardBonusPoints + "</label>");
                                        
                                        $('#playerprofiledetails8').show();
                                    }
                                    document.getElementById("results6").style.display = "block";
                                    document.getElementById("playerprofile6").style.display = "block";
                                    document.getElementById("playerprofiledetails6").style.display = "block";
                                    
                                    document.getElementById("results5").style.display = "none";
                                    document.getElementById("playerprofiledetails5").style.display = "none";
                                    document.getElementById("playerprofile5").style.display = "none";
                                    
                                    $("#tblmsg6").html("<label>" + dataprofile.Msg + "</label>");
                                    $("#tblage6").html("<label>" + dataprofile.DateTimeMigration + "</label>");
                                    $("#tblgender6").html("<label>" + dataprofile.Site + "</label>");
                                    $("#tblstatus6").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tbllpoints6").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints6").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints6").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints6").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    
                                    $('#playerprofile6').show();
                                    $('#results6').show();
                                }
                                    
                            }
                            else {
                                document.getElementById("results").style.display = "none";
                                document.getElementById("results2").style.display = "none";
                                document.getElementById("results3").style.display = "none";
                                document.getElementById("results4").style.display = "none";
                                document.getElementById("results5").style.display = "none";
                                document.getElementById("results6").style.display = "none";
                                
                                $('#SuccessDialog').dialog({
                                    modal: true,
                                    width: '400',
                                    title: 'Confirmation of Migrated Cards',
                                    closeOnEscape: true,
                                    buttons: {
                                        "Ok": function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                                jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                            }
                        },
                        error: function(error)
                        {
                            var dataprofile = $.parseJSON(error);
                            $("#errorMessage").html("<span>" + dataprofile.msg + "</span>");

                        }
                    });
        }
        
        function hideAllDiv(){
            document.getElementById("results").style.display = "none";
            document.getElementById("playerprofiledetails").style.display = "none";
            document.getElementById("playerprofile").style.display = "none";
            
            document.getElementById("results2").style.display = "none";
            document.getElementById("playerprofiledetails2").style.display = "none";
            document.getElementById("playerprofile2").style.display = "none";
            
            document.getElementById("results3").style.display = "none";
            document.getElementById("playerprofiledetails3").style.display = "none";
            document.getElementById("playerprofile3").style.display = "none";
            
            document.getElementById("results4").style.display = "none";
            document.getElementById("playerprofiledetails4").style.display = "none";
            document.getElementById("playerprofile4").style.display = "none";
            
            document.getElementById("results5").style.display = "none";
            document.getElementById("playerprofiledetails5").style.display = "none";
            document.getElementById("playerprofile5").style.display = "none";
            
            document.getElementById("results6").style.display = "none";
            document.getElementById("playerprofiledetails6").style.display = "none";
            document.getElementById("playerprofile6").style.display = "none";
            
            document.getElementById("playerprofiledetails7").style.display = "none";
            document.getElementById("playerprofiledetails8").style.display = "none";
        
        }
        
    });

</script>

<div align="center">
<div class="maincontainer">
    <form name="playerlists" id="playerlists" method="POST">
        
            <?php include('menu.php'); ?>
            <br/>
            <div  style="float: left;" class="title">&nbsp;&nbsp;&nbsp;<?php echo $pagetitle?></div>
            <div class="pad5" align="right"> </div>
            <br/><br/>
            <hr color="black">
            <br>
            <div class="pad5" align="right"></div>
            
            <div id="advsrchdiv">
                <table align="left">
                    <tr>
                        <td align="right"><?php echo $btnAdvanceSearch;?></td>
                    </tr>
                </table>
            </div>
            <div id="cardsearch2" align="center" style="display: block;">
            <table align="left">
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;Select Card Type : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td><?php echo $cboIDSelection2; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>
                    </tr>
                </table>
            </div>
            <div id="cardsearch" align="center" style="display: none;">
                <table align="left">
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;Card Number &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td><?php echo $txtCardNumber; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>
                        <td align="right"><?php echo $btnSubmit; ?></td>
                        <td align="right"><?php echo $btnClear1; ?></td>
                    </tr>
                </table>
            </div>
               
            <div id="advancesearch" align="center" style="display: none;">
                    <table align="left">
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;Email : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td><?php echo $txtEmail; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>
                        <td align="right"><?php echo $btnSubmit2; ?></td>
                        <td align="right"><?php echo $btnClear2; ?></td>
                    </tr>
                    </table>
            </div>
            
        
    </form>
    
    <div class="content2">
                <div align="center" id="playerprofile" style="display: none;">
                    <div id="results" style="display: none;">
                        <div id="playerprofiledetails" style="display: none;">
                            <div class="pad5" align="center"> </div>
                            <br/><br/>
                            <hr color="black">
                            <br>
                            <div class="pad5" align="center"></div>
                            <table id="tblplayerdetails2">
                                <tr><th colspan="3">Card Details</th></tr>
                                <tr><td style="width: 600px;">Information</td><td id="tblmsg" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Date and Time of Migration</td><td id="tblage" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Site Name</td><td id="tblgender" style="width: 500px;"></td></tr>   
                                <tr><td style="width: 600px;">Life Time Points</td><td id="tbllpoints" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Current Points</td><td id="tblcpoints" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Redeemed Points</td><td id="tblrpoints" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Bonus Points</td><td id="tblbpoints" style="width: 500px;"></td></tr>
                            </table>
                        </div>
                        <br/>
                    </div>
                    
                </div>
        
        
                <div align="center" id="playerprofile2" style="display: none;">
                    <div id="results2" style="display: none;">
                        <div id="playerprofiledetails2" style="display: none;">
                            <div class="pad5" align="center"> </div>
                            <br/><br/>
                            <hr color="black">
                            <br>
                            <div class="pad5" align="center"></div>
                            <table id="tblplayerdetails2">
                                <tr><th colspan="3">Card Details</th></tr>
                                <tr><td style="width: 600px;">Information</td><td id="tblmsg2" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Date and Time of Verification</td><td id="tblage2" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Site Name</td><td id="tblgender2" style="width: 500px;"></td></tr>   
                                <tr><td style="width: 600px;">Life Time Points</td><td id="tbllpoints2" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Current Points</td><td id="tblcpoints2" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Redeemed Points</td><td id="tblrpoints2" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Bonus Points</td><td id="tblbpoints2" style="width: 500px;"></td></tr>
                            </table>
                        </div>
                        <br/>
                    </div>
                    
                </div>
        
            <div align="center" id="playerprofile3" style="display: none;">
                    <div id="results3" style="display: none;">
                        <div id="playerprofiledetails3" style="display: none;">
                            <div class="pad5" align="center"> </div>
                            <br/><br/>
                            <hr color="black">
                            <br>
                            <div class="pad5" align="center"></div>
                            <table id="tblplayerdetails2">
                                <tr><th colspan="3">Card Details</th></tr>
                                <tr><td style="width: 600px;">Information</td><td id="tblmsg3" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Date and Time of Registration</td><td id="tblage3" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Status</td><td id="tblgender3" style="width: 500px;"></td></tr>   
                                <tr><td style="width: 600px;">Life Time Points</td><td id="tbllpoints3" style="width: 500px;"></td></tr>
                                
                                <tr><td style="width: 600px;">Redeemed Points</td><td id="tblrpoints3" style="width: 500px;"></td></tr>
                               
                            </table>
                        </div>
                        <br/>
                    </div>
                    
                </div>
        
        
                <div align="center" id="playerprofile4" style="display: none;">
                    <div id="results4" style="display: none;">
                        <div id="playerprofiledetails4" style="display: none;">
                            <div class="pad5" align="center"> </div>
                            <br/><br/>
                            <hr color="black">
                            <br>
                            <div class="pad5" align="center"></div>
                            <table id="tblplayerdetails2">
                                <tr><th colspan="3">Card Details</th></tr>
                                <tr><td style="width: 600px;">Information</td><td id="tblmsg4" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Date and Time of Migration/Transferring of Points</td><td id="tblage4" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Site Name</td><td id="tblgender4" style="width: 500px;"></td></tr>   
                                <tr><td style="width: 600px;">Life Time Points Transferred</td><td id="tbllpoints4" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Current Points Transferred</td><td id="tblcpoints4" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Redeemed Points Transferred</td><td id="tblrpoints4" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Bonus Points Transferred</td><td id="tblbpoints4" style="width: 500px;"></td></tr>
                            </table>
                        </div>
                        <br/>
                    </div>
                    
                </div>
        
                <div align="center" id="playerprofile5" style="display: none;">
                    <div id="results5" style="display: none;">
                        <div id="playerprofiledetails5" style="display: none;">
                            <div class="pad5" align="center"> </div>
                            <br/><br/>
                            <hr color="black">
                            <br>
                            <div class="pad5" align="center"></div>
                            <table id="tblplayerdetails2">
                                <tr><th colspan="3">Card Details</th></tr>
                                <tr><td style="width: 600px;">Information</td><td id="tblmsg5" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Date and Time of Migration/Transferring of Points</td><td id="tblage5" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Site Name</td><td id="tblgender5" style="width: 500px;"></td></tr>   
                                <tr><td style="width: 600px;">Life Time Points Transferred</td><td id="tbllpoints5" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Current Points Transferred</td><td id="tblcpoints5" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Redeemed Points Transferred</td><td id="tblrpoints5" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Bonus Points Transferred</td><td id="tblbpoints5" style="width: 500px;"></td></tr>
                            </table>
                        </div>
                        <br/>
                    </div>
                    
                </div>
        
                <div align="center" id="playerprofile6" style="display: none;">
                    <div id="results6" style="display: none;">
                        <div id="playerprofiledetails6" style="display: none;">
                            <div class="pad5" align="center"> </div>
                            <br/><br/>
                            <hr color="black">
                            <br>
                            <div class="pad5" align="center"></div>
                            <table id="tblplayerdetails2">
                                <tr><th colspan="3">Card Details</th></tr>
                                <tr><td style="width: 500px;">Information</td><td id="tblmsg6" style="width: 500px;"></td></tr>
                                <tr><td style="width: 500px;">Date and Time of Migration</td><td id="tblage6" style="width: 500px;"></td></tr>
                                <tr><td style="width: 500px;">Site Name</td><td id="tblgender6" style="width: 500px;"></td></tr>   
                                <tr><td style="width: 500px;">Life Time Points</td><td id="tbllpoints6" style="width: 500px;"></td></tr>
                                <tr><td style="width: 500px;">Current Points</td><td id="tblcpoints6" style="width: 500px;"></td></tr>
                                <tr><td style="width: 500px;">Redeemed Points</td><td id="tblrpoints6" style="width: 500px;"></td></tr>
                                <tr><td style="width: 500px;">Bonus Points</td><td id="tblbpoints6" style="width: 500px;"></td></tr>
                            </table>
                        </div>
                        <br/><br/>
                        <div id="playerprofiledetails7" style="display: none;">
                            <div class="pad5" align="center"> </div>
                            <br/>
                            <hr color="black">
                            <br>
                            <div class="pad5" align="center"></div>
                            <table id="tblplayerdetails2">
                                <tr><th colspan="3">Migrated Card Details</th></tr>
                                <tr><td style="width: 600px;">Migrated Card Information</td><td id="tblmsg7" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Date and Time of Migration</td><td id="tblage7" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Site Name</td><td id="tblgender7" style="width: 500px;"></td></tr>   
                                <tr><td style="width: 600px;">Life Time Points Transferred</td><td id="tbllpoints7" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Current Points Transferred</td><td id="tblcpoints7" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Redeemed Points Transferred</td><td id="tblrpoints7" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Bonus Points Transferred</td><td id="tblbpoints7" style="width: 500px;"></td></tr>
                            </table>
                        </div>
                        <br/><br/>
                        <div id="playerprofiledetails8" style="display: none;">
                            <div class="pad5" align="center"> </div>
                            <br/>
                            <hr color="black">
                            <br>
                            <div class="pad5" align="center"></div>
                            <table id="tblplayerdetails2">
                                <tr><th colspan="3">Red Card Details</th></tr>
                                <tr><td style="width: 600px;">Red Card Information</td><td id="tblmsg8" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Date and Time of Transferring of Points</td><td id="tblage8" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Site Name</td><td id="tblgender8" style="width: 500px;"></td></tr>   
                                <tr><td style="width: 600px;">Life Time Points Transferred</td><td id="tbllpoints8" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Current Points Transferred</td><td id="tblcpoints8" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Redeemed Points Transferred</td><td id="tblrpoints8" style="width: 500px;"></td></tr>
                                <tr><td style="width: 600px;">Bonus Points Transferred</td><td id="tblbpoints8" style="width: 500px;"></td></tr>
                            </table>
                        </div>
                        <br/><br/>
                    </div>
                    
                </div>
                <div id="SuccessDialog" name="SuccessDialog">
                </div>
            </div>
    </div>
    <?php include("footer.php"); ?>