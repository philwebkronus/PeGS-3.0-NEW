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

if($fproc->IsPostBack)
{
    if($btnSubmit->SubmittedValue == "Query")
    {
        $fromdate = $fromdateverified->SubmittedValue;
        $todate = $todateverified->SubmittedValue;
        
        //Group info by gender and age
        $memInfo21to31 = $_MemberInfo->getAge21to30(1, $fromdate, $todate);
        $memInfo21to32 = $_MemberInfo->getAge21to30(2, $fromdate, $todate);

        $memInfo31to40M = $_MemberInfo->getAge31to40(1, $fromdate, $todate);
        $memInfo31to40F = $_MemberInfo->getAge31to40(2, $fromdate, $todate);

        $memInfo41to50M = $_MemberInfo->getAge41to50(1, $fromdate, $todate);
        $memInfo41to50F = $_MemberInfo->getAge41to50(2, $fromdate, $todate);

        $memInfo51to60M = $_MemberInfo->getAge51to60(1, $fromdate, $todate);
        $memInfo51to60F = $_MemberInfo->getAge51to60(2, $fromdate, $todate);

        $memInfo61andupM = $_MemberInfo->getAge61andup(1, $fromdate, $todate);
        $memInfo61andupF = $_MemberInfo->getAge61andup(2, $fromdate, $todate);

        //loop through results from 21 to 30
        foreach ($memInfo21to31 as $value) {
            foreach ($value as $value2) {
            }
        }

        foreach ($memInfo21to32 as $val) {
            foreach ($val as $value3) {
            }
        }
        
        $total = $value2 + $value3; //total result of people ages 21 - 30
            
        //loop through results from 31 to 40
        foreach ($memInfo31to40M as $val1) {
            foreach ($val1 as $value4) {
            }
        }

        foreach ($memInfo31to40F as $val2) {
            foreach ($val2 as $value5) {
            }
        }
        $total2 = $value4 + $value5; //total result of people ages 31 - 40
                
        //loop through results from 41 to 50
        foreach ($memInfo41to50M as $val3) {
            foreach ($val3 as $value6) {
            }
        }

        foreach ($memInfo41to50F as $val4) {
            foreach ($val4 as $value7) {
            }
        }
        $total3 = $value6 + $value7; //total result of people ages 41 - 50
                
        //loop through results from 51 to 60
        foreach ($memInfo51to60M as $val5) {
            foreach ($val5 as $value8) {
            }
        }

        foreach ($memInfo51to60F as $val6) {
            foreach ($val6 as $value9) {
            }
        }
        $total4 = $value8 + $value9; //total result of people ages 51 - 60
            
        //loop through results from 61 and up
        foreach ($memInfo61andupM as $val7) {
            foreach ($val7 as $value10) {
            }
        }

        foreach ($memInfo61andupF as $val8) {
            foreach ($val8 as $value11) {
            }
        }
        $total5 = $value10 + $value11; //total result of people ages 61 and up
                
        $maletotal = $value2+$value4+$value6+$value8+$value10;
        $femaletotal = $value3+$value5+$value7+$value9+$value11;
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
            'Male' => $value2,
            'Female' => $value3,
            'Total' => $total,
            'Percentage' => $percent." %",
        ),
          array(
            'Date Range' => '31-40',
            'Male' => $value4,
            'Female' => $value5,
            'Total' => $total2,
            'Percentage' => $percent2." %",
        ),
          array(
            'Date Range' => '41-50',
            'Male' => $value6,
            'Female' => $value7,
            'Total' => $total3,
            'Percentage' => $percent3." %",
        ),
          array(
            'Date Range' => '51-60',
            'Male' => $value8,
            'Female' => $value9,
            'Total' => $total4,
            'Percentage' => $percent4." %",
        ),
          array(
            'Date Range' => '61 and above',
            'Male' => $value10,
            'Female' => $value11,
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
                   alert("Queried End Date must me greater than Start Date");
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
            <div style="float: left;" class="title">Membership Demographics:</div>
            <br /><br />
            <table>
            <tr>
            <td>Filters: &nbsp; &nbsp;</td>    
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
                    <?php
                    }?>
                </div>
            </div>     
        </div>
    </form>
</div>
<?php include("footer.php"); ?>
