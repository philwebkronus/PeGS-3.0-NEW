<script type="text/javascript">
$(document).ready(function(){
    $('#ReportsFormModel_date').datepicker({
        inline: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        maxDate : '<?php echo date('Y-m-d'); ?>'
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
<h1>Transaction History Per Site</h1>
<form id="frmtranshist">
    <?php echo MI_HTML::inputHidden($reportsFormModel, 'terminal_id') ?>
    <?php echo MI_HTML::inputHidden($reportsFormModel, 'trans_sum_id') ?>
    <?php echo MI_HTML::label($reportsFormModel, 'date', 'Select Date'); ?>
    <?php echo MI_HTML::inputText($reportsFormModel, 'date',array('readonly'=>'readonly','value'=>date('Y-m-d'))); ?>
    <input type="hidden" id="terminalidlink" name="terminalidlink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorypertID'); ?>" />
    <input type="hidden" id="totaltranslink" name="totaltranslink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorytotal'); ?>" />
    <input type="hidden" id="salestranslink" name="salestranslink" value="<?php echo Mirage::app()->createUrl('reports/transactionhistorysales'); ?>" />
    <input type="hidden" id="compareddate" name="compareddate" value="<?php echo Mirage::app()->param['eSAFEStartDate']; ?>" />
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
      
            $deposit = 0;
            $reload = 0;
            $withdraw = 0;
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

            foreach($rows as $r){
                $rgrosshold = 0;
                $date_ended = explode('.', $r['DateEnded']);
                $date_started = explode('.', $r['DateStarted']);
                $rterminalcode = $r['TerminalCode'];
                $rterminalid = $r['TerminalID'];
                $rtransummid = $r['TransactionSummaryID'];
                $rstartdate = date('Y-m-d h:i:s A',  strtotime($date_started[0]));
                $renddate = (($date_ended[0])?date('Y-m-d h:i:s A',  strtotime($date_ended[0])):'Still playing ...');
                $deposit = number_format($r['TotalTransDeposit'],2);
                $reload = number_format($r['TotalTransReload'],2);
                $withdraw = number_format($r['TotalTransRedemption'],2);
                $totaldeposit += $r['TotalTransDeposit'];
                $totalreload += $r['TotalTransReload'];
                $totalwithdraw += $r['TotalTransRedemption'];
                
                if($pickdate < $compareddate){
                    
                    $grosshold = ($r['TotalTransDeposit']+$r['TotalTransReload']) - $r['TotalTransRedemption'];
                    $rgrosshold = number_format($grosshold,2);
                    
                    echo "<tr>";
                    echo "<td align='center'><b><a id='termtranssumm' style='color: black;' href='#' tcode='$rterminalcode' transSummID='$rtransummid' tID='$rterminalid'>$rterminalcode</a></b></td>";
                    echo "<td align='center'>$rstartdate</td>";
                    echo "<td align='center'>$renddate</td>";
                    echo "<td colspan='3' class='right'>$deposit</td>";
                    echo "<td colspan='3' class='right'>$reload</td>";
                    echo "<td colspan='2' class='right'>$withdraw</td>";         
                    echo "<td class='right'>$rgrosshold</td>";
                    echo "</tr>";
                } else {
                    if($r['IseSAFETrans']){ //If eSAFE transaction
                        if($r['DateEnded'] == 0){
                            $startbal = 'N/A';
                            $esafereloads = 'N/A';
                            $endbal = 'N/A';
                            $grosshold = 'N/A';
                        } else {
                            $totalstartbal += $r['StartBalance'];
                            $totalesafereloads += $r['WalletReloads'];
                            $totalendbal += $r['EndBalance'];
                            $startbal = number_format($r['StartBalance'],2);
                            $esafereloads = number_format($r['WalletReloads'],2);
                            $endbal = number_format($r['EndBalance'],2); 
                            $rgrosshold = ($r['StartBalance']+$r['WalletReloads']) - $r['EndBalance']; 
                            $totalgrosshold +=  $rgrosshold;
                            $grosshold = number_format($rgrosshold,2);
                        }
                    } else {
                        $startbal = 'N/A';
                        $esafereloads = 'N/A';
                        $endbal = 'N/A';
                        $rgrosshold = ($r['TotalTransDeposit']+$r['TotalTransReload'])-$r['TotalTransRedemption'];
                        $totalgrosshold +=  $rgrosshold;
                        $grosshold = number_format($rgrosshold,2);
                    }
                    
                    echo "<tr>";
                    echo "<td align='center'><b><a id='termtranssumm' style='color: black;' href='#' tcode='$rterminalcode' transSummID='$rtransummid' tID='$rterminalid'>$rterminalcode</a></b></td>";
                    echo "<td align='center'>$rstartdate</td>";
                    echo "<td align='center'>$renddate</td>";
                    echo "<td colspan='3' class='right'>$deposit</td>";
                    echo "<td colspan='3' class='right'>$reload</td>";
                    echo "<td colspan='2' class='right'>$withdraw</td>";         
//                    echo "<td colspan='2' class='right'>$startbal</td>";         
//                    echo "<td colspan='2' class='right'>$esafereloads</td>";         
//                    echo "<td colspan='2' class='right'>$endbal</td>";         
//                    echo "<td class='right'>$grosshold</td>";
                    echo "</tr>";
                }
     }
     
     if($pickdate < $compareddate){
         echo "<tr>
                    <td></td>
                    <th style='background-color:#BCBCBA'><b><a id='tottranssumm' style='color: black;' href='#' >Total</a></b></th>
                    <td style='background-color:#BCBCBA'></td>
                    <td class='right' colspan='3' style='background-color:#BCBCBA'>".number_format($totaldeposit,2)."</td>
                    <td class='right' colspan='3' style='background-color:#BCBCBA'>".number_format($totalreload,2)."</td>
                    <td class='right' colspan='2' style='background-color:#BCBCBA'>".number_format($totalwithdraw,2)."</td>
                    <td class='right' style='background-color:#BCBCBA'>".number_format($totalgrosshold,2)."</td>
                </tr>
                <tr><td colspan='12'><b><a id='transSiteSumm' href='#' style='text-decoration: underline ;color: black;'>Click here to view the summary breakdown</a></b></td></tr>";
     } else {
         echo "<tr>
                    <td></td>
                    <th style='background-color:#BCBCBA'><b><a id='tottranssumm' style='color: black;' href='#' >Total</a></b></th>
                    <td style='background-color:#BCBCBA'></td>
                    <td class='right' colspan='3' style='background-color:#BCBCBA'>".number_format($totaldeposit,2)."</td>
                    <td class='right' colspan='3' style='background-color:#BCBCBA'>".number_format($totalreload,2)."</td>
                    <td class='right' colspan='2' style='background-color:#BCBCBA'>".number_format($totalwithdraw,2)."</td>
                    <!--<td class='right' colspan='2' style='background-color:#BCBCBA'>".number_format($totalstartbal,2)."</td>
                    <td class='right' colspan='2' style='background-color:#BCBCBA'>".number_format($totalesafereloads,2)."</td>
                    <td class='right' colspan='2' style='background-color:#BCBCBA'>".number_format($totalendbal,2)."</td>
                    <td class='right' style='background-color:#BCBCBA'>".number_format($totalgrosshold,2)."</td>-->
                </tr>
                <tr><td colspan='12'><b><a id='transSiteSumm' href='#' style='text-decoration: underline ;color: black;'>Click here to view the summary breakdown</a></b></td></tr>
                <!--<tr><td colspan='18'><b><a id='transSiteSumm' href='#' style='text-decoration: underline ;color: black;'>Click here to view the summary breakdown</a></b></td></tr>-->";
     }
     
     ?>
      <?php endif; ?>
   </tbody>
</table>
    </div>
<br/>
<form method="post" id="frmtranshistory" action="<?php echo Mirage::app()->createUrl('pdf/transactionhistory'); ?>">
    <input type="hidden" id="hidselected_date" name="hidselected_date" />
<!--    <input id="btn_submit" type="button" value="Genereate PDF" />-->
</form>