<?php

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Transactions Per Cut-Off";
$currentpage = "Reports";

App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Kronus", "TransactionSummary");

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

App::LoadModuleClass('Kronus', 'Sites');
$_Sites = new Sites();
$cboSiteID = new ComboBox("SiteID", "SiteID", "eGames Site: &nbsp;");
$opt1 = null;
$opt1[] = new ListItem("Select One", "-1", true);
$cboSiteID->Items = $opt1;
$arrsites = $_Sites->getAllSite();
$list_posaccts = new ArrayList();
$list_posaccts->AddArray($arrsites);
$cboSiteID->DataSource = $list_posaccts;
$cboSiteID->ShowCaption = true;
$cboSiteID->DataSourceValue = "SiteID";
$cboSiteID->DataSourceText = "SiteName";
$cboSiteID->DataBind();
$fproc->AddControl($cboSiteID);

$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$dsmaxdate->AddYears(+21);
$dsmindate->AddYears(-100);
$thestime = date('Y-m-d H:i:s');
$datetime_from = date("Y-m-d",strtotime("-6 days",strtotime($thestime)));
$fromdateverified = new DatePicker("fromDateverified", "fromDateverified", "From");
$fromdateverified->MaxDate = $dsmaxdate->CurrentDate;
$fromdateverified->MinDate = $dsmindate->CurrentDate;
$fromdateverified->ShowCaption = false;
$fromdateverified->SelectedDate = $datetime_from;
$fromdateverified->Value = date('Y-m-d');
$fromdateverified->YearsToDisplay = "-100";
$fromdateverified->CssClass = "validate[required]";
$fromdateverified->isRenderJQueryScript = true;
$fromdateverified->Size = 20;
$fproc->AddControl($fromdateverified);


$todateverified = new DatePicker("toDateverified", "toDateverified", "To");
$todateverified->MaxDate = $dsmaxdate->CurrentDate;
$todateverified->MinDate = $dsmindate->CurrentDate;
$todateverified->SelectedDate = date('Y-m-d');
$todateverified->Value = date('Y-m-d');
$todateverified->ShowCaption = false;
$todateverified->YearsToDisplay = "-100";
$todateverified->CssClass = "validate[required]";
$todateverified->isRenderJQueryScript = true;
$todateverified->Size = 20;
$fproc->AddControl($todateverified);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Query");
$btnSubmit->ShowCaption = true;
$btnSubmit->Style = "padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px;";
$btnSubmit->Enabled = true;
$fproc->AddControl($btnSubmit);

$fproc->ProcessForms();

$showresult = false;

//Clear the session for Redemtion
if(isset($_SESSION['CardRed'])){
    unset($_SESSION['CardRed']);
}

if($fproc->IsPostBack)
{
    if($btnSubmit->SubmittedValue == "Query")
    {
     $showresult = true;

    }
}
else{
    $showresult = false;
}
?>

