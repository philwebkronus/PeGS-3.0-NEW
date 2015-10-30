<script type="text/javascript">
$(document).ready(function(){
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

}
displayData(transactionHistory);
</script>
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
<table id="tblTransView">
    <thead>
        <tr>
            <th style="width: 300px">Datetime</th>
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
