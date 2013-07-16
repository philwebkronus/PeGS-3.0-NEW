<?php
/*
 * Description: View the of Member Activity such as Deposit, Reload, and Withdrawal.
 * @Author: Junjun S. Hernandez
 * Date Created: 07-02-2013 05:00 PM
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Member Activity Report";
$currentpage = "Reports";

App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Kronus", "TransactionSummary");

App::LoadCore('Validation.class.php');

App::LoadControl("DatePicker");
App::LoadControl("ComboBox");
App::LoadControl("Pagination");
App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("DataGrid");
App::LoadControl("Hidden");


// Instantiate pagination object with appropriate arguments
$pagesPerSection = 10;       // How many pages will be displayed in the navigation bar
// former number of pages will be displayed
$options = array(5, 10, 25, 50, "All"); // Display options
$paginationID = "changestat";     // This is the ID name for pagination object
$stylePageOff = "pageOff";     // The following are CSS style class names. See styles.css
$stylePageOn = "pageOn";
$styleErrors = "paginationErrors";
$styleSelect = "paginationSelect";

$fproc = new FormsProcessor();

$_Cards = new MemberCards();
$defaultsearchvalue = 'Enter Card Number';
$txtCardNumber = new TextBox("txtCardNumber", "txtCardNumber", "Card Number: ");
$txtCardNumber->ShowCaption = false;
$txtCardNumber->CssClass = 'validate[required]]';
$txtCardNumber->Style = 'color: #666';
$txtCardNumber->Size = 30;
$txtCardNumber->AutoComplete = false;
$txtCardNumber->Args = 'placeholder="Enter Card Number" onkeypress="javascript: return AlphaNumericOnly(event)"';
$fproc->AddControl($txtCardNumber);

$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$dsmaxdate->AddYears(+21);
$dsmindate->AddYears(-100);
$thestime = date('Y-m-d H:i:s');
$datetime_from = date("Y-m-d", strtotime("-1 week", strtotime($thestime)));
$fromTransDate = new DatePicker("fromTransDate", "fromTransDate", "From");
$fromTransDate->MaxDate = $dsmaxdate->CurrentDate;
$fromTransDate->MinDate = $dsmindate->CurrentDate;
$fromTransDate->ShowCaption = false;
$fromTransDate->SelectedDate = $datetime_from;
$fromTransDate->Value = date('Y-m-d');
$fromTransDate->YearsToDisplay = "-100";
$fromTransDate->CssClass = "validate[required]";
$fromTransDate->isRenderJQueryScript = true;
$fromTransDate->Size = 27;
$fproc->AddControl($fromTransDate);


$toTransDate = new DatePicker("toTransDate", "toTransDate", "To");
$toTransDate->MaxDate = $dsmaxdate->CurrentDate;
$toTransDate->MinDate = $dsmindate->CurrentDate;
$toTransDate->SelectedDate = date('Y-m-d');
$toTransDate->Value = date('Y-m-d');
$toTransDate->ShowCaption = false;
$toTransDate->YearsToDisplay = "-100";
$toTransDate->CssClass = "validate[required]";
$toTransDate->isRenderJQueryScript = true;
$toTransDate->Size = 27;
$fproc->AddControl($toTransDate);

$aAge = new TextBox("aAge", "aAge");
$aAge->ReadOnly = True;
$fproc->AddControl($aAge);

$aGender = new TextBox("aGender", "aGender");
$aGender->ReadOnly = True;
$fproc->AddControl($aGender);

$aStatus = new TextBox("aStatus", "aStatus");
$aStatus->ReadOnly = True;
$fproc->AddControl($aStatus);

//$hdnMemberCardID = new Hidden("hhdnMemberCardID", "hdnMemberCardID");
//$fproc->AddControl($hdnMemberCardID);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Query");
$btnSubmit->ShowCaption = true;
$btnSubmit->Enabled = true;
$fproc->AddControl($btnSubmit);

$fproc->ProcessForms();

$showresult = false;

if ($fproc->IsPostBack) {

//    if ($btnSubmit->SubmittedValue == "Query") {
//        $showresult = true;
//    }
} else {
    $showresult = false;
}
?>

<?php include("header.php"); ?>
<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript'>

    $(document).ready(function() {
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
        $("#txtCardNumber").blur(function() {
            $("#txtCardNumber").change();
        });
        $("#txtCardNumber").change(function() {
            if ($("#txtCardNumber").val() == "")
            {
                $("#btnSubmit").val("Query");
            }

        });

        $('#btnSubmit').live('click', function() {
            $('#playerlists').attr('action', 'updateaccount.php');
            $('#playerlists').submit();
            getProfileData($("#txtCardNumber").val());
            var card = jQuery("#txtCardNumber").val();
            if (card == '') {
                $("#dRange").html("");
                $('#playerprofile').hide();
                $('#playerprofiledetails').hide();
                $('#pagination').hide();
                alert('Please enter a valid Card Number');
                return false;
            }
            var date1 = "<?php echo date("Ymd"); ?>"
            var isValidDateTime = validateDateTime(date1);
            var date = new Date();
            var curr_date = date.getDate();
            var curr_month = date.getMonth();
            curr_month = curr_month + 1;
            var curr_year = date.getFullYear();
            var curr_hr = date.getHours();
            var curr_min = date.getMinutes();
            var curr_secs = date.getSeconds();
            if (curr_month < 10)
            {
                curr_month = "0" + curr_month;
                if (curr_date < 10)
                    curr_date = "0" + curr_date;
            }
            var datenow = curr_year + '-' + curr_month + '-' + curr_date + ' ' + curr_hr + ':' + curr_min + ':' + curr_secs;
            var time1 = " <?php echo App::getParam("cutofftime"); ?>";
            var datez1 = $("#fromTransDate").val().concat(time1);
            var datez2 = $("#toTransDate").val().concat(' 05:59:59');
            var separator = " to ";
            if (isValidDateTime == true) {
                if (card == -1) {
                    jQuery('#players').GridUnload();
                    $("#dRange").html("");
                    $('#playerprofile').hide();
                    $('#playerprofiledetails').hide();
                    $('#pagination').hide();
                    alert("Please Select a Card Number");
                    return false;
                }
                else if ((datenow) < (document.getElementById('fromTransDate').value))
                {
                    jQuery('#players').GridUnload();
                    $("#dRange").html("");
                    $('#playerprofile').hide();
                    $('#playerprofiledetails').hide();
                    $('#pagination').hide();
                    alert("Queried date must not be greater than today");
                    return false;
                }
                else if ((datenow) < (document.getElementById('toTransDate').value))
                {
                    jQuery('#players').GridUnload();
                    $("#dRange").html("");
                    $('#playerprofile').hide();
                    $('#playerprofiledetails').hide();
                    $('#pagination').hide();
                    alert("Queried date must not be greater than today");
                    return false;
                }
                else if (datez2 < datez1)
                {
                    jQuery('#players').GridUnload();
                    $("#dRange").html("");
                    $('#playerprofile').hide();
                    $('#playerprofiledetails').hide();
                    $('#pagination').hide();
                    alert("Queried End Date must me greater than Start Date");
                    return false;
                }
                else
                {
                    $("#dRange").html("Date Range: ".concat(datez1, separator, datez2));
                    $('#playerprofile').show();
                    $('#playerprofiledetails').show();
                    $('#pagination').show();
                    loadDetails();
                }
            }

        });

        function loadDetails()
        {
            var url = "Helper/helper.playeractivity.php";

            jQuery('#players').GridUnload();
            jQuery("#players").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                    pager: function() {
                        return "ActivityReport";
                    },
                    Card: function() {
                        return jQuery("#txtCardNumber").val();
                    },
                    fromTransDate: function() {
                        return jQuery("#fromTransDate").val();
                    },
                    toTransDate: function() {
                        return jQuery("#toTransDate").val();
                    }
                },
                datatype: "json",
                colNames: ['Date', 'eGames', 'Playing Time', 'Deposit', 'Reload', 'Redemption', 'Player Win', 'TotalTime'],
                colModel: [
                    {name: 'Date', index: 'Date', align: 'left', width: 245},
                    {name: 'eGames', index: 'eGames', align: 'left', width: 245},
                    {name: 'PlayingTime', index: 'PlayingTime'},
                    {name: 'Deposit', index: 'Deposit', formatter: 'currency', formatoptions: {thousandsSeparator: ', '}, align: 'right', width: 245},
                    {name: 'Reload', index: 'Reload', formatter: 'currency', formatoptions: {thousandsSeparator: ', '}, align: 'right', width: 245},
                    {name: 'Redemption', index: 'Redemption', formatter: 'currency', formatoptions: {thousandsSeparator: ', '}, align: 'right', width: 245},
                    {name: 'PlayerWin', index: 'PlayerWin', formatter: 'currency', formatoptions: {thousandsSeparator: ', '}, align: 'right', width: 245},
                    {name: 'TotalTime', index: 'TotalTime', hidden: true}
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                height: 250,
                width: 970,
                pager: '#pager2',
                refresh: true,
                loadonce: true,
                viewrecords: true,
                multiselect: false,
                subGrid: true,
                sortorder: "asc",
                caption: "Activity Report",
                footerrow: true,
                subGridRowExpanded: function(subgrid_id, row_id) { // we pass two parameters // subgrid_id is a id of the div tag created whitin a table data // the id of this elemenet is a combination of the "sg_" + id of the row // the row_id is the id of the row // If we wan to pass additinal parameters to the url we can use // a method getRowData(row_id) - which returns associative array in type name-value // here we can easy construct the flowing
                    var subgrid_table_id, pager_id;
                    subgrid_table_id = subgrid_id + "_t";
                    pager_id = "p_" + subgrid_table_id;
                    $("#" + subgrid_id).html("<table id='" + subgrid_table_id + "' class='scroll'></table><div id='" + pager_id + "' class='scroll'></div>");
                    
                    jQuery("#" + subgrid_table_id).jqGrid({
                        url: url,
                        mtype: 'post',
                        postData: {
                            pager: function() {
                                return "ActivityReport2";
                            },
                            transID: function() {
                                return row_id;
                            }
                        },
                        rowNum: 10,
                        rowList: [10, 20, 30],
                        height: 'auto',
                        loadonce: true,
                        refresh: true,
                        datatype: "json",
                        viewrecords: true,
                        pager: pager_id, 
                        sortorder: "asc",
                        caption: "Transaction Details",
                        colNames: ['Deposit', 'Reload', 'Redemption', 'Payment Method', 'Voucher Code'],
                        colModel: [
                            {name: 'Deposit', index: 'Deposit', formatter: 'currency', formatoptions: {thousandsSeparator: ', '}, align: 'right', width: 245},
                            {name: 'Amount', index: "Amount", formatter: 'currency', formatoptions: {thousandsSeparator: ', '}, align: 'right', width: 150},
                            {name: 'Withdrawal', index: 'Withdrawal', formatter: 'currency', formatoptions: {thousandsSeparator: ', '}, align: 'right', width: 245},
                            {name: 'PaymentMethod', index: 'PaymentMethod', align: 'right', width: 245},
                            {name: 'VoucherCode', index: 'VoucherCode', align: 'right', width: 245}
                        ]
                        });
                        jQuery("#" + subgrid_table_id).setGridParam({ rowNum: 10 }).trigger("reloadGrid");
                        jQuery("#" + subgrid_table_id).jqGrid('navGrid', "#" + pager_id,{edit:false,add:false,del:false,search:false});
//                    jQuery("#" + subgrid_table_id).jqGrid('navGrid', "#" + pager_id,
//                            {edit: false, add: false, del: false, search: false})
                    
                },
                loadComplete: function() {

                    var grid = $("#players");
                    datatype: "json",
                            sumPlayingTime = grid.jqGrid('getCol', 'TotalTime', false, 'sum');

                    var hours = parseInt(sumPlayingTime / 3600) % 24;
                    var minutes = parseInt(sumPlayingTime / 60) % 60;
                    var seconds = sumPlayingTime % 60;

                    var result = (hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);

                    sumDeposit = grid.jqGrid('getCol', 'Deposit', false, 'sum');
                    sumReload = grid.jqGrid('getCol', 'Reload', false, 'sum');
                    sumRedemption = grid.jqGrid('getCol', 'Redemption', false, 'sum');
                    sumPlayerWin = grid.jqGrid('getCol', 'PlayerWin', false, 'sum');
                    jQuery("#players").jqGrid('footerData',
                            'set',
                            {Date: 'Totals',
                                PlayingTime: result,
                                Deposit: sumDeposit,
                                Reload: sumReload,
                                Redemption: sumRedemption,
                                PlayerWin: sumPlayerWin
                            });
                }
            });
            jQuery("#players").jqGrid('navGrid', '#pager2',
                    {
                        edit: false, add: false, del: false, search: false, refresh: true});

        }

        function getProfileData(cardnumber) {
            $.ajax(
                    {
                        url: "Helper/helper.playeractivity.php",
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
                            document.getElementById("playerprofiledetails").style.display = "block";
                            $("#tblage").html("<label>" + dataprofile.Age + "</label>");
                            $("#tblgender").html("<label>" + dataprofile.Gender + "</label>");
                            $("#tblstatus").html("<label>" + dataprofile.Status + "</label>");

                        },
                        error: function(error)
                        {
                            var dataprofile = $.parseJSON(error);
                            $("#errorMessage").html("<span>" + dataprofile.msg + "</span>");

                        }
                    });
        }

        function validateDateTime(date) {
            var time1 = " <?php echo App::getParam("cutofftime"); ?>";
            var time2 = " <?php echo App::getParam("cutofftime"); ?>";
            var date1 = $("#fromTransDate").val().concat(time1);
            var date2 = $("#toTransDate").val().concat(time2);

            var fromDateTime = date1.split(" ");
            var toDateTime = date2.split(" ");
            var fromTimeArray = fromDateTime[1].split(":");
            var fromTime = parseInt("".concat(fromTimeArray[0]).concat(fromTimeArray[1]).concat(fromTimeArray[2]), 10);
            var toTimeArray = toDateTime[1].split(":");
            var toTime = parseInt("".concat(toTimeArray[0]).concat(toTimeArray[1]).concat(toTimeArray[2]), 10);
            var fromDate = fromDateTime[0].split("-");
            var toDateArray = toDateTime[0].split("-");
            var toDate = parseInt("".concat(toDateArray[0]).concat(toDateArray[1]).concat(toDateArray[2]));
            var fromDateAsInt = parseInt("".concat(fromDate[0]).concat(fromDate[1]).concat(fromDate[2]));
            var toDatez = toDateTime[0].split("-");

            var year = parseInt(fromDate[0], 10);
            var year2 = parseInt(toDatez[0], 10);
            var month = parseInt(fromDate[1], 10);
            var month2 = parseInt(toDatez[1], 10);
            var day = parseInt(fromDate[2], 10);
            var day2 = parseInt(toDatez[2], 10);
            var monthsum = month2 - month;

            var theNextDate = "";
            var leadingZero = "0";

            var currentDate = date;

            /**
             * @Code Block to check validity of date and time parameters
             * 
             */
            if (month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12) { //31 Days

                if (month == 12) {

                    if (day == 31) {
                        theNextDate = theNextDate.concat((year + 1), '01', '01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, '12', (leadingZero.concat((day + 1).toString())).substr(-2));
                    }

                }
                else {

                    if (day == 31) {
                        theNextDate = theNextDate.concat(year, (leadingZero.concat((month + 1).toString())).substr(-2), '01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, (leadingZero.concat((month).toString())).substr(-2), (leadingZero.concat((day + 1).toString())).substr(-2));
                    }

                }

            }
            else if (month == 4 || month == 6 || month == 9 || month == 11) { //30 Days

                if (day == 30) {
                    theNextDate = theNextDate.concat(year, (leadingZero.concat((month + 1).toString())).substr(-2), '01');
                }
                else {
                    theNextDate = theNextDate.concat(year, (leadingZero.concat((month).toString())).substr(-2), (leadingZero.concat((day + 1).toString())).substr(-2));
                }

            }
            else { //February

                if ((year % 4) == 0) {

                    if (day == 29) {
                        theNextDate = theNextDate.concat(year, '03', '01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, '02', (leadingZero.concat((day + 1).toString())).substr(-2));
                    }

                }
                else {

                    if (day == 28) {
                        theNextDate = theNextDate.concat(year, '03', '01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, '02', (leadingZero.concat((day + 1).toString())).substr(-2));
                    }

                }

            }

            if (month2 < month) {
                $("#dRange").html("");
                $('#playerprofile').hide();
                $('#playerprofiledetails').hide();
                $('#pagination').hide();
                alert("End Date must be greater than the Start Date");
                jQuery('#players').GridUnload();
                $("#dRange").html("");
                return false;
            }
            else {
                var monthresult = month2 - month;
                if (monthresult > 1) {
                    $("#dRange").html("");
                    $('#playerprofile').hide();
                    $('#playerprofiledetails').hide();
                    $('#pagination').hide();
                    alert("Your Starting and Ending Date and Time must be within 1-Week Frame");
                    jQuery('#players').GridUnload();
                    $("#dRange").html("");
                    return false;
                }
                else {

                    if (year == year2) {

                        if (month == month2) {
                            var daydiff = day2 - day;

                            if (daydiff <= 7) {
                                return true;
                            }
                            else {
                                $("#dRange").html("");
                                $('#playerprofile').hide();
                                $('#playerprofiledetails').hide();
                                $('#pagination').hide();
                                alert("Your Starting and Ending Date and Time must be within 1-Week Frame");
                                jQuery('#players').GridUnload();
                                $("#dRange").html("");
                                return false;
                            }
                        }
                        else {
                            if (month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12)
                            {
                                //31
                                var result = 31 - day;
                                var result2 = result + day2;
                                if (result2 <= 7) {
                                    return true;
                                }

                                else
                                {
                                    $("#dRange").html("");
                                    $('#playerprofile').hide();
                                    $('#playerprofiledetails').hide();
                                    $('#pagination').hide();
                                    alert("Your Starting and Ending Date and Time must be within 1-Week Frame");
                                    jQuery('#players').GridUnload();
                                    $("#dRange").html("");
                                    return false;
                                }
                            }
                            else if (month == 4 || month == 6 || month == 9 || month == 11)
                            {
                                //30
                                var result = 30 - day;
                                var result2 = result + day2;
                                if (result2 <= 7) {
                                    return true;
                                }
                                else {
                                    $("#dRange").html("");
                                    $('#playerprofile').hide();
                                    $('#playerprofiledetails').hide();
                                    $('#pagination').hide();
                                    alert("Your Starting and Ending Date and Time must be within 1-Week Frame");
                                    jQuery('#players').GridUnload();
                                    $("#dRange").html("");
                                    return false;
                                }

                            }
                            else {
                                $("#dRange").html("");
                                $('#playerprofile').hide();
                                $('#playerprofiledetails').hide();
                                $('#pagination').hide();
                                alert("Your Starting and Ending Date and Time must be within 1-Week Frame");
                                jQuery('#players').GridUnload();
                                $("#dRange").html("");
                                return false;
                            }
                        }


                    }
                    else {
                        $("#dRange").html("");
                        $('#playerprofile').hide();
                        $('#playerprofiledetails').hide();
                        $('#pagination').hide();
                        alert("Your Starting and Ending Date and Time must be within 1-Week Frame");
                        jQuery('#players').GridUnload();
                        $("#dRange").html("");
                        return false;
                    }
                }
            }


            if ((fromDateAsInt > toDate)) {

                alert("Invalid Date");
                jQuery('#players').GridUnload();
                $("#dRange").html("");
                return false;
            }
            else if ((toDate > currentDate || fromDateAsInt > currentDate)) {
                alert("Queried date must not be greater than today");
                jQuery('#players').GridUnload();
                $("#dRange").html("");
                return false;
            }
            else {

                alert("Your Starting and Ending Date and Time must be within 1-Week Frame2");
                return false;
            }



        }

    });

</script>

<div align="center">

    <form name="playerlists" id="playerlists" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <br/>
            <div class="title">&nbsp;&nbsp;&nbsp;&nbsp;Member Activity</div>
            <br/>
            <div align="center">
                <table align="center">
                    <tr>
                        <td>
                            <table align ='left'>
                                <td><?php echo $txtCardNumber; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            </table>
                            <table align ='right'>
                                <tr>
                                    <td>From&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td><?php echo $fromTransDate; ?></td>
                                </tr>
                                <tr>
                                    <td>To&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td><?php echo $toTransDate; ?></td>
                                </tr>
                                <tr>
                                    <td align =" 'right'">
                                    <td align ="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <br>
                                        <?php echo $btnSubmit; ?> </td>
                                </tr>
                            </table>
                        </td>
                    </tr> 
                </table>
            </div>
            <div class="content">
                <div align="center" id="playerprofile">
                    <div id="playerprofiledetails" style="display: none;">
                        <table id="tblplayerdetails">
                            <tr><th colspan="2">Player Profile</th></tr>
                            <tr><td style="width: 200px;">Age</td><td id="tblage"></td></tr>
                            <tr><td style="width: 200px;">Gender</td><td id="tblgender"></td></tr>
                            <tr><td style="width: 200px;">Status</td><td id="tblstatus"></td></tr>
                        </table>
                    </div>
                </div>
                <br/><br/><br/>
                <br/><br/><br/>
                <br/><br/><br/>
                <div id="pagination">
                    <div id="dateRange">
                        &nbsp;&nbsp;&nbsp;&nbsp;<label id="dRange"></label>
                    </div>
                    <br/>
                    <table align="center" border="1" id="players">
                        <table align="center" border="1" id="players2">

                        </table>
                        <div id="pager3"></div>
                    </table>
                    <div id="pager2"></div>
                    <span id="errorMessage"></span>
                </div>
            </div>

        </div>
    </form>
</div>
<?php include("footer.php"); ?>