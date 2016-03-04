<script type="text/javascript">
$(document).ready(function(){
 if ($('#siteamountinfo').val() == 0){
             showLightbox(function(){
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[010]: Site amount is not set.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );   
            });
  }
    $('#txtDate2').datepicker({
        inline: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        maxDate : '<?php echo date('Y-m-d') ?>'
    });
    
    
    $('#txtDate2').change(function(){
        
        var limit = $('#transhistorypage2 option:selected').val();
        var d = $('#txtDate2').val();
        var data = 'limit='+limit+'&date='+d;
        showLightbox(function(){
            queryTrans(data);
        });
    });
    
   $('#transhistorypage2').live('change',function(){
        var limit = $('#transhistorypage2 option:selected').val();
        var d = $('#txtDate2').val();
        var data = 'limit='+limit+'&date='+d;
        showLightbox(function(){
            queryTrans(data);
        });
    });
});
var transactionHistory = <?php echo $transactionHistory; ?>; 

function queryTrans(data){
    var url = '<?php echo Mirage::app()->createUrl('viewtrans/ewalletPerCashier') ?>' ;
    
    $.ajax({
       url:url,
       data:data,
       success:function(data){
           try{
              displayData(JSON.parse(data));
           }catch(e) {
               alert('Oops! Something went wrong. Please try again');
           }
           hideLightbox();
       },
       error:function(e) {
           alert('Oops! Something went wrong. Please try again');
           hideLightbox();
       }
   });
    
}

function displayData(data){
    var html = '';
    for(i=0;i < data.trans_details.length;i++) {
        
        var obj = data.trans_details[i];
        var transType = obj.TransType;
        switch(transType){
            case 'D':
                transType='DEPOSIT';
                break;
            case 'R':
                transType = 'RELOAD';
                break;
            case 'W':
                transType = 'WITHDRAW';
        }
        
        html+='<tr>';
        var datetime = formatDateAMPM(obj.StartDate);
        var tCode = '';
        if(obj.Source == "Genesis"){
            tCode = obj.TerminalCode == null ? '':'G'+obj.TerminalCode;
        } else {
            tCode = obj.TerminalCode == null ? '':obj.TerminalCode;
        }
        html+='<td>'+datetime+'</td>';
        html+='<td>'+tCode+'</td>';
        html+='<td>'+obj.LoyaltyCardNumber+'</td>';
        html+='<td>'+transType+'</td>';
        html+='<td class="right">'+toMoney(obj.Amount)+'</td>';
        html+='</tr>';
    }
    $('#tbodyviewtrans').html(html);
    $('#coverage').html(data.coverage);

}
displayData(transactionHistory);
</script>
<input type="hidden" name="siteamountinfo" id="siteamountinfo" value="<?php echo $siteAmountInfo; ?>" />
<style>
    #tblTransView thead tr{background-color: #bcbcba} 
    #ishover{background-color: #eee}
    .isClick{background-color: #eee}
    #tblTransView tbody tr{cursor: pointer}
</style>
<h1>e-SAFE Transaction History Per Cashier</h1>
<input id="txtDate2" type="text" readonly="readonly" value="<?php echo date('Y-m-d'); ?>" />
<label>View By Last</label>
<select id="transhistorypage2" url ="<?php echo Mirage::app()->createUrl('viewtranspervc'); ?>">
    <option value="10">10</option>
    <option value="20">20</option>
    <option value="30">30</option>
    <option value="40">40</option>
    <option selected="selected">50</option>
</select>
<br/>
<table style="width: 100%">
<tr><td><h3 id="coverage"></h3></td></tr>
</table>
<table id="tblTransView">
    <thead>
        <tr>
            <th style="width: 300px">Date and Time</th>
            <th style="width: 200px">Terminal</th>
            <th style="width: 200px">Card Number</th>
            <th style="width: 200px">Transaction Type</th>
            <th style="width: 200px">Amount</th>
        </tr>
    </thead>
    <tbody id="tbodyviewtrans">
        <tr></tr>
    </tbody>
</table>
