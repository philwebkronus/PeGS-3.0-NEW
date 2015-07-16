<?php
/*
 * Description:
 * @Author: 
 * Date Created: 
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "UB Player Change Password";
$currentpage = "Administration";

App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Kronus", "TransactionSummary");
App::LoadModuleClass("Kronus", "CasinoServices");

App::LoadCore('Validation.class.php');

App::LoadControl("DatePicker");
App::LoadControl("ComboBox");
App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("Hidden");

$fproc = new FormsProcessor();
$_CasinoServices = new CasinoServices();

$casinoservice = new ComboBox("casinoservice","casinoservice","Casino Service: ");
$casinoservice->ShowCaption = true;
$casinoservices = $_CasinoServices->getUserBasedCasinoServices();
$alftnlist = new ArrayList();
$alftnlist->AddArray($casinoservices);
$casinoservice->ClearItems();
$litem = null;
$litem[] = new ListItem("Select One", "-1", true);
$casinoservice->Items = $litem;
$casinoservice->DataSource = $alftnlist;
$casinoservice->DataSourceText = "ServiceName";
$casinoservice->DataSourceValue = "ServiceID";
$casinoservice->DataBind();

$txtCardNumber = new TextBox("txtCardNumber", "txtCardNumber", "Card Number: ");
$txtCardNumber->ShowCaption = false;
$txtCardNumber->CssClass = 'validate[required]]';
$txtCardNumber->Style = 'color: #666';
$txtCardNumber->Size = 20;
$txtCardNumber->AutoComplete = false;
$txtCardNumber->Args = 'placeholder="Enter Card Number" onkeypress="javascript: return AlphaNumericOnly(event)"';
$fproc->AddControl($txtCardNumber);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Submit");
$btnSubmit->ShowCaption = true;
$btnSubmit->Enabled = true;
$btnSubmit->Style = "padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px;";
$fproc->AddControl($btnSubmit);


$fproc->ProcessForms();

//Clear the session for Redemtion
if (isset($_SESSION['CardRed'])) {
    unset($_SESSION['CardRed']);
}
?>

<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<!--<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>-->
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>
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
                $("#btnSubmit").val("Submit");
            }

        });

        $('#btnSubmit').live('click', function() {

           var cardnumber = $("#txtCardNumber").val();
           var casinoservice = $("#casinoservice").val();
           
           if(casinoservice <= 0){
               alert('Please Select a Casino Service');
           }
           else if(cardnumber == ''){
               alert('Please Input Membership/Temporary Card Number');
           }
           else{
               createPT(cardnumber, casinoservice);
           }
        });


        //Function to manual create pt account
        function createPT(cardnumber, casinoservice) {
            document.getElementById('loading').style.display='block';
            document.getElementById('fade').style.display='block';
            $.ajax(
                    {
                        url: "Helper/helper.ubplayerchangepassword",
                        type: 'post',
                        data: {
                            pager: function() {
                                return "ChangePassword";
                            },
                            Card: function() {
                                return cardnumber;
                            },
                            CasinoService: function() {
                                return casinoservice;
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                            var result = $.parseJSON(data);
                            $("#dialog-text").html(result.Msg);
                            document.getElementById('loading').style.display='none';
                            document.getElementById('fade').style.display='none';
                            if (result.Msg == 'Session Expired') {
                                window.location.href = result.RedirectToPage;
                            } else {
                                
                            
                                    $('#SuccessDialog').dialog({
                                        modal: true,
                                        width: '400',
                                        title: 'UB Player Change Password',
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
                            }
                            $("#SuccessDialog").dialog("open");
                        },
                        error: function(error)
                        {
                            var dataprofile = $.parseJSON(error);
                            $("#errorMessage").html("<span>" + dataprofile.msg + "</span>");

                        }
                    });
                    $("#txtCardNumber").val("");
                    document.getElementById("casinoservice").selectedIndex = 'Select One';
        }
        
    });

</script>
<style>
        .black_overlay
{
			display: none;
			position: fixed;
			top: 0%;
			left: 0%;
			width: 100%;
			height: 100%;
			background-color: black;
			z-index:1001;
			-moz-opacity: 0.8;
			opacity:.80;
			filter: alpha(opacity=80);
}
    
    #loading{
                position: fixed;
                z-index: 5000;
                background: url('images/Please_wait.gif') no-repeat;
                height: 300px;
                width: 300px;
                margin: -500px 400px;
                display: none;
}
</style>    

<div align="center">
    <div class="maincontainer">
        <form name="playerlists" id="playerlists" method="POST">

            <?php include('menu.php'); ?>
            <br/>
            <div  style="float: left;" class="title">&nbsp;&nbsp;&nbsp;<?php echo "$pagetitle";?></div>
            <div class="pad5" align="right"> </div>
            <br/><br/>
            <hr color="black">
            <br>
            <div class="pad5" align="right"></div>

            <div align="left">
                <table align="left">
                    <tr>
                        <td><?php echo $casinoservice; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;Card Number &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td><?php echo $txtCardNumber; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>
                        <td>Membership | Temporary</td>
                        <td align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $btnSubmit; ?></td>
                    </tr>  
                </table>
            </div>

          


        </form>

        <div class="content">
        <div id="SuccessDialog" name="SuccessDialog">
            <p>
                <center>
            <span id="dialog-text"> </span>
                </center>
            </p>
        </div>
        </div>
        <div id="fade" class="black_overlay" oncontextmenu="return false"></div>
        <div id="loading"></div>
    </div>
    <?php include("footer.php"); ?>