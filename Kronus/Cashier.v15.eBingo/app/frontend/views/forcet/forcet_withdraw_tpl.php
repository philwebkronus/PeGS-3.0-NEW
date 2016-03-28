<?php
?>
<?php Mirage::loadLibraries(array('CardScripts','LoyaltyScripts')); ?>
<script type="text/javascript" src="jscripts/validation.js"></script> 
<script type="text/javascript" src="jscripts/check_partner.js"></script>
<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript">
    $('document').ready(function(){
        $('#ForceTFormModel_amount').autoNumeric();
    });
</script>

 <table class="standalonetbl">
     <input type="hidden" name="siteamountinfo" id="siteamountinfo" value="<?php echo $siteAmountInfo; ?>" />
        <tr>
            <th><?php echo MI_HTML::label($FTModel, 'loyalty_card', 'Membership Card') ?></th>
            <td><?php echo MI_HTML::inputPassword($FTModel, 'loyalty_card') ?></td>
            <!--<td><a href="javascript:void(0);" id="get_info_card3">Get Card Info</a><a style="display: none;" href="javascript:void(0);" id="register">Register</a></td>-->
        </tr>
        <tr>
            <th>Current Playing Balance </th>
            <td id="cur_playing_bal_ub"></td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($FTModel, 'pin', 'PIN :') ?></th>
            <td><?php echo MI_HTML::inputPassword($FTModel, 'pin',array('maxlength'=>6)) ?></td>
        </tr>

        <tr>
            <th>Amount to be withdrawn </th>
            <td><?php echo MI_HTML::inputText($FTModel, 'amount', array('class'=>'auto','maxlength'=>10,'value'=>'0.00')) ?></td>
        </tr>
        <tr>
            <td><input type="button" value="Withdraw" id="btnWithdraw2"/></td>
        </tr>                
</table>




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
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );   
            });
        }
        $('#btnWithdraw2').click(function(){
                    
              if ($('#siteamountinfo').val() == 0){
             showLightbox(function(){
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );   
            });
        }else{
        
            if($('#ForceTFormModel_loyalty_card').val() == '') {
                alert('Please input membership card');
                return false;
            }
            
            var issuccess = identifyCard3();
            
            if(issuccess == "false"){
            
            if($('#ForceTFormModel_amount').val() == '') {
                alert('Please Enter Amount to be withdrawn');
                return false;
            }
            
            if($('#ForceTFormModel_pin').val() == '') {
                alert('Please Enter PIN');
                return false;
            }
            
            if($('#ForceTFormModel_amount').val() <= 0) {
                alert('Invalid Amount to be withdrawn');
                return false;
            }
            
            
            var amount = toMoney($("#ForceTFormModel_amount").val(), true);
            amount = Number(amount.replace(/[^0-9\.]+/g,""));
            //get terminal code for blocking
            
            if(amount=='0.00'){
                alert('Indicated amount cannot be withdrawn.');
                return false;
            }
            
            if(parseFloat(amount) > 999999){
                alert('Indicated amount cannot be withdrawn.');
                return false;
            }
            
            if(!confirm('Are you sure you want to withdraw from this account the amount of ' + toMoney(amount) + '?')) {
                return false;
            }
            
            $('#btnWithdraw2').attr('disabled','disabled'); //added 11-05-2015 2:40 PM
            
            showLightbox(function(){
                var url = '<?php echo Mirage::app()->createUrl('redeem/redeemForcet') ?>';
                var data =  {amount: function(){ return $("#ForceTFormModel_amount").val();},
                            pin: function(){ return $("#ForceTFormModel_pin").val();},
                            cardnumber: function(){return $("#ForceTFormModel_loyalty_card").val();}
                         };
                $.ajax({
                    url : url,
                    type: 'post',
                    data : data,
                    success : function(data) {
                        try {
                            var json = $.parseJSON(data);
                            var msg = json.message;
                                if (msg.indexOf('successful') !== -1) { 
                                    alert(json.message);
                                    $('#btnWithdraw2').removeAttr('disabled'); //added 11-05-2015 2:40 PM
                                    location.reload(true);
                                }
                                else{
                                    updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">'+ json.message +'</label>' + 
                                                                    '<br /></center>' + 
                                                                    '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClr" />',
                                                                    ''          
                                    );
                                    $('#btnWithdraw2').removeAttr('disabled'); //added 11-05-2015 2:40 PM
                                }
                        }catch(e) {
                            alert('Oops! Something went wrong');
                            $('#btnWithdraw2').removeAttr('disabled'); //added 11-05-2015 2:40 PM
                            location.reload(true);
                        }
                        
                    },
                    error : function(e) {
                        displayError(e);
                    }
                });
            });
            
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
            return false;
            }
        });
         
            $('#ForceTFormModel_loyalty_card').bind("enterKey",function(e){
                var issuccess = identifyCard3();

                var url = '<?php echo Mirage::app()->createUrl('forcet/getbalance') ?>';
                var data =  {loyalty_card: function(){return $("#ForceTFormModel_loyalty_card").val();}};
                $.ajax({
                    url : url,
                    type: 'post',
                    data : data,
                    success : function(data) {
                        try {
                            $('#cur_playing_bal_ub').html("");
                            $('#cur_playing_bal_ub').html(data);
                        }catch(e) {
                            alert('Oops! Something went wrong');
                            location.reload(true);
                        }

                    },
                    error : function(e) {
                        displayError(e);
                    }
                });
                
//                if(issuccess == 'false'){
//                    var url = '<?php //echo Mirage::app()->createUrl('redeem/getbalance') ?>';
//                    var data = 'loyalty_card='+$('#ForceTFormModel_loyalty_card').val();
//                    var tbody = '';
////                    var total_reload = 0;
//                    showLightbox(function(){
//                        $.ajax({
//                            type : 'post',
//                            url : url,
//                            data : data,
//                            success : function(data) {
//                                try {
//                                    var json = $.parseJSON(data);
//                                    $('#cur_playing_bal').html('PhP ' + json.amount);
//                                } catch(e) {
//                                    alert('Oops! Something went wrong');
//                                    location.reload(true);
//                                }
//                                hideLightbox();
//                            },
//                            error : function(e) {
//                                displayError(e);
//                            }
//                        });
//                    });
//                }
//                return false
            });
            $('#ForceTFormModel_loyalty_card').keyup(function(e){
                if(e.keyCode == 13)
            {
                $(this).trigger("enterKey");
            } else {
                $('#cur_playing_bal_ub').html("");
            }
            });
        
        $('#showdetails').click(function(){
            $('#reloadtbody').html('');
            if($(this).is(':checked') && $('#ForceTFormModel_terminal_id').val() != '') {
                showLightbox(function(){
                    var url = '<?php echo Mirage::app()->createUrl('withdraw/getdetail') ?>';
                    var data = 'terminal_id='+$('#ForceTFormModel_terminal_id').val();
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
        
    });
</script>