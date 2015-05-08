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
        var regdepositcash = 0;
        var regdepositticket = 0;
        var regdepositcoupon = 0;

        var regreloadcash = 0;
        var regreloadticket = 0;
        var regreloadcoupon = 0;

        var gendepositcash = 0;
        var gendepositticket = 0;
        var gendepositcoupon = 0;

        var genreloadcash = 0;
        var genreloadticket = 0;
        var genreloadcoupon = 0;


        var withdrawalcashier2 = 0;
        var withdrawalgenesis2 = 0;
        
        var grosholdregular = 0;
        var grosholdgenesis = 0;

        /*--- SUBTOTAL ---*/

        var subtotaldcash = 0;
        var subtotaldticket = 0;
        var subtotaldcoupon = 0;

        var subtotalrcash = 0;
        var subtotalrticket = 0;
        var subtotalrcoupon = 0;

        var subtotalgrosshold = 0;


        /*--- TOTAL ---*/

        var totaldeposit = 0;
        var totalreload = 0;
        var totalwithdraw = 0;

        var totalgrosshold = 0;

        /*--- SALES ---*/

        var totalcash = 0;
        var totaltickets = 0;
        var totalcoupons = 0;

        var totalsales = 0;
        
        var cashonhand = 0;
        var eWalletDeposit = 0;
        var eWalletWithdrawal = 0;
        
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
                            
                            tbody+='<th>Cash</th>';
                            tbody+='<th>Ticket</th>';
                            tbody+='<th>Coupon</th>';
                            
                            tbody+='<th>Cash</th>';
                            tbody+='<th>Ticket</th>';
                            tbody+='<th>Coupon</th>';
                            
                            tbody+='<th>Cashier</th>';
                            tbody+='<th>Genesis</th>';
                            
                            tbody+='<th></th>';
                            tbody+='</tr>';
                            tbody+='<tr><td>&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                        for(i=0;i<json.rows.length;i++) {
                            var grosshold = ((parseFloat(json.rows[i].DCash) + parseFloat(json.rows[i].DTicket) + parseFloat(json.rows[i].DCoupon))+(parseFloat(json.rows[i].RCash) + parseFloat(json.rows[i].RTicket) + parseFloat(json.rows[i].RCoupon)))-(parseFloat(json.rows[i].WCashier) + parseFloat(json.rows[i].WGenesis));
                            
                            tbody+='<tr>';
                            tbody+='<td align="center">' + json.rows[i].TerminalCode + '</td>';
                            tbody+='<td align="center">' + formatDateAMPM(removeMillisec(json.rows[i].DateStarted)) + '</td>';
                            tbody+='<td align="center">' + ((json.rows[i].DateEnded == 0)?'Still playing ...':formatDateAMPM(removeMillisec(json.rows[i].DateEnded))) + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(json.rows[i].DCash,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].DTicket,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].DCoupon,'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(json.rows[i].RCash,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].RTicket,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].RCoupon,'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(json.rows[i].WCashier,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].WGenesis,'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(grosshold,'no') + '</td>';
                            
                            
                            //tbody+='<td class="right">' + toMoney(parseFloat(toInt(json.rows[i].Deposit)) + parseFloat(toInt(json.rows[i].Reload)) - parseFloat(toInt(json.rows[i].Withdrawal)),'no') + '</td>';
                            tbody+='</tr>';
