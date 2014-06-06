<?php 
$pagetitle = "GH Balance Per Cut-off"; 
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
    <form method="post" id="frmexport" method='POST' action='GrossHoldBalanceViewDetails.php'>
        <input type='hidden' name='hdnsiteid' id='hdnsiteid' value='' />
        <input type='hidden' name='hdnstartdate' id='hdnstartdate' value='' />
        <input type='hidden' name='hdnenddate' id='hdnenddate' value='' />
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <table>
            <tr>
                <td>Start Date</td>
                <td>
                    <input type="text" value="<?php echo date('Y-m-d') ?>" id="startdate" readonly="readonly" name="startdate" />&nbsp;<img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('startdate', false, 'ymd', '-');"/>
                </td>
                <td>End Date</td>
                <td>
                    <input type="text" value="<?php echo date('Y-m-d',strtotime(date("Y-m-d", strtotime(date('Y-m-d'))))) ?>" id="enddate" readonly="readonly" name="enddate" />&nbsp;<img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('enddate', false, 'ymd', '-');"/> 
                </td>
            </tr>
            <tr>
                <td>Site / PEGS</td>
                <td>
                    <select name="selsitecode" id="selsitecode">
                        <option value="All">All</option>
                        <?php foreach($param['sites'] as $site): ?>
                         <option label="<?php echo $site['SiteName']." / ".$site['POSAccountNo']; ?>" value="<?php echo $site['SiteID'] ?>"><?php echo substr($site['SiteCode'], strlen(BaseProcess::$sitecode)); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label id="lblsitename"></label>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="button" value="Search" id="btnsearch"/>
        </div>
        <br /><br />
        <div align="center" id="pagination" style="overflow-x: scroll; width: 1200px;">
            <table id="tblreplenish">
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
        jQuery('#selsitecode').change(function(){
            jQuery('#lblsitename').html(jQuery(this).children('option:selected').attr('label'));
        });
      
        jQuery('#btnpdf').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=grossholdbalancepdf');
            jQuery('#frmexport').submit();            
        });
        
        jQuery('#btnexcel').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=grossholdbalanceexcel');
            jQuery('#frmexport').submit();  
        });        
        
        jQuery('#tblreplenish').jqGrid({
            url : 'process/ProcessTopUpPaginate.php?action=grossholdbalance&site='+jQuery('#selsitecode').val(),
            datatype: "json",
            colNames:['Site / PEGS Code', 'Cut Off Date', 'Beginning Balance', 'Deposit', 'Reload','Redemption','Manual Redemption','Printed Tickets',
                                'Active Tickets for the Day','Coupons','Cash on Hand','Gross Hold','Replenishment','Collection','Ending Balance', 'View Details'],
            rowNum:10,
            height: 280,
            width: 1800,
            pager: '#pager2',
            viewrecords: true,
            rowList:[10,20,30],
            sortorder: "asc",
            caption: "GH Balance Per Cut-off",            
            colModel:[
                {name:'SiteCode',index:'SiteCode',align:'left'},
                {name:'cutoff', index:'cutoff',align:'center'},
                {name:'BeginningBalance',index:'BeginningBalance',align:'right'},
                {name:'InitialDeposit',index:'InitialDeposit',align:'right'},
                {name:'Reload',index:'Reload',align:'right'},
                {name:'Redemption',index:'Redemption',align:'right'},
                {name:'ManualRedemption',index:'manualredemption',align:'right'},
                {name:'PrintedTickets',index:'PrintedTickets',align:'right'},
                {name:'UnusedTickets',index:'UnusedTickets',align:'right'},
                {name:'Coupon',index:'Coupon',align:'right'},
                {name:'CashonHand',index:'CashonHand',align:'right'},
                {name:'GrossHold',index:'GrossHold',align:'right'},
                {name:'Replenishment',index:'Replenishment',align:'right'},
                {name:'Collection',index:'Collection',align:'right'},
                {name:'EndingBalance',index:'EndingBalance',align:'right'},
                {name:'GHBalCutOffInfo',index:'GHBalCutOffInfo',align:'center'}
            ]
        });
        
        jQuery('#btnsearch').click(function(){
            if(!validateDateTopup()) {
              return false;
            }
            var startdate = jQuery('#startdate').val();
            var enddate = jQuery('#enddate').val();
            var site = jQuery('#selsitecode').val();
            jQuery("#tblreplenish").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=grossholdbalance&startdate="+startdate+
                "&enddate="+enddate+"&site="+site,page:1}).trigger("reloadGrid");  
        });
    });
</script>
<?php  
    }
}
include "footer.php"; ?>