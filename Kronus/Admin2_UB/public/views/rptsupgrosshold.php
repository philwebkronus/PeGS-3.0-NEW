<?php
    $pagetitle = "Gross Hold";
    include 'process/ProcessRptSupervisor.php';
    include 'header.php';
    
    $vaccesspages = array('3');
    $vctr = 0;
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
    jQuery(document).ready(function()
    {
        jQuery("#btnquery").bind('click', function()
        {
            var result = chkdateformat();
            if(result == true)
            {
                $('#userdata').trigger("reloadGrid");
                jQuery("#senchaexport1").show();
                jqgrid();
            }   
        });
    });
    
    function jqgrid()
    {
        jQuery("#userdata").jqGrid({
               url:'process/ProcessRptSupervisor.php',
               mtype: 'post',
               postData: {
                            paginate: function() {return "GrossHold";},
                            strDate: function() {return $("#rptDate").val();},
                            endDate: function() {return $("#rptDate2").val();},
                            cmbsitename: function() {return $("#cmbsite").val();}
                         },
               datatype: "json",
               colNames:['Real Cashier Name', 'Total Deposit', 'Total Reload', 'Total Redemption', 'Gross Hold'],
               colModel:[
                         {name:'Name',index:'uname', align: 'center', sortable:false},
                         {name:'TotalDeposit', align: 'right', sortable:false},
                         {name:'TotalReload', align: 'right', sortable:false},
                         {name:'TotalWithdrawal', align: 'right', sortable:false},
                         {name:'GrossHold', align: 'right', sortable:false}
                        ],
               rowNum:10,
               rowList:[10,20,30],
               height: 220,
               width: 1000,
               pager: '#pager2',
               viewrecords: true,
               sortorder: "asc",
               loadComplete: function (){gettotal();},
               caption:"Gross Hold"
            });
            jQuery("#userdata").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
            
            function gettotal()
            {
                jQuery.ajax({
                   url: 'process/ProcessRptSupervisor.php',
                   data: {
                             gettotal: function(){return "GetTotals"},
                             strDate: function() {return $("#rptDate").val();},
                             endDate: function() {return $("#rptDate2").val();},
                             cmbsitename: function() {return $("#cmbsite").val();}
                         },
                   type: 'post',
                   dataType: 'json',
                   success: function (data){
                       var withdraw = data.withdraw;
                       var deposit = data.deposit;
                       var reload = data.reload;
                       var grosstotal = data.grosstotal;
                       var granddeposit = data.granddeposit;
                       var grandreload = data.grandreload;
                       var grandwithdraw = data.grandwithdraw;
                       var grandgross = data.grosshold;
                       var sales = data.sales;
                       var grandsales = data.grandsales;
                       document.getElementById('trans').style.display='block';
                       //display summary per page
                       document.getElementById('totsales').innerHTML = sales;
                       document.getElementById('totwithdraw').innerHTML = withdraw;
                       document.getElementById('totgross').innerHTML = grosstotal;
                       document.getElementById('sales').innerHTML = grandsales;
                       document.getElementById('withdraw').innerHTML = grandwithdraw;
                       document.getElementById('gross').innerHTML = grandgross;
                   },
                   error: function(e)
                   {
                       alert(e.responseText);
                       document.getElementById('totsales').innerHTML = "0.00";
                       document.getElementById('totwithdraw').innerHTML = "0.00";
                       document.getElementById('totgross').innerHTML = "0.00";
                       document.getElementById('sales').innerHTML = "0.00";
                       document.getElementById('withdraw').innerHTML = "0.00";
                       document.getElementById('gross').innerHTML = "0.00";
                   }
                });
            }
    }
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form method="post" action="#">
        <input type="hidden" id="txtDate" value="<?php echo date("Y-m-d");?>" />
        <table>
            <tr>
                <td>Start Date</td>
                <td>
                    <input name='strDate' id='rptDate' readonly value="<?php echo date("Y-m-d");?>" />
                    <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('strDate', false, 'ymd', '-');"/>
                </td>
            </tr>
            <tr>
                <td>End Date</td>
                <td>
                    <input name='endDate' id='rptDate2' readonly value="<?php echo date ( 'Y-m-d'); ?>" />
                    <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('endDate', false, 'ymd', '-');"/>
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
            <div id="pager2" style="height: 180px;">
                <table id="trans" style="background-color:#D6EB99; margin-right: 200px; display: none; font-size: 14px; height: 40% ">
                    <tr>
                        <td>Summary per Page</td>
                        <td style="padding-left: 150px;"></td>
                        <td>Total Sales</td>
                        <td id="totsales" style="font-weight: bold;"></td>
                        <td style="padding-left: 80px;"></td>
                        <td>Total Redemption</td>
                        <td id="totwithdraw" style="font-weight: bold;"></td>
                        <td style="padding-left: 20px;"></td>
                        <td>Total Grosshold</td>
                        <td id="totgross" style="font-weight: bold;"></td>
                    </tr>
                    <tr>
                        <td>Grand Total</td>
                        <td style="padding-left: 150px;"></td>
                        <td>Sales</td>
                        <td id="sales" style="font-weight: bold;"></td>
                        <td style="padding-left: 80px;"></td>
                        <td>Redemption</td>
                        <td id="withdraw" style="font-weight: bold;"></td>
                        <td style="padding-left: 20px;"></td>
                        <td>Grosshold</td>
                        <td id="gross" style="font-weight: bold;"></td>
                    </tr>
                </table>
            </div>
            <div id="senchaexport1" style="background-color: #6A6A6A; width: 1000px; padding-bottom: 60px; display: none;">
                <br />
                <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' 
                       onclick="window.location.href='process/ProcessRptSupervisor.php?pdf=generatepdf&DateFrom='+document.getElementById('rptDate').value+'&DateTo='+document.getElementById('rptDate2').value" style="float: right;" />  
                <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
                       onclick="window.location.href='process/ProcessRptSupervisor.php?excel=generateexel&DateFrom='+document.getElementById('rptDate').value+'&DateTo='+document.getElementById('rptDate2').value+'&fn=GrossHold_for_'+document.getElementById('rptDate').value" style="float: right;"/>
            </div>
        </div>
</div>
<?php  
    }
}
include "footer.php"; ?>