//                            page_total_deposit = parseFloat(page_total_deposit) + parseFloat(toInt(json.rows[i].Deposit));
//                            page_total_reload = parseFloat(page_total_reload) + parseFloat(toInt(json.rows[i].Reload));
//                            page_total_redemption = parseFloat(page_total_redemption) + parseFloat(toInt(json.rows[i].Withdrawal));
                            //page_total_gross_hold+=parseFloat(page_total_deposit) + parseFloat(page_total_reload) - parseFloat(page_total_redemption);
                        }
                        
                        for(i=0;i<json.total_rows.length;i++) {
                            regdepositcash += parseFloat(json.total_rows[i].RegDCash);
                            regdepositticket += parseFloat(json.total_rows[i].RegDTicket);
                            regdepositcoupon += parseFloat(json.total_rows[i].RegDCoupon);

                            regreloadcash += parseFloat(json.total_rows[i].RegRCash);
                            regreloadticket += parseFloat(json.total_rows[i].RegRTicket);
                            regreloadcoupon += parseFloat(json.total_rows[i].RegRCoupon);

                            gendepositcash += parseFloat(json.total_rows[i].GenDCash);
                            gendepositticket += parseFloat(json.total_rows[i].GenDTicket);
                            gendepositcoupon += parseFloat(json.total_rows[i].GenDCoupon);

                            genreloadcash += parseFloat(json.total_rows[i].GenRCash);
                            genreloadticket += parseFloat(json.total_rows[i].GenRTicket);
                            genreloadcoupon += parseFloat(json.total_rows[i].GenRCoupon);

                            withdrawalcashier2 += parseFloat(json.total_rows[i].WCashier);
                            withdrawalgenesis2 += parseFloat(json.total_rows[i].WGenesis);
                        }
                        
                        grosholdregular = ((regdepositcash + regdepositticket + regdepositcoupon)+(regreloadcash + regreloadticket + regreloadcoupon))-(withdrawalcashier2);
                        grosholdgenesis = ((gendepositcash + gendepositticket + gendepositcoupon)+(genreloadcash + genreloadticket + genreloadcoupon))-(withdrawalgenesis2);

                        /*--- SUBTOTAL ---*/
                        
                        subtotaldcash = regdepositcash + gendepositcash;
                        subtotaldticket = regdepositticket + gendepositticket;
                        subtotaldcoupon = regdepositcoupon + gendepositcoupon;

                        subtotalrcash = regreloadcash + genreloadcash;
                        subtotalrticket = regreloadticket + genreloadticket;
                        subtotalrcoupon = regreloadcoupon + genreloadcoupon;

                        subtotalgrosshold = grosholdregular + grosholdgenesis;


                        /*--- TOTAL ---*/
                        eWalletDeposit = parseFloat(json.eWalletDeposits);
                        eWalletWithdrawal = parseFloat(json.eWalletWithdrawals);
                        totaldeposit = subtotaldcash + subtotaldticket + subtotaldcoupon;
                        totalreload = subtotalrcash + subtotalrticket + subtotalrcoupon;
                        totalwithdraw = withdrawalcashier2 + withdrawalgenesis2;

                        totalgrosshold = subtotalgrosshold;

                        /*--- SALES ---*/

                        totalcash = subtotaldcash + subtotalrcash;
                        totaltickets = subtotaldticket + subtotalrticket;
                        totalcoupons = subtotaldcoupon + subtotalrcoupon;

                        totalsales = totalcash + totaltickets + totalcoupons + eWalletDeposit;
       
                        cashonhand = totalcash - (parseFloat(withdrawalcashier2) + parseFloat(json.manualredemptions) + parseFloat(json.ticketlist[0].EncashedTickets))+(eWalletDeposit - eWalletWithdrawal);
                        
                        
                        $('#coverage').html(json.coverage);
                        $('#tbltranshistorybody').html('');

                        if(json.rows.length > 0) {
//                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<th rowspan="3" style="background-color:#BCBCBA">Breakdown</th>';
                            tbody+='<th align="center" style="background-color:#BCBCBA">Regular</th>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(regdepositcash,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(regdepositticket,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(regdepositcoupon,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(regreloadcash,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(regreloadticket,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(regreloadcoupon,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(withdrawalcashier2,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(0,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(grosholdregular,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<th align="center" style="background-color:#BCBCBA">Genesis</th>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(gendepositcash,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(gendepositticket,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(gendepositcoupon,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(genreloadcash,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(genreloadticket,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(genreloadcoupon,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(0,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(withdrawalgenesis2,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(grosholdgenesis,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<th align="center" style="background-color:#BCBCBA">Subtotal</th>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(subtotaldcash,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(subtotaldticket,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(subtotaldcoupon,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(subtotalrcash,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(subtotalrticket,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(subtotalrcoupon,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(withdrawalcashier2,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(withdrawalgenesis2,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(subtotalgrosshold,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<th style="background-color:#BCBCBA">Total</th>';
                            tbody+='<td style="background-color:#BCBCBA"></td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totaldeposit,'no') +'</td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totalreload,'no') +'</td>';
                            tbody+='<td class="right" colspan="2" style="background-color:#BCBCBA">'+ toMoney(totalwithdraw,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(totalgrosshold,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr> ';
                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Sales</th>';
                            tbody+='<td colspan="1" >&nbsp;&nbsp;&nbsp;</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="center">Cash</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(totalcash,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="center">Tickets</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(totaltickets,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="center">Coupons</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(totalcoupons,'no') +'</td>';
                            tbody+='</tr>';
                            
                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="center">e-wallet Deposits</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(eWalletDeposit,'no') +'</td>';
                            tbody+='</tr>';
                            
                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Sales</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(totalsales,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">&nbsp;&nbsp;</th>';
                            tbody+='<td colspan="1" >&nbsp;&nbsp;&nbsp;</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Printed Tickets</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(json.ticketlist[0].PrintedRedemptionTickets,'no') +'</td>';
                            tbody+='<td style="border:0;">* Through Redemption</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Active Tickets For The Day</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(json.ticketlist[0].UnusedTickets,'no') +'</td>';
                            tbody+='</tr>';

//                            tbody+='<tr>';
//                            tbody+='<th colspan="1" align="left">Total Cancelled Tickets</th>';
//                            tbody+='<td class="right" colspan="1">'+ toMoney(json.ticketlist[0].CancelledTickets,'no') +'</td>';
//                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Encashed Tickets</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(json.ticketlist[0].EncashedTickets,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Active Running Tickets</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(json.runningactivetickets,'no') +'</td>';
                            tbody+='</tr>';
                            
                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">&nbsp;&nbsp;&nbsp;</th>';
                            tbody+='<td colspan="1" >&nbsp;&nbsp;&nbsp;</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Cash on Hand</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(cashonhand,'no') +'</td>';
                            tbody+='</tr>';
                            
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
    
    $('#btntranshistpercash').live('click',function(){
        var url = $('#ReportsFormModel_reports_type').val();
        var data = $('#frmtranshist').serialize();
        
        /*--- SUBTOTAL ---*/

        var depositcash = 0;
        var depositticket = 0;
        var depositcoupon = 0;

        var reloadcash = 0;
        var reloadticket = 0;
        var reloadcoupon = 0;

        var withdrawalcashier = 0;
        var withdrawalgenesis = 0;

        var subtotalcash = 0;
        var subtotalticket = 0;
        var subtotalcoupon = 0;
        
        var subtotaldticket = 0;
        var subtotalrticket = 0;
        
        /*--- TOTAL ---*/

        var totaldeposit = 0;
        var totalreload = 0;
        var totalwithdraw = 0;

        var totalgrosshold = 0;

        /*--- SALES ---*/

        var totalcash = 0;
        var totaltickets = 0;
        var totalcoupons = 0;

        var totalsales = 0;
        
        var cashonhand = 0;
        var eWalletDeposit = 0;
        var eWalletWithdrawal = 0;
        
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
                            
                            tbody+='<th>Cash</th>';
                            tbody+='<th>Ticket</th>';
                            tbody+='<th>Coupon</th>';
                            
                            tbody+='<th>Cash</th>';
                            tbody+='<th>Ticket</th>';
                            tbody+='<th>Coupon</th>';
                            
                            tbody+='<th>Cashier</th>';
                            tbody+='<th>Genesis</th>';
                            
                            tbody+='<th></th>';
                            tbody+='</tr>';
                            tbody+='<tr><td>&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                        for(i=0;i<json.rows.length;i++) {
                            
                            depositcash += parseFloat(json.rows[i].DCash);
                            depositticket += parseFloat(json.rows[i].DTicket);
                            depositcoupon += parseFloat(json.rows[i].DCoupon);

                            reloadcash += parseFloat(json.rows[i].RCash);
                            reloadticket += parseFloat(json.rows[i].RTicket);
                            reloadcoupon += parseFloat(json.rows[i].RCoupon);

                            withdrawalcashier += parseFloat(json.rows[i].WCashier);
                            withdrawalgenesis += parseFloat(json.rows[i].WGenesis);
                            
                            var grosshold = ((parseFloat(json.rows[i].DCash) + parseFloat(json.rows[i].DTicket) + parseFloat(json.rows[i].DCoupon))+(parseFloat(json.rows[i].RCash) + parseFloat(json.rows[i].RTicket) + parseFloat(json.rows[i].RCoupon)))-(parseFloat(json.rows[i].WCashier) - parseFloat(json.rows[i].WGenesis));
                            
                            totalgrosshold += parseFloat(grosshold);
                            
                            tbody+='<tr>';
                            tbody+='<td align="center">' + json.rows[i].TerminalCode + '</td>';
                            tbody+='<td align="center">' + formatDateAMPM(removeMillisec(json.rows[i].DateStarted)) + '</td>';
                            tbody+='<td align="center">' + ((json.rows[i].DateEnded == 0)?'Still playing ...':formatDateAMPM(removeMillisec(json.rows[i].DateEnded))) + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(json.rows[i].DCash,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].DTicket,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].DCoupon,'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(json.rows[i].RCash,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].RTicket,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].RCoupon,'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(json.rows[i].WCashier,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].WGenesis,'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(grosshold,'no') + '</td>';
                            
                            tbody+='</tr>';
//                         
                        }
                       
                        /*--- TOTAL ---*/

                        totaldeposit = depositcash + depositticket + depositcoupon;
                        totalreload = reloadcash + reloadticket + reloadcoupon;
                        totalwithdraw = withdrawalcashier + withdrawalgenesis;

                        totalgrosshold = totalgrosshold;
                        
                        
                        subtotalcash = depositcash + reloadcash;
       
                        subtotalticket = depositticket + reloadticket;
                        
                        subtotalcoupon = depositcoupon + reloadcoupon;

                        /*--- SALES ---*/
                        eWalletDeposit = parseFloat(json.eWalletDeposits);
                        eWalletWithdrawal = parseFloat(json.eWalletWithdrawals);
                        
                        totalcash = subtotalcash;
                        totaltickets = subtotalticket;
                        totalcoupons = subtotalcoupon;

                        totalsales = totalcash + totaltickets + totalcoupons;
                       
                        cashonhand = parseFloat(totalcash) - (parseFloat(withdrawalcashier) + parseFloat(json.ticketlist[0].EncashedTickets)) + (eWalletDeposit - eWalletWithdrawal);
                        
       
                        $('#coverage').html(json.coverage);
                        $('#tbltranshistorybody').html('');
                        
                        if(json.rows.length > 0) {
//                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<td align="center" style="background-color:#BCBCBA">Subtotal</td>';
                            tbody+='<td style="background-color:#BCBCBA"></td>';
                            tbody+='<td align="right" style="background-color:#BCBCBA">'+ toMoney(depositcash,'no') +'</td>';
                            tbody+='<td align="right" style="background-color:#BCBCBA">'+ toMoney(depositticket,'no') +'</td>';
                            tbody+='<td align="right" style="background-color:#BCBCBA">'+ toMoney(depositcoupon,'no') +'</td>';
                            tbody+='<td align="right" style="background-color:#BCBCBA">'+ toMoney(reloadcash,'no') +'</td>';
                            tbody+='<td align="right" style="background-color:#BCBCBA">'+ toMoney(reloadticket,'no') +'</td>';
                            tbody+='<td align="right" style="background-color:#BCBCBA">'+ toMoney(reloadcoupon,'no') +'</td>';
                            tbody+='<td align="right" style="background-color:#BCBCBA">'+ toMoney(withdrawalcashier,'no') +'</td>';
                            tbody+='<td align="right" style="background-color:#BCBCBA">'+ toMoney(withdrawalgenesis,'no') +'</td>';
                            tbody+='<td align="right" style="background-color:#BCBCBA">'+ toMoney(totalgrosshold,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<th style="background-color:#BCBCBA">Total</th>';
                            tbody+='<td style="background-color:#BCBCBA"></td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totaldeposit,'no') +'</td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totalreload,'no') +'</td>';
                            tbody+='<td class="right" colspan="2" style="background-color:#BCBCBA">'+ toMoney(totalwithdraw,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(totalgrosshold,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr> ';
                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Sales</th>';
                            tbody+='<td colspan="1" >&nbsp;&nbsp;&nbsp;</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="center">Cash</th>';
                            tbody+='<td colspan="1" class="right"">'+ toMoney(totalcash,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="center">Tickets</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(totaltickets,'no') +'</td>';
                            tbody+='</tr>';
                            
                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="center">Coupons</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(totalcoupons,'no') +'</td>';
                            tbody+='</tr>';
                            
                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="center">e-wallet Deposits</th>';
                            tbody+='<td class="right" colspan="1">'+ toMoney(eWalletDeposit,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Sales</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(totalsales,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">&nbsp;&nbsp;</th>';
                            tbody+='<td colspan="1" >&nbsp;&nbsp;&nbsp;</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Encashed Tickets</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(json.ticketlist[0].EncashedTickets,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">&nbsp;&nbsp;&nbsp;</th>';
                            tbody+='<td colspan="1" >&nbsp;&nbsp;&nbsp;</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Cash on Hand</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(cashonhand,'no') +'</td>';
                            tbody+='</tr>';
                        
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
    
    $('#btntranshistpervc').live('click',function(){
        var url = $('#ReportsFormModel_reports_type').val();
        var data = $('#frmtranshist').serialize();

        /*--- SUBTOTAL ---*/

        var depositcash = 0;
        var depositticket = 0;
        var depositcoupon = 0;

        var reloadcash = 0;
        var reloadticket = 0;
        var reloadcoupon = 0;

        var withdrawalcashier = 0;
        var withdrawalcashier2 = 0;
        var withdrawalgenesis = 0;

        var subtotalcash = 0;
        var subtotalticket = 0;
        
        var subtotaldticket = 0;
        var subtotalrticket = 0;
        
        /*--- TOTAL ---*/

        var totaldeposit = 0;
        var totalreload = 0;
        var totalwithdraw = 0;

        var totalgrosshold = 0;

        /*--- SALES ---*/

        var totalcash = 0;
        var totaltickets = 0;

        var totalsales = 0;
        
        var cashonhand = 0;
        
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
                            
                            tbody+='<th>Cash</th>';
                            tbody+='<th>Ticket</th>';
                            tbody+='<th>Coupon</th>';
                            
                            tbody+='<th>Cash</th>';
                            tbody+='<th>Ticket</th>';
                            tbody+='<th>Coupon</th>';
                            
                            tbody+='<th>Cashier</th>';
                            tbody+='<th>Genesis</th>';
                            
                            tbody+='<th></th>';
                            tbody+='</tr>';
                            tbody+='<tr><td>&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                        for(i=0;i<json.rows.length;i++) {
                            
                            depositcash += parseFloat(json.rows[i].DCash);
                            depositticket += parseFloat(json.rows[i].DTicket);
                            depositcoupon += parseFloat(json.rows[i].DCoupon);

                            reloadcash += parseFloat(json.rows[i].RCash);
                            reloadticket += parseFloat(json.rows[i].RTicket);
                            reloadcoupon += parseFloat(json.rows[i].RCoupon);

                            withdrawalcashier += parseFloat(json.rows[i].WCashier);
                            withdrawalgenesis += parseFloat(json.rows[i].WGenesis);
                            
                            var grosshold = parseFloat((parseFloat(json.rows[i].DCash) + parseFloat(json.rows[i].DTicket) + parseFloat(json.rows[i].DCoupon))+(parseFloat(json.rows[i].RCash) + parseFloat(json.rows[i].RTicket) + parseFloat(json.rows[i].RCoupon)))-parseFloat(parseFloat(json.rows[i].WCashier) + parseFloat(json.rows[i].WGenesis));
                            
                            totalgrosshold += parseFloat(grosshold);
                            
                            tbody+='<tr>';
                            tbody+='<td align="center">' + json.rows[i].TerminalCode + '</td>';
                            tbody+='<td align="center">' + formatDateAMPM(removeMillisec(json.rows[i].DateStarted)) + '</td>';
                            tbody+='<td align="center">' + ((json.rows[i].DateEnded == 0)?'Still playing ...':formatDateAMPM(removeMillisec(json.rows[i].DateEnded))) + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(json.rows[i].DCash,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].DTicket,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].DCoupon,'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(json.rows[i].RCash,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].RTicket,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].RCoupon,'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(json.rows[i].WCashier,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].WGenesis,'no') + '</td>';
                            
                            tbody+='<td class="right">' + toMoney(grosshold,'no') + '</td>';
                            
                            tbody+='</tr>';

                        }
                        
                        for(i=0;i<json.rows2.length;i++) {
                            withdrawalcashier2 += parseFloat(json.rows2[i].WCashier);
                        }
                       
                        /*--- TOTAL ---*/

                        totaldeposit = depositcash + depositticket + depositcoupon;
                        totalreload = reloadcash + reloadticket + reloadcoupon;
                        totalwithdraw = withdrawalcashier + withdrawalgenesis;

                        totalgrosshold = totalgrosshold;
                        
                        
                        subtotalcash = depositcash + reloadcash;
       
                        subtotalticket = depositticket + reloadticket;

                        /*--- SALES ---*/

                        totalcash = subtotalcash;
                        totaltickets = subtotalticket;

                        totalsales = totalcash + totaltickets;
                        
                        cashonhand = parseFloat(totalcash) - (parseFloat(withdrawalcashier2) + parseFloat(json.manualredemptions));

                        tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                        
                        $('#coverage').html(json.coverage);
                        $('#tbltranshistorybody').html('');
                        
                        if(json.rows.length > 0) {
//                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<td align="center" style="background-color:#BCBCBA">Subtotal</td>';
                            tbody+='<td style="background-color:#BCBCBA"></td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(depositcash,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(depositticket,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(depositcoupon,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(reloadcash,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(reloadticket,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(reloadcoupon,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(withdrawalcashier,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(withdrawalgenesis,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(totalgrosshold,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr>';
                            tbody+='<tr>';
                            tbody+='<td></td>';
                            tbody+='<th style="background-color:#BCBCBA">Total</th>';
                            tbody+='<td style="background-color:#BCBCBA"></td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totaldeposit,'no') +'</td>';
                            tbody+='<td class="right" colspan="3" style="background-color:#BCBCBA">'+ toMoney(totalreload,'no') +'</td>';
                            tbody+='<td class="right" colspan="2" style="background-color:#BCBCBA">'+ toMoney(totalwithdraw,'no') +'</td>';
                            tbody+='<td class="right" style="background-color:#BCBCBA">'+ toMoney(totalgrosshold,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr><td colspan="12">&nbsp;&nbsp;&nbsp;</td></tr> ';
                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Sales</th>';
                            tbody+='<td colspan="1" >&nbsp;&nbsp;&nbsp;</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="center">Cash</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(totalcash,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="center">Tickets</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(totaltickets,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Sales</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(totalsales,'no') +'</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">&nbsp;&nbsp;</th>';
                            tbody+='<td colspan="1" >&nbsp;&nbsp;&nbsp;</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Printed Tickets</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(json.ticketlist[0].PrintedRedemptionTickets,'no') +'</td>';
                            tbody+='<td style="border:0;">* Through Redemption</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Active Tickets For The Day</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(json.ticketlist[0].UnusedTickets,'no') +'</td>';
                            tbody+='</tr>';

//                            tbody+='<tr>';
//                            tbody+='<th colspan="1" align="left">Total Cancelled Tickets</th>';
//                            tbody+='<td colspan="1" class="right">'+ toMoney(json.ticketlist[0].CancelledTickets,'no') +'</td>';
//                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Total Active Running Tickets</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(json.runningactivetickets,'no') +'</td>';
                            tbody+='</tr>';
                            
                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">&nbsp;&nbsp;&nbsp;</th>';
                            tbody+='<td colspan="1" >&nbsp;&nbsp;&nbsp;</td>';
                            tbody+='</tr>';

                            tbody+='<tr>';
                            tbody+='<th colspan="1" align="left">Cash on Hand</th>';
                            tbody+='<td colspan="1" class="right">'+ toMoney(cashonhand,'no') +'</td>';
                            tbody+='</tr>';
                            
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
                        tbody+='<tr><td><b>Total Deposits</b></td>';
                        tbody+='<td colspan="3">'+toMoney(totalDeposit)+'</td>';
                        tbody+='</tr>';
                        tbody+='<tr><td><b>Total Withdrawals</b></td>';
                        tbody+='<td colspan="3">'+toMoney(totalWithdrawal)+'</td>';
                        tbody+='</tr>';
                        tbody+='<tr><td><b>Cash on Hand</b></td>';
                        tbody+='<td colspan="3">'+toMoney(cashOnHand)+'</td>';
                        tbody+='</tr>';
                        
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
    
    $('#btnEWalletPerCashier').live('click',function(){
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
                                tbody+='<td style="text-align:right;">'+toMoney(amount,'no')+'</td>';
                                tbody+='<td style="text-align:right;">'+transactionType+'</td>';
                                tbody+='</tr>';
                                
                            }
                        }catch(e){}
                        
                        tbody+='<tr style="height:30px;"> <td colspan="4"></td></tr>';
                        tbody+='<tr><td><b>Total Deposits</b></td>';
                        tbody+='<td colspan="3">'+toMoney(totalDeposit)+'</td>';
                        tbody+='</tr>';
                        tbody+='<tr><td><b>Total Withdrawals</b></td>';
                        tbody+='<td colspan="3">'+toMoney(totalWithdrawal)+'</td>';
                        tbody+='</tr>';
                        tbody+='<tr><td><b>Cash on Hand</b></td>';
                        tbody+='<td colspan="3">'+toMoney(cashOnHand)+'</td>';
                        tbody+='</tr>';
                        
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