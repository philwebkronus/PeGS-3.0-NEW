<?php 
$pagetitle = "Top-up History";
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
<form id="frmexport" method="post" action="#">
    <div id="workarea">
        <div id="pagetitle">Top-up History</div>
        <br />
        <table>
            <tr>
                <td>Transaction Date</td>
                <td>
                    <input type="text" value="<?php echo date('Y-m-d') ?>" id="startdate" readonly="readonly" name="startdate" />
                    &nbsp;
                    <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('startdate', false, 'ymd', '-');"/>
                </td>
            </tr>
<!--            <tr>
                <td>End Date</td>
                <td>
                    <input type="text" value="<?php // echo date('Y-m-d',strtotime(date("Y-m-d", strtotime(date('Y-m-d'))))) ?>" id="enddate" readonly="readonly" name="enddate" />
                    &nbsp;
                    <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('enddate', false, 'ymd', '-');"/> 
                </td>
            </tr>-->
            <tr>
                <td>Top-up Transaction Type</td>
                <td>
                    <select id="seltopuptype" name="seltopuptype">
                        <option value="">All</option>
                        <option value="0">Manual</option>
                        <option value="1">Auto</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Site / PEGS</td>
                <td>
                    <select id="selSiteCode" name="selSiteCode">
                        <option value="">All</option>
                        <?php foreach($param['sitCode'] as $v): ?>
                        <option label="<?php echo $v['SiteName']." / ".$v['POSAccountNo']; ?>" value="<?php echo $v['SiteID']; ?>"><?php echo substr($v['SiteCode'], strlen(BaseProcess::$sitecode)); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label id="lblsitename"></label>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="button" value="Search" id="btnsearch"/>
        </div>
        <div align="center" id="pagination">
          <table id="tuhistory">
          </table>
          <div id="pager2"></div>
        </div>
        <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1200px;">
            <br />
            <input type="button" id="btnpdf" value="Export to PDF File" style="float: right;"/>
            <input type="button" id="btnexcel" value="Export to Excel File" style="float: right;"/>
        </div>
    </div>
</form>    
<script type="text/javascript" src="jscripts/topup_date_validation.js" ></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#selSiteCode').change(function(){
            var pegs = $('#selSiteCode > option:selected').attr('label');
            $('#lblsitename').html(pegs);
        });
        
        jQuery('#btnpdf').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=topuphistorypdf');
            jQuery('#frmexport').submit();            
        });
        
        jQuery('#btnexcel').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=topuphistoryexcel');
            jQuery('#frmexport').submit();  
        });
        
        jQuery("#tuhistory").jqGrid({
            url : 'process/ProcessTopUpPaginate.php?action=gettopuphistory',
            datatype: "json",
            colNames:['Site / PEGS Name', 'Site / PEGS Code', 'POS Account','Start Balance', 'End Balance', 'Min Balance', 'Max Balance','Top-up Count', 'Top-up Amount', 'Total Top-up Amount' ,'Transaction Date' ,'Top-up Type'],
            rowNum:10,
            height: 280,
            width: 1200,
            rowList:[10,20,30],
            pager: '#pager2',
            viewrecords: true,
            sortorder: "asc",
            caption:"Top-up History",
            colModel:[
                {name:'SiteName',index:'SiteName',align:'left'},
                {name:'SiteCode',index:'SiteCode',align:'left'},
                {name:'POSAccountNo',index:'POSAccountNo', align:'center'},
                {name:'StartBalance',index:'StartBalance',align:'right'},
                {name:'EndBalance',index:'EndBalance',align:'right'},
                {name:'MinBalance',index:'MinBalance',align:'right'},
                {name:'MaxBalance',index:'MaxBalance',align:'right'},
                {name:'TopupCount',index:'TopupCount',align:'left'},
                {name:'TopupAmount',index:'TopupAmount',align:'right'},
                {name:'TotalTopupAmount',index:'TotalTopupAmount',align:'right'},
                {name:'DateCreated',index:'DateCreated',align:'left'},
                {name:'TopupType',index:'TopupType',align:'left'}
            ],     
            resizable:true
        });
        
        jQuery('#btnsearch').click(function() {
            if(!validateDateTopup()) {
              return false;
            }            
            var startdate = jQuery('#startdate').val();
            //var enddate = jQuery('#enddate').val();
            var site_code = $('#selSiteCode').val();
            var type = jQuery('#seltopuptype option:selected').val();
            jQuery("#tuhistory").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=gettopuphistory&startdate="+startdate+
                "&type="+type+"&site_code="+site_code,page:1}).trigger("reloadGrid");             
        });
        
//        jQuery('#seltopuptype').change(function(){
//            var startdate = jQuery('#startdate').val();
//            var enddate = jQuery('#enddate').val();
//            var type = jQuery(this).val();
//            jQuery("#tuhistory").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=gettopuphistory&startdate="+startdate+
//                "&enddate="+enddate+"&type="+type,page:1}).trigger("reloadGrid");               
//        });
    });
</script>
<?php  
    }
}
include "footer.php"; ?>