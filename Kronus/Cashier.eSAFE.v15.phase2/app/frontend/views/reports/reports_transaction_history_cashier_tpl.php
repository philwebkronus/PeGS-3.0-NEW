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
    <?php echo MI_HTML::inputHidden($reportsFormModel, 'terminal_id') ?>
    <?php echo MI_HTML::inputHidden($reportsFormModel, 'trans_sum_id') ?>
    <b><?php echo MI_HTML::label($reportsFormModel, 'date', 'Select Date'); ?></b>
    <?php echo MI_HTML::inputText($reportsFormModel, 'date',array('readonly'=>'readonly','value'=>date('Y-m-d'))); ?>
    <input type="hidden" id="terminalidclink" name="terminalidclink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorypercashierwtID'); ?>" />
    <input type="hidden" id="totalctranslink" name="totalctranslink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistoryperctotal'); ?>" />
    <input type="hidden" id="salesctranslink" name="salesctranslink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorypercsales'); ?>" />
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
          <th colspan="3" style=" width: 15%">Deposit</th>
          <th colspan="3" style=" width: 15%">Reload</th> 
          <th colspan="2" style=" width: 15%">Redemption</th>
          <th style=" width: 15%">Grosshold</th>
   </thead>
   <tbody id="tbltranshistorybody">
      <?php if($rows): ?>
       <tr>
       <th style=" width: 12%">Terminal#</th>
       <th style=" width: 14%">Login</th>
       <th style=" width: 14%">Logout</th>
       
       <th colspan="3"></th>
       <th colspan="3"></th>
       <th colspan="2"></th>
       
       <th></th>
     </tr>
     <tr><td>&nbsp;&nbsp;&nbsp;</td></tr>   
     <tr>
     <?php 
        $totaldeposit = 0;
        $totalreload = 0;
        $totalwithdraw = 0;
        $grosshold = 0;
        $totalgrosshold = 0;
     ?>
     <?php foreach($rows as $r): ?>
       <?php //debug($r); exit; ?>
      <tr>
          <?php //$suffix = (($r['isVIP'])?'vip':''); ?>
         <td align="center"><b><a id="termctranssumm" style="color: black;" href="#" tcode="<?php echo $r['TerminalCode']; ?>" transSummID="<?php echo $r['TransactionSummaryID']; ?>" tID="<?php echo $r['TerminalID']; ?>" ><?php echo $r['TerminalCode']; ?></a></b></td>
         <td align="center">
            <?php $date_started = explode('.', $r['DateStarted']); ?>
            <?php echo date('Y-m-d h:i:s A',  strtotime($date_started[0])); ?>
         </td>
         <td align="center">
            <?php $date_ended = explode('.', $r['DateEnded']); ?>
            <?php echo (($date_ended[0])?date('Y-m-d h:i:s A',  strtotime($date_ended[0])):'Still playing ...'); ?>
         </td>
         <?php 
            $totaldeposit += $r['TotalCTransDeposit'];
            $totalreload += $r['TotalCTransReload'];
            $totalwithdraw += $r['TotalCTransRedemption'];
            $grosshold = ($r['TotalCTransDeposit']+$r['TotalCTransReload'])-($r['TotalCTransRedemption']); 
            $totalgrosshold += $grosshold;
         ?>
         <td colspan='3' class="right"><?php echo number_format($r['TotalCTransDeposit'],2) ?></td>     
         <td colspan='3' class="right"><?php echo number_format($r['TotalCTransReload'],2) ?></td>
         <td colspan='2'  class="right"><?php echo number_format($r['TotalCTransRedemption'],2) ?></td>
         <td class="right"><?php echo number_format($grosshold,2) ?></td>       
      </tr>
      <?php endforeach; ?>
     </tr>
     <tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>
      <tr>
          <td></td>
          <th style="background-color:#BCBCBA"><b><a id='totctranssumm' style='color: black;' href='#' >Total</a></b></th>
          <td style="background-color:#BCBCBA"></td>
          <td align="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totaldeposit,2);?></td>
          <td align="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totalreload,2);?></td>
          <td align="right" colspan="2" style="background-color:#BCBCBA"><?php echo number_format($totalwithdraw,2);?></td>
          <td align="right" style="background-color:#BCBCBA"><?php echo number_format($totalgrosshold,2);?></td>
      </tr>
      <tr><td colspan="12"><b><a id="transCSiteSumm" href="#" style="text-decoration: underline ;color: black;">Click here to view the summary breakdown</a></b></td></tr>   
      <?php endif; ?>
   </tbody>
</table>
    </div>
   
<form method="post" id="frmtranshistory" action="<?php echo Mirage::app()->createUrl('pdf/transactionhistorycashier'); ?>">
    <input type="hidden" id="hidselected_date" name="hidselected_date" />
<!--    <input id="btn_submit" type="button" value="Genereate PDF" />-->
</form>