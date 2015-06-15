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
<h1>Transaction History Per Cashier</h1>
<form id="frmtranshist">
    <?php // this hidden field is just indicator for javascript because it also use javascript of per history ?>
<!--    <input type="hidden" id="hidpercashierindicator" />-->
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
          <th colspan="3" style=" width: 40%">Session</th>
          <th colspan="3" style=" width: 12%">Deposit</th>
          <th colspan="3" style=" width: 12%">Reload</th> 
          <th colspan="2" style=" width: 16%">Redemption</th>
          <th style=" width: 10%">Grosshold</th>
   </thead>
   <tbody id="tbltranshistorybody">
      <?php if($rows): ?>
       <tr>
       <th style=" width: 12%">Terminal#</th>
       <th style=" width: 14%">Login</th>
       <th style=" width: 14%">Logout</th>
       
       <th>Cash</th>
       <th>Ticket</th>
       <th>Coupon</th>
       
       <th>Cash</th>
       <th>Ticket</th>
       <th>Coupon</th>
       
       <th>Cashier</th>
       <th>Genesis</th>
       
       <th></th>
     </tr>
     <tr><td>&nbsp;&nbsp;&nbsp;</td></tr>   
     <tr>
     <?php foreach($rows as $r): ?>
       <?php //debug($r); exit; ?>
      <tr>
          <?php //$suffix = (($r['isVIP'])?'vip':''); ?>
         <td align="center"><?php echo $r['TerminalCode']; ?></td>
         <td align="center">
            <?php $date_started = explode('.', $r['DateStarted']); ?>
            <?php echo date('Y-m-d h:i:s A',  strtotime($date_started[0])); ?>
         </td>
         <td align="center">
            <?php $date_ended = explode('.', $r['DateEnded']); ?>
            <?php echo (($date_ended[0])?date('Y-m-d h:i:s A',  strtotime($date_ended[0])):'Still playing ...'); ?>
         </td>
         <?php 
            $depositcash += $r['DCash'];
            $depositticket += $r['DTicket'];
            $depositcoupon += $r['DCoupon'];
            
            $reloadcash += $r['RCash'];
            $reloadticket += $r['RTicket'];
            $reloadcoupon += $r['RCoupon'];
            
            
            $withdrawalcashier += $r['WCashier'];
            $withdrawalgenesis += $r['WGenesis'];
            
            $grosshold = (($r['DCash'] + $r['DTicket'] + $r['DCoupon'])+($r['RCash'] + $r['RTicket'] + $r['RCoupon']))-($r['WCashier']+$r['WGenesis']);
            $grossholdtotal += $grosshold;
         ?>
         <td class="right"><?php echo number_format($r['DCash'],2) ?></td>
         <td class="right"><?php echo number_format($r['DTicket'],2) ?></td>
         <td class="right"><?php echo number_format($r['DCoupon'],2); ?></td>
         
         <td class="right"><?php echo number_format($r['RCash'],2) ?></td>
         <td class="right"><?php echo number_format($r['RTicket'],2) ?></td>
         <td class="right"><?php echo number_format($r['RCoupon'],2); ?></td>
         
         <td class="right"><?php echo number_format($r['WCashier'],2) ?></td>
         <td class="right"><?php echo number_format($r['WGenesis'],2); ?></td>
         
         <td class="right"><?php echo number_format($grosshold,2) ?></td>
              
      </tr>
      <?php endforeach; ?>
      <?php 
       /*--- TOTAL ---*/
       
       $totaldeposit = $depositcash + $depositticket + $depositcoupon;
       $totalreload = $reloadcash + $reloadticket + $reloadcoupon;
       $totalwithdraw = $withdrawalcashier + $withdrawalgenesis;
       
       $totalgrosshold = $grossholdtotal;
       
       $subtotaldcash = $depositcash + $reloadcash;
       
       $subtotalrcash = $depositticket + $reloadticket;
       
       /*--- SALES ---*/
       
       $totalcash = $subtotaldcash;
       $totaltickets = $subtotalrcash;
       $totalcoupons = $depositcoupon + $reloadcoupon;
       
       $totalsales = $totalcash + $totaltickets + $totalcoupons;
       $cashonhand = $totalcash-($withdrawalcashier + $ticketlist[0]['EncashedTickets']) + ($eWalletDeposits - $eWalletWithdrawals);
      ?>
     </tr>
     <tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>
      <tr>
          <td></td>
          <th align="center" style="background-color:#BCBCBA">Subtotal</th>
          <td style="background-color:#BCBCBA"></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($depositcash,2);?></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($depositticket,2);?></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($depositcoupon,2);?></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($reloadcash,2);?></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($reloadticket,2);?></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($reloadcoupon,2);?></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($withdrawalcashier,2);?></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($withdrawalgenesis,2);?></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($grossholdtotal,2);?></td>
      </tr>
      <tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>
      <tr>
          <td></td>
          <th style="background-color:#BCBCBA">Total</th>
          <td style="background-color:#BCBCBA"></td>
          <td align="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totaldeposit,2);?></td>
          <td align="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totalreload,2);?></td>
          <td align="right" colspan="2" style="background-color:#BCBCBA"><?php echo number_format($totalwithdraw,2);?></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($totalgrosshold,2);?></td>
      </tr>
      <tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>   
      <tr>
          <th colspan="1" align="left">Sales</th>
          <td colspan="1" >&nbsp;&nbsp;&nbsp;</td>
      </tr>
      <tr>
          <th colspan="1" align="center">Cash</th>
          <td colspan="1" align="right"><?php echo number_format($totalcash,2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="center">Tickets</th>
          <td colspan="1" align="right"><?php echo number_format($totaltickets,2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="center">Coupons</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($totalcoupons,2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="center">e-SAFE Deposits</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($eWalletDeposits,2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="left">Total Sales</th>
          <td colspan="1" align="right"><?php echo number_format($totalsales,2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="left">&nbsp;&nbsp;</th>
          <td colspan="1" >&nbsp;&nbsp;&nbsp;</td>
      </tr>
      <tr>
          <th colspan="1" align="left">Total Encashed Tickets</th>
          <td colspan="1" align="right"><?php echo number_format($ticketlist[0]['EncashedTickets'],2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="left">&nbsp;&nbsp;&nbsp;</th>
          <td colspan="1" >&nbsp;&nbsp;&nbsp;</td>
      </tr>
   
      <tr>
          <th colspan="1" align="left">Cash on Hand</th>
          <td colspan="1" align="right"><?php echo number_format($cashonhand,2);?></td>
      </tr>
      <?php endif; ?>
   </tbody>
</table>
    </div>
   
<form method="post" id="frmtranshistory" action="<?php echo Mirage::app()->createUrl('pdf/transactionhistorycashier'); ?>">
    <input type="hidden" id="hidselected_date" name="hidselected_date" />
<!--    <input id="btn_submit" type="button" value="Genereate PDF" />-->
</form>