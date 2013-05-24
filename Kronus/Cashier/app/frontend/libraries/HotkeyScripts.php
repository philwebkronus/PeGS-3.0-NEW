<?php

/**
 * Date Created 11 2, 11 12:52:21 PM <pre />
 * Description of Hotkey
 * @author Bryan Salazar
 */
?>
<script type="text/javascript">
function deposit() {
    ActionType.Deposit = true;
    $.ajax({
        url : '<?php echo Mirage::app()->createUrl('terminal/startsessionhk'); ?>',
        success : function(data) {
            updateLightbox(data, 'START SESSION',function(){
                $('#StartSessionFormModel_terminal_id').focus();
            });

        },
        error : function(e) {
            displayError(e);
        }
    });
}

function reload() {
    ActionType.Deposit = false;
    $.ajax({
        url : '<?php echo Mirage::app()->createUrl('terminal/reloadhk'); ?>',
        success : function(data) {
            updateLightbox(data, 'RELOAD',function(){
                $('#StartSessionFormModel_terminal_id').focus();
            });
        },
        error : function(e) {
            displayError(e);
        }
    });
}

function redeem() {
    $.ajax({
        url : '<?php echo Mirage::app()->createUrl('terminal/redeemhk'); ?>',
        success : function(data) {
            updateLightbox(data, 'REDEEM',function(){
                $('#redeem_terminal_id').focus();
            });
        },
        error : function(e) {
            displayError(e);
        }
    });
}

