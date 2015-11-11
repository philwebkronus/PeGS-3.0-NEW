<?php $eSAFEStartDate = Mirage::app()->param['eSAFEStartDate']; ?>
<script type="text/javascript">
$(document).ready(function(){
    $('#ReportsFormModel_date').datepicker({
        inline: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        maxDate : '<?php echo date('Y-m-d'); ?>',
        minDate : '<?php echo date("Y-m-d", strtotime($eSAFEStartDate)); ?>'
    });
})
</script>
<h1>Site Cash On Hand</h1>
<form id="frmtranshist">
    <?php // this hidden field is just indicator for javascript because it also use javascript of per history ?>
    <b><?php echo MI_HTML::label($reportsFormModel, 'date', 'Select Date'); ?></b>
    <?php echo MI_HTML::inputText($reportsFormModel, 'date',array('readonly'=>'readonly','value'=>date('Y-m-d'))); ?>
    <input type="button" value="Query" id="btncashonhandsumm" />
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
                    overflow:auto; width: 1000px;">
<table id="tbltranshistory" border="1">
   <thead>
          <th style=" width: 10%"></th>
          <th colspan="2" style=" width: 30%">Deposit</th>
          <th colspan="2" style=" width: 30%">Withdrawal</th> 
          <th style=" width: 25%">Cash On Hand</th>
   </thead>
   <tbody id="tbltranshistorybody">
      <?php if($transdetails): ?>
       <tr>
       <th></th>
       <th style=" width: 15%"></th>
       <th style=" width: 15%"></th>
       <th style=" width: 15%"></th>
       <th style=" width: 15%"></th>
       <th></th>
     </tr>
     <?php 
        $totaldcash = 0;
        $totaldcoupon = 0;
        $totaldbancnet = 0;
        $totaldticket = 0;
        $totalwcash = 0;
        $totalwticket = 0;
        $totalwencashedtickets = 0;
        $grandtotaldeposit = 0;
        $grandtotalwithdraw = 0;
        $grandtotalcashonhand = 0;
     ?>
     <?php foreach($transdetails as $r) {
            $rsubdtotal = 0;
            $rsubwtotal = 0;
            $rcashonhand = 0;

            $rsubdtotal += ($r['LoadCash'] + $r['LoadCoupon'] + $r['LoadBancnet'] + $r['LoadTicket']);
            $rsubwtotal += ($r['WCash'] + $r['WTicket'] + $r['EncashedTickets']);
            $rcashonhand = $rsubdtotal - $rsubwtotal;
            
            $totaldcash += $r['LoadCash'];
            $totaldcoupon += $r['LoadCoupon'];
            $totaldbancnet += $r['LoadBancnet'];
            $totaldticket += $r['LoadTicket'];
            $totalwcash += $r['WCash'];
            $totalwticket += $r['WTicket'];
            $totalwencashedtickets += $r['EncashedTickets'];
            $grandtotalcashonhand += $rcashonhand;
        } ?>
            <tr><th rowspan="5" align="center"><b>Total</b></th>
               <th style="text-align:left; padding-left:20px;">Cash</th>
               <td style="text-align:right;"><?php echo number_format($totaldcash,2); ?></td>
               <th style="text-align:left; padding-left:20px;">Cash</th>
               <td style="text-align:right;"><?php echo number_format($totalwcash,2); ?></td>
               <td></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:20px;">Coupon</th>
               <td style="text-align:right;"><?php echo number_format($totaldcoupon,2); ?></td>
               <th style="text-align:left; padding-left:20px;">Ticket</th>
               <td style="text-align:right;"><?php echo number_format($totalwticket,2); ?></td>
               <td></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:20px;">Bancnet</th>
               <td style="text-align:right;"><?php echo number_format($totaldbancnet,2); ?></td>
               <th style="text-align:left; padding-left:20px;">Encashed Tickets</th>
               <td style="text-align:right;"><?php echo number_format($totalwencashedtickets,2); ?></td>
               <td></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:20px;">Ticket</th>
               <td style="text-align:right"><?php echo number_format($totaldticket,2); ?></td>
               <th style="text-align:left; padding-left:20px;"></th>
               <td></td>
               <td></td>
            </tr>
            <?php
                $grandtotaldeposit = ($totaldcash + $totaldcoupon + $totaldbancnet + $totaldticket);
                $grandtotalwithdraw = ($totalwcash + $totalwticket + $totalwencashedtickets);
            ?>
            <tr>
               <th style="text-align:left; padding-left:20px;"><b>Grand Total</b></th>
               <td style="text-align:right"><b><?php echo number_format($grandtotaldeposit,2); ?></b></td>
               <th style="text-align:left; padding-left:20px;"><b>Grand Total</b></th>
               <td style="text-align:right"><b><?php echo number_format($grandtotalwithdraw,2); ?></b></td>
               <td style="text-align:right"><b><?php echo number_format($grandtotalcashonhand,2); ?></b></td>
            </tr>
      <?php endif; ?>
   </tbody>
</table>
    </div>