<?php 
$pagetitle = "e-wallet Transaction History Per Membership Card"; 
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
                <td>Card Number</td>
                <td>
                    <input type='text' id='cardnum' name='cardnum' maxlength="30" size='30'/>
                </td>
                <td>Membership | Temporary</td>
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
                 <td>Date Range</td>
                    <td>
                    From: 
                     <input name="txtDate1" id="popupDatepicker1" readonly value="<?php echo date('Y-m-d')?>"/>
                     <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
                    </td>
                    <td>
                    To:
                    <input name="txtDate2" id="popupDatepicker2" readonly value="<?php echo date ( 'Y-m-d'); ?>"/>
                    <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate2', false, 'ymd', '-');"/>
                    </td>
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
        
        var msg="";
        jQuery("#cardnum").change(function(){
            
           var cardNumber = jQuery("#cardnum").val();
           if(cardNumber){
           $.get('process/ProcessTopUpPaginate.php?action=getCardNumberStatus&cardNum='+cardNumber,{},function(data){
               
             if(data){
              if(data==0){
                  alert("Inactive Card Number");
                  jQuery("#btnsearch").attr('disabled',true);
              }
              else if(data==2){
                  alert("Deactivated Card Number");
                  jQuery("#btnsearch").attr('disabled',true);
              }
              else if(data==7){
                  alert("Newly Migrated Card Number");
                  jQuery("#btnsearch").attr('disabled',true);
              }
              else if(data==8){
                  alert("Temporary Migrated Card Number");
                  jQuery("#btnsearch").attr('disabled',true);
              }
              else{
                  
                  jQuery("#btnsearch").removeAttr('disabled');
                  
              }
          }else{
              alert("Invalid Card Number");
              jQuery("#btnsearch").attr('disabled',true);
          }
              
           });
       }
       
           
        });
        
        
        
        jQuery('#btnpdf').click(function(){
            var cardNumber = jQuery("#cardnum").val();
            var transStatus = jQuery("#transacStatus").val();
            var transType = jQuery("#transacType").val();
            var startdate = jQuery("#popupDatepicker1").val();
            var enddate = jQuery("#popupDatepicker2").val();
            
            jQuery('#frmexport').attr('action',"process/ProcessTopUpGenerateReports.php?action=ewalletTransactioncardhistoryPDF&cardNum="+cardNumber+
                                    "&cmbtransStatus="+transStatus+"&cmbtransType="+transType+"&dateFrom="+startdate+
                             "&dateTo="+enddate);
            jQuery('#frmexport').submit();                 
        });
        
        jQuery('#btnexcel').click(function(){
            var cardNumber = jQuery("#cardnum").val();
            var transStatus = jQuery("#transacStatus").val();
            var transType = jQuery("#transacType").val();
            var startdate = jQuery("#popupDatepicker1").val();
            var enddate = jQuery("#popupDatepicker2").val();
            
            jQuery('#frmexport').attr('action',"process/ProcessTopUpGenerateReports.php?action=ewalletTransactioncardhistoryExcel&cardNum="+cardNumber+
                                    "&cmbtransStatus="+transStatus+"&cmbtransType="+transType+"&dateFrom="+startdate+
                             "&dateTo="+enddate);
            jQuery('#frmexport').submit();
        });
        
        jQuery("#ewalletThistory").jqGrid({
            url : 'process/ProcessTopUpPaginate.php?action=getewalletcardhistory',
            datatype: "json",
            colNames:['Card Number','Start Date','End Date', 'Amount', 'Transaction Type', 'Status','Created By'],
            rowNum:10,
            height: 280,
            width: 1200,
            rowList:[10,20,30],
            pager: '#pager2',
            viewrecords: true,
            sortorder: "asc",
            caption: "e-wallet Transaction History",
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
            
            var cardNumber = jQuery("#cardnum").val();
            var transStatus = jQuery("#transacStatus").val();
            var transType = jQuery("#transacType").val();
            var startdate = jQuery("#popupDatepicker1").val();
            var enddate = jQuery("#popupDatepicker2").val();
            
            
            
            if(cardNumber==""){
                
                alert("Please Enter a Card Number");
                return false;
            }
            
            if(new Date(startdate) > new Date(enddate)){
                
                alert('Start date must not greater than end date');
                return false;
            }
            if(new Date(enddate) > new Date()){
                alert('Queried date must not be greater than today');
                return false;
            }
            
            
            jQuery("#ewalletThistory").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=getewalletcardhistory&cardNum="+cardNumber+
                                    "&cmbtransStatus="+transStatus+"&cmbtransType="+transType+"&dateFrom="+startdate+
                             "&dateTo="+enddate,page:1}).trigger('reloadGrid');
  
           jQuery("html, body").animate({ scrollTop: "500px" });
                                                 
                                
        });  
        
        
    });
</script>
<?php  
    }
}
include "footer.php"; ?>