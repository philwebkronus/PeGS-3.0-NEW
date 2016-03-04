<br />
<div class="clear"></div>
<div id="tm-reload-form">
    <div style="width: 600px;">
        <form id="frmredeemsa">
            <table class="standalonetbl">
                <tr>
                    <input type="hidden" name="siteamountinfo" id="siteamountinfo" value="<?php echo $siteAmountInfo; ?>" />
                    <th><?php echo MI_HTML::label($startSessionFormModel, 'terminal_id', 'Gaming Terminal'); ?></th>
                    <td><?php echo MI_HTML::dropDownArray($startSessionFormModel, 'terminal_id', $terminals, 'id', 'code', array(''=>'--Select Terminal--')) ?></td>
                </tr>
                <tr>
                    <th>Show details</th>
                    <td><input type="checkbox" disabled="disabled" name="showdetails" id="showdetails" /></td>
                </tr>
                <tr>
                    <th>Current Casino</th>
                    <td id="current_casino"></td>
                </tr>
                <tr>
                    <th>Redeemable Amount </th>
                    <td id="cur_playing_bal"></td>
                </tr>   
                <tr>
                    <td><input type="button" value="Redeem" id="btnreloadsa"/></td>
                </tr>                
            </table> 
        </form>
    </div>
    <div class="details">
        <table border="1" style="width: 100%">
            <tr>
                <td colspan="5">LOGIN: <b id="reloadlogin"></b></td>
            </tr>
            <tr>
                <td colspan="5">TIME IN: 
                    <b id="reloadtimein"></b>
                </td>
            </tr>  
            <tr>
                <td colspan="5">INITIAL DEPOSIT: <b id="initialdeposit"></b></td>
            </tr>
            <tr>
                <td colspan="5">TOTAL RELOAD: <b id="totalreload"></b></td>
            </tr>
            <tr>
                <th colspan="5" style="background-color: #62AF35">
                    <i>SESSION DETAILS</i>
                </th>
            </tr>  
            <tr>
                <th style="width: 70px;">Type</th><th style="width: 100px;">Amount</th><th>Time</th><!--<th>Terminal Type</th><th>Source</th>-->
            </tr>
            <tbody id="reloadtbody">
            </tbody>   
        </table>
   
    </div>
