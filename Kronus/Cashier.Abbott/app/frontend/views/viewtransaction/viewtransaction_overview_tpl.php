<link type="text/css" href="css/le-frog/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
<script type="text/javascript" src="jscripts/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="jscripts/viewtransaction.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#txtDate').datepicker({
        inline: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        maxDate : '<?php echo date('Y-m-d') ?>'
    });        
});
</script>
<style>
    #tblTransView thead tr{background-color: #bcbcba} 
    #ishover{background-color: #eee}
    .isClick{background-color: #eee}
    #tblTransView tbody tr{cursor: pointer}
</style>
<h1>Transaction History</h1>
<input id="txtDate" type="text" readonly="readonly" value="<?php echo date('Y-m-d'); ?>" />
<label>View By Last</label>
<select id="transhistorypage" url ="<?php echo Mirage::app()->createUrl('viewtrans'); ?>">
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
            <th style="width: 200px">Transaction Type</th>
            <th style="width: 200px">Amount</th>
        </tr>
    </thead>
    <tbody id="tbodyviewtrans">
        <tr></tr>
    </tbody>
</table>