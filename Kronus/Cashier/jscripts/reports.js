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
        var page_total_deposit = 0;
        var page_total_reload = 0;
        var page_total_redemption = 0;
        var page_total_gross_hold = 0;
        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                success : function(data) {
                    try {
                        var tbody = '';
                        var json = $.parseJSON(data);
                        for(i=0;i<json.rows.length;i++) {
                            tbody+='<tr>';
                            tbody+='<td>' + json.rows[i].TerminalCode + '</td>';
                            tbody+='<td>' + formatDateAMPM(removeMillisec(json.rows[i].DateStarted)) + '</td>';
                            tbody+='<td>' + ((json.rows[i].DateEnded == 0)?'Still playing ...':formatDateAMPM(removeMillisec(json.rows[i].DateEnded))) + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].Deposit,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].Reload,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(json.rows[i].Withdrawal,'no') + '</td>';
                            tbody+='<td class="right">' + toMoney(parseFloat(toInt(json.rows[i].Deposit)) + parseFloat(toInt(json.rows[i].Reload)) - parseFloat(toInt(json.rows[i].Withdrawal)),'no') + '</td>';
                            tbody+='</tr>';
                            page_total_deposit = parseFloat(page_total_deposit) + parseFloat(toInt(json.rows[i].Deposit));
                            page_total_reload = parseFloat(page_total_reload) + parseFloat(toInt(json.rows[i].Reload));
                            page_total_redemption = parseFloat(page_total_redemption) + parseFloat(toInt(json.rows[i].Withdrawal));
                            //page_total_gross_hold+=parseFloat(page_total_deposit) + parseFloat(page_total_reload) - parseFloat(page_total_redemption);
                        }
                        tbody+= '<tr style="background-color: rgb(188, 188, 186) ! important;"><th colspan="3">TOTAL</th><td class="right">'
                            +toMoney(page_total_deposit,'no')+'</td><td class="right">'+toMoney(page_total_reload,'no')+'</td><td class="right">'
                            +toMoney(page_total_redemption,'no')+'</td><td class="right">'+toMoney((page_total_deposit+page_total_reload-page_total_redemption),'no')+'</td></tr>';
                        tbody+='<tr style="background-color: rgb(188, 188, 186) ! important;"><th colspan="3">GRAND TOTAL</th><td class="right">'
                            +toMoney(json.total_rows.totaldeposit,'no')+'</td><td class="right">'+toMoney(json.total_rows.totalreload,'no')+'</td><td class="right">'
                            +toMoney(json.total_rows.totalwithdrawal,'no')+'</td><td class="right">'
                            +toMoney(parseFloat(json.total_rows.totaldeposit)+parseFloat(json.total_rows.totalreload)-parseFloat(json.total_rows.totalwithdrawal),'no')+'</td></tr>';
                        
                        
                        var current_page = $('#startlimit').val();
                        var opt = '';
                        
                        if(json.page_count) {
                            for(i=0;i<json.page_count;i++) {
                                if(current_page == (i + 1)) {
                                    opt+='<option selected="selected" value="'+(i + 1)+'">'+(i + 1)+'</option>';
                                } else {
                                    opt+='<option value="'+(i + 1)+'">'+(i + 1)+'</option>';
                                }
                            }
                        } else {
                            opt = '<option value="0">No result</option>';
                        }
                        $('#displayingpageof').html(json.displayingpageof);
                        $('#coverage').html(json.coverage);
                        $('#startlimit').html(opt);
                        $('#tbltranshistorybody').html('');
                        if(json.rows.length > 0) {
//                            if($('#hidpercashierindicator').length) {
                                tbody+='<tr style="background-color: #BCBCBA !important"><th colspan="3">TOTAL SALES</th>';
                                tbody+='<td colspan="2" align="center">'+toMoney(parseFloat(json.total_rows.totaldeposit) + parseFloat(json.total_rows.totalreload),'no')+'</td><td colspan="2"></td></tr>';
//                            }
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
});