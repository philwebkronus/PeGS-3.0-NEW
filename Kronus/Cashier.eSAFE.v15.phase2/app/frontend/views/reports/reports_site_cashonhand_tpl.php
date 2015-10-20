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
<h1>Site Cash On Hand</h1>
<form id="frmtranshist">
    <?php // this hidden field is just indicator for javascript because it also use javascript of per history ?>
    <b><?php echo MI_HTML::label($reportsFormModel, 'date', 'Select Date'); ?></b>
    <?php echo MI_HTML::inputText($reportsFormModel, 'date',array('readonly'=>'readonly','value'=>date('Y-m-d'))); ?>
    <input type="button" value="Query" id="btntranshistpercash" />
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
          <th style=" width: 15%">Casino</th>
          <th colspan="2" style=" width: 35%">Deposit</th>
          <th colspan="2" style=" width: 35%">Withdrawal</th> 
          <th style=" width: 15%">Cash On Hand</th>
   </thead>
   <tbody id="tbltranshistorybody">
      <?php if($rows): ?>
       <tr>
       <th></th>
       <th style=" width: 20%"></th>
       <th style=" width: 15%"></th>
       <th style=" width: 20%"></th>
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
     <?php foreach($rows as $r) {
            $rsubdtotal = 0;
            $rsubwtotal = 0;
            $rcashonhand = 0;

            $rsubdtotal += ($r['LoadCash'] + $r['LoadCoupon'] + $r['LoadBancnet'] + $r['LoadTicket']);
            $rsubwtotal += ($r['WCash'] + $r['WTicket'] + $r['EncashedTicket']);
            $rcashonhand = $rsubdtotal - $rsubwtotal;
            
            $totaldcash += $r['LoadCash'];
            $totaldcoupon += $r['LoadCoupon'];
            $totaldbancnet += $r['LoadBancnet'];
            $totaldticket += $r['LoadTicket'];
            $totalwcash += $r['WCash'];
            $totalwticket += $r['WTicket'];
            $totalwencashedtickets += $r['EncashedTicket'];
            $grandtotalcashonhand += $rcashonhand;
        } ?>
        <tr id="space-bar-td"><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><th rowspan="5" align="center"><b>Total</b></th>
               <th>Cash</th>
               <td><?php echo number_format($totaldcash,2); ?></td>
               <th>Cash</th>
               <td><?php echo number_format($totalwcash,2); ?></td>
               <td></td>
            </tr>
            <tr>
               <th>Coupon</th>
               <td><?php echo number_format($totaldcoupon,2); ?></td>
               <th>Ticket</th>
               <td><?php echo number_format($totalwticket,2); ?></td>
               <td></td>
            </tr>
            <tr>
               <th>Bancnet</th>
               <td><?php echo number_format($totaldbancnet,2); ?></td>
               <th>Encashed Tickets</th>
               <td><?php echo number_format($totalwencashedtickets,2); ?></td>
               <td></td>
            </tr>
            <tr>
               <th>Ticket</th>
               <td><?php echo number_format($totaldticket,2); ?></td>
               <th></th>
               <td></td>
               <td></td>
            </tr>
            <?php
                $grandtotaldeposit += ($totaldcash + $totaldcoupon + $totaldbancnet + $totaldticket);
                $grandtotalwithdraw += ($totalwcash + $totalwticket + $totalwencashedtickets);
            ?>
            <tr>
               <th><b>Grand Total</b></th>
               <td><?php echo number_format($grandtotaldeposit,2); ?></td>
               <th><b>Grand Total</b></th>
               <td><?php echo number_format($grandtotalwithdraw,2); ?></td>
               <td><?php echo number_format($grandtotalcashonhand,2); ?></td>
            </tr>
      <?php endif; ?>
   </tbody>
</table>
    </div>