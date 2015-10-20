<?php

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Membership Demographics";
$currentpage = "Reports";

App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberInfo");

App::LoadControl("DatePicker");
App::LoadControl("Pagination");
App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("DataGrid");
App::LoadControl("Hidden");


$_Members = new Members();
$_MemberInfo = new MemberInfo();

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
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$dsmaxdate->AddYears(+21);
$dsmindate->AddYears(-100);

$fromdateverified = new DatePicker("fromDateverified", "fromDateverified", "From");
$fromdateverified->MaxDate = $dsmaxdate->CurrentDate;
$fromdateverified->MinDate = $dsmindate->CurrentDate;
$fromdateverified->ShowCaption = false;
$fromdateverified->SelectedDate = date('Y-m-d');
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
$btnSubmit->IsSubmit = true;
$btnSubmit->CssClass = "btnDefault roundcorners";
$btnSubmit->Style = "padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px;";
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
        $fromdate = $fromdateverified->SubmittedValue;
        $todate = $todateverified->SubmittedValue;
        list($yr, $mon, $day) = preg_split("/\-/", $todate);

        $todate = $yr."-".$mon."-".($day+1);

        function CalculateAge($BirthDate) {
            list($Year, $Month, $Day) = explode("-", $BirthDate);
            if(date("m") >  $Month){
                $age = date("Y") - $Year;
            }
            elseif(date("m") <  $Month){
                $age = date("Y") - $Year;
                $age = $age - 1;
            }
            elseif($Month ==  date("m")){
                if(date("d") < $Day){
                    $age = date("Y") - $Year;
                    $age = $age - 1;
                }
                else{
                    $age = date("Y") - $Year;
                }
            }

            return $age;
        }

        $boysbday = $_MemberInfo->getBirthdays(1, $fromdate, $todate);
        $girlsbday = $_MemberInfo->getBirthdays(2, $fromdate, $todate);

        $arrboys21 = array();
        $arrboys31 = array();
        $arrboys41 = array();
        $arrboys51 = array();
        $arrboys60 = array();

        foreach ($boysbday as $value) {

            $boysage = CalculateAge($value['Birthdate']);

            if($boysage >= 21 && $boysage <= 30){
                array_push($arrboys21, $boysage);
            }

            if($boysage >= 31 && $boysage <= 40){
                array_push($arrboys31, $boysage);
            }

            if($boysage >= 41 && $boysage <= 50){
                array_push($arrboys41, $boysage);
            }

            if($boysage >= 51 && $boysage <= 60){
                array_push($arrboys51, $boysage);
            }

            if($boysage > 60 ){
                array_push($arrboys60, $boysage);
            }
        }

        $arrgirls21 = array();
        $arrgirls31 = array();
        $arrgirls41 = array();
        $arrgirls51 = array();
        $arrgirls60 = array();

        foreach ($girlsbday as $value2) {

            $girlsage = CalculateAge($value2['Birthdate']);



            if($girlsage >= 21 && $girlsage <= 30){
                array_push($arrgirls21, $girlsage);
            }

            if($girlsage >= 31 && $girlsage <= 40){
                array_push($arrgirls31, $girlsage);
            }

            if($girlsage >= 41 && $girlsage <= 50){
                array_push($arrgirls41, $girlsage);
            }

            if($girlsage >= 51 && $girlsage <= 60){
                array_push($arrgirls51, $girlsage);
            }

            if($girlsage > 60 ){
                array_push($arrgirls60, $girlsage);
            }

        }
        $countboys21 = count($arrboys21);
        $countboys31 = count($arrboys31);
        $countboys41 = count($arrboys41);
        $countboys51 = count($arrboys51);
        $countboys60 = count($arrboys60);

        $countgirls21 = count($arrgirls21);
        $countgirls31 = count($arrgirls31);
        $countgirls41 = count($arrgirls41);
        $countgirls51 = count($arrgirls51);
        $countgirls60 = count($arrgirls60);

        $total = $countboys21 + $countgirls21; //total result of people ages 21 - 30

        $total2 = $countgirls31 + $countboys31; //total result of people ages 31 - 40

        $total3 = $countgirls41 + $countboys41; //total result of people ages 41 - 50

        $total4 = $countboys51 + $countgirls51; //total result of people ages 51 - 60


        $total5 = $countboys60 + $countgirls60; //total result of people ages 61 and up

        $maletotal = $countboys21+$countboys31+$countboys41+$countboys51+$countboys60;
        $femaletotal = $countgirls21+$countgirls31+$countgirls41+$countgirls51+$countgirls60;
        $supertotal = $total+$total2+$total3+$total4+$total5;

        if($supertotal > 0){
            $percent1 = ($total/$supertotal)*100;
            $percent = round($percent1);

            $percent21 = ($total2/$supertotal)*100;
            $percent2 = round($percent21);

            $percent31 = ($total3/$supertotal)*100;
            $percent3 = round($percent31);

            $percent41 = ($total4/$supertotal)*100;
            $percent4 = round($percent41);

            $percent51 = ($total5/$supertotal)*100;
            $percent5 = round($percent51);
        }
        else{
            $percent1 = 0;
            $percent21 = 0;
            $percent31 = 0;
            $percent41 = 0;
            $percent51 = 0;
            $percent = 0;
            $percent2 = 0;
            $percent3 = 0;
            $percent4 = 0;
            $percent5 = 0;
        }

        //computation to get Total percentage
        $percentage = $percent1+$percent21+$percent31+$percent41+$percent51;
        $percentage = round($percentage);

        //array format to be passed on the grid
      $resultz = array(
        array(
            'Date Range' => '21-30',
            'Male' => $countboys21,
            'Female' => $countgirls21,
            'Total' => $total,
            'Percentage' => $percent." %",
        ),
          array(
            'Date Range' => '31-40',
            'Male' => $countboys31,
            'Female' => $countgirls31,
            'Total' => $total2,
            'Percentage' => $percent2." %",
        ),
          array(
            'Date Range' => '41-50',
            'Male' => $countboys41,
            'Female' => $countgirls41,
            'Total' => $total3,
            'Percentage' => $percent3." %",
        ),
          array(
            'Date Range' => '51-60',
            'Male' => $countboys51,
            'Female' => $countgirls51,
            'Total' => $total4,
            'Percentage' => $percent4." %",
        ),
          array(
            'Date Range' => '61 and above',
            'Male' => $countboys60,
            'Female' => $countgirls60,
            'Total' => $total5,
            'Percentage' => $percent5." %",
        ),
          array(
            'Date Range' => 'TOTAL',
            'Male' => $maletotal,
            'Female' => $femaletotal,
            'Total' => $supertotal,
            'Percentage' => $percentage.' %',
        )
      );

        $result = $resultz;

        $datagrid_bn = new DataGrid();
        $datagrid_bn->AddColumn("Age Bracket", "Date Range", DataGridColumnType::Text, DataGridColumnAlignment::Left);
        $datagrid_bn->AddColumn("Male", "Male", DataGridColumnType::Text, DataGridColumnAlignment::Center);
        $datagrid_bn->AddColumn("Female", "Female", DataGridColumnType::Text, DataGridColumnAlignment::Center);
        $datagrid_bn->AddColumn("Total", "Total", DataGridColumnType::Text, DataGridColumnAlignment::Center);
        $datagrid_bn->AddColumn("Percentage", "Percentage", DataGridColumnType::Text, DataGridColumnAlignment::Center);

        $datagrid_bn->DataItems = $result;
        $membershipdemographics = $datagrid_bn->Render();

        $showresult = true;
    }
}
else{
    $showresult = false;
}
?>

