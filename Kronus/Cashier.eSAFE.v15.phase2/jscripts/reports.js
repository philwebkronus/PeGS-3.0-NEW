$(document).ready(function(){
    $('#ReportsFormModel_reports_type').change(function(){
        if($(this).val() == '') {
            $('#reports_container').html('');
            return false;
        }
        var url = $(this).val();
        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                success : function(data) {
                    $('#reports_container').html(data);
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            })
        });
    });
    
    jQuery('#tbltranshistory > tbody > tr').live('mouseover',function(){
        jQuery(this).addClass('highlight');
    });
    jQuery('#tbltranshistory > tbody > tr').live('mouseout',function(){
        jQuery(this).removeClass('highlight');
    });

    jQuery('#tblpercas > tbody > tr').live('mouseover',function(){
        jQuery(this).addClass('highlight');
    });
    jQuery('#tblpercas > tbody > tr').live('mouseout',function(){
        jQuery(this).removeClass('highlight');
    });  
    
    $('#startlimit').live('change',function(){
        $('#btntranshist').trigger('click');
    })
    
    $('#btntranshist').live('click',function(){
        var url = $('#ReportsFormModel_reports_type').val();
        var data = $('#frmtranshist').serialize();

        /*--- TOTAL ---*/

        var totaldeposit = 0;
        var totalreload = 0;
        var totalwithdraw = 0;

        var totalgrosshold = 0;
        
        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var tbody = '';
                        var json = $.parseJSON(data);
                        
                            tbody+='<tr>';
                            tbody+='<th style=" width: 12%">Terminal#</th>';
                            tbody+='<th style=" width: 14%">Login</th>';
                            tbody+='<th style=" width: 14%">Logout</th>';
                            
                            tbody+='<th colspan="3"></th>';
                            tbody+='<th colspan="3"></th>';
                            tbody+='<th colspan="2"></th>';
                            
                            tbody+='<th></th>';
                            tbody+='</tr>';
                            tbody+='<tr><td>&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                        for(i=0;i<json.rows.length;i++) {
                            var grosshold = (parseFloat(json.rows[i].TotalTransDeposit) +parseFloat(json.rows[i].TotalTransReload))-(parseFloat(json.rows[i].TotalTransRedemption));
                            
                            tbody+='<tr>';
                            tbody+='<td align="center"><b><a id="termtranssumm" style="color: black;" href="#" tcode="'+ json.rows[i].TerminalCode +'" transSummID="'+ json.rows[i].TransactionSummaryID +'" tID="'+ json.rows[i].TerminalID +'" >' + json.rows[i].TerminalCode + '</a></b></td>';
                            tbody+='<td align="center">' + formatDateAMPM(removeMillisec(json.rows[i].DateStarted)) + '</td>';
                            tbody+='<td align="center">' + ((json.rows[i].DateEnded == 0)?'Still playing ...':formatDateAMPM(removeMillisec(json.rows[i].DateEnded))) + '</td>';
                            
                            tbody+='<td colspan="3"  class="right">' + toMoney(json.rows[i].TotalTransDeposit,'no') + '</td>';
                            tbody+='<td colspan="3" class="right">' + toMoney(json.rows[i].TotalTransReload,'no') + '</td>';
                            tbody+='<td colspan="2" class="right">' + toMoney(json.rows[i].TotalTransRedemption,'no') + '</td>';

                            tbody+='<td class="right">' + toMoney(grosshold,'no') + '</td>';

                        }
                        
                        for(i=0;i<json.total_rows.length;i++) {
                            totaldeposit += parseFloat(json.total_rows[i].TotalTransDeposit);
                            totalreload += parseFloat(json.total_rows[i].TotalTransReload);
                            totalwithdraw += parseFloat(json.total_rows[i].TotalTransRedemption);
                        }

                        /*--- TOTAL ---*/
                        totalgrosshold = (totaldeposit + totalreload) - totalwithdraw;

                        $('#coverage').html(json.coverage);
                        $('#tbltranshistorybody').html('');

                        if(json.rows.length > 0) {
                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<th style="background-color:#BCBCBA"><b><a id="tottranssumm" style="color: black;" href="#" >Total</a></b></th>';
                            tbody+='<td style="background-color:#BCBCBA"></td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totaldeposit,'no') +'</td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totalreload,'no') +'</td>';
                            tbody+='<td class="right" colspan="2" style="background-color:#BCBCBA">'+ toMoney(totalwithdraw,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(totalgrosshold,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr><td colspan="12"><b><a id="transSiteSumm" href="#" style="text-decoration: underline ;color: black;">Click here to view the summary breakdown</a></b></td></tr> ';
                            
                            $('#tbltranshistorybody').html(tbody);
                        }
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
    });
    
    $('#termtranssumm').live('click',function(){
        $("#ReportsFormModel_terminal_id").val($(this).attr("tID"));
        $("#ReportsFormModel_trans_sum_id").val($(this).attr("transSummID"));
        
        var data = $('#frmtranshist').serialize();
        var tcode = $(this).attr("tcode");
        var url =  $("#terminalidlink").val();

        var DepCash = 0;
        var DepTicket = 0;
        var DepCoupon = 0;

        var RelCash = 0;
        var RelCoupon = 0;
        var RelTicket = 0;

        var RedCashier = 0;
        var RedGenesis = 0;
        
        var Totgrosshold = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);
                        
                        for(i=0;i<json.rows.length;i++) {
                            var grosshold = 0;
                            if(i == 0){
                                if(json.rows > 1){
                                    grosshold = ((parseFloat(json.rows[i].DCash) + parseFloat(json.rows[i].DTicket) + parseFloat(json.rows[i].DCoupon))+(parseFloat(json.rows[i].RCash) + parseFloat(json.rows[i].RTicket) + parseFloat(json.rows[i].RCoupon)))-(parseFloat(json.rows[i].WCashier) + parseFloat(json.rows[i].WGenesis));

                                    DepCash = parseFloat(json.rows[i].DCash) ;
                                    DepTicket = parseFloat(json.rows[i].DTicket);
                                    DepCoupon = parseFloat(json.rows[i].DCoupon);

                                    RelCash = parseFloat(json.rows[i].RCash);
                                    RelTicket = parseFloat(json.rows[i].RTicket);
                                    RelCoupon = parseFloat(json.rows[i].RCoupon);

                                    RedCashier = parseFloat(json.rows[i].WCashier);
                                    RedGenesis = parseFloat(json.rows[i].WGenesis);

                                    Totgrosshold = grosshold;
                                } else {
                                    grosshold = ((parseFloat(json.rows[i].DCash) + parseFloat(json.rows[i].DTicket) + parseFloat(json.rows[i].DCoupon))+(parseFloat(json.rows[i].RCash) + parseFloat(json.rows[i].RTicket) + parseFloat(json.rows[i].RCoupon)))-(parseFloat(json.rows[i].WCashier) + parseFloat(json.rows[i].WGenesis));

                                    DepCash = toMoney(json.rows[i].DCash,'no');
                                    DepTicket = toMoney(json.rows[i].DTicket,'no');
                                    DepCoupon = toMoney(json.rows[i].DCoupon,'no');

                                    RelCash = toMoney(json.rows[i].RCash,'no');
                                    RelTicket = toMoney(json.rows[i].RTicket,'no');
                                    RelCoupon = toMoney(json.rows[i].RCoupon,'no');

                                    RedCashier = toMoney(json.rows[i].WCashier,'no');
                                    RedGenesis = toMoney(json.rows[i].WGenesis,'no');

                                    Totgrosshold = toMoney(grosshold,'no');
                                }
                                
                            } else {
                                grosshold += ((parseFloat(json.rows[i].DCash) + parseFloat(json.rows[i].DTicket) + parseFloat(json.rows[i].DCoupon))+(parseFloat(json.rows[i].RCash) + parseFloat(json.rows[i].RTicket) + parseFloat(json.rows[i].RCoupon)))-(parseFloat(json.rows[i].WCashier) + parseFloat(json.rows[i].WGenesis));

                                DepCash += parseFloat(json.rows[i].DCash) ;
                                DepTicket += parseFloat(json.rows[i].DTicket);
                                DepCoupon += parseFloat(json.rows[i].DCoupon);

                                RelCash += parseFloat(json.rows[i].RCash);
                                RelTicket += parseFloat(json.rows[i].RTicket);
                                RelCoupon += parseFloat(json.rows[i].RCoupon);

                                RedCashier += parseFloat(json.rows[i].WCashier);
                                RedGenesis += parseFloat(json.rows[i].WGenesis);

                                Totgrosshold += grosshold;
                            }

                        }
                        
                        updateLightbox( '<div style="margin-bottom: 10px; font-weight: bold;">Terminal #: '+ tcode +'</div><table id="terminaltranssumm" ><tr><td><b>Deposit</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cash</td><td style="text-align: right;">' + DepCash+ '</td>' +
                                                    '</tr><tr><td></td><td>Ticket</td><td style="text-align: right;">' + DepTicket+ '</td>' +
                                                    '</tr><tr><td></td><td>Coupon</td><td style="text-align: right;">' + DepCoupon+ '</td>' +
                                                    '</tr><tr><td><b>Reload</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cash</td><td style="text-align: right;">' + RelCash+ '</td>' +
                                                    '</tr><tr><td></td><td>Ticket</td><td style="text-align: right;">' + RelTicket+ '</td>' +
                                                    '</tr><tr><td></td><td>Coupon</td><td style="text-align: right;">' + RelCoupon+ '</td>' +
                                                    '</tr><tr><td><b>Redemption</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cashier</td><td style="text-align: right;">' + RedCashier+ '</td>' +
                                                    '</tr><tr><td></td><td>Genesis</td><td style="text-align: right;">' + RedGenesis+ '</td>' +
                                                    '</tr><tr><td><b>Grosshold</b></td><td></td><td style="text-align: right; font-weight: bold;">' + Totgrosshold + '</td>' +
                                                    '</tr></table>' +
                                                    '<br /><center><input type="button" style="width: 60px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                    ''          
                    ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
        
    });

    $('#transSiteSumm').live('click',function(){
        var data = $('#frmtranshist').serialize();
        var url =  $("#salestranslink").val();

        var RegCash = 0;
        var RegTicket = 0;
        var RegCoupon = 0;
        var CashierRedemption = 0;
        var TotalRegCash = 0;
        var TotalRegTicket = 0;
        var TotalRegCoupon = 0;

        var eSAFECash = 0;
        var eSAFETickets = 0;
        var eSAFECoupon = 0;
        var TotaleSAFECash = 0;
        var TotaleSAFETickets = 0;
        var TotaleSAFECoupon = 0;

        var Sales = 0;
        var TotalSales = 0;
        var TotalPrintedTickets = 0;
        var TotalActiveTicketsForTheDay = 0;
        var TotalEncashedTickets = 0;
        var SubEncashedTickets = 0;
        var TotalActiveRunningTickets = 0;
        
        var CompCashOnHand = 0;
        var CashOnHand = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);

                        TotalRegCash = toMoney(json.total_rows.TotalRegCash,'no');
                        TotalRegTicket = toMoney(json.total_rows.TotalRegTicket,'no');
                        TotalRegCoupon = toMoney(json.total_rows.TotalRegCoupon,'no');
                        TotalCashierRedemption = toMoney(json.total_rows.TotalCashierRedemption,'no');
                        RegCash = parseFloat(json.total_rows.TotalRegCash) ;
                        RegTicket = parseFloat(json.total_rows.TotalRegTicket);
                        RegCoupon = parseFloat(json.total_rows.TotalRegCoupon);
                        CashierRedemption = parseFloat(json.total_rows.TotalCashierRedemption);

                        for(i=0;i<json.ticketlist.length;i++) {
                            if(i == 0){
                                if(json.ticketlist > 1){
                                    TotalPrintedTickets = parseFloat(json.ticketlist[i].PrintedRedemptionTickets) ;
                                    TotalActiveTicketsForTheDay = parseFloat(json.ticketlist[i].UnusedTickets) ;
                                    TotalEncashedTickets = parseFloat(json.ticketlist[i].EncashedTickets);
                                    SubEncashedTickets = parseFloat(json.ticketlist[i].EncashedTickets);
                                } else {
                                    TotalPrintedTickets = toMoney(json.ticketlist[i].PrintedRedemptionTickets,'no');
                                    TotalActiveTicketsForTheDay = toMoney(json.ticketlist[i].UnusedTickets,'no');
                                    TotalEncashedTickets = toMoney(json.ticketlist[i].EncashedTickets,'no');
                                    SubEncashedTickets = parseFloat(json.ticketlist[i].EncashedTickets,'no');
                                }
                            } else {
                                TotalPrintedTickets += parseFloat(json.ticketlist[i].PrintedRedemptionTickets) ;
                                TotalActiveTicketsForTheDay += parseFloat(json.ticketlist[i].UnusedTickets) ;
                                TotalEncashedTickets += parseFloat(json.ticketlist[i].EncashedTickets);
                                SubEncashedTickets += parseFloat(json.ticketlist[i].EncashedTickets);
                            }
                        }

                        eSAFECash = parseFloat(json.eWalletCashDeposits);
                        eSAFETickets = parseFloat(json.eWalletTicketDeposits);
                        eSAFECoupon = parseFloat(json.eWalletCouponDeposits);
                        TotaleSAFECash = toMoney(json.eWalletCashDeposits,'no');
                        TotaleSAFETickets = toMoney(json.eWalletTicketDeposits,'no');
                        TotaleSAFECoupon = toMoney(json.eWalletCouponDeposits,'no');

                        Sales = RegCash + RegTicket + RegCoupon + eSAFECash + eSAFETickets + eSAFECoupon;
                        TotalSales = toMoney(Sales,'no');
                        TotalActiveRunningTickets = toMoney(json.runningactivetickets,'no');

                        //Compute Cash On Hand [ Formula: (((Total Cash from Cashier & Genesis + eSAFE Cash Load) - (Total Cashier Redemption + eSAFE Withdraw)) - Total Encashed Tickets) - Total Manual Redemption ]
                        CompCashOnHand = ((RegCash + eSAFECash) - (CashierRedemption + parseFloat(json.eWalletWithdrawals)) - SubEncashedTickets) - parseFloat(json.manualredemptions);
                        CashOnHand = toMoney(CompCashOnHand,'no');

                        updateLightbox( '<div style="margin-bottom: 10px; font-weight: bold;"> Sales </div><table id="salestranssumm" ><tr><td style="text-align: center"><b>Non e-SAFE Cash</b></td><td style="text-align: right;">'+TotalRegCash+'</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>Non e-SAFE Tickets</b></td><td style="text-align: right;">' + TotalRegTicket+ '</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>Non e-SAFE Coupons</b></td><td style="text-align: right;">' + TotalRegCoupon+ '</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>e-SAFE Cash Deposits</b></td><td style="text-align: right;">' + TotaleSAFECash+ '</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>e-SAFE Ticket Deposits</b></td><td style="text-align: right;">' + TotaleSAFETickets+ '</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>e-SAFE Coupon Deposits</b></td><td style="text-align: right;">' + TotaleSAFECoupon+ '</td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Sales</b></td><td style="text-align: right;">' + TotalSales+ '</td>' +
                                                    '</tr><tr><td colspan="2" style="padding-top: 10px;padding-bottom:10px;"></td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Printed Tickets</b></td><td style="text-align: right;">' + TotalPrintedTickets+ '</td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Active Tickets For The Day</b></td><td style="text-align: right;">' + TotalActiveTicketsForTheDay+ '</td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Encashed Tickets</b></td><td style="text-align: right;">' + TotalEncashedTickets+ '</td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Active Running Tickets</b></td><td style="text-align: right;">' + TotalActiveRunningTickets+ '</td>' +
                                                    '</tr><tr><td colspan="2" style="padding-top: 10px;padding-bottom:10px;"></td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Cash On Hand</b></td><td style="text-align: right;">' + CashOnHand+ '</td>' +
                                                    '</tr></table>' +
                                                    '<br /><center><input type="button" style="width: 60px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                    ''          
                    ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
        
    });
    
    
    $('#tottranssumm').live('click',function(){
        
        var data = $('#frmtranshist').serialize();
        var url =  $("#totaltranslink").val();
        
        var RegDepCash = 0;
        var RegDepTicket = 0;
        var RegDepCoupon = 0;

        var RegRelCash = 0;
        var RegRelTicket = 0;
        var RegRelCoupon = 0;

        var GenDepCash = 0;
        var GenDepTicket = 0;
        var GenDepCoupon = 0;

        var GenRelCash = 0;
        var GenRelTicket = 0;
        var GenRelCoupon = 0;


        var RegRedCashier = 0;
        var RegRedGenesis = 0;

        var GenRedCashier = 0;
        var GenRedGenesis = 0;
        
        var RegGrosshold = 0;
        var GenGrosshold = 0;

        /*--- SUBTOTAL ---*/

        var SubDepCash = 0;
        var SubDepTicket = 0;
        var SubDepCoupon = 0;

        var SubRelCash = 0;
        var SubRelTicket = 0;
        var SubRelCoupon = 0;
        
        var SubRedCashier = 0;
        var SubRedGenesis = 0;

        var SubGrosshold = 0;
        
        /*--- TOTAL ---*/

        var TotalDeposit = 0;
        var TotalReload = 0;
        var TotalWithdraw = 0;

        var TotalGrosshold = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);

                        for(i=0;i<json.total_rows.length;i++) {
                            RegDepCash += parseFloat(json.total_rows[i].RegDCash);
                            RegDepTicket += parseFloat(json.total_rows[i].RegDTicket);
                            RegDepCoupon += parseFloat(json.total_rows[i].RegDCoupon);

                            RegRelCash += parseFloat(json.total_rows[i].RegRCash);
                            RegRelTicket += parseFloat(json.total_rows[i].RegRTicket);
                            RegRelCoupon += parseFloat(json.total_rows[i].RegRCoupon);

                            GenDepCash += parseFloat(json.total_rows[i].GenDCash);
                            GenDepTicket += parseFloat(json.total_rows[i].GenDTicket);
                            GenDepCoupon += parseFloat(json.total_rows[i].GenDCoupon);

                            GenRelCash += parseFloat(json.total_rows[i].GenRCash);
                            GenRelTicket += parseFloat(json.total_rows[i].GenRTicket);
                            GenRelCoupon += parseFloat(json.total_rows[i].GenRCoupon);

                            RegRedCashier += parseFloat(json.total_rows[i].WCashier);
                            GenRedGenesis += parseFloat(json.total_rows[i].WGenesis);
                        }
 
                        RegGrosshold = ((RegDepCash + RegDepTicket + RegDepCoupon)+(RegRelCash + RegRelTicket + RegRelCoupon))-(RegRedCashier + RegRedGenesis);
                        GenGrosshold = ((GenDepCash + GenDepTicket + GenDepCoupon)+(GenRelCash + GenRelTicket + GenRelCoupon))-(GenRedCashier + GenRedGenesis);

                        /*--- SUBTOTAL ---*/
                        
                        SubDepCash = RegDepCash + GenDepCash;
                        SubDepTicket = RegDepTicket + GenDepTicket;
                        SubDepCoupon = RegDepCoupon + GenDepCoupon;

                        SubRelCash = RegRelCash + GenRelCash;
                        SubRelTicket = RegRelTicket + GenRelTicket;
                        SubRelCoupon = RegRelCoupon + GenRelCoupon;
                        
                        SubRedCashier = RegRedCashier + RegRedGenesis;
                        SubRedGenesis = GenRedCashier + GenRedGenesis;

                        SubGrosshold = RegGrosshold + GenGrosshold;
                        
                        /*--- TOTAL ---*/
                        TotalDeposit = SubDepCash + SubDepTicket + SubDepCoupon;
                        TotalReload = SubRelCash + SubRelTicket + SubRelCoupon;
                        TotalWithdraw = SubRedCashier + SubRedGenesis;

                        TotalGrosshold = SubGrosshold;
                        
                        updateLightbox( '<div style="margin-bottom: 10px; font-weight: bold;">Total: </div><table id="totaltranssumm" ><thead> <th colspan="2" style=" width: 20%"></th>'+
                                                    '<th colspan="3" style="width: 22%;">Deposit</th>'+
                                                    '<th colspan="3" style="width: 21%;">Reload</th>'+
                                                    '<th colspan="2" style="width: 22%;">Redemption</th>'+
                                                    '<th style="width: 10%;">Grosshold</th></thead><tr>'+
                                                    '<th colspan="2" style="width: 10%;background-color: white;"></th>'+
                                                    '<th style="background-color: white;">Cash</th><th style="background-color: white;">Ticket</th><th style="background-color: white;">Coupon</th>'+
                                                    '<th style="background-color: white;">Cash</th><th style="background-color: white;">Ticket</th><th style="background-color: white;">Coupon</th>'+
                                                    '<th style="background-color: white;">Cashier</th><th style="background-color: white;">Genesis</th>'+
                                                    '<th style="background-color: white;"></th></tr>'+
                                                    '<th rowspan="3" style="background-color: white;">Breakdown</th>'+
                                                    '<th align="center" style="background-color: white;">Regular</th>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(RegDepCash,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(RegDepTicket,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(RegDepCoupon,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(RegRelCash,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(RegRelTicket,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(RegRelCoupon,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(RegRedCashier,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(RegRedGenesis,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(RegGrosshold,'no')+'</td></tr>'+
                                                    '<tr><th align="center" style="background-color: white;">Genesis</th>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(GenDepCash,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(GenDepTicket,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(GenDepCoupon,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(GenRelCash,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(GenRelTicket,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(GenRelCoupon,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(GenRedCashier,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(GenRedGenesis,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(GenGrosshold,'no')+'</td></tr>'+
                                                    '<tr><th align="center" style="background-color: white;">Subtotal</th>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(SubDepCash,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(SubDepTicket,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(SubDepCoupon,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(SubRelCash,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(SubRelTicket,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(SubRelCoupon,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(SubRedCashier,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(SubRedGenesis,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(SubGrosshold,'no')+'</td></tr>'+
                                                    '<tr><td colspan="11" class="right" style="padding-top: 7px; padding-bottom: 7px;background-color: white;"></td></tr>'+
                                                    '<tr><th colspan="2" align="center" style="background-color: white;">Total</th>'+
                                                    '<td colspan="3" class="right" style="background-color: white;">'+toMoney(TotalDeposit,'no')+'</td>'+
                                                    '<td colspan="3" class="right" style="background-color: white;">'+toMoney(TotalReload,'no')+'</td>'+
                                                    '<td colspan="2" class="right" style="background-color: white;">'+toMoney(TotalWithdraw,'no')+'</td>'+
                                                    '<td class="right" style="background-color: white;">'+toMoney(TotalGrosshold,'no')+'</td></tr>'+
                                                    '</table>' +
                                                    '<br /><center><input type="button" style="width: 60px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                    ''          
                    ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
        
    });
    
    $('#btntranshistpercash').live('click',function(){
        var url = $('#ReportsFormModel_reports_type').val();
        var data = $('#frmtranshist').serialize();
        
        var totaldeposit = 0;
        var totalreload = 0;
        var totalwithdraw = 0;

        var totalgrosshold = 0;
        
        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var tbody = '';
                        var json = $.parseJSON(data);
                        
                            tbody+='<tr>';
                            tbody+='<th style=" width: 12%">Terminal#</th>';
                            tbody+='<th style=" width: 14%">Login</th>';
                            tbody+='<th style=" width: 14%">Logout</th>';
                            
                            tbody+='<th colspan="3"></th>';
                            tbody+='<th colspan="3"></th>';
                            tbody+='<th colspan="2"></th>';
                            
                            tbody+='<th></th>';
                            tbody+='</tr>';
                            tbody+='<tr><td>&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                        for(i=0;i<json.rows.length;i++) {
                            
                            totaldeposit += parseFloat(json.rows[i].TotalCTransDeposit);
                            totalreload += parseFloat(json.rows[i].TotalCTransReload);
                            totalwithdraw += parseFloat(json.rows[i].TotalCTransRedemption);
                            
                            var grosshold = (parseFloat(json.rows[i].TotalCTransDeposit) + parseFloat(json.rows[i].TotalCTransReload))-(parseFloat(json.rows[i].TotalCTransRedemption));
                            
                            totalgrosshold += parseFloat(grosshold);
                            
                            tbody+='<tr>';
                            tbody+='<td align="center"><b><a id="termctranssumm" style="color: black;" href="#" tcode="'+ json.rows[i].TerminalCode +'" transSummID="'+ json.rows[i].TransactionSummaryID +'" tID="'+ json.rows[i].TerminalID +'" >' + json.rows[i].TerminalCode + '</a></b></td>';
                            tbody+='<td align="center">' + formatDateAMPM(removeMillisec(json.rows[i].DateStarted)) + '</td>';
                            tbody+='<td align="center">' + ((json.rows[i].DateEnded == 0)?'Still playing ...':formatDateAMPM(removeMillisec(json.rows[i].DateEnded))) + '</td>';
                            
                            tbody+='<td colspan="3" class="right">' + toMoney(json.rows[i].TotalCTransDeposit,'no') + '</td>';
                            tbody+='<td colspan="3" class="right">' + toMoney(json.rows[i].TotalCTransReload,'no') + '</td>';
                            tbody+='<td colspan="2" class="right">' + toMoney(json.rows[i].TotalCTransRedemption,'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(grosshold,'no') + '</td>';
                            
                            tbody+='</tr>';
//                         
                        }
                       
                        $('#coverage').html(json.coverage);
                        $('#tbltranshistorybody').html('');
                        
                        if(json.rows.length > 0) {
                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<th style="background-color:#BCBCBA"><b><a id="totctranssumm" style="color: black;" href="#" >Total</a></b></th>';
                            tbody+='<td style="background-color:#BCBCBA"></td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totaldeposit,'no') +'</td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totalreload,'no') +'</td>';
                            tbody+='<td class="right" colspan="2" style="background-color:#BCBCBA">'+ toMoney(totalwithdraw,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(totalgrosshold,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr><td colspan="12"><b><a id="transCSiteSumm" href="#" style="text-decoration: underline ;color: black;">Click here to view the summary breakdown</a></b></td></tr> ';
                        
                            $('#tbltranshistorybody').html(tbody);
                        }
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
    });
    
    $('#termctranssumm').live('click',function(){
        $("#ReportsFormModel_terminal_id").val($(this).attr("tID"));
        $("#ReportsFormModel_trans_sum_id").val($(this).attr("transSummID"));
        
        var data = $('#frmtranshist').serialize();
        var tcode = $(this).attr("tcode");
        var url =  $("#terminalidclink").val();

        var DepCash = 0;
        var DepTicket = 0;
        var DepCoupon = 0;

        var RelCash = 0;
        var RelCoupon = 0;
        var RelTicket = 0;

        var RedCashier = 0;
        var RedGenesis = 0;
        
        var Totgrosshold = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);
                        
                        for(i=0;i<json.rows.length;i++) {
                            var grosshold = 0;
                            if(i == 0){
                                if(json.rows > 1){
                                    grosshold = ((parseFloat(json.rows[i].DCash) + parseFloat(json.rows[i].DTicket) + parseFloat(json.rows[i].DCoupon))+(parseFloat(json.rows[i].RCash) + parseFloat(json.rows[i].RTicket) + parseFloat(json.rows[i].RCoupon)))-(parseFloat(json.rows[i].WCashier) + parseFloat(json.rows[i].WGenesis));

                                    DepCash = parseFloat(json.rows[i].DCash) ;
                                    DepTicket = parseFloat(json.rows[i].DTicket);
                                    DepCoupon = parseFloat(json.rows[i].DCoupon);

                                    RelCash = parseFloat(json.rows[i].RCash);
                                    RelTicket = parseFloat(json.rows[i].RTicket);
                                    RelCoupon = parseFloat(json.rows[i].RCoupon);

                                    RedCashier = parseFloat(json.rows[i].WCashier);
                                    RedGenesis = parseFloat(json.rows[i].WGenesis);

                                    Totgrosshold = grosshold;
                                } else {
                                    grosshold = ((parseFloat(json.rows[i].DCash) + parseFloat(json.rows[i].DTicket) + parseFloat(json.rows[i].DCoupon))+(parseFloat(json.rows[i].RCash) + parseFloat(json.rows[i].RTicket) + parseFloat(json.rows[i].RCoupon)))-(parseFloat(json.rows[i].WCashier) + parseFloat(json.rows[i].WGenesis));

                                    DepCash = toMoney(json.rows[i].DCash,'no');
                                    DepTicket = toMoney(json.rows[i].DTicket,'no');
                                    DepCoupon = toMoney(json.rows[i].DCoupon,'no');

                                    RelCash = toMoney(json.rows[i].RCash,'no');
                                    RelTicket = toMoney(json.rows[i].RTicket,'no');
                                    RelCoupon = toMoney(json.rows[i].RCoupon,'no');

                                    RedCashier = toMoney(json.rows[i].WCashier,'no');
                                    RedGenesis = toMoney(json.rows[i].WGenesis,'no');

                                    Totgrosshold = toMoney(grosshold,'no');
                                }
                                
                            } else {
                                grosshold += ((parseFloat(json.rows[i].DCash) + parseFloat(json.rows[i].DTicket) + parseFloat(json.rows[i].DCoupon))+(parseFloat(json.rows[i].RCash) + parseFloat(json.rows[i].RTicket) + parseFloat(json.rows[i].RCoupon)))-(parseFloat(json.rows[i].WCashier) + parseFloat(json.rows[i].WGenesis));

                                DepCash += parseFloat(json.rows[i].DCash) ;
                                DepTicket += parseFloat(json.rows[i].DTicket);
                                DepCoupon += parseFloat(json.rows[i].DCoupon);

                                RelCash += parseFloat(json.rows[i].RCash);
                                RelTicket += parseFloat(json.rows[i].RTicket);
                                RelCoupon += parseFloat(json.rows[i].RCoupon);

                                RedCashier += parseFloat(json.rows[i].WCashier);
                                RedGenesis += parseFloat(json.rows[i].WGenesis);

                                Totgrosshold += grosshold;
                            }

                        }
                        
                        updateLightbox( '<div style="margin-bottom: 10px; font-weight: bold;">Terminal #: '+ tcode +'</div><table id="terminalctranssumm" ><tr><td><b>Deposit</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cash</td><td style="text-align: right;">' + DepCash+ '</td>' +
                                                    '</tr><tr><td></td><td>Ticket</td><td style="text-align: right;">' + DepTicket+ '</td>' +
                                                    '</tr><tr><td></td><td>Coupon</td><td style="text-align: right;">' + DepCoupon+ '</td>' +
                                                    '</tr><tr><td><b>Reload</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cash</td><td style="text-align: right;">' + RelCash+ '</td>' +
                                                    '</tr><tr><td></td><td>Ticket</td><td style="text-align: right;">' + RelTicket+ '</td>' +
                                                    '</tr><tr><td></td><td>Coupon</td><td style="text-align: right;">' + RelCoupon+ '</td>' +
                                                    '</tr><tr><td><b>Redemption</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cashier</td><td style="text-align: right;">' + RedCashier+ '</td>' +
                                                    '</tr><tr><td></td><td>Genesis</td><td style="text-align: right;">' + RedGenesis+ '</td>' +
                                                    '</tr><tr><td><b>Grosshold</b></td><td></td><td style="text-align: right; font-weight: bold;">' + Totgrosshold + '</td>' +
                                                    '</tr></table>' +
                                                    '<br /><center><input type="button" style="width: 60px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                    ''          
                        ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
        
    });
    
    $('#totctranssumm').live('click',function(){
        
        var data = $('#frmtranshist').serialize();
        var url =  $("#totalctranslink").val();

        /*--- SUBTOTAL ---*/

        var SubDepCash = 0;
        var SubDepTicket = 0;
        var SubDepCoupon = 0;

        var SubRelCash = 0;
        var SubRelTicket = 0;
        var SubRelCoupon = 0;
        
        var SubRedCashier = 0;
        var SubRedGenesis = 0;

        var SubGrosshold = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);

                        for(i=0;i<json.total_rows.length;i++) {
                            SubDepCash += parseFloat(json.total_rows[i].DCash);
                            SubDepTicket += parseFloat(json.total_rows[i].DTicket);
                            SubDepCoupon += parseFloat(json.total_rows[i].DCoupon);

                            SubRelCash += parseFloat(json.total_rows[i].RCash);
                            SubRelTicket += parseFloat(json.total_rows[i].RTicket);
                            SubRelCoupon += parseFloat(json.total_rows[i].RCoupon);

                            SubRedCashier += parseFloat(json.total_rows[i].WCashier);
                            SubRedGenesis += parseFloat(json.total_rows[i].WGenesis);
                        }

                        SubGrosshold = ((SubDepCash + SubDepTicket + SubDepCoupon)+(SubRelCash + SubRelTicket + SubRelCoupon))-(SubRedCashier + SubRedGenesis);
                        
                        updateLightbox( '<div style="margin-bottom: 10px; font-weight: bold;">Total: </div><table id="totalctranssumm" ><tr><td><b>Deposit</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cash</td><td style="text-align: right;">' + toMoney(SubDepCash,'no')+ '</td>' +
                                                    '</tr><tr><td></td><td>Ticket</td><td style="text-align: right;">' + toMoney(SubDepTicket,'no')+ '</td>' +
                                                    '</tr><tr><td></td><td>Coupon</td><td style="text-align: right;">' + toMoney(SubDepCoupon,'no')+ '</td>' +
                                                    '</tr><tr><td><b>Reload</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cash</td><td style="text-align: right;">' + toMoney(SubRelCash,'no')+ '</td>' +
                                                    '</tr><tr><td></td><td>Ticket</td><td style="text-align: right;">' + toMoney(SubRelTicket,'no')+ '</td>' +
                                                    '</tr><tr><td></td><td>Coupon</td><td style="text-align: right;">' + toMoney(SubRelCoupon,'no')+ '</td>' +
                                                    '</tr><tr><td><b>Redemption</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cashier</td><td style="text-align: right;">' + toMoney(SubRedCashier,'no')+ '</td>' +
                                                    '</tr><tr><td></td><td>Genesis</td><td style="text-align: right;">' + toMoney(SubRedGenesis,'no')+ '</td>' +
                                                    '</tr><tr><td><b>Grosshold</b></td><td></td><td style="text-align: right; font-weight: bold;">' + toMoney(SubGrosshold,'no') + '</td>' +
                                                    '</tr></table>' +
                                                    '<br /><center><input type="button" style="width: 60px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                    ''          
                        ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
        
    });
    
    $('#transCSiteSumm').live('click',function(){
        var data = $('#frmtranshist').serialize();
        var url =  $("#salesctranslink").val();

        var RegCash = 0;
        var RegTicket = 0;
        var RegCoupon = 0;
        var CashierRedemption = 0;
        var TotalRegCash = 0;
        var TotalRegTicket = 0;
        var TotalRegCoupon = 0;

        var eSAFECash = 0;
        var eSAFETickets = 0;
        var eSAFECoupon = 0;
        var TotaleSAFECash = 0;
        var TotaleSAFETickets = 0;
        var TotaleSAFECoupon = 0;

        var Sales = 0;
        var TotalSales = 0;
        
        var TotalEncashedTickets = 0;
        var SubEncashedTickets = 0;
        
        var CompCashOnHand = 0;
        var CashOnHand = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);

                        TotalRegCash = toMoney(json.total_rows.TotalRegCash,'no');
                        TotalRegTicket = toMoney(json.total_rows.TotalRegTicket,'no');
                        TotalRegCoupon = toMoney(json.total_rows.TotalRegCoupon,'no');
                        TotalCashierRedemption = toMoney(json.total_rows.TotalCashierRedemption,'no');
                        RegCash = parseFloat(json.total_rows.TotalRegCash) ;
                        RegTicket = parseFloat(json.total_rows.TotalRegTicket);
                        RegCoupon = parseFloat(json.total_rows.TotalRegCoupon);
                        CashierRedemption = parseFloat(json.total_rows.TotalCashierRedemption);

                        for(i=0;i<json.ticketlist.length;i++) {
                            if(i == 0){
                                if(json.ticketlist > 1){
                                    TotalEncashedTickets = parseFloat(json.ticketlist[i].EncashedTickets);
                                    SubEncashedTickets = parseFloat(json.ticketlist[i].EncashedTickets);
                                } else {
                                    TotalEncashedTickets = toMoney(json.ticketlist[i].EncashedTickets,'no');
                                    SubEncashedTickets = parseFloat(json.ticketlist[i].EncashedTickets,'no');
                                }
                            } else {
                                TotalEncashedTickets += parseFloat(json.ticketlist[i].EncashedTickets);
                                SubEncashedTickets += parseFloat(json.ticketlist[i].EncashedTickets);
                            }
                        }

                        eSAFECash = parseFloat(json.eWalletCashDeposits);
                        eSAFETickets = parseFloat(json.eWalletTicketDeposits);
                        eSAFECoupon = parseFloat(json.eWalletCouponDeposits);
                        TotaleSAFECash = toMoney(json.eWalletCashDeposits,'no');
                        TotaleSAFETickets = toMoney(json.eWalletTicketDeposits,'no');
                        TotaleSAFECoupon = toMoney(json.eWalletCouponDeposits,'no');

                        Sales = RegCash + RegTicket + RegCoupon + eSAFECash + eSAFETickets + eSAFECoupon;
                        TotalSales = toMoney(Sales,'no');

                        //Compute Cash On Hand [ Formula: (((Total Cash from Cashier & Genesis + eSAFE Cash Load) - (Total Cashier Redemption + eSAFE Withdraw)) - Total Encashed Tickets) - Total Manual Redemption ]
                        CompCashOnHand = ((RegCash + eSAFECash) - (CashierRedemption + parseFloat(json.eWalletWithdrawals)) - SubEncashedTickets);
                        CashOnHand = toMoney(CompCashOnHand,'no');

                        updateLightbox( '<div style="margin-bottom: 10px; font-weight: bold;"> Sales </div><table id="salesctranssumm" ><tr><td style="text-align: center"><b>Non e-SAFE Cash</b></td><td style="text-align: right;">'+TotalRegCash+'</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>Non e-SAFE Tickets</b></td><td style="text-align: right;">' + TotalRegTicket+ '</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>Non e-SAFE Coupons</b></td><td style="text-align: right;">' + TotalRegCoupon+ '</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>e-SAFE Cash Deposits</b></td><td style="text-align: right;">' + TotaleSAFECash+ '</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>e-SAFE Ticket Deposits</b></td><td style="text-align: right;">' + TotaleSAFETickets+ '</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>e-SAFE Coupon Deposits</b></td><td style="text-align: right;">' + TotaleSAFECoupon+ '</td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Sales</b></td><td style="text-align: right;">' + TotalSales+ '</td>' +
                                                    '</tr><tr><td colspan="2" style="padding-top: 10px;padding-bottom:10px;"></td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Encashed Tickets</b></td><td style="text-align: right;">' + TotalEncashedTickets+ '</td>' +
                                                    '</tr><tr><td colspan="2" style="padding-top: 10px;padding-bottom:10px;"></td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Cash On Hand</b></td><td style="text-align: right;">' + CashOnHand+ '</td>' +
                                                    '</tr></table>' +
                                                    '<br /><center><input type="button" style="width: 60px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                    ''          
                    ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
        
    });
    
    $('#btntranshistpervc').live('click',function(){
        var url = $('#ReportsFormModel_reports_type').val();
        var data = $('#frmtranshist').serialize();
        
        var totaldeposit = 0;
        var totalreload = 0;
        var totalwithdraw = 0;

        var totalgrosshold = 0;
        
        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var tbody = '';
                        var json = $.parseJSON(data);
                        
                            tbody+='<tr>';
                            tbody+='<th style=" width: 12%">Terminal#</th>';
                            tbody+='<th style=" width: 14%">Login</th>';
                            tbody+='<th style=" width: 14%">Logout</th>';
                            
                            tbody+='<th colspan="3"></th>';
                            tbody+='<th colspan="3"></th>';
                            tbody+='<th colspan="2"></th>';
                            
                            tbody+='<th></th>';
                            tbody+='</tr>';
                            tbody+='<tr><td>&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                            //alert(json.rows[7].TransactionSummaryID);
                        for(i=0;i<json.rows.length;i++) {
                            
                            totaldeposit += parseFloat(json.rows[i].TotalCTransDeposit);
                            totaldeposit += parseFloat(json.rows[i].eWalletDeposits);
                            totalreload += parseFloat(json.rows[i].TotalCTransReload);
                            totalwithdraw += parseFloat(json.rows[i].TotalCTransRedemption);
                            totalwithdraw += parseFloat(json.rows[i].eWalletWithdrawals);
                            
                            var grosshold = (parseFloat(json.rows[i].TotalCTransDeposit) + parseFloat(json.rows[i].eWalletDeposits) + parseFloat(json.rows[i].TotalCTransReload)) - (parseFloat(json.rows[i].TotalCTransRedemption) + parseFloat(json.rows[i].eWalletWithdrawals));
                            
                            tbody+='<tr>';
                            tbody+='<td align="center"><b><a id="termvctranssumm" style="color: black;" href="#" tcode="'+ json.rows[i].TerminalCode +'" transSummID="'+ json.rows[i].TransactionSummaryID +'" tID="'+ json.rows[i].TerminalID +'" >' + json.rows[i].TerminalCode + '</a></b></td>';
                            tbody+='<td align="center">' + formatDateAMPM(removeMillisec(json.rows[i].DateStarted)) + '</td>';
                            tbody+='<td align="center">' + ((json.rows[i].DateEnded == 0)?'Still playing ...':formatDateAMPM(removeMillisec(json.rows[i].DateEnded))) + '</td>';
                            
                            tbody+='<td colspan="3" class="right">' + toMoney((parseFloat(json.rows[i].TotalCTransDeposit) + parseFloat(json.rows[i].eWalletDeposits)),'no') + '</td>';
                            tbody+='<td colspan="3" class="right">' + toMoney(json.rows[i].TotalCTransReload,'no') + '</td>';
                            tbody+='<td colspan="2" class="right">' + toMoney((parseFloat(json.rows[i].TotalCTransRedemption) + parseFloat(json.rows[i].eWalletWithdrawals)),'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(grosshold,'no') + '</td>';
                            
                            tbody+='</tr>';
                            totalgrosshold += grosshold;
                        }

                        $('#coverage').html(json.coverage);
                        $('#tbltranshistorybody').html('');
                        
                        if(json.rows.length > 0) {
                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<th style="background-color:#BCBCBA"><b><a id="totvctranssumm" style="color: black;" href="#" >Total</a></b></th>';
                            tbody+='<td style="background-color:#BCBCBA"></td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totaldeposit,'no') +'</td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totalreload,'no') +'</td>';
                            tbody+='<td class="right" colspan="2" style="background-color:#BCBCBA">'+ toMoney(totalwithdraw,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(totalgrosshold,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr><td colspan="12"><b><a id="transVCSiteSumm" href="#" style="text-decoration: underline ;color: black;">Click here to view the summary breakdown</a></b></td></tr> ';
                        
                            $('#tbltranshistorybody').html(tbody);
                        }
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
    });
    
    $('#termvctranssumm').live('click',function(){
        $("#ReportsFormModel_terminal_id").val($(this).attr("tID"));
        $("#ReportsFormModel_trans_sum_id").val($(this).attr("transSummID"));
        
        var data = $('#frmtranshist').serialize();
        var tcode = $(this).attr("tcode");
        var url =  $("#terminalidvclink").val();

        var DepCash = 0;
        var DepTicket = 0;
        var DepCoupon = 0;

        var RelCash = 0;
        var RelCoupon = 0;
        var RelTicket = 0;

        var RedCashier = 0;
        var RedGenesis = 0;
        
        var Totgrosshold = 0;
        var TotalDeposit = 0;
        var TotalReload = 0;
        var TotalRedemption = 0;
        var TotalDepCash = 0;
        var TotalDepTicket = 0;
        var TotalGenRedemption = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);
                        //alert(json.rows);
                        for(i=0;i<json.rows.length;i++) {
                            var grosshold = 0;
                            if(i == 0){
                                if(json.rows > 1){
                                    DepCash = parseFloat(json.rows[i].DCash) ;
                                    DepTicket = parseFloat(json.rows[i].DTicket);
                                    DepCoupon = parseFloat(json.rows[i].DCoupon);

                                    RelCash = parseFloat(json.rows[i].RCash);
                                    RelTicket = parseFloat(json.rows[i].RTicket);
                                    RelCoupon = parseFloat(json.rows[i].RCoupon);

                                    RedCashier = parseFloat(json.rows[i].WCashier);
                                    RedGenesis = parseFloat(json.rows[i].WGenesis);
                                    
                                    TotalDeposit += parseFloat(json.rows[i].DCash);
                                    TotalDeposit += parseFloat(json.rows[i].DTicket);
                                    TotalDeposit += parseFloat(json.rows[i].DCoupon);
                                    
                                    TotalReload += parseFloat(json.rows[i].RCash);
                                    TotalReload += parseFloat(json.rows[i].RTicket);
                                    TotalReload += parseFloat(json.rows[i].RCoupon);
                                    
                                    TotalRedemption += parseFloat(json.rows[i].WCashier);
                                    TotalRedemption += parseFloat(json.rows[i].WGenesis);
                                    
                                } else {
                                    DepCash = parseFloat(json.rows[i].DCash);
                                    DepTicket = parseFloat(json.rows[i].DTicket);
                                    DepCoupon = toMoney(json.rows[i].DCoupon,'no');

                                    RelCash = toMoney(json.rows[i].RCash,'no');
                                    RelTicket = toMoney(json.rows[i].RTicket,'no');
                                    RelCoupon = toMoney(json.rows[i].RCoupon,'no');

                                    RedCashier = toMoney(json.rows[i].WCashier,'no');
                                    RedGenesis = parseFloat(json.rows[i].WGenesis);
                                    
                                    TotalDeposit += parseFloat(json.rows[i].DCash);
                                    TotalDeposit += parseFloat(json.rows[i].DTicket);
                                    TotalDeposit += parseFloat(json.rows[i].DCoupon);
                                    
                                    TotalReload += parseFloat(json.rows[i].RCash);
                                    TotalReload += parseFloat(json.rows[i].RTicket);
                                    TotalReload += parseFloat(json.rows[i].RCoupon);
                                    
                                    TotalRedemption += parseFloat(json.rows[i].WCashier);
                                    TotalRedemption += parseFloat(json.rows[i].WGenesis);
                                }
                                
                            } else {
                                DepCash += parseFloat(json.rows[i].DCash) ;
                                DepTicket += parseFloat(json.rows[i].DTicket);
                                DepCoupon += parseFloat(json.rows[i].DCoupon);

                                RelCash += parseFloat(json.rows[i].RCash);
                                RelTicket += parseFloat(json.rows[i].RTicket);
                                RelCoupon += parseFloat(json.rows[i].RCoupon);

                                RedCashier += parseFloat(json.rows[i].WCashier);
                                RedGenesis += parseFloat(json.rows[i].WGenesis);
                                
                                TotalDeposit += parseFloat(json.rows[i].DCash);
                                TotalDeposit += parseFloat(json.rows[i].DTicket);
                                TotalDeposit += parseFloat(json.rows[i].DCoupon);

                                TotalReload += parseFloat(json.rows[i].RCash);
                                TotalReload += parseFloat(json.rows[i].RTicket);
                                TotalReload += parseFloat(json.rows[i].RCoupon);

                                TotalRedemption += parseFloat(json.rows[i].WCashier);
                                TotalRedemption += parseFloat(json.rows[i].WGenesis);
                            }

                        }
                        
                        grosshold = (TotalDeposit + TotalReload + parseFloat(json.eWalletCashDeposits) + parseFloat(json.eWalletTicketDeposits)) - (TotalRedemption + parseFloat(json.eWalletWithdrawals));
                        Totgrosshold = toMoney(grosshold,'no');
                        
                        TotalDepCash = toMoney((DepCash + parseFloat(json.eWalletCashDeposits)) ,'no');
                        TotalDepTicket = toMoney((DepTicket + parseFloat(json.eWalletTicketDeposits)),'no') ;
                        TotalGenRedemption = toMoney((RedGenesis + parseFloat(json.eWalletWithdrawals)),'no') ;
                        
                        updateLightbox( '<div style="margin-bottom: 10px; font-weight: bold;">Terminal #: '+ tcode +'</div><table id="terminalvctranssumm" ><tr><td><b>Deposit</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cash</td><td style="text-align: right;">' + TotalDepCash+ '</td>' +
                                                    '</tr><tr><td></td><td>Ticket</td><td style="text-align: right;">' + TotalDepTicket+ '</td>' +
                                                    '</tr><tr><td></td><td>Coupon</td><td style="text-align: right;">' + DepCoupon+ '</td>' +
                                                    '</tr><tr><td><b>Reload</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cash</td><td style="text-align: right;">' + RelCash+ '</td>' +
                                                    '</tr><tr><td></td><td>Ticket</td><td style="text-align: right;">' + RelTicket+ '</td>' +
                                                    '</tr><tr><td></td><td>Coupon</td><td style="text-align: right;">' + RelCoupon+ '</td>' +
                                                    '</tr><tr><td><b>Redemption</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cashier</td><td style="text-align: right;">' + RedCashier+ '</td>' +
                                                    '</tr><tr><td></td><td>Genesis</td><td style="text-align: right;">' + TotalGenRedemption+ '</td>' +
                                                    '</tr><tr><td><b>Grosshold</b></td><td></td><td style="text-align: right; font-weight: bold;">' + Totgrosshold + '</td>' +
                                                    '</tr></table>' +
                                                    '<br /><center><input type="button" style="width: 60px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                    ''          
                        ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
        
    });
    
        $('#totvctranssumm').live('click',function(){
        
        var data = $('#frmtranshist').serialize();
        var url =  $("#totalvctranslink").val();

        /*--- SUBTOTAL ---*/

        var SubDepCash = 0;
        var SubDepTicket = 0;
        var SubDepCoupon = 0;

        var SubRelCash = 0;
        var SubRelTicket = 0;
        var SubRelCoupon = 0;
        
        var SubRedCashier = 0;
        var SubRedGenesis = 0;

        var SubGrosshold = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);

                        for(i=0;i<json.total_rows.length;i++) {
                            SubDepCash += parseFloat(json.total_rows[i].DCash);
                            SubDepTicket += parseFloat(json.total_rows[i].DTicket);
                            SubDepCoupon += parseFloat(json.total_rows[i].DCoupon);

                            SubRelCash += parseFloat(json.total_rows[i].RCash);
                            SubRelTicket += parseFloat(json.total_rows[i].RTicket);
                            SubRelCoupon += parseFloat(json.total_rows[i].RCoupon);

                            SubRedCashier += parseFloat(json.total_rows[i].WCashier);
                            SubRedGenesis += parseFloat(json.total_rows[i].WGenesis);
                        }
                        
                        SubDepCash += parseFloat(json.eWalletCashDeposits);
                        SubDepTicket += parseFloat(json.eWalletTicketDeposits);
                        SubRedGenesis += parseFloat(json.eWalletWithdrawals);

                        SubGrosshold = ((SubDepCash + SubDepTicket + SubDepCoupon)+(SubRelCash + SubRelTicket + SubRelCoupon))-(SubRedCashier + SubRedGenesis);
                        
                        updateLightbox( '<div style="margin-bottom: 10px; font-weight: bold;">Total: </div><table id="totalvctranssumm" ><tr><td><b>Deposit</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cash</td><td style="text-align: right;">' + toMoney(SubDepCash,'no')+ '</td>' +
                                                    '</tr><tr><td></td><td>Ticket</td><td style="text-align: right;">' + toMoney(SubDepTicket,'no')+ '</td>' +
                                                    '</tr><tr><td></td><td>Coupon</td><td style="text-align: right;">' + toMoney(SubDepCoupon,'no')+ '</td>' +
                                                    '</tr><tr><td><b>Reload</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cash</td><td style="text-align: right;">' + toMoney(SubRelCash,'no')+ '</td>' +
                                                    '</tr><tr><td></td><td>Ticket</td><td style="text-align: right;">' + toMoney(SubRelTicket,'no')+ '</td>' +
                                                    '</tr><tr><td></td><td>Coupon</td><td style="text-align: right;">' + toMoney(SubRelCoupon,'no')+ '</td>' +
                                                    '</tr><tr><td><b>Redemption</b></td><td></td><td></td>' +
                                                    '</tr><tr><td></td><td>Cashier</td><td style="text-align: right;">' + toMoney(SubRedCashier,'no')+ '</td>' +
                                                    '</tr><tr><td></td><td>Genesis</td><td style="text-align: right;">' + toMoney(SubRedGenesis,'no')+ '</td>' +
                                                    '</tr><tr><td><b>Grosshold</b></td><td></td><td style="text-align: right; font-weight: bold;">' + toMoney(SubGrosshold,'no') + '</td>' +
                                                    '</tr></table>' +
                                                    '<br /><center><input type="button" style="width: 60px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                    ''          
                        ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
        
    });
    
    $('#transVCSiteSumm').live('click',function(){
        var data = $('#frmtranshist').serialize();
        var url =  $("#salesvctranslink").val();

        var RegCash = 0;
        var RegTicket = 0;
        var CashierRedemption = 0;
        var TotalRegCash = 0;
        var TotalRegTicket = 0;

        var eSAFECash = 0;
        var eSAFETickets = 0;
        var TotaleSAFECash = 0;
        var TotaleSAFETickets = 0;

        var Sales = 0;
        var TotalSales = 0;
        var TotalPrintedTickets = 0;
        var TotalActiveTicketsForTheDay = 0;
        var TotalActiveRunningTickets = 0;
        
        var CompCashOnHand = 0;
        var CashOnHand = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);

                        TotalRegCash = toMoney(json.total_rows.TotalRegCash,'no');
                        TotalRegTicket = toMoney(json.total_rows.TotalRegTicket,'no');
                        TotalCashierRedemption = toMoney(json.total_rows.TotalCashierRedemption,'no');
                        RegCash = parseFloat(json.total_rows.TotalRegCash) ;
                        RegTicket = parseFloat(json.total_rows.TotalRegTicket);
                        CashierRedemption = parseFloat(json.total_rows.TotalCashierRedemption);

                        for(i=0;i<json.ticketlist.length;i++) {
                            if(i == 0){
                                if(json.ticketlist > 1){
                                    TotalPrintedTickets = parseFloat(json.ticketlist[i].PrintedRedemptionTickets) ;
                                    TotalActiveTicketsForTheDay = parseFloat(json.ticketlist[i].UnusedTickets) ;
                                } else {
                                    TotalPrintedTickets = toMoney(json.ticketlist[i].PrintedRedemptionTickets,'no');
                                    TotalActiveTicketsForTheDay = toMoney(json.ticketlist[i].UnusedTickets,'no');
                                }
                            } else {
                                TotalPrintedTickets += parseFloat(json.ticketlist[i].PrintedRedemptionTickets) ;
                                TotalActiveTicketsForTheDay += parseFloat(json.ticketlist[i].UnusedTickets) ;
                            }
                        }

                        eSAFECash = parseFloat(json.eWalletCashDeposits);
                        eSAFETickets = parseFloat(json.eWalletTicketDeposits);
                        TotaleSAFECash = toMoney(json.eWalletCashDeposits,'no');
                        TotaleSAFETickets = toMoney(json.eWalletTicketDeposits,'no');

                        Sales = RegCash + RegTicket + eSAFECash + eSAFETickets;
                        TotalSales = toMoney(Sales,'no');
                        TotalActiveRunningTickets = toMoney(json.runningactivetickets,'no');

                        //Compute Cash On Hand [ Formula: (((Total Cash from Cashier & Genesis + eSAFE Cash Load) - (Total Cashier Redemption + eSAFE Withdraw)) - Total Encashed Tickets) - Total Manual Redemption ]
                        CompCashOnHand = (RegCash + eSAFECash) - (CashierRedemption + parseFloat(json.eWalletWithdrawals)) - parseFloat(json.manualredemptions);
                        CashOnHand = toMoney(CompCashOnHand,'no');

                        updateLightbox( '<div style="margin-bottom: 10px; font-weight: bold;"> Sales </div><table id="salesvctranssumm" ><tr><td style="text-align: center"><b>Non e-SAFE Cash</b></td><td style="text-align: right;">'+TotalRegCash+'</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>Non e-SAFE Tickets</b></td><td style="text-align: right;">' + TotalRegTicket+ '</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>e-SAFE Cash Deposits</b></td><td style="text-align: right;">' + TotaleSAFECash+ '</td>' +
                                                    '</tr><tr><td style="text-align: center"><b>e-SAFE Ticket Deposits</b></td><td style="text-align: right;">' + TotaleSAFETickets+ '</td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Sales</b></td><td style="text-align: right;">' + TotalSales+ '</td>' +
                                                    '</tr><tr><td colspan="2" style="padding-top: 10px;padding-bottom:10px;"></td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Printed Tickets</b></td><td style="text-align: right;">' + TotalPrintedTickets+ '</td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Active Tickets For The Day</b></td><td style="text-align: right;">' + TotalActiveTicketsForTheDay+ '</td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Total Active Running Tickets</b></td><td style="text-align: right;">' + TotalActiveRunningTickets+ '</td>' +
                                                    '</tr><tr><td colspan="2" style="padding-top: 10px;padding-bottom:10px;"></td>' +
                                                    '</tr><tr><td style="padding-left:5px;"><b>Cash On Hand</b></td><td style="text-align: right;">' + CashOnHand+ '</td>' +
                                                    '</tr></table>' +
                                                    '<br /><center><input type="button" style="width: 60px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                    ''          
                    ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
        
    });
    
    $('#btnEWalletPerSite').live('click',function(){
        var url = $('#ReportsFormModel_reports_type').val();
        var data = $('#frmtranshist').serialize();
        
        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(result) {
                    var totalDeposit = 0;
                    var totalWithdrawal = 0;
                    
                    try {
                        var obj = JSON.parse(result);
                        var data = obj['data'];
                        var coverage = obj['coverage'];
                        var tbody = '';
                        var cashOnHand = obj['cashOnHand'];
                        try{
                            for(var i=0;i<data.length;i++){
                                var transactionDetails = {'':'', 'D':'Deposit', 'W':'Withdraw'};
                                var values = data[i];
                                var cardNumber = values['LoyaltyCardNumber'];
                                var date = values['StartDate'];
                                var amount = parseFloat(values['Amount']);
                                var transType = values['TransType'];
                                var transactionType = transactionDetails[transType];
                                
                                if(transType=='D'){totalDeposit+=amount;}
                                else if(transType=='W'){totalWithdrawal+=amount;}
                               

                                tbody+='<tr>';
                                tbody+='<td style="text-align: center;">'+cardNumber+'</td>';
                                tbody+='<td style="text-align: center;">'+formatDateAMPM(removeMillisec(date))+'</td>';
                                tbody+='<td style="text-align:right;">'+toMoney(amount, 'no')+'</td>';
                                tbody+='<td style="text-align:right;">'+transactionType+'</td>';
                                tbody+='</tr>';
                            }
                        }catch(e){}
                        
                        tbody+='<tr style="height:30px;"> <td colspan="4"></td></tr>';
                        tbody+='<tr style="height:30px;"><td colspan="4"><b><a id="eSAFETransSiteSumm" href="#" style="text-decoration: underline ;color: black;">Click here to view the summary breakdown</a></b></td></tr>';
//                        tbody+='<tr><td><b>Total Deposits</b></td>';
//                        tbody+='<td colspan="3">'+toMoney(totalDeposit)+'</td>';
//                        tbody+='</tr>';
//                        tbody+='<tr><td><b>Total Withdrawals</b></td>';
//                        tbody+='<td colspan="3">'+toMoney(totalWithdrawal)+'</td>';
//                        tbody+='</tr>';
//                        tbody+='<tr><td><b>Cash on Hand</b></td>';
//                        tbody+='<td colspan="3">'+toMoney(cashOnHand)+'</td>';
//                        tbody+='</tr>';
                        
                        $("#totaldeposit").val(toMoney(totalDeposit));
                        $("#totalwithdrawals").val(toMoney(totalWithdrawal));
                        $("#cashonhand").val(toMoney(cashOnHand));
                        $("#tbltranshistorybody").html(tbody);
                        $("#coverage").html(coverage);
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
    });
    
    $('#eSAFETransCashierSumm').live('click',function(){
        var data = $('#frmtranshist').serialize();
        var url =  $("#totalesafetranslinkpercsh").val();

        var TDeposit = 0;
        var TWithdrawal = 0;
        var TotalDeposit = 0;
        var TotalWithdrawal = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);

                        for(i=0;i<json.data.length;i++) {
                            switch(json.data[i].TransType){
                                case 'D':
                                    TDeposit += parseFloat(json.data[i].Amount);
                                    break;
                                case 'W':
                                    TWithdrawal += parseFloat(json.data[i].Amount);
                                    break;
                            }
                        }

                        TotalDeposit = toMoney(TDeposit,'no');
                        TotalWithdrawal = toMoney(TWithdrawal,'no');

                        updateLightbox( '<table id="esafesummtable" ><tr><td>Total Deposits</td><td style="text-align:right;"> ' + TotalDeposit +
                                                        '</td></tr><tr><td>Total Withdrawals</td><td style="text-align:right;"> ' + TotalWithdrawal + '</td>' +
                                                        '</tr><tr><td>Cash On Hand</td><td style="text-align:right;"> ' + toMoney(json.cashOnHand,'no') + '</td>' +
                                                        '</tr></table>' +
                                                        '<br /><center><input type="button" style="width: 50px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                        ''          
                        ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
    });
    
    $('#eSAFETransSiteSumm').live('click',function(){
        var data = $('#frmtranshist').serialize();
        var url =  $("#totalesafetranslinkpersite").val();

        var TDeposit = 0;
        var TWithdrawal = 0;
        var TotalDeposit = 0;
        var TotalWithdrawal = 0;

        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);

                        for(i=0;i<json.data.length;i++) {
                            switch(json.data[i].TransType){
                                case 'D':
                                    TDeposit += parseFloat(json.data[i].Amount);
                                    break;
                                case 'W':
                                    TWithdrawal += parseFloat(json.data[i].Amount);
                                    break;
                            }
                        }

                        TotalDeposit = toMoney(TDeposit,'no');
                        TotalWithdrawal = toMoney(TWithdrawal,'no');

                        updateLightbox( '<table id="esafesummtable" ><tr><td>Total Deposits</td><td style="text-align:right;"> ' + TotalDeposit +
                                                        '</td></tr><tr><td>Total Withdrawals</td><td style="text-align:right;"> ' + TotalWithdrawal + '</td>' +
                                                        '</tr><tr><td>Cash On Hand</td><td style="text-align:right;"> ' + toMoney(json.cashOnHand,'no') + '</td>' +
                                                        '</tr></table>' +
                                                        '<br /><center><input type="button" style="width: 50px; height: 25px;"  value="Close" class="btnClose" /></center>',
                                                        ''          
                        ); 
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
    });
    
    $('#btnEWalletPerCashier').live('click',function(){
        var url = $('#ReportsFormModel_reports_type').val();
        var data = $('#frmtranshist').serialize();
        
        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(result) {
                    
                    try {
                        var obj = JSON.parse(result);
                        var data = obj['data'];
                        var coverage = obj['coverage'];
                        var tbody = '';
                        try{
                            for(var i=0;i<data.length;i++){
                                var transactionDetails = {'':'', 'D':'Deposit', 'W':'Withdraw'};
                                var values = data[i];
                                var cardNumber = values['LoyaltyCardNumber'];
                                var date = values['StartDate'];
                                var amount = parseFloat(values['Amount']);
                                var transType = values['TransType'];
                                var transactionType = transactionDetails[transType];

                                tbody+='<tr>';
                                tbody+='<td style="text-align: center;">'+cardNumber+'</td>';
                                tbody+='<td style="text-align: center;">'+formatDateAMPM(removeMillisec(date))+'</td>';
                                tbody+='<td style="text-align:right;">'+toMoney(amount,'no')+'</td>';
                                tbody+='<td style="text-align:right;">'+transactionType+'</td>';
                                tbody+='</tr>';
                                
                            }
                        }catch(e){}
                        
                        tbody+='<tr style="height:30px;"> <td colspan="4"></td></tr>';
                        tbody+='<tr style="height:30px;"><td colspan="4"><b><a id="eSAFETransCashierSumm" href="#" style="text-decoration: underline ;color: black;">Click here to view the summary breakdown</a></b></td></tr>';
                        
                        $("#tbltranshistorybody").html(tbody);
                        $("#coverage").html(coverage);
                        
                    } catch(e) {
                        alert('Oops! Something went wrong');
                    }
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
    });

});