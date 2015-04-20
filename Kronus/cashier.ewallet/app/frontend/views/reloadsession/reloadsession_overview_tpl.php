<br />
<div class="clear"></div>
<div id="tm-reload-form">
    <div style="width: 600px;">
        <form id="frmreloadsa">
            <?php if($startSessionFormModel->error_count): ?>
            <?php echo $startSessionFormModel->getErrorMessages(); ?>
            <?php endif; ?>       
            <?php echo MI_HTML::inputHidden($startSessionFormModel, 'max_deposit') ?>
            <?php echo MI_HTML::inputHidden($startSessionFormModel, 'min_deposit') ?>    
            <table class="standalonetbl">
                <tr>
                    <th><?php echo MI_HTML::label($startSessionFormModel, 'terminal_id', 'Gaming Terminal'); ?></th>
                    <td><?php echo MI_HTML::dropDownArray($startSessionFormModel, 'terminal_id', $terminals, 'id', 'code', array(''=>'--Select Terminal--')) ?></td>
                </tr>
                <tr>
                    <th>Current Casino</th>
                    <td id="current_casino"></td>
                </tr>
                <tr>
                    <th>Current Playing Balance </th>
                    <td id="cur_playing_bal"></td>
                </tr>
                <tr>
                    <th><?php echo MI_HTML::label($startSessionFormModel, 'sel_amount', 'Amount'); ?></th>
                    <td><?php echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', array(''=>'Select Amount')) ?></td>
                    <th>
                        <input type="checkbox" id="chkotheramount" disabled="disabled" name="chkotheramount"/>
                        <?php echo MI_HTML::label($startSessionFormModel, 'amount', 'Other Amount',array('id'=>'lblotheramount'))  ?>
                    </th>
                    <td><?php echo MI_HTML::inputText($startSessionFormModel, 'amount', array('disabled'=>'disabled','class'=>'auto','maxlength'=>8)) ?></td>
                </tr>
                <tr>
                    <th>
                        <input type="checkbox" id="chkbancnet" name="chkbancnet"/>
                        <?php echo MI_HTML::label($startSessionFormModel, 'lblbancnet', 'Bancnet',array('id'=>'lblbancnet'))  ?>
                    </th>
                </tr>
                <tr>
                    <th><?php echo MI_HTML::label($startSessionFormModel, 'lbltrace_number', 'Trace Number'); ?></th>
                    <td><?php echo MI_HTML::inputText($startSessionFormModel, 'trace_number', array('disabled'=>'disabled','maxlength'=>20)) ?> <td>
                </tr>
                <tr>
                    <th><?php echo MI_HTML::label($startSessionFormModel, 'lblreference_number', 'Reference Number'); ?></th>
                    <td><?php echo MI_HTML::inputText($startSessionFormModel, 'reference_number', array('disabled'=>'disabled','maxlength'=>20)) ?></td>
                </tr>
                <tr>
                    <th><?php echo MI_HTML::label($startSessionFormModel, 'voucher_code', 'Voucher Code') ?></th>
                    <td><?php echo MI_HTML::inputText($startSessionFormModel, 'voucher_code', array('maxlength'=>20)) ?></td>
                </tr>
                <tr>
                    <td><input type="button" value="Reload" id="btnreloadsa"/></td>
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
                <td colspan="5">INITIAL DEPOSIT: <b id="reloadinitialdeposit"></b></td>
            </tr>
            <tr>
                <td colspan="5">TOTAL RELOAD: <b id="reloadtotalreload"></b></td>
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
<script type="text/javascript" src="jscripts/validation.js"></script> 
<script type="text/javascript">
    $(document).ready(function(){
        $('#StartSessionFormModel_terminal_id').focus();
        
        
        $('#btnreloadsa').click(function(){
            var voucher = $("#StartSessionFormModel_voucher_code").val();
            if(!reloadSessionStandAloneChecking()) {
                return false;
            }
//            if($('#StartSessionFormModel_sel_amount').is(':disabled')) {
//                var amount = $('#StartSessionFormModel_amount').val();
//            } else {
//                var amount = $('#StartSessionFormModel_sel_amount').val();
//            } 
            
            //check voucher length
            if(voucher.length == 0){
                if(!confirm('Are you sure you want to reload this session with the amount of  ' + toMoney($('#StartSessionFormModel_amount').val())+'?')) {
                    return false;
                }
            } else {
                $("#StartSessionFormModel_voucher_code").val(voucher);
                if(!confirm('Are you sure you want to reload this session using a voucher?')) {
                    return false;
                }
            }
            
            showLightbox(function(){
                var url = '<?php echo Mirage::app()->createUrl('reload') ?>';
                var data = $('#frmreloadsa').serialize();
                $.ajax({
                    type : 'post',
                    url : url,
                    data :data,
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
        });
        
        // change denomination
        $('#StartSessionFormModel_sel_amount').live('change',function(){
            if($(this).val() != '--') {
                $('#StartSessionFormModel_amount').val($(this).children('option:selected').html());
                if($(this).val() == '')
                    $('#StartSessionFormModel_amount').val('');
                }
        });
        
        //$('#StartSessionFormModel_terminal_id').live('change',function(){
        $('#StartSessionFormModel_terminal_id').change(function(){    
            $('#StartSessionFormModel_sel_amount').html('<option value="">Select Amount</option>');
            $('#cur_playing_bal').html(''); 
            $('#current_casino').html('');
            $('#chkotheramount').attr('disabled','disabled');
            $('#chkotheramount').removeAttr('checked');
            $('#StartSessionFormModel_amount').val('');
            $('#StartSessionFormModel_amount').attr('disabled','disabled');
            $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
            $('#StartSessionFormModel_max_deposit').val('');
            $('#StartSessionFormModel_min_deposit').val('');
            
            $('#reloadlogin').html('');
            $('#reloadtimein').html('');
            $('#reloadinitialdeposit').html('');
            $('#reloadtotalreload').html('');
            $('#reloadtbody').html('');
            $('#reloadlogin').html('');
            $('#curr_playing_bal').remove();
            
            if($(this).val() == '') {
                return false;
            }
            $('#chkotheramount').removeAttr('disabled');
            $('#chkbancnet').removeAttr('disabled');
            $('#StartSessionFormModel_trace_number').attr('disabled','disabled');
            $('#StartSessionFormModel_reference_number').attr('disabled','disabled');
            
            showLightbox(function(){
                var url = '<?php echo Mirage::app()->createUrl('terminal/denomination') ?>';
                var data = 'terminal_id='+$('#StartSessionFormModel_terminal_id').val() + '&isreload=1';
                $.ajax({
                    type: 'post',
                    url : url,
                    data : data,
                    success : function(data){
                        try {
                            var json = $.parseJSON(data);
                            var opt = '';
                            $('#cur_playing_bal').html(json.terminal_balance);
                            $('#StartSessionFormModel_max_deposit').val(json.max_denomination);
                            $('#StartSessionFormModel_min_deposit').val(json.min_denomination);
                            $.each(json.denomination,function(k,v){
                                opt+='<option value="'+k+'" >'+v+'</option>';
                            });
                            $('#StartSessionFormModel_sel_amount').append(opt);
                            
                            $('#reloadtimein').html(json.terminal_session_data.DateStarted);
                            $('#current_casino').html(json.casino[json.terminal_session_data.ServiceID]);
                            var initial_deposit = 0;
                            var tbody = '';
                            var total_reload = 0;
                            var login = $('#StartSessionFormModel_terminal_id').children('option:selected').html();
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
//                                tbody += '<td>' + v.TerminalType + '</td>';
//                                tbody += '<td>' + v.Name + '</td>';
                                tbody += '</tr>';
                            });
                            $('#reloadlogin').html(login);
                            $('#reloadinitialdeposit').html(toMoney(initial_deposit));
                            $('#reloadtotalreload').html(toMoney(total_reload));
                            $('#reloadtbody').html(tbody);                            
                            hideLightbox();
                        }catch(e) {
                            alert('Oops! Something went wrong');
                            location.reload(true);
                        }
                    },
                    error : function(e) {
                        displayError(e);
                    }
                });
            });
        });
        
        $('#lblotheramount').click(function(){
            if($('#StartSessionFormModel_terminal_id').val() == '') {
                return false;
            }
            $('#StartSessionFormModel_amount').val('');
            $('#StartSessionFormModel_sel_amount').val('');
            if($('#chkotheramount').is(':checked')) {
                $('#chkotheramount').removeAttr('checked');
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').attr('disabled','disabled');
            } else {
                $('#chkotheramount').attr('checked','checked');
                $('#StartSessionFormModel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').val(0.00);
                $('#StartSessionFormModel_sel_amount').attr('disabled','disabled');
            }
        });
        
        $('#chkotheramount').click(function(){
            if($(this).is(':checked')) {
                $('#StartSessionFormModel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').val(0.00);
                $('#StartSessionFormModel_sel_amount').attr('disabled','disabled');
                $('#StartSessionFormModel_voucher_code').attr('disabled','disabled');
                $('#StartSessionFormModel_sel_amount').val('');
                $('#StartSessionFormModel_voucher_code').val('');
                $('#chkbancnet').attr('disabled','disabled');
                $('#ForceTFormModel_trace_number').val('');
                $('#ForceTFormModel_reference_number').val('');
            } else {
                $('#StartSessionFormModel_amount').val('');
                $('#chkbancnet').removeAttr('disabled');
                $('#StartSessionFormModel_amount').attr('disabled','disabled');
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
            }
        });
        
        $('#chkbancnet').click(function(){
            if($(this).is(':checked')) {
                $('#StartSessionFormModel_reference_number').removeAttr('disabled');
                $('#StartSessionFormModel_trace_number').removeAttr('disabled');
                $('#StartSessionFormModel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').val(0.00);
                $('#StartSessionFormModel_sel_amount').attr('disabled','disabled');
                $('#chkotheramount').attr('disabled','disabled');
                $('#StartSessionFormModel_voucher_code').attr('disabled','disabled');
                $('#StartSessionFormModel_sel_amount').val('');
                $('#StartSessionFormModel_voucher_code').val('');
                $('#StartSessionFormModel_trace_number').focus(function(){
                        $("#StartSessionFormModel_trace_number").bind("keypress", function (event) {
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

                $('#StartSessionFormModel_reference_number').focus(function(){
                        $("#StartSessionFormModel_reference_number").bind("keypress", function (event) {
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
            } else {
                $('#StartSessionFormModel_amount').val('');
                $('#StartSessionFormModel_trace_number').val('');
                $('#StartSessionFormModel_amount').attr('disabled','disabled');
                $('#StartSessionFormModel_trace_number').attr('disabled','disabled');
                $('#StartSessionFormModel_amount').attr('disabled','disabled');
                $('#chkotheramount').removeAttr('disabled');
                $('#StartSessionFormModel_reference_number').val('');
                $('#StartSessionFormModel_reference_number').attr('disabled','disabled');
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
            }
        });

        
        //for mouseout event for initial deposit and voucher fields
        $('#StartSessionFormModel_sel_amount').mouseout(function() {       
            if($('#chkotheramount').is(':checked')){
                //voucher code textbox  will stay disabled..
            } else {
                $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
            }
        });
        $('#StartSessionFormModel_voucher_code').mouseout(function() {
            if($('#chkotheramount').is(':checked')){
                //sel_amount drop down  will stay disabled..
            } else {
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                if($('#StartSessionFormModel_voucher_code').val() != ''){
                    $('#StartSessionFormModel_amount').val('');
                }
            }
        });
        //-------------------------end code for mouseout event------------------------------//
        

        //onfocus event for initial deposit, other amount and voucher fields
        $('#StartSessionFormModel_voucher_code').focus(function() {
            $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
            $('#StartSessionFormModel_sel_amount').attr('disabled', 'disabled');    
                        //for keypress event, allow alphanumeric values in voucher code
                        $("#StartSessionFormModel_voucher_code").bind("keypress", function (event) {
                                if (event.charCode!=0) {
                                    var regex = new RegExp("^[a-zA-Z0-9]+$");
                                    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                                    if (!regex.test(key)) {
                                        event.preventDefault();
                                        return false;
                                    }
                                }
                                
                                //clear amount textbox when keypress event start in voucher code textbox
                                if($('#StartSessionFormModel_voucher_code').val() != '' ){
                                        $('#StartSessionFormModel_sel_amount').val('');
                                        $('#StartSessionFormModel_amount').val('');
                                        $('#chkotheramount').removeAttr('checked');
                                        $('#StartSessionFormModel_amount').removeAttr('disabled');
                                        $('#StartSessionFormModel_amount').val('');
                                        $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
                                }
                        });
                        
                        //clear amount textbox when voucher code textbox has value
                        if($('#StartSessionFormModel_voucher_code').val() != '' ){
                                $('#StartSessionFormModel_sel_amount').val('');
                                $('#StartSessionFormModel_amount').val('');
                                $('#chkotheramount').removeAttr('checked');
                                $('#StartSessionFormModel_amount').removeAttr('disabled');
                                $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
                        }
                        //-------------------end code for keypress event---------------------//
        });
        $('#StartSessionFormModel_sel_amount').focus(function() {
            $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
            $('#StartSessionFormModel_voucher_code').attr('disabled', 'disabled');
            $('#StartSessionFormModel_sel_amount').change(function() {
                if($('#StartSessionFormModel_sel_amount').val() != '' ){
                        $('#StartSessionFormModel_voucher_code').val('');
                }
            });
        });
        $('#StartSessionFormModel_amount').focus(function() {
            $('#StartSessionFormModel_voucher_code').attr('disabled', 'disabled');
            $('#StartSessionFormModel_amount').change(function() {
                if($('#StartSessionFormModel_amount').val() != '' ){
                        $('#StartSessionFormModel_voucher_code').val('');
                }
            });
        });
        //-------------------------end code for onfocus event------------------------------//
        
    });
</script>
