<script type="text/javascript">
$(document).ready(function(){
       if ($('#siteamountinfo').val() == 0){
             showLightbox(function(){
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[010]: Site amount is not set.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );   
            });
        }
    $('#ReportsFormModel_date').datepicker({
        inline: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        maxDate : '<?php echo date('Y-m-d') ?>'
    });
    
    $('#ReportsFormModel_date').live('change',function(){
        var pickdate  = $('#ReportsFormModel_date').val();
        var compareddate  = '<?php echo Mirage::app()->param['eSAFEStartDate']; ?>';
        var theader1st = '';
        var theader2nd = '';
        $("#tbltranshistorybody").html("");
        if(pickdate < compareddate){
            $("#tbltranshistoryhead").html("");
            $("#tbltranshistorysecondaryheader").html("");
            
            theader1st += '<th colspan="3" style=" width: 40%">Session</th>';
            theader1st += '<th colspan="3" style=" width: 15%">Deposit</th>';
            theader1st += '<th colspan="3" style=" width: 15%">Reload</th> ';
            theader1st += '<th colspan="2" style=" width: 15%">Redemption</th>';
            theader1st += '<th style=" width: 15%">Grosshold</th>';
            
            theader2nd += '<th style=" width: 12%">Terminal#</th>';
            theader2nd += '<th style=" width: 14%">Login</th>';
            theader2nd += '<th style=" width: 14%">Logout</th>';
            theader2nd += '<th colspan="3"></th>';
            theader2nd += '<th colspan="3"></th>';
            theader2nd += '<th colspan="2"></th>';
            theader2nd += '<th></th>';
            $("#tbltranshistoryhead").html(theader1st);
            $("#tbltranshistorysecondaryheader").html(theader2nd);
            
        } else {
            $("#tbltranshistoryhead").html("");
            $("#tbltranshistorysecondaryheader").html("");
//            theader1st += '<th colspan="3" style=" width: 30%">Session</th>';
//            theader1st += '<th colspan="3" style=" width: 10%">Deposit</th>';
//            theader1st += '<th colspan="3" style=" width: 10%">Reload</th> ';
//            theader1st += '<th colspan="2" style=" width: 10%">Redemption</th>';
//            theader1st += '<th colspan="2" style=" width: 10%">Starting Balance</th>';
//            theader1st += '<th colspan="2" style=" width: 10%">e-SAFE Loads <br/>(with Session)</th>';
//            theader1st += '<th colspan="2" style=" width: 10%">Ending Balance</th>';
//            theader1st += '<th style=" width: 10%">Grosshold</th>';
//            
//            theader2nd += '<th style=" width: 10%">Terminal#</th>';
//            theader2nd += '<th style=" width: 10%">Login</th>';
//            theader2nd += '<th style=" width: 10%">Logout</th>';
//            theader2nd += '<th colspan="3"></th>';
//            theader2nd += '<th colspan="3"></th>';
//            theader2nd += '<th colspan="2"></th>';
//            theader2nd += '<th colspan="2"></th>';
//            theader2nd += '<th colspan="2"></th>';
//            theader2nd += '<th colspan="2"></th>';
//            theader2nd += '<th></th>';

            theader1st += '<th colspan="3" style=" width: 40%">Session</th>';
            theader1st += '<th colspan="3" style=" width: 20%">Deposit</th>';
            theader1st += '<th colspan="3" style=" width: 20%">Reload</th> ';
            theader1st += '<th colspan="2" style=" width: 20%">Redemption</th>';
            
            theader2nd += '<th style=" width: 12%">Terminal#</th>';
            theader2nd += '<th style=" width: 14%">Login</th>';
            theader2nd += '<th style=" width: 14%">Logout</th>';
            theader2nd += '<th colspan="3"></th>';
            theader2nd += '<th colspan="3"></th>';
            theader2nd += '<th colspan="2"></th>';
            $("#tbltranshistoryhead").html(theader1st);
            $("#tbltranshistorysecondaryheader").html(theader2nd);
        }
        
    });
    
})
</script>
<input type="hidden" name="siteamountinfo" id="siteamountinfo" value="<?php echo $siteAmountInfo; ?>" />
<h1>Transaction History Per Virtual Cashier</h1>
<form id="frmtranshist">
    <?php // this hidden field is just indicator for javascript because it also use javascript of per history ?>
