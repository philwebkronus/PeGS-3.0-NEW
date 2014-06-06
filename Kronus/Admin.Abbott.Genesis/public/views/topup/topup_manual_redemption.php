<?php 
$pagetitle = "Manual Redemption History"; 
include "header.php";
$vaccesspages = array('5');
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
<div id="workarea">
    <form id="frmexport" method="post">
        <div id="pagetitle"><?php echo $pagetitle;?></div>
        <br />
        <label>Start Date</label>&nbsp;<input type="text" value="<?php echo date('Y-m-d') ?>" id="startdate" readonly="readonly" name="startdate" />&nbsp;<img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('startdate', false, 'ymd', '-');"/>
        <label>End Date</label>&nbsp;<input type="text" value="<?php echo date('Y-m-d',strtotime(date("Y-m-d", strtotime(date('Y-m-d'))))) ?>" id="enddate" readonly="readonly" name="enddate" />&nbsp;<img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('enddate', false, 'ymd', '-');"/> 
        <input type="button" value="Search" id="btnsearch"/>
        <br /><br />
        <div align="center" id="pagination">
            <table id="manualredemp">
            </table>
            <div id="pager2"></div>
        </div>
        <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1200px;">
            <br />
            <input type="button" id="btnpdf" value="Export to PDF File" style="float:right;"/>
            <input type="button" id="btnexcel" value="Export to Excel File" style="float:right;"/>
        </div>
    </form> 
</div>
<script type="text/javascript" src="jscripts/topup_date_validation.js" ></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#btnpdf').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=manualredemppdf');
            jQuery('#frmexport').submit();                 
        });
        
        jQuery('#btnexcel').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=manualredempexcel');
            jQuery('#frmexport').submit();
        });
        
        jQuery("#manualredemp").jqGrid({
            url : 'process/ProcessTopUpPaginate.php?action=getmanualredemption',
            datatype: "json",
            colNames:['Site / PEGS Code','Site / PEGS Name','POS Account', 'Terminal Code', 'Reported Amount', 'Processed By', 'Transaction Date','Ticket ID','Transaction ID','Remarks','Status', 'Service Name'],
            rowNum:10,
            height: 280,
            width: 1200,
            rowList:[10,20,30],
            pager: '#pager2',
            viewrecords: true,
            sortorder: "asc",
            caption: "Manual Redemption History",
            colModel:[
                {name:'SiteCode', index:'SiteCode',align:'left'},
                {name:'SiteName', index:'SiteName',align:'left'},
                {name:'POSAccountNo',index:'POSAccountNo',align:'center'},
                {name:'Terminal',index:'TerminalName',align:'left'},
                {name:'ReportedAmount',index:'ReportedAmount',align:'right'},
                {name:'RequestedBy',index:'UserName',align:'left'},
                {name:'TransDate',index:'TransDate',align:'left'},
                {name:'TicketID',index:'TicketID',align:'left'},
                {name:'TransactionID',index:'TransactionID',align:'left'},
                {name:'Remarks',index:'Remarks',align:'left'},
                {name:'Status',index:'Status',align:'left'},
                {name:'ServiceName',index:'ServiceName',align:'left'},
            ],     
            resizable:true
        });
        
        jQuery('#btnsearch').click(function() {
            if(!validateDateTopup()) {
              return false;
            }                
            var startdate = jQuery('#startdate').val();
            var enddate = jQuery('#enddate').val();
            jQuery("#manualredemp").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=getmanualredemption&startdate="+startdate+
                "&enddate="+enddate,page:1}).trigger("reloadGrid");             
        });  
    });
</script>
<?php  
    }
}
include "footer.php"; ?>