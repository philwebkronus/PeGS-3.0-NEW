<script type="text/javascript">
$(document).ready(function(){
    $('#ReportsFormModel_date').datepicker({
        inline: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        maxDate : '<?php echo date('Y-m-d'); ?>'
    });
})
</script>
<h1>Transaction History Per Site</h1>
<form id="frmtranshist">
    <?php echo MI_HTML::inputHidden($reportsFormModel, 'terminal_id') ?>
    <?php echo MI_HTML::inputHidden($reportsFormModel, 'trans_sum_id') ?>
    <?php echo MI_HTML::label($reportsFormModel, 'date', 'Select Date'); ?>
    <?php echo MI_HTML::inputText($reportsFormModel, 'date',array('readonly'=>'readonly','value'=>date('Y-m-d'))); ?>
    <input type="hidden" id="terminalidlink" name="terminalidlink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorypertID'); ?>" />
    <input type="hidden" id="totaltranslink" name="totaltranslink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorytotal'); ?>" />
    <input type="hidden" id="salestranslink" name="salestranslink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorysales'); ?>" />
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
      <?php foreach($rows as $r){
          $grosshold = ($r['TotalTransDeposit']+$r['TotalTransReload'])-($r['TotalTransRedemption']);
                $rgrosshold = number_format($grosshold,2);
                $date_ended = explode('.', $r['DateEnded']);
                $date_started = explode('.', $r['DateStarted']);
                $rterminalcode = $r['TerminalCode'];
                $rterminalid = $r['TerminalID'];
                $rtransummid = $r['TransactionSummaryID'];
                $rstartdate = date('Y-m-d h:i:s A',  strtotime($date_started[0]));
                $renddate = (($date_ended[0])?date('Y-m-d h:i:s A',  strtotime($date_ended[0])):'Still playing ...');
                $tottransdep = number_format($r['TotalTransDeposit'],2);
                $tottransrel = number_format($r['TotalTransReload'],2);
                $tottransred = number_format($r['TotalTransRedemption'],2);
         echo "<tr>";
         echo "<td align='center'><b><a id='termtranssumm' style='color: black;' href='#' tcode='$rterminalcode' transSummID='$rtransummid' tID='$rterminalid'>$rterminalcode</a></b></td>";
         echo "<td align='center'>$rstartdate</td>";
         echo "<td align='center'>$renddate</td>";
         echo "<td colspan='3' class='right'>$tottransdep</td>";
         echo "<td colspan='3' class='right'>$tottransrel</td>";
         echo "<td colspan='2' class='right'>$tottransred</td>";         
         echo "<td class='right'>$rgrosshold</td>";
      echo "</tr>";
     }
     ?>
      <?php
      $res = $total_rows;
      
        $totaldeposit = 0.00;
        $totalreload = 0.00;
        $totalwithdraw = 0.00;
        $totalgrosshold = 0.00;

      foreach ($res as $r) {
            $totaldeposit += $r['TotalTransDeposit'];
            $totalreload += $r['TotalTransReload'];
            $totalwithdraw += $r['TotalTransRedemption'];
        }

       /*--- TOTAL ---*/
       $totalgrosshold = ($totaldeposit + $totalreload) - $totalwithdraw;

      ?>
     
     <tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>
      <tr>
          <td></td>
          <th style="background-color:#BCBCBA"><b><a id='tottranssumm' style='color: black;' href='#' >Total</a></b></th>
          <td style="background-color:#BCBCBA"></td>
          <td class="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totaldeposit,2);?></td>
          <td class="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totalreload,2);?></td>
          <td class="right" colspan="2" style="background-color:#BCBCBA"><?php echo number_format($totalwithdraw,2);?></td>
          <td class="right" style="background-color:#BCBCBA"><?php echo number_format($totalgrosshold,2);?></td>
      </tr>
      <tr><td colspan="12"><b><a id="transSiteSumm" href="#" style="text-decoration: underline ;color: black;">Click here to view the summary breakdown</a></b></td></tr>   
      <?php endif; ?>
   </tbody>
</table>
    </div>
<br/>
<form method="post" id="frmtranshistory" action="<?php echo Mirage::app()->createUrl('pdf/transactionhistory'); ?>">
    <input type="hidden" id="hidselected_date" name="hidselected_date" />
<!--    <input id="btn_submit" type="button" value="Genereate PDF" />-->
</form>