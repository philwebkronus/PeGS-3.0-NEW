<?php
    $pagetitle = "Audit Trail";
    include 'process/ProcessRptOthers.php';
    include 'header.php';
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
       jQuery('#btnquery').bind('click',function(){
            var validate = validatedate(jQuery("#rptDate").val());
            if(validate == true)
            {
                $('#userdata').trigger("reloadGrid");
                jQuery("#senchaexport1").show();
                jqgrid();
            }
       }); 
       function jqgrid()
       {
            jQuery("#userdata").jqGrid({
                   url:'process/ProcessRptOthers.php',
                   mtype: 'post',
                   postData: {
                                rptpage: function() {return "AuditTrail";},
                                strDate: function() {return $("#rptDate").val();}
                                //endDate: function() {return $("#rptDate2").val();},
                             },
                   datatype: "json",
                   colNames:['User Name', 'Transaction Details', 'Transaction Date', 'IP Address'],
                   colModel:[
                             {name:'AID',index:'AID', align: 'left', width: 70},
                             {name:'TransactioDetails', index:'TransDetails', align: 'left', width: 250},
                             {name:'TransctionDate', index:'TransDateTime', align: 'left', width: 90},
                             {name:'IPAddress', index:'RemoteIP', align: 'left', width: 70}
                            ],
                   rowNum:10,
                   rowList:[10,20,30],
                   height: 220,
                   width: 1200,
                   pager: '#pager2',
                   viewrecords: true,
                   sortorder: "asc",
                   caption:"Audit Trail",
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
                <td>Transaction Date</td>
                <td>
                    <input name='strDate' id='rptDate' readonly value="<?php echo date("Y-m-d");?>" />
                    <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('strDate', false, 'ymd', '-');"/>
                </td>
            </tr>
<!--            <tr>
                <td>End Date</td>
                <td>
                    <input name='endDate' id='rptDate2' readonly value="<?php //echo date("Y-m-d");?>" />
                    <img name="cal" src="../images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('endDate', false, 'ymd', '-');"/>
                </td>
            </tr>-->
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
                       onclick="window.location.href='process/ProcessRptOthers.php?pdf=AuditTrail&DateFrom='+document.getElementById('rptDate').value" style="float: right;" />  
           <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
                       onclick="window.location.href='process/ProcessRptOthers.php?excel=AuditTrail&DateFrom='+document.getElementById('rptDate').value+'&fn=AuditTrail_for_'+document.getElementById('rptDate').value" style="float: right;"/>
        </div>
    </div>
    
</div>
<?php include 'footer.php'; ?>