<!--    <input type="hidden" id="hidpercashierindicator" />-->
    <?php echo MI_HTML::inputHidden($reportsFormModel, 'terminal_id') ?>
    <?php echo MI_HTML::inputHidden($reportsFormModel, 'trans_sum_id') ?>
    <b><?php echo MI_HTML::label($reportsFormModel, 'date', 'Select Date'); ?></b>
    <?php echo MI_HTML::inputText($reportsFormModel, 'date',array('readonly'=>'readonly','value'=>date('Y-m-d'))); ?>
    <input type="hidden" id="terminalidvclink" name="terminalidvclink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorypervcashierwtID'); ?>" />
    <input type="hidden" id="totalvctranslink" name="totalvctranslink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorypervctotal'); ?>" />
    <input type="hidden" id="salesvctranslink" name="salesvctranslink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorypervcsales'); ?>" />
    <input type="hidden" id="compareddate" name="compareddate" value="<?php echo Mirage::app()->param['eSAFEStartDate']; ?>" />
    <input type="button" value="Query" id="btntranshistpervc" />
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
   <thead id="tbltranshistoryhead">
        <?php
             $compareddate = Mirage::app()->param['eSAFEStartDate']; 
             $pickdate = date('Y-m-d');

             if($pickdate < $compareddate):             // Old Computation
        ?>
                <th colspan="3" style=" width: 40%">Session</th>
                <th colspan="3" style=" width: 15%">Deposit</th>
                <th colspan="3" style=" width: 15%">Reload</th> 
                <th colspan="2" style=" width: 15%">Redemption</th>
                <th style=" width: 15%">Grosshold</th>
        <?php else: ?>
<!--                <th colspan="3" style=" width: 30%">Session</th>
                <th colspan="3" style=" width: 10%">Deposit</th>
                <th colspan="3" style=" width: 10%">Reload</th> 
                <th colspan="2" style=" width: 10%">Redemption</th>
                <th colspan="2" style=" width: 10%">Starting Balance</th>
                <th colspan="2" style=" width: 10%">e-SAFE Loads <br/>(with Session)</th>
                <th colspan="2" style=" width: 10%">Ending Balance</th>
                <th style=" width: 10%">Grosshold</th>-->
                
                <th colspan="3" style=" width: 40%">Session</th>
                <th colspan="3" style=" width: 20%">Deposit</th>
                <th colspan="3" style=" width: 20%">Reload</th> 
                <th colspan="2" style=" width: 20%">Redemption</th>
        <?php endif; ?>
   </thead>
   <tbody id="tbltranshistorybody">
       <?php if($rows): ?>
       <tr id="tbltranshistorysecondaryheader">
           <?php 
                if($pickdate < $compareddate):             // Old Computation
            ?>
                <th style=" width: 12%">Terminal#</th>
                <th style=" width: 14%">Login</th>
                <th style=" width: 14%">Logout</th>

                <th colspan="3"></th>
                <th colspan="3"></th>
                <th colspan="2"></th>

                <th></th>
            <?php else: ?>
