<?php

/**
 * Date Created 11 10, 11 5:08:21 PM <pre />
 * Description of ButtonClickScripts
 * @author Bryan Salazar
 */
?>
<script type="text/javascript">
$(document).ready(function(){
    var type = {
        VIP : 1,
        NON_VIP : 0
    }

    /************************* START SESSION BUTTON CLICK *********************/
    jQuery('.startsession').live('click',function(){
        showLightbox();
        var parent = $(this).parents('td').attr('id');
        var tid = '';
        if($('#'+parent + ' > div:nth-child(2)').children('p').children('.togglevip').is(':checked')) {
            tid = $('#'+parent).attr('vipid');
            isvip = type.VIP;
        } else {
            tid = $('#'+parent).attr('nonvipid');
            isvip = type.NON_VIP;
        }
                                                      // use for updating the view
        var data = 'tid=' + tid + '&isvip=' + isvip + '&tcode='+parent;
        var url = '<?php echo Mirage::app()->createUrl('terminal/startsession'); ?>';
        $.ajax({
            type : 'post',
            url : url,
            data : data,
            success : function(data) {
                var code = $('#'+parent).children('.tcode').children('h1').html();
                updateLightbox(data,'START SESSION ' + code,function(){
                    $('#StartSessionFormModel_sel_amount').focus();
                });
            },
            error : function(e) {
                displayError(e);
            }
        });
    });

    /******************** POSTING OF START SESSION ****************************/
    $('#btnInitailDeposit').live('click',function(){

        if(!startSessionChecking()) {
            return false;
        } else {
            var issuccess = identifyCard();
        }

        if(issuccess == "false")
        {
                if($("#StartSessionFormModel_sel_amount").val() != 'voucher'){
                    if(!confirm('Are you sure you want to start a new session with the initial playing balance of  ' + toMoney($('#StartSessionFormModel_amount').val())+'?')) {
                        return false;
                    }
                } else {
                    if(!confirm('Are you sure you want to start a new session using a voucher?')) {
                        return false;
                    }
                }

                //get terminal code for blocking
                var preffixCode = '<?php echo $_SESSION['last_code']; ?>';
                var terminalCode = $('#tcode').val();
                terminalCode = terminalCode.replace(/vip/i,'');
                terminalCode = preffixCode+terminalCode;

                var data = $('#frmstartsession').serialize();
                showLightbox(function(){
                    $.ajax({
                        url : '<?php echo Mirage::app()->createUrl('terminal/startsession'); ?>',
                        type :'post',
                        data : data,
                        success : function(data) {
                            try {
                                var json = $.parseJSON(data);
                                <?php if($_SESSION['spyder_enabled'] == 0): ?>
                                try {
                                    var oaxPSMAC = new ActiveXObject("PEGS.StationManager.ActiveX.Controller");
                                    if(oaxPSMAC.UnlockScreen(terminalCode,<?php echo Mirage::app()->param['port'] ?>) != 1) {
                                        alert('<?php echo Mirage::app()->param['failed_unlock'] ?> ' + terminalCode);
                                    }
                                } catch(e) {
                                    alert('<?php echo Mirage::app()->param['pegsstationerrormsg'] ?>');
                                }
                                <?php endif; ?>
                            alert('Transaction Successful \n New player session started. The player initial playing balance is PhP ' + json.initial_deposit);
                            location.reload(true);
                            } catch(e) {
                                updateLightbox(data,'START SESSION');
                            }
                        },
                        error : function(e) {
                            displayError(e);
                        }
                    });
                });
        }

        return false;
        
    });

    $('#btnReload').live('click',function(){
        if(!startSessionChecking()) {
            return false;
        }

        if($("#StartSessionFormModel_sel_amount").val() != 'voucher'){
            if(!confirm('Are you sure you want to reload this session with the amount of  ' + toMoney($('#StartSessionFormModel_amount').val())+'?')) {
                return false;
            }
        } else {
            if(!confirm('Are you sure you want to reload this session using a voucher?')) {
                return false;
            }
        }

        var tcode = $('#tcode').val();
        var tid = $('#tid').val();
        var url = '<?php echo Mirage::app()->createUrl('terminal/reload'); ?>';
        var data = $('#frmreload').serialize();
        showLightbox(function(){
            $.ajax({
               url : url,
               data : data,
               type : 'post',
               success : function(data) {

                    try {
                        var json = $.parseJSON(data);
                        alert('Transaction Successful \n The amount of PhP '+ json.reload_amount + ' is successfully loaded.');
                        location.reload(true);
                    } catch(e) {
                        updateLightbox(data, 'RELOAD ' + tcode);
                    }
               },
               error : function(e) {
                   displayError(e);
               }
            });
        });
        return false;
    });

    // change denomination
    $('#StartSessionFormModel_sel_amount').live('change',function(){
        
        //empty value
        if($(this).val() != '--' && $(this).val() != 'voucher') {
            var caption = $(this).children('option:selected').html();
            if($(this).val() == '')
                caption = '';
            $('.auto').val(caption);
            $('.auto').attr('readonly','readonly');
            $('#StartSessionFormModel_voucher_code').hide();
            $('#StartSessionFormModel_voucher_code').val("");
            $('#StartSessionFormModel_amount').show();
        } 
        
         //voucher
        if($(this).val() == 'voucher'){
            $('#StartSessionFormModel_voucher_code').show();
            $('#StartSessionFormModel_amount').hide();     
            $('#StartSessionFormModel_voucher_code').val("");
            $('#StartSessionFormModel_amount').val("");
            $('#StartSessionFormModel_voucher_code').focus(function(){
                    $("#StartSessionFormModel_voucher_code").bind("keypress", function (event) {
                            if (event.charCode!=0) {
                                var regex = new RegExp("^[a-zA-Z0-9]+$");
                                var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                                if (!regex.test(key)) {
                                    event.preventDefault();
                                    return false;
                                }
                            }
                    });
            });
            $('#StartSessionFormModel_voucher_code').select();
        } 
        
        if($(this).val() == '--') {         
            $('#StartSessionFormModel_voucher_code').hide();
            $('#StartSessionFormModel_voucher_code').val("");
            $('#StartSessionFormModel_amount').show();
            $('.auto').removeAttr('readonly');
            $('.auto').val(0);
            $('.auto').select();
        }        
    });

    $('#showdetails_click').live('click',function(){
        var data = 'redeem_click=1&showdetails=1&StartSessionFormModel[terminal_id]='+$('#hidterminalid').val();
        var tbody = '';
        var total_reload = 0;
        var login = $('#terminal_code').html();
        if($(this).is(':checked')) {
            $.ajax({
                type: 'post',
                url : '<?php echo Mirage::app()->createUrl('terminal/redeem'); ?>',
                data : data,
                success : function(data) {
                    try {
                        var json = $.parseJSON(data);
                        for(i=0; i<json.trans_details.length;i++) {
                            tbody+= '<tr>';
                            if(json.trans_details[i].TransType == 'Deposit') {
                                initial_deposit = json.trans_details[i].Amount;
                                time_in = json.trans_details[i].DateStarted;
                            } else if(json.trans_details[i].TransType == 'Reload') {
                                total_reload +=parseFloat(toInt(json.trans_details[i].Amount));
                            }
                            tbody+= '<td>'+json.trans_details[i].TransType+'</td>';
                            tbody+= '<td>'+toMoney(json.trans_details[i].Amount,'no')+'</td>';
                            tbody+= '<td>'+json.trans_details[i].DateCreated+'</td>';
                            tbody+='</tr>';
                        }
                        $('#reloadlogin').html(login);
                        $('#reloadtimein').html(time_in);
                        $('#reloadinitialdeposit').html(toMoney(initial_deposit));
                        $('#reloadtotalreload').html(toMoney(total_reload));
                        $('#reloadtbody').html(tbody);
                    } catch(e) {
                        alert('Oops! Something went wrong');
                        location.reload(true);
                    }
                },
                error : function(e) {
                    displayError(e);
                }
            });
        } else {
            $('#reloadtbody').html('');
        }
    });

    $('.redeem').live('click',function(){
        var parentid = $(this).parents('td').attr('id');
        var terminal_code = $('#'+parentid).children('.tcode').children('h1').html();
        var tid = $(this).attr('tid');
        var data = 'redeem_click=1&StartSessionFormModel[terminal_id]='+tid;
        showLightbox(function(){
            
            $.ajax({
                type: 'post',
                url : '<?php echo Mirage::app()->createUrl('terminal/redeem'); ?>',
                data : data,
                success : function(data) {
                    updateLightbox(data, 'REDEEM <span id="terminal_code">'+terminal_code + '</span>',function(){
                        $('#reloadlogin').html(terminal_code);
                    });
                    
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
    }); // end redeem click

    $('.reload').live('click',function(){
        var url = '<?php echo Mirage::app()->createUrl('terminal/reload') ?>';
        var parentid = $(this).parents('td').attr('id');
        var tcode = $('#'+parentid).children('.tcode').children('h1').html();
        var is_vip = 0;
        var tid = $(this).attr('tid');
        var cid = $(this).attr('cid');
        if(strpos(tcode, 'vip')) {
            is_vip = 1;
        }
        var data = 'is_vip='+is_vip+'&tcode='+tcode+'&tid='+tid+'&cid='+cid;
        showLightbox(function(){
            $.ajax({
               url:url,
               type: 'post',
               data : data,
               success : function(data) {
                   updateLightbox(data, 'RELOAD ' + tcode,function(){
                       $('#StartSessionFormModel_sel_amount').focus();
                   });
               },
               error : function(e) {
                   displayError(e);
               }
            });
        });

    });    
});
</script>