<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>-->
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript'>

    $(document).ready(function(){
        $('#pagination').hide();
        $('#btnSubmit').live('click', function(){
            var site = jQuery("#SiteID").val();
            var date1 = "<?php echo date("Ymd");?>"
            var isValidDateTime = validateDateTime(date1);
            var date = new Date();
            var curr_date = date.getDate();
            var curr_month = date.getMonth();
            curr_month = curr_month + 1;
            var curr_year = date.getFullYear();
            var curr_hr = date.getHours();
            var curr_min = date.getMinutes();
            var curr_secs = date.getSeconds();
            if(curr_month < 10)
            {
                curr_month = "0" + curr_month;
                if(curr_date < 10)
                   curr_date = "0" + curr_date;
            }
            var datenow = curr_year + '-'+ curr_month + '-'+ curr_date + ' ' + curr_hr + ':' + curr_min + ':' + curr_secs;
            var time1 = " <?php echo App::getParam("cutofftime");?>";
            var time2 = " <?php echo App::getParam("cutofftimeend");?>";
            var datez1 = $("#fromDateverified").val().concat(time1);
            var datez2 = $("#toDateverified").val().concat(' 05:59:59');
            var separator = " to ";
            if(isValidDateTime == true){
                if(site == -1)
                {
                    alert("Please select e-Games Site");
                    jQuery('#players').GridUnload();
                    $("#dRange").html("");
                    return false;
                }
                else if((datenow) < (document.getElementById('fromDateverified').value))
                {
                   alert("Queried date must not be greater than today");
                   jQuery('#players').GridUnload();
                   $("#dRange").html("");
                   return false;
                }
                else if((datenow) < (document.getElementById('toDateverified').value))
                {
                   alert("Queried date must not be greater than today");
                   jQuery('#players').GridUnload();
                   $("#dRange").html("");
                   return false;
                }
                else if(datez2 < datez1)
                {
                   alert("Queried End Date must be greater than Start Date");
                   jQuery('#players').GridUnload();
                   $("#dRange").html("");
                   return false;
                }
                else
                {
                    $("#dRange").html("Date Range: ".concat(datez1, separator, datez2));
                    document.getElementById("pagination").style.display = "block";
                loadDetails();
                }
            }


        });


        function loadDetails()
        {
            var url = "Helper/helper.transpercutoff.php";
            jQuery('#players').GridUnload();
            jQuery("#players").jqGrid({
                    url:url,
                    mtype: 'post',
                    postData: {
                                Sites : function() {return jQuery("#SiteID").val();},
                                fromDateverified : function() {return jQuery("#fromDateverified").val();},
                                toDateverified : function() {return jQuery("#toDateverified").val();}
                              },
                    datatype: "json",
                    colNames:['Account Number', 'Status', 'Deposit', 'Reload', 'Redemption', 'Player Net Win'],
                    colModel:[
                            {name:'LoyaltyCardNumber',index:'LoyaltyCardNumber',align: 'left',width: 200, fixed: true},
                            {name:'Status',index:'Status', align: 'left',width: 150, fixed: true},
                            {name:'Deposit',index:'Deposit', align: 'right',width: 150, fixed: true},
                            {name:'Reload',index:'Reload', align: 'right',width: 150, fixed: true},
                            {name:'Withdrawal',index:'Withdrawal', align: 'right',width: 150, fixed: true},
                            {name:'PlayerNW',index:'PlayerNW', align: 'right',width: 140, fixed: true}
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    height: 250,
                    width: 970,
                    shrinkToFit: true,
                    pager: '#pager2',
                    refresh: true,
                    loadonce: true,
                    viewrecords: true,
                    sortorder: "asc",
                    caption:"Deposit/Withdrawal Transaction Per Cut Off"
            });
            jQuery("#players").jqGrid('navGrid','#pager2',
                                {
                                    edit:false,add:false,del:false, search:false, refresh: true});
        }

        function validateDateTime (date) {
            var time1 = " <?php echo App::getParam("cutofftime");?>";
            var time2 = " <?php echo App::getParam("cutofftime");?>";
            var date1 = $("#fromDateverified").val().concat(time1);
            var date2 = $("#toDateverified").val().concat(time2);

            var fromDateTime = date1.split(" ");
            var toDateTime = date2.split(" ");
            var fromTimeArray = fromDateTime[1].split(":");
            var fromTime = parseInt("".concat(fromTimeArray[0]).concat(fromTimeArray[1]).concat(fromTimeArray[2]), 10);
            var toTimeArray = toDateTime[1].split(":");
            var toTime = parseInt("".concat(toTimeArray[0]).concat(toTimeArray[1]).concat(toTimeArray[2]),10);
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
            if(month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12) { //31 Days

                if(month == 12) {

                    if(day == 31) {
                        theNextDate = theNextDate.concat((year+1),'01','01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, '12', (leadingZero.concat((day+1).toString())).substr(-2));
                    }

                }
                else {

                    if(day == 31) {
                        theNextDate = theNextDate.concat(year, (leadingZero.concat((month+1).toString())).substr(-2),'01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, (leadingZero.concat((month).toString())).substr(-2), (leadingZero.concat((day+1).toString())).substr(-2));
                    }

                }

            }
            else if (month == 4 || month == 6 || month == 9 || month == 11) { //30 Days

                 if(day == 30) {
                     theNextDate = theNextDate.concat(year, (leadingZero.concat((month+1).toString())).substr(-2),'01');
                 }
                 else {
                     theNextDate = theNextDate.concat(year, (leadingZero.concat((month).toString())).substr(-2), (leadingZero.concat((day+1).toString())).substr(-2));
                 }

            }
            else { //February

                if((year%4) == 0) {

                    if(day == 29) {
                         theNextDate = theNextDate.concat(year, '03','01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, '02', (leadingZero.concat((day+1).toString())).substr(-2));
                    }

                }
                else {

                    if(day == 28) {
                         theNextDate = theNextDate.concat(year, '03','01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, '02', (leadingZero.concat((day+1).toString())).substr(-2));
                    }

                }

            }


                var monthresult = month2 - month;
                if(monthresult > 1){
                    alert("Start and End dates must be within a 1-week time frame.");
                    jQuery('#players').GridUnload();
                    $("#dRange").html("");
                    return false;
                }
                else{

                if(year == year2 ){

                    if(month == month2){
                        var daydiff = day2 - day;

                        if(daydiff < 7){
                            return true;
                        }
                        else{
                            alert("Start and End dates must be within a 1-week time frame.");
                            jQuery('#players').GridUnload();
                            $("#dRange").html("");
                            return false;
                        }
                    }
                    else{
                        if(month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12)
                        {
                            //31
                            var result = 31 - day;
                            var result2 = result + day2;
                            if(result2 <= 7){
                                return true;
                            }

                            else
                            {
                                alert("Start and End dates must be within a 1-week time frame.");
                                jQuery('#players').GridUnload();
                                $("#dRange").html("");
                                return false;
                            }
                        }
                        else if(month == 4 || month == 6 || month == 9 || month == 11)
                        {
                            //30
                            var result = 30 - day;
                            var result2 = result + day2;
                            if(result2 < 7){
                                return true;
                            }
                            else{
                                alert("Start and End dates must be within a 1-week time frame.");
                                jQuery('#players').GridUnload();
                                $("#dRange").html("");
                                return false;
                            }

                        }
                        else{
                            alert("Start and End dates must be within a 1-week time frame.");
                                jQuery('#players').GridUnload();
                                $("#dRange").html("");
                                return false;
                        }
                    }


                  }
                  else{

                      var yeardiff = year2 - year;

                      if(yeardiff > 1){
                          alert("Start and End dates must be within a 1-week time frame.");
                            jQuery('#players').GridUnload();
                            $("#dRange").html("");
                            return false;
                      }
                      else{
                          if(month == 12 && month2 == 1){
                              //31
                            var result = 31 - day;
                            var result2 = result + day2;
                            if(result2 < 7){
                                return true;
                            }

                            else
                            {
                                alert("Start and End dates must be within a 1-week time frame.");
                                jQuery('#players').GridUnload();
                                $("#dRange").html("");
                                return false;
                            }
                          }
                          else{
                              alert("Start and End dates must be within a 1-week time frame.");
                                jQuery('#players').GridUnload();
                                $("#dRange").html("");
                                return false;
                          }
                      }

                  }
               }



                if((fromDateAsInt > toDate) ) {

                    alert("Invalid Date");
                    jQuery('#players').GridUnload();
                    $("#dRange").html("");
                    return false;
                }
                else if((toDate > currentDate || fromDateAsInt > currentDate)){
                    alert("Queried date must not be greater than today");
                    jQuery('#players').GridUnload();
                    $("#dRange").html("");
                    return false;
                }
                else {

                    alert("Start and End dates must be within a 1-week time frame.");
                    return false;
                }



        }
    });
</script>

<div align="center">

    <form name="bannedplayerlists" id="bannedplayerlists" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <br />
            <div class="title">&nbsp;&nbsp;&nbsp;Member Deposit/Withdraw Transaction per Cut-off</div>
            <br />
            <hr color="black" />
            <br />
            <div align="left">
                <table align="left">
                    <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $cboSiteID; ?></td>
                    <td width="30"></td>
                    <td>From&nbsp;</td>
                        <td><?php echo $fromdateverified; ?></td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To&nbsp;</td>
                        <td><?php echo $todateverified; ?></td>
                        <td width="20"></td>
                        <td align ="right"><?php echo $btnSubmit; ?> </td>
                    </tr>
                    <tr>
                    <td></td>
                    <td></td><td></td>
                    <td></td><td></td>
                    <td></td><td></td>
                    </tr>
                </table>
            </div>
<!--            <div id="dateRange" style="float: left;">
                &nbsp;&nbsp;&nbsp;&nbsp;<label id="dRange"></label>
            </div>-->
            <br/><br/>
            <br/>
            <div class="content">
                    <div align="center" id="pagination">
                         <hr color="black" />
                    <br>
                        <table border="1" id="players">

                        </table>
                        <div id="pager2"></div>
                        <span id="errorMessage"></span>
                    </div>
            </div>
        </div>
    </form>
</div>
<?php include("footer.php"); ?>