<!--                <th style=" width: 6%">Terminal#</th>
                <th style=" width: 12%">Login</th>
                <th style=" width: 12%">Logout</th>

                <th colspan="3"></th>
                <th colspan="3"></th>
                <th colspan="2"></th>
                <th colspan="2"></th>
                <th colspan="2"></th>
                <th colspan="2"></th>

                <th></th>-->
                
                <th style=" width: 12%">Terminal#</th>
                <th style=" width: 14%">Login</th>
                <th style=" width: 14%">Logout</th>

                <th colspan="3"></th>
                <th colspan="3"></th>
                <th colspan="2"></th>
            <?php endif; ?>
     </tr>
     <tr>
    <?php 
        $totaldeposit = 0;
        $totalreload = 0;
        $totalwithdraw = 0;
        $totalgrosshold = 0;
        $totalstartbal = 0;
        $totalendbal = 0;
        $totalesafereloads = 0;
        
        $startbal = 0;
        $esafereloads = 0;
        $endbal = 0;
     ?>
    <?php 
        if($pickdate < $compareddate): 
    ?>
           <?php foreach($rows as $r): ?>
          <tr>
              <?php //$suffix = (($r['isVIP'])?'vip':''); ?>
             <td align="center"><b><a id="termvctranssumm" style="color: black;" href="#" tcode="<?php echo $r['TerminalCode']; ?>" transSummID="<?php echo $r['TransactionSummaryID']; ?>" tID="<?php echo $r['TerminalID']; ?>" ><?php echo $r['TerminalCode']; ?></a></b></td>
             <td align="center">
                <?php $date_started = explode('.', $r['DateStarted']); ?>
                <?php echo date('Y-m-d h:i:s A',  strtotime($date_started[0])); ?>
             </td>
             <td align="center">
                <?php $date_ended = explode('.', $r['DateEnded']); ?>
                <?php echo (($date_ended[0])?date('Y-m-d h:i:s A',  strtotime($date_ended[0])):'Still playing ...'); ?>
             </td>
             <?php 
                $grosshold = 0;
                $totaldeposit += $r['TotalCTransDeposit'];
                $totalreload += $r['TotalCTransReload'];
                $totalwithdraw += $r['TotalCTransRedemption'];
                
                $grosshold = ($r['TotalCTransDeposit']+$r['TotalCTransReload'])-$r['TotalCTransRedemption'];
                $totalgrosshold +=  $grosshold;

             ?>
             <td colspan='3' class="right"><?php echo number_format($r['TotalCTransDeposit'],2) ?></td>     
             <td colspan='3' class="right"><?php echo number_format($r['TotalCTransReload'],2) ?></td>
             <td colspan='2'  class="right"><?php echo number_format($r['TotalCTransRedemption'],2) ?></td>
             <td class="right"><?php echo number_format($grosshold,2) ?></td>
          </tr>
          <?php endforeach; ?>
         </tr>
          <tr>
              <td></td>
              <th style="background-color:#BCBCBA"><b><a id='totvctranssumm' style='color: black;' href='#' >Total</a></b></th>
              <td style="background-color:#BCBCBA"></td>
              <td align="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totaldeposit,2);?></td>
              <td align="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totalreload,2);?></td>
              <td align="right" colspan="2" style="background-color:#BCBCBA"><?php echo number_format($totalwithdraw,2);?></td>
              <td align="right" style="background-color:#BCBCBA"><?php echo number_format($totalgrosshold,2);?></td>
          </tr>
          <tr><td colspan="12"><b><a id="transVCSiteSumm" href="#" style="text-decoration: underline ;color: black;">Click here to view the summary breakdown</a></b></td></tr>   
    <?php else: ?>
         <?php foreach($rows as $r): ?>
          <tr>
              <?php //$suffix = (($r['isVIP'])?'vip':''); ?>
             <td align="center"><b><a id="termvctranssumm" style="color: black;" href="#" tcode="<?php echo $r['TerminalCode']; ?>" transSummID="<?php echo $r['TransactionSummaryID']; ?>" tID="<?php echo $r['TerminalID']; ?>" ><?php echo $r['TerminalCode']; ?></a></b></td>
             <td align="center">
                <?php $date_started = explode('.', $r['DateStarted']); ?>
                <?php echo date('Y-m-d h:i:s A',  strtotime($date_started[0])); ?>
             </td>
             <td align="center">
                <?php $date_ended = explode('.', $r['DateEnded']); ?>
                <?php echo (($date_ended[0])?date('Y-m-d h:i:s A',  strtotime($date_ended[0])):'Still playing ...'); ?>
             </td>
             <?php 
                $grosshold = 0;
                $totaldeposit += $r['TotalCTransDeposit'];
                $totaldeposit += $r['eWalletDeposits'];
                $totalreload += $r['TotalCTransReload'];
                $totalwithdraw += $r['TotalCTransRedemption'];
                $totalwithdraw += $r['eWalletWithdrawals'];
                $totalstartbal += $r['StartBalance'];
                $totalesafereloads += $r['WalletReloads'];
                $totalendbal += $r['EndBalance'];

                if($r['IseSAFETrans']){ //If eSAFE transaction
                    if($r['DateEnded'] == 0){
                        $startbal = 'N/A';
                        $esafereloads = 'N/A';
                        $endbal = 'N/A';
                        $grosshold = 'N/A';
                    } else {
                        $startbal = number_format($r['StartBalance'],2);
                        $esafereloads = number_format($r['WalletReloads'],2);
                        $endbal = number_format($r['EndBalance'],2); 
                        $grosshold = ($r['StartBalance']+$r['WalletReloads'])-$r['EndBalance']; 
                        $totalgrosshold +=  $grosshold;
                    }
                } else {
                    $startbal = 'N/A';
                    $esafereloads = 'N/A';
                    $endbal = 'N/A';
                    $grosshold = ($r['TotalCTransDeposit']+$r['TotalCTransReload'] + $r['eWalletDeposits'])-($r['TotalCTransRedemption'] + $r['eWalletWithdrawals']);
                    $totalgrosshold +=  $grosshold;
                }

             ?>
             <td colspan='3' class="right"><?php echo number_format(($r['TotalCTransDeposit'] + $r['eWalletDeposits']),2) ?></td>     
             <td colspan='3' class="right"><?php echo number_format($r['TotalCTransReload'],2) ?></td>
             <td colspan='2'  class="right"><?php echo number_format(($r['TotalCTransRedemption'] + $r['eWalletWithdrawals']),2) ?></td>
