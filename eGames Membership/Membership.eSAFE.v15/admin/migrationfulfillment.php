<?php
/**
 * Fulfillment of Card Migration
 * Mark Kenneth Esguerra
 * August 17, 2015
 * Updated by: Ralph Sison
 * Date Updated: 09-04-2015
 */
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Card Migration Fulfillment";

App::LoadControl("Button");
App::LoadControl("TextBox");
App::LoadControl("ComboBox");

$openSuccessDialog = false;
$openErrorDialog = false;

$fproc = new FormsProcessor();

$txtCardNumber = new TextBox("txtCardNumber","txtCardNumber","Card Number: ");
$txtCardNumber->CssClass = "validate[required]";
$txtCardNumber->Length = 30;
$txtCardNumber->Size = 25;
$txtCardNumber->Args = 'onkeypress="javascript: return AlphaNumericOnlyWithSpace(event)"';
$fproc->AddControl($txtCardNumber);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Submit");
$btnSubmit->IsSubmit = true;
$btnSubmit->Enabled = true;
$btnSubmit->Style = "margin-left: 80px;position:relative";
$fproc->AddControl($btnSubmit);

$fproc->ProcessForms();

?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>
<script type="text/javascript">
    $(document).ready(function(){
       $("#imgloading").hide();
       $("#btnSubmit").live("click", function(e){
          e.preventDefault();
          $("#imgloading").show();
          
          var cardnumber = $("#txtCardNumber").val();
          var cardtype = $("#cboCardType").val();
          
          $.ajax({
             url: 'Helper/helper.migrationfulfillment.php', 
             type: 'post', 
             dataType: 'json',
             data: {
                 process: function(){ return "CheckDetails"; }, 
                 cardnumber : cardnumber, 
                 cardtype : cardtype
             },
             success: function(data){
                $("#imgloading").hide();
                if (data.TransCode === 0) {
                    $("#cardnumber").html(data.CardNumber);
                    $("#cardstatus").html(data.CardStatus);
                    $("#tempcode").html(data.TempCode);
                    $("#tempstatus").html(data.TempStatus);
                    
                    $("#hdnCardNumber").val(data.CardNumber);
                    $("#hdnTempCode").val(data.TempCode);
                    
                    $("#fulfillmentdlg").dialog('open');
                }
                else {
                    $("#dialog").dialog('option', 'title', 'Message');
                    $("#dialog").dialog('open');
                    $("#msg").html(data.Message);
                }
             }
          });
       });
       
       $("#dialog").dialog({
          autoOpen: false, 
          modal: true,
          resizable: false,
          buttons: {
                "OK": function(){
                    $(this).dialog("close");
                }
            }
        });
        
        $("#fulfillmentdlg").dialog({
          autoOpen: false, 
          modal: true,
          resizable: false,
          width: '500', 
          height: '300', 
          buttons: {
                "Proceed": function(){
                    var cardnumber = $("#hdnCardNumber").val();
                    var tempcode = $("#hdnTempCode").val();
                    
                    fullFillMigration(cardnumber, tempcode);
                }, 
                "Cancel": function() {
                    $(this).dialog("close");
                }
            }
        });
        
    });
    function fullFillMigration(cardNumber, tempCode) {
        $.ajax({
            url: 'Helper/helper.migrationfulfillment.php',
            type: 'post', 
            dataType: 'json', 
            data: {
                cardnumber : cardNumber, 
                tempcode: tempCode, 
                process: 'Fulfill'
            }, 
            success: function(data){
                $("#dialog").dialog('option', 'title', 'Message');
                $("#dialog").dialog('option', 'buttons', {'OK' : function() {window.location.reload(); }});
                $("#dialog").dialog('open');
                $("#msg").html(data.TransMsg);
            }
        });
    }
</script>    
<style>
    .statusactive {
        display:block;background:#197519;color:#fff;width:80px;padding: 5px;
        border-radius: 3px; 
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
    }
    .statusinactive {
        display:block;background:#CC2900;color:#fff;width:80px;padding: 5px;
        border-radius: 3px; 
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
    }
</style>    
<div align="center">
    <form name="" id="" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <br>
                    <div class="title">&nbsp;&nbsp;&nbsp;Card Migration Fulfillment</div>
                    <div class="pad5" align="right"></div>
                        <hr color="black">
                    <div class="pad5" align="right"></div>
                    <br>
            <div class="content">
                <table id="tbladdpromo">
                    <tr>
                        <td id="caption" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Card Number: &nbsp;</td>
                        <td id="field"><?php echo $txtCardNumber; ?></td>
<!--                        <td id="caption" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Card Type: </td>-->
<!--                        <td id="field">
                            <select name="cboCardType" id="cboCardType">
                                <option value="0">-Please select-</option>
                                <option value="1">UB Card</option>
                                <option value="2">Temp Card</option>
                            </select>    
                        </td>-->
                        <td>
                            <?php echo $btnSubmit; ?> &nbsp;&nbsp;<img id="imgloading" src="images/loading-blue.gif" width="15" height="15"/>
                        </td>
                        <input type="hidden" name="hdnCardNumber" id="hdnCardNumber" />
                        <input type="hidden" name="hdnTempCode" id="hdnTempCode" />
                    </tr>
                </table>
            </div> 
        </div>
    </form>
    <div id="dialog" title="">
        <p id="msg">
        </p>
    </div>  
    <div id="fulfillmentdlg" title="Fulfillment of Card Migration">
        <h4>Below are the details of the failed migration.</h4>
        <table style="margin: 30px 0 0 0">
            <tr>
                <td>
                    <b>UB Card Number: &nbsp;</b>
                </td>
                <td>
                    <span id="cardnumber"></span> &nbsp; 
                </td>    
                <td>
                    <b><span id="cardstatus" class="statusinactive"></span></b>
                </td>
            </tr>
            <tr>
                <td></td><td></td>
            </tr>
            <tr>
                <td>
                    <b>Temp Code: &nbsp;</b>
                </td>
                <td>
                    <span id="tempcode"></span> &nbsp;
                </td>   
                <td>
                    <b><span id="tempstatus" class="statusactive"></span></b>
                </td>    
            </tr>
        </table>    
        <br />
    </div>  
</div>
<?php include("footer.php"); ?>
                        