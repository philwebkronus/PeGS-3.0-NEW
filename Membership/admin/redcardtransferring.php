<?php
/*
 * Description:
 * @Author: 
 * Date Created: 
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Red Card Transferring";
$currentpage = "Administration";

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


$arrids = array(array('SearchID' => '1', 'SearchName' => 'Card Number'), array('SearchID' => '2', 'SearchName' => 'UserName'));
$cboIDSelection = new ComboBox("cboIDSelection", "cboIDSelection", "cboIDSelection");
$opt1[] = new ListItem("Select One", "-1", true);
$cboIDSelection->Items = $opt1;
$cboIDSelection->ShowCaption = false;
$cboIDSelection->DataSource = $arrids;
$cboIDSelection->DataSourceText = "SearchName";
$cboIDSelection->DataSourceValue = "SearchID";
$cboIDSelection->DataBind();
$fproc->AddControl($cboIDSelection);

$txtUserName = new TextBox("txtUserName", "txtUserName", "UserName: ");
$txtUserName->ShowCaption = false;
$txtUserName->CssClass = 'validate[required]]';
$txtUserName->Style = 'color: #666';
$txtUserName->Size = 20;
$txtUserName->AutoComplete = false;
$txtUserName->Args = 'placeholder="Enter Username" onkeypress="javascript: return alphanumericemail2(event)"';
$fproc->AddControl($txtUserName);

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
$txtNewCardNumber->Args = 'placeholder="Enter New Card Number" onkeypress="javascript: return AlphaNumericOnly(event)"';
$fproc->AddControl($txtNewCardNumber);

$hdnMID = new Hidden('hdnMID', 'hdnMID');
$fproc->AddControl($hdnMID);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Search");
$btnSubmit->ShowCaption = true;
$btnSubmit->Enabled = true;
$btnSubmit->Style = "padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px;";
$fproc->AddControl($btnSubmit);

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

$btnTransfer = new Button("btnTransfer", "btnTransfer", "Transfer Points");
$btnTransfer->ShowCaption = true;
$btnTransfer->Enabled = true;
$btnTransfer->Style = "padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px; width: 120px";
$fproc->AddControl($btnTransfer);

$fproc->ProcessForms();

//Clear the session for Redemtion
if (isset($_SESSION['CardRed'])) {
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
        
        function specialcharacter(elementvalue)
        {
            var iChars = "!`@#$%^&*()+=[]\\\';,./{}|\":<>?~_";
            var data = elementvalue;
            for (var i = 0; i < data.length; i++)
            {
                if (iChars.indexOf(data.charAt(i)) != -1 || iChars.indexOf(data.charAt(i)) == 32)
                {
                    return false;
                }
            }
            return true;
        }


        $('#txtCardNumber').change(function() {
            $(this).val($(this).val().replace(" ", ""));
        });
        
            $("#cboIDSelection").live("change", function() {
        if ($("#cboIDSelection").val() == -1) {
            $("#txtCardNumber").val("");
            $("#txtUsername").val("");
            $("#txtNewCardNumber").val("");
            $("#percard").hide();
            $("#perusername").hide();
            $("#newCardNumberTable").hide();
            $("#btnTransferTable").hide();
            $("#tblplayerdetails").hide();
        } else {
            $("#txtCardNumber").val("");
            $("#txtUsername").val("");
            $("#txtNewCardNumber").val("");
            $("#percard").show();
            $("#perusername").show();
            $("#newCardNumberTable").show();
            $("#btnTransferTable").show();
            $("#tblplayerdetails").show();
        }
    });

        $("#txtCardNumber").focus(function() {
            $("#txtCardNumber").bind('paste', function(event) {
                setTimeout(function(event) {
                    var data = $("#txtCardNumber").val();
                    if (!specialcharacter(data)) {
                        $("#txtCardNumber").val("");
                        $("#txtCardNumber").focus();
                    }
                }, 0);
            });
        });
        
        $("#txtNewCardNumber").focus(function() {
            $("#txtNewCardNumber").bind('paste', function(event) {
                setTimeout(function(event) {
                    var data = $("#txtNewCardNumber").val();
                    if (!specialcharacter(data)) {
                        $("#txtNewCardNumber").val("");
                        $("#txtNewCardNumber").focus();
                    }
                }, 0);
            });
        });
        
        function specialcharacter2(elementvalue)
        {
            var iChars = "!`#$%^&*()+=[]\\\';,/{}|\":<>?~";
            var data = elementvalue;
            for (var i = 0; i < data.length; i++)
            {
                if (iChars.indexOf(data.charAt(i)) != -1 || iChars.indexOf(data.charAt(i)) == 32)
                {
                    return false;
                }
            }
            return true;
        }
        $('#txtUserName').change(function() {
            $(this).val($(this).val().replace(" ", ""));
        });
        $("#txtUserName").focus(function() {
            $("#txtUserName").bind('paste', function(event) {
                setTimeout(function(event) {
                    var data = $("#txtUserName").val();
                    if (!specialcharacter2(data)) {
                        $("#txtUserName").val("");
                        $("#txtUserName").focus();
                    }
                }, 0);
            });
        });

        $('#cboIDSelection').live('change', function() {
            var id = jQuery("#cboIDSelection option:selected").val();

            if (id == 1) {
                $("#percard").css("display", "block");
                $("#perusername").css("display", "none");
                $("#txtUserName").val("");
                $("#txtNewCardNumber").val("");
                $('#playerprofile').hide();
                $('#results').hide();
                $('#pagination').hide();

            }
            else if (id == 2) {
                $("#perusername").css("display", "block");
                $("#percard").css("display", "none");
                $("#txtCardNumber").val("");
                $("#txtNewCardNumber").val("");
                $('#playerprofile').hide();
                $('#results').hide();
                $('#pagination').hide();
            } else {
                $("#txtUserName").val("");
                $("#txtCardNumber").val("");
                $("#txtNewCardNumber").val("");
            }
        });

        $('#playerprofile').show();
        $("#txtCardNumber").click(function() {
            $("#txtCardNumber").change();
            if ($("#txtCardNumber").val() == "")
            {
                $("#txtCardNumber").val("");
            }
        });
        $("#txtCardNumber").keyup(function() {
            $("#txtCardNumber").change();
        });
        $("#txtCardNumber").change(function() {
            if ($("#txtCardNumber").val() == "")
            {
                $("#btnSubmit").val("Search");
            }

        });

        $('#btnSubmit').live('click', function() {

            var card = jQuery("#txtCardNumber").val();
            var username = jQuery("#txtUserName").val();
            var divusername = document.getElementById("perusername");
            var divcardnumber = document.getElementById("percard");

            if (divusername.style.display == "none") {
                if ((card == '') || card == 'Enter Card Number') {
                    $('#playerprofile').hide();
                    $('#results').hide();
                    $('#pagination').hide();
                    alert('Please Enter a Valid Card Number');
                    return false;
                }

                if (card == -1) {
                    $('#playerprofile').hide();
                    $('#results').hide();
                    $('#pagination').hide();
                    alert("Please Select a Card Number");
                    return false;
                }
                else
                {
                    if ($("#txtCardNumber").val() == '') {
                        getProfileData2($("#txtUserName").val());
                    }
                    else {
                        getProfileData($("#txtCardNumber").val());
                    }

                }
            }
            else if (divcardnumber.style.display == "none") {
                if ((username == '' || username == 'Enter Username')) {
                    $('#playerprofile').hide();
                    $('#results').hide();
                    $('#pagination').hide();
                    alert('Please Enter a Valid Username');
                    return false;
                }

                if (username == -1) {
                    $('#playerprofile').hide();
                    $('#results').hide();
                    $('#pagination').hide();
                    alert("Please Select a Card Number");
                    return false;
                }
                else
                {
                    jQuery("#txtNewCardNumber").val("");
                    if ($("#txtCardNumber").val() == '') {
                        getProfileData2($("#txtUserName").val());
                    }
                    else {
                        getProfileData($("#txtCardNumber").val());
                    }

                }
            }
        });


        $('#btnClear1').live('click', function() {
            document.getElementById("results").style.display = "none";
            $("#txtCardNumber").val("");
        });


        $('#btnClear2').live('click', function() {
            document.getElementById("results").style.display = "none";
            $("#txtUserName").val("");
        });


        $('#btnTransfer').live('click', function() {
            var divusername = document.getElementById("perusername");
            var divcardnumber = document.getElementById("percard");
            var card = jQuery("#txtNewCardNumber").val();
            var username = jQuery("#txtUserName").val();

            if (divusername.style.display == "none") {
                if (card == '') {

                    alert('Please Enter a Valid Card Number');
                    return false;
                }

                if (card == -1) {

                    alert("Please Select a Card Number");
                    return false;
                }
                else
                {
                    processPoints($("#txtNewCardNumber").val());
                }
            }
            else if (divcardnumber.style.display == "none") {

                if (username == '') {

                    alert('Please Enter a Valid Username');
                    return false;
                }

                if (username == -1) {

                    alert('Please Enter a Valid Username');
                    return false;
                }
                else
                {
                    processPoints2($("#txtUserName").val());
                }

            }
        });



        //Function to hide Profile Details and Grid
        function notActiveStatus() {
            jQuery('#players').GridUnload();
            $('#playerprofile').hide();
            $('#results').hide();
            $('#pagination').hide();
        }

        //Function to get Profile Details
        function getProfileData(cardnumber) {
            $.ajax(
                    {
                        url: "Helper/helper.redcardtransferring.php",
                        type: 'post',
                        data: {
                            pager: function() {
                                return "ProfileData";
                            },
                            Card: function() {
                                return cardnumber;
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                            var dataprofile = $.parseJSON(data);
                            if (dataprofile.Msg == 'Session Expired') {
                                window.location.href = dataprofile.RedirectToPage;
                            } else {
                                if (dataprofile.MID != '') {
                                    document.getElementById("results").style.display = "block";
                                    $("#tblname").html("<label>" + dataprofile.Name + "</label>");
                                    $("#tblbirthdate").html("<label>" + dataprofile.Birthdate + "</label>");
                                    $("#tblage").html("<label>" + dataprofile.Age + "</label>");
                                    $("#tblgender").html("<label>" + dataprofile.Gender + "</label>");
                                    $("#tblstatus").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tblltpoints").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    $('#playerprofile').show();
                                    $('#results').show();
                                    $('#pagination').show();
                                    jQuery('#hdnMID').val(dataprofile.MID);

                                }
                                else {
                                    jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                                    $('#SuccessDialog').dialog({
                                        modal: true,
                                        width: '400',
                                        title: 'Red Card Transferring',
                                        closeOnEscape: true,
                                        draggable: false,
                                        resizable: false,
                                        open: function(event, ui) {
                                            $(this).closest('.ui-dialog').find('.ui-dialog-titlebar-close').hide();
                                        },
                                        buttons: {
                                            "Ok": function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                    if (dataprofile.MID == '') {
                                        jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                                        notActiveStatus();
                                    }
                                }
                            }
                        },
                        error: function(error)
                        {
                            var dataprofile = $.parseJSON(error);
                            $("#errorMessage").html("<span>" + dataprofile.msg + "</span>");

                        }
                    });
        }


        //Function to get Profile Details
        function getProfileData2(username) {
            $.ajax(
                    {
                        url: "Helper/helper.redcardtransferring.php",
                        type: 'post',
                        data: {
                            pager: function() {
                                return "ProfileData2";
                            },
                            UserName: function() {
                                return username;
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                            var dataprofile = $.parseJSON(data);
                            if (dataprofile.Msg == 'Session Expired') {
                                window.location.href = dataprofile.RedirectToPage;
                            } else {
                                if (dataprofile.MID != '') {
                                    document.getElementById("results").style.display = "block";
                                    $("#tblname").html("<label>" + dataprofile.Name + "</label>");
                                    $("#tblbirthdate").html("<label>" + dataprofile.Birthdate + "</label>");
                                    $("#tblage").html("<label>" + dataprofile.Age + "</label>");
                                    $("#tblgender").html("<label>" + dataprofile.Gender + "</label>");
                                    $("#tblstatus").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tblltpoints").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    $('#playerprofile').show();
                                    $('#results').show();
                                    $('#pagination').show();
                                    jQuery('#hdnMID').val(dataprofile.MID);

                                }
                                else {
                                    jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                                    $('#SuccessDialog').dialog({
                                        modal: true,
                                        width: '400',
                                        title: 'Red Card Transferring',
                                        closeOnEscape: true,
                                        draggable: false,
                                        resizable: false,
                                        open: function(event, ui) {
                                            $(this).closest('.ui-dialog').find('.ui-dialog-titlebar-close').hide();
                                        },
                                        buttons: {
                                            "Ok": function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                    if (dataprofile.MID == '') {
                                        jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                                        notActiveStatus();
                                    }
                                }
                            }
                        },
                        error: function(error)
                        {
                            var dataprofile = $.parseJSON(error);
                            $("#errorMessage").html("<span>" + dataprofile.msg + "</span>");

                        }
                    });
        }

        //Function to get Profile Details
        function processPoints(cardnumber) {
            $.ajax(
                    {
                        url: "Helper/helper.redcardtransferring.php",
                        type: 'post',
                        data: {
                            pager: function() {
                                return "ProcessPoints";
                            },
                            NewCard: function() {
                                return cardnumber;
                            },
                            OldCard: function() {
                                return jQuery("#txtCardNumber").val();
                            },
                            MID: function() {
                                return jQuery("#hdnMID").val();
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {

                            var dataprofile = $.parseJSON(data);
                            if (dataprofile.Msg == 'Session Expired') {
                                window.location.href = dataprofile.RedirectToPage;
                            } else {
                                if (dataprofile.MID != '') {
                                    document.getElementById("results").style.display = "block";
                                    $("#tblname").html("<label>" + dataprofile.Name + "</label>");
                                    $("#tblbirthdate").html("<label>" + dataprofile.Birthdate + "</label>");
                                    $("#tblage").html("<label>" + dataprofile.Age + "</label>");
                                    $("#tblgender").html("<label>" + dataprofile.Gender + "</label>");
                                    $("#tblstatus").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tblltpoints").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    $('#playerprofile').show();
                                    $('#results').show();
                                    $('#pagination').show();
                                    jQuery('#hdnMID').val(dataprofile.MID);

                                }
                                else {
                                    jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                                    $('#SuccessDialog').dialog({
                                        modal: true,
                                        width: '400',
                                        title: 'Red Card Transferring',
                                        closeOnEscape: true,
                                        draggable: false,
                                        resizable: false,
                                        open: function(event, ui) {
                                            $(this).closest('.ui-dialog').find('.ui-dialog-titlebar-close').hide();
                                        },
                                        buttons: {
                                            "Ok": function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                    if (dataprofile.MID == '') {
                                        jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                                        notActiveStatus();
                                    }
                                }
                            }
                        },
                        error: function(error)
                        {
                            var dataprofile = $.parseJSON(error);
                            $("#errorMessage").html("<span>" + dataprofile.msg + "</span>");

                        }
                    });

            document.getElementById("cboIDSelection").selectedIndex = 'Select One';
            $("#perusername").css("display", "none");
            $("#percard").css("display", "none");
            $("#txtCardNumber").val("");
            $("#txtUserName").val("");
        }


        //Function to get Profile Details
        function processPoints2(username) {
            $.ajax(
                    {
                        url: "Helper/helper.redcardtransferring.php",
                        type: 'post',
                        data: {
                            pager: function() {
                                return "ProcessPoints2";
                            },
                            UserName: function() {
                                return username;
                            },
                            NewCard: function() {
                                return jQuery("#txtNewCardNumber").val();
                            },
                            MID: function() {
                                return jQuery("#hdnMID").val();
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                            var dataprofile = $.parseJSON(data);
                            if (dataprofile.Msg == 'Session Expired') {
                                window.location.href = dataprofile.RedirectToPage;
                            } else {
                                if (dataprofile.MID != '') {
                                    document.getElementById("results").style.display = "block";
                                    $("#tblname").html("<label>" + dataprofile.Name + "</label>");
                                    $("#tblbirthdate").html("<label>" + dataprofile.Birthdate + "</label>");
                                    $("#tblage").html("<label>" + dataprofile.Age + "</label>");
                                    $("#tblgender").html("<label>" + dataprofile.Gender + "</label>");
                                    $("#tblstatus").html("<label>" + dataprofile.Status + "</label>");
                                    $("#tblltpoints").html("<label>" + dataprofile.LifeTimePoints + "</label>");
                                    $("#tblcpoints").html("<label>" + dataprofile.CurrentPoints + "</label>");
                                    $("#tblrpoints").html("<label>" + dataprofile.RedeemedPoints + "</label>");
                                    $("#tblbpoints").html("<label>" + dataprofile.BonusPoints + "</label>");
                                    $('#playerprofile').show();
                                    $('#results').show();
                                    $('#pagination').show();
                                    jQuery('#hdnMID').val(dataprofile.MID);

                                }
                                else {
                                    jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                                    $('#SuccessDialog').dialog({
                                        modal: true,
                                        width: '400',
                                        title: 'Red Card Transferring',
                                        closeOnEscape: true,
                                        draggable: false,
                                        resizable: false,
                                        open: function(event, ui) {
                                            $(this).closest('.ui-dialog').find('.ui-dialog-titlebar-close').hide();
                                        },
                                        buttons: {
                                            "Ok": function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                    if (dataprofile.MID == '') {
                                        jQuery('.ui-dialog-content').html("<p><center><label>" + dataprofile.Msg + "</label></center></p>");
                                        notActiveStatus();
                                    }
                                }
                            }
                        },
                        error: function(error)
                        {
                            var dataprofile = $.parseJSON(error);
                            $("#errorMessage").html("<span>" + dataprofile.msg + "</span>");

                        }
                    });
            document.getElementById("cboIDSelection").selectedIndex = 'Select One';
            $("#perusername").css("display", "none");
            $("#percard").css("display", "none");
            $("#txtCardNumber").val("");
            $("#txtUserName").val("");
        }

    });

</script>

<div align="center">
    <div class="maincontainer">
        <form name="playerlists" id="playerlists" method="POST">

            <?php include('menu.php'); ?>
            <br/>
            <div  style="float: left;" class="title">&nbsp;&nbsp;&nbsp;Red Card Transferring</div>
            <div class="pad5" align="right"> </div>
            <br/><br/>
            <hr color="black">
            <br>
            <div class="pad5" align="right"></div>

            <div align="left">
                <table align="left">
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;Search by : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td><?php echo $cboIDSelection; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>
                    </tr>
                </table>
            </div>

            <div id="percard" align="center" style="display: none;">
                <table align="left">
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;Card Number &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td><?php echo $txtCardNumber; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>
                        <td align="right"><?php echo $btnSubmit; ?></td>
                        <td align="right"><?php echo $btnClear1; ?></td>
                    </tr>
                </table>
            </div>

            <div id="perusername" align="center" style="display: none;">
                <table align="left">
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;Username &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td><?php echo $txtUserName; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>
                        <td align="right"><?php echo $btnSubmit; ?></td>
                        <td align="right"><?php echo $btnClear2; ?></td>
                    </tr>
                </table>
            </div>


        </form>

        <div class="content">
            <div id="playerprofile">
                <div id="results" style="display: none;">
                    <div id="playerprofiledetails" style="display: block;">
                        <div class="pad5" align="right"> </div>
                        <br/><br/>
                        <hr color="black">
                        <br>
                        <div class="pad5" align="right"></div>
                        <div style="float: left">
                            <table id="tblplayerdetails">
                                <tr><th colspan="2">Player Profile</th></tr>
                                <tr><td style="width: 200px;">Name</td><td style="width: 200px;" id="tblname"></td></tr>
                                <tr><td style="width: 200px;">Birthdate</td><td style="width: 200px;" id="tblbirthdate"></td></tr>
                                <tr><td style="width: 200px;">Age</td><td style="width: 200px;" id="tblage"></td></tr>
                                <tr><td style="width: 200px;">Gender</td><td style="width: 200px;" id="tblgender"></td></tr>
                                <tr><td style="width: 200px;">Status</td><td style="width: 200px;" id="tblstatus"></td></tr>
                                <tr><td style="width: 200px;">Life Time Points</td><td style="width: 200px;" id="tblltpoints"></td></tr>
                                <tr><td style="width: 200px;">Current Points</td><td style="width: 200px;" id="tblcpoints"></td></tr>
                                <tr><td style="width: 200px;">Redeemed Points</td><td style="width: 200px;" id="tblrpoints"></td></tr>
                                <tr><td style="width: 200px;">Bonus Points</td><td style="width: 200px;" id="tblbpoints"></td></tr>
                            </table>
                            <?php echo "$hdnMID"; ?>
                        </div>
                        <div style="float: left">
                            <table align="left" id="newCardNumberTable">
                                <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;New Card Number &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
                                <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo "$txtNewCardNumber"; ?></td></tr>
                            </table>
                            <table align="left" id="btnTransferTable">
                                <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>    
                                <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo "$btnTransfer"; ?></td></tr>
                            </table>
                        </div>
                    </div>
                    <br/>




                </div>
                <br/><br/><br/>
                <br/><br/><br/>

            </div>
            <div id="SuccessDialog" name="SuccessDialog">
            </div>
        </div>
    </div>
    <?php include("footer.php"); ?>