</div>
<script type="text/javascript">
    function CommaFormatted(num) 
    {
        num = num.toString().replace(/\$|\,/g,'');
        if(isNaN(num))
            num = "0";
        var sign = (num == (num = Math.abs(num)));
        num = Math.floor(num*100+0.50000000001);
        var cents = num%100;
        num = Math.floor(num/100).toString();
        if(cents<10)
            cents = "0" + cents;
        for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
            num = num.substring(0,num.length-(4*i+3))+','+ num.substring(num.length-(4*i+3));
        return (((sign)?'':'-') + num + '.' + cents);
    }
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
        $('#StartSessionFormModel_terminal_id').focus();
        
        $('#btnreloadsa').click(function(){
        if ($('#siteamountinfo').val() == 0){
             showLightbox(function(){
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[010]: Site amount is not set.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );   
            });
        }else{
            if($('#StartSessionFormModel_terminal_id').val() == '') {
                alert('Please select terminal');
                return false;
            }
            var amount = $('#cur_playing_bal').html().substr(3);
                amount = amount.replace(/,/g,"");
            //get terminal code for blocking
            
            var preffixCode = '<?php echo (!empty($_SESSION['last_code']) ? $_SESSION['last_code'] : ""); ?>';
            var terminalCode = $('#StartSessionFormModel_terminal_id > option:selected').html();
            var tid = $('#StartSessionFormModel_terminal_id').val();
            var isEwalletSession = false;
            
            if(terminalCode == null){
                terminalCode = $('#terminal_code').html();
            }
        
            terminalCode = terminalCode.replace(/vip/i,'');
            terminalCode = preffixCode+terminalCode;
            
             $.ajax({
                type : 'post',
                async: false,
                url : '<?php echo Mirage::app()->createUrl('terminal/isEwallet') ?>',
                data : {'tid':tid},
                success : function(data) {
                    try{
                        var json = JSON.parse(data);
                        isEwalletSession = json.IsEWallet;
                    }catch(e){
                    }
                },
                error : function(e) {
                    displayError(e);
                }

            });
            
            
            if(isEwalletSession){
                if(!confirm('Are you sure you want to end the session in this terminal?')) {
                    return false;
                }
            }else{
                if(!confirm('Are you sure you want to redeem the amount of ' + toMoney(amount) + '?')) {
                    return false;
                }
            }
            
            showLightbox(function(){
                var url = '<?php echo Mirage::app()->createUrl('redeem') ?>';
                var data =  $('#frmredeemsa').serialize()+'&StartSessionFormModel[amount]='+amount;
                $.ajax({
                    type: 'post',
                    data : data,
                    success : function(data) {
                        try {
                            var json = $.parseJSON(data);
                            alert(json.message);
                        }catch(e) {
                            alert('Oops! Something went wrong');
                        }
                        location.reload(true);
                    },
                    error : function(e) {
                        displayError(e);
                    }
                });
            });
//            if(eval(amount) > 0){
//                
//            }
//            else{
//                showLightbox(function(){
//                   alert("Terminal Session already been ended");
//                   location.reload(true); 
//                });
//            }
            
            <?php if($_SESSION['spyder_enabled'] == 0): ?>
            try {
                var oaxPSMAC = new ActiveXObject("PEGS.StationManager.ActiveX.Controller");
                if(oaxPSMAC.LockScreen(terminalCode,<?php echo Mirage::app()->param['port'] ?>) != 1) {
                    alert('<?php echo Mirage::app()->param['failed_lock'] ?> ' + terminalCode);
                    if(!confirm('<?php echo Mirage::app()->param['failed_lock'] ?> ' + terminalCode + ". \n Do you want to continue?")) {
                        return false;
                    }
                }
            } catch(e) {
                alert('<?php echo Mirage::app()->param['pegsstationerrormsg'] ?>');
            }
            <?php endif; ?>
        }
        });
        
        $('#showdetails').click(function(){
            $('#reloadtbody').html('');
            if($(this).is(':checked') && $('#StartSessionFormModel_terminal_id').val() != '') {
                showLightbox(function(){
                    var url = '<?php echo Mirage::app()->createUrl('redeem/getdetail') ?>';
                    var data = 'terminal_id='+$('#StartSessionFormModel_terminal_id').val();
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
//                                    tbody+='<td>'+json.trans_details[i].TerminalType+'</td>';
//                                    tbody+='<td>'+json.trans_details[i].Name+'</td>';
                                    tbody+='</tr>';
                                }
                                $('#reloadtbody').html(tbody);
                                hideLightbox();
                            } catch(e) {
                                alert('Oops! Something went wrong');
                                location.reload(true);
                            }
                        },
                        error : function(e) {
                            displayError(e);
                        }
                    });
                });
            } 
        });
        
        $('#StartSessionFormModel_terminal_id').change(function(){
            $('#reloadlogin').html('');
            $('#reloadtimein').html('');
            $('#current_casino').html('');
            $('#initialdeposit').html('');
            $('#totalreload').html('');
            $('#reloadtbody').html('');
            $('#cur_playing_bal').html('');
            $('#showdetails').removeAttr('checked');
            if($(this).val()=='') {
                $('#showdetails').attr('disabled','disabled');
                return false;
            }
            $('#showdetails').removeAttr('disabled');
            
            var time_in='';
            var initial_deposit = '';
         
            showLightbox(function(){
                var url = '<?php echo Mirage::app()->createUrl('redeem/getamount') ?>';
                var data = $('#frmredeemsa').serialize();
                var total_reload = 0;
                var login = $('#StartSessionFormModel_terminal_id').children('option:selected').html();
                var tbody = '';
                $.ajax({
                    type: 'post',
                    data : data,
                    url : url,
                    success : function(data) {
                        try {
                            var json = $.parseJSON(data);
                            $('#cur_playing_bal').html('PhP ' + json.amount);
                            if(json.total_detail != undefined && json.total_detail!=null) {
                                
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
                                    tbody+='<td>'+json.trans_details[i].DateCreated+'</td>';
//                                    tbody+='<td>'+json.trans_details[i].TerminalType+'</td>';
//                                    tbody+='<td>'+json.trans_details[i].Name+'</td>';
                                    tbody+='</tr>';
                                }
                            }
                            
                            $('#current_casino').html(json.casino);
                            $('#reloadtimein').html(time_in);
                            $('#initialdeposit').html(toMoney(initial_deposit));
                            $('#totalreload').html(toMoney(total_reload));                            
                            $('#reloadlogin').html(login);
                            $('#reloadtbody').html(tbody);
                            hideLightbox();
                        }catch(e) {
                            console.log(e);
                            alert('Oops! Something went wrong');
                            location.reload(true);
                        }
                        
                    },
                    error : function(e) {
                        displayError(e);
                    }
                })
            })
        });
    });
</script>