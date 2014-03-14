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
                <th align="center">
                Page Index
                <select id="startlimit" name="startlimit">
                    <?php if($page_count): ?>
                    <?php for($i =1;$i<= $page_count; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                    <?php else: ?>
                        <option value="0">No result</option>
                    <?php endif; ?>
                </select>
                </th>
            </tr>
        </table>
    </div>
</form>
<div class="clear"></div>

<div id="displayingpageof"><?php echo $displayingpageof ?></div>
<table id="tbltranshistory" border="1">
   <thead>
       <tr>
         <th>Login</th>
         <th>Time In</th>
         <th>Time Out</th>
         <th class="right">Initial Deposit</th>
         <th class="right">Total Reload</th>
         <th class="right">Redemption</th>
         <th class="right">Gross Hold</th>
      </tr>
   </thead>
   <tbody id="tbltranshistorybody">
      <?php $class = 'odd'; ?>
       <?php
           $deposit = 0;
           $reload = 0;
           $withdrawal = 0;
           $grosshold = 0;
       ?>
      <?php foreach($rows as $r): ?>
       <?php //debug($r); exit; ?>
      <tr class="<?php echo $class; ?>">
          <?php //$suffix = (($r['isVIP'])?'vip':''); ?>
         <td><?php echo $r['TerminalCode']; ?></td>
         <td>
            <?php $date_started = explode('.', $r['DateStarted']); ?>
            <?php echo date('Y-m-d h:i:s A',  strtotime($date_started[0])); ?>
         </td>
         <td>
            <?php $date_ended = explode('.', $r['DateEnded']); ?>
            <?php echo (($date_ended[0])?date('Y-m-d h:i:s A',  strtotime($date_ended[0])):'Still playing ...'); ?>
         </td>
         <?php 
            $deposit += $r['Deposit'];
            $reload += $r['Reload'];
            $withdrawal += $r['Withdrawal'];
            $grosshold = $grosshold + ($r['Deposit'] + $r['Reload'] - $r['Withdrawal']);
         ?>
         <td class="right"><?php echo number_format($r['Deposit'],2) ?></td>
         <td class="right"><?php echo number_format($r['Reload'],2) ?></td>
         <td class="right"><?php echo number_format($r['Withdrawal'],2); ?></td>
         <td class="right"><?php echo number_format(($r['Deposit'] + $r['Reload'] - $r['Withdrawal']),2); ?></td>      
      </tr>
      <?php
      if($class =='odd')
         $class = 'even';
      else
         $class = 'odd';
      ?>
      <?php endforeach; ?>
      <?php if($rows): ?>
      <tr style="background-color: #BCBCBA !important">
          <th colspan="3" >TOTAL</th>
          <td class="right"><?php echo number_format($deposit,2); ?></td>
          <td class="right"><?php echo number_format($reload,2); ?></td>
          <td class="right"><?php echo number_format($withdrawal,2); ?></td>
          <td class="right"><?php echo number_format(($deposit + $reload - $withdrawal),2); ?></td>
      </tr>
      <tr style="background-color: #BCBCBA !important">
          <th colspan="3" >GRAND TOTAL</th>
          <td class="right"><?php echo number_format($total_rows['totaldeposit'],2) ?></td>
          <td class="right"><?php echo number_format($total_rows['totalreload'],2) ?></td>
          <td class="right"><?php echo number_format($total_rows['totalwithdrawal'],2) ?></td>
          <td class="right"><?php echo number_format(($total_rows['totaldeposit'] + $total_rows['totalreload'] - $total_rows['totalwithdrawal']),2); ?></td>       
      </tr>
      <tr style="background-color: #BCBCBA !important">
          <th colspan="3">TOTAL SALES</th>
          <td colspan="2" align="center"><?php echo toMoney($total_rows['totaldeposit']+$total_rows['totalreload']) ?></td>
          <td colspan="2"></td>
      </tr>
      <?php endif; ?>
   </tbody>
</table>
<form method="post" id="frmtranshistory" action="<?php echo Mirage::app()->createUrl('pdf/transactionhistory'); ?>">
    <input type="hidden" id="hidselected_date" name="hidselected_date" />
<!--    <input id="btn_submit" type="button" value="Genereate PDF" />-->
</form>