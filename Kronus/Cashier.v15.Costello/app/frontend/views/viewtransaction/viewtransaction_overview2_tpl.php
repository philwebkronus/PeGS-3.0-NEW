<script type="text/javascript">
if ($('#siteamountinfo').val() == 0){
             showLightbox(function(){
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );   
            });
}
$(document).ready(function(){
    $('#txtDate').datepicker({
        inline: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        maxDate : '<?php echo date('Y-m-d') ?>'
    });
    
    $('#txtDate').change(function(){
        var url = $('#transhistorypage').attr('url');
        var limit = $('#transhistorypage option:selected').val();
        var d = $('#txtDate').val();
        var data = 'limit='+limit+'&date='+d;
        showLightbox(function(){
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
        });
    });
});
var transactionHistory = <?php echo $transactionHistory; ?>; 


function displayData(data){
    var html = '';
    for(i=0;i < data.trans_details.length;i++) {
        html+='<tr>';
        var datetime = data.trans_details[i].DateCreated;
        datetime = datetime.split('.');
        html+='<td>'+formatDateAMPM(datetime[0])+'</td>';
        var terminal_name = data.trans_details[i].TerminalCode;

        terminal_name = terminal_name.replace(data.site_code,'');
        if(data.trans_details[i].TerminalType == 1){
            terminal_name = 'G'+terminal_name;
        }

        html+='<td>'+terminal_name+'</td>';
        html+='<td>'+getTransType(data.trans_details[i].TransactionType)+'</td>';
        html+='<td class="right">'+toMoney(data.trans_details[i].Amount)+'</td>';
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
<h1>Transaction History Per Virtual Cashier</h1>
<input id="txtDate" type="text" readonly="readonly" value="<?php echo date('Y-m-d'); ?>" />
<label>View By Last</label>
<select id="transhistorypage" url ="<?php echo Mirage::app()->createUrl('viewtranspervc'); ?>">
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
            <th style="width: 200px">Transaction Type</th>
            <th style="width: 200px">Amount</th>
        </tr>
    </thead>
    <tbody id="tbodyviewtrans">
        <tr></tr>
    </tbody>
</table>


