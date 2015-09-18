<?php 
$pagetitle = "e-SAFE Transaction History Per Site"; 
include "header.php";
$vaccesspages = array('5','6','9','12','18');
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
        <table>
             <tr>
                <td>Site / PEGS</td>
                <td>
                    <select name="selsitecode" id="selsitecode">
                        <option value="0">Select Site</option>
                        <?php foreach($param['sites'] as $site): ?>
                         <option label="<?php echo $site['SiteName']." / ".$site['POSAccountNo']; ?>" value="<?php echo $site['SiteID'] ?>"><?php echo substr($site['SiteCode'], strlen(BaseProcess::$sitecode)); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label id="lblsitename"></label>
                </td>
            </tr>
            <tr>
                <td>Transaction Status</td>
                <td>
                    <select name="transacStatus" id="transacStatus">
                       <option value="All">All</option>
                        <option value="0">Pending</option>
                         <option value="1">Successful</option>
                          <option value="2">Failed</option>
                           <option value="3">Fulfillment Approved</option>
                            <option value="4">Fulfillment Denied</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Transaction Type</td>
                <td>
                    <select name="transacType" id="transacType">
                       <option value="All">All</option>
                        <option value="D">Deposit</option>
                         <option value="W">Withdraw</option> 
                    </select>
                </td>
            </tr>
        </table>
            <table>
               
            <tr>
                 <td>Transaction Date</td>
                    <td>

                     <input name="txtDate1" id="popupDatepicker1" readonly value="<?php echo date('Y-m-d')?>"/>
                     <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
                    </td>
<!--                    <td>
                    To:
                    <input name="txtDate2" id="popupDatepicker2" readonly value="<?php echo date ( 'Y-m-d'); ?>"/>
                    <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate2', false, 'ymd', '-');"/>
                    </td>-->
            </tr>
        </table>
       <div id="submitarea">
            <input type="button" value="Search" id="btnsearch"/>
        </div>
        <br />
        <div align="center" id="pagination">
            <table id="ewalletThistory">
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
        jQuery('#selsitecode').change(function(){
            jQuery('#lblsitename').html(jQuery(this).children('option:selected').attr('label'));
        });
        
        jQuery('#btnpdf').click(function(){
            var site = jQuery("#selsitecode").val();
            var transStatus = jQuery("#transacStatus").val();
            var transType = jQuery("#transacType").val();
            var startdate = jQuery("#popupDatepicker1").val();
            //var enddate = jQuery("#popupDatepicker2").val();
            
            jQuery('#frmexport').attr('action',"process/ProcessTopUpGenerateReports.php?action=ewalletTransactionsitehistoryPDF&cmbsite="+site+
                                    "&cmbtransStatus="+transStatus+"&cmbtransType="+transType+"&dateFrom="+startdate);
            jQuery('#frmexport').submit();                 
        });
        
        jQuery('#btnexcel').click(function(){
            var site = jQuery("#selsitecode").val();
            var transStatus = jQuery("#transacStatus").val();
            var transType = jQuery("#transacType").val();
            var startdate = jQuery("#popupDatepicker1").val();
            //var enddate = jQuery("#popupDatepicker2").val();
            
            jQuery('#frmexport').attr('action',"process/ProcessTopUpGenerateReports.php?action=ewalletTransactionsitehistoryExcel&cmbsite="+site+
                                    "&cmbtransStatus="+transStatus+"&cmbtransType="+transType+"&dateFrom="+startdate);
            jQuery('#frmexport').submit();
        });
        
        jQuery("#ewalletThistory").jqGrid({
            url : 'process/ProcessTopUpPaginate.php?action=getewalletsitehistory',
            datatype: "json",
            colNames:['Card Number','Start Date','End Date', 'Amount', 'Transaction Type', 'Status','Created By'],
            rowNum:10,
            height: 280,
            width: 1200,
            rowList:[10,20,30],
            pager: '#pager2',
            viewrecords: true,
            sortorder: "asc",
            caption: "e-SAFE Transaction History",
            colModel:[
                {name:'LoyaltyCardNumber',index:'LoyaltyCardNumber',align: 'center', width:150},
                                    {name:'StartDate',index:'StartDate', align: 'center', width:185},
                                    {name:'EndDate',index:'EndDate', align: 'left',width:185},
                                    {name:'Amount',index:'Amount', align: 'right', width:100},
                                    {name:'TransType',index:'TransType', align: 'center', width:150},
                                    {name:'Status',index:'Status', align: 'center', width:150},
                                    {name:'Name',index:'Name', align: 'center', width:100}
            ],     
            resizable:true
        });
        
        jQuery('#btnsearch').click(function() {
            
            var site = jQuery("#selsitecode").val();
            var transStatus = jQuery("#transacStatus").val();
            var transType = jQuery("#transacType").val();
            var startdate = jQuery("#popupDatepicker1").val();
            var enddate = jQuery("#popupDatepicker2").val();
            
            
            if(site==0){
                
                alert("Please Select a Site");
                return false;
            }
            
            
//            if(new Date(startdate) > new Date(enddate)){
//                
//                alert('Start date must not be greater than end date');
//                return false;
//            }
//            if(new Date(enddate) > new Date()){
//                alert('Queried date must not be greater than today');
//                return false;
//            }
        
            
            jQuery("#ewalletThistory").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=getewalletsitehistory&cmbsite="+site+
                                    "&cmbtransStatus="+transStatus+"&cmbtransType="+transType+"&dateFrom="+startdate,page:1}).trigger('reloadGrid');
  
           jQuery("html, body").animate({ scrollTop: "500px" });
                                                 
                                
        });  
        
        
    });
</script>
<?php  
    }
}
include "footer.php"; ?>