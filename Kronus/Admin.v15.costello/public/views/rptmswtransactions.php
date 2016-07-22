<?php
/**
  Document   : mswtransactions
  Created on : July 07, 2016
  Author     :  John Aaron Vida
  Description:
  MSW Transactions Reports
 */
$pagetitle = "MSW Transactions";
include "process/ProcessCSManagement.php";
include "header.php";
$vaccesspages = array('6', '9', '18');
$vctr = 0;
if (isset($_SESSION['acctype'])) {
    foreach ($vaccesspages as $val) {
        if ($_SESSION['acctype'] == $val) {
            break;
        } else {
            $vctr = $vctr + 1;
        }
    }

    if (count($vaccesspages) == $vctr) {
        echo "<script type='text/javascript'>document.getElementById('blockl').style.display='block';
                     document.getElementById('blockf').style.display='block';</script>";
    } else {
        ?>

        <div id="workarea"> 
            <script type="text/javascript">
                $(document).ready(function() {

                    function onChangeBetRefID() {
                        $("#txtbetrefid").keypress(function() {
                            jQuery("#displayUBcard").text(" ");
                            jQuery("#displayServiceUsername").text(" ");
                            jQuery("#Betpagination").hide();
                            jQuery("#Recreditpagination").hide();
                            jQuery("#senchaexport1").hide();

                        });
                    }
                    onChangeBetRefID();

                    $("#txtbetrefid").focus(function() {
                        $("#txtbetrefid").bind('paste', function(event) {
                            setTimeout(function(event) {
                                var data = $("#txtbetrefid").val();
                                if (!specialcharacter(data)) {
                                    $("#txtbetrefid").val("");
                                    $("#txtbetrefid").focus();
                                }
                            }, 0);
                        });
                    });

                    $('#btnSubmit').click(function()
                    {
                        showLoading();
                        var url = 'process/ProcessCSManagement.php';
                        if (document.getElementById('txtbetrefid').value == "" || document.getElementById('txtbetrefid').length == 0)
                        {
                            hideLoading();
                            alert("Please Input Bet Reference ID.");
                            document.getElementById('txtbetrefid').focus();
                            return false;
                        }
                        var url2 = 'process/ProcessRptMSW.php';
                        jQuery("#displayUBcard").text("Loading..");
                        jQuery("#displayServiceUsername").text("Loading..");
                        getBetReferenceDetails(url2);
                        //

                        //getRecredit(url2,betrefid);
                    });

                    function showLoading() {
                        $("#loading").show();
                        $("#fade").show();
                    }
                    function hideLoading() {
                        $("#loading").hide();
                        $("#fade").hide();
                    }

                    function getBetReferenceDetails(url) {
                        jQuery.ajax({
                            url: url,
                            type: 'post',
                            data: {paginate: function() {
                                    return "GetBetReferenceDetails";
                                },
                                txtbetrefid: function() {
                                    return jQuery("#txtbetrefid").val();
                                }
                            },
                            success: function(data) {
                                var json = jQuery.parseJSON(data);
                                if (json.CardNumber == null) {
                                    hideLoading();
                                    jQuery("#displayUBcard").text(" ");
                                    jQuery("#displayServiceUsername").text(" ");
                                    jQuery("#Betpagination").hide();
                                    jQuery("#Recreditpagination").hide();
                                    alert("Error : Bet Reference ID Does Not Exists ");

                                }
                                else {

                                    getBetPayoutResetlement(url);
                                    getRecredit(url);
                                    hideLoading();
                                    jQuery("#Betpagination").show();
                                    jQuery("#Recreditpagination").show();
                                    jQuery("#senchaexport1").show();
                                    jQuery("#senchaexport2").show();
                                    jQuery("#displayUBcard").text(" ");
                                    jQuery("#displayServiceUsername").text(" ");
                                    jQuery("#displayUBcard").text(json.CardNumber);
                                    jQuery("#displayServiceUsername").text(json.ServiceUsername);
                                }

                            },
                            error: function(XMLHttpRequest, e)
                            {
                                alert(XMLHttpRequest.responseText);
                                if (XMLHttpRequest.status == 401)
                                {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                    function getBetPayoutResetlement(url)
                    {
                        //this part displays the site details
                        jQuery("#bet").jqGrid({
                            url: url,
                            mtype: 'post',
                            postData: {
                                paginate: function() {
                                    return 'GetBetPayoutResetlement';
                                },
                                txtbetrefid: function() {
                                    return jQuery("#txtbetrefid").val();
                                }
                            },
                            datatype: "json",
                            colNames: ['BetSlipID', 'Amount', 'Transaction No', 'Transaction Date', 'Date Last Updated', 'Status', 'Tracking ID', 'Transaction Type', 'Resettlement Type'],
                            colModel: [
                                {name: 'BetSlipID', index: 'BetSlipID', align: 'center', width: 160},
                                {name: 'Amount', index: 'Amount', align: 'right', width: 160},
                                {name: 'TransactionNo', index: 'TransactionNo', align: 'center', width: 160},
                                {name: 'TransDate', index: 'TransDate', align: 'center', width: 240},
                                {name: 'LastTransUpdate', index: 'LastTransUpdate', align: 'center', width: 240},
                                {name: 'Status', index: 'Status', align: 'center', width: 200},
                                {name: 'TrackingID', index: 'TrackingID', align: 'center', width: 200},
                                {name: 'TransTypeID', index: 'TransTypeID', align: 'center', width: 170},
                                {name: 'ResettleType', index: 'ResettleType', align: 'center', width: 160}
                            ],
                            rowNum: 10,
                            rowList: [10, 20, 30],
                            height: 240,
                            width: 1200,
                            pager: '#pager1',
                            viewrecords: true,
                            sortorder: "desc",
                            caption: "Bet, Payout, Resettlement Transactions",
                            gridview: true,
                            autowidth: false,
                            shrinkToFit: false,
                            forceFit: true,
                        });
                        jQuery("#bet").jqGrid('navGrid', '#pager1', {edit: false, add: false, del: false, search: false});
                        jQuery('#bet').trigger('reloadGrid');
                    }


                    function getRecredit(url)
                    {
                        //this part displays the site details
                        jQuery("#recredit").jqGrid({
                            url: url,
                            mtype: 'post',
                            postData: {
                                paginate: function() {
                                    return 'GetRecreditDetails';
                                },
                                txtbetrefid: function() {
                                    return jQuery("#txtbetrefid").val();
                                }
                            },
                            datatype: "json",
                            colNames: ['BetSlipID', 'Amount', 'Transaction No', 'Transaction Date', 'Date Last Updated', 'Status', 'Tracking ID'],
                            colModel: [
                                {name: 'BetSlipID', index: 'BetSlipID', align: 'center', width: 160},
                                {name: 'Amount', index: 'Amount', align: 'right', width: 160},
                                {name: 'TransactionNo', index: 'TransactionNo', align: 'center', width: 160},
                                {name: 'TransDate', index: 'TransDate', align: 'center', width: 240},
                                {name: 'LastTransUpdate', index: 'LastTransUpdate', align: 'center', width: 240},
                                {name: 'Status', index: 'Status', align: 'center', width: 190},
                                {name: 'TrackingID', index: 'TrackingID', align: 'center', width: 180},
                            ],
                            rowNum: 10,
                            rowList: [10, 20, 30],
                            height: 240,
                            width: 1200,
                            pager: '#pager2',
                            viewrecords: true,
                            sortorder: "desc",
                            caption: "Re-Credit Transactions",
                            gridview: true,
                            autowidth: false,
                            shrinkToFit: false,
                            forceFit: true,
                        });
                        jQuery("#recredit").jqGrid('navGrid', '#pager2', {edit: false, add: false, del: false, search: false});
                        jQuery('#recredit').trigger('reloadGrid');
                    }
                });
                
                function limitText(limitField, limitNum) {
                    if (limitField.value.length > limitNum) {
                        limitField.value = limitField.value.substring(0, limitNum);
                    }
                }
            </script>

            <div id="pagetitle"><?php echo $pagetitle; ?></div>
            <br />
            <input type="hidden" name="chkbalance" id="chkbalance" value="CheckBalance" />
            <form method="post" action="#" id="frmredemption" name="frmcs" class="frmmembership">
                <input type="hidden" name="page" id="page" value="ManualRedemptionUB" />
                <input type="hidden" name="txtterminal" id="txtterminal"/>
                <input type="hidden" name="txtservices" id="txtservices" />
                <input type="hidden" name="terminalcode" id="terminalcode" />
                <table>
                    <tr>
                        <td>
                            Bet Reference ID
                            <input type="text" size="30" onKeyDown="limitText(this,20);" onKeyUp="limitText(this,20);" maxlength="20" class="txtmembership" id="txtbetrefid" name="txtbetrefid" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);" />
                            <!--<div for="txtbetrefid" align='center'>Membership | Temporary</div>-->
                        </td>
                    </tr>
                    <tr>
                        <td>                
                            Membership Card :
                            <label id="displayUBcard"></label>
                        </td>
                    </tr>
                    <tr>
                        <td>                
                            Service Username :
                            <label id="displayServiceUsername"></label>
                        </td>
                    </tr>

                </table>            
                <div id="loading"></div>
                <div id="submitarea"> 
                    <input type="button" value="Submit" id="btnSubmit"/>
                </div>

                <div id="cont">
                    <div id="light" class="white_content" oncontextmenu="return false" style="width: 308px; height:212px;">
                        <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display = 'none';
                                document.getElementById('fade').style.display = 'none';"></div>
                        <input type="hidden"  name="Withdraw" value="Withdraw" />
                        <br />
                        <div id="userdata"></div>

                        <input type="hidden" id="txtamount" name="txtamount"/>
                        <input type="button" id="btnok" value="OK" style="margin-left: 130px;" onclick="document.getElementById('light').style.display = 'none';
                                document.getElementById('fade').style.display = 'none'" />
                    </div>
                    <div id="fade" class="black_overlay" oncontextmenu="return false"></div>
                </div>

                <div id="light2" class="white_page">
                    <div class="close_popup" id="btnClose" onclick="document.getElementById('light2').style.display = 'none';
                            document.getElementById('fade2').style.display = 'none';"></div>
                    <input type="hidden" name="page" value="InsertPegsConfirmation2" />
                    <input type="hidden" name="txtsitecode" id="txtsitecode" />
                    <table id="userdata2" class="tablesorter" align="center">
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr align="right">
                        <br />

                        </tr>
                    </table>
                    <br />
                    <div align="right">
                        <input type="button" id="btnok" value="OK" style="margin-left: 130px;" onclick="document.getElementById('light2').style.display = 'none';
                                document.getElementById('fade2').style.display = 'none'" />
                    </div>        
                </div>
                <div id="fade2" class="black_overlay"></div>
            </form>

            <!-- jqgrid pagination -->
            <div align="center" id="Betpagination">
                <!-- for site listing -->
                <table border="1" id="bet"></table>
                <div id="pager1"></div>
                <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 5px; display: none; margin-top: 5px;height : 40px; width: 1200px;">
                    <table style=" width : 100%;">
                        <tr>
                            <td style="float : left; margin-top: -2px;">
                                <p style="color : #fff;"><i>Note : Data will be retrieved from Production Slave DB</i></p>
                            </td>
                            <td style="float : right; margin-top: 5px;">
                                <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' 
                                       onclick="window.location.href = 'process/ProcessRptMSW.php?pdf=BetMSWTransactions&BetRefID=' + document.getElementById('txtbetrefid').value + '&sord=desc'" style="float: right;" />  
                                <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
                                       onclick="window.location.href = 'process/ProcessRptMSW.php?excel=BetMSWTransactions&BetRefID=' + document.getElementById('txtbetrefid').value + '&sord=desc'" style="float: right;" />  
                            </td>
                        </tr>
                    </table>
                    <br />
                </div>
            </div>
            <br>
            <!-- jqgrid pagination -->
            <div align="center" id="Recreditpagination">
                <!-- for site listing -->
                <table border="1" id="recredit"></table>
                <div id="pager2"></div>
                <div id="senchaexport2" style="background-color: #6A6A6A; padding-bottom: 5px; display: none; margin-top: 5px;height : 40px; width: 1200px;">
                    <table style=" width : 100%;">
                        <tr>
                            <td style="float : left; margin-top: -2px;">
                                <p style="color : #fff;"><i>Note : Data will be retrieved from Production Slave DB</i></p>
                            </td>
                            <td style="float : right; margin-top: 5px;">
                                <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' 
                                       onclick="window.location.href = 'process/ProcessRptMSW.php?pdf=RecreditMSWTransactions&BetRefID=' + document.getElementById('txtbetrefid').value + '&sord=desc'" style="float: right;" />   
                                <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
                                       onclick="window.location.href = 'process/ProcessRptMSW.php?excel=RecreditMSWTransactions&BetRefID=' + document.getElementById('txtbetrefid').value + '&sord=desc'" style="float: right;" />    
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
}
include "footer.php";
?>