<?php include("header.php"); ?>
<script language="javascript" type="text/javascript">
  $(document).ready(function()
  {
    $("#btnSubmit").click(function()
    {
        var date = new Date();
        var curr_date = date.getDate();
        var curr_month = date.getMonth();
        curr_month = curr_month + 1;
        var curr_year = date.getFullYear();
        var datez1 = $("#fromDateverified").val();
        var datez2 = $("#toDateverified").val();

           if(curr_month < 10)
           {
               curr_month = "0" + curr_month;
               if(curr_date < 10)
                  curr_date = "0" + curr_date;
           }
        var datenow = curr_year + '-'+ curr_month + '-'+ curr_date;

         if((datenow) < (document.getElementById('fromDateverified').value))
         {
           alert("Queried date must not be greater than today");
           $('#results').hide();
           return false;
         }
         else if((datenow) < (document.getElementById('toDateverified').value))
         {
           alert("Queried date must not be greater than today");
           $('#results').hide();
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
            $('#results').show();
            return true;
         }
    });


  });
</script>
<div align="center">
    </form>
    <form name="bannedplayerlists" id="bannedplayerlists" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <br />
            <div style="float: left; margin-left: 30px;" class="title">Membership Demographics:</div>
            <br /><br />
            <hr color="black">
            <br /><br />
            <table>
            <tr>
            <td>&nbsp; &nbsp;&nbsp; &nbsp;Filters: &nbsp; &nbsp;</td>
            <td>From&nbsp;</td>
            <td><?php echo $fromdateverified; ?></td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To&nbsp;</td>
            <td><?php echo $todateverified; ?></td>
            <td width="20"></td>
            <td><?php echo $btnSubmit; ?> </td>
            </tr>
            <tr></tr>
            <tr></tr>
            <tr>
            <td></td>
            <td></td>
            <td></td>
            </tr>
            </table>
             <div class="content">
                <div id="results">
                    <?php if($showresult)
                    {?>
                        <div align="right" class="pad5">

                        </div>
                        <hr color="black">
                        <br />
                        <div align="right" class="pad5">
                            <?php echo $membershipdemographics; ?>
                        </div>
                        <label>&nbsp;&nbsp;Note: Percentages are rounded to the nearest whole number.</label>
                    <?php
                    }?>
                </div>
            </div>
        </div>
    </form>
</div>
<?php include("footer.php"); ?>