$(document).ready(function(){
    var xhr = null;
    /****************** CHANGE TERMINAL IN REDEEM *****************************/ 
    $('#redeem_terminal_id').live('change',function(){
        $('#reloadtbody').html('');
        $('#current_casino').html('');
        if($('#redeemloading').length != 0) {
            $('#redeemloading').remove();
        }
        $('#redeem_terminal_balance').html('');
        var data = $('#frmredeem').serialize();
        
        if(xhr != null) {
            xhr.abort();
        }
        $('#showdetails').attr('disabled','disabled');
        if($(this).val() == '') {
            return false;
        }
        $('#showdetails').removeAttr('disabled','disabled');
        $('#frmredeem').before('<p id="redeemloading" style="color:red">Loading ...</p>');
        var total_reload = 0;
        var tbody = '';
        var login = $(this).children('option:selected').html();
        xhr = $.ajax({
            url : '<?php echo Mirage::app()->createUrl('redeem/getamount') ?>',
            type: 'post',
            data : data,
            success : function(data) {
                $('#redeemloading').remove();
//                $('#redeem_terminal_balance').html('PhP'+data);
                try {
                    var json = $.parseJSON(data);
                    $('#redeem_terminal_balance').html('PhP '+json.amount);
                    if(json.total_detail != undefined) {
                        for(i=0;i<json.total_detail.length;i++) {
                            if(json.total_detail[i].TransactionType == 'D') {
                                initial_deposit = json.total_detail[i].total_amount;
                                time_in = json.total_detail[i].DateCreated;
                            } else if(json.total_detail[i].TransactionType == 'R') {
                                total_reload += parseFloat(json.total_detail[i].total_amount);
                            }
                        }
                    }
                    if(json.terminal_session_data != undefined) {
                        time_in = json.terminal_session_data.DateStarted;
                        for(i=0;i<json.trans_details.length;i++) {
                            tbody+='<tr>';
                            tbody+='<td>'+json.trans_details[i].TransType+'</td>';                                    
                            if(json.trans_details[i].TransType == 'Deposit') {
                                initial_deposit = parseFloat(json.trans_details[i].Amount);
                            } else if(json.trans_details[i].TransType == 'Reload') {
                                total_reload += parseFloat(json.trans_details[i].Amount);
                            }
                            tbody+='<td class="amount">'+toMoney(json.trans_details[i].Amount,'no')+'</td>';
                            tbody+='<td>'+json.trans_details[i].DateStarted+'</td>';
                            tbody+='</tr>';
                        }
                    } 
                    $('#current_casino').html(json.casino);
                    $('#reloadtimein').html(time_in);
                    $('#reloadinitialdeposit').html(toMoney(initial_deposit));
                    $('#reloadtotalreload').html(toMoney(total_reload));                            
                    $('#reloadlogin').html(login);
                    $('#reloadtbody').html(tbody);
                    xhr = null;
                } catch(e) {
                    alert('Oops! Something went wrong');
                    location.reload(true);
                }

            },
            error : function(e) {
                if(xhr.status !== 0)
                    displayError(e);
            }
        })
    });
    
    var ajaxHandler = null;
    /**************** CHANGE TERMINAL IN RELOAD AND DEPOSIT *******************/ 
    $('#StartSessionFormModel_terminal_id').live('change',function(){
        if(ajaxHandler != null) {
            ajaxHandler.abort();
        }
        
        $('#StartSessionFormModel_sel_amount').html('<option value="">Select Amount</option>');
        $('#StartSessionFormModel_amount').val('');
        $('#StartSessionFormModel_amount').attr('readonly','readonly');
        $('#StartSessionFormModel_casino').html('<option value="">Select Casino</option>');
        $('#StartSessionFormModel_max_deposit').val('');
        $('#StartSessionFormModel_min_deposit').val('');
        
        data = 'terminal_id='+$('#StartSessionFormModel_terminal_id').val();
        
        if($('#reloadlogin').length != 0) {
            data+='&isreload=1';
            $('#reloadlogin').html('');
            $('#reloadtimein').html('');
            $('#reloadinitialdeposit').html('');
            $('#reloadtotalreload').html('');
            $('#reloadtbody').html('');
            $('#curr_playing_bal').remove();
            $('#current_casino').html('');
        }        
        
        if($(this).val() == '') {
            return false;
        }
        
        if(ActionType.Deposit == true) {
            if(!checkPartner($(this).children('option:selected').html())) {
                return false;
            }
        }
        
        if($('#loading').length != 0) {
            $('#loading').remove();
        }
        $('#frmhotkey').before('<p id="loading" style="color:red">Loading ...</p>');
        ajaxHandler = $.ajax({
            url : '<?php echo Mirage::app()->createUrl('terminal/denomination'); ?>',
            type : 'post',
            data: data,
            success : function(data){
                try {
                    var json = $.parseJSON(data);
                    $('#StartSessionFormModel_max_deposit').val(json.max_denomination);
                    $('#StartSessionFormModel_min_deposit').val(json.min_denomination);
                    var opt = '';
                    $.each(json.denomination,function(k,v){
                        opt+='<option value="'+k+'" >'+v+'</option>';
                    });
                    opt+='<option value="--">Other denomination</option>';
                    opt+='<option value="voucher">Voucher</option>';
                    $('#StartSessionFormModel_sel_amount').append(opt);
                    
                    var casopt = '';
                    $.each(json.casino,function(k,v){
                        casopt+='<option value="'+k+'" >'+v+'</option>';
                    });
                    $('#StartSessionFormModel_casino').html(casopt);
                    if($('#reloadlogin').length != 0) {
                        $('#frmhotkey').before('<p id="curr_playing_bal">Current Playing Balance: PhP ' + json.terminal_balance);
                        $('#reloadlogin').html($('#StartSessionFormModel_terminal_id').children('option:selected').html());
                        $('#reloadtimein').html(json.terminal_session_data.DateStarted);
                        var initial_deposit = 0;
                        var tbody = '';
                        var total_reload = 0;
                        $.each(json.trans_details,function(k,v){
                            tbody += '<tr>';
                            tbody += '<td>'+v.TransType+'</td>';
                            if(v.TransType == 'Deposit') {
                                initial_deposit =  v.Amount;
                                tbody += '<td class="amount">' + toMoney(initial_deposit,'no') + '</td>';
                            } else if(v.TransType == 'Reload') {
                                total_reload += parseFloat(toInt(v.Amount));
                                tbody += '<td class="amount">'+toMoney(v.Amount,'no') + '</td>';
                            }
                            tbody += '<td>' + v.DateCreated + '</td>';
                            tbody += '</tr>';
                        });
                        if(ActionType.Deposit == false) {
                            $('#current_casino').html(json.casino[json.terminal_session_data.ServiceID]);
                        }
                        $('#reloadinitialdeposit').html(toMoney(initial_deposit));
                        $('#reloadtotalreload').html(toMoney(total_reload));
                        $('#reloadtbody').html(tbody);
                        ajaxHandler = null;
                    }
                    
                    $('#loading').remove();
                } catch(e) {
                    alert(e);
                    location.reload(true);
                }
            },
            error : function(e) {
                if(ajaxHandler.status !== 0)
                    displayError(e);
            }
        });
    });
    
        $('#showdetails').live('click',function(){
            $('#reloadtbody').html('');
            if($(this).is(':checked') && $('#redeem_terminal_id').val() != '') {
                var url = '<?php echo Mirage::app()->createUrl('redeem/getdetail') ?>';
                var data = 'terminal_id='+$('#redeem_terminal_id').val();
                var tbody = '';
//                    var total_reload = 0;
                $.ajax({
                    type : 'post',
                    url : url,
                    data : data,
                    success : function(data) {
                        try {
                            var json = $.parseJSON(data);
                            for(i=0;i<json.trans_details.length;i++) {
                                tbody+='<tr>';
                                tbody+='<td>'+json.trans_details[i].TransType+'</td>';                                    
                                tbody+='<td class="amount">'+toMoney(json.trans_details[i].Amount,'no')+'</td>';
                                tbody+='<td>'+json.trans_details[i].DateCreated+'</td>';
                                tbody+='</tr>';
                            }
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
                
            } 
        });
    
    $('#btnRedeemHk').live('click',function(){
        if(xhr) {
            alert('Please wait ..');
            return false;
        }    
        if($('#redeem_terminal_balance').html() == '') {
            alert('Please select terminal');
            return false;
        }    
        
        var amount = $('#redeem_terminal_balance').html().substr(3);
            amount = amount.replace(/,/g,"");
            
        if(!confirm('Are you sure you want to redeem the amount of ' + $('#redeem_terminal_balance').html() + '?')) {
            return false;
        }
        var data = $('#frmredeem').serialize();
        var terminal_balance = $('#redeem_terminal_balance').html();
        data += '&StartSessionFormModel[amount]='+ terminal_balance.substr(3);
        showLightbox(function(){
            $.ajax({
                url : '<?php echo Mirage::app()->createUrl('terminal/redeemhk') ?>',
                type : 'post',
                data : data,
                success : function(data){
                    try {
                        var json = $.parseJSON(data);
                        alert(json.message);
                        location.reload(true);
                    }catch(e) {
                        updateLightbox(data, 'REDEEM');
                    }

                }, 
                error : function(e) {
                    displayError(e);
                } 
            });
        });
        
//        if(eval(amount) > 0){
//            
//        }
//        else{
//            showLightbox(function(){
//                alert("Terminal Session already been ended");
//                location.reload(true); 
//            });
//        }
        
        //get terminal code for blocking
        var preffixCode = '<?php echo $_SESSION['last_code']; ?>';
        var terminalCode = $('#redeem_terminal_id > option:selected').html();
        if(terminalCode == null){
            terminalCode = $('#terminal_code').html();
        }
        
        terminalCode = terminalCode.replace(/vip/i,'');
        terminalCode = preffixCode+terminalCode;
        <?php if($_SESSION['spyder_enabled'] == 0): ?>
        try {
            var oaxPSMAC = new ActiveXObject("PEGS.StationManager.ActiveX.Controller");
            if(oaxPSMAC.LockScreen(terminalCode,<?php echo Mirage::app()->param['port'] ?>) != 1) {
                if(!confirm('<?php echo Mirage::app()->param['failed_lock'] ?> ' + terminalCode + ".\n Do you want to continue?")) {
                    return false;
                }
                
            }
        }catch(e) {
            alert('<?php echo Mirage::app()->param['pegsstationerrormsg'] ?>');
        }
        <?php endif; ?>
        return false;
    });

    $('#btnReloadhk').live('click',function(){
        if(ajaxHandler) {
            alert('Please wait ..');
            return false;
        }        
        if($('#StartSessionFormModel_terminal_id').val() == '') {
            alert('Please select terminal');
            return false;
        }
    
        if(!startSessionChecking()) {
            return false;
        }
        
        if($("#StartSessionFormModel_sel_amount").val() != 'voucher'){
            if(!confirm('Are you sure you want to reload this session with the amount of PhP ' + toMoney($('#StartSessionFormModel_amount').val())+'?')) {
                return false;
            }
        } else {
            if(!confirm('Are you sure you want to reload this session using a voucher?')) {
                return false;
            }
        }
        
        var tcode = $('#tcode').val();
        var tid = $('#tid').val();
        var url = '<?php echo Mirage::app()->createUrl('terminal/reloadhk'); ?>';
        var data = $('#frmhotkey').serialize();
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
    
    $('#btnInitailDepositHk').live('click',function(){
        if($('#StartSessionFormModel_terminal_id').val() == '') {
            alert('Please select terminal');
            return false;
        }
        
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
                    var data = $('#frmhotkey').serialize();
                    showLightbox(function(){
                        $.ajax({
                            url : '<?php echo Mirage::app()->createUrl('terminal/startsessionhk'); ?>',
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
});

$(document).keypress(function(e){
    code = (e.keyCode)?e.keyCode:e.which;
    var key = {
        DEPOSIT : '100', // d
        RELOAD : '114', // r
        WITHDRAW : '119', // w
        CLOSE : '27' // escape
    };

    if($('#fancybox-content').html() == '') {
        switch(code.toString()) {
            case key.DEPOSIT:
                showLightbox();
                deposit();
                break;
            case key.RELOAD: 
                showLightbox();
                reload();
                break;
            case key.WITHDRAW:    
                showLightbox();
                redeem();
                break;
        }
    } else {
        if(code.toString() == key.CLOSE) {
            hideLightbox();
        }
    }
});
</script>