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
<h1>Transaction History Per Site</h1>
<form id="frmtranshist">
    <?php echo MI_HTML::label($reportsFormModel, 'date', 'Select Date'); ?>
    <?php echo MI_HTML::inputText($reportsFormModel, 'date',array('readonly'=>'readonly','value'=>date('Y-m-d'))); ?>
    <input type="button" value="Query" id="btntranshist" />
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
      $res = $total_rows;
      foreach ($res as $r) {
            $regdepositcash += $r['RegDCash'];
            $regdepositticket += $r['RegDTicket'];
            $regdepositcoupon += $r['RegDCoupon'];
            
            $regreloadcash += $r['RegRCash'];
            $regreloadticket += $r['RegRTicket'];
            $regreloadcoupon += $r['RegRCoupon'];
            
            $gendepositcash += $r['GenDCash'];
            $gendepositticket += $r['GenDTicket'];
            $gendepositcoupon += $r['GenDCoupon'];
            
            $genreloadcash += $r['GenRCash'];
            $genreloadticket += $r['GenRTicket'];
            $genreloadcoupon += $r['GenRCoupon'];
            
            
            $withdrawalcashier2 += $r['WCashier'];
            $withdrawalgenesis2 += $r['WGenesis'];
        }
        
       $grosholdregular = (($regdepositcash + $regdepositticket + $regdepositcoupon)+($regreloadcash + $regreloadticket + $regreloadcoupon))-($withdrawalcashier2);
       $grosholdgenesis = (($gendepositcash + $gendepositticket + $gendepositcoupon)+($genreloadcash + $genreloadticket + $genreloadcoupon))-($withdrawalgenesis2);
       
       /*--- SUBTOTAL ---*/
       $subtotaldcash = $regdepositcash + $gendepositcash;
       $subtotaldticket = $regdepositticket + $gendepositticket;
       $subtotaldcoupon = $regdepositcoupon + $gendepositcoupon;
       
       $subtotalrcash = $regreloadcash + $genreloadcash;
       $subtotalrticket = $regreloadticket + $genreloadticket;
       $subtotalrcoupon = $regreloadcoupon + $genreloadcoupon;
       
       $subtotalgrosshold = $grosholdregular + $grosholdgenesis;
       
       
       /*--- TOTAL ---*/
       
       $totaldeposit = $subtotaldcash + $subtotaldticket + $subtotaldcoupon;
       $totalreload = $subtotalrcash + $subtotalrticket + $subtotalrcoupon;
       $totalwithdraw = $withdrawalcashier2 + $withdrawalgenesis2;
       
       $totalgrosshold = $subtotalgrosshold;
       
       /*--- SALES ---*/
       
       $totalcash = $subtotaldcash + $subtotalrcash;
       $totaltickets = $subtotaldticket + $subtotalrticket;
       $totalcoupons = $subtotaldcoupon + $subtotalrcoupon;
       
       $totalsales = $totalcash + $totaltickets + $totalcoupons;
      ?>
     
     <tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>
     
      <tr>
          <td></td>
          <th rowspan="3" style="background-color:#BCBCBA">Breakdown</th>
          <th align="center" style="background-color:#BCBCBA">Regular</th>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($regdepositcash,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($regdepositticket,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($regdepositcoupon,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($regreloadcash,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($regreloadticket,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($regreloadcoupon,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($withdrawalcashier2,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format(0,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($grosholdregular,2);?></td>
      </tr>
      <tr>
          <td></td>
          <th align="center" style="background-color:#BCBCBA">Genesis</th>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($gendepositcash,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($gendepositticket,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($gendepositcoupon,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($genreloadcash,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($genreloadticket,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($genreloadcoupon,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format(0,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($withdrawalgenesis2,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($grosholdgenesis,2);?></td>
      </tr>
      <tr>
          <td></td>
          <th align="center" style="background-color:#BCBCBA">Subtotal</th>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($subtotaldcash,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($subtotaldticket,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($subtotaldcoupon,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($subtotalrcash,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($subtotalrticket,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($subtotalrcoupon,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($withdrawalcashier2,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($withdrawalgenesis2,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($subtotalgrosshold,2);?></td>
      </tr>
      <tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>
      <tr>
          <td></td>
          <th style="background-color:#BCBCBA">Total</th>
          <td style="background-color:#BCBCBA"></td>
          <td class="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totaldeposit,2);?></td>
          <td class="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totalreload,2);?></td>
          <td class="right" colspan="2" style="background-color:#BCBCBA"><?php echo number_format($totalwithdraw,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($totalgrosshold,2);?></td>
      </tr>
      <tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>   
      <tr>
          <th colspan="1" align="left">Sales</th>
          <td colspan="1">&nbsp;&nbsp;&nbsp;</td>
      </tr>
      <tr>
          <th colspan="1" align="center">Cash</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($totalcash,2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="center">Tickets</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($totaltickets,2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="center">Coupons</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($totalcoupons,2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="left">Total Sales</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($totalsales,2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="left">&nbsp;&nbsp;</th>
          <td colspan="1" >&nbsp;&nbsp;&nbsp;</td>
      </tr>
      <tr>
          <th colspan="1" align="left">Total Printed Tickets</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($ticketlist[0]['PrintedRedemptionTickets'],2);?></td>
          <td style="border:0;">* Through Redemption</td>
      </tr>
      <tr>
          <th colspan="1" align="left">Total Active Tickets For The Day</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($ticketlist[0]['UnusedTickets'],2);?></td>
      </tr>
<!--      <tr>
          <th colspan="1" align="left">Total Cancelled Tickets</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($ticketlist[0]['CancelledTickets'],2);?></td>
      </tr>-->
      <tr>
          <th colspan="1" align="left">Total Encashed Tickets</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($ticketlist[0]['EncashedTickets'],2);?></td>
      </tr>
      <tr>
          <th colspan="1" align="left">Total Active Running Tickets</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($runningactivetickets,2);?></td>
      </tr>
      <?php
      $cashonhand = $totalcash - ($withdrawalcashier2 + $manualredemptions + $ticketlist[0]['EncashedTickets']);
      ?>
      <tr>
          <th colspan="1" align="left">&nbsp;&nbsp;&nbsp;</th>
          <td colspan="1" >&nbsp;&nbsp;&nbsp;</td>
      </tr>
      <tr>
          <th colspan="1" align="left">Cash on Hand</th>
          <td class="right" colspan="1" align="center"><?php echo number_format($cashonhand,2);?></td>
      </tr>
      <?php endif; ?>
   </tbody>
</table>
    </div>
<br/>
<form method="post" id="frmtranshistory" action="<?php echo Mirage::app()->createUrl('pdf/transactionhistory'); ?>">
    <input type="hidden" id="hidselected_date" name="hidselected_date" />
<!--    <input id="btn_submit" type="button" value="Genereate PDF" />-->
</form>