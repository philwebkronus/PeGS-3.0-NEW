<?php 
$pagetitle = "Cash on Hand Adjustment History";
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
<form method="post" id="frmreport">
    <div id="workarea">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <label>Transaction Date</label>&nbsp;<input type="text" value="<?php echo date('Y-m-d') ?>" id="startdate" readonly="readonly" name="startdate" />&nbsp;<img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('startdate', false, 'ymd', '-');"/>
<!--        <label>End Date</label>&nbsp;<input type="text" value="<?php echo date('Y-m-d',strtotime(date("Y-m-d", strtotime(date('Y-m-d'))))) ?>" id="enddate" readonly="readonly" name="enddate" />&nbsp;<img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('enddate', false, 'ymd', '-');"/> -->
        <input type="button" value="Search" id="btnsearch"/>
        <br /><br />
        <div align="center" id="pagination">
          <table id="pdgrid">
          </table>
          <div id="pager2"></div>
        </div>
        <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1250px;">
            <br />
            <input type="button" value="Export to PDF File" id="btnPDF" style="float: right;"/>
            <input type="button" value="Export to Excel File" id="btnExcel" style="float:right;"/>
        </div>
    </div>
</form>   
<script type="text/javascript" src="jscripts/topup_date_validation.js" ></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        
        jQuery('#btnPDF').click(function(){
          jQuery('#frmreport').attr('action','process/ProcessTopUpGenerateReports.php?action=getcohadjustmentpdf');
          jQuery('#frmreport').submit();
        });
        
        jQuery('#btnExcel').click(function(){
          jQuery('#frmreport').attr('action','process/ProcessTopUpGenerateReports.php?action=getcohadjustmentexcel');
          jQuery('#frmreport').submit(); 
        });
        
        jQuery('#btnsearch').click(function(){
            
            if(!validateDateTopup()) {
              return false;
            }  
          var startdate = jQuery('#startdate').val();
          //var enddate = jQuery('#enddate').val();
       
//          if(Date.parse(startdate) > Date.parse(enddate)) {
//              alert('Start date should less than End Date'); return false;
//          }
          
          jQuery("#pdgrid").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=cohadjustmentdata&startdate="+startdate
            ,page:1}).trigger("reloadGrid"); 
        });
        
        jQuery("#pdgrid").jqGrid({
            url : 'process/ProcessTopUpPaginate.php?action=cohadjustmentdata',
            datatype: "json",
            colNames:['Site / PEGS Name','POS Account','Amount','Reason','ApprovedBy','ProcessedBy','DateCreated'],
            rowNum:10,
            rowList:[10,20,30],
            height: 280,
            width: 1250,
            pager: '#pager2',
            viewrecords: true,
            sortname: "DateCreated",
            sortorder: "asc",
            caption:"COH Adjustment History",
            colModel:[
                {name:'Site',index:'SiteName',align:'left'},
                {name:'POSAccountNo',index:'POSAccountNo',align:'left'},
                {name:'Amount',index:'Amount',align:'right'},
                {name:'Reason',index:'Reason',align:'left'},
                {name:'ApprovedBy',index:'ApprovedBy',align:'left'},
                {name:'ProcessedBy',index:'CreatedBy',align:'left'},
                {name:'DateCreated',index:'DateCreated',align:'left'}
            ],
            resizable:true
        });
    });
</script>
<?php  
    }
}
include "footer.php"; ?>