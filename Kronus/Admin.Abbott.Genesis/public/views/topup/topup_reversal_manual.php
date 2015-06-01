<?php 
$pagetitle = "Manual Top-up Reversal History";
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
    <form method="post" id="frmexport">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <label>Transaction Date:</label>&nbsp;<input type="text" value="<?php echo date('Y-m-d') ?>" id="startdate" readonly="readonly" name="startdate" />&nbsp;<img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('startdate', false, 'ymd', '-');"/>
<!--        <label>End Date</label>&nbsp;<input type="text" value="<?php echo date('Y-m-d',strtotime(date("Y-m-d", strtotime(date('Y-m-d'))))) ?>" id="enddate" readonly="readonly" name="enddate" />&nbsp;<img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('enddate', false, 'ymd', '-');"/> -->
        <input type="button" value="Search" id="btnsearch"/>
        <br /><br />
        <div align="center" id="pagination">
            <table id="reverersalmtu">
            </table>
            <div id="pager2"></div>
        </div>
        <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1200px;">
            <br />
            <input type="button" id="btnpdf" value="Export to PDF File" style="float: right;"/>
            <input type="button" id="btnexcel" value="Export to Excel File" style="float: right;"/>
        </div>
    </form>
</div>
<script type="text/javascript" src="jscripts/topup_date_validation.js" ></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#btnpdf').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=reversalmanualpdf');
            jQuery('#frmexport').submit();            
        });
        
        jQuery('#btnexcel').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=reversalmanualexcel');
            jQuery('#frmexport').submit();  
        });        
        
        jQuery("#reverersalmtu").jqGrid({
            url : 'process/ProcessTopUpPaginate.php?action=getreversalmanual',
            datatype: "json",
            colNames:['Site / PEGS Code', 'Site / PEGS Name', 'POS Account', 'Start Balance', 'End Balance', 'Reversed Amount', 'Transaction Date', 'Reversed By'],
            rowNum:10,
            height: 280,
            width: 1200,
            rowList:[10,20,30],
            pager: '#pager2',
            viewrecords: true,
            sortorder: "asc",
            caption:"Manual Top-up Reversal History",
            colModel:[
                {name:'SiteCode',index:'SiteCode',align:'left'},
                {name:'SiteName',index:'SiteName',align:'left'},
                {name:'POSAccountNo',index:'POSAccountNo',align:'center'},
                {name:'StartBalance',index:'StartBalance',align:'right'},
                {name:'EndBalance',index:'EndBalance',align:'right'},
                {name:'ReversedAmount',index:'ReversedAmount',align:'right'},
                {name:'TransDate',index:'TransDate',align:'center'},
                {name:'ReversedBy',index:'ReversedBy',align:'right'}
            ],     
            resizable:true
        });
        
        jQuery('#btnsearch').click(function(){
            if(!validateDateTopup()) {
              return false;
            }              
            var startdate = jQuery('#startdate').val();
            //var enddate = jQuery('#enddate').val();
            jQuery("#reverersalmtu").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=getreversalmanual&startdate="+startdate
                    ,page:1}).trigger("reloadGrid");  
        });
    });
</script>
<?php  
    }
}
include "footer.php"; ?>