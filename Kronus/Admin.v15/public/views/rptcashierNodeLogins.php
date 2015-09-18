<?php
/*
 * 
 * @Report Generation for Cashier Node Logins
 * @Added by Mark Nicolas Atangan
 * 
 */
    $pagetitle = "Cashier Node Logins";
    include 'process/ProcessRptCashierNodeLogins.php';
    include 'header.php';
    $vaccesspages = array('9');
$vctr = 0;
//Check if for the account type of the current session
if(isset($_SESSION['acctype']))
{
    foreach ($vaccesspages as $val)
    {
        if($_SESSION['acctype'] == $val)
        {
            break;
        }
        else
        {
            $vctr = $vctr + 1;
        }
    }

    if(count($vaccesspages) == $vctr)
    {
        echo "<script type='text/javascript'>document.getElementById('blockl').style.display='block';
                     document.getElementById('blockf').style.display='block';</script>";
    }
    else
    {
?>

<script type="text/javascript">
    jQuery(document).ready(function(){
                //Date Validation
       jQuery('#btnquery').bind('click',function(){
//       var validate = validatedate(jQuery("#popupDatepicker1").val());
       var fromDateTime = $("#popupDatepicker1").val().split(" ");
       var toDateTime = $("#popupDatepicker2").val().split(" ");
       var fromTimeArray = fromDateTime[1].split(":");
       var fromTime = parseInt("".concat(fromTimeArray[0]).concat(fromTimeArray[1]).concat(fromTimeArray[2]), 10);
       var toTimeArray = toDateTime[1].split(":");
       var toTime = parseInt("".concat(toTimeArray[0]).concat(toTimeArray[1]).concat(toTimeArray[2]),10);
       var fromDate = fromDateTime[0].split("-");
       var toDateArray = toDateTime[0].split("-");
       var toDate = parseInt("".concat(toDateArray[0]).concat(toDateArray[1]).concat(toDateArray[2]));
       var fromDateAsInt = parseInt("".concat(fromDate[0]).concat(fromDate[1]).concat(fromDate[2]));
       var year = parseInt(fromDate[0], 10);
       var month = parseInt(fromDate[1], 10);
       var day = parseInt(fromDate[2], 10);
       var theNextDate = "";
       var leadingZero = "0";
       var currentDate = <?php echo date("Ymd"); ?>;
       
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
 
       if((toDate == fromDateAsInt || toDate == theNextDate) && (toDate <= currentDate && fromDateAsInt <= currentDate)) {
           
          if(toDate == fromDateAsInt) {
               
               if(fromTime == 0) {

                    return true;
       
               }
               else {
                  
                    if((toTime >= fromTime)&&(toTime <= 235959)){
                         // Passed the validation Display the JQgrid
                                    $('#userdata').trigger("reloadGrid");
                                    jQuery("#senchaexport1").show();
                                    jqgrid();
                    }
                    else {
                        alert("Your Starting and Ending Date and Time must be within 24-Hour Frame.");
                    }
               
               }
               
               
           }
           else {
               
               if(toTime <= fromTime) {
                   // Passed the validation Display the JQgrid
                                    $('#userdata').trigger("reloadGrid");
                                    jQuery("#senchaexport1").show();
                                    jqgrid();
                    }
               else {
                   alert("Your Starting and Ending Date and Time must be within 24-Hour Frame.");
               }
             } 
       }
       else {
            //           Check if Date FROM or Date TO is greater than the current Date
           if((toDate > currentDate || fromDateAsInt > currentDate)) {
               
               alert("Invalid Date Range: Date Selected must not be Greater than current Date.");
               
           }
           //           Check if Date FROM is Greater than Date TO
           else if (( fromDateAsInt > toDate))
           {
             alert("Invalid Date Range: Date FROM must not be Greater than date TO.");
           }
           else {
               alert("Your Starting and Ending Date and Time must be within 24-Hour Frame.");
                }
           
       }
            
       }); 
       
       function jqgrid()
       {
            jQuery("#userdata").jqGrid({
                   url:'process/ProcessRptCashierNodeLogins.php',
                   mtype: 'post',
                   postData: {
                                rptpage: function() {return "CashierNodeLogins";},
                                strDate: function() {return $("#popupDatepicker1").val();},
                                endDate: function() {return $("#popupDatepicker2").val();}
                                //endDate: function() {return $("#rptDate2").val();},
                             },
                   datatype: "json",
                   colNames:['Site Code', 'Cashier Instance/ Node Logins'],
                   colModel:[
                             {name:'SiteCode',index:'SiteCode', align: 'left', width: 90},
                             {name:'TransactioDetails', index:'TransDetails', align: 'left', width: 230},
                            ],
                   rowNum:10,
                   rowList:[10,20,30],
                   height: 220,
                   width: 1200,
                   pager: '#pager2',
                   viewrecords: true,
                   sortorder: "asc",
                   caption:"Cashier Node Logins",
                   loadError : function(XMLHttpRequest, e)  {
                        if(XMLHttpRequest.status == 401)
                        {
                            window.location.reload();
                        }
                   }
           });
           jQuery("#userdata").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
        }
    });
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form method="post" action="#">
        <input type="hidden" id="txtDate" value="<?php echo date("Y-m-d");?>" />
        <table>
             <tr>
                 <td colspan="5">Transaction Date Range:</td>
             </tr>
             <tr>
                <td>&nbsp;Date From: </td>
                <td>
<input name="strDate" id="popupDatepicker1" readonly value="<?php $thestime = date('Y-m-d H:i:s');;
$datetime_from = date("Y-m-d H:i:s",strtotime("-24 hours",strtotime($thestime)));
echo $datetime_from; ?>"/>
                 <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="javascript:NewCssCal('popupDatepicker1','yyyyMMdd','dropdown',true,'24',true)"/>
                </td>
              <td>
                To:
                &nbsp;
              </td>
              <td>
                <input name="endDate" id="popupDatepicker2" readonly value="<?php echo date('Y-m-d H:i:s'); ?>"/>
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="javascript:NewCssCal('popupDatepicker2','yyyyMMdd','dropdown',true,'24',true)"/>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="button" value="Query" id="btnquery"/>
        </div>
    </form>
    
    <!--jqgrid pagination on this part-->
    <div align="center">
        <table border="1" id="userdata"></table>
        <div id="pager2"></div>
        <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; display: none; width: 1200px;">
           <br />
           <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' 
                       onclick="window.location.href='process/ProcessRptCashierNodeLogins.php?pdf=CashierNodeLogins&DateFrom='+document.getElementById('popupDatepicker1').value+'&DateTo='+document.getElementById('popupDatepicker2').value+'&sord=asc'" style="float: right;" />  
           <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
                       onclick="window.location.href='process/ProcessRptCashierNodeLogins.php?excel=CashierNodeLogins&DateFrom='+document.getElementById('popupDatepicker1').value+'&DateTo='+document.getElementById('popupDatepicker2').value+'&fn=CashierNodeLogins_for_'+document.getElementById('txtDate').value+'&sord=asc'" style="float: right;"/>
        </div>
    </div>
    
</div>
<?php 

        }
    }
    include 'footer.php'; ?>
