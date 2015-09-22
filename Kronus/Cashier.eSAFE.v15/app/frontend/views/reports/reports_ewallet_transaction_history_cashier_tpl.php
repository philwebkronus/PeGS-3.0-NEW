<script type="text/javascript">
$(document).ready(function(){
    $('#ReportsFormModel_date').datepicker({
        inline: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        maxDate : '<?php echo date('Y-m-d') ?>'
    });
})
</script>
<h1>e-SAFE Transaction History Per Cashier</h1>
<form id="frmtranshist">
    <?php // this hidden field is just indicator for javascript because it also use javascript of per history ?>
<!--    <input type="hidden" id="hidpercashierindicator" />-->
    <b><?php echo MI_HTML::label($reportsFormModel, 'date', 'Select Date'); ?></b>
    <?php echo MI_HTML::inputText($reportsFormModel, 'date',array('readonly'=>'readonly','value'=>date('Y-m-d'))); ?>
    <input type="button" value="Query" id="btnEWalletPerCashier" />
    <div>
        <table style="width: 100%">
            <tr>
<!--                <td><h3 id="coverage">Coverage <?php //echo date('l , F d, Y ') ?> 06:00:01 AM to <?php //echo date('l , F d, Y ',mktime(0,0,0,date('m'),date('d') + 1, date('Y'))) ?>06:00:00 AM</h3></td>-->
                <td><h3 id="coverage"><?php echo $coverage; ?></h3></td>
            </tr>
        </table>
    </div>
</form>
<div class="clear"></div>

<div style="height:550px;
                    overflow:auto;">
<table id="tbltranshistory" border="1">
   <thead>
        <th style=" width: 25%">Card Number</th>
        <th style=" width: 25%">Date</th>
        <th style=" width: 25%">Amount</th> 
        <th style=" width: 25%">Transaction Type</th>
   </thead>
   <tbody id="tbltranshistorybody">
      <?php 
        $transaction = array(''=>'','D'=>'Deposit', 'W'=>'Withdraw');
        foreach($data as $key=>$value){ 
           
           $cardNumber = $value['LoyaltyCardNumber'];
           $date = $value['StartDate'];
           $amount = $value['Amount'];
           $transType = trim($value['TransType']);
           $transactionType = $transaction[$transType];
           if($transType=='D'){
               $totalDeposit+=$amount;
           }else if($transType=='W'){
               $totalWithdrawal+=$amount;
           }
       ?>
       <tr>
           <td style="text-align: center;"><?php echo $cardNumber;?></td>
           <td style="text-align: center;"><?php echo date('Y-m-d h:i:s A',  strtotime($date));?></td>
           <td style="text-align: right;"><?php echo number_format($amount,2);?></td>
           <td style="text-align: right;"><?php echo $transactionType?></td>
       </tr>
       <?php }?>
      <tr style="height:30px;">
           <td colspan="4"></td>
       </tr>
       <tr>
           <td><b>Total Deposits</b></td>
           <td colspan="3"><?php echo 'PhP '.toMoney($totalDeposit); ?></td>
       </tr>
       <tr>
           <td><b>Total Withdrawals</b></td>
           <td colspan="3"><?php echo 'PhP '.toMoney($totalWithdrawal); ?></td>
       </tr>
       <tr>
           <td><b>Cash on Hand</b></td>
           <td colspan="3"><?php echo 'PhP '.toMoney($cashOnHand); ?></td>
       </tr>
   </tbody>
</table>
    </div>
   
<form method="post" id="frmtranshistory" action="<?php echo Mirage::app()->createUrl('pdf/transactionhistorycashier'); ?>">
    <input type="hidden" id="hidselected_date" name="hidselected_date" />
<!--    <input id="btn_submit" type="button" value="Genereate PDF" />-->
</form>