<!--             <td colspan='2'  class="right"><?php // echo $startbal ?></td>
             <td colspan='2'  class="right"><?php // echo $esafereloads ?></td>
             <td colspan='2'  class="right"><?php // echo $endbal ?></td>
             <td class="right"><?php // echo number_format($grosshold,2) ?></td>-->
          </tr>
          <?php endforeach; ?>
         </tr>
          <tr>
              <td></td>
              <th style="background-color:#BCBCBA"><b><a id='totvctranssumm' style='color: black;' href='#' >Total</a></b></th>
              <td style="background-color:#BCBCBA"></td>
              <td align="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totaldeposit,2);?></td>
              <td align="right" colspan="3" style="background-color:#BCBCBA"><?php echo number_format($totalreload,2);?></td>
              <td align="right" colspan="2" style="background-color:#BCBCBA"><?php echo number_format($totalwithdraw,2);?></td>
<!--              <td align="right" colspan="2" style="background-color:#BCBCBA"><?php // echo number_format($totalstartbal,2);?></td>
              <td align="right" colspan="2" style="background-color:#BCBCBA"><?php // echo number_format($totalesafereloads,2);?></td>
              <td align="right" colspan="2" style="background-color:#BCBCBA"><?php // echo number_format($totalendbal,2);?></td>
              <td align="right" style="background-color:#BCBCBA"><?php // echo number_format($totalgrosshold,2);?></td>-->
          </tr>
          <tr><td colspan="12"><b><a id="transVCSiteSumm" href="#" style="text-decoration: underline ;color: black;">Click here to view the summary breakdown</a></b></td></tr>   
          <!--<tr><td colspan="18"><b><a id="transVCSiteSumm" href="#" style="text-decoration: underline ;color: black;">Click here to view the summary breakdown</a></b></td></tr>-->   
    <?php endif; ?>
      <?php endif; ?>
   </tbody>
</table>
    </div>

<form method="post" id="frmtranshistory" action="<?php echo Mirage::app()->createUrl('pdf/transactionhistorycashier'); ?>">
    <input type="hidden" id="hidselected_date" name="hidselected_date" />
<!--    <input id="btn_submit" type="button" value="Genereate PDF" />-->
</form>